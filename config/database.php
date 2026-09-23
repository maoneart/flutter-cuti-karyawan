<?php
/**
 * Database Configuration
 * PT. Nakakin Indonesia Leave Management System
 */

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'db_cuti_nakakin');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base URL helper
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_name = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$base_url = rtrim($protocol . $host . $script_name, '/');

// Fix base_url if inside subfolder
if (!defined('BASE_URL')) {
    // If accessing through localhost/Cuti Karyawan
    define('BASE_URL', $base_url);
}

function getDbConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("<div style='font-family:sans-serif;padding:20px;background:#ffebee;color:#c62828;border-radius:8px;margin:20px;'>
                <h3>Gagal Terhubung ke Database</h3>
                <p>Pastikan MySQL di Laragon/XAMPP sudah aktif dan database <strong>" . DB_NAME . "</strong> sudah dibuat.</p>
                <small>Error: " . htmlspecialchars($e->getMessage()) . "</small>
            </div>");
        }
    }
    return $pdo;
}
