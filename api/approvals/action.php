<?php
/**
 * API Process Approval / Rejection
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

// Security Check: Supervisor can only approve members of their department
if ($user['role'] === 'atasan' && $user['departemen_id'] != $leave['departemen_id']) {
    jsonResponse(false, 'Akses ditolak. Anda hanya dapat menyetujui pengajuan di departemen Anda.', null, 403);
}

if ($leave['status'] !== 'pending') {
    jsonResponse(false, "Pengajuan ini sudah diproses sebelumnya dengan status: {$leave['status']}.", null, 400);
}

try {
    $pdo->beginTransaction();

    if ($action === 'approve') {
        $potongKuota = (int)$leave['potong_kuota'];
        $totalHari = (int)$leave['total_hari'];
        $employeeId = (int)$leave['employee_id'];
        $sisaSebelum = (int)$leave['sisa_cuti'];

        if ($potongKuota === 1) {
            if ($sisaSebelum < $totalHari) {
                $pdo->rollBack();
                jsonResponse(false, "Persetujuan gagal! Sisa kuota cuti karyawan ({$sisaSebelum} hari) kurang dari jumlah hari cuti ({$totalHari} hari).", null, 400);
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
                "Pengajuan {$leave['nama_cuti']} ({$leave['nomor_surat']}) Disetujui ({$totalHari} hari)",
                $user['id']
            ]);
        }

        // Update Leave Status to Approved
        $stmtUpdLeave = $pdo->prepare("
            UPDATE pengajuan_cuti 
            SET status = 'approved', 
                approved_by = ?, 
                approved_at = NOW(), 
                catatan_atasan = ?, 
                updated_at = NOW() 
            WHERE id = ?
        ");
        $stmtUpdLeave->execute([$user['id'], $notes, $leaveId]);

        $pdo->commit();
        jsonResponse(true, "Pengajuan cuti atas nama {$leave['nama_lengkap']} berhasil DISETUJUI!", [
            'id' => $leaveId,
            'status' => 'approved'
        ]);

    } elseif ($action === 'reject') {
        $stmtUpdLeave = $pdo->prepare("
            UPDATE pengajuan_cuti 
            SET status = 'rejected', 
                approved_by = ?, 
                approved_at = NOW(), 
                rejection_reason = ?, 
                catatan_atasan = ?, 
                updated_at = NOW() 
            WHERE id = ?
        ");
        $stmtUpdLeave->execute([$user['id'], $rejectionReason, $notes, $leaveId]);

        $pdo->commit();
        jsonResponse(true, "Pengajuan cuti atas nama {$leave['nama_lengkap']} telah DITOLAK.", [
            'id' => $leaveId,
            'status' => 'rejected'
        ]);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(false, 'Terjadi kesalahan sistem saat memproses persetujuan.', null, 500);
}
