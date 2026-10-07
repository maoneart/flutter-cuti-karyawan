<?php
/**
 * Master Data Employees View (Tailwind CSS Edition)
 * PT. Nakakin Indonesia Leave Management System
 */

$pageTitle = 'Master Data Karyawan';
require_once __DIR__ . '/../layouts/header.php';

$userRole = strtolower($currentUser['role'] ?? '');
$userLevel = (int)($currentUser['level_hierarki'] ?? 1);
$isHrdOrAdmin = in_array($userRole, ['superadmin', 'admin', 'hrd']) || $userLevel >= 7;
$isManager = ($userRole === 'manager' || ($userLevel >= 5 && $userLevel <= 6));
$canManageEmployees = $isHrdOrAdmin;

$pdo = getDbConnection();
$deptFilter = (int)($_GET['dept_id'] ?? 0);
$departments = $pdo->query("SELECT * FROM departemen ORDER BY nama_dept ASC")->fetchAll();

$conditions = ["1=1"];
$params = [];

// Access control: HRD, Super Admin, Manager can see all departments.
// Leader, Spv, Staff, Operator only see their own department.
if (!$isHrdOrAdmin && !$isManager) {
    $conditions[] = "e.departemen_id = ?";
    $params[] = (int)($currentUser['departemen_id'] ?? 0);
} else {
    if ($deptFilter > 0) {
        $conditions[] = "e.departemen_id = ?";
        $params[] = $deptFilter;
    }
}

$whereClause = implode(" AND ", $conditions);

$sql = "
    SELECT e.*, d.nama_dept, d.kode_dept, p.nama_jabatan, p.level_hierarki
    FROM karyawan e
    JOIN departemen d ON e.departemen_id = d.id
    JOIN jabatan p ON e.jabatan_id = p.id
    WHERE $whereClause
    ORDER BY d.nama_dept ASC, p.level_hierarki DESC, e.nama_lengkap ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$employees = $stmt->fetchAll();
?>

<div class="space-y-4 lg:space-y-6">

    <!-- =========================================================
         1. MOBILE VIEW (100% PARITY WITH FLUTTER EmployeeListScreen)
         ========================================================= -->
    <div class="block lg:hidden space-y-3.5">
        <?php if ($canManageEmployees): ?>
            <!-- Mobile Action Header: + Tambah Karyawan & Import & Template -->
            <div class="space-y-2">
                <a href="<?= BASE_URL ?>/index.php?page=employee-create" 
                   class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-extrabold text-xs shadow-md shadow-blue-600/25 flex items-center justify-center gap-2 transition active:scale-98">
                    <i class="fa-solid fa-user-plus text-sm"></i>
                    <span>+ Tambah Karyawan Baru</span>
                </a>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="openImportModal()" 
                            class="py-2 px-3 rounded-xl border border-emerald-500/40 dark:border-emerald-500/60 bg-emerald-50/80 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 font-bold text-xs flex items-center justify-center gap-1.5 transition active:scale-98">
                        <i class="fa-solid fa-file-arrow-up text-emerald-600 dark:text-emerald-400"></i> Import Excel
                    </button>
                    <a href="<?= BASE_URL ?>/index.php?page=employee-template&format=xlsx" 
                        class="py-2 px-3 rounded-xl border border-blue-400/40 dark:border-sky-500/60 bg-blue-50/80 dark:bg-sky-950/60 hover:bg-blue-100 dark:hover:bg-sky-900/60 text-blue-700 dark:text-sky-300 font-bold text-xs flex items-center justify-center gap-1.5 transition text-center active:scale-98">
                        <i class="fa-solid fa-file-excel text-blue-600 dark:text-sky-400"></i> Template (.xlsx)
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Mobile Live Search Box -->
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" id="mobileEmpSearch" placeholder="Cari nama, NIK, email, jabatan..." 
                   oninput="filterMobileEmployees(this.value)"
                   class="w-full pl-9 pr-9 py-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-800 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-blue-500 shadow-2xs transition">
            <button type="button" id="clearEmpSearchBtn" onclick="clearEmpSearch()" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-circle-xmark text-xs"></i>
            </button>
        </div>

        <!-- Horizontal Scrollable Department Filter Pills (Flutter FilterChips) -->
        <?php if ($isHrdOrAdmin || $isManager): ?>
            <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar text-xs">
                <button type="button" onclick="filterByDept(this, '')" 
                        class="dept-chip px-3 py-1.5 rounded-full font-bold whitespace-nowrap transition bg-blue-600 text-white shadow-2xs">
                    Semua Dept
                </button>
                <?php foreach ($departments as $dept): ?>
                    <button type="button" onclick="filterByDept(this, '<?= strtolower(htmlspecialchars($dept['nama_dept'])) ?>')" 
                            class="dept-chip px-3 py-1.5 rounded-full font-semibold whitespace-nowrap transition bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700 shadow-2xs">
                        <?= htmlspecialchars($dept['nama_dept']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="flex items-center justify-between px-3 py-2 rounded-2xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900 text-xs">
                <span class="font-bold text-blue-900 dark:text-blue-300 flex items-center gap-1.5">
                    <i class="fa-solid fa-building text-blue-600 dark:text-blue-400"></i> Departemen: <?= htmlspecialchars($currentUser['nama_dept'] ?? '-') ?>
                </span>
                <span class="px-2 py-0.5 rounded-full bg-blue-600 text-white font-extrabold text-[10.5px]">
                    <?= count($employees) ?> Orang
                </span>
            </div>
        <?php endif; ?>

        <!-- Employee Cards List (Flutter _buildEmployeeCard parity) -->
        <div id="mobileEmployeeContainer" class="space-y-2.5 pt-1">
            <?php foreach ($employees as $emp): 
                $sisa = (float)$emp['sisa_cuti'];
                $roleName = strtolower($emp['role']);
                $avatarBg = 'bg-blue-100 text-blue-700 dark:bg-blue-950/80 dark:text-blue-300';
                $pillBg = 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800';
                if ($roleName === 'superadmin') {
                    $avatarBg = 'bg-purple-100 text-purple-700 dark:bg-purple-950/80 dark:text-purple-300';
                    $pillBg = 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800';
                } elseif ($roleName === 'manager') {
                    $avatarBg = 'bg-amber-100 text-amber-700 dark:bg-amber-950/80 dark:text-amber-300';
                    $pillBg = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800';
                } elseif (in_array($roleName, ['leader', 'supervisor'])) {
                    $avatarBg = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300';
                    $pillBg = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800';
                } elseif ($roleName === 'staff') {
                    $avatarBg = 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/80 dark:text-indigo-300';
                    $pillBg = 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800';
                }
            ?>
                <div class="emp-mobile-card bg-white dark:bg-slate-800/95 p-3.5 rounded-2xl border border-slate-200/90 dark:border-slate-700 shadow-2xs transition active:scale-[0.99]"
                     data-name="<?= strtolower(htmlspecialchars($emp['nama_lengkap'])) ?>"
                     data-nik="<?= strtolower(htmlspecialchars($emp['nik'])) ?>"
                     data-email="<?= strtolower(htmlspecialchars($emp['email'])) ?>"
                     data-dept="<?= strtolower(htmlspecialchars($emp['nama_dept'])) ?>"
                     data-pos="<?= strtolower(htmlspecialchars($emp['nama_jabatan'])) ?>">
                    
                    <div class="flex items-start gap-3">
                        <!-- Avatar -->
                        <div class="w-11 h-11 rounded-full <?= $avatarBg ?> font-black text-base flex items-center justify-center flex-shrink-0 shadow-2xs">
                            <?= strtoupper(substr($emp['nama_lengkap'], 0, 1)) ?>
                        </div>

                        <!-- Employee Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-1.5">
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-tight truncate"><?= htmlspecialchars($emp['nama_lengkap']) ?></h4>
                                <span class="px-2 py-0.5 rounded-md text-[10.5px] font-bold border <?= $pillBg ?> flex-shrink-0 truncate max-w-[120px]">
                                    <?= htmlspecialchars($emp['nama_jabatan']) ?>
                                </span>
                            </div>
                            
                            <p class="text-[11.5px] text-slate-400 dark:text-slate-400 mt-0.5 font-medium">
                                <?= htmlspecialchars($emp['nik']) ?> &bull; Dept: <span class="text-slate-600 dark:text-slate-300 font-semibold"><?= htmlspecialchars($emp['nama_dept']) ?></span>
                            </p>

                            <!-- Bottom Info: Sisa Cuti & No HP -->
                            <div class="flex items-center gap-3 mt-2 text-[11px]">
                                <span class="inline-flex items-center gap-1 font-bold <?= $sisa > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' ?>">
                                    <i class="fa-solid fa-chart-pie text-[10px]"></i> Sisa Cuti: <?= $sisa ?> Hari
                                </span>
                                <?php if (!empty($emp['no_hp'])): ?>
                                    <a href="https://wa.me/<?= preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $emp['no_hp'])) ?>" 
                                       target="_blank" 
                                       class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 font-bold inline-flex items-center gap-1">
                                        <i class="fa-brands fa-whatsapp text-[11px]"></i> <?= htmlspecialchars($emp['no_hp']) ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Quick Action Row -->
                    <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-700/80 flex items-center justify-between gap-2">
                        <span class="text-[10.5px] text-slate-400 font-medium">
                            Masa Kerja: <strong class="text-slate-700 dark:text-slate-300"><?= hitungMasaKerja($emp['tanggal_masuk']) ?></strong>
                        </span>
                        <?php if ($canManageEmployees): ?>
                            <div class="flex items-center gap-1.5">
                                <a href="<?= BASE_URL ?>/index.php?page=employee-edit&id=<?= $emp['id'] ?>" 
                                   class="px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 dark:hover:bg-blue-900/60 text-blue-600 dark:text-blue-400 font-bold text-[11px] transition flex items-center gap-1 border border-transparent dark:border-blue-900">
                                    <i class="fa-solid fa-pen-to-square text-[10px]"></i> Edit
                                </a>
                                <?php if ($emp['id'] != $currentUser['id']): ?>
                                    <button type="button" 
                                            class="px-2.5 py-1 rounded-lg bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 font-bold text-[11px] transition flex items-center gap-1 border border-transparent dark:border-rose-900" 
                                            onclick="confirmDelete('<?= BASE_URL ?>/index.php?page=employee-delete&id=<?= $emp['id'] ?>', 'Hapus karyawan <?= addslashes(htmlspecialchars($emp['nama_lengkap'])) ?>?')">
                                        <i class="fa-solid fa-trash text-[10px]"></i> Hapus
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($canManageEmployees): ?>
            <!-- Floating Mobile Add Employee Button -->
            <div class="pt-2">
                <a href="<?= BASE_URL ?>/index.php?page=employee-create" 
                   class="w-full py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-extrabold text-xs shadow-md shadow-blue-600/25 flex items-center justify-center gap-2 transition">
                    <i class="fa-solid fa-user-plus text-sm"></i> Tambah Karyawan Baru
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- =========================================================
         2. DESKTOP VIEW (DATATABLE FOR WIDE SCREENS >= 1024px)
         ========================================================= -->
    <div class="hidden lg:block space-y-6">
        
        <!-- Desktop Metrics Bento Ribbon -->
        <?php
        $activeEmpCount = count(array_filter($employees, fn($e) => ($e['status_aktif'] ?? '') === 'Aktif'));
        $totalQuota = array_sum(array_column($employees, 'sisa_cuti'));
        ?>
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold border border-blue-100 shadow-2xs">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Karyawan</div>
                    <div class="text-xl font-black text-slate-900 leading-tight"><?= count($employees) ?> <span class="text-xs font-semibold text-slate-500">Orang</span></div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold border border-emerald-100 shadow-2xs">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status Aktif</div>
                    <div class="text-xl font-black text-emerald-600 leading-tight"><?= $activeEmpCount ?> <span class="text-xs font-semibold text-slate-500">Karyawan</span></div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold border border-purple-100 shadow-2xs">
                    <i class="fa-solid fa-building"></i>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Cakupan Divisi</div>
                    <div class="text-base font-black text-slate-800 leading-tight truncate">
                        <?= ($isHrdOrAdmin || $isManager) ? '15 Departemen' : htmlspecialchars($currentUser['nama_dept'] ?? 'Departemen Tim') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Department Filter Tabs -->
        <?php if ($isHrdOrAdmin || $isManager): ?>
            <div class="bg-white rounded-2xl p-3.5 border border-slate-200/80 shadow-2xs flex items-center gap-1.5 flex-wrap text-xs">
                <span class="font-bold text-slate-400 mr-2 flex items-center gap-1"><i class="fa-solid fa-filter"></i> Divisi:</span>
                <a href="<?= BASE_URL ?>/index.php?page=employees" 
                   class="px-3 py-1.5 rounded-xl font-bold transition <?= $deptFilter === 0 ? 'bg-blue-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    Semua Departemen
                </a>
                <?php foreach ($departments as $dept): ?>
                    <a href="<?= BASE_URL ?>/index.php?page=employees&dept_id=<?= $dept['id'] ?>" 
                       class="px-3 py-1.5 rounded-xl font-bold transition <?= $deptFilter === (int)$dept['id'] ? 'bg-blue-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                        <?= htmlspecialchars($dept['nama_dept']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Table Card with Integrated Action Header -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 sm:px-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/40">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-base font-bold border border-blue-200/60 shadow-2xs">
                        <i class="fa-solid fa-address-book"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">
                            <?= ($isHrdOrAdmin || $isManager) ? 'Direktori Master Data Karyawan' : 'Daftar Anggota Departemen ' . htmlspecialchars($currentUser['nama_dept'] ?? '') ?>
                        </h3>
                        <p class="text-[11px] text-slate-400 font-medium hidden sm:block">
                            <?= ($isHrdOrAdmin || $isManager) ? 'Kelola seluruh data karyawan, jabatan, masa kerja, dan kuota cuti' : 'Informasi anggota tim departemen, masa kerja, jabatan, dan sisa kuota cuti' ?>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-black border border-slate-200/60 shadow-2xs">
                        Total: <?= count($employees) ?> Orang
                    </span>
                    <?php if ($canManageEmployees): ?>
                        <a href="<?= BASE_URL ?>/index.php?page=employee-template&format=xlsx" 
                           class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 font-extrabold text-xs transition transform active:scale-95 shadow-2xs"
                           title="Unduh format template Excel resmi dengan dropdown Departemen, Jabatan, dan Role">
                            <i class="fa-solid fa-file-excel text-emerald-600"></i>
                            <span>Template Excel (.xlsx)</span>
                        </a>
                        <button type="button" onclick="openImportModal()"
                           class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 font-extrabold text-xs transition transform active:scale-95 shadow-2xs">
                            <i class="fa-solid fa-file-import text-indigo-600"></i>
                            <span>Import Excel</span>
                        </button>
                        <a href="<?= BASE_URL ?>/index.php?page=employee-create" 
                           class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-extrabold text-xs shadow-md shadow-rose-600/30 transition transform active:scale-95">
                            <i class="fa-solid fa-user-plus"></i>
                            <span>+ Tambah Karyawan</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="p-4 sm:p-5">
                <table class="w-full text-left text-xs datatable">
                    <thead>
                        <tr>
                            <th class="text-center w-8">No</th>
                            <th>Karyawan</th>
                            <th>Departemen & Jabatan</th>
                            <th>Masa Kerja</th>
                            <th class="text-center">Sisa Kuota</th>
                            <th class="text-center">Status</th>
                            <?php if ($canManageEmployees): ?>
                                <th class="text-right pr-4">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($employees as $emp): ?>
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60 transition">
                                <td class="text-center py-2.5">
                                    <span class="w-5 h-5 rounded-md bg-slate-100 dark:bg-slate-800 inline-flex items-center justify-center text-[10.5px] text-slate-600 dark:text-slate-300 font-black">
                                        <?= $no++ ?>
                                    </span>
                                </td>
                                <td class="py-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 shadow-2xs">
                                            <?= strtoupper(substr($emp['nama_lengkap'], 0, 1)) ?>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-extrabold text-slate-900 dark:text-white truncate"><?= htmlspecialchars($emp['nama_lengkap']) ?></div>
                                            <div class="text-[10.5px] text-slate-400 dark:text-slate-500 font-medium truncate">
                                                <strong class="text-slate-600 dark:text-slate-300 font-mono"><?= htmlspecialchars($emp['nik']) ?></strong> &bull; <?= htmlspecialchars($emp['status_pernikahan'] ?? 'Lajang') ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2.5">
                                    <div class="font-extrabold text-slate-900 dark:text-white"><?= htmlspecialchars($emp['nama_dept']) ?></div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-[10.5px] text-slate-500 dark:text-slate-400 font-medium truncate"><?= htmlspecialchars($emp['nama_jabatan']) ?></span>
                                        <span class="scale-90 origin-left"><?= getRoleBadge($emp['role']) ?></span>
                                    </div>
                                </td>
                                <td class="py-2.5 whitespace-nowrap">
                                    <div class="font-bold text-slate-800 dark:text-slate-200"><?= hitungMasaKerja($emp['tanggal_masuk']) ?></div>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">Join: <?= date('d/m/Y', strtotime($emp['tanggal_masuk'])) ?></div>
                                </td>
                                <td class="text-center py-2.5 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-md bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200/80 dark:border-blue-900 font-black text-[11px] shadow-2xs">
                                        <?= (float)$emp['sisa_cuti'] ?> / <?= (float)$emp['kuota_cuti'] ?> Hari
                                    </span>
                                </td>
                                <td class="text-center py-2.5 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black <?= $emp['status_aktif'] === 'Aktif' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900' ?>">
                                        <?= htmlspecialchars($emp['status_aktif']) ?>
                                    </span>
                                </td>
                                <?php if ($canManageEmployees): ?>
                                    <td class="py-2.5 text-right pr-4 whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1 justify-end">
                                            <a href="<?= BASE_URL ?>/index.php?page=employee-edit&id=<?= $emp['id'] ?>" class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 dark:hover:bg-blue-900 text-blue-600 dark:text-blue-400 font-bold flex items-center justify-center transition shadow-2xs border border-transparent dark:border-blue-900" title="Edit Data">
                                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                            </a>
                                            <?php if ($emp['id'] != $currentUser['id']): ?>
                                                <button type="button" class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 dark:hover:bg-rose-900 text-rose-600 dark:text-rose-400 font-bold flex items-center justify-center transition shadow-2xs border border-transparent dark:border-rose-900" onclick="confirmDelete('<?= BASE_URL ?>/index.php?page=employee-delete&id=<?= $emp['id'] ?>', 'Hapus karyawan <?= addslashes(htmlspecialchars($emp['nama_lengkap'])) ?>?')" title="Hapus">
                                                    <i class="fa-solid fa-trash text-xs"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modern Modal Import Excel Karyawan -->
<div id="importModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm transition-opacity items-center justify-center p-4" style="display: none;">
    <div class="relative w-full max-w-2xl rounded-3xl bg-white dark:bg-[#1e293b] p-5 sm:p-6 shadow-2xl border border-slate-100 dark:border-slate-800 space-y-5 animate-scaleUp max-h-[90vh] flex flex-col">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg font-bold border border-indigo-200/60 dark:border-indigo-900 shadow-2xs">
                    <i class="fa-solid fa-file-import"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Import Data Karyawan Massal</h3>
                    <p class="text-xs text-slate-400">Unggah file Excel (.xlsx / .csv) untuk memasukkan banyak karyawan sekaligus</p>
                </div>
            </div>
            <button type="button" onclick="closeImportModal()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-300 flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Step 1: Upload Dropzone -->
        <div id="uploadSection" class="space-y-4">
            <div class="p-4 rounded-2xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900 text-xs text-blue-800 dark:text-blue-200 space-y-1">
                <strong class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-circle-info"></i> Petunjuk Import:</strong>
                <p>1. Unduh template resmi terlebih dahulu agar struktur kolom sesuai.</p>
                <p>2. Pastikan NIK & Email belum terdaftar dan Departemen sesuai salah satu dari 15 departemen resmi.</p>
                <p>3. Password awal untuk akun hasil import otomatis di-set ke: <code class="bg-blue-100 dark:bg-blue-900/60 px-1.5 py-0.5 rounded font-mono font-bold">password123</code></p>
            </div>

            <div class="border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-400 rounded-3xl p-8 text-center transition cursor-pointer bg-slate-50/50 dark:bg-slate-900/50 hover:bg-indigo-50/30 dark:hover:bg-indigo-950/30 group" id="dropzoneBox" onclick="document.getElementById('excelFileInput').click()">
                <input type="file" id="excelFileInput" accept=".xlsx, .csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, text/csv" class="hidden" onchange="handleFileSelected(this)">
                <div class="w-14 h-14 rounded-2xl bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl mx-auto mb-3 group-hover:scale-110 transition shadow-sm">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <div class="text-sm font-extrabold text-slate-800 dark:text-white mb-1" id="dropzoneTitle">Klik atau seret file Excel/CSV ke sini</div>
                <p class="text-xs text-slate-400" id="dropzoneSubtitle">Mendukung format .xlsx dan .csv (Maks. 10MB)</p>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 pt-2">
                <div class="flex items-center gap-2">
                    <a href="<?= BASE_URL ?>/index.php?page=employee-template&format=xlsx" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 dark:text-emerald-300 hover:text-emerald-800 bg-emerald-100/70 dark:bg-emerald-950/60 px-3.5 py-2 rounded-xl border border-emerald-300 dark:border-emerald-800 transition shadow-2xs">
                        <i class="fa-solid fa-file-excel text-emerald-600 text-sm"></i> Unduh Template Excel (.xlsx - 2 Sheet & Dropdown)
                    </a>
                    <a href="<?= BASE_URL ?>/index.php?page=employee-template&format=csv" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-800 bg-slate-100 dark:bg-slate-800 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 transition">
                        <i class="fa-solid fa-file-csv text-blue-500"></i> Format CSV
                    </a>
                </div>
                <button type="button" id="btnProcessPreview" onclick="uploadAndPreview()" disabled class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-200 dark:disabled:bg-slate-800 disabled:text-slate-400 text-white font-extrabold text-xs shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-magnifying-glass"></i> Periksa & Validasi
                </button>
            </div>
        </div>

        <!-- Step 2: Validation Preview (Hidden by default) -->
        <div id="previewSection" class="hidden space-y-4 overflow-y-auto flex-1 custom-scrollbar pr-1">
            
            <!-- Summary Metric Cards -->
            <div class="grid grid-cols-3 gap-3">
                <div class="p-3.5 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 text-center">
                    <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Total Baris</div>
                    <div class="text-xl font-black text-slate-900 dark:text-white" id="previewTotalCount">0</div>
                </div>
                <div class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-900 text-center">
                    <div class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase">Valid (Siap Simpan)</div>
                    <div class="text-xl font-black text-emerald-600 dark:text-emerald-400" id="previewValidCount">0</div>
                </div>
                <div class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-900 text-center">
                    <div class="text-[11px] font-bold text-rose-600 dark:text-rose-400 uppercase">Error (Gagal)</div>
                    <div class="text-xl font-black text-rose-600 dark:text-rose-400" id="previewInvalidCount">0</div>
                </div>
            </div>

            <!-- Error List Box (if any) -->
            <div id="errorListBox" class="hidden space-y-2">
                <div class="text-xs font-extrabold text-rose-700 dark:text-rose-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-triangle-exclamation"></i> Baris dengan Kesalahan (Tidak akan disimpan):
                </div>
                <div class="max-h-36 overflow-y-auto bg-rose-50/70 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 rounded-2xl p-3 text-xs text-rose-900 dark:text-rose-200 space-y-1.5 font-medium" id="errorListContent">
                </div>
            </div>

            <!-- Valid Rows Preview Table -->
            <div id="validTableBox" class="space-y-2">
                <div class="text-xs font-extrabold text-slate-800 dark:text-white flex items-center justify-between">
                    <span>Daftar Karyawan Valid:</span>
                    <span class="text-[11px] text-slate-400 font-normal">Hanya data valid yang akan dimasukkan ke database</span>
                </div>
                <div class="max-h-48 overflow-y-auto border border-slate-200 dark:border-slate-700 rounded-2xl">
                    <table class="w-full text-left text-[11px]">
                        <thead class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold sticky top-0">
                            <tr>
                                <th class="p-2.5">Baris</th>
                                <th class="p-2.5">NIK</th>
                                <th class="p-2.5">Nama Lengkap</th>
                                <th class="p-2.5">Departemen</th>
                                <th class="p-2.5">Role</th>
                            </tr>
                        </thead>
                        <tbody id="validTableBody" class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-800 dark:text-slate-200">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Commit Actions -->
            <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="resetImportModal()" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition">
                    &larr; Pilih File Lain
                </button>
                <button type="button" id="btnCommitImport" onclick="commitImport()" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i> Simpan Data Valid
                </button>
            </div>

        </div>

    </div>
</div>

<script>
let selectedImportFile = null;
let currentValidRows = [];

window.openImportModal = function() {
    const modal = document.getElementById('importModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }
    resetImportModal();
};

window.closeImportModal = function() {
    const modal = document.getElementById('importModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }
};

function resetImportModal() {
    selectedImportFile = null;
    currentValidRows = [];
    const fileInput = document.getElementById('excelFileInput');
    if (fileInput) fileInput.value = '';
    const title = document.getElementById('dropzoneTitle');
    if (title) title.innerText = 'Klik atau seret file Excel/CSV ke sini';
    const sub = document.getElementById('dropzoneSubtitle');
    if (sub) sub.innerText = 'Mendukung format .xlsx dan .csv (Maks. 10MB)';
    const btn = document.getElementById('btnProcessPreview');
    if (btn) btn.disabled = true;
    const upSec = document.getElementById('uploadSection');
    if (upSec) upSec.classList.remove('hidden');
    const prevSec = document.getElementById('previewSection');
    if (prevSec) prevSec.classList.add('hidden');
}

function handleFileSelected(input) {
    if (input.files && input.files[0]) {
        selectedImportFile = input.files[0];
        document.getElementById('dropzoneTitle').innerText = selectedImportFile.name;
        document.getElementById('dropzoneSubtitle').innerText = (selectedImportFile.size / 1024).toFixed(1) + ' KB - Siap diperiksa';
        document.getElementById('btnProcessPreview').disabled = false;
    }
}

function uploadAndPreview() {
    if (!selectedImportFile) return;

    const btn = document.getElementById('btnProcessPreview');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memvalidasi Data...';

    const formData = new FormData();
    formData.append('file', selectedImportFile);

    fetch('<?= BASE_URL ?>/index.php?page=employee-import-preview', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Periksa & Validasi';

        if (!res.success) {
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                text: res.message || 'File tidak dapat diproses'
            });
            return;
        }

        const data = res.data;
        currentValidRows = data.valid_rows || [];

        // Show Preview Section
        document.getElementById('uploadSection').classList.add('hidden');
        document.getElementById('previewSection').classList.remove('hidden');

        document.getElementById('previewTotalCount').innerText = data.total_rows;
        document.getElementById('previewValidCount').innerText = data.valid_count;
        document.getElementById('previewInvalidCount').innerText = data.invalid_count;

        // Render Errors
        const errorBox = document.getElementById('errorListBox');
        const errorContent = document.getElementById('errorListContent');
        if (data.invalid_count > 0 && data.invalid_rows) {
            errorBox.classList.remove('hidden');
            let errHtml = '';
            data.invalid_rows.forEach(r => {
                errHtml += `<div class="p-2 rounded-lg bg-rose-100/60 border border-rose-200 flex items-start gap-2">
                    <span class="px-1.5 py-0.5 rounded bg-rose-600 text-white font-mono text-[10px] font-bold">Baris ${r.row_number}</span>
                    <div class="flex-1">
                        <strong>${r.nama_lengkap || r.nik || 'Data'}</strong>: ${r.errors.join(' &bull; ')}
                    </div>
                </div>`;
            });
            errorContent.innerHTML = errHtml;
        } else {
            errorBox.classList.add('hidden');
            errorContent.innerHTML = '';
        }

        // Render Valid Table
        const tbody = document.getElementById('validTableBody');
        if (currentValidRows.length > 0) {
            let rowHtml = '';
            currentValidRows.forEach(r => {
                rowHtml += `<tr class="hover:bg-slate-50">
                    <td class="p-2.5 font-mono text-slate-500">${r.row_number}</td>
                    <td class="p-2.5 font-bold text-slate-900">${r.nik}</td>
                    <td class="p-2.5 font-medium">${r.nama_lengkap}</td>
                    <td class="p-2.5 text-slate-600">${r.departemen_input || '-'}</td>
                    <td class="p-2.5"><span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-bold uppercase text-[10px]">${r.role}</span></td>
                </tr>`;
            });
            tbody.innerHTML = rowHtml;
            document.getElementById('btnCommitImport').disabled = false;
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-slate-400 font-semibold">Tidak ada data valid untuk diimpor. Harap perbaiki file Excel Anda.</td></tr>';
            document.getElementById('btnCommitImport').disabled = true;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Periksa & Validasi';
        Swal.fire({
            icon: 'error',
            title: 'Kesalahan Sistem',
            text: 'Gagal mengirim file ke server: ' + err.message
        });
    });
}

function commitImport() {
    if (!currentValidRows || currentValidRows.length === 0) return;

    Swal.fire({
        title: 'Konfirmasi Simpan',
        text: `Simpan ${currentValidRows.length} data karyawan valid ke database?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan Semua',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#059669'
    }).then(result => {
        if (result.isConfirmed) {
            const btn = document.getElementById('btnCommitImport');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

            fetch('<?= BASE_URL ?>/index.php?page=employee-import-commit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ valid_rows: currentValidRows })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Import Berhasil!',
                        text: res.message || 'Data karyawan telah disimpan.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> Simpan Data Valid';
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: res.message || 'Terjadi kesalahan saat menyimpan'
                    });
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Simpan Data Valid';
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Jaringan',
                    text: err.message
                });
            });
        }
    });
}

let selectedMobileDept = '';

function filterByDept(btnElement, deptName) {
    selectedMobileDept = deptName.toLowerCase().trim();
    
    // Update chip styling
    document.querySelectorAll('.dept-chip').forEach(c => {
        c.className = 'dept-chip px-3 py-1.5 rounded-full font-semibold whitespace-nowrap transition bg-slate-100 text-slate-600 hover:bg-slate-200';
    });
    btnElement.className = 'dept-chip px-3 py-1.5 rounded-full font-bold whitespace-nowrap transition bg-blue-600 text-white shadow-2xs';

    applyMobileEmployeeFilters();
}

function filterMobileEmployees(q) {
    const clearBtn = document.getElementById('clearEmpSearchBtn');
    if (clearBtn) {
        if (q.trim()) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }
    applyMobileEmployeeFilters();
}

function clearEmpSearch() {
    const input = document.getElementById('mobileEmpSearch');
    if (input) input.value = '';
    const clearBtn = document.getElementById('clearEmpSearchBtn');
    if (clearBtn) clearBtn.classList.add('hidden');
    applyMobileEmployeeFilters();
}

function applyMobileEmployeeFilters() {
    const term = (document.getElementById('mobileEmpSearch')?.value || '').toLowerCase().trim();
    const cards = document.querySelectorAll('.emp-mobile-card');
    
    cards.forEach(c => {
        const name = c.getAttribute('data-name') || '';
        const nik = c.getAttribute('data-nik') || '';
        const email = c.getAttribute('data-email') || '';
        const dept = c.getAttribute('data-dept') || '';
        const pos = c.getAttribute('data-pos') || '';

        const matchesDept = !selectedMobileDept || dept.includes(selectedMobileDept);
        const matchesTerm = !term || name.includes(term) || nik.includes(term) || email.includes(term) || dept.includes(term) || pos.includes(term);

        if (matchesDept && matchesTerm) {
            c.style.display = '';
        } else {
            c.style.display = 'none';
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

