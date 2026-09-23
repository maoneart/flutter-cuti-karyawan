<?php
/**
 * API Public Today's Attendance & Leave Board
 * GET /api/public/board.php
 */

require_once __DIR__ . '/../config/api_bootstrap.php';

$pdo = getDbConnection();
$today = date('Y-m-d');
$deptId = isset($_GET['dept_id']) ? (int)$_GET['dept_id'] : 0;

$params = [$today];
$deptCondition = "";
if ($deptId > 0) {
    $deptCondition = " AND k.departemen_id = ?";
    $params[] = $deptId;
}

// 1. Employees currently on approved leave today
$stmtLeaves = $pdo->prepare("
    SELECT 
        p.id, p.nomor_surat, p.tanggal_mulai, p.tanggal_selesai, p.total_hari, p.alasan,
        k.nama_lengkap, k.nik, k.foto,
        d.nama_dept, d.kode_dept,
        j.nama_jabatan,
        l.nama_cuti, l.kode as kode_cuti
    FROM pengajuan_cuti p
    JOIN karyawan k ON p.employee_id = k.id
    JOIN departemen d ON k.departemen_id = d.id
    JOIN jabatan j ON k.jabatan_id = j.id
    JOIN jenis_cuti l ON p.leave_type_id = l.id
    WHERE p.status = 'approved' 
      AND ? BETWEEN p.tanggal_mulai AND p.tanggal_selesai
      $deptCondition
    ORDER BY d.nama_dept ASC, k.nama_lengkap ASC
");
$stmtLeaves->execute($params);
$leavesToday = $stmtLeaves->fetchAll();

// 2. Summary stats
$totalKaryawan = (int)$pdo->query("SELECT COUNT(*) FROM karyawan WHERE status_aktif = 'Aktif'")->fetchColumn();
$totalCutiHariIni = count($leavesToday);
$totalHadir = max(0, $totalKaryawan - $totalCutiHariIni);
$presentRate = $totalKaryawan > 0 ? round(($totalHadir / $totalKaryawan) * 100, 1) : 100;

// 3. Departments List
$departments = $pdo->query("SELECT id, kode_dept, nama_dept FROM departemen ORDER BY nama_dept ASC")->fetchAll();

jsonResponse(true, 'Data papan kehadiran hari ini berhasil dimuat', [
    'today_formatted' => date('l, d F Y'),
    'summary' => [
        'total_karyawan' => $totalKaryawan,
        'total_hadir' => $totalHadir,
        'total_cuti_izin' => $totalCutiHariIni,
        'tingkat_kehadiran' => $presentRate
    ],
    'departments' => $departments,
    'leaves_today' => $leavesToday
]);
