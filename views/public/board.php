<?php
/**
 * Public & In-App Today's Attendance & Leave Board
 * PT. Nakakin Indonesia Leave Management System
 * 100% Parity with Flutter PublicBoardScreen (Mobile) & Fullscreen TV Display (Desktop/TV)
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
$isTvMode = ($_GET['view'] ?? '') === 'tv';

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
$tingkatKehadiran = $totalKaryawan > 0 ? round(($countHadirHariIni / $totalKaryawan) * 100, 1) : 100;
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

// IF RAW TV MODE (Fullscreen dark dashboard for TV screens)
if ($isTvMode):
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TV Display &bull; Papan Kehadiran Live | <?= htmlspecialchars($appSettings['nama_perusahaan']) ?></title>
    <link rel="icon" type="image/png" href="<?= $favUrl ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="min-h-full bg-[#0a0f1d] text-slate-100 font-sans antialiased flex flex-col p-6 sm:p-8 space-y-6">
    <header class="flex items-center justify-between border-b border-slate-800 pb-5">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-rose-500 to-red-700 p-0.5 shadow-lg shadow-rose-600/30 flex items-center justify-center">
                <div class="w-full h-full bg-[#0a0f1d] rounded-[14px] flex items-center justify-center p-1.5">
                    <img src="<?= $logoUrl ?>" alt="Logo" class="max-h-full max-w-full object-contain">
                </div>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-white"><?= htmlspecialchars($appSettings['nama_perusahaan']) ?></h1>
                <p class="text-xs text-slate-400 font-medium">Papan Informasi Kehadiran & Cuti Karyawan (Live TV Display)</p>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="px-4 py-2 rounded-2xl bg-slate-800/80 border border-slate-700/80 text-xs font-mono">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse inline-block mr-2"></span>
                <span id="tvClock" class="font-bold text-emerald-400">00:00:00 WIB</span>
            </div>
            <div class="px-3 py-2 rounded-2xl bg-slate-800/60 border border-slate-700/60 text-slate-400 text-xs font-mono">
                Auto-Refresh: <strong id="tvRefreshTimer" class="text-emerald-400 font-bold">60s</strong>
            </div>
        </div>
    </header>

    <!-- TV 4 Stats Cards -->
    <div class="grid grid-cols-4 gap-5">
        <div class="bg-slate-800/70 rounded-3xl p-5 border border-slate-700/60">
            <div class="text-xs font-extrabold text-slate-400 uppercase">Total Karyawan</div>
            <div class="text-3xl font-black text-white mt-1"><?= $totalKaryawan ?></div>
        </div>
        <div class="bg-slate-800/70 rounded-3xl p-5 border border-emerald-500/30">
            <div class="text-xs font-extrabold text-emerald-400 uppercase">Hadir Bekerja</div>
            <div class="text-3xl font-black text-emerald-400 mt-1"><?= $countHadirHariIni ?></div>
        </div>
        <div class="bg-slate-800/70 rounded-3xl p-5 border border-rose-500/30">
            <div class="text-xs font-extrabold text-rose-400 uppercase">Cuti / Izin</div>
            <div class="text-3xl font-black text-rose-400 mt-1"><?= $countCutiHariIni ?></div>
        </div>
        <div class="bg-slate-800/70 rounded-3xl p-5 border border-purple-500/30">
            <div class="text-xs font-extrabold text-purple-400 uppercase">Tingkat Kehadiran</div>
            <div class="text-3xl font-black text-purple-400 mt-1"><?= $tingkatKehadiran ?>%</div>
        </div>
    </div>

    <!-- TV Leaves Grid -->
    <div class="flex-1">
        <?php if (empty($todayLeaves)): ?>
            <div class="rounded-3xl bg-slate-800/50 border border-slate-800 p-14 text-center space-y-3">
                <div class="text-emerald-400 text-4xl"><i class="fa-solid fa-circle-check"></i></div>
                <h3 class="text-xl font-bold text-white">Semua Karyawan Hadir Lengkap!</h3>
                <p class="text-xs text-slate-400">Tidak ada catatan cuti/izin aktif hari ini.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-3 gap-4">
                <?php foreach ($todayLeaves as $leave): ?>
                    <div class="p-4 rounded-2xl bg-slate-800/70 border border-slate-700/60 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-white text-sm"><?= htmlspecialchars($leave['nama_lengkap']) ?></span>
                            <span class="px-2 py-0.5 rounded bg-rose-500/20 text-rose-300 font-bold text-xs"><?= htmlspecialchars($leave['kode_cuti'] ?: 'CUTI') ?></span>
                        </div>
                        <div class="text-xs text-slate-400"><?= htmlspecialchars($leave['nik']) ?> &bull; <?= htmlspecialchars($leave['nama_dept']) ?></div>
                        <div class="text-xs font-semibold text-blue-400"><?= date('d/m/Y', strtotime($leave['tanggal_mulai'])) ?> - <?= date('d/m/Y', strtotime($leave['tanggal_selesai'])) ?> (<?= $leave['total_hari'] ?> hari)</div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function updateTvClock() {
            const now = new Date();
            const el = document.getElementById('tvClock');
            if (el) el.textContent = now.toLocaleTimeString('id-ID') + ' WIB';
        }
        setInterval(updateTvClock, 1000);
        updateTvClock();

        let countdown = 60;
        setInterval(function() {
            countdown--;
            const timerEl = document.getElementById('tvRefreshTimer');
            if (timerEl) timerEl.textContent = countdown + 's';
            if (countdown <= 0) window.location.reload();
        }, 1000);
    </script>
</body>
</html>
<?php 
    exit;
endif;

// =========================================================================
// NORMAL / MOBILE / DESKTOP VIEW (FOR BOTH LOGGED IN & PUBLIC GUESTS)
// =========================================================================
if ($currentUser) {
    $pageTitle = 'Papan Kehadiran Hari Ini';
    require_once __DIR__ . '/../layouts/header.php';
} else {
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50 dark:bg-[#0F172A]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Papan Kehadiran Hari Ini &bull; <?= htmlspecialchars($appSettings['nama_perusahaan'] ?: 'PT. Nakakin Indonesia') ?></title>
    <link rel="icon" type="image/png" href="<?= $favUrl ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('theme');
                if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {}
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        .material-symbols-rounded {
            font-family: 'Material Symbols Rounded', sans-serif;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            vertical-align: middle;
            line-height: 1;
        }
        .material-symbols-rounded.filled {
            font-variation-settings: 'FILL' 1, 'wght' 600, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="min-h-full bg-[#f8fafc] dark:bg-[#0F172A] font-sans antialiased text-slate-800 dark:text-slate-100 flex flex-col">

    <!-- Standalone Header for Public Visitors (Matches Flutter PublicBoardScreen AppBar) -->
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-xl border-b border-slate-200/80 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="<?= BASE_URL ?>/index.php?page=login" 
               class="p-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-slate-100 transition flex items-center justify-center -ml-1" 
               title="Kembali ke Login">
                <span class="material-symbols-rounded text-xl">arrow_back</span>
            </a>
            <div>
                <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight leading-tight">
                    Papan Kehadiran Hari Ini
                </h1>
                <p class="text-[11px] text-slate-400 font-medium hidden sm:block">PT. Nakakin Indonesia &bull; Live Board</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="location.reload()" 
                    class="p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition flex items-center justify-center" 
                    title="Segarkan Data">
                <span class="material-symbols-rounded text-xl">refresh</span>
            </button>
            <a href="<?= BASE_URL ?>/index.php?page=login" 
               class="px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5">
                <span class="material-symbols-rounded text-base">login</span>
                <span>Masuk</span>
            </a>
        </div>
    </header>

    <main class="flex-1 p-3.5 sm:p-6 max-w-5xl w-full mx-auto pb-12">
<?php } ?>

<!-- Main Board Content (Unified for Mobile & Desktop) -->
<div class="space-y-4 lg:space-y-6">

    <!-- ========================================================================= -->
    <!-- 1. MOBILE VIEW (Screen < 1024px) - 100% PARITY WITH FLUTTER PublicBoardScreen -->
    <!-- ========================================================================= -->
    <div class="block lg:hidden space-y-4">
        
        <!-- A. Live Clock / Date Card (Flutter Gradient Container) -->
        <div class="p-4 rounded-2xl bg-gradient-to-br from-[#1E3A8A] via-[#1E293B] to-[#0F172A] text-white shadow-md shadow-blue-950/30">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-full bg-white/15 flex items-center justify-center text-lg text-white flex-shrink-0">
                    <span class="material-symbols-rounded text-2xl">calendar_today</span>
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-bold text-white/70 tracking-widest uppercase">STATUS KEHADIRAN LIVE</div>
                    <div class="text-sm font-bold text-white leading-tight truncate mt-0.5">
                        <?= $dayNameIndo ?>, <?= $todayFormatted ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- B. Stats Summary Row (3 Bento Stat Boxes: Hadir, Cuti/Izin, % Kehadiran) -->
        <div class="grid grid-cols-3 gap-2.5">
            <!-- Box 1: Hadir -->
            <div class="p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs text-center flex flex-col items-center justify-center">
                <div class="text-emerald-500 text-xl mb-1">
                    <span class="material-symbols-rounded text-emerald-500">check_circle</span>
                </div>
                <div class="text-base font-black text-emerald-600 dark:text-emerald-400 leading-tight">
                    <?= $countHadirHariIni ?>
                </div>
                <div class="text-[10.5px] text-slate-500 dark:text-slate-400 font-semibold mt-0.5">
                    Hadir
                </div>
            </div>

            <!-- Box 2: Cuti / Izin -->
            <div class="p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs text-center flex flex-col items-center justify-center">
                <div class="text-amber-500 text-xl mb-1">
                    <span class="material-symbols-rounded text-amber-500">beach_access</span>
                </div>
                <div class="text-base font-black text-amber-600 dark:text-amber-400 leading-tight">
                    <?= $countCutiHariIni ?>
                </div>
                <div class="text-[10.5px] text-slate-500 dark:text-slate-400 font-semibold mt-0.5">
                    Cuti / Izin
                </div>
            </div>

            <!-- Box 3: % Kehadiran -->
            <div class="p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs text-center flex flex-col items-center justify-center">
                <div class="text-blue-500 text-xl mb-1">
                    <span class="material-symbols-rounded text-blue-500">pie_chart</span>
                </div>
                <div class="text-base font-black text-blue-600 dark:text-blue-400 leading-tight">
                    <?= $tingkatKehadiran ?>%
                </div>
                <div class="text-[10.5px] text-slate-500 dark:text-slate-400 font-semibold mt-0.5">
                    % Kehadiran
                </div>
            </div>
        </div>

        <!-- C. Department ChoiceChips (Horizontal Scrollable) -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar text-xs">
            <button type="button" onclick="filterBoardByDept(this, '')" 
                    class="board-dept-chip px-3 py-1.5 rounded-full font-bold whitespace-nowrap transition bg-blue-600 text-white shadow-2xs">
                Semua Dept
            </button>
            <?php foreach ($departments as $dept): ?>
                <button type="button" onclick="filterBoardByDept(this, '<?= strtolower(htmlspecialchars($dept['nama_dept'])) ?>')" 
                        class="board-dept-chip px-3 py-1.5 rounded-full font-semibold whitespace-nowrap transition bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700 shadow-2xs">
                    <?= htmlspecialchars($dept['nama_dept']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- D. Section Title: Karyawan Cuti Hari Ini -->
        <div class="flex items-center justify-between pt-1">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                Karyawan Cuti Hari Ini (<span id="mobileLeaveCount"><?= count($todayLeaves) ?></span>)
            </h3>
            <button type="button" onclick="location.reload()" class="text-xs text-blue-600 dark:text-blue-400 font-bold flex items-center gap-1">
                <span class="material-symbols-rounded text-sm">refresh</span> Segarkan
            </button>
        </div>

        <!-- E. Employee Leave Cards List -->
        <div id="mobileBoardList" class="space-y-3">
            <?php if (empty($todayLeaves)): ?>
                <!-- Empty State (Matching Flutter) -->
                <div id="mobileEmptyState" class="p-7 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-center space-y-2 shadow-2xs">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl mx-auto">
                        <span class="material-symbols-rounded text-3xl text-emerald-500">sentiment_satisfied</span>
                    </div>
                    <div class="text-sm font-bold text-slate-900 dark:text-white">Semua Karyawan Hadir Lengkap!</div>
                    <div class="text-xs text-slate-400">Tidak ada catatan cuti/izin aktif untuk hari ini.</div>
                </div>
            <?php else: ?>
                <div id="mobileEmptyState" class="hidden p-7 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-center space-y-2 shadow-2xs">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl mx-auto">
                        <span class="material-symbols-rounded text-3xl text-emerald-500">sentiment_satisfied</span>
                    </div>
                    <div class="text-sm font-bold text-slate-900 dark:text-white">Semua Karyawan Hadir Lengkap!</div>
                    <div class="text-xs text-slate-400">Tidak ada karyawan di departemen ini yang sedang cuti.</div>
                </div>

                <?php foreach ($todayLeaves as $leave): 
                    $durasiText = ($leave['total_hari'] == 0.5) ? '0.5 hari' : $leave['total_hari'] . ' hari';
                ?>
                    <div class="board-mobile-card p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-2.5 transition"
                         data-dept="<?= strtolower(htmlspecialchars($leave['nama_dept'])) ?>"
                         data-name="<?= strtolower(htmlspecialchars($leave['nama_lengkap'])) ?>">
                        
                        <div class="flex items-start gap-3">
                            <div class="w-11 h-11 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 font-bold text-sm flex items-center justify-center flex-shrink-0 border border-blue-100 dark:border-blue-900">
                                <?= strtoupper(substr($leave['nama_lengkap'], 0, 1)) ?>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="text-xs font-bold text-slate-900 dark:text-white truncate">
                                        <?= htmlspecialchars($leave['nama_lengkap']) ?>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 font-extrabold text-[10px] border border-amber-200/80 dark:border-amber-800 flex-shrink-0">
                                        <?= htmlspecialchars($leave['kode_cuti'] ?: 'CUTI') ?>
                                    </span>
                                </div>

                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    <?= htmlspecialchars($leave['nik']) ?> &bull; <?= htmlspecialchars($leave['nama_dept']) ?>
                                </div>

                                <div class="text-xs font-semibold text-blue-600 dark:text-blue-400 mt-1.5">
                                    <?= $leave['tanggal_mulai'] ?> s/d <?= $leave['tanggal_selesai'] ?> (<?= $durasiText ?>)
                                </div>

                                <?php if (!empty($leave['alasan'])): ?>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">
                                        Alasan: <?= htmlspecialchars($leave['alasan']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 2. DESKTOP IN-APP VIEW (Screen >= 1024px) -->
    <!-- ========================================================================= -->
    <div class="hidden lg:block space-y-6">
        
        <!-- Header Actions & TV Mode Link -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-3xl p-6 text-white border border-slate-700/60 shadow-xl flex items-center justify-between">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Papan Informasi Kehadiran Perusahaan</span>
                </div>
                <h2 class="text-xl font-black text-white">Status Kehadiran & Cuti Hari Ini</h2>
                <p class="text-xs text-slate-400"><?= $dayNameIndo ?>, <?= $todayFormatted ?> &bull; Data tersinkronisasi otomatis</p>
            </div>
            <a href="<?= BASE_URL ?>/index.php?page=board&view=tv" target="_blank" 
               class="px-5 py-3 rounded-2xl bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-extrabold text-xs shadow-lg shadow-rose-600/30 flex items-center gap-2 transition transform active:scale-95">
                <span class="material-symbols-rounded text-lg">tv</span>
                <span>Buka Mode TV Fullscreen</span>
            </a>
        </div>

        <!-- 4 Bento Metric Stat Cards -->
        <div class="grid grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-2xs flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl font-bold border border-blue-100 dark:border-blue-900 shadow-2xs">
                    <span class="material-symbols-rounded text-2xl text-blue-600 dark:text-blue-400">group</span>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Karyawan</div>
                    <div class="text-xl font-black text-slate-900 dark:text-white leading-tight"><?= $totalKaryawan ?> <span class="text-xs font-semibold text-slate-500">Orang</span></div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-2xs flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl font-bold border border-emerald-100 dark:border-emerald-900 shadow-2xs">
                    <span class="material-symbols-rounded text-2xl text-emerald-600 dark:text-emerald-400">check_circle</span>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Hadir Bekerja</div>
                    <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 leading-tight"><?= $countHadirHariIni ?> <span class="text-xs font-semibold text-slate-500">Orang</span></div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-2xs flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl font-bold border border-amber-100 dark:border-amber-900 shadow-2xs">
                    <span class="material-symbols-rounded text-2xl text-amber-600 dark:text-amber-400">beach_access</span>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Cuti / Izin</div>
                    <div class="text-xl font-black text-amber-600 dark:text-amber-400 leading-tight"><?= $countCutiHariIni ?> <span class="text-xs font-semibold text-slate-500">Orang</span></div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-2xs flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl font-bold border border-purple-100 dark:border-purple-900 shadow-2xs">
                    <span class="material-symbols-rounded text-2xl text-purple-600 dark:text-purple-400">pie_chart</span>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tingkat Kehadiran</div>
                    <div class="text-xl font-black text-purple-600 dark:text-purple-400 leading-tight"><?= $tingkatKehadiran ?>%</div>
                </div>
            </div>
        </div>

        <!-- Table View of Today's Leaves -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="p-5 sm:px-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/40 dark:bg-slate-800/40">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center text-base font-bold border border-blue-200/60 dark:border-blue-900 shadow-2xs">
                        <span class="material-symbols-rounded text-2xl text-blue-600 dark:text-blue-400">schedule</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Daftar Karyawan Cuti & Izin Hari Ini</h3>
                        <p class="text-[11px] text-slate-400 font-medium">Data resmi karyawan yang sedang menjalani cuti aktif</p>
                    </div>
                </div>
                <span class="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-black border border-slate-200/60 dark:border-slate-700 shadow-2xs">
                    Total: <?= count($todayLeaves) ?> Karyawan
                </span>
            </div>

            <div class="p-4 sm:p-6 overflow-x-auto">
                <table class="w-full text-left text-xs datatable">
                    <thead>
                        <tr>
                            <th class="text-center w-12">No</th>
                            <th>Karyawan</th>
                            <th>Departemen & Jabatan</th>
                            <th>Jenis Cuti</th>
                            <th>Periode Cuti</th>
                            <th class="text-center">Durasi</th>
                            <th>Alasan</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($todayLeaves as $leave): ?>
                            <tr>
                                <td class="text-center">
                                    <span class="w-6 h-6 rounded-lg bg-slate-100 dark:bg-slate-800 inline-flex items-center justify-center text-[11px] text-slate-600 dark:text-slate-300 font-black">
                                        <?= $no++ ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 shadow-2xs">
                                            <?= strtoupper(substr($leave['nama_lengkap'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="font-extrabold text-slate-900 dark:text-white text-xs"><?= htmlspecialchars($leave['nama_lengkap']) ?></div>
                                            <div class="text-[11px] text-slate-400 font-mono">NIK: <strong class="text-slate-700 dark:text-slate-300"><?= htmlspecialchars($leave['nik']) ?></strong></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-extrabold text-slate-900 dark:text-white text-xs"><?= htmlspecialchars($leave['nama_dept']) ?></div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-0.5"><?= htmlspecialchars($leave['nama_jabatan']) ?></div>
                                </td>
                                <td>
                                    <span class="px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 font-extrabold text-[11px] border border-blue-200/60 dark:border-blue-900">
                                        <?= htmlspecialchars($leave['nama_cuti']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="font-bold text-slate-800 dark:text-slate-200"><?= date('d M Y', strtotime($leave['tanggal_mulai'])) ?> s/d <?= date('d M Y', strtotime($leave['tanggal_selesai'])) ?></div>
                                </td>
                                <td class="text-center whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-black text-[11px]">
                                        <?= $leave['total_hari'] ?> Hari
                                    </span>
                                </td>
                                <td class="max-w-[200px] truncate text-slate-500 dark:text-slate-400">
                                    <?= htmlspecialchars($leave['alasan']) ?>
                                </td>
                                <td class="text-center whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-black text-[10.5px] border border-emerald-200 dark:border-emerald-800">
                                        Disetujui
                                    </span>
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
let selectedBoardDept = '';

function filterBoardByDept(btnElement, deptName) {
    selectedBoardDept = deptName.toLowerCase().trim();

    // Update styling for choice chips
    document.querySelectorAll('.board-dept-chip').forEach(c => {
        c.className = 'board-dept-chip px-3 py-1.5 rounded-full font-semibold whitespace-nowrap transition bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700 shadow-2xs';
    });
    btnElement.className = 'board-dept-chip px-3 py-1.5 rounded-full font-bold whitespace-nowrap transition bg-blue-600 text-white shadow-2xs';

    let visibleCount = 0;
    const cards = document.querySelectorAll('.board-mobile-card');
    cards.forEach(card => {
        const cardDept = card.getAttribute('data-dept') || '';
        if (!selectedBoardDept || cardDept.includes(selectedBoardDept)) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    const countEl = document.getElementById('mobileLeaveCount');
    if (countEl) countEl.innerText = visibleCount;

    const emptyState = document.getElementById('mobileEmptyState');
    if (emptyState) {
        if (visibleCount === 0) emptyState.classList.remove('hidden');
        else emptyState.classList.add('hidden');
    }
}
</script>

<?php 
if ($currentUser) {
    require_once __DIR__ . '/../layouts/footer.php';
} else {
?>
    </main>
    <footer class="border-t border-slate-200 bg-white py-4 px-4 text-center text-xs text-slate-400">
        &copy; <?= date('Y') ?> <?= htmlspecialchars($appSettings['nama_perusahaan']) ?> &bull; Papan Informasi Kehadiran Publik
    </footer>
</body>
</html>
<?php } ?>
