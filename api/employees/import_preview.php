<?php
/**
 * API Employee Bulk Import Preview & Validation
 * POST /api/employees/import_preview.php (Multipart file upload)
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';
require_once __DIR__ . '/../../config/excel_helper.php';

$user = authenticateApiUser();
requireApiRole(['admin'], $user);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Metode HTTP harus POST.', null, 405);
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    jsonResponse(false, 'File Excel / CSV tidak ditemukan atau gagal diunggah.', null, 400);
}

$file = $_FILES['file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, ['xlsx', 'csv', 'txt'])) {
    jsonResponse(false, 'Format file tidak didukung. Harap gunakan file berformat .xlsx atau .csv.', null, 400);
}

try {
    $rawRows = ExcelHelper::parseFile($file['tmp_name'], $file['name']);

    if (empty($rawRows)) {
        jsonResponse(false, 'File kosong atau tidak dapat dibaca.', null, 400);
    }

    $pdo = getDbConnection();
    $validation = ExcelHelper::validateRows($rawRows, $pdo);

    jsonResponse(true, 'File berhasil diproses dan divalidasi.', [
        'file_name' => $file['name'],
        'total_rows' => $validation['total_rows'],
        'valid_count' => $validation['valid_count'],
        'invalid_count' => $validation['invalid_count'],
        'valid_rows' => $validation['valid_rows'],
        'invalid_rows' => $validation['invalid_rows']
    ]);

} catch (Exception $e) {
    jsonResponse(false, 'Terjadi kesalahan saat memproses file: ' . $e->getMessage(), null, 500);
}
