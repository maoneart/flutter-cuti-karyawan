# Aplikasi Cuti Karyawan - Flutter (Android, iOS & Web)
**PT. Nakakin Indonesia Leave Management System**

Aplikasi cross-platform modern berbasis **Flutter (Dart)** yang terhubung langsung ke **Backend PHP Native REST API** dengan proteksi keamanan tinggi.

---

## 🔒 Fitur Keamanan yang Diterapkan

1. **HMAC-SHA256 Token Authentication**:
   - Autentikasi berbasis token Bearer dengan validasi signature kriptografis dan waktu kedaluwarsa (Token Expiration).
2. **Prepared Statements (PDO)**:
   - Kebal terhadap serangan SQL Injection pada semua query backend.
3. **Role-Based Access Control (RBAC)**:
   - Pemisahan hak akses ketat antara `operator`, `staff`, `atasan` (Supervisor/Manager), dan `admin` (HRD). Atasan hanya dapat memproses pengajuan cuti anggota departemennya.
4. **Validasi & Sanitasi Data Input**:
   - Pencegahan Cross-Site Scripting (XSS) dan pembersihan data di setiap endpoint.
5. **Keamanan Upload Berkas Lampiran**:
   - Pengecekan MIME type asli (`finfo`), whitelist ekstensi file (`.jpg`, `.jpeg`, `.png`, `.pdf`), batas ukuran file (max 5MB), dan penamaan hash acak unik.
6. **CORS & Safe Headers**:
   - Konfigurasi header CORS aman untuk Android, iOS, dan Web Browser.

---

## 🚀 Cara Menjalankan Aplikasi

### 1. Prasyarat
- **Laragon / XAMPP**: Pastikan Apache dan MySQL sudah berjalan dan database `db_cuti_nakakin` sudah diimpor.
- **Flutter SDK**: Versi >= 3.0.0.

### 2. Jalankan di Web (Google Chrome)
```bash
cd flutter_cuti_karyawan
flutter run -d chrome
```

### 3. Jalankan di Android Emulator
```bash
flutter run
```

### 4. Menghubungkan ke HP Fisik (via Wi-Fi)
1. Hubungkan HP dan PC ke jaringan Wi-Fi yang sama.
2. Cek IP Komputer Anda (misal `192.168.1.15`).
3. Di aplikasi Flutter (Layar Login atau Profil), klik ikon pengaturan server dan ubah URL ke:
   ```
   http://192.168.1.15/Cuti_Karyawan/api
   ```

---

## 📦 Cara Build Aplikasi

### 1. Build APK Android
Hasil file `.apk` dapat langsung diinstal di HP Android:
```bash
flutter build apk --release
```
*Output: `build/app/outputs/flutter-apk/app-release.apk`*

### 2. Build Web (Untuk Ditaruh di Laragon / Hosting)
```bash
flutter build web --release
```
*Output: Folder `build/web/` dapat disalin ke folder web server Anda.*

### 3. Build iOS (IPA)
```bash
flutter build ipa --release
```

---

## 👥 Akun Demo untuk Pengujian

| Role | Username / Email | Password |
| :--- | :--- | :--- |
| **Admin / HRD** | `hrd@nakakin.co.id` | `password123` |
| **Atasan (Spv QC)** | `hendra.qc@nakakin.co.id` | `password123` |
| **Karyawan (Operator)** | `budi.santoso@nakakin.co.id` | `password123` |
