<?php
/**
 * API Employee Options (Departments & Positions & Roles)
 * GET /api/employees/options.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$user = authenticateApiUser();
requireApiRole(['admin', 'hrd', 'superadmin'], $user);

$pdo = getDbConnection();

$deptStmt = $pdo->query("SELECT id, nama_dept, kode_dept FROM departemen ORDER BY id ASC");
$departments = $deptStmt->fetchAll(PDO::FETCH_ASSOC);

$posStmt = $pdo->query("SELECT id, nama_jabatan, level_hierarki FROM jabatan ORDER BY level_hierarki ASC");
$positions = $posStmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'data' => [
        'departments' => $departments,
        'positions' => $positions,
        'roles' => [
            ['id' => 'operator', 'label' => 'Operator Produksi'],
            ['id' => 'staff', 'label' => 'Staff'],
            ['id' => 'leader', 'label' => 'Leader / Supervisor'],
            ['id' => 'manager', 'label' => 'Manager'],
            ['id' => 'hrd', 'label' => 'HRD'],
            ['id' => 'superadmin', 'label' => 'Super Admin (Sistem)'],
        ],
        'genders' => ['Laki-laki', 'Perempuan'],
        'marital_statuses' => ['Belum Menikah', 'Menikah', 'Duda', 'Janda']
    ]
]);
