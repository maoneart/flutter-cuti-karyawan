<?php
/**
 * Team & Shift Attendance View (Leader & Supervisor Dashboard)
 * Nakakin Mobile - Standard PKB PT. Nakakin Indonesia
 */

$pageTitle = 'Tim & Absensi Shift - ' . htmlspecialchars($department['nama_dept'] ?? 'Departemen');
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6">

    <!-- Top Info Banner & Shift Mechanism Notice (Desktop Only) -->
    <div class="hidden lg:block p-4 sm:p-5 rounded-3xl bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 border border-blue-900/40 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-blue-600/30 border border-blue-400/30 flex items-center justify-center text-blue-300 text-xl font-black shadow-inner flex-shrink-0">
                    <i class="fa-solid fa-users-gear"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-base sm:text-lg font-black tracking-tight text-white">
                            Departemen <?= htmlspecialchars($department['nama_dept'] ?? 'Produksi') ?>
                        </h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-500/20 text-blue-300 border border-blue-400/30">
                            <?= htmlspecialchars($department['kode_dept'] ?? '-') ?>
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-400/30">
                            <i class="fa-solid fa-repeat text-[9px] mr-1"></i> 2 Shift (Pagi & Malam)
                        </span>
                    </div>
                    <p class="text-xs text-slate-300 mt-1 flex items-center gap-1.5 flex-wrap">
                        <i class="fa-solid fa-circle-info text-blue-400"></i>
                        <span>Mekanisme <strong>Shift 2 (Malam)</strong>: Jadwal kerja Senin dimulai <strong>Minggu Malam</strong> (Malam Senin).</span>
                    </p>
                </div>
            </div>

            <!-- Action Controls: Date Picker & Rolling Shift -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Date Filter -->
                <form method="GET" action="<?= BASE_URL ?>/index.php" class="flex items-center gap-1.5 bg-slate-800/80 p-1.5 rounded-2xl border border-slate-700/60 shadow-inner">
                    <input type="hidden" name="page" value="team-attendance">
                    <?php if (isset($_GET['dept_id'])): ?>
                        <input type="hidden" name="dept_id" value="<?= (int)$_GET['dept_id'] ?>">
                    <?php endif; ?>
                    <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>" 
                           onchange="this.form.submit()" 
                           class="bg-transparent text-white text-xs font-semibold px-2 py-1 outline-none cursor-pointer">
                    <button type="submit" class="w-7 h-7 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xs hover:bg-blue-500 transition">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>

                <!-- Rolling Shift Button (Leader Only) -->
                <?php if ($isLeaderOrSpv || $isHRD): ?>
                    <form id="formRollingShift" method="POST" action="<?= BASE_URL ?>/index.php?page=team-attendance-rolling">
                        <input type="hidden" name="dept_id" value="<?= $deptId ?>">
                        <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
                        <button type="button" onclick="confirmRollingShift()" 
                                class="px-3.5 py-2.5 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-bold text-xs shadow-md shadow-amber-600/20 transition flex items-center gap-2">
                            <i class="fa-solid fa-shuffle"></i>
                            <span class="hidden sm:inline">Rolling Shift Tim</span>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Department Filter Tabs (Desktop Only) -->
    <?php if (!empty($allDepartments)): ?>
        <div class="hidden lg:flex bg-white rounded-2xl p-3.5 border border-slate-200/80 shadow-xs items-center gap-1.5 flex-wrap text-xs">
            <span class="font-bold text-slate-400 mr-2 flex items-center gap-1"><i class="fa-solid fa-filter"></i> Pilih Divisi:</span>
            <?php foreach ($allDepartments as $d): ?>
                <a href="<?= BASE_URL ?>/index.php?page=team-attendance&dept_id=<?= $d['id'] ?>&tanggal=<?= htmlspecialchars($tanggal) ?>" 
                   class="px-3 py-1.5 rounded-xl font-bold transition <?= $deptId === (int)$d['id'] ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    <?= htmlspecialchars($d['nama_dept']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- =========================================================
         MOBILE VIEW (100% IDENTICAL TO FLUTTER TeamAttendanceScreen)
         ========================================================= -->
    <div class="block lg:hidden space-y-4">
        <!-- Date Selector & Rolling Shift Header Card -->
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-3.5 border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center justify-between gap-2.5">
            <!-- Date Filter Picker -->
            <form method="GET" action="<?= BASE_URL ?>/index.php" class="flex items-center gap-2.5 bg-blue-50/90 dark:bg-sky-950/50 px-3.5 py-2 rounded-xl border border-blue-200/80 dark:border-sky-800/50 flex-1 transition">
                <input type="hidden" name="page" value="team-attendance">
                <?php if (isset($_GET['dept_id'])): ?>
                    <input type="hidden" name="dept_id" value="<?= (int)$_GET['dept_id'] ?>">
                <?php endif; ?>
                <i class="fa-solid fa-calendar-days text-blue-600 dark:text-sky-400 text-sm flex-shrink-0"></i>
                <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>" 
                       onchange="this.form.submit()" 
                       class="bg-transparent text-blue-900 dark:text-sky-200 font-bold text-xs outline-none w-full cursor-pointer border-0 p-0 focus:ring-0 focus:outline-none">
            </form>

            <!-- Rolling Shift Button (Leader Only) -->
            <?php if ($isLeaderOrSpv || $isHRD): ?>
                <form id="formRollingShiftMobile" method="POST" action="<?= BASE_URL ?>/index.php?page=team-attendance-rolling">
                    <input type="hidden" name="dept_id" value="<?= $deptId ?>">
                    <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
                    <button type="button" onclick="confirmRollingShift()" 
                            class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-sky-600 dark:hover:bg-sky-500 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5 flex-shrink-0">
                        <i class="fa-solid fa-shuffle text-xs"></i>
                        <span>Rolling Shift</span>
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- 4 Quick Stats Ribbon Cards (Flutter Style) -->
        <?php
        $countShift1 = 0;
        $countShift2 = 0;
        $countMangkir = 0;
        $countCuti = 0;
        foreach ($teamMembers as $tm) {
            if (stripos($tm['current_shift'] ?? '', 'Shift 2') !== false) $countShift2++;
            else $countShift1++;

            if (isset($absensiMap[$tm['id']]) && $absensiMap[$tm['id']]['status'] === 'mangkir') $countMangkir++;
            if (isset($leavesMap[$tm['id']])) $countCuti++;
        }
        ?>
        <div class="grid grid-cols-4 gap-2">
            <!-- 1. Total Anggota -->
            <div class="bg-white rounded-2xl p-2.5 border border-slate-200/80 shadow-xs flex flex-col items-center text-center">
                <i class="fa-solid fa-users text-blue-600 text-base mb-1"></i>
                <span class="text-sm font-black text-slate-900"><?= count($teamMembers) ?></span>
                <span class="text-[9px] font-semibold text-slate-400 truncate w-full">Total Anggota</span>
            </div>
            <!-- 2. Shift 1 (Pagi) -->
            <div class="bg-white rounded-2xl p-2.5 border border-slate-200/80 shadow-xs flex flex-col items-center text-center">
                <i class="fa-solid fa-sun text-indigo-600 text-base mb-1"></i>
                <span class="text-sm font-black text-slate-900"><?= $countShift1 ?></span>
                <span class="text-[9px] font-semibold text-slate-400 truncate w-full">Shift 1 (Pagi)</span>
            </div>
            <!-- 3. Shift 2 (Malam) -->
            <div class="bg-white rounded-2xl p-2.5 border border-slate-200/80 shadow-xs flex flex-col items-center text-center">
                <i class="fa-solid fa-moon text-purple-600 text-base mb-1"></i>
                <span class="text-sm font-black text-slate-900"><?= $countShift2 ?></span>
                <span class="text-[9px] font-semibold text-slate-400 truncate w-full">Shift 2 (Malam)</span>
            </div>
            <!-- 4. Mangkir -->
            <div class="bg-white rounded-2xl p-2.5 border border-slate-200/80 shadow-xs flex flex-col items-center text-center">
                <i class="fa-solid fa-user-xmark text-rose-600 text-base mb-1"></i>
                <span class="text-sm font-black text-slate-900"><?= $countMangkir ?></span>
                <span class="text-[9px] font-semibold text-slate-400 truncate w-full">Mangkir</span>
            </div>
        </div>

        <!-- Section Title -->
        <div class="flex items-center justify-between pt-1">
            <h3 class="text-sm font-black text-slate-900">Daftar Anggota & Roster Shift</h3>
            <span class="text-[11px] font-semibold text-slate-400"><?= count($teamMembers) ?> Karyawan</span>
        </div>

        <!-- Member Cards List (Flutter _buildMemberCard parity) -->
        <?php if (empty($teamMembers)): ?>
            <div class="bg-white rounded-3xl p-8 border border-slate-200/80 text-center shadow-xs">
                <i class="fa-solid fa-users-slash text-3xl text-slate-300 mb-2"></i>
                <p class="text-xs font-bold text-slate-500">Tidak ada data anggota di departemen ini.</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($teamMembers as $tm): 
                    $empId = $tm['id'];
                    $absRec = $absensiMap[$empId] ?? null;
                    $leaveRec = $leavesMap[$empId] ?? null;
                    $isLeader = in_array(strtolower($tm['role']), ['leader', 'supervisor']);
                    $currentShift = $tm['current_shift'] ?? 'Shift 1';
                    $isShift2 = stripos($currentShift, 'Shift 2') !== false;
                    $isMangkir = ($absRec && $absRec['status'] === 'mangkir');
                    $isRevised = ($absRec && ($absRec['status_revisi'] ?? '') === 'revised');
                    $catatan = $absRec['keterangan'] ?? ($absRec['alasan_revisi'] ?? '');
                ?>
                    <div class="bg-white rounded-2xl p-4 border <?= $isMangkir ? 'border-rose-300 ring-2 ring-rose-500/10' : 'border-slate-200/80' ?> shadow-xs space-y-3">
                        <div class="flex items-start gap-3">
                            <!-- Avatar Circle with Initial -->
                            <div class="w-10 h-10 rounded-full <?= $isShift2 ? 'bg-rose-100 text-rose-700' : 'bg-blue-100 text-blue-700' ?> font-black text-sm flex items-center justify-center flex-shrink-0 shadow-2xs">
                                <?= strtoupper(substr($tm['nama_lengkap'], 0, 1)) ?>
                            </div>

                            <!-- Name & Details -->
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-black text-slate-900 leading-tight truncate"><?= htmlspecialchars($tm['nama_lengkap']) ?></h4>
                                <p class="text-[11px] text-slate-400 mt-0.5"><?= htmlspecialchars($tm['nik']) ?> &bull; <?= htmlspecialchars($tm['nama_jabatan']) ?></p>
                                <p class="text-[11px] font-bold text-emerald-600 mt-0.5">Sisa Cuti: <?= (float)$tm['sisa_cuti'] ?> Hari</p>
                            </div>

                            <!-- Shift Badge & Quick Toggle -->
                            <?php if ($isLeaderOrSpv || $isHRD): ?>
                                <form method="POST" action="<?= BASE_URL ?>/index.php?page=team-attendance-shift" class="flex-shrink-0">
                                    <input type="hidden" name="employee_id" value="<?= $empId ?>">
                                    <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
                                    <select name="shift" onchange="this.form.submit()" 
                                            class="text-[11px] font-extrabold rounded-xl px-2.5 py-1.5 border transition cursor-pointer shadow-2xs outline-none <?= $isShift2 ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-blue-50 border-blue-200 text-blue-700' ?>">
                                        <option value="Shift 1 (Pagi)" <?= !$isShift2 ? 'selected' : '' ?>>☀️ Shift 1 (Pagi)</option>
                                        <option value="Shift 2 (Malam)" <?= $isShift2 ? 'selected' : '' ?>>🌙 Shift 2 (Malam)</option>
                                    </select>
                                </form>
                            <?php else: ?>
                                <span class="px-2.5 py-1 rounded-xl text-[11px] font-extrabold border <?= $isShift2 ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-blue-50 border-blue-200 text-blue-700' ?>">
                                    <?= $isShift2 ? '🌙 Shift 2' : '☀️ Shift 1' ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="border-t border-slate-100 pt-2.5 flex items-center justify-between gap-2">
                            <!-- Status Kehadiran -->
                            <div>
                                <?php if ($leaveRec): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10.5px] font-black bg-blue-100 text-blue-800 border border-blue-200">
                                        <i class="fa-solid fa-umbrella-beach"></i> Cuti (<?= htmlspecialchars($leaveRec['kode_cuti']) ?>)
                                    </span>
                                <?php elseif ($isMangkir): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10.5px] font-black bg-rose-100 text-rose-800 border border-rose-300 animate-pulse">
                                        <i class="fa-solid fa-triangle-exclamation"></i> MANGKIR
                                    </span>
                                <?php elseif ($isRevised): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10.5px] font-black bg-amber-100 text-amber-800 border border-amber-300">
                                        <i class="fa-solid fa-check-double"></i> Direvisi: <?= htmlspecialchars($absRec['direvisi_menjadi']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10.5px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i> Hadir Normal
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Action Buttons -->
                            <?php if ($isLeaderOrSpv || $isHRD): ?>
                                <div>
                                    <?php if ($isMangkir): ?>
                                        <button type="button" 
                                                onclick="openRevisiModal(<?= $absRec['id'] ?>, '<?= htmlspecialchars(addslashes($tm['nama_lengkap'])) ?>', '<?= htmlspecialchars($absRec['shift']) ?>')"
                                                class="px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-2xs flex items-center gap-1 transition">
                                            <i class="fa-solid fa-pen-to-square"></i> Revisi Mangkir
                                        </button>
                                    <?php elseif (!$leaveRec && !$isRevised): ?>
                                        <button type="button" 
                                                onclick="openMangkirModal(<?= $empId ?>, '<?= htmlspecialchars(addslashes($tm['nama_lengkap'])) ?>', '<?= htmlspecialchars($currentShift) ?>')"
                                                class="px-3 py-1.5 rounded-xl border border-rose-300 bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold text-xs flex items-center gap-1 transition">
                                            <i class="fa-solid fa-user-xmark"></i> Tandai Mangkir
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($catatan)): ?>
                            <p class="text-[11px] text-slate-400 italic bg-slate-50 p-2 rounded-lg border border-slate-100">
                                <i class="fa-solid fa-circle-info mr-1 text-slate-400"></i> Ket: <?= htmlspecialchars($catatan) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- =========================================================
         DESKTOP VIEW (TABLE LAYOUT FOR WIDE SCREENS >= 1024px)
         ========================================================= -->
    <div class="hidden lg:block bg-white rounded-3xl border border-slate-200/80 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.04)] overflow-hidden">
        
        <div class="p-5 sm:px-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm font-bold border border-indigo-200/60 shadow-2xs">
                    <i class="fa-solid fa-clipboard-user"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 leading-snug">Roster Anggota Tim & Kehadiran Shift</h3>
                    <p class="text-xs text-slate-400 font-medium">Tanggal: <span class="font-bold text-slate-700"><?= formatTanggalIndo($tanggal) ?></span> &bull; Total: <span class="font-bold text-blue-600"><?= count($teamMembers) ?> Karyawan</span></p>
                </div>
            </div>

            <!-- Summary Status Badges -->
            <div class="flex items-center gap-2 flex-wrap text-[11px] font-bold">
                <span class="px-2.5 py-1 rounded-xl bg-amber-50 text-amber-700 border border-amber-200/70">
                    <i class="fa-solid fa-sun mr-1"></i> Shift 1 (Pagi): <?= $countShift1 ?>
                </span>
                <span class="px-2.5 py-1 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-200/70">
                    <i class="fa-solid fa-moon mr-1"></i> Shift 2 (Malam): <?= $countShift2 ?>
                </span>
                <?php if ($countMangkir > 0): ?>
                    <span class="px-2.5 py-1 rounded-xl bg-rose-50 text-rose-700 border border-rose-200/70 animate-pulse">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> Mangkir: <?= $countMangkir ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Table Responsive -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100/75 border-b border-slate-200 text-slate-600 font-extrabold uppercase tracking-wider text-[10.5px]">
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Nama & NIK</th>
                        <th class="py-3.5 px-4">Jabatan</th>
                        <th class="py-3.5 px-4 text-center">Jadwal Shift</th>
                        <th class="py-3.5 px-4 text-center">Sisa Cuti</th>
                        <th class="py-3.5 px-4 text-center">Status Kehadiran</th>
                        <th class="py-3.5 px-4 text-center w-48">Aksi Leader</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($teamMembers)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-users-slash text-3xl mb-2 text-slate-300"></i>
                                <p class="font-bold">Tidak ada data anggota di departemen ini.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($teamMembers as $tm): 
                            $empId = $tm['id'];
                            $absRec = $absensiMap[$empId] ?? null;
                            $leaveRec = $leavesMap[$empId] ?? null;
                            $isLeader = in_array(strtolower($tm['role']), ['leader', 'supervisor']);
                            $currentShift = $tm['current_shift'] ?? 'Shift 1';
                        ?>
                            <tr class="hover:bg-blue-50/30 transition <?= ($absRec && $absRec['status'] === 'mangkir') ? 'bg-rose-50/40' : '' ?>">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-semibold"><?= $no++ ?></td>
                                
                                <!-- Name & Contact -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white font-black text-xs flex items-center justify-center shadow-xs flex-shrink-0">
                                            <?= strtoupper(substr($tm['nama_lengkap'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 leading-snug"><?= htmlspecialchars($tm['nama_lengkap']) ?></div>
                                            <div class="text-[10.5px] text-slate-400 font-mono mt-0.5 flex items-center gap-2">
                                                <span><?= htmlspecialchars($tm['nik']) ?></span>
                                                <?php if (!empty($tm['no_hp'])): ?>
                                                    <a href="https://wa.me/<?= preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $tm['no_hp'])) ?>" 
                                                       target="_blank" 
                                                       class="text-emerald-600 hover:text-emerald-700 font-bold inline-flex items-center gap-1"
                                                       title="Hubungi via WhatsApp">
                                                        <i class="fa-brands fa-whatsapp text-xs"></i> <?= htmlspecialchars($tm['no_hp']) ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Role & Jabatan -->
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[10.5px] font-bold <?= $isLeader ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-600' ?>">
                                        <i class="fa-solid <?= $isLeader ? 'fa-user-tie' : 'fa-helmet-safety' ?> text-[10px]"></i>
                                        <?= htmlspecialchars($tm['nama_jabatan']) ?>
                                    </span>
                                </td>

                                <!-- Shift Assignment & Quick Toggle -->
                                <td class="py-3.5 px-4 text-center">
                                    <form method="POST" action="<?= BASE_URL ?>/index.php?page=team-attendance-shift" class="inline-block">
                                        <input type="hidden" name="employee_id" value="<?= $empId ?>">
                                        <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
                                        <select name="shift" onchange="this.form.submit()" 
                                                class="text-[11px] font-extrabold rounded-xl px-2.5 py-1 border transition cursor-pointer shadow-2xs outline-none <?= stripos($currentShift, 'Shift 2') !== false ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'bg-amber-50 border-amber-200 text-amber-700' ?>">
                                            <option value="Shift 1 (Pagi)" <?= stripos($currentShift, 'Shift 1') !== false ? 'selected' : '' ?>>☀️ Shift 1 (Pagi)</option>
                                            <option value="Shift 2 (Malam)" <?= stripos($currentShift, 'Shift 2') !== false ? 'selected' : '' ?>>🌙 Shift 2 (Malam)</option>
                                        </select>
                                    </form>
                                </td>

                                <!-- Leave Quota -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="font-extrabold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-xl text-[11px]">
                                        <?= (float)$tm['sisa_cuti'] ?> <span class="text-[9.5px] text-slate-400 font-normal">Hari</span>
                                    </span>
                                </td>

                                <!-- Attendance Status for Today -->
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($leaveRec): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-black bg-blue-100 text-blue-800 border border-blue-200">
                                            <i class="fa-solid fa-umbrella-beach"></i> Cuti (<?= htmlspecialchars($leaveRec['kode_cuti']) ?>)
                                        </span>
                                    <?php elseif ($absRec && $absRec['status'] === 'mangkir'): ?>
                                        <div class="inline-flex flex-col items-center">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-black bg-rose-100 text-rose-800 border border-rose-300 shadow-2xs animate-pulse">
                                                <i class="fa-solid fa-triangle-exclamation"></i> MANGKIR
                                            </span>
                                            <span class="text-[9.5px] text-rose-600 font-bold mt-0.5">Potong Gaji + SP</span>
                                        </div>
                                    <?php elseif ($absRec && $absRec['status_revisi'] === 'revised'): ?>
                                        <div class="inline-flex flex-col items-center">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                <i class="fa-solid fa-check-double"></i> Direvisi: <?= htmlspecialchars($absRec['direvisi_menjadi']) ?>
                                            </span>
                                            <span class="text-[9.5px] text-slate-400 font-medium mt-0.5">Oleh <?= htmlspecialchars($absRec['nama_leader'] ?? 'Leader') ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fa-solid fa-circle-check text-[10px]"></i> Hadir Normal
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Leader Actions -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <?php if ($absRec && $absRec['status'] === 'mangkir'): ?>
                                            <!-- Revise Button if already Mangkir -->
                                            <button type="button" 
                                                    onclick="openRevisiModal(<?= $absRec['id'] ?>, '<?= htmlspecialchars(addslashes($tm['nama_lengkap'])) ?>', '<?= htmlspecialchars($absRec['shift']) ?>')"
                                                    class="px-2.5 py-1.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-[10.5px] shadow-2xs flex items-center gap-1 transition">
                                                <i class="fa-solid fa-pen-to-square"></i> Revisi
                                            </button>
                                        <?php elseif (!$leaveRec): ?>
                                            <!-- Mark Mangkir Button -->
                                            <button type="button" 
                                                    onclick="openMangkirModal(<?= $empId ?>, '<?= htmlspecialchars(addslashes($tm['nama_lengkap'])) ?>', '<?= htmlspecialchars($currentShift) ?>')"
                                                    class="px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-[10.5px] flex items-center gap-1 transition">
                                                <i class="fa-solid fa-user-xmark"></i> Catat Mangkir
                                            </button>
                                        <?php else: ?>
                                            <span class="text-[10px] text-slate-400 font-medium">Izin Aktif</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================
     MODAL DIALOGS: MAONEART GLASSMORPHISM MODAL STANDARD
     ======================================================== -->

<!-- Modal 1: Catat Mangkir Modal -->
<div id="mangkirModalBackdrop" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-md hidden flex items-center justify-center p-4 transition-all duration-200">
    <div class="bg-white dark:bg-[#1e293b] rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-md w-full p-5 sm:p-6 relative overflow-hidden animate-in fade-in zoom-in-95 duration-150 space-y-4">
        
        <!-- Header -->
        <div class="flex items-center gap-3.5 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div class="w-11 h-11 rounded-2xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg font-bold border border-rose-200/60 dark:border-rose-900 shadow-2xs flex-shrink-0">
                <i class="fa-solid fa-user-slash"></i>
            </div>
            <div>
                <h3 class="text-sm font-black text-slate-900 dark:text-white leading-snug">Pencatatan Operator Mangkir</h3>
                <p class="text-xs text-slate-400 font-medium">Konfirmasi ketidakhadiran tanpa izin / kabar</p>
            </div>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/index.php?page=team-attendance-mangkir" class="space-y-4 text-xs">
            <input type="hidden" name="employee_id" id="modalMangkirEmpId" value="">
            <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">

            <div class="p-3 rounded-2xl bg-rose-50/70 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-900 text-xs text-rose-900 dark:text-rose-200">
                Operator: <strong id="modalMangkirEmpName" class="font-extrabold text-rose-950 dark:text-rose-100">-</strong>
            </div>

            <!-- Shift Selection -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Pilih Shift Masuk Kerja <span class="text-rose-500">*</span>
                </label>
                <select name="shift" id="modalMangkirShift" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white font-semibold text-xs focus:outline-none focus:bg-white dark:focus:bg-slate-800 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition">
                    <option value="Shift 1 (Pagi)">☀️ Shift 1 (Pagi)</option>
                    <option value="Shift 2 (Malam)">🌙 Shift 2 (Malam)</option>
                </select>
                <p class="text-[10.5px] text-slate-400 mt-1">
                    *Pilih shift kerja saat anggota tidak masuk.
                </p>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Catatan Leader / Keterangan Lapangan
                </label>
                <textarea name="keterangan_mangkir" rows="2" 
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white font-medium text-xs focus:outline-none focus:bg-white dark:focus:bg-slate-800 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition resize-none placeholder:text-slate-400"
                          placeholder="Tidak ada konfirmasi saat briefing/P5M..."></textarea>
            </div>

            <!-- 100% Symmetric 2-Column Grid Action Buttons -->
            <div class="grid grid-cols-2 gap-3 pt-2 w-full">
                <button type="button" onclick="closeMangkirModal()" 
                        class="w-full py-2.5 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs transition text-center">
                    Batal
                </button>
                <button type="submit" 
                        class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 text-white font-extrabold text-xs shadow-md shadow-rose-600/30 transition text-center flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-check"></i> Simpan Mangkir
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Revisi Status Absensi Modal -->
<div id="revisiModalBackdrop" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-md hidden flex items-center justify-center p-4 transition-all duration-200">
    <div class="bg-white dark:bg-[#1e293b] rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-md w-full p-5 sm:p-6 relative overflow-hidden animate-in fade-in zoom-in-95 duration-150 space-y-4">
        
        <!-- Header -->
        <div class="flex items-center gap-3.5 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg font-bold border border-emerald-200/60 dark:border-emerald-900 shadow-2xs flex-shrink-0">
                <i class="fa-solid fa-file-medical"></i>
            </div>
            <div>
                <h3 class="text-sm font-black text-slate-900 dark:text-white leading-snug">Revisi Status Ketidakhadiran</h3>
                <p class="text-xs text-slate-400 font-medium">Konversi status mangkir dengan bukti sah susulan</p>
            </div>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/index.php?page=team-attendance-revisi" enctype="multipart/form-data" class="space-y-4 text-xs">
            <input type="hidden" name="absensi_id" id="modalRevisiAbsId" value="">
            <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">

            <div class="p-3 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900 text-xs text-emerald-900 dark:text-emerald-200">
                Operator: <strong id="modalRevisiEmpName" class="font-extrabold text-emerald-950 dark:text-emerald-100">-</strong> &bull; <span id="modalRevisiShift">Shift 1</span>
            </div>

            <!-- Target Leave/Permit Type -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Ubah Status Menjadi <span class="text-rose-500">*</span>
                </label>
                <select name="direvisi_menjadi" id="modalRevisiTarget" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white font-semibold text-xs focus:outline-none focus:bg-white dark:focus:bg-slate-800 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition">
                    <?php foreach ($revisionTypes as $rt): ?>
                        <option value="<?= htmlspecialchars($rt['kode']) ?>" <?= $rt['kode'] === 'SD' ? 'selected' : '' ?>>
                            [<?= htmlspecialchars($rt['kode']) ?>] <?= htmlspecialchars($rt['nama_cuti']) ?> <?= (int)$rt['potong_kuota'] === 1 ? '(Potong Cuti)' : '(Tanpa Potong Cuti)' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Upload File Bukti Surat -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Lampirkan Foto Surat Dokter / Bukti Resmi <span class="text-slate-400 font-normal">(JPG, PNG, PDF)</span>
                </label>
                <input type="file" name="bukti_lampiran" accept=".jpg,.jpeg,.png,.pdf" 
                       class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 dark:file:bg-emerald-950/60 file:text-emerald-700 dark:file:text-emerald-300 hover:file:bg-emerald-100 file:cursor-pointer border border-slate-200 dark:border-slate-700 rounded-xl p-1 bg-slate-50 dark:bg-slate-900">
            </div>

            <!-- Reason -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                    Alasan Perubahan Status <span class="text-rose-500">*</span>
                </label>
                <textarea name="alasan_revisi" rows="2" required 
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white font-medium text-xs focus:outline-none focus:bg-white dark:focus:bg-slate-800 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition resize-none placeholder:text-slate-400"
                          placeholder="Surat keterangan dokter rawat inap disusulkan oleh keluarga..."></textarea>
            </div>

            <!-- 100% Symmetric 2-Column Grid Action Buttons -->
            <div class="grid grid-cols-2 gap-3 pt-2 w-full">
                <button type="button" onclick="closeRevisiModal()" 
                        class="w-full py-2.5 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs transition text-center">
                    Batal
                </button>
                <button type="submit" 
                        class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-xs shadow-md shadow-emerald-600/30 transition text-center flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-check"></i> Simpan Revisi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Mangkir Modal Handlers
    function openMangkirModal(empId, empName, shift) {
        document.getElementById('modalMangkirEmpId').value = empId;
        document.getElementById('modalMangkirEmpName').innerText = empName;
        document.getElementById('modalMangkirShift').value = shift;
        document.getElementById('mangkirModalBackdrop').classList.remove('hidden');
    }

    function closeMangkirModal() {
        document.getElementById('mangkirModalBackdrop').classList.add('hidden');
    }

    // Revisi Modal Handlers
    function openRevisiModal(absId, empName, shift) {
        document.getElementById('modalRevisiAbsId').value = absId;
        document.getElementById('modalRevisiEmpName').innerText = empName;
        document.getElementById('modalRevisiShift').innerText = shift;
        document.getElementById('revisiModalBackdrop').classList.remove('hidden');
    }

    function closeRevisiModal() {
        document.getElementById('revisiModalBackdrop').classList.add('hidden');
    }

    // Confirm Rolling Shift using MaoneArt Glassmorphism Modal Standard
    function confirmRollingShift() {
        if (window.Swal) {
            Swal.fire({
                title: '',
                html: `
                    <div class="text-left space-y-3">
                        <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                            <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg font-bold border border-amber-200/60 shadow-2xs flex-shrink-0">
                                <i class="fa-solid fa-shuffle"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 leading-snug">Konfirmasi Rolling Shift Tim</h3>
                                <p class="text-[11px] text-slate-400 font-medium">Rotasi jadwal kerja mingguan</p>
                            </div>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Apakah Anda ingin menukar shift seluruh operator di departemen ini? 
                            <br><br>
                            <span class="font-bold text-amber-700">&bull; Operator Shift 1 (Pagi) akan beralih ke Shift 2 (Malam).</span><br>
                            <span class="font-bold text-indigo-700">&bull; Operator Shift 2 (Malam) akan beralih ke Shift 1 (Pagi).</span>
                        </p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonColor: '#d97706',
                confirmButtonText: '<i class="fa-solid fa-check mr-1.5"></i> Ya, Rolling Sekarang',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'modern-swal-popup',
                    htmlContainer: 'modern-swal-html',
                    actions: 'modern-swal-actions',
                    confirmButton: 'modern-swal-confirm',
                    cancelButton: 'modern-swal-cancel'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('formRollingShift').submit();
                }
            });
        } else {
            document.getElementById('formRollingShift').submit();
        }
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
