<?php
/**
 * API Update Profile Endpoint (Employee Self-Service)
 * POST /api/auth/update_profile.php
 * PT. Nakakin Indonesia Leave Management System
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Metode HTTP tidak diizinkan. Gunakan POST.', null, 405);
}

$user = authenticateApiUser();
$pdo = getDbConnection();
$input = getApiRequestData();

$namaLengkap = trim($input['nama_lengkap'] ?? '');
$email = trim($input['email'] ?? '');
$noHp = trim($input['no_hp'] ?? $input['no_telepon'] ?? $input['no_wa'] ?? '');
$alamat = trim($input['alamat'] ?? '');

$errors = [];
if (empty($namaLengkap)) {
    $errors['nama_lengkap'] = 'Nama lengkap wajib diisi.';
}
if (empty($email)) {
    $errors['email'] = 'Alamat email wajib diisi.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Format email tidak valid.';
} else {
    // Check if email is used by another employee
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM karyawan WHERE email = ? AND id != ?");
    $stmtCheck->execute([$email, $user['id']]);
    if ($stmtCheck->fetchColumn() > 0) {
        $errors['email'] = 'Alamat email sudah digunakan oleh karyawan lain.';
    }
}

if (!empty($errors)) {
    jsonResponse(false, 'Validasi gagal. Silakan periksa input Anda.', null, 422, $errors);
}

// Update editable profile columns
$stmt = $pdo->prepare("
    UPDATE karyawan 
    SET nama_lengkap = ?, email = ?, no_hp = ?, alamat = ?, updated_at = NOW() 
    WHERE id = ?
");
$stmt->execute([$namaLengkap, $email, $noHp, $alamat, $user['id']]);

// Fetch updated user record
$stmtUser = $pdo->prepare("
    SELECT k.*, d.nama_dept, d.kode_dept, j.nama_jabatan 
    FROM karyawan k
    LEFT JOIN departemen d ON k.departemen_id = d.id
    LEFT JOIN jabatan j ON k.jabatan_id = j.id
    WHERE k.id = ?
");
$stmtUser->execute([$user['id']]);
$updatedUser = $stmtUser->fetch();
unset($updatedUser['password']);

// If logged-in web session exists, sync session
if (session_status() === PHP_SESSION_ACTIVE || isset($_SESSION['user_id'])) {
    $_SESSION['user_name'] = $namaLengkap;
    if (isset($_SESSION['user'])) {
        $_SESSION['user']['nama_lengkap'] = $namaLengkap;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['no_hp'] = $noHp;
        $_SESSION['user']['alamat'] = $alamat;
    }
}

jsonResponse(true, 'Data profil Anda berhasil diperbarui!', [
    'user' => $updatedUser
]);
