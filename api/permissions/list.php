<?php
/**
 * API: Get Permissions List & Matrix
 * GET /api/permissions/list.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';
require_once __DIR__ . '/../../models/Permission.php';

$user = authenticateApiUser();
$pdo = getDbConnection();

$role = $user['role'] ?? 'operator';
$userPerms = Permission::getByRole($role, $pdo);

$response = [
    'current_user_role' => $role,
    'my_permissions' => $userPerms['map'],
    'my_permissions_list' => $userPerms['list']
];

// If user is HRD, Manager, or Admin, include full matrix
if (in_array($role, ['hrd', 'superadmin', 'admin', 'manager'])) {
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
