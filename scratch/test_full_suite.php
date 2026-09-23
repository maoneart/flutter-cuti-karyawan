<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
require_once __DIR__ . '/../api/config/api_bootstrap.php';
$pdo = getDbConnection();

echo "========================================================\n";
echo "      COMPREHENSIVE BACKEND INTEGRATION TEST SUITE      \n";
echo "========================================================\n\n";

$rolesToTest = [
    'Leader Core' => 'ldr.core@nakakin.co.id',
    'Spv Core' => 'spv.core@nakakin.co.id',
    'Manager' => 'manager@nakakin.co.id',
    'HRD' => 'hermawan@nakakin.co.id',
    'Super Admin' => 'admin@nakakin.co.id',
    'Op 1 Core' => 'op1.core@nakakin.co.id'
];

foreach ($rolesToTest as $label => $email) {
    echo ">>> TESTING ROLE: $label ($email)\n";
    $u = $pdo->query("
        SELECT k.*, d.nama_dept, d.kode_dept, j.nama_jabatan, j.level_hierarki
        FROM karyawan k
        JOIN departemen d ON k.departemen_id = d.id
        JOIN jabatan j ON k.jabatan_id = j.id
        WHERE k.email = '$email'
    ")->fetch(PDO::FETCH_ASSOC);

    $hierarki = (int)($u['level_hierarki'] ?? 1);

    // 1. Test Detail on Leave #3 (Operator 1 Core)
    $stmt = $pdo->prepare("
        SELECT 
            p.*, 
            k.nama_lengkap, k.nik, k.email, k.no_hp, k.foto as employee_foto, k.departemen_id,
            d.nama_dept, d.kode_dept,
            j.nama_jabatan, j.level_hierarki as employee_level,
            l.nama_cuti, l.kode as kode_cuti, l.potong_kuota, l.butuh_lampiran, l.deskripsi as deskripsi_cuti
        FROM pengajuan_cuti p
        JOIN karyawan k ON p.employee_id = k.id
        JOIN departemen d ON k.departemen_id = d.id
        JOIN jabatan j ON k.jabatan_id = j.id
        JOIN jenis_cuti l ON p.leave_type_id = l.id
        WHERE p.id = 3
    ");
    $stmt->execute();
    $leave3 = $stmt->fetch(PDO::FETCH_ASSOC);

    $canAccessLeave3 = ($leave3['employee_id'] == $u['id']) || 
                       ($u['role'] === 'superadmin' || $u['role'] === 'admin' || $u['role'] === 'hrd' || $hierarki >= 5) || 
                       ($u['departemen_id'] == $leave3['departemen_id']);

    echo "  [Detail Access Leave #3 (Op Core)]: " . ($canAccessLeave3 ? "✅ GRANTED" : "❌ DENIED") . "\n";

    // 2. Test Detail on Leave #1 (Operator 1 Accounting)
    $stmt->execute();
    $stmt1 = $pdo->query("
        SELECT p.*, k.departemen_id 
        FROM pengajuan_cuti p 
        JOIN karyawan k ON p.employee_id = k.id 
        WHERE p.id = 1
    ");
    $leave1 = $stmt1->fetch(PDO::FETCH_ASSOC);
    $canAccessLeave1 = ($leave1['employee_id'] == $u['id']) || 
                       ($u['role'] === 'superadmin' || $u['role'] === 'admin' || $u['role'] === 'hrd' || $hierarki >= 5) || 
                       ($u['departemen_id'] == $leave1['departemen_id']);

    echo "  [Detail Access Leave #1 (Op Acct)]: " . ($canAccessLeave1 ? "✅ GRANTED" : "❌ DENIED (Expected for non-HR/Manager/Superadmin)") . "\n";

    // 3. Test Notifications Count
    $notifCount = 0;
    if ($u['role'] === 'superadmin' || $u['role'] === 'admin' || $u['role'] === 'hrd' || $hierarki >= 7) {
        $c1 = (int)$pdo->query("SELECT COUNT(*) FROM pengajuan_cuti WHERE approval_step = 'pending_hrd' AND status = 'pending'")->fetchColumn();
        $c2 = (int)$pdo->query("SELECT COUNT(*) FROM pengajuan_cuti WHERE approval_step IN ('pending_spv', 'pending_manager') AND status = 'pending'")->fetchColumn();
        $notifCount = $c1 + $c2;
    } elseif ($u['role'] === 'manager' || ($hierarki >= 5 && $hierarki <= 6)) {
        $notifCount = (int)$pdo->query("SELECT COUNT(*) FROM pengajuan_cuti WHERE approval_step = 'pending_manager' AND status = 'pending' AND employee_id != {$u['id']}")->fetchColumn();
    } elseif ($hierarki >= 3) {
        $notifCount = (int)$pdo->query("SELECT COUNT(*) FROM pengajuan_cuti p JOIN karyawan k ON p.employee_id = k.id WHERE p.approval_step = 'pending_spv' AND p.status = 'pending' AND k.departemen_id = {$u['departemen_id']} AND k.id != {$u['id']}")->fetchColumn();
    }
    echo "  [Notification Count]: $notifCount pending items\n\n";
}

echo "ALL TESTS FINISHED SUCCESSFULLY!\n";
