<?php
require_once __DIR__ . '/../api/config/api_bootstrap.php';
$pdo = getDbConnection();

$roles = $pdo->query('SELECT DISTINCT role FROM karyawan ORDER BY role ASC')->fetchAll(PDO::FETCH_COLUMN);
$jabs = $pdo->query('SELECT id, nama_jabatan, level_hierarki FROM jabatan ORDER BY level_hierarki DESC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
$depts = $pdo->query('SELECT id, nama_dept FROM departemen ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);

echo "ROLES:\n";
foreach ($roles as $r) echo "- $r\n";

echo "\nJABATAN (LEVELS):\n";
foreach ($jabs as $j) echo "- ID {$j['id']}: {$j['nama_jabatan']} (Level {$j['level_hierarki']})\n";

echo "\nDEPARTEMEN (TOTAL " . count($depts) . "):\n";
foreach ($depts as $d) echo "- ID {$d['id']}: {$d['nama_dept']}\n";
