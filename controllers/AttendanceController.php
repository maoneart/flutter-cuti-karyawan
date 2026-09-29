<?php
/**
 * Attendance Controller
 * Nakakin Mobile - Shift Management, AWOL/Mangkir, & Attendance Revisions
 * Standard PKB PT. Nakakin Indonesia
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/session.php';

class AttendanceController {

    // View Team & Shift Attendance (Scoped by Department for Leaders & Supervisors)
    public function teamAttendance() {
        requireLogin();
        $currentUser = getCurrentUser();
        $pdo = getDbConnection();

        $userRole = strtolower($currentUser['role'] ?? '');
        $userLevel = (int)($currentUser['level_hierarki'] ?? 1);
        $isLeaderOrSpv = in_array($userRole, ['leader', 'supervisor', 'atasan']) || ($userLevel >= 3 && $userLevel <= 4);
        $isManager = $userRole === 'manager' || ($userLevel >= 5 && $userLevel <= 6);
        $isHRD = in_array($userRole, ['admin', 'superadmin', 'hrd']) || $userLevel >= 7;

        if (!$isLeaderOrSpv && !$isManager && !$isHRD) {
            setFlash('error', 'Akses ditolak! Menu ini khusus untuk Leader, Supervisor, dan Manajemen.');
            header('Location: ' . BASE_URL . '/index.php?page=dashboard');
            exit;
        }

        // Department determination: Strictly locked for Leader/Spv
        if ($isLeaderOrSpv) {
            $deptId = (int)$currentUser['departemen_id'];
        } else {
            // Manager/HRD can choose department or defaults to dept 1
            $deptId = (int)($_GET['dept_id'] ?? $currentUser['departemen_id']);
            if ($deptId <= 0) $deptId = 1;
        }

        $tanggal = cleanInput($_GET['tanggal'] ?? date('Y-m-d'));

        // Get Department info
        $stmtDept = $pdo->prepare("SELECT * FROM departemen WHERE id = ?");
        $stmtDept->execute([$deptId]);
        $department = $stmtDept->fetch();
        if (!$department) {
            $deptId = (int)$currentUser['departemen_id'];
            $stmtDept->execute([$deptId]);
            $department = $stmtDept->fetch();
        }

        // Get all departments for filter (Manager & HRD only)
        $allDepartments = [];
        if ($isManager || $isHRD) {
            $allDepartments = $pdo->query("SELECT * FROM departemen ORDER BY nama_dept ASC")->fetchAll();
        }

        // Fetch team members in this department
        $stmtTeam = $pdo->prepare("
            SELECT e.*, d.nama_dept, d.kode_dept, j.nama_jabatan, j.level_hierarki
            FROM karyawan e
            JOIN departemen d ON e.departemen_id = d.id
            JOIN jabatan j ON e.jabatan_id = j.id
            WHERE e.departemen_id = ?
            ORDER BY j.level_hierarki DESC, e.nama_lengkap ASC
        ");
        $stmtTeam->execute([$deptId]);
        $teamMembers = $stmtTeam->fetchAll();

        // Fetch today's absensi_shift records for this department & date
        $stmtAbs = $pdo->prepare("
            SELECT a.*, l.nama_lengkap AS nama_leader
            FROM absensi_shift a
            JOIN karyawan e ON a.employee_id = e.id
            LEFT JOIN karyawan l ON a.leader_id = l.id
            WHERE e.departemen_id = ? AND a.tanggal = ?
        ");
        $stmtAbs->execute([$deptId, $tanggal]);
        $absensiRecordsRaw = $stmtAbs->fetchAll();
        
        $absensiMap = [];
        foreach ($absensiRecordsRaw as $rec) {
            $absensiMap[$rec['employee_id']] = $rec;
        }

        // Fetch today's approved leave requests
        $stmtLeaves = $pdo->prepare("
            SELECT lr.*, jc.nama_cuti, jc.kode AS kode_cuti
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

        // Available leave types for revision
        $revisionTypes = $pdo->query("SELECT id, kode, nama_cuti, kategori, butuh_lampiran, potong_kuota FROM jenis_cuti WHERE kode != 'ALPHA' ORDER BY id ASC")->fetchAll();

        require __DIR__ . '/../views/attendance/team.php';
    }

    // Record Mangkir (Executed directly by Leader / Spv)
    public function recordMangkir() {
        requireLogin();
        $currentUser = getCurrentUser();
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?page=team-attendance');
            exit;
        }

        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $tanggal = cleanInput($_POST['tanggal'] ?? date('Y-m-d'));
        $shift = cleanInput($_POST['shift'] ?? 'Shift 1');
        $keterangan = cleanInput($_POST['keterangan_mangkir'] ?? 'Mangkir tanpa kabar saat jam shift masuk.');

        if (stripos($shift, 'Shift 2') !== false) {
            $shift = 'Shift 2 (Malam)';
        } else {
            $shift = 'Shift 1 (Pagi)';
        }

        // Security check: verify employee exists and belongs to leader's department
        $stmtEmp = $pdo->prepare("SELECT e.*, d.nama_dept FROM karyawan e JOIN departemen d ON e.departemen_id = d.id WHERE e.id = ?");
        $stmtEmp->execute([$employeeId]);
        $employee = $stmtEmp->fetch();

        if (!$employee) {
            setFlash('error', 'Karyawan tidak ditemukan!');
            header('Location: ' . BASE_URL . '/index.php?page=team-attendance');
            exit;
        }

        $userRole = strtolower($currentUser['role'] ?? '');
        $userLevel = (int)($currentUser['level_hierarki'] ?? 1);
        $isManagement = in_array($userRole, ['admin', 'superadmin', 'hrd', 'manager']) || $userLevel >= 5;

        if (!$isManagement && (int)$currentUser['departemen_id'] !== (int)$employee['departemen_id']) {
            setFlash('error', 'Anda hanya memiliki wewenang untuk mencatat absensi di departemen Anda sendiri!');
            header('Location: ' . BASE_URL . '/index.php?page=team-attendance');
            exit;
        }

        // Insert or update record in absensi_shift
        try {
            $stmt = $pdo->prepare("
                INSERT INTO absensi_shift (employee_id, leader_id, tanggal, shift, status, keterangan_mangkir, status_revisi, hrd_read)
                VALUES (?, ?, ?, ?, 'mangkir', ?, 'original', 0)
                ON DUPLICATE KEY UPDATE 
                    leader_id = VALUES(leader_id),
                    status = 'mangkir',
                    keterangan_mangkir = VALUES(keterangan_mangkir),
                    status_revisi = 'original',
                    direvisi_menjadi = NULL,
                    alasan_revisi = NULL,
                    bukti_lampiran = NULL,
                    direvisi_pada = NULL,
                    hrd_read = 0,
                    updated_at = NOW()
            ");
            $stmt->execute([$employeeId, $currentUser['id'], $tanggal, $shift, $keterangan]);

            setFlash('success', 'Status Mangkir berhasil dicatat untuk ' . htmlspecialchars($employee['nama_lengkap']) . ' pada ' . $shift . '. Notifikasi tembusan otomatis terkirim ke HRD.');
        } catch (PDOException $e) {
            setFlash('error', 'Gagal mencatat mangkir: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/index.php?page=team-attendance&tanggal=' . $tanggal);
        exit;
    }

    // Revise Attendance (Executed directly by Leader / Spv when doctor letter / notice follows)
    public function reviseAttendance() {
        requireLogin();
        $currentUser = getCurrentUser();
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?page=team-attendance');
            exit;
        }

        $absensiId = (int)($_POST['absensi_id'] ?? 0);
        $direvisiMenjadi = cleanInput($_POST['direvisi_menjadi'] ?? 'SD');
        $alasanRevisi = cleanInput($_POST['alasan_revisi'] ?? '');
        $tanggal = cleanInput($_POST['tanggal'] ?? date('Y-m-d'));

        // Fetch existing record
        $stmtAbs = $pdo->prepare("
            SELECT a.*, e.nama_lengkap, e.departemen_id, e.sisa_cuti, e.cuti_terpakai 
            FROM absensi_shift a
            JOIN karyawan e ON a.employee_id = e.id
            WHERE a.id = ?
        ");
        $stmtAbs->execute([$absensiId]);
        $record = $stmtAbs->fetch();

        if (!$record) {
            setFlash('error', 'Data absensi tidak ditemukan!');
            header('Location: ' . BASE_URL . '/index.php?page=team-attendance');
            exit;
        }

        $userRole = strtolower($currentUser['role'] ?? '');
        $userLevel = (int)($currentUser['level_hierarki'] ?? 1);
        $isManagement = in_array($userRole, ['admin', 'superadmin', 'hrd', 'manager']) || $userLevel >= 5;

        if (!$isManagement && (int)$currentUser['departemen_id'] !== (int)$record['departemen_id']) {
            setFlash('error', 'Akses ditolak! Anda hanya berwenang merevisi data di departemen Anda.');
            header('Location: ' . BASE_URL . '/index.php?page=team-attendance');
            exit;
        }

        // Fetch leave type info
        $stmtType = $pdo->prepare("SELECT * FROM jenis_cuti WHERE kode = ?");
        $stmtType->execute([$direvisiMenjadi]);
        $leaveType = $stmtType->fetch();

        // Handle attachment upload if provided
        $attachmentName = null;
        if (!empty($_FILES['bukti_lampiran']['name'])) {
            $file = $_FILES['bukti_lampiran'];
            $allowedExts = ['jpg', 'jpeg', 'png', 'pdf'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExts)) {
                setFlash('error', 'Format berkas bukti tidak didukung! Harap unggah format JPG, PNG, atau PDF.');
                header('Location: ' . BASE_URL . '/index.php?page=team-attendance&tanggal=' . $tanggal);
                exit;
            }

            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $attachmentName = 'REVISI_' . time() . '_' . rand(100, 999) . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $attachmentName)) {
                $attachmentName = null;
            }
        }

        try {
            $pdo->beginTransaction();

            $newStatus = strtolower($direvisiMenjadi);
            $stmtUpdate = $pdo->prepare("
                UPDATE absensi_shift 
                SET status = ?,
                    status_revisi = 'revised',
                    direvisi_menjadi = ?,
                    alasan_revisi = ?,
                    bukti_lampiran = COALESCE(?, bukti_lampiran),
                    direvisi_pada = NOW(),
                    hrd_read = 0
                WHERE id = ?
            ");
            $stmtUpdate->execute([$newStatus, $direvisiMenjadi, $alasanRevisi, $attachmentName, $absensiId]);

            // If revised to a type that deducts leave quota (e.g. CT, CT-HALF, ST)
            if ($leaveType && (int)$leaveType['potong_kuota'] === 1) {
                $potongHari = ($direvisiMenjadi === 'CT-HALF') ? 0.5 : 1.0;
                
                $sisaBaru = max(0, (float)$record['sisa_cuti'] - $potongHari);
                $terpakaiBaru = (float)$record['cuti_terpakai'] + $potongHari;

                $stmtKaryawan = $pdo->prepare("UPDATE karyawan SET sisa_cuti = ?, cuti_terpakai = ? WHERE id = ?");
                $stmtKaryawan->execute([$sisaBaru, $terpakaiBaru, $record['employee_id']]);

                // Record in riwayat_kuota_cuti
                $stmtHist = $pdo->prepare("
                    INSERT INTO riwayat_kuota_cuti (employee_id, kuota_sebelum, perubahan, kuota_sesudah, tipe, keterangan, created_by)
                    VALUES (?, ?, ?, ?, 'potong_cuti', ?, ?)
                ");
                $stmtHist->execute([
                    $record['employee_id'],
                    $record['sisa_cuti'],
                    -$potongHari,
                    $sisaBaru,
                    'Revisi Mangkir menjadi ' . $leaveType['nama_cuti'] . ' oleh Leader ' . $currentUser['nama_lengkap'],
                    $currentUser['id']
                ]);
            }

            $pdo->commit();
            setFlash('success', 'Status absensi untuk ' . htmlspecialchars($record['nama_lengkap']) . ' berhasil direvisi menjadi ' . htmlspecialchars($leaveType['nama_cuti'] ?? $direvisiMenjadi) . '!');
        } catch (Exception $e) {
            $pdo->rollBack();
            setFlash('error', 'Gagal merevisi absensi: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/index.php?page=team-attendance&tanggal=' . $tanggal);
        exit;
    }

    // Toggle / Update Individual Shift for a Team Member
    public function updateShift() {
        requireLogin();
        $currentUser = getCurrentUser();
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?page=team-attendance');
            exit;
        }

        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $newShift = cleanInput($_POST['shift'] ?? 'Shift 1');
        $tanggal = cleanInput($_POST['tanggal'] ?? date('Y-m-d'));

        if (stripos($newShift, 'Shift 2') !== false) {
            $newShift = 'Shift 2 (Malam)';
        } else {
            $newShift = 'Shift 1 (Pagi)';
        }

        $stmt = $pdo->prepare("UPDATE karyawan SET current_shift = ? WHERE id = ?");
        $stmt->execute([$newShift, $employeeId]);

        setFlash('success', 'Jadwal shift karyawan berhasil diperbarui menjadi ' . $newShift . '.');
        header('Location: ' . BASE_URL . '/index.php?page=team-attendance&tanggal=' . $tanggal);
        exit;
    }

    // Weekly Rolling Shift for Department (Shift 1 <-> Shift 2)
    public function rollingShift() {
        requireLogin();
        $currentUser = getCurrentUser();
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?page=team-attendance');
            exit;
        }

        $deptId = (int)($_POST['dept_id'] ?? $currentUser['departemen_id']);
        $tanggal = cleanInput($_POST['tanggal'] ?? date('Y-m-d'));

        $stmt = $pdo->prepare("
            UPDATE karyawan 
            SET current_shift = CASE 
                WHEN current_shift LIKE '%Shift 1%' THEN 'Shift 2 (Malam)' 
                ELSE 'Shift 1 (Pagi)' 
            END 
            WHERE departemen_id = ? AND role IN ('operator', 'staff')
        ");
        $stmt->execute([$deptId]);
        $affected = $stmt->rowCount();

        setFlash('success', 'Rolling shift mingguan berhasil diterapkan untuk ' . $affected . ' operator di departemen ini!');
        header('Location: ' . BASE_URL . '/index.php?page=team-attendance&tanggal=' . $tanggal);
        exit;
    }

    // HRD & Management Attendance Log & Mangkir Summary
    public function rekapAttendance() {
        requireRole('admin');
        $pdo = getDbConnection();

        $deptFilter = (int)($_GET['dept_id'] ?? 0);
        $startDate = cleanInput($_GET['start_date'] ?? date('Y-m-01'));
        $endDate = cleanInput($_GET['end_date'] ?? date('Y-m-d'));
        $statusFilter = cleanInput($_GET['status'] ?? '');

        $departments = $pdo->query("SELECT * FROM departemen ORDER BY nama_dept ASC")->fetchAll();

        $sql = "
            SELECT a.*, e.nik, e.nama_lengkap, e.no_hp, d.nama_dept, d.kode_dept, j.nama_jabatan, l.nama_lengkap AS nama_leader
            FROM absensi_shift a
            JOIN karyawan e ON a.employee_id = e.id
            JOIN departemen d ON e.departemen_id = d.id
            JOIN jabatan j ON e.jabatan_id = j.id
            LEFT JOIN karyawan l ON a.leader_id = l.id
            WHERE a.tanggal BETWEEN ? AND ?
        ";
        $params = [$startDate, $endDate];

        if ($deptFilter > 0) {
            $sql .= " AND e.departemen_id = ? ";
            $params[] = $deptFilter;
        }

        if (!empty($statusFilter)) {
            $sql .= " AND a.status = ? ";
            $params[] = $statusFilter;
        }

        $sql .= " ORDER BY a.tanggal DESC, a.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll();

        // Mark as read by HRD
        $pdo->query("UPDATE absensi_shift SET hrd_read = 1 WHERE hrd_read = 0");

        require __DIR__ . '/../views/attendance/rekap.php';
    }
}
