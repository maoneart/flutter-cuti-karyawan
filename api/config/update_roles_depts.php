<?php
/**
 * Migration Script: Update Departemen, Jabatan, and Roles
 * PT. Nakakin Indonesia Leave Management System
 */

require_once __DIR__ . '/../../config/database.php';

$pdo = getDbConnection();

try {
    // 1. Modify karyawan.role to VARCHAR(50) so any role can be stored cleanly
    $pdo->exec("ALTER TABLE karyawan MODIFY COLUMN `role` VARCHAR(50) NOT NULL DEFAULT 'operator'");
    echo "1. Altered karyawan.role to VARCHAR(50).\n";

    // 2. Clear and repopulate departemen with exact requested names
    // To preserve foreign keys safely, let's update or truncate/insert
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE departemen;");

    $depts = [
        ['kode' => 'ACC', 'nama' => 'Accounting', 'desc' => 'Accounting & Finance Department'],
        ['kode' => 'CAST', 'nama' => 'Casting', 'desc' => 'Casting & Foundry Department'],
        ['kode' => 'CORE', 'nama' => 'Core', 'desc' => 'Core Production Department'],
        ['kode' => 'DC', 'nama' => 'Diecasting', 'desc' => 'Die Casting Production Department'],
        ['kode' => 'ENG', 'nama' => 'Engineering', 'desc' => 'Engineering & Technical Department'],
        ['kode' => 'FET', 'nama' => 'Fettling', 'desc' => 'Fettling & Finishing Department'],
        ['kode' => 'GA', 'nama' => 'GA', 'desc' => 'General Affairs Department'],
        ['kode' => 'HRD', 'nama' => 'HRD', 'desc' => 'Human Resources Department'],
        ['kode' => 'MC', 'nama' => 'Machining', 'desc' => 'Machining Production Department'],
        ['kode' => 'MKT', 'nama' => 'Marketing', 'desc' => 'Sales & Marketing Department'],
        ['kode' => 'MAINT', 'nama' => 'Maintenance', 'desc' => 'Machine & Utility Maintenance Department'],
        ['kode' => 'PPIC', 'nama' => 'PPIC', 'desc' => 'Production Planning & Inventory Control'],
        ['kode' => 'PURCH', 'nama' => 'Purchasing', 'desc' => 'Purchasing & Procurement Department'],
        ['kode' => 'QC', 'nama' => 'QC', 'desc' => 'Quality Control Department'],
        ['kode' => 'QCL', 'nama' => 'QC Line', 'desc' => 'Quality Control Line Inspection Department'],
    ];

    $stmtDept = $pdo->prepare("INSERT INTO departemen (id, kode_dept, nama_dept, deskripsi, created_at) VALUES (?, ?, ?, ?, NOW())");
    $id = 1;
    foreach ($depts as $d) {
        $stmtDept->execute([$id, $d['kode'], $d['nama'], $d['desc']]);
        $id++;
    }
    echo "2. Populated 15 Departments successfully.\n";

    // 3. Update Jabatan table
    $pdo->exec("TRUNCATE TABLE jabatan;");
    $positions = [
        ['id' => 1, 'nama' => 'Operator Produksi', 'level' => 1],
        ['id' => 2, 'nama' => 'Staff', 'level' => 2],
        ['id' => 3, 'nama' => 'Leader', 'level' => 3],
        ['id' => 4, 'nama' => 'Supervisor (Spv)', 'level' => 4],
        ['id' => 5, 'nama' => 'Department Manager', 'level' => 6],
        ['id' => 6, 'nama' => 'HRD', 'level' => 7],
        ['id' => 7, 'nama' => 'Super Admin', 'level' => 8],
    ];

    $stmtJab = $pdo->prepare("INSERT INTO jabatan (id, nama_jabatan, level_hierarki, created_at) VALUES (?, ?, ?, NOW())");
    foreach ($positions as $p) {
        $stmtJab->execute([$p['id'], $p['nama'], $p['level']]);
    }
    echo "3. Populated Positions (Jabatan) successfully.\n";

    // 4. Update existing employees departemen_id & role if needed
    // Map HRD employee Hermawan
    $pdo->exec("UPDATE karyawan SET departemen_id = 8, jabatan_id = 6, role = 'hrd' WHERE email = 'hermawan@nakakin.co.id' OR nik = 'NAK-001'");
    // Map Super Admin if any
    $pdo->exec("UPDATE karyawan SET role = 'superadmin' WHERE email LIKE '%admin@%' AND id = 1");
    // Fix foreign keys
    $pdo->exec("UPDATE karyawan SET departemen_id = 1 WHERE departemen_id > 15 OR departemen_id = 0");
    $pdo->exec("UPDATE karyawan SET jabatan_id = 1 WHERE jabatan_id > 7 OR jabatan_id = 0");

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "4. Migrations completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
