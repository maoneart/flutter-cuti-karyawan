<?php
/**
 * Auth Middleware
 * Verifies Bearer Token and User Status
 */

require_once __DIR__ . '/../config/api_bootstrap.php';

/**
 * Get Authorization Header from Request
 */
function getAuthorizationHeader() {
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

/**
 * Extract Bearer Token
 */
function getBearerToken() {
    $headers = getAuthorizationHeader();
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/i', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

/**
 * Authenticate API User
 * Returns user data array or dies with 401 JSON
 */
function authenticateApiUser() {
    $token = getBearerToken();
    if (!$token) {
        jsonResponse(false, 'Akses ditolak. Token otentikasi tidak ditemukan.', null, 401);
    }

    $payload = verifyApiToken($token);
    if (!$payload || !isset($payload['user_id'])) {
        jsonResponse(false, 'Sesi tidak valid atau telah kedaluwarsa. Silakan login kembali.', null, 401);
    }

    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        SELECT e.*, d.nama_dept, d.kode_dept, j.nama_jabatan, j.level_hierarki
        FROM karyawan e
        JOIN departemen d ON e.departemen_id = d.id
        JOIN jabatan j ON e.jabatan_id = j.id
        WHERE e.id = ? AND e.status_aktif = 'Aktif'
        LIMIT 1
    ");
    $stmt->execute([$payload['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(false, 'Pengguna tidak ditemukan atau akun sedang dinonaktifkan.', null, 401);
    }

    // Hide password hash
    unset($user['password']);

    return $user;
}

/**
 * Check User Role Requirement
 */
function requireApiRole(array $allowedRoles, $user) {
    // If superadmin or admin, automatically allow any admin-level or atasan-level action
    $role = $user['role'];
    $hierarki = (int)($user['level_hierarki'] ?? 1);

    if ($role === 'superadmin' || $role === 'admin' || $role === 'hrd' || $hierarki >= 7) {
        if (in_array('admin', $allowedRoles, true) || in_array('hrd', $allowedRoles, true) || in_array('atasan', $allowedRoles, true) || in_array('manager', $allowedRoles, true) || in_array('leader', $allowedRoles, true)) {
            return;
        }
    }

    if (in_array('atasan', $allowedRoles, true)) {
        if ($role === 'manager' || $role === 'leader' || $role === 'supervisor' || $hierarki >= 3) {
            return;
        }
    }

    if (!in_array($role, $allowedRoles, true)) {
        jsonResponse(false, 'Akses ditolak. Anda tidak memiliki izin untuk tindakan ini.', null, 403);
    }
}
