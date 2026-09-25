<?php
/**
 * Migration Script: Role Permissions (Hak Akses & Privilege)
 */

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDbConnection();

    // Create table role_permissions
    $sql = "CREATE TABLE IF NOT EXISTS role_permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role VARCHAR(50) NOT NULL,
        permission_key VARCHAR(100) NOT NULL,
        permission_name VARCHAR(150) NOT NULL,
        category VARCHAR(50) NOT NULL,
        description VARCHAR(255) NULL,
        is_granted TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uk_role_perm (role, permission_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sql);

    // Master Permissions Definition
    $allPermissions = [
        'leave_request' => [
            'name' => 'Pengajuan Cuti Pribadi',
            'category' => 'Cuti & Kehadiran',
            'description' => 'Mengajukan cuti mandiri, upload surat sakit/lampiran & cek sisa kuota'
        ],
        'view_public_board' => [
            'name' => 'Papan Informasi & Kalender Bersama',
            'category' => 'Cuti & Kehadiran',
            'description' => 'Melihat jadwal cuti rekan kerja, kalender pabrik, dan statistik umum'
        ],
        'approval_tier1' => [
            'name' => 'Persetujuan Tier 1 (Leader / Supervisor)',
            'category' => 'Persetujuan (Approval)',
            'description' => 'Verifikasi dan review pengajuan cuti operator/staff di departemen sendiri'
        ],
        'approval_tier2' => [
            'name' => 'Persetujuan Tier 2 (Plant Manager)',
            'category' => 'Persetujuan (Approval)',
            'description' => 'Persetujuan operasional tingkat manajerial lintas seluruh 15 departemen'
        ],
        'approval_tier3' => [
            'name' => 'Persetujuan Tier 3 / Final (HRD)',
            'category' => 'Persetujuan (Approval)',
            'description' => 'Persetujuan akhir resmi dan eksekusi otomatis pemotongan kuota cuti tahunan'
        ],
        'manage_employees' => [
            'name' => 'Kelola Master Data Karyawan',
            'category' => 'Manajemen HRD',
            'description' => 'Tambah, ubah, hapus, reset password, dan import massal Excel data karyawan'
        ],
        'manage_quotas' => [
            'name' => 'Penyesuaian & Alokasi Kuota Cuti',
            'category' => 'Manajemen HRD',
            'description' => 'Penyesuaian sisa kuota cuti individual maupun alokasi kuota massal (Bulk)'
        ],
        'view_reports' => [
            'name' => 'Rekap Laporan & Cetak Surat Cuti',
            'category' => 'Laporan & Dokumen',
            'description' => 'Melihat filter analitik rekapitulasi cuti dan cetak surat resmi format PDF/A4'
        ],
        'manage_settings' => [
            'name' => 'Pengaturan Sistem, Kop Surat & Privilege',
            'category' => 'Sistem & Konfigurasi',
            'description' => 'Mengatur nama instansi, logo perusahaan, nomor surat, dan matriks hak akses'
        ],
    ];

    // Default Role Granted Mapping
    $roleDefaults = [
        'operator' => ['leave_request', 'view_public_board'],
        'staff' => ['leave_request', 'view_public_board'],
        'leader' => ['leave_request', 'view_public_board', 'approval_tier1', 'view_reports'],
        'supervisor' => ['leave_request', 'view_public_board', 'approval_tier1', 'view_reports'],
        'manager' => ['leave_request', 'view_public_board', 'approval_tier2', 'view_reports'],
        'hrd' => ['leave_request', 'view_public_board', 'approval_tier3', 'manage_employees', 'manage_quotas', 'view_reports', 'manage_settings'],
        'superadmin' => ['leave_request', 'view_public_board', 'approval_tier1', 'approval_tier2', 'approval_tier3', 'manage_employees', 'manage_quotas', 'view_reports', 'manage_settings'],
    ];

    $stmt = $pdo->prepare("INSERT INTO role_permissions (role, permission_key, permission_name, category, description, is_granted) 
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            permission_name = VALUES(permission_name),
            category = VALUES(category),
            description = VALUES(description)");

    foreach ($roleDefaults as $role => $grantedKeys) {
        foreach ($allPermissions as $pKey => $pData) {
            $isGranted = in_array($pKey, $grantedKeys) ? 1 : 0;
            $stmt->execute([$role, $pKey, $pData['name'], $pData['category'], $pData['description'], $isGranted]);
        }
    }

    echo "=== MIGRATION SUCCESSFUL ===\nTable role_permissions created and populated successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
