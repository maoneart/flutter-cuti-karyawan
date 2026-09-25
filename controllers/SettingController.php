<?php
/**
 * Setting Controller
 * Application & Company Profile Configuration Management (Commercial & White-labeling ready)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/session.php';

class SettingController {
    
    /**
     * Display Settings Page
     */
    public function index() {
        requireRole('admin');
        
        $pdo = getDbConnection();
        $settings = getAppSettings($pdo);
        
        require_once __DIR__ . '/../models/Permission.php';
        $permissionsMatrix = Permission::getAllMatrix($pdo);
        
        $pageTitle = "Pengaturan Aplikasi & Perusahaan";

        require __DIR__ . '/../views/settings/index.php';
    }

    /**
     * Process Role Permissions Matrix Update
     */
    public function updatePermissions() {
        requireLogin();

        $currUser = getCurrentUser();
        $isSuperAdmin = ($currUser && ($currUser['role'] === 'superadmin' || $currUser['role'] === 'admin' || (int)($currUser['level_hierarki'] ?? 0) >= 8));

        if (!$isSuperAdmin) {
            if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Akses ditolak. Pengaturan Hak Akses hanya dapat diubah oleh Super Admin.']);
                exit;
            }
            setFlash('error', 'Akses ditolak! Pengaturan Hak Akses & Privilege hanya dapat diubah oleh Super Admin.');
            header('Location: ' . BASE_URL . '/index.php?page=settings');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?page=settings#tab-privileges');
            exit;
        }

        $pdo = getDbConnection();
        require_once __DIR__ . '/../models/Permission.php';

        // Check if AJAX toggle
        if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
            header('Content-Type: application/json');
            $role = cleanInput($_POST['role'] ?? '');
            $permKey = cleanInput($_POST['permission_key'] ?? '');
            $isGranted = (isset($_POST['is_granted']) && ($_POST['is_granted'] === '1' || $_POST['is_granted'] === 'true' || $_POST['is_granted'] === 1)) ? 1 : 0;

            if ($role === 'superadmin') {
                echo json_encode(['success' => false, 'message' => 'Hak akses Super Admin tidak dapat dinonaktifkan.']);
                exit;
            }

            $success = Permission::toggle($role, $permKey, $isGranted, $pdo);
            echo json_encode(['success' => (bool)$success, 'message' => $success ? 'Hak akses berhasil diperbarui' : 'Gagal menyimpan ke database']);
            exit;
        }

        // Full matrix post
        $matrix = $_POST['matrix'] ?? [];
        $success = Permission::updateMatrix($matrix, $pdo);

        if ($success) {
            setFlash('success', 'Matriks Hak Akses & Privilege Role berhasil disimpan!');
        } else {
            setFlash('error', 'Gagal memperbarui matriks hak akses.');
        }

        header('Location: ' . BASE_URL . '/index.php?page=settings#tab-privileges');
        exit;
    }

    /**
     * Process Settings Update
     */
    public function update() {
        requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?page=settings');
            exit;
        }

        $pdo = getDbConnection();
        $currentSettings = getAppSettings($pdo);

        // Sanitize text inputs
        $namaAplikasi = cleanInput($_POST['nama_aplikasi'] ?? 'Sistem Informasi Cuti Karyawan');
        $singkatanAplikasi = cleanInput($_POST['singkatan_aplikasi'] ?? 'FORM CUTI ONLINE');
        $tagline = cleanInput($_POST['tagline'] ?? '');
        $namaPerusahaan = cleanInput($_POST['nama_perusahaan'] ?? 'PT. Nakakin Indonesia');
        $singkatanPerusahaan = cleanInput($_POST['singkatan_perusahaan'] ?? 'NAKAKIN');
        $subSingkatanPerusahaan = cleanInput($_POST['sub_singkatan_perusahaan'] ?? 'INDONESIA');
        $alamatPerusahaan = cleanInput($_POST['alamat_perusahaan'] ?? '');
        $telepon = cleanInput($_POST['telepon'] ?? '');
        $emailPerusahaan = cleanInput($_POST['email_perusahaan'] ?? '');
        $website = cleanInput($_POST['website'] ?? '');
        $prefixNomorSurat = strtoupper(cleanInput($_POST['prefix_nomor_surat'] ?? 'CUTI/NAK'));
        $defaultKuotaCuti = (int)($_POST['default_kuota_cuti'] ?? 12);
        $namaKepalaHrd = cleanInput($_POST['nama_kepala_hrd'] ?? '');
        $jabatanKepalaHrd = cleanInput($_POST['jabatan_kepala_hrd'] ?? '');
        $lokasiSurat = cleanInput($_POST['lokasi_surat'] ?? 'Karawang');
        $footerText = cleanInput($_POST['footer_text'] ?? '');

        // Handle Logo Upload
        $logoFilename = $currentSettings['logo'];
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
            $fileInfo = pathinfo($_FILES['logo']['name']);
            $ext = strtolower($fileInfo['extension'] ?? '');

            if (in_array($ext, $allowedExts)) {
                $newLogoName = 'logo_brand_' . time() . '.' . $ext;
                $targetDir = __DIR__ . '/../assets/images/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                $targetPath = $targetDir . $newLogoName;
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                    $logoFilename = $newLogoName;
                }
            } else {
                setFlash('error', 'Format file logo tidak valid! Gunakan format PNG, JPG, WEBP, atau SVG.');
                header('Location: ' . BASE_URL . '/index.php?page=settings');
                exit;
            }
        }

        // Handle Favicon Upload
        $faviconFilename = $currentSettings['favicon'];
        if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
            $allowedFavExts = ['ico', 'png', 'jpg', 'jpeg', 'webp', 'svg'];
            $fileInfo = pathinfo($_FILES['favicon']['name']);
            $ext = strtolower($fileInfo['extension'] ?? '');

            if (in_array($ext, $allowedFavExts)) {
                $newFavName = 'favicon_' . time() . '.' . $ext;
                $targetDir = __DIR__ . '/../assets/images/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                $targetPath = $targetDir . $newFavName;
                if (move_uploaded_file($_FILES['favicon']['tmp_name'], $targetPath)) {
                    $faviconFilename = $newFavName;
                }
            }
        }

        try {
            $stmt = $pdo->prepare("
                UPDATE pengaturan_aplikasi 
                SET nama_aplikasi = ?,
                    singkatan_aplikasi = ?,
                    tagline = ?,
                    nama_perusahaan = ?,
                    singkatan_perusahaan = ?,
                    sub_singkatan_perusahaan = ?,
                    alamat_perusahaan = ?,
                    telepon = ?,
                    email_perusahaan = ?,
                    website = ?,
                    logo = ?,
                    favicon = ?,
                    prefix_nomor_surat = ?,
                    default_kuota_cuti = ?,
                    nama_kepala_hrd = ?,
                    jabatan_kepala_hrd = ?,
                    lokasi_surat = ?,
                    footer_text = ?,
                    updated_at = NOW()
                WHERE id = 1
            ");

            $stmt->execute([
                $namaAplikasi,
                $singkatanAplikasi,
                $tagline,
                $namaPerusahaan,
                $singkatanPerusahaan,
                $subSingkatanPerusahaan,
                $alamatPerusahaan,
                $telepon,
                $emailPerusahaan,
                $website,
                $logoFilename,
                $faviconFilename,
                $prefixNomorSurat,
                $defaultKuotaCuti,
                $namaKepalaHrd,
                $jabatanKepalaHrd,
                $lokasiSurat,
                $footerText
            ]);

            // Sync current logged-in admin user's name & session
            if (!empty($namaKepalaHrd)) {
                $currUser = getCurrentUser();
                if ($currUser && $currUser['role'] === 'admin') {
                    $stmtAdmin = $pdo->prepare("UPDATE karyawan SET nama_lengkap = ? WHERE id = ?");
                    $stmtAdmin->execute([$namaKepalaHrd, $currUser['id']]);
                    $_SESSION['user_name'] = $namaKepalaHrd;
                }
            }

            setFlash('success', 'Pengaturan nama aplikasi, profil perusahaan, dan branding sistem berhasil diperbarui!');
        } catch (PDOException $e) {
            setFlash('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/index.php?page=settings');
        exit;
    }
}
