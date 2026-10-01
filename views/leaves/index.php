<?php
/**
 * My Leaves & Team Leaves History View (Synced with Flutter Mobile & Desktop)
 * PT. Nakakin Indonesia Leave Management System
 */

$pageTitle = 'Riwayat Cuti';
require_once __DIR__ . '/../layouts/header.php';

$pdo = getDbConnection();
$userId = (int)$currentUser['id'];
$hierarki = (int)($currentUser['level_hierarki'] ?? 1);
$canApprove = in_array($currentUser['role'] ?? '', ['leader', 'supervisor', 'manager', 'hrd', 'admin', 'superadmin']) || $hierarki >= 3;
$isManagerOrAdmin = in_array($currentUser['role'] ?? '', ['manager', 'hrd', 'admin', 'superadmin']) || $hierarki >= 5;
$teamLabel = $isManagerOrAdmin ? 'Semua Karyawan' : 'Tim Departemen';

$selectedScope = $_GET['scope'] ?? 'my';
if (!$canApprove) {
    $selectedScope = 'my';
}

// 1. Fetch User's Own Leaves
$stmtMy = $pdo->prepare("
    SELECT lr.*, lt.nama_cuti, lt.potong_kuota, lt.kode as kode_cuti,
           k.nama_lengkap, k.nik, k.foto as employee_foto,
           d.nama_dept, d.kode_dept,
           jb.nama_jabatan,
           ap.nama_lengkap as nama_atasan, ap.nik as nik_atasan
    FROM pengajuan_cuti lr
    JOIN karyawan k ON lr.employee_id = k.id
    JOIN departemen d ON k.departemen_id = d.id
    JOIN jabatan jb ON k.jabatan_id = jb.id
    JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
    LEFT JOIN karyawan ap ON lr.approved_by = ap.id
    WHERE lr.employee_id = ?
    ORDER BY lr.created_at DESC
");
$stmtMy->execute([$userId]);
$myLeaves = $stmtMy->fetchAll();

// 2. Fetch Team/Company Leaves (if user can approve)
$teamLeaves = [];
if ($canApprove) {
    if ($isManagerOrAdmin) {
        $stmtTeam = $pdo->prepare("
            SELECT lr.*, lt.nama_cuti, lt.potong_kuota, lt.kode as kode_cuti,
                   k.nama_lengkap, k.nik, k.foto as employee_foto,
                   d.nama_dept, d.kode_dept,
                   jb.nama_jabatan,
                   ap.nama_lengkap as nama_atasan, ap.nik as nik_atasan
            FROM pengajuan_cuti lr
            JOIN karyawan k ON lr.employee_id = k.id
            JOIN departemen d ON k.departemen_id = d.id
            JOIN jabatan jb ON k.jabatan_id = jb.id
            JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
            LEFT JOIN karyawan ap ON lr.approved_by = ap.id
            ORDER BY lr.created_at DESC
        ");
        $stmtTeam->execute();
    } else {
        $stmtTeam = $pdo->prepare("
            SELECT lr.*, lt.nama_cuti, lt.potong_kuota, lt.kode as kode_cuti,
                   k.nama_lengkap, k.nik, k.foto as employee_foto,
                   d.nama_dept, d.kode_dept,
                   jb.nama_jabatan,
                   ap.nama_lengkap as nama_atasan, ap.nik as nik_atasan
            FROM pengajuan_cuti lr
            JOIN karyawan k ON lr.employee_id = k.id
            JOIN departemen d ON k.departemen_id = d.id
            JOIN jabatan jb ON k.jabatan_id = jb.id
            JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
            LEFT JOIN karyawan ap ON lr.approved_by = ap.id
            WHERE k.departemen_id = ?
            ORDER BY lr.created_at DESC
        ");
        $stmtTeam->execute([(int)$currentUser['departemen_id']]);
    }
    $teamLeaves = $stmtTeam->fetchAll();
}

$leaves = ($selectedScope === 'all' && $canApprove) ? $teamLeaves : $myLeaves;
?>

<div class="w-full space-y-4 sm:space-y-6">

    <!-- ========================================================================= -->
    <!-- 1. MOBILE VIEW (Screen < 1024px) - EXACT FLUTTER LEAVE HISTORY SCREEN    -->
    <!-- ========================================================================= -->
    <div class="block lg:hidden space-y-3 max-w-lg mx-auto pb-20">
        
        <!-- Flutter-style TabBar (Top Status Filter Tabs) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            
            <!-- Scope Switcher Segmented Control (Only for Approvers/Leader/Spv/Manager/HRD/Superadmin) -->
            <?php if ($canApprove): ?>
                <div class="p-3 pb-2 border-b border-slate-100">
                    <div class="flex items-center p-1 bg-slate-100/90 rounded-xl">
                        <button type="button" onclick="switchScope('my')" id="scopeBtnMy"
                                class="flex-1 py-1.5 text-xs font-bold rounded-lg transition text-center <?= $selectedScope === 'my' ? 'bg-white text-blue-600 shadow-2xs' : 'text-slate-500 hover:text-slate-800' ?>">
                            Cuti Saya
                        </button>
                        <button type="button" onclick="switchScope('all')" id="scopeBtnAll"
                                class="flex-1 py-1.5 text-xs font-bold rounded-lg transition flex items-center justify-center gap-1.5 <?= $selectedScope === 'all' ? 'bg-white text-blue-600 shadow-2xs' : 'text-slate-500 hover:text-slate-800' ?>">
                            <span class="material-symbols-rounded text-sm <?= $selectedScope === 'all' ? 'text-blue-600' : 'text-slate-400' ?>">visibility</span>
                            <span><?= $teamLabel ?></span>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Material TabBar: Semua, Menunggu, Disetujui, Ditolak, Dibatalkan -->
            <div class="flex items-center justify-between border-b border-slate-200/80 px-2 overflow-x-auto no-scrollbar" id="flutterTabBar">
                <button type="button" onclick="selectTab('all')" data-tab="all" 
                        class="tab-btn px-3 py-2.5 text-xs font-bold transition flex items-center gap-1 border-b-2 border-blue-600 text-blue-600 flex-shrink-0">
                    <span>Semua</span>
                </button>
                <button type="button" onclick="selectTab('pending')" data-tab="pending" 
                        class="tab-btn px-3 py-2.5 text-xs font-bold transition flex items-center gap-1 border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex-shrink-0">
                    <span>Menunggu</span>
                </button>
                <button type="button" onclick="selectTab('approved')" data-tab="approved" 
                        class="tab-btn px-3 py-2.5 text-xs font-bold transition flex items-center gap-1 border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex-shrink-0">
                    <span>Disetujui</span>
                </button>
                <button type="button" onclick="selectTab('rejected')" data-tab="rejected" 
                        class="tab-btn px-3 py-2.5 text-xs font-bold transition flex items-center gap-1 border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex-shrink-0">
                    <span>Ditolak</span>
                </button>
                <button type="button" onclick="selectTab('cancelled')" data-tab="cancelled" 
                        class="tab-btn px-3 py-2.5 text-xs font-bold transition flex items-center gap-1 border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex-shrink-0">
                    <span>Dibatalkan</span>
                </button>
            </div>

            <!-- Search Box (Exact Flutter TextField with Icon & Clear button) -->
            <div class="p-3">
                <div class="relative flex items-center">
                    <span class="material-symbols-rounded absolute left-3 text-slate-400 text-lg pointer-events-none">search</span>
                    <input type="text" id="leaveSearchInput" 
                           placeholder="<?= $selectedScope === 'my' ? 'Cari nomor surat atau alasan cuti...' : 'Cari nama karyawan, departemen, alasan...' ?>" 
                           class="w-full pl-9 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    <button type="button" id="clearSearchBtn" onclick="clearSearch()" 
                            class="absolute right-2.5 w-5 h-5 rounded-full bg-slate-200/80 hover:bg-slate-300 text-slate-500 flex items-center justify-center text-xs hidden">
                        <span class="material-symbols-rounded text-xs">close</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Card Container (Rendered dynamically based on active scope & filter) -->
        <div id="leavesContainer" class="space-y-3">
            <!-- Populated via PHP / JS below -->
            <?php 
            function renderMobileLeaveCard($leave, $isOtherEmployee = false) {
                $status = strtolower($leave['status'] ?? 'pending');
                $step = strtolower($leave['approval_step'] ?? 'pending_spv');
                $tglMulai = $leave['tanggal_mulai'];
                $tglSelesai = $leave['tanggal_selesai'];
                $periode = "$tglMulai s/d $tglSelesai";
                $durasiNum = (float)$leave['total_hari'];
                $durasi = number_format($durasiNum, 1, '.', '') . ' Hari';

                // Status Badge styling identical to Flutter AppTheme
                if ($status === 'approved') {
                    $badgeBg = 'bg-[#DCFCE7] text-[#15803D]';
                    $badgeLabel = 'Disetujui';
                } elseif ($status === 'rejected') {
                    $badgeBg = 'bg-[#FEE2E2] text-[#B91C1C]';
                    $badgeLabel = 'Ditolak';
                } elseif ($status === 'cancelled') {
                    $badgeBg = 'bg-[#F1F5F9] text-[#475569]';
                    $badgeLabel = 'Dibatalkan';
                } else {
                    $badgeBg = 'bg-[#FEF3C7] text-[#B45309]';
                    if ($step === 'pending_spv') $badgeLabel = 'Menunggu Spv';
                    elseif ($step === 'pending_manager') $badgeLabel = 'Menunggu Manager';
                    elseif ($step === 'pending_hrd') $badgeLabel = 'Menunggu HRD';
                    else $badgeLabel = 'Menunggu';
                }

                $searchCorpus = strtolower(
                    ($leave['nomor_surat'] ?? '') . ' ' . 
                    ($leave['nama_cuti'] ?? '') . ' ' . 
                    ($leave['alasan'] ?? '') . ' ' . 
                    ($leave['nama_lengkap'] ?? '') . ' ' . 
                    ($leave['nama_dept'] ?? '') . ' ' . 
                    ($leave['nama_jabatan'] ?? '')
                );
                ?>
                <div class="leave-card bg-white rounded-2xl border border-slate-200/90 p-4 space-y-2.5 shadow-2xs hover:shadow-xs transition cursor-pointer"
                     data-status="<?= $status ?>"
                     data-search="<?= htmlspecialchars($searchCorpus) ?>"
                     onclick="window.location.href='<?= BASE_URL ?>/index.php?page=leave-detail&id=<?= $leave['id'] ?>'">
                    
                    <!-- Header: ID/Nomor Surat & Status Badge -->
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-bold text-blue-600 font-mono tracking-tight">
                            <?= htmlspecialchars($leave['nomor_surat']) ?>
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?= $badgeBg ?>">
                            <?= $badgeLabel ?>
                        </span>
                    </div>

                    <!-- Employee Profile (if viewing team / company scope) -->
                    <?php if ($isOtherEmployee && !empty($leave['nama_lengkap'])): ?>
                        <div class="flex items-center gap-2.5 pt-0.5">
                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs border border-blue-100 flex-shrink-0">
                                <span class="material-symbols-rounded text-sm">person</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-bold text-slate-900 leading-tight truncate">
                                    <?= htmlspecialchars($leave['nama_lengkap']) ?>
                                </h4>
                                <p class="text-[11px] text-slate-400 truncate mt-0.5 font-medium">
                                    <?= htmlspecialchars($leave['nama_dept'] ?? '-') ?> &bull; <?= htmlspecialchars($leave['nama_jabatan'] ?? '-') ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Leave Type Name (15px bold in Flutter) -->
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 leading-tight">
                            <?= htmlspecialchars($leave['nama_cuti'] ?? 'Cuti') ?>
                        </h3>
                    </div>

                    <!-- Date Range with Calendar Icon & Total Hari pill -->
                    <div class="flex items-center gap-1.5 text-xs text-slate-600">
                        <span class="material-symbols-rounded text-slate-400 text-sm">date_range</span>
                        <span><?= $periode ?></span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 font-bold text-[11px] ml-1">
                            <?= $durasi ?>
                        </span>
                    </div>

                    <!-- Reason (Alasan Cuti) -->
                    <div class="text-xs text-slate-600 line-clamp-2">
                        <?= htmlspecialchars($leave['alasan']) ?>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-slate-100 pt-2 flex items-center justify-between">
                        <span class="text-[11.5px] text-slate-400">
                            Ketuk untuk lihat detail
                        </span>
                        
                        <!-- Cetak Surat Button (Always available for all leave cards matching Flutter) -->
                        <a href="<?= BASE_URL ?>/index.php?page=leave-print&id=<?= $leave['id'] ?>" target="_blank" 
                           onclick="event.stopPropagation()"
                           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[#0284C7] hover:text-[#0369a1] hover:bg-sky-50 font-bold text-xs transition">
                            <span class="material-symbols-rounded text-sm">print</span>
                            <span>Cetak Surat</span>
                        </a>
                    </div>

                </div>
            <?php } ?>

            <!-- My Leaves List -->
            <div id="myLeavesContainer" class="<?= $selectedScope === 'my' ? 'space-y-3' : 'hidden' ?>">
                <?php if (empty($myLeaves)): ?>
                    <div class="empty-state p-8 rounded-2xl bg-white border border-slate-200 text-center shadow-2xs">
                        <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3">
                            <span class="material-symbols-rounded text-2xl">description</span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">Tidak Ada Data Pengajuan Cuti</h4>
                        <p class="text-xs text-slate-500 mt-1">Belum ada pengajuan pada kategori ini.</p>
                        <a href="<?= BASE_URL ?>/index.php?page=leave-create" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold shadow-sm hover:bg-blue-700 transition">
                            <span class="material-symbols-rounded text-sm">add_circle</span>
                            <span>Ajukan Cuti Baru</span>
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($myLeaves as $leave) { renderMobileLeaveCard($leave, false); } ?>
                <?php endif; ?>
            </div>

            <!-- Team / All Leaves List (for Approvers) -->
            <?php if ($canApprove): ?>
                <div id="teamLeavesContainer" class="<?= $selectedScope === 'all' ? 'space-y-3' : 'hidden' ?>">
                    <?php if (empty($teamLeaves)): ?>
                        <div class="empty-state p-8 rounded-2xl bg-white border border-slate-200 text-center shadow-2xs">
                            <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3">
                                <span class="material-symbols-rounded text-2xl">description</span>
                            </div>
                            <h4 class="text-sm font-bold text-slate-900">Tidak Ada Data Pengajuan Cuti</h4>
                            <p class="text-xs text-slate-500 mt-1">Belum ada pengajuan cuti dari tim atau anggota pada kategori ini.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($teamLeaves as $leave) { renderMobileLeaveCard($leave, true); } ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Global Empty Search Result (Shown when all cards are hidden by filter/search) -->
            <div id="noMatchState" class="hidden p-8 rounded-2xl bg-white border border-slate-200 text-center shadow-2xs">
                <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-rounded text-2xl">description</span>
                </div>
                <h4 class="text-sm font-bold text-slate-900">Tidak Ada Data Pengajuan Cuti</h4>
                <p class="text-xs text-slate-500 mt-1">Belum ada pengajuan pada kategori ini.</p>
            </div>

        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 2. DESKTOP VIEW (Screen >= 1024px) - FULL DATATABLES TABLE                -->
    <!-- ========================================================================= -->
    <div class="hidden lg:block bg-white rounded-3xl border border-slate-200/80 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.04)] overflow-hidden">
        <div class="p-5 sm:px-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/40">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold border border-blue-200/60 shadow-2xs">
                    <span class="material-symbols-rounded text-lg">history</span>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">
                        <?= ($selectedScope === 'all' && $canApprove) ? "Monitoring Cuti - $teamLabel" : "Riwayat Cuti Saya" ?>
                    </h3>
                    <p class="text-[11px] text-slate-400 font-medium">
                        <?= ($selectedScope === 'all' && $canApprove) ? "Seluruh pengajuan cuti bawahan & tim yang tercatat" : "Seluruh riwayat izin cuti Anda beserta status persetujuan terbarunya" ?>
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-2.5">
                <?php if ($canApprove): ?>
                    <div class="flex items-center bg-slate-100 p-1 rounded-xl">
                        <a href="<?= BASE_URL ?>/index.php?page=leaves-my&scope=my" 
                           class="px-3 py-1.5 text-xs font-bold rounded-lg transition <?= $selectedScope === 'my' ? 'bg-white text-blue-600 shadow-2xs' : 'text-slate-500 hover:text-slate-800' ?>">
                            Cuti Saya (<?= count($myLeaves) ?>)
                        </a>
                        <a href="<?= BASE_URL ?>/index.php?page=leaves-my&scope=all" 
                           class="px-3 py-1.5 text-xs font-bold rounded-lg transition flex items-center gap-1 <?= $selectedScope === 'all' ? 'bg-white text-blue-600 shadow-2xs' : 'text-slate-500 hover:text-slate-800' ?>">
                            <span class="material-symbols-rounded text-xs">visibility</span>
                            <span><?= $teamLabel ?> (<?= count($teamLeaves) ?>)</span>
                        </a>
                    </div>
                <?php else: ?>
                    <span class="px-3.5 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-black border border-slate-200/60 shadow-2xs">
                        Total: <?= count($leaves) ?> Data
                    </span>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>/index.php?page=leave-create" 
                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-extrabold text-xs shadow-md shadow-rose-600/30 transition transform active:scale-95">
                    <span class="material-symbols-rounded text-sm">add_circle</span>
                    <span>Ajukan Cuti Baru</span>
                </a>
            </div>
        </div>

        <div class="p-5">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs datatable">
                    <thead>
                        <tr>
                            <th class="text-center w-8">No</th>
                            <?php if ($selectedScope === 'all' && $canApprove): ?>
                                <th>Karyawan</th>
                            <?php endif; ?>
                            <th>No. Surat & Jenis Cuti</th>
                            <th>Periode Cuti</th>
                            <th class="text-center">Durasi</th>
                            <th class="max-w-[200px]">Alasan</th>
                            <th class="text-center">Status Tahapan</th>
                            <th class="text-right pr-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($leaves as $leave): 
                            $tglMulaiFormatted = date('d M Y', strtotime($leave['tanggal_mulai']));
                            $tglSelesaiFormatted = date('d M Y', strtotime($leave['tanggal_selesai']));
                            $periodeText = ($leave['tanggal_mulai'] === $leave['tanggal_selesai']) ? $tglMulaiFormatted : "$tglMulaiFormatted - $tglSelesaiFormatted";
                            $durasiText = ($leave['total_hari'] == 0.5) ? '0.5 Hari' : $leave['total_hari'] . ' Hari';
                        ?>
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60 transition">
                                <td class="text-center py-2.5">
                                    <span class="w-5 h-5 rounded-md bg-slate-100 dark:bg-slate-800 inline-flex items-center justify-center text-[10.5px] text-slate-500 dark:text-slate-400 font-bold">
                                        <?= $no++ ?>
                                    </span>
                                </td>

                                <?php if ($selectedScope === 'all' && $canApprove): ?>
                                    <td class="py-2.5">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs border border-blue-100 dark:border-blue-900">
                                                <span class="material-symbols-rounded text-xs">person</span>
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($leave['nama_lengkap']) ?></div>
                                                <div class="text-[10px] text-slate-400 dark:text-slate-500"><?= htmlspecialchars($leave['nama_dept']) ?> &bull; <?= htmlspecialchars($leave['nama_jabatan']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                <?php endif; ?>

                                <td class="py-2.5">
                                    <div class="space-y-0.5">
                                        <a href="<?= BASE_URL ?>/index.php?page=leave-detail&id=<?= $leave['id'] ?>" class="font-mono font-bold text-blue-600 dark:text-blue-400 hover:text-blue-800 text-xs inline-block">
                                            <?= htmlspecialchars($leave['nomor_surat']) ?>
                                        </a>
                                        <div class="text-[11px] font-extrabold text-slate-800 dark:text-slate-200"><?= htmlspecialchars($leave['nama_cuti']) ?></div>
                                    </div>
                                </td>
                                <td class="py-2.5 whitespace-nowrap">
                                    <div class="space-y-0.5">
                                        <div class="font-semibold text-slate-800 dark:text-slate-200 text-xs"><?= $periodeText ?></div>
                                        <?php if (!empty($leave['shift'])): ?>
                                            <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium"><?= htmlspecialchars($leave['shift']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center py-2.5 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-md bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 font-black text-[11px] border border-blue-200/60 dark:border-blue-900">
                                        <?= $durasiText ?>
                                    </span>
                                </td>
                                <td class="max-w-[200px] py-2.5">
                                    <div class="truncate text-slate-600 dark:text-slate-300 font-medium text-xs" title="<?= htmlspecialchars($leave['alasan']) ?>">
                                        <?= htmlspecialchars($leave['alasan']) ?>
                                    </div>
                                </td>
                                <td class="text-center py-2.5 whitespace-nowrap">
                                    <div><?= getStatusBadge($leave['status']) ?></div>
                                    <?php if (!empty($leave['approval_step']) && $leave['status'] === 'pending'): ?>
                                        <div class="text-[10px] text-amber-600 dark:text-amber-400 font-bold mt-0.5">
                                            <?= $leave['approval_step'] === 'pending_spv' ? 'Menunggu Spv' : ($leave['approval_step'] === 'pending_manager' ? 'Menunggu Manager' : 'Menunggu HRD') ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2.5 text-right pr-4 whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1 justify-end">
                                        <a href="<?= BASE_URL ?>/index.php?page=leave-detail&id=<?= $leave['id'] ?>" class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition flex items-center justify-center shadow-2xs border border-transparent dark:border-slate-700" title="Lihat Detail">
                                            <span class="material-symbols-rounded text-sm">visibility</span>
                                        </a>

                                        <a href="<?= BASE_URL ?>/index.php?page=leave-print&id=<?= $leave['id'] ?>" target="_blank" class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 text-emerald-600 dark:text-emerald-400 transition flex items-center justify-center shadow-2xs border border-transparent dark:border-emerald-900" title="Cetak Surat Izin Cuti">
                                            <span class="material-symbols-rounded text-sm">print</span>
                                        </a>

                                        <?php if ($leave['status'] === 'pending' && $leave['employee_id'] == $userId): ?>
                                            <button type="button" class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition flex items-center justify-center shadow-2xs" onclick="confirmDelete('<?= BASE_URL ?>/index.php?page=leave-action&action=cancel&id=<?= $leave['id'] ?>', 'Batalkan pengajuan cuti ini?')" title="Batalkan Pengajuan">
                                                <span class="material-symbols-rounded text-sm">block</span>
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

</div>

<script>
let currentActiveScope = '<?= $selectedScope ?>';
let currentActiveTab = 'all';

function switchScope(scope) {
    currentActiveScope = scope;

    const btnMy = document.getElementById('scopeBtnMy');
    const btnAll = document.getElementById('scopeBtnAll');
    const containerMy = document.getElementById('myLeavesContainer');
    const containerTeam = document.getElementById('teamLeavesContainer');
    const searchInput = document.getElementById('leaveSearchInput');

    if (btnMy && btnAll) {
        if (scope === 'my') {
            btnMy.className = "flex-1 py-1.5 text-xs font-bold rounded-lg transition text-center bg-white text-blue-600 shadow-2xs";
            btnAll.className = "flex-1 py-1.5 text-xs font-bold rounded-lg transition flex items-center justify-center gap-1.5 text-slate-500 hover:text-slate-800";
            if (searchInput) searchInput.placeholder = "Cari nomor surat atau alasan cuti...";
        } else {
            btnMy.className = "flex-1 py-1.5 text-xs font-bold rounded-lg transition text-center text-slate-500 hover:text-slate-800";
            btnAll.className = "flex-1 py-1.5 text-xs font-bold rounded-lg transition flex items-center justify-center gap-1.5 bg-white text-blue-600 shadow-2xs";
            if (searchInput) searchInput.placeholder = "Cari nama karyawan, departemen, alasan...";
        }
    }

    if (containerMy && containerTeam) {
        if (scope === 'my') {
            containerMy.classList.remove('hidden');
            containerTeam.classList.add('hidden');
        } else {
            containerMy.classList.add('hidden');
            containerTeam.classList.remove('hidden');
        }
    }

    applyFiltering();
}

function selectTab(tab) {
    currentActiveTab = tab;

    // Update TabBar visual indicators
    const tabBtns = document.querySelectorAll('#flutterTabBar .tab-btn');
    tabBtns.forEach(btn => {
        if (btn.getAttribute('data-tab') === tab) {
            btn.className = "tab-btn px-3 py-2.5 text-xs font-bold transition flex items-center gap-1 border-b-2 border-blue-600 text-blue-600 flex-shrink-0";
        } else {
            btn.className = "tab-btn px-3 py-2.5 text-xs font-bold transition flex items-center gap-1 border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex-shrink-0";
        }
    });

    applyFiltering();
}

function clearSearch() {
    const input = document.getElementById('leaveSearchInput');
    if (input) {
        input.value = '';
        document.getElementById('clearSearchBtn')?.classList.add('hidden');
        applyFiltering();
    }
}

function applyFiltering() {
    const searchInput = document.getElementById('leaveSearchInput');
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const clearBtn = document.getElementById('clearSearchBtn');
    
    if (clearBtn) {
        if (query.length > 0) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }
    }

    const activeContainer = currentActiveScope === 'my' 
        ? document.getElementById('myLeavesContainer') 
        : document.getElementById('teamLeavesContainer');

    if (!activeContainer) return;

    const cards = activeContainer.querySelectorAll('.leave-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const cardStatus = card.getAttribute('data-status');
        const cardSearch = card.getAttribute('data-search') || '';

        const matchesStatus = (currentActiveTab === 'all') || (cardStatus === currentActiveTab);
        const matchesQuery = !query || cardSearch.includes(query);

        if (matchesStatus && matchesQuery) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    // Check if empty state should be shown
    const emptyState = activeContainer.querySelector('.empty-state');
    const noMatchState = document.getElementById('noMatchState');

    if (cards.length > 0) {
        if (visibleCount === 0) {
            noMatchState?.classList.remove('hidden');
        } else {
            noMatchState?.classList.add('hidden');
        }
    } else {
        noMatchState?.classList.add('hidden');
    }
}

document.getElementById('leaveSearchInput')?.addEventListener('input', applyFiltering);
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
