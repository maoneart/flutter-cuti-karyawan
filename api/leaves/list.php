<?php
/**
 * API Leaves List (My Leaves / Team Leaves)
 * GET /api/leaves/list.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$user = authenticateApiUser();
$pdo = getDbConnection();

$status = $_GET['status'] ?? 'all';
$type = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';
$scope = $_GET['scope'] ?? 'my'; // 'my' or 'team'

$params = [];
$conditions = [];

if ($scope === 'team' && in_array($user['role'], ['admin', 'atasan'], true)) {
    if ($user['role'] === 'admin') {
        // Admin sees all
    } else {
        // Supervisor sees their department
        $conditions[] = "k.departemen_id = ?";
        $params[] = $user['departemen_id'];
    }
} else {
    // Default to own leaves
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

if (!empty($search)) {
    $conditions[] = "(p.nomor_surat LIKE ? OR p.alasan LIKE ? OR k.nama_lengkap LIKE ?)";
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
        p.approved_by, p.approved_at, p.rejection_reason, p.catatan_atasan,
        p.created_at, p.updated_at,
        k.nama_lengkap, k.nik, k.foto as employee_foto,
        d.nama_dept, d.kode_dept,
        j.nama_cuti, j.kode as kode_cuti, j.potong_kuota, j.butuh_lampiran,
        appr.nama_lengkap as approver_name
    FROM pengajuan_cuti p
    JOIN karyawan k ON p.employee_id = k.id
    JOIN departemen d ON k.departemen_id = d.id
    JOIN jenis_cuti j ON p.leave_type_id = j.id
    LEFT JOIN karyawan appr ON p.approved_by = appr.id
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
