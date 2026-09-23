<?php
/**
 * Quota Controller
 * PT. Nakakin Indonesia Leave Management System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/session.php';

class QuotaController {
    
    // Set official annual quota entitlement for employee (e.g. Manager = 14, Staff = 12)
    public function adjust() {
        requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?page=quotas');
            exit;
        }

        $pdo = getDbConnection();
        $currentUser = getCurrentUser();

        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $newTotalKuota = max(1, (int)($_POST['total_kuota_baru'] ?? 12));
        $tipe = cleanInput($_POST['tipe'] ?? 'alokasi_tahunan');
        $keterangan = cleanInput($_POST['keterangan'] ?? 'Penetapan Hak Jatah Cuti Tahunan');

        if (!$employeeId || empty($keterangan)) {
            setFlash('error', 'Semua data penetapan kuota wajib diisi!');
            header('Location: ' . BASE_URL . '/index.php?page=quotas');
            exit;
        }

        $stmtEmp = $pdo->prepare("SELECT e.*, p.nama_jabatan, d.nama_dept FROM karyawan e JOIN jabatan p ON e.jabatan_id = p.id JOIN departemen d ON e.departemen_id = d.id WHERE e.id = ?");
        $stmtEmp->execute([$employeeId]);
        $emp = $stmtEmp->fetch();

        if (!$emp) {
            setFlash('error', 'Karyawan tidak ditemukan!');
            header('Location: ' . BASE_URL . '/index.php?page=quotas');
            exit;
        }

        $kuotaSebelum = (int)$emp['sisa_cuti'];
        $cutiTerpakai = (int)$emp['cuti_terpakai'];
        $newSisaCuti = max(0, $newTotalKuota - $cutiTerpakai);
        $perubahan = $newSisaCuti - $kuotaSebelum;

        $pdo->beginTransaction();
        try {
            $stmtUpdate = $pdo->prepare("UPDATE karyawan SET kuota_cuti = ?, sisa_cuti = ? WHERE id = ?");
            $stmtUpdate->execute([$newTotalKuota, $newSisaCuti, $employeeId]);

            // Insert into quota audit history
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
            setFlash('success', "Hak cuti tahunan {$emp['nama_lengkap']} berhasil ditetapkan menjadi {$newTotalKuota} hari! (Sisa saldo: {$newSisaCuti} hari)");
        } catch (Exception $e) {
            $pdo->rollBack();
            setFlash('error', 'Gagal menetapkan kuota: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/index.php?page=quotas');
        exit;
    }

    // Batch / Bulk quota allocation (All employees, by Department, or by Position)
    public function batchReset() {
        requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?page=quotas');
            exit;
        }

        $pdo = getDbConnection();
        $currentUser = getCurrentUser();

        $targetScope = cleanInput($_POST['target_scope'] ?? 'all'); // 'all', 'department', 'position'
        $targetId = (int)($_POST['target_id'] ?? 0);
        $batchAction = cleanInput($_POST['batch_action'] ?? 'reset'); // 'reset' (new annual quota & reset used) or 'add' (+N days)
        $quotaAmount = (int)($_POST['default_quota'] ?? 12);
        $keterangan = cleanInput($_POST['batch_keterangan'] ?? 'Alokasi Jatah Cuti Tahunan');

        if ($quotaAmount <= 0) {
            setFlash('error', 'Jumlah hari kuota harus lebih dari 0!');
            header('Location: ' . BASE_URL . '/index.php?page=quotas');
            exit;
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
            setFlash('error', 'Tidak ada karyawan aktif yang cocok dengan kriteria target yang dipilih!');
            header('Location: ' . BASE_URL . '/index.php?page=quotas');
            exit;
        }

        $pdo->beginTransaction();
        try {
            if ($batchAction === 'reset') {
                // Annual reset: new total quota, cuti_terpakai = 0, sisa_cuti = default_quota
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
                // Add delta to existing balance
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
            setFlash('success', $pesan);
        } catch (Exception $e) {
            $pdo->rollBack();
            setFlash('error', 'Gagal melakukan alokasi massal: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/index.php?page=quotas');
        exit;
    }
}
