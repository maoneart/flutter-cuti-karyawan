<?php
/**
 * API Update Employee Shift
 * POST /api/attendance/shift.php
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
    jsonResponse(false, 'Akses ditolak! Anda tidak memiliki wewenang mengubah shift.', null, 403);
}

$input = getApiRequestData();
$employeeId = (int)($input['employee_id'] ?? 0);
$newShift = trim($input['shift'] ?? 'Shift 1');

if (stripos($newShift, 'Shift 2') !== false) {
    $newShift = 'Shift 2 (Malam)';
} else {
    $newShift = 'Shift 1 (Pagi)';
}

if ($employeeId <= 0) {
    jsonResponse(false, 'Karyawan tidak valid.', null, 422);
}

// Check employee and department restriction
$stmtEmp = $pdo->prepare("SELECT id, nama_lengkap, departemen_id FROM karyawan WHERE id = ?");
$stmtEmp->execute([$employeeId]);
$emp = $stmtEmp->fetch();

if (!$emp) {
    jsonResponse(false, 'Karyawan tidak ditemukan.', null, 404);
}

if ($isLeaderOrSpv && (int)$user['departemen_id'] !== (int)$emp['departemen_id']) {
    jsonResponse(false, 'Akses ditolak! Anda hanya berwenang mengatur shift karyawan di bagian Anda.', null, 403);
}

$stmtUpdate = $pdo->prepare("UPDATE karyawan SET current_shift = ? WHERE id = ?");
$stmtUpdate->execute([$newShift, $employeeId]);

jsonResponse(true, "Shift untuk {$emp['nama_lengkap']} berhasil diubah menjadi $newShift.", [
    'employee_id' => $employeeId,
    'current_shift' => $newShift
]);
