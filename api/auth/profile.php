<?php
/**
 * API User Profile Endpoint
 * GET /api/auth/profile.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$user = authenticateApiUser();
$pdo = getDbConnection();

// Get Quota History
$stmtQuota = $pdo->prepare("
    SELECT r.*, k.nama_lengkap as created_by_name
    FROM riwayat_kuota_cuti r
    LEFT JOIN karyawan k ON r.created_by = k.id
    WHERE r.employee_id = ?
    ORDER BY r.created_at DESC
    LIMIT 10
");
$stmtQuota->execute([$user['id']]);
$quotaHistory = $stmtQuota->fetchAll();

// Get Leaves Count
$stmtCount = $pdo->prepare("
    SELECT 
        COUNT(*) as total_pengajuan,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as total_pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as total_approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as total_rejected
    FROM pengajuan_cuti
    WHERE employee_id = ?
");
$stmtCount->execute([$user['id']]);
$summary = $stmtCount->fetch();

jsonResponse(true, 'Data profil berhasil diambil', [
    'user' => $user,
    'quota_history' => $quotaHistory,
    'summary' => [
        'total_pengajuan' => (int)($summary['total_pengajuan'] ?? 0),
        'total_pending' => (int)($summary['total_pending'] ?? 0),
        'total_approved' => (int)($summary['total_approved'] ?? 0),
        'total_rejected' => (int)($summary['total_rejected'] ?? 0),
    ]
]);
