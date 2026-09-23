<?php
/**
 * Sidebar Layout (Tailwind CSS Edition with Mini Collapsed Mode)
 * Dynamic White-label Ready Edition
 */

$currentPage = $_GET['page'] ?? 'dashboard';
$appSettings = getAppSettings();
$sidebarLogoUrl = !empty($appSettings['logo']) ? BASE_URL . '/assets/images/' . $appSettings['logo'] : BASE_URL . '/assets/images/Nakakin.png';
?>

<aside id="mainSidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-[#090e1a] text-slate-300 flex flex-col border-r border-slate-800/80 shadow-2xl transition-all duration-300 transform -translate-x-full lg:translate-x-0 no-print overflow-x-hidden">
    
    <!-- Ultra Modern Brand Header -->
    <div class="sidebar-brand px-4 sm:px-5 py-4 flex items-center justify-between border-b border-slate-800/80 bg-gradient-to-r from-slate-900/95 via-slate-900/60 to-slate-950/95 relative overflow-hidden transition-all">
        <!-- Subtle Ambient Glow in background -->
        <div class="absolute -top-6 -left-6 w-24 h-24 bg-rose-600/15 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-blue-600/10 rounded-full blur-2xl pointer-events-none"></div>

        <a href="<?= BASE_URL ?>/index.php?page=dashboard" class="sidebar-text flex items-center gap-3 group relative z-10">
            <!-- Sleek Stylized Tech Icon / Logo -->
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-rose-500 via-red-600 to-rose-700 p-0.5 shadow-lg shadow-rose-600/25 group-hover:shadow-rose-600/40 group-hover:scale-105 transition-all duration-300 flex items-center justify-center flex-shrink-0">
                <div class="w-full h-full bg-[#0a0f1d]/90 rounded-[14px] flex items-center justify-center backdrop-blur-sm p-1 overflow-hidden">
                    <img src="<?= $sidebarLogoUrl ?>" alt="Logo" class="max-h-full max-w-full object-contain">
                </div>
            </div>

            <!-- Typography & Futuristic Badge -->
            <div class="overflow-hidden">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="font-display font-black text-white text-[14px] tracking-wide uppercase leading-tight group-hover:text-rose-200 transition truncate max-w-[110px]" title="<?= htmlspecialchars($appSettings['nama_perusahaan']) ?>">
                        <?= htmlspecialchars($appSettings['singkatan_perusahaan'] ?: 'NAKAKIN') ?>
                    </span>
                    <?php if (!empty($appSettings['sub_singkatan_perusahaan'])): ?>
                        <span class="text-[8.5px] font-black px-1.5 py-0.2 rounded-md bg-rose-950/90 text-rose-400 border border-rose-800/60 uppercase tracking-widest flex-shrink-0">
                            <?= htmlspecialchars($appSettings['sub_singkatan_perusahaan']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-1.5 mt-1">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-gradient-to-r from-rose-500/20 via-red-500/10 to-transparent border border-rose-500/35 text-[9.5px] font-black text-rose-300 tracking-wider uppercase shadow-xs truncate">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse flex-shrink-0"></span>
                        <span class="truncate"><?= htmlspecialchars($appSettings['singkatan_aplikasi'] ?: 'FORM CUTI ONLINE') ?></span>
                    </span>
                </div>
            </div>
        </a>

        <button type="button" id="closeSidebarBtn" class="lg:hidden text-slate-400 hover:text-white p-1.5 rounded-xl hover:bg-slate-800/80 transition relative z-10">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>

    <!-- Active User Profile Card -->
    <div class="sidebar-profile-card mx-3 my-2 p-3 rounded-2xl bg-gradient-to-b from-slate-800/60 to-slate-900/60 border border-slate-700/40 relative backdrop-blur-md shadow-sm transition-all flex flex-col justify-center">
        <div class="sidebar-profile-flex flex items-center gap-3">
            <a href="<?= BASE_URL ?>/index.php?page=profile" 
               class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-extrabold text-sm flex items-center justify-center shadow-md flex-shrink-0 hover:ring-2 hover:ring-blue-400 transition" 
               title="<?= htmlspecialchars($currentUser['nama_lengkap'] ?? '') ?>">
                <?php if (!empty($currentUser['foto']) && file_exists(__DIR__ . '/../../uploads/' . $currentUser['foto'])): ?>
                    <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($currentUser['foto']) ?>" alt="Foto" class="w-full h-full object-cover rounded-xl">
                <?php else: ?>
                    <?= strtoupper(substr($currentUser['nama_lengkap'] ?? 'N', 0, 1)) ?>
                <?php endif; ?>
            </a>
            <div class="sidebar-text overflow-hidden transition-opacity duration-200">
                <a href="<?= BASE_URL ?>/index.php?page=profile" class="block font-bold text-white text-xs truncate hover:text-blue-300" title="<?= htmlspecialchars($currentUser['nama_lengkap'] ?? '') ?>">
                    <?= htmlspecialchars($currentUser['nama_lengkap'] ?? '') ?>
                </a>
                <div class="text-[11px] font-semibold text-blue-400 flex items-center gap-1 truncate mt-0.5">
                    <i class="fa-solid fa-briefcase text-[10px]"></i> <?= htmlspecialchars($currentUser['nama_jabatan'] ?? 'Karyawan') ?>
                </div>
                <div class="text-[10px] text-slate-400 truncate mt-0.5">
                    <?= htmlspecialchars($currentUser['nama_dept'] ?? '-') ?> &bull; <span class="text-slate-300 font-mono"><?= htmlspecialchars($currentUser['nik'] ?? '') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-2.5 space-y-1 overflow-y-auto pb-6 custom-scrollbar">
        
        <div class="sidebar-text px-3 pt-2 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Menu Utama</div>
        <div class="sidebar-section-divider hidden border-t border-slate-800 my-2"></div>
        <a href="<?= BASE_URL ?>/index.php?page=dashboard" 
           class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition group <?= $currentPage === 'dashboard' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-600/30 font-bold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' ?>"
           title="Dashboard">
            <i class="fa-solid fa-gauge-high text-sm w-5 text-center flex-shrink-0 <?= $currentPage === 'dashboard' ? 'text-white' : 'text-slate-500 group-hover:text-blue-400' ?>"></i>
            <span class="sidebar-text">Dashboard</span>
        </a>
        <a href="<?= BASE_URL ?>/index.php?page=home" target="_blank"
           class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition group text-slate-400 hover:bg-slate-800/60 hover:text-white"
           title="Buka Papan Kehadiran Publik (Live Board)">
            <i class="fa-solid fa-tv text-sm w-5 text-center flex-shrink-0 text-slate-500 group-hover:text-amber-400"></i>
            <span class="sidebar-text">Papan Kehadiran Live</span>
        </a>

        <div class="sidebar-text px-3 pt-3 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Layanan Cuti</div>
        <div class="sidebar-section-divider hidden border-t border-slate-800 my-2"></div>
        <a href="<?= BASE_URL ?>/index.php?page=leave-create" 
           class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition group <?= $currentPage === 'leave-create' ? 'bg-gradient-to-r from-rose-600 to-red-600 text-white shadow-lg shadow-rose-600/30 font-bold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' ?>"
           title="Ajukan Cuti Baru">
            <i class="fa-solid fa-calendar-plus text-sm w-5 text-center flex-shrink-0 <?= $currentPage === 'leave-create' ? 'text-white' : 'text-slate-500 group-hover:text-rose-400' ?>"></i>
            <span class="sidebar-text">Ajukan Cuti Baru</span>
        </a>
        <a href="<?= BASE_URL ?>/index.php?page=leaves-my" 
           class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition group <?= in_array($currentPage, ['leaves-my', 'leave-detail']) ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-600/30 font-bold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' ?>"
           title="Riwayat Cuti Saya">
            <i class="fa-solid fa-clock-rotate-left text-sm w-5 text-center flex-shrink-0 <?= in_array($currentPage, ['leaves-my', 'leave-detail']) ? 'text-white' : 'text-slate-500 group-hover:text-blue-400' ?>"></i>
            <span class="sidebar-text">Riwayat Cuti Saya</span>
        </a>

        <!-- Menu for Atasan & Admin -->
        <?php if (in_array($currentUser['role'] ?? '', ['atasan', 'admin'])): ?>
            <div class="sidebar-text px-3 pt-3 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Persetujuan (Approval)</div>
            <div class="sidebar-section-divider hidden border-t border-slate-800 my-2"></div>
            <a href="<?= BASE_URL ?>/index.php?page=leave-approvals" 
               class="sidebar-nav-item flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition group <?= $currentPage === 'leave-approvals' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-600/30 font-bold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' ?>"
               title="Approval Cuti Tim">
                <div class="flex items-center gap-3 min-w-0">
                    <i class="fa-solid fa-clipboard-check text-sm w-5 text-center flex-shrink-0 <?= $currentPage === 'leave-approvals' ? 'text-white' : 'text-slate-500 group-hover:text-blue-400' ?>"></i>
                    <span class="sidebar-text truncate">Approval Cuti Tim</span>
                </div>
                <?php if ($pendingApprovalCount > 0): ?>
                    <span class="sidebar-text px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500 text-white shadow-sm flex-shrink-0">
                        <?= $pendingApprovalCount ?>
                    </span>
                <?php endif; ?>
            </a>
            <a href="<?= BASE_URL ?>/index.php?page=leaves-team" 
               class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition group <?= $currentPage === 'leaves-team' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-600/30 font-bold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' ?>"
               title="Cuti Departemen">
                <i class="fa-solid fa-users-viewfinder text-sm w-5 text-center flex-shrink-0 <?= $currentPage === 'leaves-team' ? 'text-white' : 'text-slate-500 group-hover:text-blue-400' ?>"></i>
                <span class="sidebar-text">Cuti Departemen</span>
            </a>
        <?php endif; ?>

        <!-- Menu for HRD Only -->
        <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
            <div class="sidebar-text px-3 pt-3 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Manajemen HRD</div>
            <div class="sidebar-section-divider hidden border-t border-slate-800 my-2"></div>
            <a href="<?= BASE_URL ?>/index.php?page=quotas" 
               class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition group <?= $currentPage === 'quotas' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-600/30 font-bold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' ?>"
               title="Kelola Jatah Cuti">
                <i class="fa-solid fa-gift text-sm w-5 text-center flex-shrink-0 <?= $currentPage === 'quotas' ? 'text-white' : 'text-slate-500 group-hover:text-blue-400' ?>"></i>
                <span class="sidebar-text">Kelola Jatah Cuti</span>
            </a>
            <a href="<?= BASE_URL ?>/index.php?page=employees" 
               class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition group <?= in_array($currentPage, ['employees', 'employee-create', 'employee-edit']) ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-600/30 font-bold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' ?>"
               title="Data Karyawan">
                <i class="fa-solid fa-id-card-clip text-sm w-5 text-center flex-shrink-0 <?= in_array($currentPage, ['employees', 'employee-create', 'employee-edit']) ? 'text-white' : 'text-slate-500 group-hover:text-blue-400' ?>"></i>
                <span class="sidebar-text">Data Karyawan</span>
            </a>
            <a href="<?= BASE_URL ?>/index.php?page=reports" 
               class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition group <?= $currentPage === 'reports' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-600/30 font-bold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' ?>"
               title="Laporan & Rekap Cuti">
                <i class="fa-solid fa-chart-pie text-sm w-5 text-center flex-shrink-0 <?= $currentPage === 'reports' ? 'text-white' : 'text-slate-500 group-hover:text-blue-400' ?>"></i>
                <span class="sidebar-text">Laporan & Rekap Cuti</span>
            </a>
            <a href="<?= BASE_URL ?>/index.php?page=settings" 
               class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition group <?= $currentPage === 'settings' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-600/30 font-bold' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' ?>"
               title="Pengaturan Aplikasi & Perusahaan">
                <i class="fa-solid fa-sliders text-sm w-5 text-center flex-shrink-0 <?= $currentPage === 'settings' ? 'text-white' : 'text-slate-500 group-hover:text-indigo-400' ?>"></i>
                <span class="sidebar-text">Pengaturan Aplikasi</span>
            </a>
        <?php endif; ?>

    </nav>
</aside>
