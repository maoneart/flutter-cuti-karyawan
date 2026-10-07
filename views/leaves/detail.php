<?php
/**
 * Leave Request Detail View (Tailwind CSS Edition - Synced with Flutter Mobile & Desktop)
 * PT. Nakakin Indonesia Leave Management System
 */

$pageTitle = 'Detail Permohonan Cuti';
require_once __DIR__ . '/../layouts/header.php';

$id = (int)($_GET['id'] ?? 0);
$pdo = getDbConnection();

$stmt = $pdo->prepare("
    SELECT p.*, 
           k.nik, k.nama_lengkap, k.email, k.no_hp, k.alamat as alamat_karyawan, k.tanggal_masuk, k.sisa_cuti, k.kuota_cuti, k.cuti_terpakai, k.foto as employee_foto, k.departemen_id,
           d.nama_dept, d.kode_dept,
           j.nama_jabatan, j.level_hierarki as employee_level,
           lt.nama_cuti, lt.potong_kuota, lt.deskripsi as deskripsi_cuti, lt.kode as kode_cuti,
           spv.nama_lengkap as spv_name, spv.nik as spv_nik, spv_j.nama_jabatan as spv_jabatan,
           mgr.nama_lengkap as manager_name, mgr.nik as manager_nik, mgr_j.nama_jabatan as manager_jabatan,
           hrd.nama_lengkap as hrd_name, hrd.nik as hrd_nik, hrd_j.nama_jabatan as hrd_jabatan,
           appr.nama_lengkap as approver_name, appr.nik as approver_nik, appr_j.nama_jabatan as approver_jabatan
    FROM pengajuan_cuti p
    JOIN karyawan k ON p.employee_id = k.id
    JOIN departemen d ON k.departemen_id = d.id
    JOIN jabatan j ON k.jabatan_id = j.id
    JOIN jenis_cuti lt ON p.leave_type_id = lt.id
    LEFT JOIN karyawan spv ON p.spv_id = spv.id
    LEFT JOIN jabatan spv_j ON spv.jabatan_id = spv_j.id
    LEFT JOIN karyawan mgr ON p.manager_id = mgr.id
    LEFT JOIN jabatan mgr_j ON mgr.jabatan_id = mgr_j.id
    LEFT JOIN karyawan hrd ON p.hrd_id = hrd.id
    LEFT JOIN jabatan hrd_j ON hrd.jabatan_id = hrd_j.id
    LEFT JOIN karyawan appr ON p.approved_by = appr.id
    LEFT JOIN jabatan appr_j ON appr.jabatan_id = appr_j.id
    WHERE p.id = ?
    LIMIT 1
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

$step = strtolower($leave['approval_step'] ?? 'pending_spv');
$status = strtolower($leave['status'] ?? 'pending');

// Strict Multi-Tier Approval eligibility check
$canApprove = false;
if ($status === 'pending' && (int)$currentUser['id'] !== (int)$leave['employee_id']) {
    if ($step === 'pending_spv') {
        $canApprove = $isSpv && ((int)$currentUser['departemen_id'] === (int)$leave['departemen_id']);
    } elseif ($step === 'pending_manager') {
        $canApprove = $isManager;
    } elseif ($step === 'pending_hrd') {
        $canApprove = $isHRD;
    }
}

// Multi-Tier Status Flags
$employeeLevel = (int)($leave['employee_level'] ?? 1);
$isRejected = ($status === 'rejected');
$isApproved = ($status === 'approved');

$spvDone = !empty($leave['spv_id']) || ($employeeLevel >= 3) || $isApproved;
$spvCurrent = ($step === 'pending_spv' && $status === 'pending');
$spvRejected = ($isRejected && $step === 'pending_spv');

$mgrDone = !empty($leave['manager_id']) || ($employeeLevel >= 5) || $isApproved;
$mgrCurrent = ($step === 'pending_manager' && $status === 'pending');
$mgrRejected = ($isRejected && $step === 'pending_manager');

$hrdDone = $isApproved;
$hrdCurrent = ($step === 'pending_hrd' && $status === 'pending');
$hrdRejected = ($isRejected && $step === 'pending_hrd');

$waDetail = getLeaveWhatsAppNotificationData($leave['id'], $pdo);

$tglMulai = date('d M Y', strtotime($leave['tanggal_mulai']));
$tglSelesai = date('d M Y', strtotime($leave['tanggal_selesai']));
$periode = ($leave['tanggal_mulai'] === $leave['tanggal_selesai']) ? $tglMulai : "$tglMulai s/d $tglSelesai";
$durasi = ($leave['total_hari'] == 0.5) ? '0.5 Hari' : $leave['total_hari'] . ' Hari';
$initial = strtoupper(substr($leave['nama_lengkap'] ?? 'K', 0, 1));
?>

<div class="w-full space-y-4 sm:space-y-6 max-w-4xl mx-auto pb-20">

    <!-- Top Navigation Action Bar -->
    <div class="flex items-center justify-between flex-wrap gap-2.5">
        <a href="javascript:history.back()" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 border border-slate-200 text-xs font-bold text-slate-700 shadow-2xs transition flex items-center gap-1.5">
            <span class="material-symbols-rounded text-sm">arrow_back</span>
            <span>Kembali</span>
        </a>
        
        <div class="flex items-center gap-2">
            <?php if ($waDetail && $status === 'pending'): ?>
                <a href="<?= htmlspecialchars($waDetail['wa_url']) ?>" target="_blank" 
                   class="px-3.5 py-2 rounded-xl bg-[#25D366] hover:bg-[#20bd5a] text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                    <i class="fa-brands fa-whatsapp text-sm"></i> Kirim WA ke Atasan
                </a>
            <?php endif; ?>

            <?php if ($isApproved): ?>
                <a href="<?= BASE_URL ?>/index.php?page=leave-print&id=<?= $leave['id'] ?>" target="_blank" 
                   class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                    <span class="material-symbols-rounded text-sm">print</span>
                    <span>Cetak Surat Izin</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- 1. Status Indicator Card (Flutter Style) -->
    <?php if ($isApproved): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200/90 flex items-center gap-3.5 text-emerald-900 shadow-2xs">
            <span class="material-symbols-rounded text-2xl text-emerald-600 flex-shrink-0">check_circle</span>
            <div>
                <h3 class="text-sm font-bold text-emerald-900">Permohonan Cuti Telah Disetujui</h3>
                <p class="text-xs text-emerald-700 mt-0.5">Pengajuan telah diverifikasi penuh hingga tahap akhir HRD dan kuota cuti resmi terpotong.</p>
            </div>
        </div>
    <?php elseif ($isRejected): ?>
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200/90 flex items-center gap-3.5 text-rose-900 shadow-2xs">
            <span class="material-symbols-rounded text-2xl text-rose-600 flex-shrink-0">cancel</span>
            <div>
                <h3 class="text-sm font-bold text-rose-900">Permohonan Cuti Ditolak</h3>
                <p class="text-xs text-rose-700 mt-0.5">Alasan: <?= htmlspecialchars($leave['rejection_reason'] ?: ($leave['catatan_atasan'] ?: 'Permohonan tidak disetujui.')) ?></p>
            </div>
        </div>
    <?php else: ?>
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/90 flex items-center gap-3.5 text-amber-900 shadow-2xs">
            <span class="material-symbols-rounded text-2xl text-amber-600 flex-shrink-0">hourglass_top</span>
            <div>
                <h3 class="text-sm font-bold text-amber-900">
                    <?= $step === 'pending_spv' ? 'Menunggu Review Supervisor / Leader' : ($step === 'pending_manager' ? 'Menunggu Review Plant Manager' : 'Menunggu Verifikasi Akhir HRD') ?>
                </h3>
                <p class="text-xs text-amber-700 mt-0.5">Pengajuan sedang dalam proses peninjauan persetujuan bertingkat.</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- 2. Employee Profile Card (Flutter Style) -->
    <div class="p-4 sm:p-5 bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 shadow-2xs flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-12 h-12 rounded-full bg-blue-600 text-white font-bold text-lg flex items-center justify-center flex-shrink-0 shadow-sm">
                <?= $initial ?>
            </div>
            <div class="min-w-0">
                <h3 class="text-sm sm:text-base font-bold text-slate-900 truncate leading-snug"><?= htmlspecialchars($leave['nama_lengkap']) ?></h3>
                <p class="text-xs text-slate-500 truncate mt-0.5"><?= htmlspecialchars($leave['nik']) ?> &bull; <?= htmlspecialchars($leave['nama_jabatan']) ?></p>
            </div>
        </div>
        <div class="text-right flex-shrink-0">
            <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200">
                <?= htmlspecialchars($leave['nama_dept']) ?>
            </span>
            <div class="text-[11px] text-slate-400 mt-1 font-medium">Masa Kerja: <?= hitungMasaKerja($leave['tanggal_masuk']) ?></div>
        </div>
    </div>

    <!-- 3. Leave Details Bento Grid (Flutter Style) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        
        <!-- Rincian Cuti Card -->
        <div class="p-5 bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 shadow-2xs space-y-3.5">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                <span class="material-symbols-rounded text-blue-600 text-base">description</span>
                <span>Rincian Pengajuan Cuti</span>
            </h4>

            <div class="space-y-2.5 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-400 font-medium">Nomor Surat:</span>
                    <strong class="font-mono text-blue-600 font-bold"><?= htmlspecialchars($leave['nomor_surat']) ?></strong>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-400 font-medium">Jenis Permohonan:</span>
                    <strong class="text-slate-900 font-bold"><?= htmlspecialchars($leave['nama_cuti']) ?></strong>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-400 font-medium">Periode Cuti:</span>
                    <strong class="text-slate-800"><?= $periode ?></strong>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-400 font-medium">Total Durasi:</span>
                    <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-bold"><?= $durasi ?></span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-400 font-medium">Pengaruh Kuota:</span>
                    <strong class="<?= !empty($leave['potong_kuota']) ? 'text-rose-600' : 'text-emerald-600' ?>">
                        <?= !empty($leave['potong_kuota']) ? 'Memotong Kuota Tahunan' : 'Bebas Kuota (Izin Khusus)' ?>
                    </strong>
                </div>
            </div>

            <!-- Alasan Cuti -->
            <div class="pt-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase">Alasan / Keterangan:</span>
                <div class="p-3 mt-1 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-700 font-medium leading-relaxed">
                    <?= nl2br(htmlspecialchars($leave['alasan'])) ?>
                </div>
            </div>

            <!-- Lampiran File -->
            <?php if (!empty($leave['attachment'])): ?>
                <div class="pt-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase">Dokumen Lampiran:</span>
                    <div class="mt-1">
                        <a href="<?= BASE_URL ?>/assets/uploads/leaves/<?= htmlspecialchars($leave['attachment']) ?>" target="_blank" 
                           class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition">
                            <span class="material-symbols-rounded text-sm">attach_file</span>
                            <span>Buka / Unduh Lampiran Medis</span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Multi-Tier Timeline Card (Flutter Exact Stepper) -->
        <div class="p-5 bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 shadow-2xs space-y-4">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                <span class="material-symbols-rounded text-indigo-600 text-base">account_tree</span>
                <span>Alur Persetujuan Bertingkat</span>
            </h4>

            <div class="space-y-4 relative pl-1">
                
                <!-- Tier 1: Leader / Spv -->
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm flex-shrink-0 <?= $spvDone ? 'bg-emerald-100 text-emerald-600' : ($spvCurrent ? 'bg-amber-100 text-amber-600' : ($spvRejected ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-400')) ?>">
                        <span class="material-symbols-rounded text-base"><?= $spvDone ? 'check' : ($spvCurrent ? 'hourglass_top' : ($spvRejected ? 'close' : 'radio_button_unchecked')) ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold text-slate-900 leading-tight">1. Leader / Supervisor Departemen</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">
                            <?php if ($employeeLevel >= 3): ?>
                                <span class="text-slate-400 italic">Bypass otomatis (Pemohon level Leader/Spv)</span>
                            <?php elseif ($spvDone): ?>
                                <span class="text-emerald-600 font-medium">Disetujui oleh <?= htmlspecialchars($leave['spv_name'] ?? 'Leader/Spv') ?> <?= !empty($leave['spv_at']) ? '• ' . date('d/m/y H:i', strtotime($leave['spv_at'])) : '' ?></span>
                                <?php if (!empty($leave['spv_notes'])): ?>
                                    <div class="text-[10.5px] text-slate-600 bg-slate-50 p-1.5 rounded-lg mt-1">"<?= htmlspecialchars($leave['spv_notes']) ?>"</div>
                                <?php endif; ?>
                            <?php elseif ($spvCurrent): ?>
                                <span class="text-amber-600 font-medium">Sedang menunggu persetujuan Leader/Spv</span>
                            <?php elseif ($spvRejected): ?>
                                <span class="text-rose-600 font-medium">Ditolak di tahap ini</span>
                            <?php else: ?>
                                <span class="text-slate-400">Antrean</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tier 2: Plant Manager -->
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm flex-shrink-0 <?= $mgrDone ? 'bg-emerald-100 text-emerald-600' : ($mgrCurrent ? 'bg-blue-100 text-blue-600' : ($mgrRejected ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-400')) ?>">
                        <span class="material-symbols-rounded text-base"><?= $mgrDone ? 'check' : ($mgrCurrent ? 'hourglass_top' : ($mgrRejected ? 'close' : 'radio_button_unchecked')) ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold text-slate-900 leading-tight">2. Department / Plant Manager</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">
                            <?php if ($employeeLevel >= 5): ?>
                                <span class="text-slate-400 italic">Bypass otomatis (Pemohon level Manager)</span>
                            <?php elseif ($mgrDone): ?>
                                <span class="text-emerald-600 font-medium">Disetujui oleh <?= htmlspecialchars($leave['manager_name'] ?? 'Plant Manager') ?> <?= !empty($leave['manager_at']) ? '• ' . date('d/m/y H:i', strtotime($leave['manager_at'])) : '' ?></span>
                                <?php if (!empty($leave['manager_notes'])): ?>
                                    <div class="text-[10.5px] text-slate-600 bg-slate-50 p-1.5 rounded-lg mt-1">"<?= htmlspecialchars($leave['manager_notes']) ?>"</div>
                                <?php endif; ?>
                            <?php elseif ($mgrCurrent): ?>
                                <span class="text-blue-600 font-medium">Sedang menunggu review Plant Manager</span>
                            <?php elseif ($mgrRejected): ?>
                                <span class="text-rose-600 font-medium">Ditolak oleh Plant Manager</span>
                            <?php else: ?>
                                <span class="text-slate-400">Menunggu tahap 1</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tier 3: HRD / Super Admin -->
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm flex-shrink-0 <?= $hrdDone ? 'bg-emerald-100 text-emerald-600' : ($hrdCurrent ? 'bg-purple-100 text-purple-600' : ($hrdRejected ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-400')) ?>">
                        <span class="material-symbols-rounded text-base"><?= $hrdDone ? 'check' : ($hrdCurrent ? 'hourglass_top' : ($hrdRejected ? 'close' : 'radio_button_unchecked')) ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold text-slate-900 leading-tight">3. HRD & Super Admin (Final)</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">
                            <?php if ($hrdDone): ?>
                                <span class="text-emerald-600 font-medium">Disetujui final oleh <?= htmlspecialchars($leave['hrd_name'] ?? ($leave['approver_name'] ?? 'HRD Admin')) ?> <?= !empty($leave['approved_at']) ? '• ' . date('d/m/y H:i', strtotime($leave['approved_at'])) : '' ?></span>
                                <?php if (!empty($leave['catatan_atasan'])): ?>
                                    <div class="text-[10.5px] text-slate-600 bg-slate-50 p-1.5 rounded-lg mt-1">"<?= htmlspecialchars($leave['catatan_atasan']) ?>"</div>
                                <?php endif; ?>
                            <?php elseif ($hrdCurrent): ?>
                                <span class="text-purple-600 font-medium">Sedang menunggu verifikasi akhir HRD</span>
                            <?php elseif ($hrdRejected): ?>
                                <span class="text-rose-600 font-medium">Ditolak oleh HRD</span>
                            <?php else: ?>
                                <span class="text-slate-400">Menunggu tahap 2</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Approval Action Box for Approvers -->
            <?php if ($canApprove): ?>
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2">
                    <span class="text-xs font-bold text-slate-900 dark:text-white block">Tindakan Giliran Anda:</span>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" class="py-2.5 rounded-xl border border-rose-400 text-rose-600 hover:bg-rose-50 font-bold text-xs flex items-center justify-center gap-1.5 transition" onclick="openRejectModal(<?= $leave['id'] ?>, '<?= addslashes(htmlspecialchars($leave['nama_lengkap'])) ?>')">
                            <span class="material-symbols-rounded text-sm">close</span>
                            <span>Tolak</span>
                        </button>
                        <button type="button" class="py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm flex items-center justify-center gap-1.5 transition" onclick="openApproveModal(<?= $leave['id'] ?>, '<?= addslashes(htmlspecialchars($leave['nama_lengkap'])) ?>')">
                            <span class="material-symbols-rounded text-sm">check</span>
                            <span>Setujui</span>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <?php 
            $isOwner = ($currentUser['id'] == $leave['employee_id']);
            $canCancel = ($status === 'pending') && ($isOwner || $isHRD);
            ?>
            <?php if ($canCancel): ?>
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" 
                            class="w-full py-2.5 rounded-xl border border-rose-300 dark:border-rose-900/60 bg-rose-50/60 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/60 font-bold text-xs flex items-center justify-center gap-1.5 transition shadow-2xs" 
                            onclick="confirmDelete('<?= BASE_URL ?>/index.php?page=leave-action&action=cancel&id=<?= $leave['id'] ?>', 'Batalkan permohonan cuti ini?')">
                        <span class="material-symbols-rounded text-base">block</span>
                        <span>Batalkan Pengajuan Cuti</span>
                    </button>
                </div>
            <?php endif; ?>

        </div>

    </div>

</div>

<!-- Interactive Approval & Reject Modal Dialogs -->
<script>
function openApproveModal(id, name) {
    Swal.fire({
        title: '<div class="flex items-center justify-center gap-2 text-emerald-600 dark:text-emerald-400"><span class="material-symbols-rounded text-2xl">check_circle</span><span class="text-base font-bold text-slate-900 dark:text-white">Setujui Pengajuan</span></div>',
        html: `
            <div class="text-left text-xs space-y-3 mt-2">
                <p class="text-slate-600 dark:text-slate-300">Apakah Anda yakin ingin menyetujui pengajuan cuti untuk <strong>${name}</strong>?</p>
                <div class="space-y-1 pt-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300 text-[11px] uppercase tracking-wider">Catatan Tambahan (Opsional)</label>
                    <textarea id="swalApproveNote" rows="3" class="w-full p-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="Misal: Disetujui, pekerjaan didelegasikan..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fa-solid fa-check mr-1.5"></i> Ya, Setujui',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const noteVal = document.getElementById('swalApproveNote')?.value || '';
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= BASE_URL ?>/index.php?page=leave-action&action=approve&id=' + id;
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'note';
            input.value = noteVal;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function openRejectModal(id, name) {
    Swal.fire({
        title: '<div class="flex items-center justify-center gap-2 text-rose-600 dark:text-rose-400"><span class="material-symbols-rounded text-2xl">highlight_off</span><span class="text-base font-bold text-slate-900 dark:text-white">Tolak Pengajuan</span></div>',
        html: `
            <div class="text-left text-xs space-y-3 mt-2">
                <p class="text-slate-600 dark:text-slate-300">Masukkan alasan penolakan pengajuan untuk <strong>${name}</strong>:</p>
                <div class="space-y-1 pt-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300 text-[11px] uppercase tracking-wider">Alasan Penolakan (Wajib) <span class="text-rose-500">*</span></label>
                    <textarea id="swalRejectNote" rows="3" class="w-full p-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500" placeholder="Tuliskan alasan penolakan..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fa-solid fa-xmark mr-1.5"></i> Tolak Cuti',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        preConfirm: () => {
            const noteVal = document.getElementById('swalRejectNote')?.value.trim();
            if (!noteVal) {
                Swal.showValidationMessage('Alasan penolakan wajib diisi!');
                return false;
            }
            return noteVal;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= BASE_URL ?>/index.php?page=leave-action&action=reject&id=' + id;
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'reason';
            input.value = result.value;
            
            const inputNotes = document.createElement('input');
            inputNotes.type = 'hidden';
            inputNotes.name = 'catatan_atasan';
            inputNotes.value = result.value;

            form.appendChild(input);
            form.appendChild(inputNotes);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
