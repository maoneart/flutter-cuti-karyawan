# Aturan & Panduan Pengembangan E-Cuti Karyawan

## 1. Approval Bertingkat (Strict Multi-Tier)
- Operator -> Leader/Spv (Tier 1) -> Plant Manager (Tier 2) -> HRD/SuperAdmin (Tier 3/Final)
- Leader -> Langsung Plant Manager (Tier 2) -> HRD (Tier 3)
- Tombol Setujui/Tolak hanya aktif bagi peran yang sedang gilirannya (`canApproveNow = true`). Role yang belum gilirannya memiliki tombol disabled.
- Jika ditolak di tahap mana pun, status langsung `rejected` dan proses berhenti.
- Kuota cuti terpotong HANYA jika HRD/SuperAdmin menyetujui pengajuan.

## 2. Standar Flutter & CI/CD
- Gunakan `isScrollControlled: true` untuk `showModalBottomSheet`.
- CI pipeline: `flutter create --platforms=android,web,ios .` & `flutter build apk --release --no-tree-shake-icons`.
- Pastikan seluruh halaman mendukung tema terang & gelap (Dark Mode).

## 3. Webbase & Backend
- Jaga integritas header PHP (`ob_start()`) agar tidak muncul warning `headers already sent`.
- Pertahankan menu pengaturan Logo, Surat Cuti, dan Master Data di sidebar webbase.
