<?php
/**
 * API Update Employee Data (For HRD / Super Admin)
 * POST /api/employees/update.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$currentUser = authenticateApiUser();
requireApiRole(['superadmin', 'admin', 'hrd'], $currentUser);

$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?? $_POST;

$id = (int)($input['id'] ?? 0);
$nik = trim($input['nik'] ?? '');
$nama = trim($input['nama_lengkap'] ?? '');
$email = trim($input['email'] ?? '');
$role = trim($input['role'] ?? 'operator');
$deptId = (int)($input['departemen_id'] ?? 0);
$jabatanId = (int)($input['jabatan_id'] ?? 0);
$tanggalMasuk = trim($input['tanggal_masuk'] ?? date('Y-m-d'));
$kuotaCuti = isset($input['kuota_cuti']) ? (int)$input['kuota_cuti'] : 12;
$sisaCuti = isset($input['sisa_cuti']) ? (int)$input['sisa_cuti'] : $kuotaCuti;
$jenisKelamin = trim($input['jenis_kelamin'] ?? 'Laki-laki');
$agama = trim($input['agama'] ?? 'Islam');
$statusPernikahan = trim($input['status_pernikahan'] ?? 'Belum Menikah');
$noHp = trim($input['no_hp'] ?? '');
$alamat = trim($input['alamat'] ?? '');
$statusAktif = trim($input['status_aktif'] ?? 'Aktif');
$newPassword = trim($input['new_password'] ?? '');

if (!$id || empty($nik) || empty($nama) || empty($email) || !$deptId || !$jabatanId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Semua kolom bertanda bintang (*) seperti NIK, Nama, Email, Departemen, dan Jabatan wajib diisi!'
    ]);
    exit;
}

$pdo = getDbConnection();

// Check if employee exists
$stmtEmp = $pdo->prepare("SELECT * FROM karyawan WHERE id = ?");
$stmtEmp->execute([$id]);
$emp = $stmtEmp->fetch(PDO::FETCH_ASSOC);

if (!$emp) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Data karyawan tidak ditemukan!'
    ]);
    exit;
}

// Check unique NIK and Email (excluding current employee)
$stmtCheck = $pdo->prepare("SELECT id FROM karyawan WHERE (nik = ? OR email = ?) AND id != ?");
$stmtCheck->execute([$nik, $email, $id]);
if ($stmtCheck->fetch()) {
    http_response_code(409);
    echo json_encode([
        'success' => false,
        'message' => 'NIK atau Email sudah digunakan karyawan lain!'
    ]);
    exit;
}

try {
    if (!empty($newPassword)) {
        $hashedPass = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmtUpdate = $pdo->prepare("
            UPDATE karyawan SET
                nik = ?, nama_lengkap = ?, email = ?, password = ?, role = ?,
                departemen_id = ?, jabatan_id = ?, tanggal_masuk = ?,
                kuota_cuti = ?, sisa_cuti = ?,
                jenis_kelamin = ?, agama = ?, status_pernikahan = ?,
                no_hp = ?, alamat = ?, status_aktif = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmtUpdate->execute([
            $nik, $nama, $email, $hashedPass, $role,
            $deptId, $jabatanId, $tanggalMasuk,
            $kuotaCuti, $sisaCuti,
            $jenisKelamin, $agama, $statusPernikahan,
            $noHp, $alamat, $statusAktif, $id
        ]);
    } else {
        $stmtUpdate = $pdo->prepare("
            UPDATE karyawan SET
                nik = ?, nama_lengkap = ?, email = ?, role = ?,
                departemen_id = ?, jabatan_id = ?, tanggal_masuk = ?,
                kuota_cuti = ?, sisa_cuti = ?,
                jenis_kelamin = ?, agama = ?, status_pernikahan = ?,
                no_hp = ?, alamat = ?, status_aktif = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmtUpdate->execute([
            $nik, $nama, $email, $role,
            $deptId, $jabatanId, $tanggalMasuk,
            $kuotaCuti, $sisaCuti,
            $jenisKelamin, $agama, $statusPernikahan,
            $noHp, $alamat, $statusAktif, $id
        ]);
    }

    echo json_encode([
        'success' => true,
        'message' => "Data karyawan {$nama} ({$nik}) berhasil diperbarui!"
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan basis data: ' . $e->getMessage()
    ]);
}
