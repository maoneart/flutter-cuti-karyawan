<?php
/**
 * API Adjust Individual Employee Leave Quota
 * POST /api/quotas/adjust.php
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

$employeeId = (int)($input['employee_id'] ?? 0);
$mode = trim($input['mode'] ?? 'set_total'); // 'set_total' (ubah total hak tahunan) atau 'delta' (tambah/kurang saldo)
$keterangan = trim($input['keterangan'] ?? 'Penyesuaian Jatah Cuti oleh HRD');

if (!$employeeId) {
    jsonResponse(false, 'ID Karyawan wajib diisi!', null, 400);
}

$stmtEmp = $pdo->prepare("
    SELECT e.*, d.nama_dept, p.nama_jabatan 
    FROM karyawan e
    JOIN departemen d ON e.departemen_id = d.id
    JOIN jabatan p ON e.jabatan_id = p.id
    WHERE e.id = ?
");
$stmtEmp->execute([$employeeId]);
$emp = $stmtEmp->fetch();

if (!$emp) {
    jsonResponse(false, 'Karyawan tidak ditemukan!', null, 404);
}

$kuotaSebelum = (int)$emp['sisa_cuti'];
$totalSebelum = (int)$emp['kuota_cuti'];
$cutiTerpakai = (int)$emp['cuti_terpakai'];

$pdo->beginTransaction();

try {
    if ($mode === 'set_total') {
        // Mode 1: Tetapkan total hak kuota tahunan baru (misal Manager = 14, Staff = 12)
        $newTotalKuota = max(0, (int)($input['total_kuota'] ?? 12));
        $newSisaCuti = max(0, $newTotalKuota - $cutiTerpakai);
        $perubahan = $newSisaCuti - $kuotaSebelum;
        $tipe = 'alokasi_tahunan';

        $stmtUpdate = $pdo->prepare("UPDATE karyawan SET kuota_cuti = ?, sisa_cuti = ? WHERE id = ?");
        $stmtUpdate->execute([$newTotalKuota, $newSisaCuti, $employeeId]);

        $stmtLog = $pdo->prepare("
            INSERT INTO riwayat_kuota_cuti (
                employee_id, kuota_sebelum, perubahan, kuota_sesudah, 
                tipe, keterangan, created_by, created_at
            ) VALUES (
                ?, ?, ?, ?, 
                ?, ?, ?, NOW()
            )
        ");
        $stmtLog->execute([
            $employeeId, $kuotaSebelum, $perubahan, $newSisaCuti,
            $tipe, $keterangan, $currentUser['id']
        ]);

        $pdo->commit();

        jsonResponse(true, "Hak cuti tahunan {$emp['nama_lengkap']} berhasil diubah menjadi {$newTotalKuota} hari (Sisa saldo: {$newSisaCuti} hari).", [
            'employee_id' => $employeeId,
            'kuota_cuti' => $newTotalKuota,
            'cuti_terpakai' => $cutiTerpakai,
            'sisa_cuti' => $newSisaCuti,
        ]);
    } else {
        // Mode 2: Penyesuaian Delta (+/- Hari)
        $delta = (int)($input['delta'] ?? 0);
        if ($delta === 0) {
            jsonResponse(false, 'Nilai penyesuaian delta tidak boleh 0!', null, 400);
        }

        $newSisaCuti = max(0, $kuotaSebelum + $delta);
        $newTotalKuota = max(0, $totalSebelum + $delta);
        $tipe = 'penyesuaian_hrd';

        $stmtUpdate = $pdo->prepare("UPDATE karyawan SET kuota_cuti = ?, sisa_cuti = ? WHERE id = ?");
        $stmtUpdate->execute([$newTotalKuota, $newSisaCuti, $employeeId]);

        $stmtLog = $pdo->prepare("
            INSERT INTO riwayat_kuota_cuti (
                employee_id, kuota_sebelum, perubahan, kuota_sesudah, 
                tipe, keterangan, created_by, created_at
            ) VALUES (
                ?, ?, ?, ?, 
                ?, ?, ?, NOW()
            )
        ");
        $stmtLog->execute([
            $employeeId, $kuotaSebelum, $delta, $newSisaCuti,
            $tipe, $keterangan, $currentUser['id']
        ]);

        $pdo->commit();

        $prefix = $delta > 0 ? "+{$delta}" : "{$delta}";
        jsonResponse(true, "Penyesuaian saldo ({$prefix} hari) untuk {$emp['nama_lengkap']} berhasil disimpan! (Sisa saldo baru: {$newSisaCuti} hari).", [
            'employee_id' => $employeeId,
            'kuota_cuti' => $newTotalKuota,
            'cuti_terpakai' => $cutiTerpakai,
            'sisa_cuti' => $newSisaCuti,
        ]);
    }
} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(false, 'Gagal memperbarui jatah kuota: ' . $e->getMessage(), null, 500);
}
