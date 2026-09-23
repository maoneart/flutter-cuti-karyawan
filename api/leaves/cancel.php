<?php
/**
 * API Cancel Leave Request
 * POST /api/leaves/cancel.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Metode HTTP tidak diizinkan. Gunakan POST.', null, 405);
}

$user = authenticateApiUser();
$pdo = getDbConnection();

$input = getApiRequestData();
$leaveId = (int)($input['id'] ?? 0);

if ($leaveId <= 0) {
    jsonResponse(false, 'ID pengajuan tidak valid.', null, 400);
}

$stmt = $pdo->prepare("SELECT * FROM pengajuan_cuti WHERE id = ?");
$stmt->execute([$leaveId]);
$leave = $stmt->fetch();

if (!$leave) {
    jsonResponse(false, 'Pengajuan cuti tidak ditemukan.', null, 404);
}

if ($leave['employee_id'] != $user['id'] && $user['role'] !== 'admin') {
    jsonResponse(false, 'Akses ditolak. Anda hanya dapat membatalkan pengajuan milik Anda sendiri.', null, 403);
}

if ($leave['status'] !== 'pending') {
    jsonResponse(false, "Pengajuan cuti tidak dapat dibatalkan karena statusnya sudah '{$leave['status']}'.", null, 400);
}

$stmtUpdate = $pdo->prepare("
    UPDATE pengajuan_cuti 
    SET status = 'cancelled', updated_at = NOW() 
    WHERE id = ?
");
$stmtUpdate->execute([$leaveId]);

jsonResponse(true, 'Pengajuan cuti berhasil dibatalkan.', [
    'id' => $leaveId,
    'status' => 'cancelled'
]);
