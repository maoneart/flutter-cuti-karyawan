<?php
/**
 * API Reset Employee Password (For HRD / Super Admin)
 * POST /api/employees/reset_password.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$currentUser = authenticateApiUser();
requireApiRole(['superadmin', 'admin', 'hrd'], $currentUser);

$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?? $_POST;

$id = (int)($input['id'] ?? 0);
$newPassword = trim($input['new_password'] ?? 'password123');

if (!$id) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID karyawan wajib disertakan!'
    ]);
    exit;
}

if (empty($newPassword)) {
    $newPassword = 'password123';
}

$pdo = getDbConnection();

$stmtEmp = $pdo->prepare("SELECT id, nama_lengkap, nik FROM karyawan WHERE id = ?");
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

try {
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE karyawan SET password = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$hashedPassword, $id]);

    echo json_encode([
        'success' => true,
        'message' => "Password akun karyawan {$emp['nama_lengkap']} ({$emp['nik']}) berhasil direset!"
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan basis data: ' . $e->getMessage()
    ]);
}
