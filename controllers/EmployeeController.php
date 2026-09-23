<?php
/**
 * Employee Controller
 * PT. Nakakin Indonesia Leave Management System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/session.php';

class EmployeeController {
    
    // Save New Employee
    public function save() {
        requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?page=employee-create');
            exit;
        }

        $pdo = getDbConnection();
        $currentUser = getCurrentUser();

        $nik = cleanInput($_POST['nik'] ?? '');
        $nama = cleanInput($_POST['nama_lengkap'] ?? '');
        $email = cleanInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? 'password123';
        $role = cleanInput($_POST['role'] ?? 'operator');
        $deptId = (int)($_POST['departemen_id'] ?? 0);
        $jabatanId = (int)($_POST['jabatan_id'] ?? 0);
        $tanggalMasuk = cleanInput($_POST['tanggal_masuk'] ?? date('Y-m-d'));
        $kuotaCuti = (int)($_POST['kuota_cuti'] ?? 12);
        $jenisKelamin = cleanInput($_POST['jenis_kelamin'] ?? 'Laki-laki');
        $agama = cleanInput($_POST['agama'] ?? 'Islam');
        $statusPernikahan = cleanInput($_POST['status_pernikahan'] ?? 'Belum Menikah');
        $noHp = cleanInput($_POST['no_hp'] ?? '');
        $alamat = cleanInput($_POST['alamat'] ?? '');

        if (empty($nik) || empty($nama) || empty($email) || !$deptId || !$jabatanId) {
            setFlash('error', 'Semua kolom bertanda bintang (*) wajib diisi!');
            header('Location: ' . BASE_URL . '/index.php?page=employee-create');
            exit;
        }

        // Check unique NIK and Email
        $stmtCheck = $pdo->prepare("SELECT id FROM karyawan WHERE nik = ? OR email = ?");
        $stmtCheck->execute([$nik, $email]);
        if ($stmtCheck->fetch()) {
            setFlash('error', 'NIK atau Email sudah terdaftar di sistem! Silakan gunakan NIK/Email lain.');
            header('Location: ' . BASE_URL . '/index.php?page=employee-create');
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("
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
                    ?, ?, 'Aktif', NOW()
                )
            ");
            $stmt->execute([
                $nik, $nama, $email, $hashedPassword, $role,
                $deptId, $jabatanId, $tanggalMasuk,
                $kuotaCuti, $kuotaCuti,
                $jenisKelamin, $agama, $statusPernikahan,
                $noHp, $alamat
            ]);

            $newEmpId = $pdo->lastInsertId();

            // Log initial quota
            $stmtLog = $pdo->prepare("
                INSERT INTO riwayat_kuota_cuti (
                    employee_id, kuota_sebelum, perubahan, kuota_sesudah, 
                    tipe, keterangan, created_by, created_at
                ) VALUES (?, 0, ?, ?, 'alokasi_tahunan', 'Alokasi Jatah Cuti Awal Masuk Kerja', ?, NOW())
            ");
            $stmtLog->execute([$newEmpId, $kuotaCuti, $kuotaCuti, $currentUser['id']]);

            setFlash('success', "Karyawan baru ({$nama}) berhasil ditambahkan ke database!");
            header('Location: ' . BASE_URL . '/index.php?page=employees');
            exit;
        } catch (PDOException $e) {
            setFlash('error', 'Gagal menyimpan data karyawan: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/index.php?page=employee-create');
            exit;
        }
    }

    // Update Employee
    public function update() {
        requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?page=employees');
            exit;
        }

        $pdo = getDbConnection();
        $id = (int)($_POST['id'] ?? 0);
        $nik = cleanInput($_POST['nik'] ?? '');
        $nama = cleanInput($_POST['nama_lengkap'] ?? '');
        $email = cleanInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = cleanInput($_POST['role'] ?? 'operator');
        $deptId = (int)($_POST['departemen_id'] ?? 0);
        $jabatanId = (int)($_POST['jabatan_id'] ?? 0);
        $tanggalMasuk = cleanInput($_POST['tanggal_masuk'] ?? date('Y-m-d'));
        $statusAktif = cleanInput($_POST['status_aktif'] ?? 'Aktif');
        $kuotaCuti = (int)($_POST['kuota_cuti'] ?? 12);
        $sisaCuti = (int)($_POST['sisa_cuti'] ?? 12);
        $jenisKelamin = cleanInput($_POST['jenis_kelamin'] ?? 'Laki-laki');
        $agama = cleanInput($_POST['agama'] ?? 'Islam');
        $statusPernikahan = cleanInput($_POST['status_pernikahan'] ?? 'Belum Menikah');
        $noHp = cleanInput($_POST['no_hp'] ?? '');
        $alamat = cleanInput($_POST['alamat'] ?? '');

        // Check unique NIK and Email for other employees
        $stmtCheck = $pdo->prepare("SELECT id FROM karyawan WHERE (nik = ? OR email = ?) AND id != ?");
        $stmtCheck->execute([$nik, $email, $id]);
        if ($stmtCheck->fetch()) {
            setFlash('error', 'NIK atau Email sudah digunakan oleh karyawan lain!');
            header('Location: ' . BASE_URL . '/index.php?page=employee-edit&id=' . $id);
            exit;
        }

        try {
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("
                    UPDATE karyawan SET 
                        nik = ?, nama_lengkap = ?, email = ?, password = ?, role = ?,
                        departemen_id = ?, jabatan_id = ?, tanggal_masuk = ?, status_aktif = ?,
                        kuota_cuti = ?, sisa_cuti = ?, jenis_kelamin = ?, agama = ?,
                        status_pernikahan = ?, no_hp = ?, alamat = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $nik, $nama, $email, $hashedPassword, $role,
                    $deptId, $jabatanId, $tanggalMasuk, $statusAktif,
                    $kuotaCuti, $sisaCuti, $jenisKelamin, $agama,
                    $statusPernikahan, $noHp, $alamat, $id
                ]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE karyawan SET 
                        nik = ?, nama_lengkap = ?, email = ?, role = ?,
                        departemen_id = ?, jabatan_id = ?, tanggal_masuk = ?, status_aktif = ?,
                        kuota_cuti = ?, sisa_cuti = ?, jenis_kelamin = ?, agama = ?,
                        status_pernikahan = ?, no_hp = ?, alamat = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $nik, $nama, $email, $role,
                    $deptId, $jabatanId, $tanggalMasuk, $statusAktif,
                    $kuotaCuti, $sisaCuti, $jenisKelamin, $agama,
                    $statusPernikahan, $noHp, $alamat, $id
                ]);
            }

            setFlash('success', "Data karyawan ({$nama}) berhasil diperbarui!");
            header('Location: ' . BASE_URL . '/index.php?page=employees');
            exit;
        } catch (PDOException $e) {
            setFlash('error', 'Gagal memperbarui data: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/index.php?page=employee-edit&id=' . $id);
            exit;
        }
    }

    // Delete Employee
    public function delete() {
        requireRole('admin');

        $id = (int)($_GET['id'] ?? 0);
        $currentUser = getCurrentUser();

        if ($id === (int)$currentUser['id']) {
            setFlash('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
            header('Location: ' . BASE_URL . '/index.php?page=employees');
            exit;
        }

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("DELETE FROM karyawan WHERE id = ?");
        $stmt->execute([$id]);

        setFlash('success', 'Data karyawan telah berhasil dihapus dari sistem.');
        header('Location: ' . BASE_URL . '/index.php?page=employees');
        exit;
    }

    // Update Personal Profile
    public function updateProfile() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?page=profile');
            exit;
        }

        $pdo = getDbConnection();
        $currentUser = getCurrentUser();
        $id = $currentUser['id'];

        $namaLengkap = cleanInput($_POST['nama_lengkap'] ?? $currentUser['nama_lengkap']);
        $email = cleanInput($_POST['email'] ?? $currentUser['email']);
        $noHp = cleanInput($_POST['no_hp'] ?? '');
        $alamat = cleanInput($_POST['alamat'] ?? '');
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (empty($namaLengkap)) {
            setFlash('error', 'Nama lengkap tidak boleh kosong!');
            header('Location: ' . BASE_URL . '/index.php?page=profile');
            exit;
        }

        // Email format & uniqueness validation
        if (!empty($email) && $email !== $currentUser['email']) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                setFlash('error', 'Format email tidak valid!');
                header('Location: ' . BASE_URL . '/index.php?page=profile');
                exit;
            }
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM karyawan WHERE email = ? AND id != ?");
            $stmtCheck->execute([$email, $id]);
            if ($stmtCheck->fetchColumn() > 0) {
                setFlash('error', 'Alamat email tersebut sudah digunakan oleh pengguna lain!');
                header('Location: ' . BASE_URL . '/index.php?page=profile');
                exit;
            }
        }

        if (!empty($newPass)) {
            if ($newPass !== $confirmPass) {
                setFlash('error', 'Konfirmasi password baru tidak cocok!');
                header('Location: ' . BASE_URL . '/index.php?page=profile');
                exit;
            }
            if (strlen($newPass) < 6) {
                setFlash('error', 'Password baru minimal harus 6 karakter!');
                header('Location: ' . BASE_URL . '/index.php?page=profile');
                exit;
            }

            $hashed = password_hash($newPass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE karyawan SET nama_lengkap = ?, email = ?, no_hp = ?, alamat = ?, password = ? WHERE id = ?");
            $stmt->execute([$namaLengkap, $email, $noHp, $alamat, $hashed, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE karyawan SET nama_lengkap = ?, email = ?, no_hp = ?, alamat = ? WHERE id = ?");
            $stmt->execute([$namaLengkap, $email, $noHp, $alamat, $id]);
        }

        // Immediately update session user name
        $_SESSION['user_name'] = $namaLengkap;

        // If user is Admin, also sync nama_kepala_hrd in pengaturan_aplikasi
        if ($currentUser['role'] === 'admin') {
            try {
                $stmtSet = $pdo->prepare("UPDATE pengaturan_aplikasi SET nama_kepala_hrd = ? WHERE id = 1");
                $stmtSet->execute([$namaLengkap]);
            } catch (Exception $e) {}
        }

        setFlash('success', 'Pembaruan data profil dan nama akun Anda berhasil disimpan!');
        header('Location: ' . BASE_URL . '/index.php?page=profile');
        exit;
    }

    // Download Excel/CSV Template
    public function template() {
        requireRole('admin');
        require_once __DIR__ . '/../config/excel_helper.php';

        $format = isset($_GET['format']) ? strtolower(trim($_GET['format'])) : 'xlsx';

        if ($format === 'csv') {
            $filename = 'template_import_karyawan_nakakin.csv';
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            echo ExcelHelper::generateTemplateCsv();
        } else {
            $filename = 'template_import_karyawan_nakakin.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            echo ExcelHelper::generateXlsxTemplate();
        }
        exit;
    }

    // Process Import Preview (AJAX or Form POST)
    public function importPreview() {
        requireRole('admin');
        require_once __DIR__ . '/../config/excel_helper.php';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
            echo json_encode(['success' => false, 'message' => 'File tidak valid']);
            exit;
        }

        $file = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Gagal mengunggah file']);
            exit;
        }

        try {
            $rawRows = ExcelHelper::parseFile($file['tmp_name'], $file['name']);
            if (empty($rawRows)) {
                echo json_encode(['success' => false, 'message' => 'File kosong atau format tidak sesuai']);
                exit;
            }

            $pdo = getDbConnection();
            $validation = ExcelHelper::validateRows($rawRows, $pdo);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'File berhasil divalidasi',
                'data' => [
                    'file_name' => $file['name'],
                    'total_rows' => $validation['total_rows'],
                    'valid_count' => $validation['valid_count'],
                    'invalid_count' => $validation['invalid_count'],
                    'valid_rows' => $validation['valid_rows'],
                    'invalid_rows' => $validation['invalid_rows']
                ]
            ]);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
            exit;
        }
    }

    // Commit Bulk Import to Database
    public function importCommit() {
        requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan']);
            exit;
        }

        $rawBody = file_get_contents('php://input');
        $data = json_decode($rawBody, true) ?: $_POST;
        $rows = $data['valid_rows'] ?? [];

        if (empty($rows) || !is_array($rows)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Tidak ada data valid untuk disimpan']);
            exit;
        }

        $pdo = getDbConnection();
        $currentUser = getCurrentUser();
        $pdo->beginTransaction();

        $insertedCount = 0;
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

                // Check race condition
                $stmtCheck = $pdo->prepare("SELECT id FROM karyawan WHERE nik = ? OR email = ?");
                $stmtCheck->execute([$nik, $email]);
                if ($stmtCheck->fetch()) {
                    continue;
                }

                $stmtInsert->execute([
                    $nik, $nama, $email, $defaultPasswordHash, $role,
                    $deptId, $jabatanId, $tanggalMasuk,
                    $kuota, $kuota,
                    $jk, $agama, $statusNikah,
                    $noHp, $alamat, $statusAktif
                ]);

                $empId = $pdo->lastInsertId();
                $stmtLog->execute([$empId, $kuota, $kuota, $currentUser['id']]);
                $insertedCount++;
            }

            $pdo->commit();

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => "Berhasil mengimpor {$insertedCount} data karyawan baru ke database!",
                'inserted_count' => $insertedCount
            ]);
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()]);
            exit;
        }
    }
}
