<?php
/**
 * Leave Approvals View (Pixel-Perfect Synchronization with Flutter leave_approval_screen.dart)
 * PT. Nakakin Indonesia Leave Management System
 */

$pageTitle = 'Persetujuan Cuti Anggota';
require_once __DIR__ . '/../layouts/header.php';

$userRole = strtolower($currentUser['role'] ?? '');
$userLevel = (int)($currentUser['level_hierarki'] ?? 1);
$isHRD = in_array($userRole, ['admin', 'superadmin', 'hrd']) || $userLevel >= 7;
$isManager = $userRole === 'manager' || ($userLevel >= 5 && $userLevel <= 6);
$isSpv = in_array($userRole, ['supervisor', 'leader', 'atasan']) || ($userLevel >= 3 && $userLevel <= 4);
$isApprover = $isHRD || $isManager || $isSpv;

if (!$isApprover) {
    echo '<div class="p-6 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 font-bold text-sm">Akses ditolak! Halaman ini hanya untuk Leader, Supervisor, Manager, dan HRD.</div>';
    require_once __DIR__ . '/../layouts/footer.php';
    exit;
}

$pdo = getDbConnection();
$deptId = (int)$currentUser['departemen_id'];
$statusFilter = cleanInput($_GET['status'] ?? 'pending'); // 'pending' or 'all'

$sql = "
    SELECT lr.*, 
           e.nik, e.nama_lengkap, e.email, e.no_hp, e.sisa_cuti, e.kuota_cuti, e.tanggal_masuk, e.departemen_id,
           d.nama_dept, d.kode_dept,
           p.nama_jabatan, p.level_hierarki as employee_level,
           lt.nama_cuti, lt.potong_kuota, lt.kode as kode_cuti,
           ap.nama_lengkap as nama_atasan
    FROM pengajuan_cuti lr
    JOIN karyawan e ON lr.employee_id = e.id
    JOIN departemen d ON e.departemen_id = d.id
    JOIN jabatan p ON e.jabatan_id = p.id
    JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
    LEFT JOIN karyawan ap ON lr.approved_by = ap.id
    WHERE 1=1
";

$params = [];

if ($isHRD) {
    if ($statusFilter === 'pending') {
        $sql .= " AND lr.status = 'pending' ";
    } elseif ($statusFilter !== 'all') {
        $sql .= " AND lr.status = ? ";
        $params[] = $statusFilter;
    }
} elseif ($isManager) {
    $sql .= " AND lr.employee_id != ? ";
    $params[] = $currentUser['id'];
    if ($statusFilter === 'pending') {
        $sql .= " AND lr.status = 'pending' ";
    } elseif ($statusFilter !== 'all') {
        $sql .= " AND lr.status = ? ";
        $params[] = $statusFilter;
    }
} else {
    // Leader / Supervisor (Department scoped)
    $sql .= " AND e.departemen_id = ? AND e.id != ? ";
    $params[] = $deptId;
    $params[] = $currentUser['id'];
    if ($statusFilter === 'pending') {
        $sql .= " AND lr.status = 'pending' ";
    } elseif ($statusFilter !== 'all') {
        $sql .= " AND lr.status = ? ";
        $params[] = $statusFilter;
    }
}

$sql .= " ORDER BY CASE 
            WHEN lr.approval_step = 'pending_hrd' AND " . ($isHRD ? "1=1" : "1=0") . " THEN 1 
            WHEN lr.approval_step = 'pending_manager' AND " . ($isManager ? "1=1" : "1=0") . " THEN 1 
            WHEN lr.approval_step = 'pending_spv' AND " . ($isSpv ? "1=1" : "1=0") . " THEN 1 
            WHEN lr.status = 'pending' THEN 2 
            ELSE 3 
          END, lr.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Helper function to check if this user can approve this item (Strict multi-tier check)
function canUserApproveItem($leave, $user, $isHRD, $isManager, $isSpv, $deptId) {
    if (strtolower($leave['status'] ?? '') !== 'pending') return false;
    if ((int)$user['id'] === (int)$leave['employee_id']) return false;

    $step = strtolower($leave['approval_step'] ?? 'pending_spv');
    if ($step === 'pending_spv') {
        return $isSpv && ((int)$user['departemen_id'] === (int)$leave['departemen_id']);
    } elseif ($step === 'pending_manager') {
        return $isManager;
    } elseif ($step === 'pending_hrd') {
        return $isHRD;
    }
    return false;
}
?>

<div class="space-y-4 sm:space-y-6">

    <!-- ========================================================================= -->
    <!-- 1. MOBILE VIEW (Screen < 1024px) - EXACT FLUTTER LEAVE APPROVAL SCREEN   -->
    <!-- ========================================================================= -->
    <div class="block lg:hidden space-y-3 max-w-lg mx-auto pb-24">
        
        <!-- Mobile AppBar Header (Exact Flutter layout) -->
        <div class="flex items-center justify-between py-1 px-1">
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Persetujuan Cuti Anggota</h2>
            
            <div class="flex items-center gap-1">
                <!-- Filter Popover / Dropdown Menu -->
                <div class="relative" id="filterDropdownContainer">
                    <button type="button" onclick="toggleFilterMenu()" 
                            class="w-9 h-9 rounded-xl hover:bg-slate-100 text-slate-700 flex items-center justify-center transition" 
                            title="Filter Status">
                        <span class="material-symbols-rounded text-xl">filter_list</span>
                    </button>

                    <div id="filterMenu" class="hidden absolute right-0 mt-1 w-52 bg-white rounded-2xl shadow-xl border border-slate-200/90 py-1.5 z-50 text-xs font-semibold">
                        <a href="<?= BASE_URL ?>/index.php?page=leave-approvals&status=pending" 
                           class="flex items-center gap-2 px-4 py-2.5 text-slate-800 hover:bg-slate-50 <?= $statusFilter === 'pending' ? 'font-bold text-blue-600 bg-blue-50/50' : '' ?>">
                            <span class="material-symbols-rounded text-base <?= $statusFilter === 'pending' ? 'text-blue-600' : 'text-slate-400' ?>">hourglass_top</span>
                            <span>Hanya Menunggu (Pending)</span>
                        </a>
                        <a href="<?= BASE_URL ?>/index.php?page=leave-approvals&status=all" 
                           class="flex items-center gap-2 px-4 py-2.5 text-slate-800 hover:bg-slate-50 <?= $statusFilter === 'all' ? 'font-bold text-blue-600 bg-blue-50/50' : '' ?>">
                            <span class="material-symbols-rounded text-base <?= $statusFilter === 'all' ? 'text-blue-600' : 'text-slate-400' ?>">history</span>
                            <span>Semua Riwayat Pengajuan</span>
                        </a>
                    </div>
                </div>

                <!-- Refresh Button -->
                <button type="button" onclick="window.location.reload()" 
                        class="w-9 h-9 rounded-xl hover:bg-slate-100 text-slate-700 flex items-center justify-center transition" 
                        title="Segarkan">
                    <span class="material-symbols-rounded text-xl">refresh</span>
                </button>
            </div>
        </div>

        <!-- Approval Cards List / Empty State -->
        <?php if (empty($requests)): ?>
            <div class="p-8 rounded-2xl bg-white border border-slate-200 text-center shadow-2xs mt-4">
                <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-rounded text-3xl">done_all</span>
                </div>
                <h4 class="text-base font-bold text-slate-900">Semua Bersih!</h4>
                <p class="text-xs text-slate-500 mt-1">Tidak ada pengajuan cuti yang memerlukan persetujuan.</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($requests as $req): 
                    $status = strtolower($req['status'] ?? 'pending');
                    $step = strtolower($req['approval_step'] ?? 'pending_spv');
                    $canApproveThis = canUserApproveItem($req, $currentUser, $isHRD, $isManager, $isSpv, $deptId);
                    $empLevel = (int)($req['employee_level'] ?? 1);

                    $tglMulai = $req['tanggal_mulai'];
                    $tglSelesai = $req['tanggal_selesai'];
                    $durasiNum = (float)$req['total_hari'];
                    $durasiText = number_format($durasiNum, 1, '.', '') . ' Hari';

                    // Flutter stepLabel getter logic
                    if ($status === 'approved') {
                        $stageLabel = 'Disetujui Sepenuhnya';
                        $stageIcon = 'check_circle';
                        $stageBg = 'bg-emerald-50 text-emerald-700 border-[#BBF7D0]';
                    } elseif ($status === 'rejected') {
                        $stageLabel = 'Ditolak';
                        $stageIcon = 'cancel';
                        $stageBg = 'bg-rose-50 text-rose-700 border-[#FECDD3]';
                    } elseif ($status === 'cancelled') {
                        $stageLabel = 'Dibatalkan';
                        $stageIcon = 'block';
                        $stageBg = 'bg-slate-100 text-slate-700 border-slate-200';
                    } else {
                        $stageIcon = 'layers';
                        $stageBg = 'bg-[#FFF7ED] text-[#EA580C] border-[#FFEDD5]';
                        if ($step === 'pending_spv') {
                            $stageLabel = 'Tahap 1: Review Spv / Leader';
                        } elseif ($step === 'pending_manager') {
                            $stageLabel = ($empLevel <= 2) ? 'Tahap 2: Review Manager' : 'Tahap 1: Review Manager';
                        } elseif ($step === 'pending_hrd') {
                            $stageLabel = ($empLevel <= 2) ? 'Tahap 3: Review HRD Final' : (($empLevel <= 4) ? 'Tahap 2: Review HRD Final' : 'Tahap 1: Review HRD Final');
                        } else {
                            $stageLabel = 'Menunggu Persetujuan';
                        }
                    }

                    $initial = strtoupper(substr($req['nama_lengkap'] ?? 'O', 0, 1));
                    $kodeCuti = htmlspecialchars($req['kode_cuti'] ?? 'CT');
                ?>
                    <div class="bg-white rounded-2xl border border-slate-200/90 p-4 space-y-3 shadow-2xs hover:shadow-xs transition cursor-pointer"
                         onclick="window.location.href='<?= BASE_URL ?>/index.php?page=leave-detail&id=<?= $req['id'] ?>'">
                        
                        <!-- Top Header: Avatar + Name + NIK & Dept + Kode Cuti Badge -->
                        <div class="flex items-center justify-between gap-2.5">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 font-bold flex items-center justify-center text-sm border border-blue-100 flex-shrink-0">
                                    <?= $initial ?>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-slate-900 truncate leading-tight">
                                        <?= htmlspecialchars($req['nama_lengkap']) ?>
                                    </h4>
                                    <p class="text-xs text-slate-400 truncate mt-0.5 font-medium">
                                        <?= htmlspecialchars($req['nik']) ?> &bull; <?= htmlspecialchars($req['nama_dept']) ?>
                                    </p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600 font-bold text-[11px] flex-shrink-0">
                                <?= $kodeCuti ?>
                            </span>
                        </div>

                        <!-- Stage Chip (Tahap Review Badge) -->
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11.5px] font-bold border <?= $stageBg ?>">
                                <span class="material-symbols-rounded text-sm"><?= $stageIcon ?></span>
                                <span><?= $stageLabel ?></span>
                            </span>
                        </div>

                        <div class="border-t border-slate-100 pt-0.5"></div>

                        <!-- Details: Date Range & Duration -->
                        <div class="flex items-center justify-between text-xs text-slate-700">
                            <div class="flex items-center gap-1.5 font-bold">
                                <span class="material-symbols-rounded text-slate-400 text-sm">date_range</span>
                                <span><?= $tglMulai ?> s/d <?= $tglSelesai ?></span>
                            </div>
                            <span class="font-bold text-blue-600 text-xs"><?= $durasiText ?></span>
                        </div>

                        <!-- Reason (Alasan Box) -->
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-600">
                            Alasan: <?= htmlspecialchars($req['alasan']) ?>
                        </div>

                        <!-- Action Buttons (Exact Flutter Design: Tolak border red, Setujui solid green) -->
                        <?php if ($status === 'pending'): ?>
                            <div class="pt-1" onclick="event.stopPropagation()">
                                <div class="flex items-center gap-2.5">
                                    <!-- Reject Button -->
                                    <button type="button" 
                                            <?= $canApproveThis ? '' : 'disabled' ?>
                                            onclick="openRejectModal(<?= $req['id'] ?>, '<?= addslashes(htmlspecialchars($req['nama_lengkap'])) ?>', '<?= addslashes(htmlspecialchars($req['nik'])) ?>', '<?= $durasiText ?>', '<?= $tglMulai ?> s/d <?= $tglSelesai ?>')"
                                            class="flex-1 py-2.5 px-3 rounded-xl border text-xs font-bold transition flex items-center justify-center gap-1.5 <?= $canApproveThis ? 'border-[#DC2626] text-[#DC2626] bg-white hover:bg-rose-50 active:bg-rose-100' : 'border-slate-200 text-slate-300 bg-slate-50 cursor-not-allowed' ?>">
                                        <span class="material-symbols-rounded text-sm">close</span>
                                        <span>Tolak</span>
                                    </button>

                                    <!-- Approve Button -->
                                    <button type="button" 
                                            <?= $canApproveThis ? '' : 'disabled' ?>
                                            onclick="openApproveModal(<?= $req['id'] ?>, '<?= addslashes(htmlspecialchars($req['nama_lengkap'])) ?>', '<?= addslashes(htmlspecialchars($req['nik'])) ?>', '<?= $durasiText ?>', '<?= $tglMulai ?> s/d <?= $tglSelesai ?>')"
                                            class="flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 <?= $canApproveThis ? 'bg-[#059669] hover:bg-[#047857] text-white shadow-sm active:scale-[0.98]' : 'bg-slate-100 text-slate-400 cursor-not-allowed' ?>">
                                        <span class="material-symbols-rounded text-sm">check</span>
                                        <span>Setujui</span>
                                    </button>
                                </div>

                                <?php if (!$canApproveThis): ?>
                                    <p class="text-center text-[11px] font-medium text-rose-500 mt-2">
                                        <?= $step === 'pending_spv' ? 'Menunggu persetujuan Leader/Spv lebih dulu' : ($step === 'pending_manager' ? 'Menunggu persetujuan Plant Manager lebih dulu' : 'Menunggu persetujuan akhir HRD') ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <!-- ========================================================================= -->
    <!-- 2. DESKTOP VIEW (Screen >= 1024px) - FULL DATATABLES TABLE                -->
    <!-- ========================================================================= -->
    <div class="hidden lg:block bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.04)] overflow-hidden">
        <div class="p-5 sm:px-6 border-b border-slate-100 dark:border-slate-800 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 bg-slate-50/40 dark:bg-slate-800/40">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm font-bold border border-amber-200/60 dark:border-amber-900 shadow-2xs">
                    <span class="material-symbols-rounded text-lg">verified</span>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">
                        <?= $isHRD ? 'Persetujuan & Monitoring Cuti (Seluruh 15 Departemen)' : ($isManager ? 'Persetujuan Cuti Plant Manager' : 'Permohonan Cuti Anggota Tim (' . htmlspecialchars($currentUser['nama_dept'] ?? 'Departemen') . ')') ?>
                    </h3>
                    <p class="text-[11px] text-slate-400 font-medium">
                        <?= $isHRD ? 'Pantau seluruh tahapan cuti karyawan dan lakukan verifikasi final ketika giliran approval HRD tiba' : 'Verifikasi dan berikan persetujuan izin cuti untuk anggota tim Anda' ?>
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-2.5 flex-wrap">
                <!-- Filter Status Pills -->
                <div class="inline-flex p-1 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 rounded-2xl shadow-2xs text-xs font-bold gap-1">
                    <a href="<?= BASE_URL ?>/index.php?page=leave-approvals&status=pending" 
                       class="px-3 py-1 rounded-xl transition <?= $statusFilter === 'pending' ? 'bg-amber-500 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' ?>">
                        Menunggu
                    </a>
                    <a href="<?= BASE_URL ?>/index.php?page=leave-approvals&status=approved" 
                       class="px-3 py-1 rounded-xl transition <?= $statusFilter === 'approved' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' ?>">
                        Disetujui
                    </a>
                    <a href="<?= BASE_URL ?>/index.php?page=leave-approvals&status=rejected" 
                       class="px-3 py-1 rounded-xl transition <?= $statusFilter === 'rejected' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' ?>">
                        Ditolak
                    </a>
                    <a href="<?= BASE_URL ?>/index.php?page=leave-approvals&status=all" 
                       class="px-3 py-1 rounded-xl transition <?= $statusFilter === 'all' ? 'bg-slate-900 dark:bg-slate-700 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' ?>">
                        Semua
                    </a>
                </div>

                <span class="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-black border border-slate-200/60 dark:border-slate-700 shadow-2xs">
                    Total: <?= count($requests) ?> Data
                </span>
            </div>
        </div>

        <div class="p-5">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs datatable">
                    <thead>
                        <tr>
                            <th class="text-center w-8">No</th>
                            <th>Karyawan & Departemen</th>
                            <th>Permohonan & Periode Cuti</th>
                            <th class="text-center">Sisa Kuota</th>
                            <th class="text-center">Status / Tahapan</th>
                            <th class="text-right pr-4">Aksi Persetujuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($requests as $req): 
                            $step = strtolower($req['approval_step'] ?? 'pending_spv');
                            $canApproveRow = canUserApproveItem($req, $currentUser, $isHRD, $isManager, $isSpv, $deptId);
                            $stepBadge = '';

                            if ($req['status'] === 'pending') {
                                if ($step === 'pending_spv') {
                                    $stepBadge = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 text-[10.5px] font-bold">Review Leader</span>';
                                } elseif ($step === 'pending_manager') {
                                    $stepBadge = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-[10.5px] font-bold">Review Manager</span>';
                                } elseif ($step === 'pending_hrd') {
                                    $stepBadge = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-purple-50 text-purple-700 border border-purple-200 text-[10.5px] font-bold">Menunggu HRD</span>';
                                }
                            } else {
                                $stepBadge = getStatusBadge($req['status']);
                            }
                            
                            $tglMulaiFormatted = date('d M Y', strtotime($req['tanggal_mulai']));
                            $tglSelesaiFormatted = date('d M Y', strtotime($req['tanggal_selesai']));
                            $periodeText = ($req['tanggal_mulai'] === $req['tanggal_selesai']) ? $tglMulaiFormatted : "$tglMulaiFormatted - $tglSelesaiFormatted";
                            $durasiText = ($req['total_hari'] == 0.5) ? '0.5 Hari' : $req['total_hari'] . ' Hari Kerja';
                        ?>
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60 transition">
                                <td class="text-center py-2.5">
                                    <span class="w-5 h-5 rounded-md bg-slate-100 dark:bg-slate-800 inline-flex items-center justify-center text-[10.5px] text-slate-500 dark:text-slate-400 font-bold">
                                        <?= $no++ ?>
                                    </span>
                                </td>
                                <td class="py-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 font-bold flex items-center justify-center text-xs border border-blue-100 dark:border-blue-900 flex-shrink-0">
                                            <?= strtoupper(substr($req['nama_lengkap'], 0, 1)) ?>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-extrabold text-slate-900 dark:text-white truncate"><?= htmlspecialchars($req['nama_lengkap']) ?></div>
                                            <div class="text-[10.5px] text-slate-400 dark:text-slate-500 font-medium">
                                                <?= htmlspecialchars($req['nik']) ?> &bull; <?= htmlspecialchars($req['nama_dept']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2.5 whitespace-nowrap">
                                    <div class="space-y-0.5">
                                        <div class="font-extrabold text-slate-800 dark:text-slate-200"><?= htmlspecialchars($req['nama_cuti']) ?></div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                            <?= $periodeText ?> <span class="text-blue-600 dark:text-blue-400 font-bold">(<?= $durasiText ?>)</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center py-2.5 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-[11px] border border-slate-200 dark:border-slate-700">
                                        <?= (int)$req['sisa_cuti'] ?> Hari
                                    </span>
                                </td>
                                <td class="text-center py-2.5 whitespace-nowrap">
                                    <?= $stepBadge ?>
                                </td>
                                <td class="py-2.5 text-right pr-4 whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <?php if ($canApproveRow): ?>
                                            <button type="button" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1" onclick="openApproveModal(<?= $req['id'] ?>, '<?= addslashes(htmlspecialchars($req['nama_lengkap'])) ?>', '<?= addslashes(htmlspecialchars($req['nik'])) ?>', '<?= $durasiText ?>', '<?= $periodeText ?>')">
                                                <span class="material-symbols-rounded text-sm">check</span>
                                                <span>Setuju</span>
                                            </button>
                                            <button type="button" class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1" onclick="openRejectModal(<?= $req['id'] ?>, '<?= addslashes(htmlspecialchars($req['nama_lengkap'])) ?>', '<?= addslashes(htmlspecialchars($req['nik'])) ?>', '<?= $durasiText ?>', '<?= $periodeText ?>')">
                                                <span class="material-symbols-rounded text-sm">close</span>
                                                <span>Tolak</span>
                                            </button>
                                        <?php elseif ($req['status'] === 'pending'): ?>
                                            <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold text-[11px] border border-slate-200 dark:border-slate-700 flex items-center gap-1" title="Menunggu giliran approval hierarki">
                                                <span class="material-symbols-rounded text-xs text-slate-400">hourglass_top</span>
                                                <span>Pantau</span>
                                            </span>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>/index.php?page=leave-detail&id=<?= $req['id'] ?>" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition flex items-center justify-center shadow-2xs border border-transparent dark:border-slate-700" title="Lihat Detail">
                                            <span class="material-symbols-rounded text-sm">visibility</span>
                                        </a>
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

<!-- Interactive Flutter-identical Modal Dialogs & Scripts -->
<script>
function toggleFilterMenu() {
    const menu = document.getElementById('filterMenu');
    if (menu) {
        menu.classList.toggle('hidden');
    }
}

// Close filter menu when clicking outside
document.addEventListener('click', function(e) {
    const container = document.getElementById('filterDropdownContainer');
    const menu = document.getElementById('filterMenu');
    if (container && menu && !container.contains(e.target)) {
        menu.classList.add('hidden');
    }
});

function openApproveModal(id, name, nik, durasi, periode) {
    Swal.fire({
        title: '<div class="flex items-center justify-center gap-2 text-emerald-600 dark:text-emerald-400"><span class="material-symbols-rounded text-2xl">check_circle</span><span class="text-base font-bold text-slate-900 dark:text-white">Setujui Pengajuan</span></div>',
        html: `
            <div class="text-left text-xs space-y-3 mt-2">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700">
                    <div class="font-bold text-slate-900 dark:text-white text-xs">Pemohon: ${name} (${nik})</div>
                    <div class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5">Durasi: ${durasi} (${periode})</div>
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300 text-[11px] uppercase tracking-wider">Catatan Tambahan (Opsional)</label>
                    <textarea id="swalNotesApprove" rows="3" class="w-full p-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none" placeholder="Misal: Disetujui, pekerjaan didelegasikan..."></textarea>
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
            const noteVal = document.getElementById('swalNotesApprove')?.value || '';
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

function openRejectModal(id, name, nik, durasi, periode) {
    Swal.fire({
        title: '<div class="flex items-center justify-center gap-2 text-rose-600 dark:text-rose-400"><span class="material-symbols-rounded text-2xl">highlight_off</span><span class="text-base font-bold text-slate-900 dark:text-white">Tolak Pengajuan</span></div>',
        html: `
            <div class="text-left text-xs space-y-3 mt-2">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700">
                    <div class="font-bold text-slate-900 dark:text-white text-xs">Pemohon: ${name} (${nik})</div>
                    <div class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5">Durasi: ${durasi} (${periode})</div>
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300 text-[11px] uppercase tracking-wider">Alasan Penolakan (Wajib) <span class="text-rose-500">*</span></label>
                    <textarea id="swalNotesReject" rows="3" class="w-full p-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 focus:outline-none" placeholder="Tuliskan alasan penolakan..."></textarea>
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
            const noteVal = document.getElementById('swalNotesReject')?.value.trim();
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
            input.name = 'catatan_atasan';
            input.value = result.value;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
