<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
require_once __DIR__ . '/../api/config/api_bootstrap.php';
require_once __DIR__ . '/../api/middleware/auth_middleware.php';

$pdo = getDbConnection();

// Let's generate token for Leader Core (id 14)
$user = $pdo->query("
    SELECT k.*, d.nama_dept, d.kode_dept, j.nama_jabatan, j.level_hierarki
    FROM karyawan k
    JOIN departemen d ON k.departemen_id = d.id
    JOIN jabatan j ON k.jabatan_id = j.id
    WHERE k.email = 'ldr.core@nakakin.co.id'
")->fetch(PDO::FETCH_ASSOC);

echo "Leader Core: " . json_encode($user) . "\n\n";

// Let's test detail query on leave #3
$id = 3;
$stmt = $pdo->prepare("
    SELECT 
        p.*, 
        k.nama_lengkap, k.nik, k.email, k.no_hp, k.foto as employee_foto,
        d.nama_dept, d.kode_dept,
        j.nama_jabatan, j.level_hierarki as employee_level,
        l.nama_cuti, l.kode as kode_cuti, l.potong_kuota, l.butuh_lampiran, l.deskripsi as deskripsi_cuti,
        spv.nama_lengkap as spv_name, spv.nik as spv_nik,
        mgr.nama_lengkap as manager_name, mgr.nik as manager_nik,
        hrd.nama_lengkap as hrd_name, hrd.nik as hrd_nik,
        appr.nama_lengkap as approver_name, appr.nik as approver_nik
    FROM pengajuan_cuti p
    JOIN karyawan k ON p.employee_id = k.id
    JOIN departemen d ON k.departemen_id = d.id
    JOIN jabatan j ON k.jabatan_id = j.id
    JOIN jenis_cuti l ON p.leave_type_id = l.id
    LEFT JOIN karyawan spv ON p.spv_id = spv.id
    LEFT JOIN karyawan mgr ON p.manager_id = mgr.id
    LEFT JOIN karyawan hrd ON p.hrd_id = hrd.id
    LEFT JOIN karyawan appr ON p.approved_by = appr.id
    WHERE p.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$leave = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Leave query result:\n" . json_encode($leave, JSON_PRETTY_PRINT) . "\n\n";

$hierarki = (int)($user['level_hierarki'] ?? 1);
$canAccess = ($leave['employee_id'] == $user['id']) || 
             ($user['role'] === 'superadmin' || $user['role'] === 'admin' || $user['role'] === 'hrd' || $hierarki >= 5) || 
             ($user['departemen_id'] == $leave['departemen_id']);

echo "Can access? " . ($canAccess ? "YES" : "NO") . "\n";
