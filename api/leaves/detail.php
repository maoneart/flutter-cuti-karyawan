<?php
/**
 * API Leave Request Detail (with 3-Tier Multi-Level Approval Timeline)
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
        k.nama_lengkap, k.nik, k.email, k.no_hp, k.foto as employee_foto, k.departemen_id,
        d.nama_dept, d.kode_dept,
        j.nama_jabatan, j.level_hierarki as employee_level,
        l.nama_cuti, l.kode as kode_cuti, l.potong_kuota, l.butuh_lampiran, l.deskripsi as deskripsi_cuti,
        spv.nama_lengkap as spv_name, spv.nik as spv_nik,
        mgr.nama_lengkap as manager_name, mgr.nik as manager_nik,
        hrd.nama_lengkap as hrd_name, hrd.nik as hrd_nik,
        appr.nama_lengkap as approver_name, appr.nik as approver_nik
    FROM pengajuan_cuti p
    JOIN karyawan k ON p.employee_id = k.id
    JOIN departemen d ON k.departemen_id = d.id
    JOIN jabatan j ON k.jabatan_id = j.id
    JOIN jenis_cuti l ON p.leave_type_id = l.id
    LEFT JOIN karyawan spv ON p.spv_id = spv.id
    LEFT JOIN karyawan mgr ON p.manager_id = mgr.id
    LEFT JOIN karyawan hrd ON p.hrd_id = hrd.id
    LEFT JOIN karyawan appr ON p.approved_by = appr.id
    WHERE p.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$leave = $stmt->fetch();

if (!$leave) {
    jsonResponse(false, 'Pengajuan cuti tidak ditemukan.', null, 404);
}

// Access Control: Employee themself, HRD/Admin, Plant Manager (level >= 5), or same department members
$hierarki = (int)($user['level_hierarki'] ?? 1);
$canAccess = ($leave['employee_id'] == $user['id']) || 
             ($user['role'] === 'superadmin' || $user['role'] === 'admin' || $user['role'] === 'hrd' || $hierarki >= 5) || 
             ($user['departemen_id'] == $leave['departemen_id']);

if (!$canAccess) {
    jsonResponse(false, 'Akses ditolak. Anda tidak memiliki izin untuk melihat pengajuan ini.', null, 403);
}

// Attachment URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$attachmentBaseUrl = rtrim($protocol . $host . rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/\\'), '/') . '/assets/uploads/leaves/';
$leave['attachment_url'] = !empty($leave['attachment']) ? $attachmentBaseUrl . $leave['attachment'] : null;

// Build timeline tracking structure for Flutter UI
$employeeLevel = (int)($leave['employee_level'] ?? 1);
$timeline = [];

// Step 1: Submission
$timeline[] = [
    'step' => 'submitted',
    'title' => 'Pengajuan Dibuat',
    'status' => 'completed',
    'actor' => $leave['nama_lengkap'],
    'time' => $leave['created_at'],
    'notes' => $leave['alasan'],
];

// Step 2: Leader / Supervisor Approval (Required if applicant is Operator/Staff level 1-2)
if ($employeeLevel <= 2) {
    $spvStatus = 'waiting';
    if (!empty($leave['spv_id'])) {
        $spvStatus = 'approved';
    } elseif ($leave['status'] === 'rejected' && $leave['approval_step'] === 'rejected' && empty($leave['spv_id'])) {
        $spvStatus = 'rejected';
    }
    $timeline[] = [
        'step' => 'spv',
        'title' => 'Persetujuan Leader / Supervisor',
        'status' => $spvStatus,
        'actor' => $leave['spv_name'] ?? 'Leader / Spv Departemen',
        'time' => $leave['spv_at'],
        'notes' => $leave['spv_notes'],
    ];
}

// Step 3: Department Manager Approval (Required if applicant is <= level 4)
if ($employeeLevel <= 4) {
    $mgrStatus = 'waiting';
    if (!empty($leave['manager_id'])) {
        $mgrStatus = 'approved';
    } elseif ($leave['status'] === 'rejected' && $leave['approval_step'] === 'rejected' && !empty($leave['spv_id']) && empty($leave['manager_id'])) {
        $mgrStatus = 'rejected';
    }
    $timeline[] = [
        'step' => 'manager',
        'title' => 'Persetujuan Department Manager',
        'status' => $mgrStatus,
        'actor' => $leave['manager_name'] ?? 'Department Manager',
        'time' => $leave['manager_at'],
        'notes' => $leave['manager_notes'],
    ];
}

// Step 4: HRD Final Approval
$hrdStatus = 'waiting';
if ($leave['status'] === 'approved') {
    $hrdStatus = 'approved';
} elseif ($leave['status'] === 'rejected' && $leave['approval_step'] === 'rejected' && (!empty($leave['manager_id']) || $employeeLevel >= 5)) {
    $hrdStatus = 'rejected';
}
$timeline[] = [
    'step' => 'hrd',
    'title' => 'Persetujuan Final HRD',
    'status' => $hrdStatus,
    'actor' => $leave['hrd_name'] ?? $leave['approver_name'] ?? 'HRD Admin',
    'time' => $leave['hrd_at'] ?? $leave['approved_at'],
    'notes' => $leave['hrd_notes'] ?? $leave['catatan_atasan'],
];

$leave['timeline'] = $timeline;

jsonResponse(true, 'Detail pengajuan cuti berhasil diambil', $leave);
