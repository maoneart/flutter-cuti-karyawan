<?php
/**
 * API Dashboard Stats Endpoint (with 3-Tier Multi-Level Approval Counter & Smart Bell Badge)
 * GET /api/dashboard/stats.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$user = authenticateApiUser();
$pdo = getDbConnection();
$today = date('Y-m-d');
$hierarki = (int)($user['level_hierarki'] ?? 1);

// 1. Employee Leave Counters
$stmtSummary = $pdo->prepare("
    SELECT 
        COUNT(*) as total_pengajuan,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count
    FROM pengajuan_cuti
    WHERE employee_id = ?
");
$stmtSummary->execute([$user['id']]);
$stats = $stmtSummary->fetch();

// 2. Pending Approvals Count (Filtered by 3-Tier Stage for this specific user role)
$pendingApprovalsCount = 0;
if ($user['role'] === 'superadmin' || $user['role'] === 'admin' || $user['role'] === 'hrd' || $hierarki >= 7) {
    // HRD & Superadmin: Count ALL pending leaves across company for realtime monitoring & badge counter
    $stmtPending = $pdo->query("SELECT COUNT(*) FROM pengajuan_cuti WHERE status = 'pending'");
    $pendingApprovalsCount = (int)$stmtPending->fetchColumn();
} elseif ($user['role'] === 'manager' || ($hierarki >= 5 && $hierarki <= 6)) {
    // Manager -> pending_manager across all departments (except own leave)
    $stmtPending = $pdo->prepare("
        SELECT COUNT(p.id) 
        FROM pengajuan_cuti p
        WHERE p.approval_step = 'pending_manager' 
          AND p.status = 'pending'
          AND p.employee_id != ?
    ");
    $stmtPending->execute([$user['id']]);
    $pendingApprovalsCount = (int)$stmtPending->fetchColumn();
} elseif ($hierarki >= 3) {
    // Leader / Supervisor -> pending_spv in their dept
    $stmtPending = $pdo->prepare("
        SELECT COUNT(p.id) 
        FROM pengajuan_cuti p
        JOIN karyawan k ON p.employee_id = k.id
        WHERE p.approval_step = 'pending_spv' 
          AND p.status = 'pending'
          AND k.departemen_id = ? 
          AND k.id != ?
    ");
    $stmtPending->execute([$user['departemen_id'], $user['id']]);
    $pendingApprovalsCount = (int)$stmtPending->fetchColumn();
}

// 3. Employee unread status updates (e.g. newly approved/rejected leaves)
$stmtUnreadMy = $pdo->prepare("
    SELECT COUNT(*) 
    FROM pengajuan_cuti 
    WHERE employee_id = ? AND notif_read = 0 AND status IN ('approved', 'rejected')
");
$stmtUnreadMy->execute([$user['id']]);
$unreadMyLeavesCount = (int)$stmtUnreadMy->fetchColumn();

// Total Bell Notification Badge:
// If approver -> their pending approvals + their own leave alerts
// If operator -> their own leave alerts
$bellNotificationCount = $pendingApprovalsCount + $unreadMyLeavesCount;

// 4. Today's Employees on Leave
$stmtToday = $pdo->prepare("
    SELECT p.id, p.tanggal_mulai, p.tanggal_selesai, p.total_hari, p.alasan,
           k.nama_lengkap, k.nik, d.nama_dept, j.nama_cuti, j.kode as kode_cuti
    FROM pengajuan_cuti p
    JOIN karyawan k ON p.employee_id = k.id
    JOIN departemen d ON k.departemen_id = d.id
    JOIN jenis_cuti j ON p.leave_type_id = j.id
    WHERE p.status = 'approved' 
      AND ? BETWEEN p.tanggal_mulai AND p.tanggal_selesai
    ORDER BY d.nama_dept ASC, k.nama_lengkap ASC
");
$stmtToday->execute([$today]);
$todayOnLeave = $stmtToday->fetchAll();

// 5. Recent Leaves for current user
$stmtRecent = $pdo->prepare("
    SELECT p.*, j.nama_cuti, j.kode as kode_cuti, j.potong_kuota
    FROM pengajuan_cuti p
    JOIN jenis_cuti j ON p.leave_type_id = j.id
    WHERE p.employee_id = ?
    ORDER BY p.created_at DESC
    LIMIT 5
");
$stmtRecent->execute([$user['id']]);
$recentLeaves = $stmtRecent->fetchAll();

foreach ($recentLeaves as &$item) {
    $step = $item['approval_step'] ?? 'pending_spv';
    if ($step === 'pending_spv') {
        $item['step_label'] = 'Menunggu Leader / Spv';
    } elseif ($step === 'pending_manager') {
        $item['step_label'] = 'Menunggu Manager';
    } elseif ($step === 'pending_hrd') {
        $item['step_label'] = 'Menunggu HRD (Final)';
    } elseif ($item['status'] === 'approved') {
        $item['step_label'] = 'Disetujui Lengkap';
    } elseif ($item['status'] === 'rejected') {
        $item['step_label'] = 'Ditolak';
    } else {
        $item['step_label'] = 'Dibatalkan';
    }
}
unset($item);

jsonResponse(true, 'Data dashboard berhasil dimuat', [
    'user' => [
        'id' => (int)$user['id'],
        'nama_lengkap' => $user['nama_lengkap'],
        'nik' => $user['nik'],
        'role' => $user['role'],
        'nama_dept' => $user['nama_dept'],
        'nama_jabatan' => $user['nama_jabatan'],
        'level_hierarki' => (int)$user['level_hierarki'],
        'kuota_cuti' => (int)$user['kuota_cuti'],
        'cuti_terpakai' => (int)$user['cuti_terpakai'],
        'sisa_cuti' => (int)$user['sisa_cuti'],
    ],
    'counters' => [
        'sisa_cuti' => (int)$user['sisa_cuti'],
        'kuota_cuti' => (int)$user['kuota_cuti'],
        'cuti_terpakai' => (int)$user['cuti_terpakai'],
        'total_pengajuan' => (int)($stats['total_pengajuan'] ?? 0),
        'pending_count' => (int)($stats['pending_count'] ?? 0),
        'approved_count' => (int)($stats['approved_count'] ?? 0),
        'rejected_count' => (int)($stats['rejected_count'] ?? 0),
        'cancelled_count' => (int)($stats['cancelled_count'] ?? 0),
        'pending_approvals_count' => $pendingApprovalsCount,
        'bell_notification_count' => $bellNotificationCount,
        'today_on_leave_count' => count($todayOnLeave),
    ],
    'recent_leaves' => $recentLeaves,
    'today_on_leave' => $todayOnLeave
]);
