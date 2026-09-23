<?php
/**
 * API Pending Approvals List (for Leader/Spv, Manager, and HRD Admin in 3-Tier Approval)
 * GET /api/approvals/list.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$user = authenticateApiUser();
requireApiRole(['atasan', 'admin'], $user);

$pdo = getDbConnection();
$status = $_GET['status'] ?? 'pending'; // 'pending' or 'all'
$search = $_GET['search'] ?? '';
$hierarki = (int)($user['level_hierarki'] ?? 1);

$params = [];
$conditions = [];

// Determine which approval_step belongs to this user:
// If HRD Admin -> pending_hrd (across all departments)
// If Manager (level 5-6) -> pending_manager (their department)
// If Leader/Spv (level 3-4) -> pending_spv (their department)
if ($user['role'] === 'admin' || $hierarki >= 7) {
    if ($status === 'pending') {
        $conditions[] = "p.approval_step = 'pending_hrd' AND p.status = 'pending'";
    }
} elseif ($hierarki >= 5) {
    // Manager
    $conditions[] = "k.departemen_id = ?";
    $params[] = $user['departemen_id'];
    $conditions[] = "k.id != ?";
    $params[] = $user['id'];
    if ($status === 'pending') {
        $conditions[] = "p.approval_step = 'pending_manager' AND p.status = 'pending'";
    }
} else {
    // Leader / Supervisor (level 3-4)
    $conditions[] = "k.departemen_id = ?";
    $params[] = $user['departemen_id'];
    $conditions[] = "k.id != ?";
    $params[] = $user['id'];
    if ($status === 'pending') {
        $conditions[] = "p.approval_step = 'pending_spv' AND p.status = 'pending'";
    }
}

if (!empty($search)) {
    $conditions[] = "(k.nama_lengkap LIKE ? OR k.nik LIKE ? OR p.nomor_surat LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

$stmt = $pdo->prepare("
    SELECT 
        p.*, 
        k.nama_lengkap, k.nik, k.foto as employee_foto, k.sisa_cuti as sisa_cuti_karyawan,
        d.nama_dept, d.kode_dept,
        j.nama_jabatan, j.level_hierarki as employee_level,
        l.nama_cuti, l.kode as kode_cuti, l.potong_kuota, l.butuh_lampiran,
        spv.nama_lengkap as spv_name,
        mgr.nama_lengkap as manager_name,
        hrd.nama_lengkap as hrd_name
    FROM pengajuan_cuti p
    JOIN karyawan k ON p.employee_id = k.id
    JOIN departemen d ON k.departemen_id = d.id
    JOIN jabatan j ON k.jabatan_id = j.id
    JOIN jenis_cuti l ON p.leave_type_id = l.id
    LEFT JOIN karyawan spv ON p.spv_id = spv.id
    LEFT JOIN karyawan mgr ON p.manager_id = mgr.id
    LEFT JOIN karyawan hrd ON p.hrd_id = hrd.id
    $whereClause
    ORDER BY p.created_at DESC
");
$stmt->execute($params);
$list = $stmt->fetchAll();

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$attachmentBaseUrl = rtrim($protocol . $host . rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/\\'), '/') . '/assets/uploads/leaves/';

foreach ($list as &$item) {
    $item['id'] = (int)$item['id'];
    $item['employee_id'] = (int)$item['employee_id'];
    $item['leave_type_id'] = (int)$item['leave_type_id'];
    $item['total_hari'] = (int)$item['total_hari'];
    $item['potong_kuota'] = (int)$item['potong_kuota'];
    $item['attachment_url'] = !empty($item['attachment']) ? $attachmentBaseUrl . $item['attachment'] : null;
    
    // Label tahap saat ini
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

jsonResponse(true, 'Daftar pengajuan persetujuan cuti berhasil dimuat', $list);
