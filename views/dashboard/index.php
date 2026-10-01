<?php
/**
 * Dashboard View (Tailwind CSS Edition - Synced with Flutter Mobile & Desktop)
 * PT. Nakakin Indonesia Leave Management System
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../layouts/header.php';

$pdo = getDbConnection();
$userId = $currentUser['id'];
$userRole = strtolower($currentUser['role'] ?? '');
$userLevel = (int)($currentUser['level_hierarki'] ?? 1);
$deptId = $currentUser['departemen_id'];

$isHRD = in_array($userRole, ['admin', 'superadmin', 'hrd']) || $userLevel >= 7;
$isSuperAdmin = in_array($userRole, ['admin', 'superadmin']) || $userLevel >= 8;
$isManager = $userRole === 'manager' || ($userLevel >= 5 && $userLevel <= 6);
$isSpv = in_array($userRole, ['supervisor', 'leader', 'atasan']) || ($userLevel >= 3 && $userLevel <= 4);
$isApprover = $isHRD || $isManager || $isSpv;
$canManageTeam = $isApprover || $isHRD;

// 1. Personal Stats
$stmtPersonal = $pdo->prepare("
    SELECT 
        COUNT(*) as total_pengajuan,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as total_approved,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as total_pending,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as total_rejected,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as total_cancelled
    FROM pengajuan_cuti 
    WHERE employee_id = ?
");
$stmtPersonal->execute([$userId]);
$myStats = $stmtPersonal->fetch();

// 2. Personal Recent Leaves
$stmtMyLeaves = $pdo->prepare("
    SELECT lr.*, lt.nama_cuti, lt.potong_kuota
    FROM pengajuan_cuti lr
    JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
    WHERE lr.employee_id = ?
    ORDER BY lr.created_at DESC
    LIMIT 6
");
$stmtMyLeaves->execute([$userId]);
$myRecentLeaves = $stmtMyLeaves->fetchAll();

// 3. Pending Approvals & Monitoring Queue
$deptPendingLeaves = [];
if ($isApprover) {
    if ($isHRD) {
        $stmtDeptPending = $pdo->query("
            SELECT lr.*, e.nama_lengkap, e.nik, e.departemen_id, d.nama_dept, p.nama_jabatan, lt.nama_cuti, lt.potong_kuota
            FROM pengajuan_cuti lr
            JOIN karyawan e ON lr.employee_id = e.id
            JOIN departemen d ON e.departemen_id = d.id
            JOIN jabatan p ON e.jabatan_id = p.id
            JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
            WHERE lr.status = 'pending'
            ORDER BY CASE WHEN lr.approval_step = 'pending_hrd' THEN 1 ELSE 2 END, lr.created_at ASC
            LIMIT 10
        ");
        $deptPendingLeaves = $stmtDeptPending->fetchAll();
    } elseif ($isManager) {
        $stmtDeptPending = $pdo->prepare("
            SELECT lr.*, e.nama_lengkap, e.nik, e.departemen_id, d.nama_dept, p.nama_jabatan, lt.nama_cuti, lt.potong_kuota
            FROM pengajuan_cuti lr
            JOIN karyawan e ON lr.employee_id = e.id
            JOIN departemen d ON e.departemen_id = d.id
            JOIN jabatan p ON e.jabatan_id = p.id
            JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
            WHERE lr.status = 'pending' AND lr.employee_id != ?
            ORDER BY CASE WHEN lr.approval_step = 'pending_manager' THEN 1 ELSE 2 END, lr.created_at ASC
            LIMIT 10
        ");
        $stmtDeptPending->execute([$userId]);
        $deptPendingLeaves = $stmtDeptPending->fetchAll();
    } else {
        $stmtDeptPending = $pdo->prepare("
            SELECT lr.*, e.nama_lengkap, e.nik, e.departemen_id, d.nama_dept, p.nama_jabatan, lt.nama_cuti, lt.potong_kuota
            FROM pengajuan_cuti lr
            JOIN karyawan e ON lr.employee_id = e.id
            JOIN departemen d ON e.departemen_id = d.id
            JOIN jabatan p ON e.jabatan_id = p.id
            JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
            WHERE lr.status = 'pending' AND e.departemen_id = ? AND lr.employee_id != ?
            ORDER BY lr.created_at ASC
            LIMIT 10
        ");
        $stmtDeptPending->execute([$deptId, $userId]);
        $deptPendingLeaves = $stmtDeptPending->fetchAll();
    }
}

// 4. Admin HRD Overview Metrics
if ($isHRD) {
    $totalKaryawan = $pdo->query("SELECT COUNT(*) FROM karyawan WHERE status_aktif = 'Aktif'")->fetchColumn();
    $totalCutiBulanIni = $pdo->query("SELECT COUNT(*) FROM pengajuan_cuti WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetchColumn();
    $totalPendingAll = $pdo->query("SELECT COUNT(*) FROM pengajuan_cuti WHERE status = 'pending'")->fetchColumn();
    $cutiHariIni = $pdo->query("SELECT COUNT(*) FROM pengajuan_cuti WHERE status = 'approved' AND CURRENT_DATE() BETWEEN tanggal_mulai AND tanggal_selesai")->fetchColumn();

    $stmtChartDept = $pdo->query("
        SELECT d.nama_dept, COUNT(lr.id) as total_cuti
        FROM departemen d
        LEFT JOIN karyawan e ON d.id = e.departemen_id
        LEFT JOIN pengajuan_cuti lr ON e.id = lr.employee_id AND lr.status = 'approved'
        GROUP BY d.id, d.nama_dept
    ");
    $chartDeptData = $stmtChartDept->fetchAll();
}

$currentShiftText = (strpos($currentUser['current_shift'] ?? '', 'Shift 2') !== false) ? 'Shift 2 (Malam)' : 'Shift 1 (Pagi)';
$isShiftMalam = strpos($currentUser['current_shift'] ?? '', 'Shift 2') !== false;
?>

<!-- ========================================================================= -->
<!-- 1. MOBILE VIEW (Screen < 1024px) - 100% IDENTICAL TO FLUTTER APK SCREEN -->
<!-- ========================================================================= -->
<div class="block lg:hidden space-y-4 max-w-lg mx-auto">

    <!-- 1. User Header Banner (Flutter Exact Gradient & Layout) -->
    <div class="p-5 rounded-2xl bg-gradient-to-br from-[#0F172A] to-[#1E3A8A] text-white shadow-xl shadow-blue-950/20 border border-slate-800/80">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="w-13 h-13 rounded-full bg-white/20 border border-white/30 flex items-center justify-center text-white text-xl font-black flex-shrink-0 shadow-inner">
                    <?= strtoupper(substr($currentUser['nama_lengkap'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="text-base font-bold text-white truncate leading-snug">
                        <?= htmlspecialchars($currentUser['nama_lengkap'] ?? 'Karyawan') ?>
                    </h2>
                    <p class="text-xs text-white/85 truncate mt-0.5">
                        <?= htmlspecialchars($currentUser['nik'] ?? '') ?> &bull; <?= htmlspecialchars($currentUser['nama_jabatan'] ?? '') ?>
                    </p>
                </div>
            </div>
            <div class="px-2.5 py-1 rounded-xl bg-white/20 border border-white/20 text-[10.5px] font-black text-white uppercase tracking-wider flex-shrink-0">
                <?= htmlspecialchars($currentUser['role'] ?? 'operator') ?>
            </div>
        </div>

        <!-- Inset Dark Container -->
        <div class="mt-4 p-3 rounded-xl bg-black/25 border border-white/10 space-y-2 text-xs text-white">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-building text-white/70 w-4 text-center"></i>
                <span class="truncate">Dept: <strong><?= htmlspecialchars($currentUser['nama_dept'] ?? '-') ?></strong></span>
            </div>
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-clock text-white/70 w-4 text-center"></i>
                <span class="truncate">Masa Kerja: <strong><?= hitungMasaKerja($currentUser['tanggal_masuk']) ?></strong></span>
            </div>
            <div class="flex items-center gap-2">
                <i class="fa-solid <?= $isShiftMalam ? 'fa-moon text-purple-300' : 'fa-sun text-amber-300' ?> w-4 text-center"></i>
                <span class="truncate">Jadwal Shift: <strong><?= $currentShiftText ?></strong></span>
            </div>
        </div>
    </div>

    <!-- 2. Super Admin Dedicated Banner (if Super Admin) -->
    <?php if ($isSuperAdmin): ?>
        <a href="<?= BASE_URL ?>/index.php?page=employees" 
           class="flex items-center justify-between p-3.5 rounded-2xl bg-gradient-to-r from-[#4F46E5] to-[#7C3AED] text-white shadow-md shadow-indigo-600/25 active:scale-[0.98] transition">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fa-solid fa-sliders"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold truncate">Matriks Setting Role</span>
                        <span class="px-1.5 py-0.5 rounded bg-white/25 text-[8.5px] font-black tracking-wider">SUPER ADMIN</span>
                    </div>
                    <p class="text-[11px] text-white/80 truncate">Konfigurasi wewenang 7 role karyawan</p>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right text-xs text-white/70"></i>
        </a>
    <?php endif; ?>

    <!-- 3. Feature Action Cards (by Role matching Flutter) -->
    <div class="space-y-2.5">
        
        <!-- A. Khusus HRD & Admin: Kelola Jatah Cuti & List Karyawan -->
        <?php if ($isHRD): ?>
            <div class="grid grid-cols-2 gap-2.5">
                <a href="<?= BASE_URL ?>/index.php?page=quotas" 
                   class="flex items-center gap-2.5 p-3 rounded-2xl bg-gradient-to-r from-[#4F46E5] to-[#6366F1] text-white shadow-md shadow-indigo-600/20 active:scale-[0.98] transition">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-base flex-shrink-0">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold leading-tight truncate">Kelola Jatah</div>
                        <div class="text-[10px] text-white/75 truncate mt-0.5">Alokasi Kuota</div>
                    </div>
                </a>

                <a href="<?= BASE_URL ?>/index.php?page=employees" 
                   class="flex items-center gap-2.5 p-3 rounded-2xl bg-gradient-to-r from-[#E11D48] to-[#F43F5E] text-white shadow-md shadow-rose-600/20 active:scale-[0.98] transition">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-base flex-shrink-0">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold leading-tight truncate">List Karyawan</div>
                        <div class="text-[10px] text-white/75 truncate mt-0.5">Daftar Pegawai</div>
                    </div>
                </a>
            </div>
        <?php endif; ?>

        <!-- B. Khusus Leader, Supervisor, Manager, & Admin: Tim & Absensi Shift -->
        <?php if ($canManageTeam): ?>
            <a href="<?= BASE_URL ?>/index.php?page=team-attendance" 
               class="flex items-center justify-between p-3.5 rounded-2xl bg-gradient-to-r from-[#8B5CF6] to-[#A855F7] text-white shadow-md shadow-purple-600/20 active:scale-[0.98] transition">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fa-solid fa-id-badge"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold truncate">Tim & Absensi Shift</div>
                        <div class="text-[10.5px] text-white/80 truncate">Kelola Shift 1 & 2, Rolling & Mangkir</div>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-xs text-white/70"></i>
            </a>

            <!-- Leader/Supervisor non-admin list anggota tim -->
            <?php if (!$isHRD): ?>
                <a href="<?= BASE_URL ?>/index.php?page=employees" 
                   class="flex items-center justify-between p-3 rounded-2xl bg-gradient-to-r from-[#E11D48] to-[#F43F5E] text-white shadow-md shadow-rose-600/20 active:scale-[0.98] transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-base flex-shrink-0">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold truncate">List Karyawan Bagian</div>
                            <div class="text-[10.5px] text-white/80 truncate">Daftar Anggota Departemen</div>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-xs text-white/70"></i>
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <!-- C. Papan Live Kehadiran (Semua Role) -->
        <a href="<?= BASE_URL ?>/index.php?page=board" 
           class="flex items-center justify-between p-3.5 rounded-2xl bg-gradient-to-r from-[#0284C7] to-[#0EA5E9] text-white shadow-md shadow-sky-600/20 active:scale-[0.98] transition">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fa-solid fa-tv"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold truncate">Papan Live Kehadiran</div>
                    <div class="text-[10.5px] text-white/80 truncate">Status Cuti Tim & Departemen Realtime</div>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right text-xs text-white/70"></i>
        </a>

        <!-- D. Buku Panduan & Tutorial PDF (Role-Specific) -->
        <a href="<?= BASE_URL ?>/api/docs/panduan.php?role=<?= urlencode($userRole) ?>" target="_blank" 
           class="flex items-center justify-between p-3.5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs active:scale-[0.98] transition">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-base flex-shrink-0">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-slate-900 truncate">Buku Panduan & Tutorial</div>
                    <div class="text-[10.5px] text-slate-500 truncate">Panduan khusus peran <?= htmlspecialchars($currentUser['nama_jabatan'] ?? 'Karyawan') ?></div>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-sky-600 text-white text-[10.5px] font-bold flex items-center gap-1 flex-shrink-0">
                <span>Buka</span>
                <i class="fa-solid fa-circle-arrow-right text-[10px]"></i>
            </span>
        </a>

    </div>

    <!-- 4. Ringkasan Kuota Cuti (Flutter 2x2 Bento & Mini Counters) -->
    <div class="space-y-2.5 pt-2">
        <h3 class="text-sm font-bold text-slate-900">Ringkasan Kuota Cuti</h3>
        
        <div class="grid grid-cols-2 gap-2.5">
            <!-- Sisa Kuota Cuti -->
            <div class="p-3.5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fa-solid fa-umbrella-beach"></i>
                </div>
                <div>
                    <div class="text-[10.5px] text-slate-500 font-medium">Sisa Kuota Cuti</div>
                    <div class="text-xl font-extrabold text-blue-900 leading-tight"><?= (int)$currentUser['sisa_cuti'] ?></div>
                    <div class="text-[9.5px] text-slate-400">Hari Tersedia</div>
                </div>
            </div>

            <!-- Cuti Terpakai -->
            <div class="p-3.5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fa-solid fa-calendar-xmark"></i>
                </div>
                <div>
                    <div class="text-[10.5px] text-slate-500 font-medium">Cuti Terpakai</div>
                    <div class="text-xl font-extrabold text-teal-700 leading-tight"><?= (int)$currentUser['cuti_terpakai'] ?></div>
                    <div class="text-[9.5px] text-slate-400">Hari Diambil</div>
                </div>
            </div>
        </div>

        <!-- 3 Mini Status Counters -->
        <div class="grid grid-cols-3 gap-2">
            <div class="p-2.5 rounded-xl bg-white border border-slate-200/90 text-center shadow-2xs">
                <i class="fa-solid fa-hourglass-half text-amber-500 text-xs"></i>
                <div class="text-sm font-bold text-amber-600 mt-0.5"><?= (int)($myStats['total_pending'] ?? 0) ?></div>
                <div class="text-[9.5px] text-slate-500 font-medium">Menunggu</div>
            </div>
            <div class="p-2.5 rounded-xl bg-white border border-slate-200/90 text-center shadow-2xs">
                <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                <div class="text-sm font-bold text-emerald-600 mt-0.5"><?= (int)($myStats['total_approved'] ?? 0) ?></div>
                <div class="text-[9.5px] text-slate-500 font-medium">Disetujui</div>
            </div>
            <div class="p-2.5 rounded-xl bg-white border border-slate-200/90 text-center shadow-2xs">
                <i class="fa-solid fa-circle-xmark text-rose-500 text-xs"></i>
                <div class="text-sm font-bold text-rose-600 mt-0.5"><?= (int)($myStats['total_rejected'] ?? 0) ?></div>
                <div class="text-[9.5px] text-slate-500 font-medium">Ditolak</div>
            </div>
        </div>
    </div>

    <!-- 5. Quick Approval Alert Banner (if Approver & pending > 0) -->
    <?php if ($isApprover && !empty($deptPendingLeaves)): ?>
        <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-300 text-slate-900 flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg flex-shrink-0 animate-pulse">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold leading-tight truncate">Ada <?= count($deptPendingLeaves) ?> Pengajuan Menunggu!</div>
                    <div class="text-[10.5px] text-slate-600 truncate mt-0.5">Perlu tindakan persetujuan hierarki dari Anda.</div>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/index.php?page=leave-approvals" class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold flex-shrink-0 shadow-sm">
                Periksa
            </a>
        </div>
    <?php endif; ?>

    <!-- 6. Pengajuan Terakhir Saya (Card-Based List matching Flutter _buildLeaveCard) -->
    <div class="space-y-2.5 pt-2">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Pengajuan Terakhir Saya</h3>
            <a href="<?= BASE_URL ?>/index.php?page=leaves-my" class="text-xs font-bold text-blue-600 hover:text-blue-700">Lihat Semua &rarr;</a>
        </div>

        <?php if (empty($myRecentLeaves)): ?>
            <div class="p-6 rounded-2xl bg-white border border-slate-200 text-center shadow-2xs">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xl mx-auto mb-2">
                    <i class="fa-solid fa-inbox"></i>
                </div>
                <p class="text-xs font-semibold text-slate-600">Belum ada riwayat pengajuan cuti.</p>
                <a href="<?= BASE_URL ?>/index.php?page=leave-create" class="inline-block mt-3 px-4 py-1.5 rounded-xl bg-rose-600 text-white text-xs font-bold shadow-sm">
                    Ajukan Cuti Sekarang
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-2.5">
                <?php foreach ($myRecentLeaves as $leave): 
                    $status = strtolower($leave['status'] ?? 'pending');
                    $step = $leave['approval_step'] ?? 'pending_spv';
                    
                    if ($status === 'approved') {
                        $badgeBg = 'bg-emerald-100 text-emerald-800';
                        $badgeLabel = 'Disetujui';
                    } elseif ($status === 'rejected') {
                        $badgeBg = 'bg-rose-100 text-rose-800';
                        $badgeLabel = 'Ditolak';
                    } elseif ($status === 'cancelled') {
                        $badgeBg = 'bg-slate-100 text-slate-700';
                        $badgeLabel = 'Dibatalkan';
                    } else {
                        $badgeBg = 'bg-amber-100 text-amber-800';
                        if ($step === 'pending_spv') $badgeLabel = 'Review Spv';
                        elseif ($step === 'pending_manager') $badgeLabel = 'Review Manager';
                        elseif ($step === 'pending_hrd') $badgeLabel = 'Review HRD';
                        else $badgeLabel = 'Menunggu';
                    }
                ?>
                    <a href="<?= BASE_URL ?>/index.php?page=leave-detail&id=<?= $leave['id'] ?>" 
                       class="block p-3.5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:shadow-sm active:scale-[0.99] transition">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base flex-shrink-0 mt-0.5">
                                <i class="fa-solid fa-calendar-days"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <h4 class="text-xs font-bold text-slate-900 truncate">
                                        <?= htmlspecialchars($leave['nama_cuti'] ?? 'Cuti') ?>
                                    </h4>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $badgeBg ?> flex-shrink-0">
                                        <?= $badgeLabel ?>
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-500 font-medium mt-1">
                                    <?= date('d M Y', strtotime($leave['tanggal_mulai'])) ?> s/d <?= date('d M Y', strtotime($leave['tanggal_selesai'])) ?> (<?= $leave['total_hari'] ?> hari)
                                </div>
                                <p class="text-[11px] text-slate-400 truncate mt-0.5">
                                    <?= htmlspecialchars($leave['alasan']) ?>
                                </p>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ========================================================================= -->
<!-- 2. DESKTOP VIEW (Screen >= 1024px) - FULL SPATIOUS BENTO & ANALYTICS GRID -->
<!-- ========================================================================= -->
<div class="hidden lg:block space-y-6">

    <!-- Hero Bento Card (Desktop) -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#090e1a] via-[#1e1b4b] to-[#1e3a8a] text-white p-8 shadow-xl border border-slate-800">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-rose-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/10 text-xs font-semibold text-blue-200">
                    <i class="fa-solid fa-building text-rose-400"></i>
                    <span><?= htmlspecialchars($currentUser['nama_dept']) ?></span>
                    <span class="text-white/40">&bull;</span>
                    <span><?= htmlspecialchars($currentUser['nama_jabatan']) ?></span>
                    <span class="text-white/40">&bull;</span>
                    <span class="text-amber-300 font-bold"><?= $currentShiftText ?></span>
                </div>
                <h2 class="text-3xl font-black font-display tracking-tight text-white">
                    Selamat Datang, <?= htmlspecialchars($currentUser['nama_lengkap']) ?>! 👋
                </h2>
                <p class="text-slate-300 text-sm max-w-xl font-medium leading-relaxed">
                    <?= htmlspecialchars($appSettings['nama_aplikasi']) ?> <?= htmlspecialchars($appSettings['nama_perusahaan']) ?>. Pantau jatah kuota cuti tahunan dan ajukan permohonan izin dengan cepat dan mudah.
                </p>
            </div>
            
            <div class="flex-shrink-0">
                <a href="<?= BASE_URL ?>/index.php?page=leave-create" 
                   class="inline-flex items-center gap-2 px-6 py-3.5 rounded-2xl bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-extrabold text-sm shadow-lg shadow-rose-600/40 hover:shadow-rose-600/60 transform hover:-translate-y-0.5 active:translate-y-0 transition duration-200">
                    <i class="fa-solid fa-calendar-plus text-base"></i>
                    <span>Ajukan Cuti Baru</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Personal Stats Bento Grid (Desktop) -->
    <div class="grid grid-cols-4 gap-4">
        
        <!-- Sisa Kuota Cuti -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-soft hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Sisa Kuota Cuti</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight">
                <?= (int)$currentUser['sisa_cuti'] ?> <span class="text-sm font-semibold text-slate-400">Hari</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs text-slate-500 font-medium pt-2 border-t border-slate-100">
                <span>Total: <?= (int)$currentUser['kuota_cuti'] ?> Hari</span>
                <span class="text-emerald-600 font-bold">Siap Pakai</span>
            </div>
        </div>

        <!-- Cuti Terpakai -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-soft hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Cuti Terpakai</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                    <i class="fa-solid fa-plane-departure"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight">
                <?= (int)$currentUser['cuti_terpakai'] ?> <span class="text-sm font-semibold text-slate-400">Hari</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs text-slate-500 font-medium pt-2 border-t border-slate-100">
                <span>Periode: <?= date('Y') ?></span>
                <span class="text-amber-600 font-bold"><?= (int)$currentUser['cuti_terpakai'] ?> hari dipakai</span>
            </div>
        </div>

        <!-- Masa Kerja -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-soft hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Masa Kerja</span>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
            </div>
            <div class="text-lg font-black text-purple-700 leading-snug truncate" title="<?= hitungMasaKerja($currentUser['tanggal_masuk']) ?>">
                <?= hitungMasaKerja($currentUser['tanggal_masuk']) ?>
            </div>
            <div class="mt-2 text-xs text-slate-500 font-medium pt-2 border-t border-slate-100 truncate">
                Join: <?= formatTanggalIndo($currentUser['tanggal_masuk']) ?>
            </div>
        </div>

        <!-- Total Pengajuan -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-soft hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Pengajuan</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight">
                <?= (int)$myStats['total_pengajuan'] ?> <span class="text-sm font-semibold text-slate-400">Kali</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs text-slate-500 font-medium pt-2 border-t border-slate-100">
                <span class="text-emerald-600 font-bold"><?= (int)$myStats['total_approved'] ?> Disetujui</span>
                <span class="text-amber-600 font-bold"><?= (int)$myStats['total_pending'] ?> Pending</span>
            </div>
        </div>
    </div>

    <!-- HRD Quick Metrics (If HRD / Super Admin) -->
    <?php if ($isHRD): ?>
        <div class="grid grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-4 border-l-4 border-rose-600 border-y border-r border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase">Total Karyawan</div>
                    <div class="text-xl font-black text-slate-900"><?= $totalKaryawan ?> Orang</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-4 border-l-4 border-blue-600 border-y border-r border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase">Pengajuan Bulan Ini</div>
                    <div class="text-xl font-black text-blue-600"><?= $totalCutiBulanIni ?> Pengajuan</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-file-signature"></i>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-4 border-l-4 border-amber-500 border-y border-r border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase">Menunggu Approval</div>
                    <div class="text-xl font-black text-amber-600"><?= $totalPendingAll ?> Pengajuan</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="fa-solid fa-hourglass-start"></i>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-4 border-l-4 border-emerald-600 border-y border-r border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase">Sedang Cuti Hari Ini</div>
                    <div class="text-xl font-black text-emerald-600"><?= $cutiHariIni ?> Orang</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Approver Monitoring Table Card (Desktop) -->
    <?php if ($isApprover): ?>
        <div class="bg-white rounded-3xl border border-amber-200 shadow-soft overflow-hidden">
            <div class="p-5 px-6 bg-gradient-to-r from-amber-50/80 to-orange-50/80 border-b border-amber-200/80 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-sm">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">
                            <?= $isHRD ? 'Pengajuan Cuti Menunggu Persetujuan & Monitoring' : ($isManager ? 'Pengajuan Cuti Menunggu Review Plant Manager' : 'Pengajuan Cuti Menunggu Persetujuan Anda') ?>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            <?= $isHRD ? 'Antrean pengajuan cuti dari seluruh 15 departemen' : ($isManager ? 'Seluruh departemen perusahaan' : 'Khusus anggota tim Departemen ' . htmlspecialchars($currentUser['nama_dept'] ?? '')) ?>
                        </p>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/index.php?page=leave-approvals" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-xs font-bold text-slate-700 shadow-sm transition">
                    Lihat Semua (<?= count($deptPendingLeaves) ?>) &rarr;
                </a>
            </div>

            <div class="p-0">
                <?php if (empty($deptPendingLeaves)): ?>
                    <div class="text-center py-8 text-slate-400">
                        <i class="fa-solid fa-circle-check text-emerald-500 text-3xl mb-2 block"></i>
                        <p class="text-xs font-semibold text-slate-600">Semua permohonan cuti sudah diproses!</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-extrabold uppercase tracking-wider border-b border-slate-100">
                                <tr>
                                    <th class="px-5 py-3.5">Karyawan</th>
                                    <th class="px-4 py-3.5">Departemen</th>
                                    <th class="px-4 py-3.5">Jenis Cuti</th>
                                    <th class="px-4 py-3.5">Tanggal</th>
                                    <th class="px-4 py-3.5">Tahapan / Status</th>
                                    <th class="px-5 py-3.5 text-left">Aksi Persetujuan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($deptPendingLeaves as $req): 
                                    $step = $req['approval_step'] ?? 'pending_spv';
                                    $canApproveRow = false;
                                    $stepBadge = '';

                                    if ($req['status'] === 'pending') {
                                        if ($isHRD && $step === 'pending_hrd') {
                                            $canApproveRow = true;
                                        } elseif ($isManager && $step === 'pending_manager') {
                                            $canApproveRow = true;
                                        } elseif ($isSpv && $step === 'pending_spv' && (int)$req['departemen_id'] === (int)$deptId && (int)$req['employee_id'] !== (int)$userId) {
                                            $canApproveRow = true;
                                        }

                                        if ($step === 'pending_spv') {
                                            $stepBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold"><i class="fa-solid fa-clock"></i> Review Leader</span>';
                                        } elseif ($step === 'pending_manager') {
                                            $stepBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-bold"><i class="fa-solid fa-clock"></i> Review Manager</span>';
                                        } elseif ($step === 'pending_hrd') {
                                            $stepBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 border border-purple-200 text-[10px] font-bold"><i class="fa-solid fa-clock"></i> Menunggu HRD</span>';
                                        }
                                    } else {
                                        $stepBadge = getStatusBadge($req['status']);
                                    }
                                ?>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 font-extrabold flex items-center justify-center text-xs">
                                                    <?= strtoupper(substr($req['nama_lengkap'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-900"><?= htmlspecialchars($req['nama_lengkap']) ?></div>
                                                    <div class="text-[11px] text-slate-400">NIK: <?= htmlspecialchars($req['nik']) ?> &bull; <?= htmlspecialchars($req['nama_jabatan']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div class="font-extrabold text-slate-900 text-xs"><?= htmlspecialchars($req['nama_dept']) ?></div>
                                        </td>
                                        <td class="px-4 py-3.5 whitespace-nowrap">
                                            <?= renderLeaveTypeDisplay($req['nama_cuti'], $req['potong_kuota'] ?? null) ?>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div class="font-semibold text-slate-700"><?= formatTanggalIndo($req['tanggal_mulai']) ?></div>
                                            <div class="text-blue-600 font-bold"><?= $req['total_hari'] ?> Hari Kerja</div>
                                        </td>
                                        <td class="px-4 py-3.5 whitespace-nowrap">
                                            <?= $stepBadge ?>
                                        </td>
                                        <td class="px-5 py-3.5 whitespace-nowrap">
                                            <div class="flex items-center gap-1.5">
                                                <?php if ($canApproveRow): ?>
                                                    <button type="button" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1" onclick="confirmApprove(<?= $req['id'] ?>, '<?= addslashes(htmlspecialchars($req['nama_lengkap'])) ?>')">
                                                        <i class="fa-solid fa-check"></i> Setuju
                                                    </button>
                                                    <button type="button" class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1" onclick="confirmReject(<?= $req['id'] ?>, '<?= addslashes(htmlspecialchars($req['nama_lengkap'])) ?>')">
                                                        <i class="fa-solid fa-xmark"></i> Tolak
                                                    </button>
                                                <?php elseif ($req['status'] === 'pending'): ?>
                                                    <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-500 font-bold text-[11px] border border-slate-200 flex items-center gap-1" title="Menunggu giliran approval">
                                                        <i class="fa-solid fa-hourglass-start text-slate-400"></i> Pantau
                                                    </span>
                                                <?php endif; ?>
                                                <a href="<?= BASE_URL ?>/index.php?page=leave-detail&id=<?= $req['id'] ?>" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition flex items-center justify-center shadow-2xs" title="Lihat Detail">
                                                    <i class="fa-solid fa-eye text-xs"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Two Columns: Department Chart & Personal Leave History (Desktop) -->
    <div class="grid grid-cols-<?= $isHRD ? '2' : '1' ?> gap-6">
        
        <?php if ($isHRD): ?>
            <!-- Department Leaves Chart -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-chart-column text-blue-600"></i> Pengajuan Cuti per Departemen
                    </h3>
                    <span class="text-xs font-semibold text-slate-400">Tahun <?= date('Y') ?></span>
                </div>
                <div class="flex-1 flex items-center justify-center">
                    <canvas id="deptLeaveChart" height="200"></canvas>
                </div>
            </div>
        <?php endif; ?>

        <!-- My Recent Leaves (Desktop) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="p-5 px-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-blue-600"></i> Riwayat Pengajuan Cuti Saya
                </h3>
                <a href="<?= BASE_URL ?>/index.php?page=leaves-my" class="text-xs font-bold text-blue-600 hover:text-blue-700">Lihat Semua &rarr;</a>
            </div>

            <div class="p-0">
                <?php if (empty($myRecentLeaves)): ?>
                    <div class="text-center py-8 text-slate-400">
                        <i class="fa-solid fa-calendar-xmark text-3xl mb-2 block"></i>
                        <p class="text-xs font-medium">Anda belum pernah mengajukan cuti.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-extrabold uppercase tracking-wider border-b border-slate-100">
                                <tr>
                                    <th class="px-5 py-3">No. Surat</th>
                                    <th class="px-4 py-3">Jenis Cuti</th>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-5 py-3 text-left">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($myRecentLeaves as $leave): ?>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="px-5 py-3 font-mono font-bold text-slate-900"><?= htmlspecialchars($leave['nomor_surat']) ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap"><?= renderLeaveTypeDisplay($leave['nama_cuti'], $leave['potong_kuota'] ?? null) ?></td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-slate-800"><?= formatTanggalIndo($leave['tanggal_mulai']) ?></div>
                                            <div class="text-[11px] text-slate-400"><?= $leave['total_hari'] ?> Hari</div>
                                        </td>
                                        <td class="px-4 py-3"><?= getStatusBadge($leave['status']) ?></td>
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <div class="flex items-center gap-1.5">
                                                <a href="<?= BASE_URL ?>/index.php?page=leave-detail&id=<?= $leave['id'] ?>" class="w-8 h-8 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold flex items-center justify-center transition shadow-2xs" title="Lihat Detail">
                                                    <i class="fa-solid fa-eye text-xs"></i>
                                                </a>
                                                <?php if ($leave['status'] === 'approved'): ?>
                                                    <a href="<?= BASE_URL ?>/index.php?page=leave-print&id=<?= $leave['id'] ?>" target="_blank" class="w-8 h-8 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-600 font-bold flex items-center justify-center transition shadow-2xs" title="Cetak Surat">
                                                        <i class="fa-solid fa-print text-xs"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php if ($userRole === 'admin' && !empty($chartDeptData)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('deptLeaveChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($chartDeptData, 'nama_dept')) ?>,
                datasets: [{
                    label: 'Cuti Disetujui',
                    data: <?= json_encode(array_column($chartDeptData, 'total_cuti')) ?>,
                    backgroundColor: '#3b82f6',
                    hoverBackgroundColor: '#2563eb',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
