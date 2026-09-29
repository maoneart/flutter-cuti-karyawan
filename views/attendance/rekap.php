<?php
/**
 * Attendance Rekap & AWOL Monitoring View (HRD / Management)
 * Nakakin Mobile - Standard PKB PT. Nakakin Indonesia
 */

$pageTitle = 'Rekap Mangkir & Shift (HRD)';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6">

    <!-- Header Banner -->
    <div class="p-5 rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border border-indigo-900/40 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600/30 border border-indigo-400/30 flex items-center justify-center text-indigo-300 text-xl font-black shadow-inner flex-shrink-0">
                <i class="fa-solid fa-clipboard-user"></i>
            </div>
            <div>
                <h2 class="text-base sm:text-lg font-black tracking-tight text-white">Monitoring Ketidakhadiran & Mangkir Shift</h2>
                <p class="text-xs text-slate-300 mt-0.5">Tembusan catatan lapangan Leader & Supervisor untuk penyesuaian Payroll HRD</p>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="<?= BASE_URL ?>/index.php" class="flex items-center gap-2 flex-wrap text-xs">
            <input type="hidden" name="page" value="absensi-rekap">
            
            <select name="dept_id" class="bg-slate-800 border border-slate-700 text-white rounded-xl px-2.5 py-1.5 font-semibold outline-none cursor-pointer">
                <option value="0">Semua Departemen</option>
                <?php foreach ($departments as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= $deptFilter === (int)$d['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['nama_dept']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="bg-slate-800 border border-slate-700 text-white rounded-xl px-2.5 py-1.5 font-semibold outline-none cursor-pointer">
            <span class="text-slate-400">s/d</span>
            <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="bg-slate-800 border border-slate-700 text-white rounded-xl px-2.5 py-1.5 font-semibold outline-none cursor-pointer">

            <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
        </form>
    </div>

    <!-- Summary Stats -->
    <?php
    $totalRecs = count($records);
    $totalMangkirStill = 0;
    $totalRevised = 0;
    foreach ($records as $r) {
        if ($r['status_revisi'] === 'revised') $totalRevised++;
        else $totalMangkirStill++;
    }
    ?>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-sm font-bold">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Catatan</div>
                <div class="text-lg font-black text-slate-900"><?= $totalRecs ?> Kasus</div>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-sm font-bold">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Mangkir Murni (Potong Gaji/SP)</div>
                <div class="text-lg font-black text-rose-600"><?= $totalMangkirStill ?> Kasus</div>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">
                <i class="fa-solid fa-check-double"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Telah Direvisi Leader</div>
                <div class="text-lg font-black text-emerald-600"><?= $totalRevised ?> Kasus</div>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.04)] overflow-hidden p-5">
        <div class="overflow-x-auto">
            <table class="datatable w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100/75 border-b border-slate-200 text-slate-600 font-extrabold uppercase tracking-wider text-[10.5px]">
                        <th class="py-3 px-3 w-10 text-center">No</th>
                        <th class="py-3 px-3">Tanggal & Shift</th>
                        <th class="py-3 px-3">Karyawan</th>
                        <th class="py-3 px-3">Departemen</th>
                        <th class="py-3 px-3 text-center">Status Akhir</th>
                        <th class="py-3 px-3">Dicatat Oleh (Leader)</th>
                        <th class="py-3 px-3">Bukti / Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400 font-bold">
                                Tidak ada data catatan ketidakhadiran pada rentang tanggal ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($records as $r): ?>
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-3 text-center text-slate-400 font-semibold"><?= $no++ ?></td>
                                
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <div class="font-bold text-slate-900"><?= formatTanggalIndo($r['tanggal']) ?></div>
                                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded-md text-[9.5px] font-extrabold <?= $r['shift'] === 'Shift 2 (Maju)' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-amber-50 text-amber-700 border border-amber-200' ?>">
                                        <?= htmlspecialchars($r['shift']) ?>
                                    </span>
                                </td>

                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900"><?= htmlspecialchars($r['nama_lengkap']) ?></div>
                                    <div class="text-[10px] text-slate-400 font-mono"><?= htmlspecialchars($r['nik']) ?> &bull; <?= htmlspecialchars($r['nama_jabatan']) ?></div>
                                </td>

                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 font-bold text-slate-700 text-[10.5px]">
                                        <?= htmlspecialchars($r['nama_dept']) ?>
                                    </span>
                                </td>

                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <?php if ($r['status_revisi'] === 'revised'): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            <i class="fa-solid fa-check-double"></i> Direvisi: <?= htmlspecialchars($r['direvisi_menjadi']) ?>
                                        </span>
                                        <div class="text-[9.5px] text-slate-400 mt-0.5"><?= formatDateTimeIndo($r['direvisi_pada']) ?></div>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-black bg-rose-100 text-rose-800 border border-rose-300">
                                            <i class="fa-solid fa-triangle-exclamation"></i> MANGKIR (ALPHA)
                                        </span>
                                        <div class="text-[9.5px] text-rose-600 font-bold mt-0.5">Potong Gaji & Premi</div>
                                    <?php endif; ?>
                                </td>

                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-800 text-[11px]"><?= htmlspecialchars($r['nama_leader'] ?? 'Leader') ?></div>
                                    <div class="text-[10px] text-slate-400"><?= formatDateTimeIndo($r['created_at']) ?></div>
                                </td>

                                <td class="py-3 px-3">
                                    <?php if ($r['status_revisi'] === 'revised'): ?>
                                        <div class="text-[11px] text-slate-700 font-medium">
                                            <?= htmlspecialchars($r['alasan_revisi'] ?: '-') ?>
                                        </div>
                                        <?php if (!empty($r['bukti_lampiran'])): ?>
                                            <a href="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($r['bukti_lampiran']) ?>" 
                                               target="_blank" 
                                               class="mt-1 inline-flex items-center gap-1 text-[10.5px] font-bold text-blue-600 hover:text-blue-700 underline">
                                                <i class="fa-solid fa-paperclip"></i> Lihat Berkas Bukti
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-[11px] text-slate-500"><?= htmlspecialchars($r['keterangan_mangkir'] ?: '-') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
