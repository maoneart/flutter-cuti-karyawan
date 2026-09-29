<?php
/**
 * API Revise Mangkir / Attendance Record
 * POST /api/attendance/revisi.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Metode HTTP tidak diizinkan. Gunakan POST.', null, 405);
}

$user = authenticateApiUser();
$pdo = getDbConnection();

$userRole = strtolower($user['role'] ?? '');
$userLevel = (int)($user['level_hierarki'] ?? 1);
$isLeaderOrSpv = in_array($userRole, ['leader', 'supervisor', 'atasan']) || ($userLevel >= 3 && $userLevel <= 4);
$isManager = $userRole === 'manager' || ($userLevel >= 5 && $userLevel <= 6);
$isHRD = in_array($userRole, ['admin', 'superadmin', 'hrd']) || $userLevel >= 7;

if (!$isLeaderOrSpv && !$isManager && !$isHRD) {
    jsonResponse(false, 'Akses ditolak! Anda tidak memiliki wewenang merevisi absensi.', null, 403);
}

$input = getApiRequestData();
$absensiId = (int)($input['absensi_id'] ?? 0);
$employeeId = (int)($input['employee_id'] ?? 0);
$tanggal = trim($input['tanggal'] ?? date('Y-m-d'));
$direvisiMenjadi = trim($input['direvisi_menjadi'] ?? 'SD');
$alasanRevisi = trim($input['alasan_revisi'] ?? '');

// Find record
if ($absensiId > 0) {
    $stmtAbs = $pdo->prepare("
        SELECT a.*, e.nama_lengkap, e.departemen_id, e.sisa_cuti, e.cuti_terpakai 
        FROM absensi_shift a
        JOIN karyawan e ON a.employee_id = e.id
        WHERE a.id = ?
    ");
    $stmtAbs->execute([$absensiId]);
    $record = $stmtAbs->fetch();
} else {
    $stmtAbs = $pdo->prepare("
        SELECT a.*, e.nama_lengkap, e.departemen_id, e.sisa_cuti, e.cuti_terpakai 
        FROM absensi_shift a
        JOIN karyawan e ON a.employee_id = e.id
        WHERE a.employee_id = ? AND a.tanggal = ?
    ");
    $stmtAbs->execute([$employeeId, $tanggal]);
    $record = $stmtAbs->fetch();
}

if (!$record) {
    jsonResponse(false, 'Data absensi tidak ditemukan.', null, 404);
}

if ($isLeaderOrSpv && (int)$user['departemen_id'] !== (int)$record['departemen_id']) {
    jsonResponse(false, 'Akses ditolak! Anda hanya berwenang merevisi data di bagian Anda.', null, 403);
}

// Fetch leave type
$stmtType = $pdo->prepare("SELECT * FROM jenis_cuti WHERE kode = ?");
$stmtType->execute([$direvisiMenjadi]);
$leaveType = $stmtType->fetch();

// Attachment handling
$attachmentFilename = null;
$uploadDir = __DIR__ . '/../../assets/uploads/revisi/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (!empty($input['bukti_base64']) && !empty($input['bukti_name'])) {
    $rawBase64 = $input['bukti_base64'];
    if (preg_match('/^data:([^;]+);base64,(.+)$/', $rawBase64, $matches)) {
        $rawBase64 = $matches[2];
    }
    $fileData = base64_decode($rawBase64);
    if ($fileData !== false && strlen($fileData) <= 5 * 1024 * 1024) {
        $ext = strtolower(pathinfo($input['bukti_name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'], true)) {
            $attachmentFilename = 'revisi_' . $record['id'] . '_' . time() . '.' . $ext;
            file_put_contents($uploadDir . $attachmentFilename, $fileData);
        }
    }
}

try {
    $pdo->beginTransaction();

    $newStatus = strtolower($direvisiMenjadi);
    $stmtUpdate = $pdo->prepare("
        UPDATE absensi_shift 
        SET status = ?,
            status_revisi = 'revised',
            direvisi_menjadi = ?,
            alasan_revisi = ?,
            bukti_lampiran = COALESCE(?, bukti_lampiran),
            direvisi_pada = NOW(),
            hrd_read = 0
        WHERE id = ?
    ");
    $stmtUpdate->execute([$newStatus, $direvisiMenjadi, $alasanRevisi, $attachmentFilename, $record['id']]);

    // If revised to leave type that deducts quota (CT, CT-HALF, ST)
    if ($leaveType && (int)$leaveType['potong_kuota'] === 1) {
        $potongHari = ($direvisiMenjadi === 'CT-HALF') ? 0.5 : 1.0;
        
        $sisaBaru = max(0, (float)$record['sisa_cuti'] - $potongHari);
        $terpakaiBaru = (float)$record['cuti_terpakai'] + $potongHari;

        $stmtKaryawan = $pdo->prepare("UPDATE karyawan SET sisa_cuti = ?, cuti_terpakai = ? WHERE id = ?");
        $stmtKaryawan->execute([$sisaBaru, $terpakaiBaru, $record['employee_id']]);

        $stmtHist = $pdo->prepare("
            INSERT INTO riwayat_kuota_cuti (employee_id, kuota_sebelum, perubahan, kuota_sesudah, tipe, keterangan, created_by)
            VALUES (?, ?, ?, ?, 'potong_cuti', ?, ?)
        ");
        $stmtHist->execute([
            $record['employee_id'],
            $record['sisa_cuti'],
            -$potongHari,
            $sisaBaru,
            'Revisi Mangkir menjadi ' . $leaveType['nama_cuti'] . ' oleh Leader ' . $user['nama_lengkap'],
            $user['id']
        ]);
    }

    $pdo->commit();
    jsonResponse(true, "Status absensi untuk {$record['nama_lengkap']} berhasil direvisi menjadi " . ($leaveType['nama_cuti'] ?? $direvisiMenjadi) . ".", [
        'absensi_id' => (int)$record['id'],
        'status_revisi' => 'revised',
        'direvisi_menjadi' => $direvisiMenjadi
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(false, 'Gagal merevisi absensi: ' . $e->getMessage(), null, 500);
}
