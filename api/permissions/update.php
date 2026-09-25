<?php
/**
 * API: Update Permission Toggle
 * POST /api/permissions/update.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';
require_once __DIR__ . '/../helpers/permission_helper.php';

$user = authenticateApiUser();

if (!in_array($user['role'], ['hrd', 'superadmin', 'admin'])) {
    jsonResponse(false, 'Akses ditolak. Hanya HRD atau Super Admin yang dapat mengubah hak akses.', null, 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Metode HTTP tidak diizinkan. Gunakan POST.', null, 405);
}

$input = getApiRequestData();
$role = trim($input['role'] ?? '');
$permissionKey = trim($input['permission_key'] ?? '');
$isGranted = isset($input['is_granted']) ? (bool)$input['is_granted'] : false;

if (empty($role) || empty($permissionKey)) {
    jsonResponse(false, 'Role dan Permission Key wajib disertakan.', null, 422);
}

// Safety check: Do not allow disabling superadmin access
if ($role === 'superadmin') {
    jsonResponse(false, 'Hak akses Super Admin tidak dapat dinonaktifkan demi integritas sistem.', null, 400);
}

$pdo = getDbConnection();
$success = Permission::toggle($role, $permissionKey, $isGranted, $pdo);

if ($success) {
    jsonResponse(true, "Hak akses '{$permissionKey}' untuk role '{$role}' berhasil diperbarui.", [
        'role' => $role,
        'permission_key' => $permissionKey,
        'is_granted' => $isGranted
    ]);
} else {
    jsonResponse(false, 'Gagal memperbarui hak akses ke database.', null, 500);
}
