<?php
/**
 * API Employees List
 * GET /api/employees/list.php?dept_id=...&search=...&role=...
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$user = authenticateApiUser();
$pdo = getDbConnection();

$hierarki = (int)($user['level_hierarki'] ?? 1);
$isHrdOrAdmin = ($user['role'] === 'superadmin' || $user['role'] === 'admin' || $user['role'] === 'hrd' || $hierarki >= 7);
$isManager = ($user['role'] === 'manager' || ($hierarki >= 5 && $hierarki <= 6));
$isLeaderOrSpv = ($user['role'] === 'supervisor' || $user['role'] === 'leader' || $hierarki >= 3);

$deptId = (int)($_GET['dept_id'] ?? 0);
$search = trim($_GET['search'] ?? '');
$role = trim($_GET['role'] ?? '');

$conditions = [];
$params = [];

// Access control: HRD, Admin, Manager can see all departments.
// Leader/Spv can see their department.
if (!$isHrdOrAdmin && !$isManager) {
    if ($isLeaderOrSpv) {
        $conditions[] = "k.departemen_id = ?";
        $params[] = $user['departemen_id'];
    } else {
        // Operator can only see themselves or same dept public info
        $conditions[] = "k.departemen_id = ?";
        $params[] = $user['departemen_id'];
    }
} else {
    if ($deptId > 0) {
        $conditions[] = "k.departemen_id = ?";
        $params[] = $deptId;
    }
}

if (!empty($role)) {
    $conditions[] = "k.role = ?";
    $params[] = $role;
}

if (!empty($search)) {
    $conditions[] = "(k.nama_lengkap LIKE ? OR k.nik LIKE ? OR k.email LIKE ? OR d.nama_dept LIKE ? OR j.nama_jabatan LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

$stmt = $pdo->prepare("
    SELECT 
        k.id, k.nik, k.nama_lengkap, k.email, k.role,
        k.departemen_id, d.nama_dept, d.kode_dept,
        k.jabatan_id, j.nama_jabatan, j.level_hierarki,
        k.tanggal_masuk, k.kuota_cuti, k.cuti_terpakai, k.sisa_cuti,
        k.jenis_kelamin, k.agama, k.status_pernikahan,
        k.no_hp, k.alamat, k.foto, k.status_aktif, k.created_at
    FROM karyawan k
    JOIN departemen d ON k.departemen_id = d.id
    JOIN jabatan j ON k.jabatan_id = j.id
    $whereClause
    ORDER BY d.id ASC, j.level_hierarki DESC, k.nama_lengkap ASC
");
$stmt->execute($params);
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($employees as &$emp) {
    $emp['id'] = (int)$emp['id'];
    $emp['departemen_id'] = (int)$emp['departemen_id'];
    $emp['jabatan_id'] = (int)$emp['jabatan_id'];
    $emp['level_hierarki'] = (int)$emp['level_hierarki'];
    $emp['kuota_cuti'] = (int)$emp['kuota_cuti'];
    $emp['cuti_terpakai'] = (int)$emp['cuti_terpakai'];
    $emp['sisa_cuti'] = (int)$emp['sisa_cuti'];
}
unset($emp);

jsonResponse(true, 'Daftar karyawan berhasil dimuat', $employees);
