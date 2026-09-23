<?php
require_once __DIR__ . '/../api/config/api_bootstrap.php';
$pdo = getDbConnection();

echo "=== 1. DISTINCT ROLES IN KARYAWAN ===\n";
$roles = $pdo->query('SELECT DISTINCT role FROM karyawan ORDER BY role ASC')->fetchAll(PDO::FETCH_COLUMN);
echo json_encode($roles, JSON_PRETTY_PRINT) . "\n\n";

echo "=== 2. ALL JABATAN (ID, NAMA, LEVEL HIERARKI) ===\n";
$jabs = $pdo->query('SELECT id, nama_jabatan, level_hierarki FROM jabatan ORDER BY level_hierarki DESC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($jabs, JSON_PRETTY_PRINT) . "\n\n";

echo "=== 3. ALL DEPARTEMEN (ID, NAMA DEPT) ===\n";
$depts = $pdo->query('SELECT id, nama_dept FROM departemen ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($depts, JSON_PRETTY_PRINT) . "\n\n";

echo "=== 4. SAMPLE KARYAWAN PER ROLE/JABATAN ===\n";
$karyawan = $pdo->query('
    SELECT k.id, k.nama_lengkap, k.email, k.role, j.nama_jabatan, j.level_hierarki, d.nama_dept
    FROM karyawan k
    JOIN jabatan j ON k.jabatan_id = j.id
    JOIN departemen d ON k.departemen_id = d.id
    ORDER BY j.level_hierarki DESC, k.id ASC
')->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($karyawan, JSON_PRETTY_PRINT) . "\n";
