<?php
/**
 * Seeder Script: Complete Dummy Data for PT. Nakakin Indonesia
 * Includes: 15 Departments, 7 Positions
 * - 1 Super Admin (System Controller)
 * - 1 HRD (HR Management & Final Leave Approval)
 * - 1 GA (General Affairs)
 * - 1 Manager (Plant / Operational Manager)
 * - In EACH of the 15 Departments: 1 Supervisor, 1 Leader, 2 Operators
 * - 15 Active Pending Leaves (1 per department) for testing approval flow
 * - 3 Approved Past Leaves with exact matching cuti_terpakai & riwayat_kuota_cuti
 */

require_once __DIR__ . '/../../config/database.php';

$pdo = getDbConnection();
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
$pdo->exec("TRUNCATE TABLE riwayat_kuota_cuti;");
$pdo->exec("TRUNCATE TABLE pengajuan_cuti;");
$pdo->exec("TRUNCATE TABLE karyawan;");
$pdo->exec("TRUNCATE TABLE departemen;");
$pdo->exec("TRUNCATE TABLE jabatan;");
$pdo->exec("TRUNCATE TABLE jenis_cuti;");

// 1. Departemen (15 Departments)
$departments = [
    1 => ['kode' => 'ACC', 'nama' => 'Accounting', 'desc' => 'Accounting & Finance Department'],
    2 => ['kode' => 'CAST', 'nama' => 'Casting', 'desc' => 'Casting & Foundry Department'],
    3 => ['kode' => 'CORE', 'nama' => 'Core', 'desc' => 'Core Production Department'],
    4 => ['kode' => 'DC', 'nama' => 'Diecasting', 'desc' => 'Die Casting Production Department'],
    5 => ['kode' => 'ENG', 'nama' => 'Engineering', 'desc' => 'Engineering & Technical Department'],
    6 => ['kode' => 'FET', 'nama' => 'Fettling', 'desc' => 'Fettling & Finishing Department'],
    7 => ['kode' => 'GA', 'nama' => 'GA', 'desc' => 'General Affairs Department'],
    8 => ['kode' => 'HRD', 'nama' => 'HRD', 'desc' => 'Human Resources Department'],
    9 => ['kode' => 'MC', 'nama' => 'Machining', 'desc' => 'Machining Production Department'],
    10 => ['kode' => 'MKT', 'nama' => 'Marketing', 'desc' => 'Sales & Marketing Department'],
    11 => ['kode' => 'MAINT', 'nama' => 'Maintenance', 'desc' => 'Machine & Utility Maintenance Department'],
    12 => ['kode' => 'PPIC', 'nama' => 'PPIC', 'desc' => 'Production Planning & Inventory Control'],
    13 => ['kode' => 'PURCH', 'nama' => 'Purchasing', 'desc' => 'Purchasing & Procurement Department'],
    14 => ['kode' => 'QC', 'nama' => 'QC', 'desc' => 'Quality Control Department'],
    15 => ['kode' => 'QCL', 'nama' => 'QC Line', 'desc' => 'Quality Control Line Inspection Department'],
];

$stmtDept = $pdo->prepare("INSERT INTO departemen (id, kode_dept, nama_dept, deskripsi, created_at) VALUES (?, ?, ?, ?, NOW())");
foreach ($departments as $id => $d) {
    $stmtDept->execute([$id, $d['kode'], $d['nama'], $d['desc']]);
}

// 2. Jabatan
$positions = [
    1 => ['nama' => 'Operator Produksi', 'level' => 1],
    2 => ['nama' => 'Staff', 'level' => 2],
    3 => ['nama' => 'Leader', 'level' => 3],
    4 => ['nama' => 'Supervisor (Spv)', 'level' => 4],
    5 => ['nama' => 'Department Manager', 'level' => 6],
    6 => ['nama' => 'HRD', 'level' => 7],
    7 => ['nama' => 'Super Admin', 'level' => 8],
];

$stmtJab = $pdo->prepare("INSERT INTO jabatan (id, nama_jabatan, level_hierarki, created_at) VALUES (?, ?, ?, NOW())");
foreach ($positions as $id => $p) {
    $stmtJab->execute([$id, $p['nama'], $p['level']]);
}

// 3. Jenis Cuti
$leaveTypes = [
    1 => ['nama' => 'Sakit Surat Dokter (Tidak Potong Gaji)', 'kode' => 'SSD', 'desc' => 'Izin sakit resmi dengan melampirkan surat keterangan dokter.', 'potong' => 0, 'max' => 14, 'lampiran' => 1],
    2 => ['nama' => 'Sakit Tanpa Surat Dokter (Potong Jatah/Gaji)', 'kode' => 'STSD', 'desc' => 'Izin sakit tanpa surat dokter.', 'potong' => 1, 'max' => 3, 'lampiran' => 0],
    3 => ['nama' => 'Cuti Tahunan (Reguler HRD)', 'kode' => 'CT', 'desc' => 'Hak cuti tahunan yang dialokasikan oleh HRD.', 'potong' => 1, 'max' => 12, 'lampiran' => 0],
    4 => ['nama' => 'Cuti Haid', 'kode' => 'CH', 'desc' => 'Cuti haid/menstruasi bagi karyawati.', 'potong' => 1, 'max' => 2, 'lampiran' => 0],
    5 => ['nama' => 'Cuti Melahirkan / Bersalin (3 Bulan)', 'kode' => 'CML', 'desc' => 'Hak cuti bersalin bagi karyawati (3 bulan / 90 hari).', 'potong' => 0, 'max' => 90, 'lampiran' => 1],
    6 => ['nama' => 'Cuti Khusus (Keluarga Meninggal)', 'kode' => 'CKH', 'desc' => 'Cuti duka cita karena anggota keluarga inti meninggal.', 'potong' => 0, 'max' => 2, 'lampiran' => 0],
    7 => ['nama' => 'Ijin Tidak Masuk (Potong Gaji)', 'kode' => 'IJN', 'desc' => 'Izin tidak masuk kerja untuk keperluan mendesak.', 'potong' => 0, 'max' => 7, 'lampiran' => 0],
];

$stmtType = $pdo->prepare("INSERT INTO jenis_cuti (id, nama_cuti, kode, deskripsi, potong_kuota, max_hari_default, butuh_lampiran, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
foreach ($leaveTypes as $id => $t) {
    $stmtType->execute([$id, $t['nama'], $t['kode'], $t['desc'], $t['potong'], $t['max'], $t['lampiran']]);
}

// 4. Karyawan (Employees)
$defaultPass = password_hash('password123', PASSWORD_BCRYPT);
$stmtEmp = $pdo->prepare("
    INSERT INTO karyawan (
        id, nik, nama_lengkap, email, password, role,
        departemen_id, jabatan_id, tanggal_masuk,
        kuota_cuti, cuti_terpakai, sisa_cuti,
        jenis_kelamin, agama, status_pernikahan,
        no_hp, alamat, status_aktif, created_at
    ) VALUES (
        ?, ?, ?, ?, ?, ?,
        ?, ?, ?,
        ?, ?, ?,
        ?, 'Islam', 'Menikah',
        ?, ?, 'Aktif', NOW()
    )
");

$empId = 1;

// 4.1 Super Admin (System Controller - 1 account)
$superAdminId = $empId++;
$stmtEmp->execute([
    $superAdminId, 'ADM-001', 'Master Super Admin', 'admin@nakakin.co.id', $defaultPass, 'superadmin',
    8, 7, '2020-01-01', 12, 0, 12, 'Laki-laki', '081100000001', 'Kantor Pusat PT. Nakakin Indonesia'
]);

// 4.2 HRD Official (1 account) -> starts clean with 0 used
$hrdId = $empId++;
$stmtEmp->execute([
    $hrdId, 'NAK-001', 'Risma (HRD)', 'hrd@nakakin.co.id', $defaultPass, 'hrd',
    8, 6, '2020-03-01', 12, 0, 12, 'Perempuan', '081234567890', 'Kawasan Industri KIIC, Karawang Barat'
]);

// 4.3 General Affairs / GA (1 account)
$gaId = $empId++;
$stmtEmp->execute([
    $gaId, 'NAK-002', 'Bambang Setyo (GA)', 'ga@nakakin.co.id', $defaultPass, 'staff',
    7, 2, '2021-02-15', 12, 0, 12, 'Laki-laki', '081234567891', 'Perum Resinda, Karawang Barat'
]);

// 4.4 Single General / Operational Manager (1 account)
$managerId = $empId++;
$stmtEmp->execute([
    $managerId, 'MGR-001', 'Ir. Hendra Wijaya (Manager)', 'manager@nakakin.co.id', $defaultPass, 'manager',
    5, 5, '2018-01-10', 12, 0, 12, 'Laki-laki', '081399990001', 'Grand Taruma, Karawang Barat'
]);

$createdOperators = [];

// 4.5 Per Department: 1 Spv, 1 Leader, 2 Operators
foreach ($departments as $deptId => $dept) {
    $kode = strtolower($dept['kode']);
    $namaDept = $dept['nama'];

    // Supervisor (1 per dept)
    $stmtEmp->execute([
        $empId++, "SPV-{$dept['kode']}", "Spv {$namaDept}", "spv.{$kode}@nakakin.co.id", $defaultPass, 'supervisor',
        $deptId, 4, '2021-01-10', 12, 0, 12, 'Laki-laki', '08140000' . str_pad($deptId, 3, '0', STR_PAD_LEFT), "Telukjambe Timur, Karawang"
    ]);

    // Leader (1 per dept)
    $stmtEmp->execute([
        $empId++, "LDR-{$dept['kode']}", "Leader {$namaDept}", "ldr.{$kode}@nakakin.co.id", $defaultPass, 'leader',
        $deptId, 3, '2022-03-15', 12, 0, 12, 'Laki-laki', '08150000' . str_pad($deptId, 3, '0', STR_PAD_LEFT), "Klari, Karawang Timur"
    ]);

    // Operator 1 (per dept)
    $op1Id = $empId++;
    $stmtEmp->execute([
        $op1Id, "OP1-{$dept['kode']}", "Operator 1 {$namaDept}", "op1.{$kode}@nakakin.co.id", $defaultPass, 'operator',
        $deptId, 1, '2023-06-01', 12, 0, 12, 'Laki-laki', '08160000' . str_pad($deptId, 3, '0', STR_PAD_LEFT), "Kosambi, Karawang"
    ]);
    $createdOperators[] = ['id' => $op1Id, 'dept' => $dept, 'name' => "Operator 1 {$namaDept}"];

    // Operator 2 (per dept)
    $stmtEmp->execute([
        $empId++, "OP2-{$dept['kode']}", "Operator 2 {$namaDept}", "op2.{$kode}@nakakin.co.id", $defaultPass, 'operator',
        $deptId, 1, '2024-01-15', 12, 0, 12, 'Perempuan', '08170000' . str_pad($deptId, 3, '0', STR_PAD_LEFT), "Cikampek, Karawang"
    ]);
}

// 5. Create 1 Active Leave Request for each department from Operator 1 (pending at pending_spv stage)
$stmtLeave = $pdo->prepare("
    INSERT INTO pengajuan_cuti (
        nomor_surat, employee_id, leave_type_id,
        tanggal_mulai, tanggal_selesai, total_hari,
        alasan, alamat_selama_cuti, kontak_darurat,
        status, approval_step, notif_read, created_at
    ) VALUES (
        ?, ?, ?,
        ?, ?, ?,
        ?, ?, ?,
        'pending', 'pending_spv', 0, NOW()
    )
");

$year = date('Y');
$month = date('m');
$seq = 1;

foreach ($createdOperators as $op) {
    $nomorSurat = sprintf("CUTI/NAK/%s/%s/%03d", $year, $month, $seq);
    $tglMulai = date('Y-m-d', strtotime("+$seq days"));
    $tglSelesai = date('Y-m-d', strtotime("+" . ($seq + 1) . " days"));

    $stmtLeave->execute([
        $nomorSurat,
        $op['id'],
        3, // Cuti Tahunan
        $tglMulai,
        $tglSelesai,
        2,
        "Keperluan keluarga mendesak dan acara syukuran di kampung halaman.",
        "Dusun Sukamaju RT 02/04, Karawang",
        "081299887766 (Keluarga)",
    ]);
    $seq++;
}

// 6. Create 1 Approved Past Leave for Operator 2 Core (with exact matching cuti_terpakai & riwayat_kuota_cuti)
// Op 2 Core has ID 16
$op2Core = $pdo->query("SELECT id, sisa_cuti, cuti_terpakai FROM karyawan WHERE email = 'op2.core@nakakin.co.id'")->fetch(PDO::FETCH_ASSOC);
if ($op2Core) {
    $op2Id = $op2Core['id'];
    $pastLeaveNomor = sprintf("CUTI/NAK/%s/%s/%03d", $year, $month, $seq++);
    
    // Insert Approved Leave (2 days)
    $stmtApprovedLeave = $pdo->prepare("
        INSERT INTO pengajuan_cuti (
            nomor_surat, employee_id, leave_type_id,
            tanggal_mulai, tanggal_selesai, total_hari,
            alasan, alamat_selama_cuti, kontak_darurat,
            status, approval_step, approved_by, approved_at,
            spv_id, spv_at, spv_notes,
            manager_id, manager_at, manager_notes,
            hrd_id, hrd_at, hrd_notes,
            notif_read, created_at, updated_at
        ) VALUES (
            ?, ?, 3,
            '2026-09-01', '2026-09-02', 2,
            'Acara keluarga dan mudik awal bulan.', 'Karawang', '0812345678',
            'approved', 'approved', ?, '2026-09-01 10:00:00',
            13, '2026-08-31 09:00:00', 'Disetujui oleh Spv',
            4, '2026-08-31 14:00:00', 'Disetujui oleh Manager',
            2, '2026-09-01 10:00:00', 'Disetujui HRD dan kuota dipotong',
            1, '2026-08-30 08:00:00', '2026-09-01 10:00:00'
        )
    ");
    $stmtApprovedLeave->execute([$pastLeaveNomor, $op2Id, $hrdId]);

    // Update Employee Quota: cuti_terpakai = 2, sisa_cuti = 10
    $pdo->exec("UPDATE karyawan SET cuti_terpakai = 2, sisa_cuti = 10 WHERE id = $op2Id");

    // Insert into riwayat_kuota_cuti
    $stmtQuotaLog = $pdo->prepare("
        INSERT INTO riwayat_kuota_cuti (
            employee_id, kuota_sebelum, perubahan, kuota_sesudah,
            tipe, keterangan, created_by, created_at
        ) VALUES (?, 12, -2, 10, 'potong_cuti', ?, ?, '2026-09-01 10:00:00')
    ");
    $stmtQuotaLog->execute([$op2Id, "Pemotongan cuti disetujui ({$pastLeaveNomor}) oleh HRD", $hrdId]);
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
echo "=== SEEDER SUCCESSFUL ===\n";
echo "Total Departemen: 15\n";
echo "1 Super Admin (admin@nakakin.co.id) [Kuota: 12, Terpakai: 0, Sisa: 12]\n";
echo "1 HRD (hrd@nakakin.co.id) [Kuota: 12, Terpakai: 0, Sisa: 12]\n";
echo "1 GA (ga@nakakin.co.id) [Kuota: 12, Terpakai: 0, Sisa: 12]\n";
echo "1 Manager (manager@nakakin.co.id) [Kuota: 12, Terpakai: 0, Sisa: 12]\n";
echo "15 Supervisor (spv.<kode>@nakakin.co.id) [Kuota: 12, Terpakai: 0, Sisa: 12]\n";
echo "15 Leader (ldr.<kode>@nakakin.co.id) [Kuota: 12, Terpakai: 0, Sisa: 12]\n";
echo "30 Operator (op1.<kode>@nakakin.co.id, op2.<kode>@nakakin.co.id)\n";
echo "Total Karyawan: " . ($empId - 1) . "\n";
echo "Total Pengajuan Cuti Demo Pending: " . count($createdOperators) . " (1 di setiap departemen siap di-testing!)\n";
echo "1 Pengajuan Cuti Approved Demo: Operator 2 Core (CUTI/NAK/2026/09/016) [Cuti Terpakai: 2, Sisa: 10, Log Riwayat: Ada]\n";
echo "Password default untuk semua akun: password123\n";
