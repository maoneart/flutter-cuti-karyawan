<?php
/**
 * API Process 3-Tier Hierarchical Approval Action
 * POST /api/approvals/action.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Metode HTTP tidak diizinkan. Gunakan POST.', null, 405);
}

$user = authenticateApiUser();
requireApiRole(['atasan', 'admin'], $user);

$pdo = getDbConnection();
$input = getApiRequestData();

$leaveId = (int)($input['id'] ?? 0);
$action = strtolower(trim($input['action'] ?? '')); // 'approve' or 'reject'
$notes = trim($input['notes'] ?? '');
$rejectionReason = trim($input['rejection_reason'] ?? $notes);
$hierarki = (int)($user['level_hierarki'] ?? 1);

if ($leaveId <= 0 || !in_array($action, ['approve', 'reject'], true)) {
    jsonResponse(false, 'Data parameter aksi atau ID pengajuan tidak valid.', null, 400);
}

if ($action === 'reject' && empty($rejectionReason)) {
    jsonResponse(false, 'Alasan penolakan wajib diisi.', null, 422, [
        'rejection_reason' => 'Alasan penolakan tidak boleh kosong'
    ]);
}

// Fetch Leave
$stmt = $pdo->prepare("
    SELECT p.*, k.departemen_id, k.sisa_cuti, k.cuti_terpakai, k.nama_lengkap, j.potong_kuota, j.nama_cuti
    FROM pengajuan_cuti p
    JOIN karyawan k ON p.employee_id = k.id
    JOIN jenis_cuti j ON p.leave_type_id = j.id
    WHERE p.id = ?
    LIMIT 1
");
$stmt->execute([$leaveId]);
$leave = $stmt->fetch();

if (!$leave) {
    jsonResponse(false, 'Pengajuan cuti tidak ditemukan.', null, 404);
}

if ($leave['status'] !== 'pending') {
    jsonResponse(false, "Pengajuan ini sudah selesai diproses dengan status: {$leave['status']}.", null, 400);
}

$currentStep = $leave['approval_step'] ?? 'pending_spv';

// Security check: HRD/Admin and Plant Manager can process approvals across all departments
// Leader / Spv (level 3-4) can only process approvals for their own department
if ($user['role'] !== 'superadmin' && $user['role'] !== 'admin' && $user['role'] !== 'hrd' && $user['role'] !== 'manager' && $hierarki < 5 && $user['departemen_id'] != $leave['departemen_id']) {
    jsonResponse(false, 'Akses ditolak. Anda hanya dapat memproses pengajuan cuti anggota departemen Anda.', null, 403);
}

try {
    $pdo->beginTransaction();

    if ($action === 'reject') {
        // Any level rejecting immediately terminates with status 'rejected'
        $stmtReject = $pdo->prepare("
            UPDATE pengajuan_cuti 
            SET status = 'rejected', 
                approval_step = 'rejected',
                approved_by = ?, 
                approved_at = NOW(), 
                rejection_reason = ?, 
                catatan_atasan = ?,
                notif_read = 0,
                updated_at = NOW() 
            WHERE id = ?
        ");
        $stmtReject->execute([$user['id'], $rejectionReason, $notes, $leaveId]);
        $pdo->commit();

        jsonResponse(true, "Pengajuan cuti atas nama {$leave['nama_lengkap']} telah DITOLAK.", [
            'id' => $leaveId,
            'status' => 'rejected',
            'approval_step' => 'rejected'
        ]);

    } elseif ($action === 'approve') {
        // Determine transition:
        // 1. If currently at pending_spv -> moves to pending_manager
        // 2. If currently at pending_manager -> moves to pending_hrd
        // 3. If currently at pending_hrd (or user is HRD/Admin) -> Final Approval!
        
        $isHrd = ($user['role'] === 'superadmin' || $user['role'] === 'admin' || $user['role'] === 'hrd' || $hierarki >= 7);
        $isManager = ($user['role'] === 'manager' || ($hierarki >= 5 && $hierarki <= 6));
        $isSpv = ($user['role'] === 'supervisor' || $user['role'] === 'leader' || ($hierarki >= 3 && $hierarki <= 4));

        if ($currentStep === 'pending_spv') {
            // Stage 1: MUST be approved by Leader / Supervisor of the applicant's department
            if (!$isSpv || ($user['departemen_id'] != $leave['departemen_id'])) {
                $pdo->rollBack();
                jsonResponse(false, 'Pengajuan ini masih menunggu persetujuan dari Leader / Supervisor departemen pemohon.', null, 403);
            }

            $stmtUpd = $pdo->prepare("
                UPDATE pengajuan_cuti 
                SET approval_step = 'pending_manager',
                    spv_id = ?,
                    spv_at = NOW(),
                    spv_notes = ?,
                    notif_read = 0,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmtUpd->execute([$user['id'], $notes, $leaveId]);
            $pdo->commit();

            jsonResponse(true, "Persetujuan Leader/Spv berhasil! Pengajuan diteruskan ke Plant Manager.", [
                'id' => $leaveId,
                'status' => 'pending',
                'approval_step' => 'pending_manager'
            ]);

        } elseif ($currentStep === 'pending_manager') {
            // Stage 2: MUST be approved by Plant Manager (or HRD override)
            if (!$isManager && !$isHrd) {
                $pdo->rollBack();
                jsonResponse(false, 'Pengajuan ini sedang menunggu persetujuan dari Plant Manager.', null, 403);
            }

            $stmtUpd = $pdo->prepare("
                UPDATE pengajuan_cuti 
                SET approval_step = 'pending_hrd',
                    manager_id = ?,
                    manager_at = NOW(),
                    manager_notes = ?,
                    notif_read = 0,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmtUpd->execute([$user['id'], $notes, $leaveId]);
            $pdo->commit();

            jsonResponse(true, "Persetujuan Plant Manager berhasil! Pengajuan diteruskan ke HRD untuk persetujuan final.", [
                'id' => $leaveId,
                'status' => 'pending',
                'approval_step' => 'pending_hrd'
            ]);

        } elseif ($currentStep === 'pending_hrd') {
            // Stage 3: Final HRD Approval & Quota Deduction
            if (!$isHrd) {
                $pdo->rollBack();
                jsonResponse(false, 'Persetujuan akhir dan pemotongan kuota cuti memerlukan wewenang HRD.', null, 403);
            }
            $potongKuota = (int)$leave['potong_kuota'];
            $totalHari = (int)$leave['total_hari'];
            $employeeId = (int)$leave['employee_id'];
            $sisaSebelum = (int)$leave['sisa_cuti'];

            if ($potongKuota === 1) {
                if ($sisaSebelum < $totalHari) {
                    $pdo->rollBack();
                    jsonResponse(false, "Persetujuan final gagal! Sisa kuota cuti karyawan ({$sisaSebelum} hari) kurang dari jumlah hari cuti ({$totalHari} hari).", null, 400);
                }

                $sisaSesudah = $sisaSebelum - $totalHari;
                $terpakaiSesudah = (int)$leave['cuti_terpakai'] + $totalHari;

                // Update Employee Balance
                $stmtUpdEmp = $pdo->prepare("
                    UPDATE karyawan 
                    SET sisa_cuti = ?, cuti_terpakai = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmtUpdEmp->execute([$sisaSesudah, $terpakaiSesudah, $employeeId]);

                // Insert Quota History
                $stmtLogQuota = $pdo->prepare("
                    INSERT INTO riwayat_kuota_cuti (
                        employee_id, kuota_sebelum, perubahan, kuota_sesudah,
                        tipe, keterangan, created_by, created_at
                    ) VALUES (?, ?, ?, ?, 'potong_cuti', ?, ?, NOW())
                ");
                $stmtLogQuota->execute([
                    $employeeId,
                    $sisaSebelum,
                    -$totalHari,
                    $sisaSesudah,
                    "Pengajuan {$leave['nama_cuti']} ({$leave['nomor_surat']}) Disetujui HRD ({$totalHari} hari)",
                    $user['id']
                ]);
            }

            // Update Leave to Final Approved
            $stmtUpdFinal = $pdo->prepare("
                UPDATE pengajuan_cuti 
                SET status = 'approved',
                    approval_step = 'approved',
                    hrd_id = ?,
                    hrd_at = NOW(),
                    hrd_notes = ?,
                    approved_by = ?, 
                    approved_at = NOW(), 
                    catatan_atasan = ?, 
                    notif_read = 0,
                    updated_at = NOW() 
                WHERE id = ?
            ");
            $stmtUpdFinal->execute([$user['id'], $notes, $user['id'], $notes, $leaveId]);

            $pdo->commit();
            jsonResponse(true, "Pengajuan cuti atas nama {$leave['nama_lengkap']} berhasil DISETUJUI FINAL oleh HRD!", [
                'id' => $leaveId,
                'status' => 'approved',
                'approval_step' => 'approved'
            ]);
        }
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(false, 'Terjadi kesalahan sistem saat memproses persetujuan: ' . $e->getMessage(), null, 500);
}
