<?php
/**
 * My Leaves History View (Tailwind CSS Edition)
 * PT. Nakakin Indonesia Leave Management System
 */

$pageTitle = 'Riwayat Cuti Saya';
require_once __DIR__ . '/../layouts/header.php';

$pdo = getDbConnection();
$userId = $currentUser['id'];

$stmt = $pdo->prepare("
    SELECT lr.*, lt.nama_cuti, lt.potong_kuota, 
           ap.nama_lengkap as nama_atasan, ap.nik as nik_atasan
    FROM pengajuan_cuti lr
    JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
    LEFT JOIN karyawan ap ON lr.approved_by = ap.id
    WHERE lr.employee_id = ?
    ORDER BY lr.created_at DESC
");
$stmt->execute([$userId]);
$leaves = $stmt->fetchAll();
?>

<div class="w-full space-y-6">

    <!-- Table Card with Integrated Action Header -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.04)] overflow-hidden">
        <div class="p-5 sm:px-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/40">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold border border-blue-200/60 shadow-2xs">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Daftar Permohonan Cuti</h3>
                    <p class="text-[11px] text-slate-400 font-medium hidden sm:block">Seluruh riwayat izin cuti Anda beserta status terbarunya</p>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="px-3.5 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-black border border-slate-200/60 shadow-2xs">
                    Total: <?= count($leaves) ?> Data
                </span>
                <a href="<?= BASE_URL ?>/index.php?page=leave-create" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-extrabold text-xs shadow-md shadow-rose-600/30 transition transform active:scale-95">
                    <i class="fa-solid fa-calendar-plus"></i>
                    <span>Ajukan Cuti Baru</span>
                </a>
            </div>
        </div>

        <div class="p-4 sm:p-6">
            <table class="w-full text-left text-xs datatable">
                <thead>
                    <tr>
                        <th class="text-center w-12">No</th>
                        <th class="min-w-[150px]">No. Surat</th>
                        <th class="min-w-[220px]">Jenis Cuti</th>
                        <th class="min-w-[160px]">Periode Cuti</th>
                        <th class="min-w-[100px] text-center">Durasi</th>
                        <th class="min-w-[160px] max-w-[220px]">Alasan</th>
                        <th class="min-w-[130px] text-center">Status</th>
                        <th class="min-w-[160px]">Pemeriksa</th>
                        <th class="min-w-[100px] text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($leaves as $leave): ?>
                        <tr>
                            <td class="text-center">
                                <span class="w-6 h-6 rounded-lg bg-slate-100/90 inline-flex items-center justify-center text-[11px] text-slate-600 font-black">
                                    <?= $no++ ?>
                                </span>
                            </td>
                            <td class="whitespace-nowrap">
                                <a href="<?= BASE_URL ?>/index.php?page=leave-detail&id=<?= $leave['id'] ?>" class="font-mono font-black text-blue-600 hover:text-blue-800 bg-blue-50/70 hover:bg-blue-100/80 px-2.5 py-1 rounded-lg border border-blue-200/60 transition inline-block">
                                    <?= htmlspecialchars($leave['nomor_surat']) ?>
                                </a>
                                <div class="text-[10.5px] text-slate-400 mt-0.5"><?= date('d/m/Y H:i', strtotime($leave['created_at'])) ?></div>
                            </td>
                            <td class="whitespace-nowrap">
                                <?= renderLeaveTypeDisplay($leave['nama_cuti'], $leave['potong_kuota']) ?>
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="font-bold text-slate-800"><?= formatTanggalIndo($leave['tanggal_mulai']) ?></div>
                                <div class="text-[11px] text-slate-400 font-medium">s/d <?= formatTanggalIndo($leave['tanggal_selesai']) ?></div>
                            </td>
                            <td class="text-center whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 border border-blue-200/80 font-black text-xs shadow-2xs">
                                    <?= $leave['total_hari'] ?> Hari
                                </span>
                            </td>
                            <td class="max-w-[220px]">
                                <div class="truncate text-slate-600 font-medium text-xs" title="<?= htmlspecialchars($leave['alasan']) ?>">
                                    <?= htmlspecialchars($leave['alasan']) ?>
                                </div>
                            </td>
                            <td class="text-center whitespace-nowrap"><?= getStatusBadge($leave['status']) ?></td>
                            <td class="whitespace-nowrap">
                                <?php if ($leave['nama_atasan']): ?>
                                    <div class="font-extrabold text-slate-800 leading-tight"><?= htmlspecialchars($leave['nama_atasan']) ?></div>
                                    <div class="text-[10.5px] text-slate-400 font-medium"><?= formatDateTimeIndo($leave['approved_at']) ?></div>
                                <?php else: ?>
                                    <span class="text-slate-400 font-semibold">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <a href="<?= BASE_URL ?>/index.php?page=leave-detail&id=<?= $leave['id'] ?>" class="w-8 h-8 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold flex items-center justify-center transition shadow-2xs" title="Lihat Detail">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>

                                    <?php if ($leave['status'] === 'approved'): ?>
                                        <a href="<?= BASE_URL ?>/index.php?page=leave-print&id=<?= $leave['id'] ?>" target="_blank" class="w-8 h-8 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-600 font-bold flex items-center justify-center transition shadow-2xs" title="Cetak Surat Izin Cuti">
                                            <i class="fa-solid fa-print text-xs"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($leave['status'] === 'pending'): ?>
                                        <button type="button" class="w-8 h-8 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold flex items-center justify-center transition shadow-2xs" onclick="confirmDelete('<?= BASE_URL ?>/index.php?page=leave-action&action=cancel&id=<?= $leave['id'] ?>', 'Batalkan pengajuan cuti ini?')" title="Batalkan Pengajuan">
                                            <i class="fa-solid fa-ban text-xs"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
