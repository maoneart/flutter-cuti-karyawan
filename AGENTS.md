# E-Cuti Karyawan - Project Blueprint & Operating Guidelines

Dokumen ini berisi seluruh ringkasan aturan bisnis, arsitektur sistem, hierarki approval bertingkat, panduan Flutter & Backend, serta catatan penting agar seluruh pengembangan aplikasi (Web & Mobile Flutter) selalu sinkron dan konsisten.

---

## 1. Alur Persetujuan Cuti Bertingkat (Strict Multi-Tier Approval)

### A. Alur Hirarki Pengajuan
1. **Pengajuan oleh Operator / Staff (Level 1 - 2)**:
   $$\text{Operator/Staff} \longrightarrow \text{Leader/Supervisor (Tier 1)} \longrightarrow \text{Plant Manager (Tier 2)} \longrightarrow \text{HRD / Super Admin (Tier 3 - Final)}$$
2. **Pengajuan oleh Leader / Supervisor (Level 3 - 4)**:
   $$\text{Leader/Supervisor} \longrightarrow \text{Plant Manager (Tier 2)} \longrightarrow \text{HRD / Super Admin (Tier 3 - Final)}$$
3. **Pengajuan oleh Plant Manager (Level 5 - 6)**:
   $$\text{Plant Manager} \longrightarrow \text{HRD / Super Admin (Tier 3 - Final)}$$

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
  * Sisa cuti karyawan **HANYA TERPOTONG** setelah HRD / Super Admin menyetujui pengajuan di tahap final (`status = approved`).

---

## 2. Hak Akses & Peran Pengguna (Role & Permissions)

| Role / Jabatan | Level | Cakupan Akses Approval | Fitur Khusus |
| :--- | :---: | :--- | :--- |
| **Operator / Staff** | 1 - 2 | Hanya cuti pribadi | Pengajuan cuti, upload surat dokter, lihat kuota & riwayat |
| **Leader / Supervisor** | 3 - 4 | Cuti tim dalam **1 Departemen yang sama** | Review Tier 1 tim departemen, filter departemen sendiri |
| **Plant Manager** | 5 - 6 | Pengajuan cuti dari **Seluruh 15 Departemen** | Review Tier 2 pabrik, monitoring produksi |
| **HRD / Admin / SuperAdmin** | 7 - 9 | Seluruh departemen (Final Tier 3) | Approval Final, Pengaturan Web, Kelola Karyawan, Master Data |

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
