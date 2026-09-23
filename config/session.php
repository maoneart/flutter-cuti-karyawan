<?php
/**
 * Session & Authentication Middleware
 * PT. Nakakin Indonesia Leave Management System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Require user to be logged in
function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('warning', 'Silakan login terlebih dahulu untuk mengakses sistem.');
        header('Location: ' . BASE_URL . '/index.php?page=login');
        exit;
    }
}

// Require specific roles
function requireRole($roles = []) {
    requireLogin();
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    
    $userRole = $_SESSION['user_role'] ?? '';
    if (!in_array($userRole, $roles)) {
        setFlash('error', 'Akses ditolak! Anda tidak memiliki izin untuk membuka halaman ini.');
        header('Location: ' . BASE_URL . '/index.php?page=dashboard');
        exit;
    }
}

// Get Current Logged In Employee Data fresh from DB
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        SELECT e.*, d.nama_dept, d.kode_dept, p.nama_jabatan, p.level_hierarki 
        FROM karyawan e
        JOIN departemen d ON e.departemen_id = d.id
        JOIN jabatan p ON e.jabatan_id = p.id
        WHERE e.id = ? AND e.status_aktif = 'Aktif'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
