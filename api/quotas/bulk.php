<?php
/**
 * API Bulk / Mass Quota Allocation
 * POST /api/quotas/bulk.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$currentUser = authenticateApiUser();
$pdo = getDbConnection();

$hierarki = (int)($currentUser['level_hierarki'] ?? 1);
$isHRDOrAdmin = ($currentUser['role'] === 'superadmin' || $currentUser['role'] === 'admin' || $currentUser['role'] === 'hrd' || $hierarki >= 7);

if (!$isHRDOrAdmin) {
    jsonResponse(false, 'Akses ditolak. Fitur Kelola Jatah Cuti hanya dapat diakses oleh HRD dan Administrator.', null, 403);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$targetScope = trim($input['target_scope'] ?? 'all'); // 'all', 'department', 'position'
$targetId = (int)($input['target_id'] ?? 0);
$batchAction = trim($input['batch_action'] ?? 'reset'); // 'reset' (set kuota tahunan & reset terpakai) atau 'add' (+N hari)
$quotaAmount = (int)($input['quota_amount'] ?? 12);
$keterangan = trim($input['keterangan'] ?? 'Alokasi Jatah Kuota Cuti Tahunan Massal');

if ($quotaAmount <= 0) {
    jsonResponse(false, 'Jumlah hari kuota harus lebih dari 0!', null, 400);
}

// Build target query
$sql = "SELECT id, sisa_cuti, kuota_cuti, cuti_terpakai, nama_lengkap FROM karyawan WHERE status_aktif = 'Aktif'";
$params = [];
$scopeName = "Seluruh Karyawan Aktif";

if ($targetScope === 'department' && $targetId > 0) {
    $sql .= " AND departemen_id = ?";
    $params[] = $targetId;
    $stmtDept = $pdo->prepare("SELECT nama_dept FROM departemen WHERE id = ?");
    $stmtDept->execute([$targetId]);
    $deptName = $stmtDept->fetchColumn();
    $scopeName = "Departemen " . ($deptName ?: 'Terpilih');
} elseif ($targetScope === 'position' && $targetId > 0) {
    $sql .= " AND jabatan_id = ?";
    $params[] = $targetId;
    $stmtPos = $pdo->prepare("SELECT nama_jabatan FROM jabatan WHERE id = ?");
    $stmtPos->execute([$targetId]);
    $posName = $stmtPos->fetchColumn();
    $scopeName = "Jabatan " . ($posName ?: 'Terpilih');
}

$stmtTarget = $pdo->prepare($sql);
$stmtTarget->execute($params);
$employees = $stmtTarget->fetchAll();

if (empty($employees)) {
    jsonResponse(false, 'Tidak ada karyawan aktif yang cocok dengan kriteria target yang dipilih!', null, 404);
}

$pdo->beginTransaction();

try {
    if ($batchAction === 'reset') {
        // Reset tahunan: kuota_cuti = quotaAmount, cuti_terpakai = 0, sisa_cuti = quotaAmount
        $stmtUpdate = $pdo->prepare("
            UPDATE karyawan 
            SET kuota_cuti = ?, cuti_terpakai = 0, sisa_cuti = ? 
            WHERE id = ?
        ");

        $stmtLog = $pdo->prepare("
            INSERT INTO riwayat_kuota_cuti (
                employee_id, kuota_sebelum, perubahan, kuota_sesudah, 
                tipe, keterangan, created_by, created_at
            ) VALUES (
                ?, ?, ?, ?, 
                'alokasi_tahunan', ?, ?, NOW()
            )
        ");

        foreach ($employees as $emp) {
            $stmtUpdate->execute([$quotaAmount, $quotaAmount, $emp['id']]);
            $stmtLog->execute([
                $emp['id'], $emp['sisa_cuti'], $quotaAmount, $quotaAmount,
                $keterangan, $currentUser['id']
            ]);
        }
        $pesan = "Alokasi massal reset tahunan ({$quotaAmount} hari) berhasil diterapkan untuk {$scopeName} (" . count($employees) . " karyawan)!";
    } else {
        // Tambah massal (+quotaAmount hari)
        $stmtUpdate = $pdo->prepare("
            UPDATE karyawan 
            SET kuota_cuti = kuota_cuti + ?, sisa_cuti = sisa_cuti + ? 
            WHERE id = ?
        ");

        $stmtLog = $pdo->prepare("
            INSERT INTO riwayat_kuota_cuti (
                employee_id, kuota_sebelum, perubahan, kuota_sesudah, 
                tipe, keterangan, created_by, created_at
            ) VALUES (
                ?, ?, ?, ?, 
                'penyesuaian_hrd', ?, ?, NOW()
            )
        ");

        foreach ($employees as $emp) {
            $kuotaSebelum = (int)$emp['sisa_cuti'];
            $kuotaSesudah = $kuotaSebelum + $quotaAmount;
            $stmtUpdate->execute([$quotaAmount, $quotaAmount, $emp['id']]);
            $stmtLog->execute([
                $emp['id'], $kuotaSebelum, $quotaAmount, $kuotaSesudah,
                $keterangan, $currentUser['id']
            ]);
        }
        $pesan = "Penambahan massal (+{$quotaAmount} hari) berhasil diterapkan untuk {$scopeName} (" . count($employees) . " karyawan)!";
    }

    $pdo->commit();
    jsonResponse(true, $pesan, [
        'affected_count' => count($employees),
        'scope' => $scopeName,
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(false, 'Gagal menjalankan alokasi massal: ' . $e->getMessage(), null, 500);
}
