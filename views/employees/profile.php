<?php
/**
 * Personal Profile View (Tailwind CSS Edition)
 * PT. Nakakin Indonesia Leave Management System
 */

$pageTitle = 'Profil Saya';
require_once __DIR__ . '/../layouts/header.php';

$pdo = getDbConnection();
$emp = getCurrentUser();

// Fetch yearly approved leaves breakdown
$stmtYearly = $pdo->prepare("
    SELECT 
        YEAR(tanggal_mulai) as tahun,
        COUNT(id) as total_pengajuan,
        SUM(total_hari) as total_hari_cuti,
        SUM(CASE WHEN leave_type_id = 3 THEN total_hari ELSE 0 END) as cuti_tahunan,
        SUM(CASE WHEN leave_type_id IN (1, 2) THEN total_hari ELSE 0 END) as cuti_sakit,
        SUM(CASE WHEN leave_type_id NOT IN (1, 2, 3) THEN total_hari ELSE 0 END) as cuti_khusus
    FROM pengajuan_cuti
    WHERE employee_id = ? AND status = 'approved'
    GROUP BY YEAR(tanggal_mulai)
    ORDER BY tahun DESC
");
$stmtYearly->execute([$emp['id']]);
$yearlyStats = $stmtYearly->fetchAll();

// Fetch personal quota change history
$stmtPersonalLogs = $pdo->prepare("
    SELECT qh.*, admin.nama_lengkap as nama_admin
    FROM riwayat_kuota_cuti qh
    LEFT JOIN karyawan admin ON qh.created_by = admin.id
    WHERE qh.employee_id = ?
    ORDER BY qh.created_at DESC
    LIMIT 10
");
$stmtPersonalLogs->execute([$emp['id']]);
$personalLogs = $stmtPersonalLogs->fetchAll();
?>

<div class="w-full space-y-6">

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        
        <!-- Profile Bento Left (4 Cols) -->
        <div class="md:col-span-4 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft text-center flex flex-col items-center">
            <div class="w-20 h-20 rounded-3xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-3xl flex items-center justify-center shadow-lg shadow-blue-600/30 mb-4">
                <?= strtoupper(substr($emp['nama_lengkap'], 0, 1)) ?>
            </div>
            <h3 class="text-base font-extrabold text-slate-900 leading-tight"><?= htmlspecialchars($emp['nama_lengkap']) ?></h3>
            <div class="mt-1 mb-3"><?= getRoleBadge($emp['role']) ?></div>
            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 font-mono font-bold text-xs">NIK: <?= htmlspecialchars($emp['nik']) ?></span>

            <div class="w-full mt-6 pt-4 border-t border-slate-100 space-y-3 text-xs text-left">
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-400 font-medium">Departemen</span>
                    <strong class="text-slate-800 font-bold"><?= htmlspecialchars($emp['nama_dept']) ?></strong>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-400 font-medium">Jabatan</span>
                    <strong class="text-slate-800 font-bold"><?= htmlspecialchars($emp['nama_jabatan']) ?></strong>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-400 font-medium">Masa Kerja</span>
                    <strong class="text-purple-600 font-bold"><?= hitungMasaKerja($emp['tanggal_masuk']) ?></strong>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-400 font-medium">Jatah Kuota <?= date('Y') ?></span>
                    <strong class="text-slate-800 font-bold"><?= $emp['kuota_cuti'] ?> Hari</strong>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-400 font-medium">Terpakai Tahun Ini</span>
                    <strong class="text-amber-600 font-bold"><?= $emp['cuti_terpakai'] ?> Hari</strong>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400 font-medium">Sisa Saldo Cuti</span>
                    <strong class="text-blue-600 font-black text-sm"><?= $emp['sisa_cuti'] ?> Hari</strong>
                </div>
            </div>
        </div>

        <!-- Settings Form Right (8 Cols) -->
        <div class="md:col-span-8 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-soft">
            <h3 class="text-base font-extrabold text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-user-pen text-blue-600"></i> Pengaturan Kontak & Password Akun
            </h3>

            <form action="<?= BASE_URL ?>/index.php?page=profile-update" method="POST" class="space-y-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap Akun <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($emp['nama_lengkap']) ?>" required
                           placeholder="Masukkan nama lengkap Anda..."
                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-bold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="<?= htmlspecialchars($emp['email']) ?>" required
                               placeholder="nama@perusahaan.co.id"
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">No. Handphone / WhatsApp <span class="text-rose-500">*</span></label>
                        <input type="text" name="no_hp" value="<?= htmlspecialchars($emp['no_hp'] ?? '') ?>" required
                               placeholder="081234567890"
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Domisili</label>
                    <textarea name="alamat" rows="2" placeholder="Alamat tempat tinggal saat ini..." class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-medium focus:outline-none focus:ring-4 focus:ring-blue-500/15"><?= htmlspecialchars($emp['alamat'] ?? '') ?></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 space-y-3">
                    <span class="block font-bold text-slate-700 uppercase tracking-wider">Ganti Password Login (Opsional)</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-slate-500 mb-1">Password Baru</label>
                            <input type="password" name="new_password" placeholder="Kosongkan jika tidak diubah"
                                   class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="block text-slate-500 mb-1">Konfirmasi Password</label>
                            <input type="password" name="confirm_password" placeholder="Ulangi password baru"
                                   class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 text-right">
                    <button type="submit" class="px-7 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs shadow-md shadow-blue-600/30 transition transform hover:-translate-y-0.5">
                        Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- Yearly Leave & Quota History Bento Cards -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        
        <!-- Yearly Usage Summary (6 Cols) -->
        <div class="md:col-span-6 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Rekap Cuti Terpakai per Tahun</h3>
                        <p class="text-[11px] text-slate-400">Total hari cuti yang telah disetujui tiap tahunnya</p>
                    </div>
                </div>
            </div>

            <?php if (empty($yearlyStats)): ?>
                <div class="text-center py-6 text-slate-400 text-xs">
                    <i class="fa-solid fa-folder-open text-2xl mb-2 text-slate-300"></i>
                    <p>Belum ada riwayat cuti yang disetujui.</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($yearlyStats as $ystat): ?>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="px-3 py-1 rounded-xl bg-blue-600 text-white font-black text-xs">
                                    Periode Tahun <?= $ystat['tahun'] ?>
                                </span>
                                <span class="text-xs font-black text-slate-900">
                                    Total Diambil: <strong class="text-blue-600"><?= $ystat['total_hari_cuti'] ?> Hari</strong> (<?= $ystat['total_pengajuan'] ?>x izin)
                                </span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-200/60 text-[11px]">
                                <div class="p-2 rounded-xl bg-white border border-slate-100 text-center">
                                    <span class="text-slate-400 block text-[10px]">Cuti Tahunan</span>
                                    <strong class="text-blue-600 font-extrabold"><?= $ystat['cuti_tahunan'] ?> Hari</strong>
                                </div>
                                <div class="p-2 rounded-xl bg-white border border-slate-100 text-center">
                                    <span class="text-slate-400 block text-[10px]">Izin Sakit</span>
                                    <strong class="text-emerald-600 font-extrabold"><?= $ystat['cuti_sakit'] ?> Hari</strong>
                                </div>
                                <div class="p-2 rounded-xl bg-white border border-slate-100 text-center">
                                    <span class="text-slate-400 block text-[10px]">Khusus / Lain</span>
                                    <strong class="text-purple-600 font-extrabold"><?= $ystat['cuti_khusus'] ?> Hari</strong>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Personal Quota Audit History (6 Cols) -->
        <div class="md:col-span-6 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Riwayat Mutasi & Alokasi Kuota</h3>
                        <p class="text-[11px] text-slate-400">Log penambahan kuota tahunan dan pemotongan cuti</p>
                    </div>
                </div>
            </div>

            <?php if (empty($personalLogs)): ?>
                <div class="text-center py-6 text-slate-400 text-xs">
                    <i class="fa-solid fa-clock-rotate-left text-2xl mb-2 text-slate-300"></i>
                    <p>Belum ada riwayat mutasi kuota.</p>
                </div>
            <?php else: ?>
                <div class="space-y-2.5 max-h-[350px] overflow-y-auto">
                    <?php foreach ($personalLogs as $plog): ?>
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-3 text-xs">
                            <div class="space-y-0.5">
                                <strong class="block text-slate-800 text-xs font-bold"><?= htmlspecialchars($plog['keterangan']) ?></strong>
                                <span class="text-[10px] text-slate-400 font-mono">
                                    <?= date('d M Y, H:i', strtotime($plog['created_at'])) ?> WIB
                                </span>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="px-2 py-0.5 rounded-full text-xs font-black <?= $plog['perubahan'] >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>">
                                    <?= $plog['perubahan'] >= 0 ? '+' . $plog['perubahan'] : $plog['perubahan'] ?> Hari
                                </span>
                                <div class="text-[10px] text-slate-400 mt-0.5 font-mono">
                                    Saldo: <strong class="text-slate-700"><?= $plog['kuota_sesudah'] ?> Hari</strong>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
