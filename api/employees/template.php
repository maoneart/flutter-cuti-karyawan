<?php
/**
 * API Download Employee Excel/CSV Template
 * GET /api/employees/template.php?format=xlsx|csv
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/excel_helper.php';

$format = isset($_GET['format']) ? strtolower(trim($_GET['format'])) : 'xlsx';

if ($format === 'csv') {
    $filename = 'template_import_karyawan_nakakin.csv';
    $staticPath = __DIR__ . '/../../assets/templates/' . $filename;
    if (file_exists($staticPath)) {
        $content = file_get_contents($staticPath);
    } else {
        $content = ExcelHelper::generateTemplateCsv();
    }
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($content));
    header('Pragma: no-cache');
    header('Expires: 0');
    echo $content;
} else {
    $filename = 'template_import_karyawan_nakakin.xlsx';
    $staticPath = __DIR__ . '/../../assets/templates/' . $filename;
    if (file_exists($staticPath)) {
        $content = file_get_contents($staticPath);
    } else {
        $content = ExcelHelper::generateXlsxTemplate();
    }
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($content));
    header('Pragma: no-cache');
    header('Expires: 0');
    echo $content;
}
exit;

