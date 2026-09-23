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
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo ExcelHelper::generateTemplateCsv();
} else {
    $filename = 'template_import_karyawan_nakakin.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo ExcelHelper::generateXlsxTemplate();
}
exit;

