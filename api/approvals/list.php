<?php
/**
 * API Pending Approvals List (Supervisor & HRD Admin)
 * GET /api/approvals/list.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$user = authenticateApiUser();
requireApiRole(['atasan', 'admin'], $user);

$pdo = getDbConnection();
$status = $_GET['status'] ?? 'pending';
$search = $_GET['search'] ?? '';

$params = [];
$conditions = [];

if ($user['role'] === 'admin') {
    // Admin sees all departments
} else {
    // Supervisor sees subordinates in their department
    $conditions[] = "k.departemen_id = ?";
    $params[] = $user['departemen_id'];
    $conditions[] = "k.id != ?";
    $params[] = $user['id'];
}

if (!empty($status) && $status !== 'all') {
    $conditions[] = "p.status = ?";
    $params[] = $status;
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
        j.nama_jabatan,
        l.nama_cuti, l.kode as kode_cuti, l.potong_kuota, l.butuh_lampiran
    FROM pengajuan_cuti p
    JOIN karyawan k ON p.employee_id = k.id
    JOIN departemen d ON k.departemen_id = d.id
    JOIN jabatan j ON k.jabatan_id = j.id
    JOIN jenis_cuti l ON p.leave_type_id = l.id
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
}
unset($item);

jsonResponse(true, 'Daftar pengajuan persetujuan cuti berhasil dimuat', $list);
