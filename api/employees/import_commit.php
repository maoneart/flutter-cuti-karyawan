<?php
/**
 * API Employee Bulk Import Commit / Save to DB
 * POST /api/employees/import_commit.php (JSON payload containing valid_rows)
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$user = authenticateApiUser();
requireApiRole(['admin'], $user);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Metode HTTP harus POST.', null, 405);
}

$input = getApiRequestData();
$rows = $input['valid_rows'] ?? [];

if (empty($rows) || !is_array($rows)) {
    jsonResponse(false, 'Tidak ada data valid yang dikirim untuk disimpan.', null, 400);
}

$pdo = getDbConnection();
$pdo->beginTransaction();

$insertedCount = 0;
$errors = [];
$defaultPasswordHash = password_hash('password123', PASSWORD_BCRYPT);

try {
    $stmtInsert = $pdo->prepare("
        INSERT INTO karyawan (
            nik, nama_lengkap, email, password, role,
            departemen_id, jabatan_id, tanggal_masuk,
            kuota_cuti, cuti_terpakai, sisa_cuti,
            jenis_kelamin, agama, status_pernikahan,
            no_hp, alamat, status_aktif, created_at
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, 0, ?,
            ?, ?, ?,
            ?, ?, ?, NOW()
        )
    ");

    $stmtLog = $pdo->prepare("
        INSERT INTO riwayat_kuota_cuti (
            employee_id, kuota_sebelum, perubahan, kuota_sesudah,
            tipe, keterangan, created_by, created_at
        ) VALUES (?, 0, ?, ?, 'alokasi_tahunan', 'Alokasi Kuota Awal (Import Excel)', ?, NOW())
    ");

    foreach ($rows as $item) {
        $nik = trim($item['nik'] ?? '');
        $nama = trim($item['nama_lengkap'] ?? '');
        $email = trim($item['email'] ?? '');
        $role = trim($item['role'] ?? 'operator');
        $deptId = (int)($item['departemen_id'] ?? 0);
        $jabatanId = (int)($item['jabatan_id'] ?? 1);
        $tanggalMasuk = trim($item['tanggal_masuk'] ?? date('Y-m-d'));
        $kuota = (int)($item['kuota_cuti'] ?? 12);
        $jk = trim($item['jenis_kelamin'] ?? 'Laki-laki');
        $agama = trim($item['agama'] ?? 'Islam');
        $statusNikah = trim($item['status_pernikahan'] ?? 'Belum Menikah');
        $noHp = trim($item['no_hp'] ?? '');
        $alamat = trim($item['alamat'] ?? '');
        $statusAktif = trim($item['status_aktif'] ?? 'Aktif');

        // Check if NIK already exists in case of race condition
        $stmtCheck = $pdo->prepare("SELECT id FROM karyawan WHERE nik = ? OR email = ?");
        $stmtCheck->execute([$nik, $email]);
        if ($stmtCheck->fetch()) {
            continue; // skip duplicate
        }

        $stmtInsert->execute([
            $nik, $nama, $email, $defaultPasswordHash, $role,
            $deptId, $jabatanId, $tanggalMasuk,
            $kuota, $kuota,
            $jk, $agama, $statusNikah,
            $noHp, $alamat, $statusAktif
        ]);

        $empId = $pdo->lastInsertId();
        $stmtLog->execute([$empId, $kuota, $kuota, $user['id']]);
        $insertedCount++;
    }

    $pdo->commit();

    jsonResponse(true, "Berhasil mengimpor {$insertedCount} data karyawan baru ke database.", [
        'inserted_count' => $insertedCount
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(false, 'Gagal menyimpan data import: ' . $e->getMessage(), null, 500);
}
