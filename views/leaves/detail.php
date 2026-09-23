<?php
/**
 * Leave Request Detail View (Tailwind CSS Edition)
 * PT. Nakakin Indonesia Leave Management System
 */

$pageTitle = 'Detail Permohonan Cuti';
require_once __DIR__ . '/../layouts/header.php';

$id = (int)($_GET['id'] ?? 0);
$pdo = getDbConnection();

$stmt = $pdo->prepare("
    SELECT lr.*, 
           e.nik, e.nama_lengkap, e.email, e.no_hp, e.tanggal_masuk, e.kuota_cuti, e.sisa_cuti, e.cuti_terpakai, e.departemen_id,
           d.nama_dept, d.kode_dept,
           p.nama_jabatan,
           lt.nama_cuti, lt.potong_kuota, lt.deskripsi as deskripsi_cuti,
           ap.nama_lengkap as nama_atasan, ap.nik as nik_atasan, pos_ap.nama_jabatan as jabatan_atasan
    FROM pengajuan_cuti lr
    JOIN karyawan e ON lr.employee_id = e.id
    JOIN departemen d ON e.departemen_id = d.id
    JOIN jabatan p ON e.jabatan_id = p.id
    JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
    LEFT JOIN karyawan ap ON lr.approved_by = ap.id
    LEFT JOIN jabatan pos_ap ON ap.jabatan_id = pos_ap.id
    WHERE lr.id = ?
");
$stmt->execute([$id]);
$leave = $stmt->fetch();

if (!$leave) {
    echo '<div class="p-6 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 font-bold text-sm">Data permohonan cuti tidak ditemukan!</div>';
    require_once __DIR__ . '/../layouts/footer.php';
    exit;
}

$userRole = strtolower($currentUser['role'] ?? '');
$userLevel = (int)($currentUser['level_hierarki'] ?? 1);
$isHRD = in_array($userRole, ['admin', 'superadmin', 'hrd']) || $userLevel >= 7;
$isManager = $userRole === 'manager' || ($userLevel >= 5 && $userLevel <= 6);
$isSpv = in_array($userRole, ['supervisor', 'leader', 'atasan']) || ($userLevel >= 3 && $userLevel <= 4);

$canView = ($currentUser['id'] == $leave['employee_id']) || $isHRD || $isManager || ($currentUser['departemen_id'] == $leave['departemen_id']);

if (!$canView) {
    echo '<div class="p-6 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 font-bold text-sm">Akses ditolak! Anda tidak memiliki izin untuk melihat pengajuan ini.</div>';
    require_once __DIR__ . '/../layouts/footer.php';
    exit;
}

$step = $leave['approval_step'] ?? 'pending_spv';
$canApprove = false;
if ($leave['status'] === 'pending' && $currentUser['id'] != $leave['employee_id']) {
    if ($step === 'pending_spv') {
        $canApprove = $isSpv && ($currentUser['departemen_id'] == $leave['departemen_id']);
    } elseif ($step === 'pending_manager') {
        $canApprove = $isManager;
    } elseif ($step === 'pending_hrd') {
        $canApprove = $isHRD;
    }
}
?>

<div class="w-full space-y-6">

    <!-- Header Actions -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <a href="javascript:history.back()" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-100 border border-slate-200 text-xs font-bold text-slate-700 shadow-sm transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
        </a>
        <div class="flex items-center gap-2">
            <?php if ($leave['status'] === 'approved'): ?>
                <a href="<?= BASE_URL ?>/index.php?page=leave-print&id=<?= $leave['id'] ?>" target="_blank" 
                   class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 text-white text-xs font-extrabold shadow-md shadow-rose-600/30 transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-print mr-1"></i> Cetak Surat Izin Cuti Resmi
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Detail Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden">
        
        <!-- Header Profile Banner -->
        <div class="p-6 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-xl flex items-center justify-center shadow-md flex-shrink-0">
                    <?= strtoupper(substr($leave['nama_lengkap'], 0, 1)) ?>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base sm:text-lg font-extrabold text-slate-900"><?= htmlspecialchars($leave['nama_lengkap']) ?></h2>
                        <span class="px-2.5 py-0.5 rounded-full bg-white border border-slate-200 text-slate-700 text-xs font-mono font-bold"><?= htmlspecialchars($leave['nik']) ?></span>
                    </div>
                    <div class="text-xs text-slate-500 font-medium mt-0.5">
                        <?= htmlspecialchars($leave['nama_dept']) ?> &bull; <?= htmlspecialchars($leave['nama_jabatan']) ?>
                    </div>
                </div>
            </div>
            <div>
                <?= getStatusBadge($leave['status']) ?>
            </div>
        </div>

        <div class="p-6 sm:p-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                
                <!-- Left: Details (7 Cols) -->
                <div class="md:col-span-7 space-y-5">
                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Rincian Permohonan Cuti</h4>
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 space-y-3 text-xs">
                            <div class="flex justify-between py-1 border-b border-slate-200/60">
                                <span class="text-slate-500">Nomor Surat</span>
                                <span class="font-mono font-bold text-slate-900"><?= htmlspecialchars($leave['nomor_surat']) ?></span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-200/60">
                                <span class="text-slate-500">Jenis Cuti</span>
                                <span class="font-bold text-blue-600"><?= htmlspecialchars($leave['nama_cuti']) ?></span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-200/60">
                                <span class="text-slate-500">Tanggal Pelaksanaan</span>
                                <span class="font-semibold text-slate-800"><?= formatTanggalIndo($leave['tanggal_mulai']) ?> s/d <?= formatTanggalIndo($leave['tanggal_selesai']) ?></span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-200/60">
                                <span class="text-slate-500">Total Hari Kerja</span>
                                <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-800 font-extrabold"><?= $leave['total_hari'] ?> Hari</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-200/60">
                                <span class="text-slate-500">Alamat Selama Cuti</span>
                                <span class="font-medium text-slate-800"><?= htmlspecialchars($leave['alamat_selama_cuti'] ?: '-') ?></span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-slate-500">Kontak Darurat</span>
                                <span class="font-medium text-slate-800"><?= htmlspecialchars($leave['kontak_darurat'] ?: '-') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Alasan Box -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Alasan / Keperluan Cuti</h4>
                        <div class="p-4 rounded-2xl bg-white border border-slate-200 text-xs text-slate-700 leading-relaxed font-medium">
                            <?= nl2br(htmlspecialchars($leave['alasan'])) ?>
                        </div>
                    </div>

                    <!-- Lampiran File -->
                    <?php if ($leave['attachment']): ?>
                        <div>
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Lampiran Dokumen</h4>
                            <a href="<?= BASE_URL ?>/assets/uploads/attachments/<?= htmlspecialchars($leave['attachment']) ?>" target="_blank" 
                               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition">
                                <i class="fa-solid fa-paperclip"></i>
                                <span>Buka / Unduh Dokumen Lampiran</span>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ($leave['status'] === 'rejected'): ?>
                        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-900 space-y-1">
                            <strong class="font-bold flex items-center gap-1 text-rose-700"><i class="fa-solid fa-circle-xmark"></i> Alasan Penolakan:</strong>
                            <p><?= nl2br(htmlspecialchars($leave['rejection_reason'] ?: '-')) ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right: Employee Balance & Approval Status (5 Cols) -->
                <div class="md:col-span-5 space-y-5">
                    
                    <!-- Balance Widget -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Status Kuota Karyawan</h4>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5 text-xs">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Masa Kerja:</span>
                                <strong class="text-slate-800"><?= hitungMasaKerja($leave['tanggal_masuk']) ?></strong>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Total Kuota:</span>
                                <strong class="text-slate-800"><?= $leave['kuota_cuti'] ?> Hari</strong>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Cuti Terpakai:</span>
                                <strong class="text-amber-600"><?= $leave['cuti_terpakai'] ?> Hari</strong>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-slate-200 font-bold text-sm">
                                <span class="text-slate-900">Sisa Hak Cuti:</span>
                                <span class="text-blue-600"><?= $leave['sisa_cuti'] ?> Hari</span>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Verification Info -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Pemeriksaan Atasan</h4>
                        <?php if ($leave['approved_by']): ?>
                            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 space-y-1">
                                <div class="font-bold text-emerald-700 flex items-center gap-1.5"><i class="fa-solid fa-circle-check"></i> Diverifikasi & Disetujui:</div>
                                <div class="font-extrabold text-slate-900 text-sm"><?= htmlspecialchars($leave['nama_atasan']) ?></div>
                                <div class="text-[11px] text-slate-500"><?= htmlspecialchars($leave['jabatan_atasan']) ?> &bull; NIK: <?= htmlspecialchars($leave['nik_atasan']) ?></div>
                                <div class="text-[11px] text-slate-400 mt-1"><i class="fa-regular fa-clock mr-1"></i> <?= formatDateTimeIndo($leave['approved_at']) ?></div>
                            </div>
                        <?php else: ?>
                            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900">
                                <i class="fa-solid fa-hourglass-half text-amber-600 mr-1"></i> Menunggu persetujuan Atasan Departemen <strong><?= htmlspecialchars($leave['nama_dept']) ?></strong>.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Approval Action Buttons (If Approver) -->
                    <?php if ($canApprove): ?>
                        <div class="pt-4 border-t border-slate-200 space-y-2">
                            <span class="block text-xs font-bold text-slate-700">Tindakan Persetujuan:</span>
                            <div class="grid grid-cols-2 gap-2.5">
                                <button type="button" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center gap-1.5" onclick="confirmApprove(<?= $leave['id'] ?>, '<?= addslashes(htmlspecialchars($leave['nama_lengkap'])) ?>')">
                                    <i class="fa-solid fa-check"></i> Setujui
                                </button>
                                <button type="button" class="w-full py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center gap-1.5" onclick="confirmReject(<?= $leave['id'] ?>, '<?= addslashes(htmlspecialchars($leave['nama_lengkap'])) ?>')">
                                    <i class="fa-solid fa-xmark"></i> Tolak
                                </button>
                            </div>
                        </div>
                    <?php elseif ($leave['status'] === 'pending' && ($isHRD || $isManager || $isSpv) && $currentUser['id'] != $leave['employee_id']): ?>
                        <div class="pt-4 border-t border-slate-200 space-y-2">
                            <div class="p-3.5 rounded-2xl bg-amber-50/80 border border-amber-200/80 text-xs text-amber-900 space-y-1">
                                <div class="font-extrabold flex items-center gap-1.5 text-amber-800">
                                    <i class="fa-solid fa-circle-info"></i> Mode Monitoring Pengajuan
                                </div>
                                <p class="text-slate-600 text-[11px] leading-relaxed">
                                    Saat ini pengajuan berada pada <strong><?= $step === 'pending_spv' ? 'Tahap 1 (Leader / Supervisor Departemen ' . htmlspecialchars($leave['nama_dept']) . ')' : ($step === 'pending_manager' ? 'Tahap 2 (Plant Manager)' : 'Tahap 3 (HRD / Super Admin)') ?></strong>.
                                    Tombol <strong>Setujui</strong> dan <strong>Tolak</strong> akan aktif setelah tahapan persetujuan mencapai giliran Anda.
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
