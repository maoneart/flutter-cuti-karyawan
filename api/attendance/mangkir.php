<?php
/**
 * API Record Mangkir (Alpha / AWOL)
 * POST /api/attendance/mangkir.php
 */

require_once __DIR__ . '/../middleware/auth_middleware.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Metode HTTP tidak diizinkan. Gunakan POST.', null, 405);
}

$user = authenticateApiUser();
$pdo = getDbConnection();

$userRole = strtolower($user['role'] ?? '');
$userLevel = (int)($user['level_hierarki'] ?? 1);
$isLeaderOrSpv = in_array($userRole, ['leader', 'supervisor', 'atasan']) || ($userLevel >= 3 && $userLevel <= 4);
$isManager = $userRole === 'manager' || ($userLevel >= 5 && $userLevel <= 6);
$isHRD = in_array($userRole, ['admin', 'superadmin', 'hrd']) || $userLevel >= 7;

if (!$isLeaderOrSpv && !$isManager && !$isHRD) {
    jsonResponse(false, 'Akses ditolak! Anda tidak memiliki wewenang mencatat mangkir.', null, 403);
}

$input = getApiRequestData();
$employeeId = (int)($input['employee_id'] ?? 0);
$tanggal = trim($input['tanggal'] ?? date('Y-m-d'));
$shift = trim($input['shift'] ?? 'Shift 1');
$keterangan = trim($input['keterangan_mangkir'] ?? 'Mangkir tanpa kabar saat jam shift masuk.');

if (!strtotime($tanggal)) {
    $tanggal = date('Y-m-d');
}

if (stripos($shift, 'Shift 2') !== false) {
    $shift = 'Shift 2 (Malam)';
} else {
    $shift = 'Shift 1 (Pagi)';
}

if ($employeeId <= 0) {
    jsonResponse(false, 'Karyawan tidak valid.', null, 422);
}

// Check employee
$stmtEmp = $pdo->prepare("SELECT id, nama_lengkap, departemen_id FROM karyawan WHERE id = ?");
$stmtEmp->execute([$employeeId]);
$emp = $stmtEmp->fetch();

if (!$emp) {
    jsonResponse(false, 'Karyawan tidak ditemukan.', null, 404);
}

if ($isLeaderOrSpv && (int)$user['departemen_id'] !== (int)$emp['departemen_id']) {
    jsonResponse(false, 'Akses ditolak! Anda hanya berwenang mencatat mangkir di bagian Anda.', null, 403);
}

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
    $stmt->execute([$employeeId, $user['id'], $tanggal, $shift, $keterangan]);

    jsonResponse(true, "Status Mangkir berhasil dicatat untuk {$emp['nama_lengkap']} pada $shift. Tembusan otomatis terkirim ke HRD.", [
        'employee_id' => $employeeId,
        'tanggal' => $tanggal,
        'shift' => $shift,
        'status' => 'mangkir'
    ]);
} catch (PDOException $e) {
    jsonResponse(false, 'Gagal mencatat status mangkir: ' . $e->getMessage(), null, 500);
}
