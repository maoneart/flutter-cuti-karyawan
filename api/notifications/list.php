<?php
/**
 * API Notifications List & Mark as Read
 * GET / POST /api/notifications/list.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$user = authenticateApiUser();
$pdo = getDbConnection();
$hierarki = (int)($user['level_hierarki'] ?? 1);

// Handle Mark as Read POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmtMark = $pdo->prepare("UPDATE pengajuan_cuti SET notif_read = 1 WHERE employee_id = ?");
    $stmtMark->execute([$user['id']]);
    jsonResponse(true, 'Notifikasi telah ditandai dibaca.');
}

// Build notification items
$notifications = [];

// 1. Check pending items waiting for this user's approval
if ($user['role'] === 'admin' || $hierarki >= 7) {
    // HRD sees pending_hrd
    $stmt = $pdo->query("
        SELECT p.id, p.nomor_surat, p.total_hari, p.tanggal_mulai, p.tanggal_selesai, p.created_at,
               k.nama_lengkap, d.nama_dept, l.nama_cuti
        FROM pengajuan_cuti p
        JOIN karyawan k ON p.employee_id = k.id
        JOIN departemen d ON k.departemen_id = d.id
        JOIN jenis_cuti l ON p.leave_type_id = l.id
        WHERE p.approval_step = 'pending_hrd' AND p.status = 'pending'
        ORDER BY p.created_at DESC
        LIMIT 10
    ");
    while ($row = $stmt->fetch()) {
        $notifications[] = [
            'id' => 'appr_' . $row['id'],
            'leave_id' => (int)$row['id'],
            'type' => 'approval_required',
            'title' => 'Menunggu Persetujuan Final HRD',
            'message' => "Pengajuan {$row['nama_cuti']} ({$row['total_hari']} hari) oleh {$row['nama_lengkap']} ({$row['nama_dept']}).",
            'time' => $row['created_at'],
            'read' => false,
        ];
    }
} elseif ($user['role'] === 'manager' || ($hierarki >= 5 && $hierarki <= 6)) {
    // Manager sees all pending_manager across all departments (except their own submission)
    $stmt = $pdo->prepare("
        SELECT p.id, p.nomor_surat, p.total_hari, p.tanggal_mulai, p.tanggal_selesai, p.created_at,
               k.nama_lengkap, d.nama_dept, l.nama_cuti
        FROM pengajuan_cuti p
        JOIN karyawan k ON p.employee_id = k.id
        JOIN departemen d ON k.departemen_id = d.id
        JOIN jenis_cuti l ON p.leave_type_id = l.id
        WHERE p.approval_step = 'pending_manager' AND p.status = 'pending' AND p.employee_id != ?
        ORDER BY p.created_at DESC
        LIMIT 15
    ");
    $stmt->execute([$user['id']]);
    while ($row = $stmt->fetch()) {
        $notifications[] = [
            'id' => 'appr_' . $row['id'],
            'leave_id' => (int)$row['id'],
            'type' => 'approval_required',
            'title' => 'Menunggu Persetujuan Manager',
            'message' => "Pengajuan {$row['nama_cuti']} ({$row['total_hari']} hari) oleh {$row['nama_lengkap']} ({$row['nama_dept']}) memerlukan persetujuan Manager.",
            'time' => $row['created_at'],
            'read' => false,
        ];
    }
} elseif ($hierarki >= 3) {
    // Leader / Spv sees pending_spv
    $stmt = $pdo->prepare("
        SELECT p.id, p.nomor_surat, p.total_hari, p.tanggal_mulai, p.tanggal_selesai, p.created_at,
               k.nama_lengkap, d.nama_dept, l.nama_cuti
        FROM pengajuan_cuti p
        JOIN karyawan k ON p.employee_id = k.id
        JOIN departemen d ON k.departemen_id = d.id
        JOIN jenis_cuti l ON p.leave_type_id = l.id
        WHERE p.approval_step = 'pending_spv' AND p.status = 'pending' AND k.departemen_id = ? AND k.id != ?
        ORDER BY p.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$user['departemen_id'], $user['id']]);
    while ($row = $stmt->fetch()) {
        $notifications[] = [
            'id' => 'appr_' . $row['id'],
            'leave_id' => (int)$row['id'],
            'type' => 'approval_required',
            'title' => 'Pengajuan Cuti Baru Masuk',
            'message' => "Pengajuan {$row['nama_cuti']} ({$row['total_hari']} hari) oleh {$row['nama_lengkap']} memerlukan persetujuan Anda.",
            'time' => $row['created_at'],
            'read' => false,
        ];
    }
}

// 2. Personal leave status updates for this employee
$stmtMy = $pdo->prepare("
    SELECT p.id, p.nomor_surat, p.status, p.approval_step, p.updated_at, p.notif_read, l.nama_cuti
    FROM pengajuan_cuti p
    JOIN jenis_cuti l ON p.leave_type_id = l.id
    WHERE p.employee_id = ? AND p.status IN ('approved', 'rejected')
    ORDER BY p.updated_at DESC
    LIMIT 10
");
$stmtMy->execute([$user['id']]);
while ($row = $stmtMy->fetch()) {
    $isApproved = ($row['status'] === 'approved');
    $notifications[] = [
        'id' => 'leave_' . $row['id'],
        'leave_id' => (int)$row['id'],
        'type' => $isApproved ? 'leave_approved' : 'leave_rejected',
        'title' => $isApproved ? 'Pengajuan Cuti Disetujui!' : 'Pengajuan Cuti Ditolak',
        'message' => "Pengajuan {$row['nama_cuti']} ({$row['nomor_surat']}) telah " . ($isApproved ? 'disetujui secara resmi oleh HRD.' : 'ditolak oleh atasan/HRD.'),
        'time' => $row['updated_at'],
        'read' => (bool)$row['notif_read'],
    ];
}

$unreadCount = 0;
foreach ($notifications as $n) {
    if (!$n['read']) {
        $unreadCount++;
    }
}

jsonResponse(true, 'Daftar notifikasi berhasil dimuat', [
    'unread_count' => $unreadCount,
    'notifications' => $notifications,
]);
