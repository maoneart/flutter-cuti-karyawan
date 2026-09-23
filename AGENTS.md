# E-Cuti Karyawan - Project Blueprint & Operating Guidelines

Dokumen ini berisi seluruh ringkasan aturan bisnis, arsitektur sistem, hierarki approval bertingkat, panduan Flutter & Backend, serta catatan penting agar seluruh pengembangan aplikasi (Web & Mobile Flutter) selalu sinkron dan konsisten.

---

## 1. Master Data Role, Jabatan & 15 Departemen

### A. Daftar Lengkap Role & Tingkat Hierarki
1. **Operator Produksi** (`role = 'operator'`, Level 1):
   * Mengajukan cuti pribadi, upload surat dokter (format gambar/PDF), cek sisa kuota, lihat riwayat cuti & kalender bersama.
2. **Staff** (`role = 'staff'`, Level 2):
   * Mengajukan cuti pribadi, cek sisa kuota, lihat riwayat cuti & kalender bersama.
3. **Leader** (`role = 'leader'`, Level 3):
   * Approval Tier 1 bagi operator & staff di **1 departemen yang sama**.
   * Pengajuan cuti pribadi (otomatis bypass Tier 1 ke Tier 2).
4. **Supervisor / Spv** (`role = 'supervisor'`, Level 4):
   * Approval Tier 1 bagi operator & staff di **1 departemen yang sama**.
   * Pengajuan cuti pribadi (otomatis bypass Tier 1 ke Tier 2).
5. **Department / Plant Manager** (`role = 'manager'`, Level 6):
   * Approval Tier 2 mencakup **seluruh 15 departemen** pabrik.
   * Monitoring status kehadiran & kalender produksi pabrik.
6. **HRD** (`role = 'hrd'`, Level 7):
   * Approval Tier 3 (Final) seluruh departemen & eksekusi pemotongan kuota cuti resmi.
   * Pengelolaan data seluruh karyawan (CRUD data karyawan).
7. **Super Admin / Admin** (`role = 'superadmin'` / `admin`, Level 8):
   * Hak akses penuh sistem (Master Data Departemen, Jabatan, Pengaturan Web, Kop Surat, Logo Perusahaan, dan Hak Istimewa Approval).

### B. Daftar 15 Departemen Resmi (PT. Nakakin Indonesia)
1. **Accounting** (ID 1)
2. **Casting** (ID 2)
3. **Core** (ID 3)
4. **Diecasting** (ID 4)
5. **Engineering** (ID 5)
6. **Fettling** (ID 6)
7. **GA (General Affairs)** (ID 7)
8. **HRD (Human Resources Department)** (ID 8)
9. **Machining** (ID 9)
10. **Marketing** (ID 10)
11. **Maintenance** (ID 11)
12. **PPIC** (ID 12)
13. **Purchasing** (ID 13)
14. **QC (Quality Control)** (ID 14)
15. **QC Line** (ID 15)

---

## 2. Alur Persetujuan Cuti Bertingkat (Strict Multi-Tier Approval)

### A. Matriks Alur Hirarki Berdasarkan Role Pemohon
| Role Pemohon | Level | Tier 1 (Leader/Spv Dept) | Tier 2 (Plant Manager) | Tier 3 (HRD / Super Admin) |
| :--- | :---: | :---: | :---: | :---: |
| **Operator Produksi** | 1 | Wajib Review | Wajib Review | Persetujuan Final & Potong Kuota |
| **Staff** | 2 | Wajib Review | Wajib Review | Persetujuan Final & Potong Kuota |
| **Leader** | 3 | *Bypass (Langsung ke Tier 2)* | Wajib Review | Persetujuan Final & Potong Kuota |
| **Supervisor** | 4 | *Bypass (Langsung ke Tier 2)* | Wajib Review | Persetujuan Final & Potong Kuota |
| **Plant Manager** | 6 | *Bypass* | *Bypass* | Persetujuan Final & Potong Kuota |
| **HRD / SuperAdmin** | 7-8 | *Bypass* | *Bypass* | Persetujuan Final Mandiri |

### B. Aturan & Validasi Approval
* **Status Tahapan (`approval_step`)**:
  * `pending_spv`: Menunggu persetujuan Leader/Supervisor Departemen terkait.
  * `pending_manager`: Menunggu persetujuan Plant Manager (lintas 15 departemen).
  * `pending_hrd`: Menunggu persetujuan akhir HRD/Super Admin.
  * `completed`: Sudah selesai (disetujui seluruhnya atau ditolak).
* **Gating Tombol Aksi (Flutter & Web)**:
  * Tombol **Setujui** dan **Tolak** **HANYA AKTIF** bagi user yang gilirannya tiba (`canApproveNow = true`).
  * Jika belum gilirannya (misal HRD melihat pengajuan yang masih `pending_spv`), tombol **HARUS DINONAKTIFKAN / DISABLED** (`onPressed: null`) dan menampilkan pesan status yang jelas.
* **Jika Ditolak**:
  * Pengajuan langsung berstatus `rejected`, `approval_step = completed`.
  * Proses approval berhenti seketika dan tidak diteruskan ke tingkat atasnya.
* **Pemotongan Kuota Cuti**:
  * Sisa kuota cuti karyawan **HANYA TERPOTONG** setelah HRD / Super Admin menyetujui pengajuan di tahap final (`status = approved`).

---

## 3. Modul Panduan & Tutorial PDF (4 Peran)

Sistem menyediakan 4 dokumen PDF panduan operasional terpisah di endpoint:
* `GET /api/docs/panduan.php?role=operator` : Panduan Operator & Staff
* `GET /api/docs/panduan.php?role=leader` : Panduan Leader & Supervisor
* `GET /api/docs/panduan.php?role=manager` : Panduan Plant Manager
* `GET /api/docs/panduan.php?role=hrd` : Panduan HRD & Admin Panel

---

## 4. Standar Kode & Aturan Flutter (Mobile)

1. **Modal Bottom Sheet**:
   * Selalu gunakan `isScrollControlled: true` (JANGAN gunakan `isScrollable` yang hanya milik `TabBar`).
2. **Kompabilitas UI & Styling**:
   * Dukung Light Mode & Dark Mode (`Theme.of(context).brightness == Brightness.dark`).
   * Gunakan helper `AppTheme` dan `CupertinoIcons` untuk tampilan profesional bergaya modern.
3. **Pemberitahuan & Badging**:
   * Lonceng notifikasi dan badge approval di dashboard harus mencerminkan jumlah pengajuan yang relevan dengan peran user saat itu.

---

## 5. Pipeline CI/CD GitHub Actions (`build_apk.yml`)

1. **File Workflow**: `.github/workflows/build_apk.yml`
2. **Langkah Kompilasi**:
   * Platform Generator: `flutter create --platforms=android,web,ios .` & `flutter pub get`
   * Android Build: `flutter build apk --release --no-tree-shake-icons` (JDK 17 Temurin)
   * iOS Build: `flutter build ios --release --no-codesign` -> Package `.ipa` Payload.
3. **Artifact Output**:
   * `Cuti-Karyawan-Release-APK` (`app-release.apk`)
   * `Cuti-Karyawan-iOS-IPA` (`CutiKaryawan-iOS.ipa`)

---

## 6. Arsitektur Webbase & API Backend (PHP Native)

* **Database**: MySQL / MariaDB (`pengajuan_cuti`, `karyawan`, `departemen`, `jabatan`, `approval_logs`, `notifikasi`).
* **API Endpoints Utama**:
  * Auth: `/api/auth/login.php`, `/api/auth/profile.php`
  * Cuti: `/api/leaves/create.php`, `/api/leaves/history.php`, `/api/leaves/detail.php`, `/api/leaves/types.php`
  * Approvals: `/api/approvals/list.php`, `/api/approvals/action.php`
  * Karyawan & Master: `/api/employees/list.php`, `/api/employees/create.php`, `/api/public/board.php`
* **Keamanan & Session Web**:
  * Hindari output HTML sebelum header redirect (`ob_start()` di awal file dan session management yang rapi).
  * Menu sidebar admin webbase mencakup: Dashboard, Data Cuti, Persetujuan Cuti, Data Karyawan, Departemen & Jabatan, Pengaturan Kop/Logo Perusahaan.
