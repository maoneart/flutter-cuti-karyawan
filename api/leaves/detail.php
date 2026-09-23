<?php
/**
 * API Leave Request Detail
 * GET /api/leaves/detail.php?id=...
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$user = authenticateApiUser();
$pdo = getDbConnection();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    jsonResponse(false, 'ID pengajuan tidak valid.', null, 400);
}

$stmt = $pdo->prepare("
    SELECT 
        p.*, 
        k.nama_lengkap, k.nik, k.email, k.no_hp, k.foto as employee_foto,
        d.nama_dept, d.kode_dept,
        j.nama_jabatan,
        l.nama_cuti, l.kode as kode_cuti, l.potong_kuota, l.butuh_lampiran, l.deskripsi as deskripsi_cuti,
        appr.nama_lengkap as approver_name, appr.nik as approver_nik
    FROM pengajuan_cuti p
    JOIN karyawan k ON p.employee_id = k.id
    JOIN departemen d ON k.departemen_id = d.id
    JOIN jabatan j ON k.jabatan_id = j.id
    JOIN jenis_cuti l ON p.leave_type_id = l.id
    LEFT JOIN karyawan appr ON p.approved_by = appr.id
    WHERE p.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$leave = $stmt->fetch();

if (!$leave) {
    jsonResponse(false, 'Pengajuan cuti tidak ditemukan.', null, 404);
}

// Access Control: Only owner, supervisors of same department, or admin can view
$canAccess = ($leave['employee_id'] == $user['id']) || 
             ($user['role'] === 'admin') || 
             ($user['role'] === 'atasan' && $user['departemen_id'] == $leave['departemen_id']);

if (!$canAccess) {
    jsonResponse(false, 'Akses ditolak. Anda tidak memiliki izin untuk melihat pengajuan ini.', null, 403);
}

// Attachment URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$attachmentBaseUrl = rtrim($protocol . $host . rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/\\'), '/') . '/assets/uploads/leaves/';
$leave['attachment_url'] = !empty($leave['attachment']) ? $attachmentBaseUrl . $leave['attachment'] : null;

jsonResponse(true, 'Detail pengajuan cuti berhasil diambil', $leave);
