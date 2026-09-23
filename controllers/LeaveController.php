<?php
/**
 * Leave Controller
 * PT. Nakakin Indonesia Leave Management System
 * 3-Tier Multi-Level Approval System (Synchronized with Mobile & API)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/session.php';

class LeaveController {
    
    // Submit new leave application
    public function submit() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?page=leave-create');
            exit;
        }

        $pdo = getDbConnection();
        $currentUser = getCurrentUser();
        $hierarki = (int)($currentUser['level_hierarki'] ?? 1);
        
        // Check if Admin/HRD is submitting on behalf of an absent employee
        $targetEmpId = (int)($_POST['target_employee_id'] ?? 0);
        $isAdmin = ($currentUser['role'] === 'superadmin' || $currentUser['role'] === 'admin' || $currentUser['role'] === 'hrd' || $hierarki >= 7);
        $isAdminOnBehalf = ($isAdmin && $targetEmpId > 0 && $targetEmpId !== $currentUser['id']);
        
        $employeeId = $isAdminOnBehalf ? $targetEmpId : $currentUser['id'];

        // Fetch employee data
        $stmtTargetEmp = $pdo->prepare("SELECT e.*, d.nama_dept, p.nama_jabatan, p.level_hierarki FROM karyawan e JOIN departemen d ON e.departemen_id = d.id JOIN jabatan p ON e.jabatan_id = p.id WHERE e.id = ?");
        $stmtTargetEmp->execute([$employeeId]);
        $targetEmp = $stmtTargetEmp->fetch();

        if (!$targetEmp) {
            setFlash('error', 'Data karyawan tidak ditemukan!');
            header('Location: ' . BASE_URL . '/index.php?page=leave-create');
            exit;
        }

        $leaveTypeId = (int)($_POST['leave_type_id'] ?? 0);
        $startDate = cleanInput($_POST['tanggal_mulai'] ?? '');
        $endDate = cleanInput($_POST['tanggal_selesai'] ?? '');
        $totalDays = (int)($_POST['total_hari'] ?? 0);
        $reason = cleanInput($_POST['alasan'] ?? '');
        $addressDuringLeave = cleanInput($_POST['alamat_selama_cuti'] ?? ($targetEmp['alamat'] ?? '-'));
        $emergencyContact = cleanInput($_POST['kontak_darurat'] ?? ($targetEmp['no_hp'] ?? '-'));
        $directApprove = ($isAdmin && isset($_POST['direct_approve']) && $_POST['direct_approve'] == '1');

        if (!$leaveTypeId || !$startDate || !$endDate || $totalDays <= 0 || empty($reason)) {
            setFlash('error', 'Mohon lengkapi semua data formulir permohonan cuti!');
            header('Location: ' . BASE_URL . '/index.php?page=leave-create');
            exit;
        }

        // Get leave type info
        $stmtType = $pdo->prepare("SELECT * FROM jenis_cuti WHERE id = ?");
        $stmtType->execute([$leaveTypeId]);
        $leaveType = $stmtType->fetch();

        if (!$leaveType) {
            setFlash('error', 'Jenis cuti yang dipilih tidak valid!');
            header('Location: ' . BASE_URL . '/index.php?page=leave-create');
            exit;
        }

        // Check quota if type deducts annual quota
        if ($leaveType['potong_kuota'] == 1) {
            if ($totalDays > $targetEmp['sisa_cuti']) {
                setFlash('error', "Sisa kuota cuti tahunan karyawan {$targetEmp['nama_lengkap']} tidak mencukupi! (Sisa: {$targetEmp['sisa_cuti']} hari, Diajukan: {$totalDays} hari)");
                header('Location: ' . BASE_URL . '/index.php?page=leave-create');
                exit;
            }
        }

        // Handle attachment file upload
        $attachmentName = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['attachment'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($fileExt, $allowedExtensions)) {
                setFlash('error', 'Format file lampiran tidak didukung! Hanya diperbolehkan format JPG, PNG, atau PDF.');
                header('Location: ' . BASE_URL . '/index.php?page=leave-create');
                exit;
            }

            if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
                setFlash('error', 'Ukuran file lampiran melebihi batas maksimal 5MB!');
                header('Location: ' . BASE_URL . '/index.php?page=leave-create');
                exit;
            }

            $uploadDir = __DIR__ . '/../assets/uploads/leaves/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $attachmentName = 'LEAVE_' . time() . '_' . uniqid() . '.' . $fileExt;
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $attachmentName)) {
                setFlash('error', 'Gagal mengunggah file lampiran!');
                header('Location: ' . BASE_URL . '/index.php?page=leave-create');
                exit;
            }
        }

        // Generate Nomor Surat
        $year = date('Y');
        $month = date('m');
        $stmtCount = $pdo->query("SELECT COUNT(*) FROM pengajuan_cuti WHERE YEAR(created_at) = '$year'");
        $countThisYear = (int)$stmtCount->fetchColumn() + 1;
        $nomorSurat = sprintf("CUTI/NAK/%s/%s/%03d", $year, $month, $countThisYear);

        // Determine Initial Approval Step based on Applicant Hierarchy
        $empHierarki = (int)($targetEmp['level_hierarki'] ?? 1);
        $initialStep = 'pending_spv';
        $initialStatus = 'pending';

        if ($directApprove || $isAdmin || $empHierarki >= 7) {
            $initialStep = 'approved';
            $initialStatus = 'approved';
        } elseif ($targetEmp['role'] === 'manager' || $empHierarki >= 5) {
            $initialStep = 'pending_hrd';
            $initialStatus = 'pending';
        } elseif ($targetEmp['role'] === 'leader' || $targetEmp['role'] === 'supervisor' || $empHierarki >= 3) {
            $initialStep = 'pending_manager';
            $initialStatus = 'pending';
        } else {
            $initialStep = 'pending_spv';
            $initialStatus = 'pending';
        }

        $pdo->beginTransaction();
        try {
            if ($initialStatus === 'approved') {
                // Direct Auto-Approval
                $stmtInsert = $pdo->prepare("
                    INSERT INTO pengajuan_cuti (
                        nomor_surat, employee_id, leave_type_id, 
                        tanggal_mulai, tanggal_selesai, total_hari, 
                        alasan, alamat_selama_cuti, kontak_darurat, 
                        attachment, status, approval_step, approved_by, approved_at, catatan_atasan, created_at
                    ) VALUES (
                        ?, ?, ?, 
                        ?, ?, ?, 
                        ?, ?, ?, 
                        ?, 'approved', 'approved', ?, NOW(), 'Disetujui langsung oleh HRD/Sistem', NOW()
                    )
                ");
                $stmtInsert->execute([
                    $nomorSurat, $employeeId, $leaveTypeId,
                    $startDate, $endDate, $totalDays,
                    $reason, $addressDuringLeave, $emergencyContact,
                    $attachmentName, $currentUser['id']
                ]);

                // Deduct quota if applicable
                if ($leaveType['potong_kuota'] == 1) {
                    $kuotaSebelum = (int)$targetEmp['sisa_cuti'];
                    $kuotaSesudah = max(0, $kuotaSebelum - $totalDays);

                    $stmtUpdateQuota = $pdo->prepare("
                        UPDATE karyawan 
                        SET cuti_terpakai = cuti_terpakai + ?, sisa_cuti = sisa_cuti - ?
                        WHERE id = ?
                    ");
                    $stmtUpdateQuota->execute([$totalDays, $totalDays, $employeeId]);

                    $stmtLog = $pdo->prepare("
                        INSERT INTO riwayat_kuota_cuti (
                            employee_id, kuota_sebelum, perubahan, kuota_sesudah, 
                            tipe, keterangan, created_by, created_at
                        ) VALUES (
                            ?, ?, ?, ?, 
                            'potong_cuti', ?, ?, NOW()
                        )
                    ");
                    $stmtLog->execute([
                        $employeeId, $kuotaSebelum, -$totalDays, $kuotaSesudah,
                        "Input cuti {$nomorSurat} oleh HRD ({$leaveType['nama_cuti']} - {$totalDays} hari)",
                        $currentUser['id']
                    ]);
                }

                $pdo->commit();
                setFlash('success', "Data cuti ({$nomorSurat}) untuk karyawan {$targetEmp['nama_lengkap']} berhasil disimpan dan diverifikasi!");
                header('Location: ' . BASE_URL . '/index.php?page=leaves-team');
                exit;

            } else {
                // Regular submission
                $stmtInsert = $pdo->prepare("
                    INSERT INTO pengajuan_cuti (
                        nomor_surat, employee_id, leave_type_id, 
                        tanggal_mulai, tanggal_selesai, total_hari, 
                        alasan, alamat_selama_cuti, kontak_darurat, 
                        attachment, status, approval_step, created_at
                    ) VALUES (
                        ?, ?, ?, 
                        ?, ?, ?, 
                        ?, ?, ?, 
                        ?, 'pending', ?, NOW()
                    )
                ");
                $stmtInsert->execute([
                    $nomorSurat, $employeeId, $leaveTypeId,
                    $startDate, $endDate, $totalDays,
                    $reason, $addressDuringLeave, $emergencyContact,
                    $attachmentName, $initialStep
                ]);

                $pdo->commit();
                if ($isAdminOnBehalf) {
                    setFlash('success', "Permohonan cuti ({$nomorSurat}) untuk karyawan {$targetEmp['nama_lengkap']} berhasil dibuat dan menunggu persetujuan!");
                    header('Location: ' . BASE_URL . '/index.php?page=leaves-team');
                } else {
                    setFlash('success', "Permohonan cuti ({$nomorSurat}) berhasil diajukan dan sedang menunggu persetujuan alur hierarki!");
                    header('Location: ' . BASE_URL . '/index.php?page=leaves-my');
                }
                exit;
            }

        } catch (Exception $e) {
            $pdo->rollBack();
            setFlash('error', 'Terjadi kesalahan sistem saat menyimpan pengajuan cuti: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/index.php?page=leave-create');
            exit;
        }
    }

    // Approve Leave Request (Multi-Tier Hierarchical Approval)
    public function approve() {
        requireLogin();
        
        $leaveId = (int)($_GET['id'] ?? 0);
        $notes = cleanInput($_GET['note'] ?? ($_POST['note'] ?? ''));
        $currentUser = getCurrentUser();
        $pdo = getDbConnection();
        $hierarki = (int)($currentUser['level_hierarki'] ?? 1);

        $stmt = $pdo->prepare("
            SELECT lr.*, e.departemen_id, e.nama_lengkap, e.sisa_cuti, e.cuti_terpakai, lt.potong_kuota, lt.nama_cuti,
                   j.level_hierarki as employee_level
            FROM pengajuan_cuti lr
            JOIN karyawan e ON lr.employee_id = e.id
            JOIN jabatan j ON e.jabatan_id = j.id
            JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
            WHERE lr.id = ?
        ");
        $stmt->execute([$leaveId]);
        $leave = $stmt->fetch();

        if (!$leave) {
            setFlash('error', 'Data permohonan cuti tidak ditemukan!');
            header('Location: ' . BASE_URL . '/index.php?page=leave-approvals');
            exit;
        }

        if ($leave['status'] !== 'pending') {
            setFlash('warning', 'Pengajuan cuti ini sudah diproses sebelumnya!');
            header('Location: ' . BASE_URL . '/index.php?page=leave-detail&id=' . $leaveId);
            exit;
        }

        $currentStep = $leave['approval_step'] ?? 'pending_spv';

        // Authorization check: Plant Manager & HRD across all depts, Spv/Leader for own dept
        $isHRD = ($currentUser['role'] === 'superadmin' || $currentUser['role'] === 'admin' || $currentUser['role'] === 'hrd' || $hierarki >= 7);
        $isManager = ($currentUser['role'] === 'manager' || ($hierarki >= 5 && $hierarki <= 6));
        $isSpv = ($currentUser['role'] === 'supervisor' || $currentUser['role'] === 'leader' || $hierarki >= 3);

        if (!$isHRD && !$isManager && ($currentUser['departemen_id'] != $leave['departemen_id'])) {
            setFlash('error', 'Akses ditolak! Anda hanya dapat memproses cuti dari departemen Anda.');
            header('Location: ' . BASE_URL . '/index.php?page=leave-approvals');
            exit;
        }

        $pdo->beginTransaction();
        try {
            if ($currentStep === 'pending_spv') {
                if (!$isSpv && !$isManager && !$isHRD) {
                    throw new Exception('Anda tidak memiliki wewenang untuk persetujuan Spv.');
                }
                $stmtUp = $pdo->prepare("
                    UPDATE pengajuan_cuti 
                    SET spv_id = ?, spv_at = NOW(), spv_notes = ?, approval_step = 'pending_manager' 
                    WHERE id = ?
                ");
                $stmtUp->execute([$currentUser['id'], $notes, $leaveId]);

            } elseif ($currentStep === 'pending_manager') {
                if (!$isManager && !$isHRD) {
                    throw new Exception('Persetujuan ini memerlukan wewenang Plant Manager.');
                }
                $stmtUp = $pdo->prepare("
                    UPDATE pengajuan_cuti 
                    SET manager_id = ?, manager_at = NOW(), manager_notes = ?, approval_step = 'pending_hrd' 
                    WHERE id = ?
                ");
                $stmtUp->execute([$currentUser['id'], $notes, $leaveId]);

            } elseif ($currentStep === 'pending_hrd') {
                if (!$isHRD) {
                    throw new Exception('Persetujuan final ini memerlukan wewenang HRD.');
                }
                $stmtUp = $pdo->prepare("
                    UPDATE pengajuan_cuti 
                    SET hrd_id = ?, hrd_at = NOW(), hrd_notes = ?, approval_step = 'approved',
                        status = 'approved', approved_by = ?, approved_at = NOW(), catatan_atasan = ?
                    WHERE id = ?
                ");
                $stmtUp->execute([$currentUser['id'], $notes, $currentUser['id'], $notes, $leaveId]);

                // Deduct Quota on Final HRD Approval
                if ($leave['potong_kuota'] == 1) {
                    $kuotaSebelum = (int)$leave['sisa_cuti'];
                    $perubahan = -(int)$leave['total_hari'];
                    $kuotaSesudah = max(0, $kuotaSebelum - (int)$leave['total_hari']);

                    $stmtUpdateEmp = $pdo->prepare("
                        UPDATE karyawan 
                        SET cuti_terpakai = cuti_terpakai + ?, sisa_cuti = sisa_cuti - ?
                        WHERE id = ?
                    ");
                    $stmtUpdateEmp->execute([$leave['total_hari'], $leave['total_hari'], $leave['employee_id']]);

                    $stmtLog = $pdo->prepare("
                        INSERT INTO riwayat_kuota_cuti (
                            employee_id, kuota_sebelum, perubahan, kuota_sesudah, 
                            tipe, keterangan, created_by, created_at
                        ) VALUES (
                            ?, ?, ?, ?, 
                            'potong_cuti', ?, ?, NOW()
                        )
                    ");
                    $logDesc = "Persetujuan final cuti ({$leave['nomor_surat']}) oleh HRD ({$currentUser['nama_lengkap']})";
                    $stmtLog->execute([$leave['employee_id'], $kuotaSebelum, $perubahan, $kuotaSesudah, $logDesc, $currentUser['id']]);
                }
            }

            $pdo->commit();
            setFlash('success', "Persetujuan tahap {$currentStep} untuk {$leave['nama_lengkap']} berhasil diproses!");
        } catch (Exception $e) {
            $pdo->rollBack();
            setFlash('error', 'Gagal memproses persetujuan cuti: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/index.php?page=leave-approvals');
        exit;
    }

    // Reject Leave Request
    public function reject() {
        requireLogin();

        $leaveId = (int)($_GET['id'] ?? 0);
        $reason = cleanInput($_GET['reason'] ?? ($_POST['reason'] ?? ''));
        $currentUser = getCurrentUser();
        $pdo = getDbConnection();
        $hierarki = (int)($currentUser['level_hierarki'] ?? 1);

        if (empty($reason)) {
            setFlash('error', 'Alasan penolakan cuti wajib diisi!');
            header('Location: ' . BASE_URL . '/index.php?page=leave-approvals');
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT lr.*, e.departemen_id, e.nama_lengkap 
            FROM pengajuan_cuti lr
            JOIN karyawan e ON lr.employee_id = e.id
            WHERE lr.id = ?
        ");
        $stmt->execute([$leaveId]);
        $leave = $stmt->fetch();

        if (!$leave) {
            setFlash('error', 'Data permohonan cuti tidak ditemukan!');
            header('Location: ' . BASE_URL . '/index.php?page=leave-approvals');
            exit;
        }

        $isHRD = ($currentUser['role'] === 'superadmin' || $currentUser['role'] === 'admin' || $currentUser['role'] === 'hrd' || $hierarki >= 7);
        $isManager = ($currentUser['role'] === 'manager' || ($hierarki >= 5 && $hierarki <= 6));

        if (!$isHRD && !$isManager && ($currentUser['departemen_id'] != $leave['departemen_id'])) {
            setFlash('error', 'Akses ditolak!');
            header('Location: ' . BASE_URL . '/index.php?page=dashboard');
            exit;
        }

        $stmtReject = $pdo->prepare("
            UPDATE pengajuan_cuti 
            SET status = 'rejected', approval_step = 'rejected', rejection_reason = ?, approved_by = ?, approved_at = NOW()
            WHERE id = ?
        ");
        $stmtReject->execute([$reason, $currentUser['id'], $leaveId]);

        setFlash('success', "Permohonan cuti {$leave['nama_lengkap']} telah ditolak.");
        header('Location: ' . BASE_URL . '/index.php?page=leave-approvals');
        exit;
    }
}
