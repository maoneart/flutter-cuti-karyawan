<?php
/**
 * API Delete Employee (For HRD / Super Admin)
 * POST /api/employees/delete.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$currentUser = authenticateApiUser();
requireApiRole(['superadmin', 'admin', 'hrd'], $currentUser);

$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?? $_POST;

$id = (int)($input['id'] ?? 0);

if (!$id) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID karyawan wajib disertakan!'
    ]);
    exit;
}

if ($id === (int)$currentUser['id']) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Anda tidak dapat menghapus akun Anda sendiri!'
    ]);
    exit;
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
    $stmt = $pdo->prepare("DELETE FROM karyawan WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => "Data karyawan {$emp['nama_lengkap']} ({$emp['nik']}) berhasil dihapus!"
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menghapus data karyawan. Karyawan ini mungkin memiliki riwayat pengajuan cuti aktif.'
    ]);
}
