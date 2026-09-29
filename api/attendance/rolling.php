<?php
/**
 * API Weekly Rolling Shift for Department (Shift 1 <-> Shift 2 Maju)
 * POST /api/attendance/rolling.php
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
    jsonResponse(false, 'Akses ditolak! Anda tidak memiliki wewenang rolling shift.', null, 403);
}

$input = getApiRequestData();
$deptId = $isLeaderOrSpv ? (int)$user['departemen_id'] : (int)($input['dept_id'] ?? $user['departemen_id']);

try {
    $stmt = $pdo->prepare("
        UPDATE karyawan 
        SET current_shift = CASE 
            WHEN current_shift LIKE '%Shift 1%' THEN 'Shift 2 (Malam)'
            ELSE 'Shift 1 (Pagi)'
        END
        WHERE departemen_id = ?
    ");
    $stmt->execute([$deptId]);
    $affected = $stmt->rowCount();

    jsonResponse(true, "Rolling Shift mingguan berhasil dilakukan untuk $affected karyawan di departemen ini.", [
        'affected_rows' => $affected
    ]);
} catch (PDOException $e) {
    jsonResponse(false, 'Gagal melakukan rolling shift: ' . $e->getMessage(), null, 500);
}
