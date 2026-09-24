<?php
/**
 * API Leaves List (My Leaves / Team Leaves / Company Leaves)
 * GET /api/leaves/list.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$user = authenticateApiUser();
$pdo = getDbConnection();

$status = $_GET['status'] ?? 'all';
$type = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';
$scope = $_GET['scope'] ?? 'my'; // 'my', 'team', 'all'

$hierarki = (int)($user['level_hierarki'] ?? 1);
$isSuperOrHRD = ($user['role'] === 'superadmin' || $user['role'] === 'admin' || $user['role'] === 'hrd' || $hierarki >= 7);
$isManager = ($user['role'] === 'manager' || ($hierarki >= 5 && $hierarki <= 6));
$isLeaderOrSpv = ($user['role'] === 'supervisor' || $user['role'] === 'leader' || $hierarki >= 3);

$params = [];
$conditions = [];

if ($scope === 'all' || $scope === 'company') {
    if ($isSuperOrHRD || $isManager) {
        // HRD, Superadmin, and Manager can view all leaves company-wide
    } elseif ($isLeaderOrSpv) {
        // Leader/Spv restricted to their department
        $conditions[] = "k.departemen_id = ?";
        $params[] = $user['departemen_id'];
    } else {
        $conditions[] = "p.employee_id = ?";
        $params[] = $user['id'];
    }
} elseif ($scope === 'team') {
    if ($isSuperOrHRD || $isManager) {
        // Superadmin, HRD, Manager can see all team leaves
    } elseif ($isLeaderOrSpv) {
        $conditions[] = "k.departemen_id = ?";
        $params[] = $user['departemen_id'];
    } else {
        $conditions[] = "p.employee_id = ?";
        $params[] = $user['id'];
    }
} else {
    // Default: 'my' (own leaves)
    $conditions[] = "p.employee_id = ?";
    $params[] = $user['id'];
}

if (!empty($status) && $status !== 'all') {
    $conditions[] = "p.status = ?";
    $params[] = $status;
}

if (!empty($type)) {
    $conditions[] = "p.leave_type_id = ?";
    $params[] = $type;
}

$filterDept = (int)($_GET['dept_id'] ?? 0);
if ($filterDept > 0) {
    $conditions[] = "k.departemen_id = ?";
    $params[] = $filterDept;
}

if (!empty($search)) {
    $conditions[] = "(p.nomor_surat LIKE ? OR p.alasan LIKE ? OR k.nama_lengkap LIKE ? OR d.nama_dept LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

$stmt = $pdo->prepare("
    SELECT 
        p.id, p.nomor_surat, p.employee_id, p.leave_type_id,
        p.tanggal_mulai, p.tanggal_selesai, p.total_hari, p.alasan,
        p.alamat_selama_cuti, p.kontak_darurat, p.attachment, p.status,
        p.approval_step, p.spv_id, p.spv_at, p.spv_notes,
        p.manager_id, p.manager_at, p.manager_notes,
        p.hrd_id, p.hrd_at, p.hrd_notes,
        p.approved_by, p.approved_at, p.rejection_reason, p.catatan_atasan,
        p.created_at, p.updated_at,
        k.nama_lengkap, k.nik, k.email, k.no_hp, k.alamat as alamat_karyawan, k.tanggal_masuk, k.sisa_cuti, k.foto as employee_foto, k.departemen_id,
        d.nama_dept, d.kode_dept,
        jb.nama_jabatan, jb.level_hierarki as employee_level,
        j.nama_cuti, j.kode as kode_cuti, j.potong_kuota, j.butuh_lampiran,
        spv.nama_lengkap as spv_name, spv_j.nama_jabatan as spv_jabatan, spv_d.nama_dept as spv_dept,
        mgr.nama_lengkap as manager_name, mgr_j.nama_jabatan as manager_jabatan, mgr_d.nama_dept as manager_dept,
        hrd.nama_lengkap as hrd_name, hrd_j.nama_jabatan as hrd_jabatan, hrd_d.nama_dept as hrd_dept,
        appr.nama_lengkap as approver_name, appr_j.nama_jabatan as approver_jabatan, appr_d.nama_dept as approver_dept
    FROM pengajuan_cuti p
    JOIN karyawan k ON p.employee_id = k.id
    JOIN departemen d ON k.departemen_id = d.id
    JOIN jabatan jb ON k.jabatan_id = jb.id
    JOIN jenis_cuti j ON p.leave_type_id = j.id
    LEFT JOIN karyawan spv ON p.spv_id = spv.id
    LEFT JOIN jabatan spv_j ON spv.jabatan_id = spv_j.id
    LEFT JOIN departemen spv_d ON spv.departemen_id = spv_d.id
    LEFT JOIN karyawan mgr ON p.manager_id = mgr.id
    LEFT JOIN jabatan mgr_j ON mgr.jabatan_id = mgr_j.id
    LEFT JOIN departemen mgr_d ON mgr.departemen_id = mgr_d.id
    LEFT JOIN karyawan hrd ON p.hrd_id = hrd.id
    LEFT JOIN jabatan hrd_j ON hrd.jabatan_id = hrd_j.id
    LEFT JOIN departemen hrd_d ON hrd.departemen_id = hrd_d.id
    LEFT JOIN karyawan appr ON p.approved_by = appr.id
    LEFT JOIN jabatan appr_j ON appr.jabatan_id = appr_j.id
    LEFT JOIN departemen appr_d ON appr.departemen_id = appr_d.id
    $whereClause
    ORDER BY p.created_at DESC
");
$stmt->execute($params);
$leaves = $stmt->fetchAll();

// Add baseUrl for attachments if present
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$attachmentBaseUrl = rtrim($protocol . $host . rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/\\'), '/') . '/assets/uploads/leaves/';

foreach ($leaves as &$item) {
    $item['id'] = (int)$item['id'];
    $item['employee_id'] = (int)$item['employee_id'];
    $item['leave_type_id'] = (int)$item['leave_type_id'];
    $item['total_hari'] = (int)$item['total_hari'];
    $item['potong_kuota'] = (int)$item['potong_kuota'];
    $item['attachment_url'] = !empty($item['attachment']) ? $attachmentBaseUrl . $item['attachment'] : null;
}
unset($item);

jsonResponse(true, 'Daftar pengajuan cuti berhasil dimuat', $leaves);
