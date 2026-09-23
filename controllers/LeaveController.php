<?php
/**
 * Leave Controller
 * PT. Nakakin Indonesia Leave Management System
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
        
        // Check if Admin/HRD is submitting on behalf of an absent employee
        $targetEmpId = (int)($_POST['target_employee_id'] ?? 0);
        $isAdminOnBehalf = ($currentUser['role'] === 'admin' && $targetEmpId > 0 && $targetEmpId !== $currentUser['id']);
        
        $employeeId = $isAdminOnBehalf ? $targetEmpId : $currentUser['id'];

        // Fetch employee data
        $stmtTargetEmp = $pdo->prepare("SELECT e.*, d.nama_dept, p.nama_jabatan FROM karyawan e JOIN departemen d ON e.departemen_id = d.id JOIN jabatan p ON e.jabatan_id = p.id WHERE e.id = ?");
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
        $directApprove = ($currentUser['role'] === 'admin' && isset($_POST['direct_approve']) && $_POST['direct_approve'] == '1');

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

            $uploadDir = __DIR__ . '/../assets/uploads/attachments/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $attachmentName = 'att_' . time() . '_' . uniqid() . '.' . $fileExt;
            $destination = $uploadDir . $attachmentName;

            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                setFlash('error', 'Gagal mengunggah file lampiran!');
                header('Location: ' . BASE_URL . '/index.php?page=leave-create');
                exit;
            }
        } else if ($leaveType['butuh_lampiran'] == 1 && !$isAdminOnBehalf) {
            setFlash('error', 'Permohonan jenis cuti ini wajib melampirkan file Surat Keterangan Dokter asli!');
            header('Location: ' . BASE_URL . '/index.php?page=leave-create');
            exit;
        }

        // Generate Letter Number
        $nomorSurat = generateNomorSurat($pdo);

        $pdo->beginTransaction();
        try {
            if ($directApprove) {
                // Direct Approval by HRD based on absence audit
                $stmtInsert = $pdo->prepare("
                    INSERT INTO pengajuan_cuti (
                        nomor_surat, employee_id, leave_type_id, 
                        tanggal_mulai, tanggal_selesai, total_hari, 
                        alasan, alamat_selama_cuti, kontak_darurat, 
                        attachment, status, approved_by, approved_at, catatan_atasan, created_at
                    ) VALUES (
                        ?, ?, ?, 
                        ?, ?, ?, 
                        ?, ?, ?, 
                        ?, 'approved', ?, NOW(), 'Diinput & disetujui langsung oleh HRD berdasarkan rekap absensi / ketidakhadiran', NOW()
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
                        "Input cuti {$nomorSurat} oleh HRD (Rekap Absensi: {$leaveType['nama_cuti']} - {$totalDays} hari)",
                        $currentUser['id']
                    ]);
                }

                $pdo->commit();
                setFlash('success', "Data cuti ({$nomorSurat}) untuk karyawan {$targetEmp['nama_lengkap']} berhasil diinput dan diverifikasi langsung oleh HRD!");
                header('Location: ' . BASE_URL . '/index.php?page=leaves-team');
                exit;

            } else {
                // Regular submission
                $stmtInsert = $pdo->prepare("
                    INSERT INTO pengajuan_cuti (
                        nomor_surat, employee_id, leave_type_id, 
                        tanggal_mulai, tanggal_selesai, total_hari, 
                        alasan, alamat_selama_cuti, kontak_darurat, 
                        attachment, status, created_at
                    ) VALUES (
                        ?, ?, ?, 
                        ?, ?, ?, 
                        ?, ?, ?, 
                        ?, 'pending', NOW()
                    )
                ");
                $stmtInsert->execute([
                    $nomorSurat, $employeeId, $leaveTypeId,
                    $startDate, $endDate, $totalDays,
                    $reason, $addressDuringLeave, $emergencyContact,
                    $attachmentName
                ]);

                $pdo->commit();
                if ($isAdminOnBehalf) {
                    setFlash('success', "Permohonan cuti ({$nomorSurat}) untuk karyawan {$targetEmp['nama_lengkap']} berhasil dibuat dan menunggu persetujuan atasan!");
                    header('Location: ' . BASE_URL . '/index.php?page=leaves-team');
                } else {
                    setFlash('success', "Permohonan cuti ({$nomorSurat}) berhasil diajukan dan sedang menunggu persetujuan atasan!");
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

    // Approve Leave Request
    public function approve() {
        requireLogin();
        
        $leaveId = (int)($_GET['id'] ?? 0);
        $catatan = cleanInput($_GET['note'] ?? '');
        $currentUser = getCurrentUser();
        $pdo = getDbConnection();

        // Get leave request with employee & leave type details
        $stmt = $pdo->prepare("
            SELECT lr.*, e.departemen_id, e.nama_lengkap, e.sisa_cuti, e.cuti_terpakai, lt.potong_kuota, lt.nama_cuti
            FROM pengajuan_cuti lr
            JOIN karyawan e ON lr.employee_id = e.id
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

        // Authorization check: Admin OR Atasan in the SAME department
        $isAuthorized = ($currentUser['role'] === 'admin') || 
                         ($currentUser['role'] === 'atasan' && $currentUser['departemen_id'] == $leave['departemen_id']);

        if (!$isAuthorized || $currentUser['id'] == $leave['employee_id']) {
            setFlash('error', 'Akses ditolak! Anda tidak memiliki wewenang untuk menyetujui pengajuan cuti ini.');
            header('Location: ' . BASE_URL . '/index.php?page=dashboard');
            exit;
        }

        if ($leave['status'] !== 'pending') {
            setFlash('warning', 'Pengajuan cuti ini sudah diproses sebelumnya!');
            header('Location: ' . BASE_URL . '/index.php?page=leave-detail&id=' . $leaveId);
            exit;
        }

        // Begin transaction to guarantee quota integrity
        $pdo->beginTransaction();
        try {
            // Update leave request status
            $stmtApprove = $pdo->prepare("
                UPDATE pengajuan_cuti 
                SET status = 'approved', approved_by = ?, approved_at = NOW(), catatan_atasan = ?
                WHERE id = ?
            ");
            $stmtApprove->execute([$currentUser['id'], $catatan, $leaveId]);

            // If leave type deducts quota, update employee table and log quota history
            if ($leave['potong_kuota'] == 1) {
                $kuotaSebelum = $leave['sisa_cuti'];
                $perubahan = -$leave['total_hari'];
                $kuotaSesudah = max(0, $kuotaSebelum - $leave['total_hari']);

                $stmtUpdateEmp = $pdo->prepare("
                    UPDATE karyawan 
                    SET cuti_terpakai = cuti_terpakai + ?, sisa_cuti = sisa_cuti - ?
                    WHERE id = ?
                ");
                $stmtUpdateEmp->execute([$leave['total_hari'], $leave['total_hari'], $leave['employee_id']]);

                // Insert into quota history
                $stmtLog = $pdo->prepare("
                    INSERT INTO riwayat_kuota_cuti (
                        employee_id, kuota_sebelum, perubahan, kuota_sesudah, 
                        tipe, keterangan, created_by, created_at
                    ) VALUES (
                        ?, ?, ?, ?, 
                        'potong_cuti', ?, ?, NOW()
                    )
                ");
                $logDesc = "Pemotongan cuti disetujui ({$leave['nomor_surat']}) oleh " . $currentUser['nama_lengkap'];
                $stmtLog->execute([$leave['employee_id'], $kuotaSebelum, $perubahan, $kuotaSesudah, $logDesc, $currentUser['id']]);
            }

            $pdo->commit();
            setFlash('success', "Permohonan cuti {$leave['nama_lengkap']} berhasil disetujui!");
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
        $reason = cleanInput($_GET['reason'] ?? '');
        $currentUser = getCurrentUser();
        $pdo = getDbConnection();

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

        $isAuthorized = ($currentUser['role'] === 'admin') || 
                         ($currentUser['role'] === 'atasan' && $currentUser['departemen_id'] == $leave['departemen_id']);

        if (!$isAuthorized || $currentUser['id'] == $leave['employee_id']) {
            setFlash('error', 'Akses ditolak!');
            header('Location: ' . BASE_URL . '/index.php?page=dashboard');
            exit;
        }

        $stmtReject = $pdo->prepare("
            UPDATE pengajuan_cuti 
            SET status = 'rejected', approved_by = ?, approved_at = NOW(), rejection_reason = ?
            WHERE id = ?
        ");
        $stmtReject->execute([$currentUser['id'], $reason, $leaveId]);

        setFlash('success', "Permohonan cuti {$leave['nama_lengkap']} telah ditolak.");
        header('Location: ' . BASE_URL . '/index.php?page=leave-approvals');
        exit;
    }

    // Cancel Leave Request (by Requester)
    public function cancel() {
        requireLogin();

        $leaveId = (int)($_GET['id'] ?? 0);
        $currentUser = getCurrentUser();
        $pdo = getDbConnection();

        $stmt = $pdo->prepare("SELECT * FROM pengajuan_cuti WHERE id = ? AND employee_id = ? AND status = 'pending'");
        $stmt->execute([$leaveId, $currentUser['id']]);
        $leave = $stmt->fetch();

        if (!$leave) {
            setFlash('error', 'Pengajuan cuti tidak ditemukan atau sudah diproses sehingga tidak dapat dibatalkan.');
            header('Location: ' . BASE_URL . '/index.php?page=leaves-my');
            exit;
        }

        $stmtCancel = $pdo->prepare("UPDATE pengajuan_cuti SET status = 'cancelled' WHERE id = ?");
        $stmtCancel->execute([$leaveId]);

        setFlash('success', 'Pengajuan cuti Anda telah berhasil dibatalkan.');
        header('Location: ' . BASE_URL . '/index.php?page=leaves-my');
        exit;
    }
}
