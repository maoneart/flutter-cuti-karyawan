<?php
/**
 * API Team Attendance & Shift List
 * GET /api/attendance/team.php?tanggal=YYYY-MM-DD&dept_id=...
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$user = authenticateApiUser();
$pdo = getDbConnection();

$userRole = strtolower($user['role'] ?? '');
$userLevel = (int)($user['level_hierarki'] ?? 1);
$isLeaderOrSpv = in_array($userRole, ['leader', 'supervisor', 'atasan']) || ($userLevel >= 3 && $userLevel <= 4);
$isManager = $userRole === 'manager' || ($userLevel >= 5 && $userLevel <= 6);
$isHRD = in_array($userRole, ['admin', 'superadmin', 'hrd']) || $userLevel >= 7;

if (!$isLeaderOrSpv && !$isManager && !$isHRD) {
    jsonResponse(false, 'Akses ditolak! Menu ini khusus untuk Leader, Supervisor, dan Manajemen.', null, 403);
}

// Scoped department
if ($isLeaderOrSpv) {
    $deptId = (int)$user['departemen_id'];
} else {
    $deptId = (int)($_GET['dept_id'] ?? $user['departemen_id']);
    if ($deptId <= 0) $deptId = 1;
}

$tanggal = trim($_GET['tanggal'] ?? date('Y-m-d'));
if (!strtotime($tanggal)) {
    $tanggal = date('Y-m-d');
}

// Get department info
$stmtDept = $pdo->prepare("SELECT id, nama_dept, kode_dept FROM departemen WHERE id = ?");
$stmtDept->execute([$deptId]);
$department = $stmtDept->fetch();
if (!$department) {
    $deptId = (int)$user['departemen_id'];
    $stmtDept->execute([$deptId]);
    $department = $stmtDept->fetch();
}

// Fetch team members
$stmtTeam = $pdo->prepare("
    SELECT e.id, e.nik, e.nama_lengkap, e.email, e.no_hp, e.sisa_cuti, e.kuota_cuti,
           e.current_shift, e.role, e.jenis_kelamin,
           d.nama_dept, d.kode_dept, j.nama_jabatan, j.level_hierarki
    FROM karyawan e
    JOIN departemen d ON e.departemen_id = d.id
    JOIN jabatan j ON e.jabatan_id = j.id
    WHERE e.departemen_id = ?
    ORDER BY j.level_hierarki DESC, e.nama_lengkap ASC
");
$stmtTeam->execute([$deptId]);
$teamMembers = $stmtTeam->fetchAll();

// Fetch absensi_shift for date
$stmtAbs = $pdo->prepare("
    SELECT a.*, l.nama_lengkap AS nama_leader
    FROM absensi_shift a
    JOIN karyawan e ON a.employee_id = e.id
    LEFT JOIN karyawan l ON a.leader_id = l.id
    WHERE e.departemen_id = ? AND a.tanggal = ?
");
$stmtAbs->execute([$deptId, $tanggal]);
$absensiRecords = $stmtAbs->fetchAll();
$absensiMap = [];
foreach ($absensiRecords as $rec) {
    $absensiMap[$rec['employee_id']] = $rec;
}

// Fetch approved leaves for date
$stmtLeaves = $pdo->prepare("
    SELECT lr.id, lr.nomor_surat, lr.employee_id, lr.total_hari, lr.shift,
           jc.nama_cuti, jc.kode AS kode_cuti
    FROM pengajuan_cuti lr
    JOIN jenis_cuti jc ON lr.leave_type_id = jc.id
    JOIN karyawan e ON lr.employee_id = e.id
    WHERE e.departemen_id = ? 
      AND lr.status = 'approved'
      AND ? BETWEEN lr.tanggal_mulai AND lr.tanggal_selesai
");
$stmtLeaves->execute([$deptId, $tanggal]);
$leavesMap = [];
foreach ($stmtLeaves->fetchAll() as $lRec) {
    $leavesMap[$lRec['employee_id']] = $lRec;
}

$teamData = [];
foreach ($teamMembers as $m) {
    $empId = (int)$m['id'];
    $abs = $absensiMap[$empId] ?? null;
    $leave = $leavesMap[$empId] ?? null;

    $statusKehadiran = 'Hadir';
    $statusColor = 'emerald';
    $isMangkir = false;
    $isRevised = false;
    $revisiMenjadi = null;
    $catatan = '';

    if ($leave) {
        $statusKehadiran = 'Cuti: ' . $leave['kode_cuti'];
        $statusColor = 'blue';
    } elseif ($abs) {
        if ($abs['status'] === 'mangkir') {
            $statusKehadiran = 'Mangkir (Alpha)';
            $statusColor = 'rose';
            $isMangkir = true;
            $catatan = $abs['keterangan_mangkir'];
        } else {
            $statusKehadiran = strtoupper($abs['direvisi_menjadi'] ?: $abs['status']);
            $statusColor = 'amber';
            $isRevised = true;
            $revisiMenjadi = $abs['direvisi_menjadi'];
            $catatan = $abs['alasan_revisi'];
        }
    }

    $teamData[] = [
        'id' => $empId,
        'nik' => $m['nik'],
        'nama_lengkap' => $m['nama_lengkap'],
        'nama_dept' => $m['nama_dept'],
        'nama_jabatan' => $m['nama_jabatan'],
        'level_hierarki' => (int)$m['level_hierarki'],
        'role' => $m['role'],
        'current_shift' => $m['current_shift'] ?: 'Shift 1',
        'sisa_cuti' => (float)$m['sisa_cuti'],
        'kuota_cuti' => (float)$m['kuota_cuti'],
        'no_hp' => $m['no_hp'],
        'absensi_id' => $abs ? (int)$abs['id'] : null,
        'status_kehadiran' => $statusKehadiran,
        'status_color' => $statusColor,
        'is_mangkir' => $isMangkir,
        'is_revised' => $isRevised,
        'revisi_menjadi' => $revisiMenjadi,
        'catatan' => $catatan,
        'shift_absen' => $abs ? $abs['shift'] : ($m['current_shift'] ?: 'Shift 1')
    ];
}

// Revision types list
$stmtTypes = $pdo->query("SELECT id, kode, nama_cuti, potong_kuota, butuh_lampiran FROM jenis_cuti WHERE kode != 'ALPHA' ORDER BY id ASC");
$revisionTypes = $stmtTypes->fetchAll();

jsonResponse(true, 'Data tim & absensi shift berhasil dimuat', [
    'department' => $department,
    'tanggal' => $tanggal,
    'is_leader_or_spv' => $isLeaderOrSpv,
    'can_manage_shift' => ($isLeaderOrSpv || $isManager || $isHRD),
    'members' => $teamData,
    'revision_types' => $revisionTypes
]);
