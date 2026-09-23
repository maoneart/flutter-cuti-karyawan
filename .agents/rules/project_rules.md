# Aturan & Panduan Pengembangan E-Cuti Karyawan

## 1. Master Data Roles & 15 Departemen
- Roles: `operator` (Level 1), `staff` (Level 2), `leader` (Level 3), `supervisor` (Level 4), `manager` (Level 6), `hrd` (Level 7), `superadmin`/`admin` (Level 8).
- 15 Departemen: Accounting, Casting, Core, Diecasting, Engineering, Fettling, GA, HRD, Machining, Marketing, Maintenance, PPIC, Purchasing, QC, QC Line.

## 2. Approval Bertingkat (Strict Multi-Tier)
- Operator/Staff (Level 1-2) -> Leader/Spv Dept (Tier 1) -> Plant Manager (Tier 2, 15 Dept) -> HRD/SuperAdmin (Tier 3 Final).
- Leader/Supervisor (Level 3-4) -> Bypass ke Plant Manager (Tier 2) -> HRD (Tier 3).
- Manager (Level 6) -> Bypass ke HRD (Tier 3).
- Tombol Setujui/Tolak hanya aktif bagi peran yang sedang gilirannya (`canApproveNow = true`). Role yang belum gilirannya memiliki tombol disabled.
- Jika ditolak di tahap mana pun, status langsung `rejected` dan proses berhenti.
- Kuota cuti terpotong HANYA jika HRD/SuperAdmin menyetujui pengajuan.

## 3. Standar Flutter & CI/CD
- Gunakan `isScrollControlled: true` untuk `showModalBottomSheet`.
- CI pipeline: `flutter create --platforms=android,web,ios .` & `flutter build apk --release --no-tree-shake-icons`.
- Pastikan seluruh halaman mendukung tema terang & gelap (Dark Mode).

## 4. Webbase & Backend
- Jaga integritas header PHP (`ob_start()`) agar tidak muncul warning `headers already sent`.
- Pertahankan menu pengaturan Logo, Surat Cuti, dan Master Data di sidebar webbase.
