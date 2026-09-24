<?php
/**
 * API Kelola Jatah Kuota Cuti Karyawan
 * GET /api/quotas/list.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$user = authenticateApiUser();
$pdo = getDbConnection();

$hierarki = (int)($user['level_hierarki'] ?? 1);
$isHRDOrAdmin = ($user['role'] === 'superadmin' || $user['role'] === 'admin' || $user['role'] === 'hrd' || $hierarki >= 7);

if (!$isHRDOrAdmin) {
    jsonResponse(false, 'Akses ditolak. Fitur Kelola Jatah Cuti hanya dapat diakses oleh HRD dan Administrator.', null, 403);
}

$deptId = (int)($_GET['dept_id'] ?? 0);
$search = trim($_GET['search'] ?? '');

$conditions = ["e.status_aktif = 'Aktif'"];
$params = [];

if ($deptId > 0) {
    $conditions[] = "e.departemen_id = ?";
    $params[] = $deptId;
}

if (!empty($search)) {
    $conditions[] = "(e.nama_lengkap LIKE ? OR e.nik LIKE ? OR d.nama_dept LIKE ? OR p.nama_jabatan LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = 'WHERE ' . implode(' AND ', $conditions);

$sql = "
    SELECT 
        e.id, e.nik, e.nama_lengkap, e.email, e.no_hp, e.foto, e.tanggal_masuk,
        e.kuota_cuti, e.cuti_terpakai, e.sisa_cuti, e.status_aktif,
        e.departemen_id, d.nama_dept, d.kode_dept,
        e.jabatan_id, p.nama_jabatan, p.level_hierarki
    FROM karyawan e
    JOIN departemen d ON e.departemen_id = d.id
    JOIN jabatan p ON e.jabatan_id = p.id
    $whereClause
    ORDER BY d.nama_dept ASC, e.nama_lengkap ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$employees = $stmt->fetchAll();

// Format numbers
foreach ($employees as &$emp) {
    $emp['id'] = (int)$emp['id'];
    $emp['departemen_id'] = (int)$emp['departemen_id'];
    $emp['jabatan_id'] = (int)$emp['jabatan_id'];
    $emp['level_hierarki'] = (int)$emp['level_hierarki'];
    $emp['kuota_cuti'] = (int)$emp['kuota_cuti'];
    $emp['cuti_terpakai'] = (int)$emp['cuti_terpakai'];
    $emp['sisa_cuti'] = (int)$emp['sisa_cuti'];
}

// Calculate Summary
$stmtSummary = $pdo->query("
    SELECT 
        COUNT(*) as total_karyawan,
        SUM(kuota_cuti) as total_kuota,
        SUM(cuti_terpakai) as total_terpakai,
        SUM(sisa_cuti) as total_sisa
    FROM karyawan 
    WHERE status_aktif = 'Aktif'
");
$summary = $stmtSummary->fetch();

// Fetch 15 Departments for dropdown/filter
$departments = $pdo->query("SELECT id, nama_dept, kode_dept FROM departemen ORDER BY id ASC")->fetchAll();
foreach ($departments as &$d) {
    $d['id'] = (int)$d['id'];
}

// Fetch Positions for bulk allocation
$positions = $pdo->query("SELECT id, nama_jabatan, level_hierarki FROM jabatan ORDER BY level_hierarki ASC")->fetchAll();
foreach ($positions as &$p) {
    $p['id'] = (int)$p['id'];
    $p['level_hierarki'] = (int)$p['level_hierarki'];
}

jsonResponse(true, 'Data jatah kuota cuti berhasil dimuat', [
    'summary' => [
        'total_karyawan' => (int)($summary['total_karyawan'] ?? 0),
        'total_kuota' => (int)($summary['total_kuota'] ?? 0),
        'total_terpakai' => (int)($summary['total_terpakai'] ?? 0),
        'total_sisa' => (int)($summary['total_sisa'] ?? 0),
    ],
    'departments' => $departments,
    'positions' => $positions,
    'employees' => $employees,
]);
