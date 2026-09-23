<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
require_once __DIR__ . '/../api/config/api_bootstrap.php';
require_once __DIR__ . '/../api/middleware/auth_middleware.php';

$pdo = getDbConnection();

function testAsUser($email, $leaveId) {
    global $pdo;
    $user = $pdo->query("
        SELECT k.*, d.nama_dept, d.kode_dept, j.nama_jabatan, j.level_hierarki
        FROM karyawan k
        JOIN departemen d ON k.departemen_id = d.id
        JOIN jabatan j ON k.jabatan_id = j.id
        WHERE k.email = '$email'
    ")->fetch(PDO::FETCH_ASSOC);

    $hierarki = (int)($user['level_hierarki'] ?? 1);

    // Fetch leave
$stmt = $pdo->prepare("
    SELECT 
        p.*, 
        k.nama_lengkap, k.nik, k.email, k.no_hp, k.foto as employee_foto, k.departemen_id,
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
    $stmt->execute([$leaveId]);
    $leave = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$leave) {
        return "Leave not found";
    }

    $canAccess = ($leave['employee_id'] == $user['id']) || 
                 ($user['role'] === 'superadmin' || $user['role'] === 'admin' || $user['role'] === 'hrd' || $hierarki >= 5) || 
                 ($user['departemen_id'] == $leave['departemen_id']);

    return [
        'user' => $user['nama_lengkap'] . " (" . $user['email'] . ")",
        'user_dept' => $user['departemen_id'],
        'leave_dept' => $leave['departemen_id'],
        'canAccess' => $canAccess ? "GRANTED" : "DENIED",
        'leave_employee' => $leave['nama_lengkap'],
    ];
}

echo "=== TEST DETAIL ACCESS ===\n";
print_r(testAsUser('ldr.core@nakakin.co.id', 3)); // Core leave
print_r(testAsUser('ldr.core@nakakin.co.id', 1)); // Accounting leave (should be DENIED)
print_r(testAsUser('manager@nakakin.co.id', 3));  // GRANTED
print_r(testAsUser('hermawan@nakakin.co.id', 3)); // GRANTED
print_r(testAsUser('admin@nakakin.co.id', 3));    // GRANTED
