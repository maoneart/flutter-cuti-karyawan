<?php
require_once __DIR__ . '/../api/config/api_bootstrap.php';

$pdo = getDbConnection();

echo "=== ALL USERS IN DB ===\n";
$users = $pdo->query("
    SELECT k.id, k.nama_lengkap, k.email, k.role, k.departemen_id, d.nama_dept, j.nama_jabatan, j.level_hierarki 
    FROM karyawan k 
    JOIN departemen d ON k.departemen_id=d.id 
    JOIN jabatan j ON k.jabatan_id=j.id 
    ORDER BY k.departemen_id ASC, k.id ASC
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $u) {
    echo "ID: {$u['id']} | {$u['nama_lengkap']} | Email: '{$u['email']}' | Role: '{$u['role']}' | Dept: {$u['nama_dept']} (ID {$u['departemen_id']}) | Level: {$u['level_hierarki']}\n";
}

echo "\n=== 2. CHECK LEAVES ===\n";
$leaves = $pdo->query("
    SELECT p.id, p.nomor_surat, p.employee_id, k.nama_lengkap, k.email, d.nama_dept, p.status, p.approval_step, p.created_at
    FROM pengajuan_cuti p
    JOIN karyawan k ON p.employee_id = k.id
    JOIN departemen d ON k.departemen_id = d.id
    ORDER BY p.id ASC
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($leaves as $l) {
    echo "Leave #{$l['id']} | No: {$l['nomor_surat']} | By: {$l['nama_lengkap']} ({$l['nama_dept']}) | Status: {$l['status']} | Step: {$l['approval_step']}\n";
}
