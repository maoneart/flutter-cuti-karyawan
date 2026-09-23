<?php
/**
 * API Create Employee (For HRD / Admin)
 * POST /api/employees/create.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$currentUser = authenticateApiUser();
requireApiRole(['superadmin', 'admin', 'hrd'], $currentUser);

$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?? $_POST;

$nik = trim($input['nik'] ?? '');
$nama = trim($input['nama_lengkap'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? 'password123';
$role = trim($input['role'] ?? 'operator');
$deptId = (int)($input['departemen_id'] ?? 0);
$jabatanId = (int)($input['jabatan_id'] ?? 0);
$tanggalMasuk = trim($input['tanggal_masuk'] ?? date('Y-m-d'));
$kuotaCuti = (int)($input['kuota_cuti'] ?? 12);
$jenisKelamin = trim($input['jenis_kelamin'] ?? 'Laki-laki');
$agama = trim($input['agama'] ?? 'Islam');
$statusPernikahan = trim($input['status_pernikahan'] ?? 'Belum Menikah');
$noHp = trim($input['no_hp'] ?? '');
$alamat = trim($input['alamat'] ?? '');

if (empty($nik) || empty($nama) || empty($email) || !$deptId || !$jabatanId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Semua kolom bertanda bintang (*) seperti NIK, Nama, Email, Departemen, dan Jabatan wajib diisi!'
    ]);
    exit;
}

$pdo = getDbConnection();

// Check unique NIK and Email
$stmtCheck = $pdo->prepare("SELECT id FROM karyawan WHERE nik = ? OR email = ?");
$stmtCheck->execute([$nik, $email]);
if ($stmtCheck->fetch()) {
    http_response_code(409);
    echo json_encode([
        'success' => false,
        'message' => 'NIK atau Email sudah terdaftar di sistem! Silakan gunakan NIK/Email lain.'
    ]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("
        INSERT INTO karyawan (
            nik, nama_lengkap, email, password, role,
            departemen_id, jabatan_id, tanggal_masuk,
            kuota_cuti, cuti_terpakai, sisa_cuti,
            jenis_kelamin, agama, status_pernikahan,
            no_hp, alamat, status_aktif, created_at
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, 0, ?,
            ?, ?, ?,
            ?, ?, 'Aktif', NOW()
        )
    ");
    $stmt->execute([
        $nik, $nama, $email, $hashedPassword, $role,
        $deptId, $jabatanId, $tanggalMasuk,
        $kuotaCuti, $kuotaCuti,
        $jenisKelamin, $agama, $statusPernikahan,
        $noHp, $alamat
    ]);

    $newEmpId = $pdo->lastInsertId();

    // Log initial quota
    $stmtLog = $pdo->prepare("
        INSERT INTO riwayat_kuota_cuti (
            employee_id, kuota_sebelum, perubahan, kuota_sesudah, 
            tipe, keterangan, created_by, created_at
        ) VALUES (?, 0, ?, ?, 'alokasi_tahunan', 'Alokasi Jatah Cuti Awal Masuk Kerja (Mobile App)', ?, NOW())
    ");
    $stmtLog->execute([$newEmpId, $kuotaCuti, $kuotaCuti, $currentUser['id']]);

    echo json_encode([
        'success' => true,
        'message' => "Karyawan {$nama} ({$nik}) berhasil didaftarkan!",
        'data' => [
            'id' => $newEmpId,
            'nik' => $nik,
            'nama_lengkap' => $nama
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan data karyawan: ' . $e->getMessage()
    ]);
}
