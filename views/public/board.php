<?php
/**
 * Public Today's Attendance & Leave Board (Papan Kehadiran & Cuti Karyawan Hari Ini)
 * White-label & TV Display Ready
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/session.php';

$pdo = getDbConnection();
$appSettings = getAppSettings($pdo);
$currentUser = getCurrentUser();

$todayDate = date('Y-m-d');
$todayFormatted = formatTanggalIndo($todayDate);
$dayNameIndo = [
    'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 
    'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
][date('l')];

// 1. Fetch Today's Active Leaves
$stmtToday = $pdo->prepare("
    SELECT lr.*, 
           e.nik, e.nama_lengkap, e.foto, e.jenis_kelamin,
           d.nama_dept, d.kode_dept,
           p.nama_jabatan,
           lt.nama_cuti, lt.kode as kode_cuti, lt.potong_kuota
    FROM pengajuan_cuti lr
    JOIN karyawan e ON lr.employee_id = e.id
    JOIN departemen d ON e.departemen_id = d.id
    JOIN jabatan p ON e.jabatan_id = p.id
    JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
    WHERE lr.status = 'approved' AND ? BETWEEN lr.tanggal_mulai AND lr.tanggal_selesai
    ORDER BY d.nama_dept ASC, e.nama_lengkap ASC
");
$stmtToday->execute([$todayDate]);
$todayLeaves = $stmtToday->fetchAll();

// 2. Fetch Upcoming Leaves (Next 7 Days)
$stmtUpcoming = $pdo->prepare("
    SELECT lr.*, 
           e.nik, e.nama_lengkap, e.foto,
           d.nama_dept,
           p.nama_jabatan,
           lt.nama_cuti
    FROM pengajuan_cuti lr
    JOIN karyawan e ON lr.employee_id = e.id
    JOIN departemen d ON e.departemen_id = d.id
    JOIN jabatan p ON e.jabatan_id = p.id
    JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
    WHERE lr.status = 'approved' AND lr.tanggal_mulai > ? AND lr.tanggal_mulai <= DATE_ADD(?, INTERVAL 7 DAY)
    ORDER BY lr.tanggal_mulai ASC, e.nama_lengkap ASC
    LIMIT 6
");
$stmtUpcoming->execute([$todayDate, $todayDate]);
$upcomingLeaves = $stmtUpcoming->fetchAll();

// 3. Metrics
$totalKaryawan = (int)$pdo->query("SELECT COUNT(*) FROM karyawan WHERE status_aktif = 'Aktif'")->fetchColumn();
$countCutiHariIni = count($todayLeaves);
$countHadirHariIni = max(0, $totalKaryawan - $countCutiHariIni);
$countSakitHariIni = 0;
$countIzinHariIni = 0;
$countTahunanHariIni = 0;

foreach ($todayLeaves as $l) {
    if (in_array($l['kode_cuti'], ['SSD', 'STSD']) || stripos($l['nama_cuti'], 'Sakit') !== false) {
        $countSakitHariIni++;
    } elseif ($l['kode_cuti'] === 'CT' || stripos($l['nama_cuti'], 'Tahunan') !== false) {
        $countTahunanHariIni++;
    } else {
        $countIzinHariIni++;
    }
}

// 4. Department List for Filter
$departments = $pdo->query("SELECT * FROM departemen ORDER BY nama_dept ASC")->fetchAll();

$logoUrl = !empty($appSettings['logo']) ? BASE_URL . '/assets/images/' . $appSettings['logo'] : BASE_URL . '/assets/images/Nakakin.png';
$favUrl = !empty($appSettings['favicon']) ? BASE_URL . '/assets/images/' . $appSettings['favicon'] : BASE_URL . '/assets/images/Nakakin.png';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Papan Informasi Kehadiran & Cuti Karyawan | <?= htmlspecialchars($appSettings['nama_perusahaan']) ?></title>
    
    <link rel="icon" type="image/png" href="<?= $favUrl ?>">

    <!-- Google Fonts: Plus Jakarta Sans & Space Grotesk -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['"Space Grotesk"', 'sans-serif'],
                    },
                    boxShadow: {
                        'soft': '0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02)',
                        'glow-blue': '0 0 35px -5px rgba(59, 130, 246, 0.35)',
                        'glow-rose': '0 0 35px -5px rgba(225, 29, 72, 0.35)',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.3); border-radius: 9999px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(148, 163, 184, 0.5); }
    </style>
</head>
<body class="min-h-full bg-[#0a0f1d] text-slate-100 font-sans antialiased flex flex-col selection:bg-rose-500 selection:text-white relative overflow-x-hidden">

    <!-- Ambient Glowing Background Lights -->
    <div class="fixed -top-40 -left-40 w-[600px] h-[600px] bg-blue-600/15 rounded-full blur-[130px] pointer-events-none"></div>
    <div class="fixed top-1/3 -right-40 w-[500px] h-[500px] bg-rose-600/15 rounded-full blur-[130px] pointer-events-none"></div>
    <div class="fixed -bottom-40 left-1/3 w-[600px] h-[600px] bg-indigo-600/15 rounded-full blur-[140px] pointer-events-none"></div>

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-50 bg-[#090e1a]/85 backdrop-blur-xl border-b border-slate-800/80 px-4 sm:px-8 py-3.5 transition-all">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            
            <!-- Left: Brand Logo & Title -->
            <a href="<?= BASE_URL ?>/index.php" class="flex items-center gap-3.5 group">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-rose-500 via-red-600 to-rose-700 p-0.5 shadow-lg shadow-rose-600/25 group-hover:scale-105 transition duration-300 flex items-center justify-center flex-shrink-0">
                    <div class="w-full h-full bg-[#0a0f1d] rounded-[14px] flex items-center justify-center p-1 overflow-hidden">
                        <img src="<?= $logoUrl ?>" alt="Logo" class="max-h-full max-w-full object-contain">
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-display font-black text-white text-base sm:text-lg tracking-wide uppercase leading-tight group-hover:text-rose-200 transition">
                            <?= htmlspecialchars($appSettings['nama_perusahaan']) ?>
                        </span>
                        <span class="hidden sm:inline-block text-[9px] font-black px-2 py-0.5 rounded-full bg-rose-950/80 text-rose-400 border border-rose-800/60 uppercase tracking-wider">
                            Live Board
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 font-medium">Papan Informasi Kehadiran & Jadwal Cuti Karyawan</p>
                </div>
            </a>

            <!-- Right: Realtime Digital Clock & Login Action -->
            <div class="flex items-center gap-3 sm:gap-4">
                
                <!-- Live Clock Pill -->
                <div class="hidden md:flex items-center gap-2.5 px-3.5 py-1.5 rounded-2xl bg-slate-800/60 border border-slate-700/60 text-xs text-slate-300 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="font-bold text-slate-200"><?= $dayNameIndo ?>, <?= $todayFormatted ?></span>
                    <span class="text-slate-600">&bull;</span>
                    <span id="liveDigitalClock" class="font-mono font-black text-emerald-400">00:00:00 WIB</span>
                </div>

                <!-- Action Button -->
                <?php if ($currentUser): ?>
                    <a href="<?= BASE_URL ?>/index.php?page=dashboard" 
                       class="px-4 sm:px-5 py-2.5 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-xs shadow-lg shadow-blue-600/30 transition transform hover:-translate-y-0.5 flex items-center gap-2">
                        <i class="fa-solid fa-gauge-high"></i>
                        <span>Dashboard Saya</span>
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/index.php?page=login" 
                       class="px-4 sm:px-5 py-2.5 rounded-2xl bg-gradient-to-r from-rose-600 via-red-600 to-rose-700 hover:from-rose-500 hover:to-red-500 text-white font-extrabold text-xs shadow-lg shadow-rose-600/30 transition transform hover:-translate-y-0.5 flex items-center gap-2">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <span>Masuk ke Akun</span>
                    </a>
                <?php endif; ?>

            </div>

        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-8 py-6 sm:py-8 space-y-7 relative z-10">

        <!-- Top Welcome Banner & Date Header -->
        <div class="rounded-3xl bg-gradient-to-r from-slate-900/95 via-[#111827]/95 to-slate-900/95 border border-slate-800 p-6 sm:p-7 shadow-2xl relative overflow-hidden backdrop-blur-xl">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 relative z-10">
                <div class="space-y-1.5">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-bold">
                        <i class="fa-solid fa-calendar-day"></i>
                        <span>Rekap Status Kehadiran Hari Ini</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-black font-display text-white tracking-tight">
                        Daftar Karyawan Tidak Hadir / Cuti Hari Ini
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-400 font-medium">
                        <?= $dayNameIndo ?>, <?= $todayFormatted ?> &bull; Data tersinkronisasi otomatis dengan permohonan cuti resmi.
                    </p>
                </div>

                <div class="flex items-center gap-2 text-xs">
                    <button type="button" onclick="window.location.reload()" 
                            class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold border border-slate-700 transition flex items-center gap-1.5 shadow-2xs">
                        <i class="fa-solid fa-rotate text-blue-400"></i>
                        <span>Segarkan Data</span>
                    </button>
                    <div class="px-3 py-2 rounded-xl bg-slate-800/60 border border-slate-700/60 text-slate-400 text-[11px] font-mono">
                        Auto-Refresh: <strong id="refreshTimer" class="text-emerald-400 font-black">60s</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 Top Bento Metric Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            
            <!-- Card 1: Total Karyawan Aktif -->
            <div class="bg-gradient-to-b from-slate-800/70 to-slate-900/80 rounded-3xl p-5 border border-slate-700/60 shadow-lg backdrop-blur-md relative overflow-hidden group hover:border-slate-600 transition">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <span class="text-xs font-extrabold text-slate-400 uppercase tracking-wider">Total Karyawan</span>
                    <div class="w-10 h-10 rounded-2xl bg-blue-500/15 text-blue-400 flex items-center justify-center text-base border border-blue-500/20">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <div class="text-2xl sm:text-3xl font-black font-display text-white"><?= $totalKaryawan ?></div>
                <div class="text-[11px] text-slate-400 mt-1 font-medium">Karyawan Terdaftar Aktif</div>
            </div>

            <!-- Card 2: Hadir Hari Ini -->
            <div class="bg-gradient-to-b from-slate-800/70 to-slate-900/80 rounded-3xl p-5 border border-emerald-500/30 shadow-lg backdrop-blur-md relative overflow-hidden group hover:border-emerald-500/50 transition">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <span class="text-xs font-extrabold text-emerald-400 uppercase tracking-wider">Hadir Bekerja</span>
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center text-base border border-emerald-500/30">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
                <div class="text-2xl sm:text-3xl font-black font-display text-emerald-400"><?= $countHadirHariIni ?></div>
                <div class="text-[11px] text-slate-400 mt-1 font-medium">Bekerja di pabrik / kantor</div>
            </div>

            <!-- Card 3: Cuti / Izin Hari Ini -->
            <div class="bg-gradient-to-b from-slate-800/70 to-slate-900/80 rounded-3xl p-5 border border-rose-500/30 shadow-lg backdrop-blur-md relative overflow-hidden group hover:border-rose-500/50 transition">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <span class="text-xs font-extrabold text-rose-400 uppercase tracking-wider">Cuti / Izin Hari Ini</span>
                    <div class="w-10 h-10 rounded-2xl bg-rose-500/15 text-rose-400 flex items-center justify-center text-base border border-rose-500/30 animate-pulse">
                        <i class="fa-solid fa-user-xmark"></i>
                    </div>
                </div>
                <div class="text-2xl sm:text-3xl font-black font-display text-rose-400"><?= $countCutiHariIni ?></div>
                <div class="text-[11px] text-slate-400 mt-1 font-medium"><?= $countTahunanHariIni ?> Cuti &bull; <?= $countIzinHariIni ?> Izin Lainnya</div>
            </div>

            <!-- Card 4: Izin Sakit Hari Ini -->
            <div class="bg-gradient-to-b from-slate-800/70 to-slate-900/80 rounded-3xl p-5 border border-amber-500/30 shadow-lg backdrop-blur-md relative overflow-hidden group hover:border-amber-500/50 transition">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <span class="text-xs font-extrabold text-amber-400 uppercase tracking-wider">Izin Sakit</span>
                    <div class="w-10 h-10 rounded-2xl bg-amber-500/15 text-amber-400 flex items-center justify-center text-base border border-amber-500/30">
                        <i class="fa-solid fa-head-side-cough"></i>
                    </div>
                </div>
                <div class="text-2xl sm:text-3xl font-black font-display text-amber-400"><?= $countSakitHariIni ?></div>
                <div class="text-[11px] text-slate-400 mt-1 font-medium">Surat Dokter & Rawat Jalan</div>
            </div>

        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-slate-900/90 rounded-3xl p-4 sm:p-5 border border-slate-800 shadow-xl flex flex-col md:flex-row items-center justify-between gap-4 backdrop-blur-md">
            
            <!-- Search Box -->
            <div class="relative w-full md:w-80">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
                <input type="text" id="filterKeyword" oninput="filterLeaveCards()" placeholder="Cari nama karyawan / NIK..."
                       class="w-full pl-9 pr-4 py-2.5 rounded-2xl bg-slate-800/80 border border-slate-700 text-white placeholder-slate-400 text-xs font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition">
            </div>

            <!-- Filter Controls -->
            <div class="flex items-center gap-3 w-full md:w-auto flex-wrap">
                <!-- Dept Filter -->
                <select id="filterDept" onchange="filterLeaveCards()" 
                        class="px-3.5 py-2.5 rounded-2xl bg-slate-800/80 border border-slate-700 text-slate-200 text-xs font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/20">
                    <option value="">-- Semua Departemen --</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= htmlspecialchars($d['nama_dept']) ?>"><?= htmlspecialchars($d['nama_dept']) ?></option>
                    <?php endforeach; ?>
                </select>

                <!-- Category Filter -->
                <select id="filterCategory" onchange="filterLeaveCards()" 
                        class="px-3.5 py-2.5 rounded-2xl bg-slate-800/80 border border-slate-700 text-slate-200 text-xs font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/20">
                    <option value="">-- Semua Kategori Cuti --</option>
                    <option value="Tahunan">Cuti Tahunan</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Melahirkan">Melahirkan</option>
                    <option value="Haid">Haid</option>
                    <option value="Khusus">Cuti Khusus</option>
                    <option value="Ijin">Izin Potong Gaji</option>
                </select>

                <!-- Display Mode Toggle -->
                <div class="inline-flex p-1 rounded-xl bg-slate-800 border border-slate-700 text-xs">
                    <button type="button" id="btnViewGrid" onclick="setViewMode('grid')" class="px-2.5 py-1.5 rounded-lg bg-blue-600 text-white font-bold transition shadow-xs" title="Tampilan Kotak Grid">
                        <i class="fa-solid fa-grip"></i>
                    </button>
                    <button type="button" id="btnViewTable" onclick="setViewMode('table')" class="px-2.5 py-1.5 rounded-lg text-slate-400 hover:text-white transition" title="Tampilan Tabel Rinci">
                        <i class="fa-solid fa-list"></i>
                    </button>
                </div>
            </div>

        </div>

        <!-- 5. Main Today's Leaves Display Area -->
        <div id="leavesContainer">
            
            <?php if (empty($todayLeaves)): ?>
                <!-- Empty State: All Present -->
                <div class="rounded-3xl bg-slate-900/80 border border-slate-800 p-10 sm:p-14 text-center space-y-4 shadow-xl">
                    <div class="w-16 h-16 rounded-3xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center text-3xl mx-auto border border-emerald-500/20 shadow-lg shadow-emerald-500/10">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-lg sm:text-xl font-black font-display text-white">Semua Karyawan Hadir Hari Ini!</h3>
                        <p class="text-xs sm:text-sm text-slate-400 max-w-md mx-auto">
                            Tidak ada karyawan yang tercatat sedang menjalani masa cuti atau izin sakit pada hari <?= $dayNameIndo ?>, <?= $todayFormatted ?>.
                        </p>
                    </div>
                </div>
            <?php else: ?>

                <!-- Grid Cards View -->
                <div id="gridViewArea" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                    <?php foreach ($todayLeaves as $leave): 
                        $avatarLetter = strtoupper(substr($leave['nama_lengkap'], 0, 1));
                        $isSakit = stripos($leave['nama_cuti'], 'Sakit') !== false;
                        $cardBorder = $isSakit ? 'border-amber-500/40 hover:border-amber-500/70' : 'border-slate-800 hover:border-blue-500/60';
                    ?>
                        <div class="leave-card bg-gradient-to-b from-slate-900/90 to-[#0c1222]/95 rounded-3xl p-5 border <?= $cardBorder ?> shadow-xl hover:shadow-2xl transition-all duration-300 space-y-4 group backdrop-blur-md"
                             data-name="<?= strtolower($leave['nama_lengkap']) ?>"
                             data-nik="<?= strtolower($leave['nik']) ?>"
                             data-dept="<?= htmlspecialchars($leave['nama_dept']) ?>"
                             data-type="<?= htmlspecialchars($leave['nama_cuti']) ?>">
                            
                            <!-- Card Header: User Profile -->
                            <div class="flex items-center gap-3.5 border-b border-slate-800/80 pb-3.5">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-purple-600 text-white font-black text-lg flex items-center justify-center shadow-md shadow-blue-600/20 flex-shrink-0 group-hover:scale-105 transition">
                                    <?= $avatarLetter ?>
                                </div>
                                <div class="overflow-hidden">
                                    <h4 class="text-sm font-extrabold text-white truncate group-hover:text-blue-300 transition" title="<?= htmlspecialchars($leave['nama_lengkap']) ?>">
                                        <?= htmlspecialchars($leave['nama_lengkap']) ?>
                                    </h4>
                                    <div class="text-[11px] text-slate-400 font-mono flex items-center gap-1.5 mt-0.5">
                                        <span>NIK: <strong class="text-slate-200"><?= htmlspecialchars($leave['nik']) ?></strong></span>
                                        <span>&bull;</span>
                                        <span class="text-blue-400 font-sans font-semibold"><?= htmlspecialchars($leave['nama_dept']) ?></span>
                                    </div>
                                    <div class="text-[10.5px] text-slate-400 truncate mt-0.5"><?= htmlspecialchars($leave['nama_jabatan']) ?></div>
                                </div>
                            </div>

                            <!-- Leave Details -->
                            <div class="space-y-2.5 text-xs">
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Jenis Cuti / Izin:</span>
                                    <div class="font-extrabold text-white text-xs flex items-center gap-1.5">
                                        <i class="fa-solid fa-tag text-rose-400 text-xs"></i>
                                        <span><?= htmlspecialchars($leave['nama_cuti']) ?></span>
                                    </div>
                                </div>

                                <div class="p-3 rounded-2xl bg-slate-800/50 border border-slate-800 space-y-1.5">
                                    <div class="flex justify-between items-center text-[11px]">
                                        <span class="text-slate-400 font-medium">Periode Cuti:</span>
                                        <span class="font-bold text-slate-200"><?= formatTanggalIndo($leave['tanggal_mulai']) ?> s/d <?= formatTanggalIndo($leave['tanggal_selesai']) ?></span>
                                    </div>
                                    <div class="flex justify-between items-center text-[11px]">
                                        <span class="text-slate-400 font-medium">Durasi:</span>
                                        <span class="px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-300 border border-blue-400/30 font-black text-[10.5px]">
                                            <?= $leave['total_hari'] ?> Hari Kerja
                                        </span>
                                    </div>
                                </div>

                                <?php if (!empty($leave['alasan'])): ?>
                                    <div class="text-[11px] text-slate-400 line-clamp-2 bg-slate-800/30 p-2.5 rounded-xl border border-slate-800/50" title="<?= htmlspecialchars($leave['alasan']) ?>">
                                        <i class="fa-solid fa-comment-dots text-slate-400 mr-1"></i> <?= htmlspecialchars($leave['alasan']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Card Footer: Status Badge -->
                            <div class="flex items-center justify-between pt-1 border-t border-slate-800/60 text-[11px]">
                                <span class="text-slate-400 font-mono text-[10px]"><?= htmlspecialchars($leave['nomor_surat']) ?></span>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 font-extrabold text-[10.5px]">
                                    <i class="fa-solid fa-circle-check text-[9px]"></i> Disetujui
                                </span>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Table View (Hidden by default) -->
                <div id="tableViewArea" class="hidden bg-slate-900 rounded-3xl border border-slate-800 shadow-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-300">
                            <thead class="bg-slate-800/80 text-slate-400 uppercase tracking-wider text-[10.5px] border-b border-slate-800">
                                <tr>
                                    <th class="p-4 text-center w-12">No</th>
                                    <th class="p-4">Karyawan</th>
                                    <th class="p-4">Departemen & Jabatan</th>
                                    <th class="p-4">Jenis Cuti</th>
                                    <th class="p-4">Periode</th>
                                    <th class="p-4 text-center">Durasi</th>
                                    <th class="p-4">Alasan</th>
                                    <th class="p-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/70">
                                <?php $n = 1; foreach ($todayLeaves as $leave): ?>
                                    <tr class="leave-card hover:bg-slate-800/40 transition"
                                        data-name="<?= strtolower($leave['nama_lengkap']) ?>"
                                        data-nik="<?= strtolower($leave['nik']) ?>"
                                        data-dept="<?= htmlspecialchars($leave['nama_dept']) ?>"
                                        data-type="<?= htmlspecialchars($leave['nama_cuti']) ?>">
                                        <td class="p-4 text-center font-bold text-slate-400"><?= $n++ ?></td>
                                        <td class="p-4">
                                            <div class="font-extrabold text-white text-xs"><?= htmlspecialchars($leave['nama_lengkap']) ?></div>
                                            <div class="text-[10.5px] text-slate-400 font-mono">NIK: <?= htmlspecialchars($leave['nik']) ?></div>
                                        </td>
                                        <td class="p-4">
                                            <div class="font-bold text-blue-300"><?= htmlspecialchars($leave['nama_dept']) ?></div>
                                            <div class="text-[10.5px] text-slate-400"><?= htmlspecialchars($leave['nama_jabatan']) ?></div>
                                        </td>
                                        <td class="p-4 font-bold text-white"><?= htmlspecialchars($leave['nama_cuti']) ?></td>
                                        <td class="p-4 whitespace-nowrap">
                                            <?= formatTanggalIndo($leave['tanggal_mulai']) ?> s/d <?= formatTanggalIndo($leave['tanggal_selesai']) ?>
                                        </td>
                                        <td class="p-4 text-center whitespace-nowrap">
                                            <span class="px-2.5 py-0.5 rounded-full bg-blue-500/20 text-blue-300 font-bold border border-blue-400/30">
                                                <?= $leave['total_hari'] ?> Hari
                                            </span>
                                        </td>
                                        <td class="p-4 max-w-[200px] truncate text-slate-400"><?= htmlspecialchars($leave['alasan']) ?></td>
                                        <td class="p-4 text-center whitespace-nowrap">
                                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 font-bold border border-emerald-500/30 text-[10.5px]">
                                                Disetujui
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php endif; ?>

        </div>

        <!-- 6. Upcoming Leaves Preview (Next 7 Days) -->
        <?php if (!empty($upcomingLeaves)): ?>
            <div class="bg-slate-900/80 rounded-3xl p-6 border border-slate-800 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center text-xs font-bold border border-purple-500/30">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-white">Jadwal Cuti Mendatang (7 Hari Ke Depan)</h3>
                            <p class="text-[11px] text-slate-400">Informasi awal untuk perencanaan shift dan operasional kerja</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <?php foreach ($upcomingLeaves as $up): ?>
                        <div class="p-3 rounded-2xl bg-slate-800/50 border border-slate-800 flex items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-slate-700 text-white font-bold flex items-center justify-center text-xs flex-shrink-0">
                                    <?= strtoupper(substr($up['nama_lengkap'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="font-extrabold text-white truncate max-w-[150px]"><?= htmlspecialchars($up['nama_lengkap']) ?></div>
                                    <div class="text-[10px] text-slate-400"><?= htmlspecialchars($up['nama_dept']) ?> &bull; <span class="text-blue-300"><?= htmlspecialchars($up['nama_cuti']) ?></span></div>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <div class="text-[10.5px] font-bold text-purple-300"><?= date('d M', strtotime($up['tanggal_mulai'])) ?></div>
                                <div class="text-[9.5px] text-slate-400"><?= $up['total_hari'] ?> Hari</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <!-- Public Footer -->
    <footer class="mt-auto border-t border-slate-800 bg-[#090e1a]/90 backdrop-blur py-5 px-4 sm:px-8 text-xs text-slate-400 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-center sm:text-left flex-wrap justify-center">
            <span>&copy; <?= date('Y') ?></span>
            <strong class="text-white font-bold"><?= htmlspecialchars($appSettings['nama_perusahaan']) ?></strong>
            <span class="text-slate-600">&bull;</span>
            <span><?= htmlspecialchars($appSettings['footer_text']) ?></span>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-[11px] text-slate-400 font-mono">Papan Informasi Publik Live</span>
            <?php if (!$currentUser): ?>
                <a href="<?= BASE_URL ?>/index.php?page=login" class="text-blue-400 hover:text-blue-300 font-bold flex items-center gap-1 transition">
                    <i class="fa-solid fa-lock text-[10px]"></i> Login Admin / Karyawan
                </a>
            <?php endif; ?>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Real-time Digital Clock
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const clockEl = document.getElementById('liveDigitalClock');
            if (clockEl) {
                clockEl.textContent = `${hours}:${minutes}:${seconds} WIB`;
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        // 60-Second Auto Refresh Timer for TV Displays
        let countdown = 60;
        setInterval(function() {
            countdown--;
            const timerEl = document.getElementById('refreshTimer');
            if (timerEl) {
                timerEl.textContent = countdown + 's';
            }
            if (countdown <= 0) {
                window.location.reload();
            }
        }, 1000);

        // Filter Functionality
        function filterLeaveCards() {
            const keyword = document.getElementById('filterKeyword').value.toLowerCase().trim();
            const dept = document.getElementById('filterDept').value.toLowerCase();
            const category = document.getElementById('filterCategory').value.toLowerCase();

            const cards = document.querySelectorAll('.leave-card');
            cards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                const nik = card.getAttribute('data-nik') || '';
                const cardDept = (card.getAttribute('data-dept') || '').toLowerCase();
                const type = (card.getAttribute('data-type') || '').toLowerCase();

                const matchKeyword = !keyword || name.includes(keyword) || nik.includes(keyword);
                const matchDept = !dept || cardDept === dept;
                const matchCategory = !category || type.includes(category);

                if (matchKeyword && matchDept && matchCategory) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // View Mode Switcher
        function setViewMode(mode) {
            const gridArea = document.getElementById('gridViewArea');
            const tableArea = document.getElementById('tableViewArea');
            const btnGrid = document.getElementById('btnViewGrid');
            const btnTable = document.getElementById('btnViewTable');

            if (mode === 'grid') {
                if (gridArea) gridArea.classList.remove('hidden');
                if (tableArea) tableArea.classList.add('hidden');
                if (btnGrid) {
                    btnGrid.className = 'px-2.5 py-1.5 rounded-lg bg-blue-600 text-white font-bold transition shadow-xs';
                }
                if (btnTable) {
                    btnTable.className = 'px-2.5 py-1.5 rounded-lg text-slate-400 hover:text-white transition';
                }
            } else {
                if (gridArea) gridArea.classList.add('hidden');
                if (tableArea) tableArea.classList.remove('hidden');
                if (btnTable) {
                    btnTable.className = 'px-2.5 py-1.5 rounded-lg bg-blue-600 text-white font-bold transition shadow-xs';
                }
                if (btnGrid) {
                    btnGrid.className = 'px-2.5 py-1.5 rounded-lg text-slate-400 hover:text-white transition';
                }
            }
        }
    </script>

</body>
</html>
