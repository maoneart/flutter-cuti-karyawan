<?php
/**
 * API Quota Change History / Audit Logs
 * GET /api/quotas/logs.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$user = authenticateApiUser();
$pdo = getDbConnection();

$hierarki = (int)($user['level_hierarki'] ?? 1);
$isHRDOrAdmin = ($user['role'] === 'superadmin' || $user['role'] === 'admin' || $user['role'] === 'hrd' || $hierarki >= 7);

if (!$isHRDOrAdmin) {
    jsonResponse(false, 'Akses ditolak. Riwayat mutasi kuota hanya dapat diakses oleh HRD dan Administrator.', null, 403);
}

$employeeId = (int)($_GET['employee_id'] ?? 0);
$limit = min(100, max(10, (int)($_GET['limit'] ?? 50)));

$conditions = [];
$params = [];

if ($employeeId > 0) {
    $conditions[] = "qh.employee_id = ?";
    $params[] = $employeeId;
}

$whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

$sql = "
    SELECT 
        qh.id, qh.employee_id, qh.kuota_sebelum, qh.perubahan, qh.kuota_sesudah,
        qh.tipe, qh.keterangan, qh.created_at,
        e.nama_lengkap, e.nik, e.foto as employee_foto,
        d.nama_dept,
        p.nama_jabatan,
        admin.nama_lengkap as nama_admin, admin.nik as nik_admin
    FROM riwayat_kuota_cuti qh
    JOIN karyawan e ON qh.employee_id = e.id
    JOIN departemen d ON e.departemen_id = d.id
    JOIN jabatan p ON e.jabatan_id = p.id
    LEFT JOIN karyawan admin ON qh.created_by = admin.id
    $whereClause
    ORDER BY qh.created_at DESC
    LIMIT $limit
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

foreach ($logs as &$log) {
    $log['id'] = (int)$log['id'];
    $log['employee_id'] = (int)$log['employee_id'];
    $log['kuota_sebelum'] = (int)$log['kuota_sebelum'];
    $log['perubahan'] = (int)$log['perubahan'];
    $log['kuota_sesudah'] = (int)$log['kuota_sesudah'];
}

jsonResponse(true, 'Riwayat mutasi kuota cuti berhasil dimuat', $logs);
