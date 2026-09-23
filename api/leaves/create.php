<?php
/**
 * API Submit Leave Request
 * POST /api/leaves/create.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Metode HTTP tidak diizinkan. Gunakan POST.', null, 405);
}

$user = authenticateApiUser();
$pdo = getDbConnection();

// Handle input from either JSON or multipart/form-data
$input = getApiRequestData();

$leaveTypeId = (int)($input['leave_type_id'] ?? 0);
$tanggalMulai = trim($input['tanggal_mulai'] ?? '');
$tanggalSelesai = trim($input['tanggal_selesai'] ?? '');
$alasan = trim($input['alasan'] ?? '');
$alamatSelamaCuti = trim($input['alamat_selama_cuti'] ?? '');
$kontakDarurat = trim($input['kontak_darurat'] ?? '');

$errors = [];

if ($leaveTypeId <= 0) {
    $errors['leave_type_id'] = 'Jenis cuti wajib dipilih.';
}
if (empty($tanggalMulai) || !strtotime($tanggalMulai)) {
    $errors['tanggal_mulai'] = 'Tanggal mulai cuti tidak valid.';
}
if (empty($tanggalSelesai) || !strtotime($tanggalSelesai)) {
    $errors['tanggal_selesai'] = 'Tanggal selesai cuti tidak valid.';
}
if (empty($alasan)) {
    $errors['alasan'] = 'Alasan cuti wajib diisi.';
}

if (!empty($tanggalMulai) && !empty($tanggalSelesai)) {
    $dMulai = new DateTime($tanggalMulai);
    $dSelesai = new DateTime($tanggalSelesai);
    if ($dSelesai < $dMulai) {
        $errors['tanggal_selesai'] = 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.';
    }
}

if (!empty($errors)) {
    jsonResponse(false, 'Validasi data gagal. Silakan periksa formulir Anda.', null, 422, $errors);
}

// Fetch Leave Type
$stmtType = $pdo->prepare("SELECT * FROM jenis_cuti WHERE id = ?");
$stmtType->execute([$leaveTypeId]);
$leaveType = $stmtType->fetch();

if (!$leaveType) {
    jsonResponse(false, 'Jenis cuti yang dipilih tidak ditemukan di sistem.', null, 404);
}

// Calculate business days
$dMulai = new DateTime($tanggalMulai);
$dSelesai = new DateTime($tanggalSelesai);
$totalHari = 0;
$period = new DatePeriod($dMulai, new DateInterval('P1D'), (clone $dSelesai)->modify('+1 day'));

foreach ($period as $dt) {
    // Exclude Sunday (0) and optionally Saturday (6) if standard office schedule
    $w = (int)$dt->format('w');
    if ($w !== 0 && $w !== 6) {
        $totalHari++;
    }
}
// Minimum 1 day
if ($totalHari === 0) {
    $totalHari = 1;
}

// Check Quota if potong_kuota is active
if ((int)$leaveType['potong_kuota'] === 1) {
    if ($user['sisa_cuti'] < $totalHari) {
        jsonResponse(false, "Sisa kuota cuti Anda tidak mencukupi! Sisa: {$user['sisa_cuti']} hari, Dibutuhkan: {$totalHari} hari.", null, 400);
    }
}

// Check overlapping pending or approved leaves
$stmtOverlap = $pdo->prepare("
    SELECT id, nomor_surat, tanggal_mulai, tanggal_selesai 
    FROM pengajuan_cuti 
    WHERE employee_id = ? 
      AND status IN ('pending', 'approved')
      AND (
          (tanggal_mulai BETWEEN ? AND ?) OR
          (tanggal_selesai BETWEEN ? AND ?) OR
          (? BETWEEN tanggal_mulai AND tanggal_selesai)
      )
    LIMIT 1
");
$stmtOverlap->execute([
    $user['id'], 
    $tanggalMulai, $tanggalSelesai, 
    $tanggalMulai, $tanggalSelesai, 
    $tanggalMulai
]);
$overlapping = $stmtOverlap->fetch();

if ($overlapping) {
    jsonResponse(false, "Anda sudah memiliki pengajuan cuti ({$overlapping['nomor_surat']}) pada rentang tanggal tersebut!", null, 400);
}

// Handle File Attachment
$attachmentFilename = null;
$uploadDir = __DIR__ . '/../../assets/uploads/leaves/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Case 1: Standard File Upload via $_FILES
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['attachment']['tmp_name'];
    $fileSize = $_FILES['attachment']['size'];
    $fileName = $_FILES['attachment']['name'];
    
    // Check Size (Max 5MB)
    if ($fileSize > 5 * 1024 * 1024) {
        jsonResponse(false, 'Ukuran berkas lampiran maksimal 5MB.', null, 422);
    }
    
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'pdf'];
    if (!in_array($ext, $allowedExts, true)) {
        jsonResponse(false, 'Format berkas tidak diizinkan. Hanya menerima JPG, PNG, atau PDF.', null, 422);
    }
    
    // Verify MIME Type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $fileTmp);
    finfo_close($finfo);
    $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];
    if (!in_array($mime, $allowedMimes, true)) {
        jsonResponse(false, 'Tipe MIME berkas tidak valid untuk keamanan.', null, 422);
    }
    
    $attachmentFilename = 'leave_' . $user['id'] . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($fileTmp, $uploadDir . $attachmentFilename)) {
        jsonResponse(false, 'Gagal menyimpan berkas lampiran.', null, 500);
    }
}
// Case 2: Base64 Encoded file payload
elseif (!empty($input['attachment_base64']) && !empty($input['attachment_name'])) {
    $rawBase64 = $input['attachment_base64'];
    $originalName = $input['attachment_name'];
    
    // Strip data URI scheme if present
    if (preg_match('/^data:([^;]+);base64,(.+)$/', $rawBase64, $matches)) {
        $rawBase64 = $matches[2];
    }
    
    $fileData = base64_decode($rawBase64);
    if ($fileData !== false) {
        if (strlen($fileData) > 5 * 1024 * 1024) {
            jsonResponse(false, 'Ukuran berkas lampiran maksimal 5MB.', null, 422);
        }
        
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($ext, $allowedExts, true)) {
            jsonResponse(false, 'Format berkas tidak diizinkan. Hanya menerima JPG, PNG, atau PDF.', null, 422);
        }
        
        $attachmentFilename = 'leave_' . $user['id'] . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        file_put_contents($uploadDir . $attachmentFilename, $fileData);
    }
}

// Require attachment if leave type mandates it
if ((int)$leaveType['butuh_lampiran'] === 1 && empty($attachmentFilename)) {
    jsonResponse(false, "Jenis cuti '{$leaveType['nama_cuti']}' mewajibkan lampiran bukti/surat dokter.", null, 422);
}

// Generate Surat Number
$year = date('Y');
$month = date('m');
$stmtCount = $pdo->query("SELECT COUNT(*) FROM pengajuan_cuti WHERE YEAR(created_at) = '$year'");
$seq = (int)$stmtCount->fetchColumn() + 1;
$nomorSurat = sprintf("CUTI/NAK/%s/%s/%03d", $year, $month, $seq);

// Insert Leave Request
try {
    $stmtInsert = $pdo->prepare("
        INSERT INTO pengajuan_cuti (
            nomor_surat, employee_id, leave_type_id,
            tanggal_mulai, tanggal_selesai, total_hari,
            alasan, alamat_selama_cuti, kontak_darurat,
            attachment, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmtInsert->execute([
        $nomorSurat,
        $user['id'],
        $leaveTypeId,
        $tanggalMulai,
        $tanggalSelesai,
        $totalHari,
        $alasan,
        $alamatSelamaCuti,
        $kontakDarurat,
        $attachmentFilename
    ]);
    
    $leaveId = (int)$pdo->lastInsertId();
    
    jsonResponse(true, 'Pengajuan cuti berhasil dikirim! Menunggu persetujuan atasan.', [
        'id' => $leaveId,
        'nomor_surat' => $nomorSurat,
        'total_hari' => $totalHari,
        'status' => 'pending'
    ], 201);
} catch (PDOException $e) {
    jsonResponse(false, 'Gagal menyimpan pengajuan cuti ke database.', null, 500);
}
