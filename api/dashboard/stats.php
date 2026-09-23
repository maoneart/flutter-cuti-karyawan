<?php
/**
 * API Dashboard Stats Endpoint
 * GET /api/dashboard/stats.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$user = authenticateApiUser();
$pdo = getDbConnection();
$today = date('Y-m-d');

// 1. Employee Leave Summary
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

// 2. Pending Approvals Count (for Atasan / HRD Admin)
$pendingApprovalsCount = 0;
if ($user['role'] === 'admin') {
    $stmtPending = $pdo->query("SELECT COUNT(*) FROM pengajuan_cuti WHERE status = 'pending'");
    $pendingApprovalsCount = (int)$stmtPending->fetchColumn();
} elseif ($user['role'] === 'atasan') {
    $stmtPending = $pdo->prepare("
        SELECT COUNT(p.id) 
        FROM pengajuan_cuti p
        JOIN karyawan k ON p.employee_id = k.id
        WHERE p.status = 'pending' 
          AND k.departemen_id = ? 
          AND k.id != ?
    ");
    $stmtPending->execute([$user['departemen_id'], $user['id']]);
    $pendingApprovalsCount = (int)$stmtPending->fetchColumn();
}

// 3. Today's Employees on Leave (Company wide / Department)
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

// 4. Recent Leave Requests for current user
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

jsonResponse(true, 'Data dashboard berhasil dimuat', [
    'user' => [
        'id' => (int)$user['id'],
        'nama_lengkap' => $user['nama_lengkap'],
        'nik' => $user['nik'],
        'role' => $user['role'],
        'nama_dept' => $user['nama_dept'],
        'nama_jabatan' => $user['nama_jabatan'],
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
        'today_on_leave_count' => count($todayOnLeave),
    ],
    'recent_leaves' => $recentLeaves,
    'today_on_leave' => $todayOnLeave
]);
