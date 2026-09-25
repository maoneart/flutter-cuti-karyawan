<?php
/**
 * API: Get Permissions List & Matrix
 * GET /api/permissions/list.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';
require_once __DIR__ . '/../helpers/permission_helper.php';

$user = authenticateApiUser();
$pdo = getDbConnection();

$role = strtolower($user['role'] ?? 'operator');
$hierarki = (int)($user['level_hierarki'] ?? 1);
$userPerms = Permission::getByRole($role, $pdo);

// Strict Super User check (Super Admin only, HRD role explicitly excluded)
$isSuperAdmin = ($role !== 'hrd') && (in_array($role, ['superadmin', 'admin'], true) || $hierarki >= 8);

$response = [
    'current_user_role' => $role,
    'current_user_level' => $hierarki,
    'is_superadmin' => $isSuperAdmin,
    'my_permissions' => $userPerms['map'],
    'my_permissions_list' => $userPerms['list']
];

// Matrix is strictly restricted to Super User only (just like webbase SettingController / settings/index.php)
if ($isSuperAdmin) {
    $matrix = Permission::getAllMatrix($pdo);
    $grouped = [];
    foreach ($matrix as $row) {
        $grouped[$row['permission_key']]['name'] = $row['permission_name'];
        $grouped[$row['permission_key']]['category'] = $row['category'];
        $grouped[$row['permission_key']]['description'] = $row['description'];
        $grouped[$row['permission_key']]['roles'][$row['role']] = (int)$row['is_granted'] === 1;
    }
    $response['matrix'] = $grouped;
    $response['raw_matrix'] = $matrix;
}

jsonResponse(true, 'Data hak akses dan privilege berhasil diambil', $response);
