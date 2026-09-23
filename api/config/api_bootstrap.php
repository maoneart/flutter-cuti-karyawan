<?php
/**
 * API Bootstrap & Security Configuration
 * PT. Nakakin Indonesia Leave Management System API
 */

// Set Response Type to JSON
header('Content-Type: application/json; charset=UTF-8');

// CORS Headers for Mobile (Flutter Android/iOS) and Web Browser
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Max-Age: 86400');

// Handle Preflight Request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Global Exception Handler to always return JSON (prevent leakage of stack traces)
set_exception_handler(function ($e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server.',
        'error_detail' => (defined('API_DEBUG') && API_DEBUG) ? $e->getMessage() : null
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
});

// Load Database Config
require_once __DIR__ . '/../../config/database.php';

// Security Secret Key for API Token (HMAC-SHA256)
define('API_SECRET_KEY', 'nakakin_secure_jwt_token_key_2026_@#$%&!_leave_system');
define('TOKEN_EXPIRATION_SECONDS', 86400 * 30); // 30 Hari

/**
 * Standard JSON Response Output
 */
function jsonResponse($success, $message, $data = null, $statusCode = 200, $errors = null) {
    http_response_code($statusCode);
    $response = [
        'success' => (bool)$success,
        'message' => (string)$message,
        'timestamp' => date('Y-m-d H:i:s'),
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    if ($errors !== null) {
        $response['errors'] = $errors;
    }

    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Sanitize API Input
 */
function cleanApiInput($data) {
    if (is_array($data)) {
        return array_map('cleanApiInput', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Get JSON Body or POST data
 */
function getApiRequestData() {
    $rawInput = file_get_contents('php://input');
    $jsonData = json_decode($rawInput, true);
    
    if (is_array($jsonData)) {
        return cleanApiInput($jsonData);
    }
    
    return cleanApiInput($_POST);
}

/**
 * Generate Secure HMAC Token
 */
function generateApiToken(array $payload) {
    $header = ['typ' => 'JWT', 'alg' => 'HS256'];
    $payload['iat'] = time();
    $payload['exp'] = time() + TOKEN_EXPIRATION_SECONDS;
    
    $base64Header = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
    $base64Payload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
    
    $signature = hash_hmac('sha256', "$base64Header.$base64Payload", API_SECRET_KEY, true);
    $base64Signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    
    return "$base64Header.$base64Payload.$base64Signature";
}

/**
 * Verify HMAC Token
 */
function verifyApiToken($token) {
    if (empty($token)) {
        return false;
    }
    
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false;
    }
    
    [$base64Header, $base64Payload, $base64Signature] = $parts;
    
    $expectedSignature = hash_hmac('sha256', "$base64Header.$base64Payload", API_SECRET_KEY, true);
    $expectedBase64Signature = rtrim(strtr(base64_encode($expectedSignature), '+/', '-_'), '=');
    
    if (!hash_equals($expectedBase64Signature, $base64Signature)) {
        return false;
    }
    
    $payloadJson = base64_decode(strtr($base64Payload, '-_', '+/'));
    $payload = json_decode($payloadJson, true);
    
    if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) {
        return false; // Token Expired
    }
    
    return $payload;
}
