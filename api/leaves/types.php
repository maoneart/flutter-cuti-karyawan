<?php
/**
 * API Leave Types List
 * GET /api/leaves/types.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

$user = authenticateApiUser();
$pdo = getDbConnection();

$stmt = $pdo->query("
    SELECT id, nama_cuti, kode, deskripsi, potong_kuota, max_hari_default, butuh_lampiran
    FROM jenis_cuti
    ORDER BY id ASC
");
$types = $stmt->fetchAll();

// Add user context info (e.g. if user is male, cuti haid/melahirkan can be flagged)
foreach ($types as &$t) {
    $t['id'] = (int)$t['id'];
    $t['potong_kuota'] = (int)$t['potong_kuota'];
    $t['max_hari_default'] = (int)$t['max_hari_default'];
    $t['butuh_lampiran'] = (int)$t['butuh_lampiran'];
    
    // Eligibility notes
    if (($t['kode'] === 'CH' || $t['kode'] === 'CML') && $user['jenis_kelamin'] !== 'Perempuan') {
        $t['eligible'] = false;
        $t['eligibility_message'] = 'Khusus Karyawati (Perempuan)';
    } else {
        $t['eligible'] = true;
        $t['eligibility_message'] = null;
    }
}
unset($t);

jsonResponse(true, 'Daftar jenis cuti berhasil diambil', $types);
