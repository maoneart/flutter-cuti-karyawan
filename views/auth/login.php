<?php
/**
 * Modern Enterprise Login Page (Split-Screen Desktop & Mobile Responsive)
 * PT. Nakakin Indonesia Leave Management System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/index.php?page=dashboard');
    exit;
}

$flash = getFlash();
$appSettings = getAppSettings();
$loginLogoUrl = !empty($appSettings['logo']) ? BASE_URL . '/assets/images/' . $appSettings['logo'] : BASE_URL . '/assets/images/Nakakin.png';
$loginFavUrl = !empty($appSettings['favicon']) ? BASE_URL . '/assets/images/' . $appSettings['favicon'] : BASE_URL . '/assets/images/Nakakin.png';
$adminPhone = !empty($appSettings['telepon']) ? preg_replace('/[^0-9]/', '', $appSettings['telepon']) : '6281234567890';
if (substr($adminPhone, 0, 1) === '0') {
    $adminPhone = '62' . substr($adminPhone, 1);
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50 dark:bg-[#0f172a]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk &bull; <?= htmlspecialchars($appSettings['nama_aplikasi'] ?: 'E-Cuti') ?> - <?= htmlspecialchars($appSettings['nama_perusahaan'] ?: 'PT. Nakakin Indonesia') ?></title>
    
    <link rel="icon" type="image/png" href="<?= $loginFavUrl ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.min.css">

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
        /* Ultra-compact Clean Scrollbar for Right Form if ever needed */
        .login-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .login-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.4);
            border-radius: 99px;
        }

        /* ========================================================
           ULTRA-MODERN SWEETALERT2 MODAL THEME (Bento SaaS Style)
           ======================================================== */
        div.swal2-container {
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            background-color: rgba(15, 23, 42, 0.65) !important;
            z-index: 99999 !important;
            padding: 1rem !important;
        }

        .swal2-popup, .swal2-popup.modern-swal-popup {
            border-radius: 1.75rem !important;
            padding: 1.75rem !important;
            background: #ffffff !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(226, 232, 240, 0.8) !important;
            font-family: inherit !important;
            max-width: 460px !important;
            width: 100% !important;
        }

        html.dark .swal2-popup, html.dark .swal2-popup.modern-swal-popup {
            background: #1e293b !important;
            border: 1px solid #334155 !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(51, 65, 85, 0.8) !important;
            color: #f8fafc !important;
        }

        .swal2-title, .swal2-title.modern-swal-title {
            font-size: 1.2rem !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            letter-spacing: -0.02em !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        html.dark .swal2-title, html.dark .swal2-title.modern-swal-title {
            color: #ffffff !important;
        }

        .swal2-html-container, .swal2-html-container.modern-swal-html {
            color: #475569 !important;
            font-size: 0.875rem !important;
            line-height: 1.55 !important;
            margin: 0 !important;
            padding: 0.5rem 0 0 0 !important;
        }

        html.dark .swal2-html-container, html.dark .swal2-html-container.modern-swal-html {
            color: #cbd5e1 !important;
        }

        /* ========================================================
           ULTRA-HIGH CONTRAST SWEETALERT2 ICONS (Bold & Vivid)
           ======================================================== */
        div.swal2-container .swal2-icon,
        .swal2-icon {
            margin: 0.75rem auto 1.25rem auto !important;
            box-sizing: content-box !important;
            border-radius: 50% !important;
            width: 5rem !important;
            height: 5rem !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            position: relative !important;
            overflow: visible !important;
        }

        div.swal2-container .swal2-icon .swal2-icon-content,
        .swal2-icon .swal2-icon-content {
            color: #ffffff !important;
            font-size: 2.75rem !important;
            font-weight: 900 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            line-height: 1 !important;
            text-shadow: 0 3px 8px rgba(0, 0, 0, 0.4) !important;
            font-family: system-ui, -apple-system, sans-serif !important;
        }

        /* 1. SUCCESS ICON (Deep Forest Emerald + Thick White Checkmark) */
        div.swal2-container .swal2-icon.swal2-success,
        .swal2-icon.swal2-success {
            border: 3.5px solid #047857 !important;
            background: #059669 !important;
            background-color: #059669 !important;
            color: #ffffff !important;
            box-shadow: 0 14px 30px -4px rgba(5, 150, 105, 0.6), 0 0 0 6px rgba(16, 185, 129, 0.25) !important;
            animation: swal2-pop-bounce 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards !important;
        }
        .swal2-icon.swal2-success .swal2-success-ring,
        .swal2-icon.swal2-success .swal2-success-fix,
        .swal2-icon.swal2-success .swal2-success-circular-line-left,
        .swal2-icon.swal2-success .swal2-success-circular-line-right,
        .swal2-icon.swal2-success [class^='swal2-success-line'] {
            display: none !important;
        }
        .swal2-icon.swal2-success::before {
            content: '' !important;
            position: absolute !important;
            inset: -9px !important;
            border-radius: 50% !important;
            border: 2.5px solid rgba(16, 185, 129, 0.6) !important;
            animation: swal2-pulse-ring 1.8s cubic-bezier(0.215, 0.61, 0.355, 1) infinite !important;
            pointer-events: none !important;
        }
        .swal2-icon.swal2-success::after {
            content: '' !important;
            display: block !important;
            width: 16px !important;
            height: 28px !important;
            border: solid #ffffff !important;
            border-width: 0 5px 5px 0 !important;
            transform: rotate(45deg) translate(-2px, -3px) !important;
            margin-top: -5px !important;
            margin-left: 2px !important;
            box-sizing: border-box !important;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3)) !important;
            animation: swal2-check-stroke 0.45s cubic-bezier(0.65, 0, 0.45, 1) 0.15s both !important;
        }

        /* 2. QUESTION ICON (Deep Royal Indigo + Thick White Question Mark) */
        div.swal2-container .swal2-icon.swal2-question,
        .swal2-icon.swal2-question {
            border: 3.5px solid #3730a3 !important;
            background: #4338ca !important;
            background-color: #4338ca !important;
            color: #ffffff !important;
            box-shadow: 0 14px 30px -4px rgba(67, 56, 202, 0.6), 0 0 0 6px rgba(99, 102, 241, 0.25) !important;
            animation: swal2-question-flip 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards !important;
        }
        .swal2-icon.swal2-question::before {
            content: '' !important;
            position: absolute !important;
            inset: -9px !important;
            border-radius: 50% !important;
            border: 2.5px solid rgba(99, 102, 241, 0.6) !important;
            animation: swal2-pulse-ring 1.8s cubic-bezier(0.215, 0.61, 0.355, 1) infinite !important;
            pointer-events: none !important;
        }
        .swal2-icon.swal2-question .swal2-icon-content {
            animation: swal2-float-wobble 2s ease-in-out 0.7s infinite alternate !important;
        }

        /* 3. ERROR ICON (Deep Crimson Red + Thick White X) */
        div.swal2-container .swal2-icon.swal2-error,
        .swal2-icon.swal2-error {
            border: 3.5px solid #b91c1c !important;
            background: #dc2626 !important;
            background-color: #dc2626 !important;
            color: #ffffff !important;
            box-shadow: 0 14px 30px -4px rgba(220, 38, 38, 0.6), 0 0 0 6px rgba(239, 68, 68, 0.25) !important;
            animation: swal2-error-shake 0.5s ease-in-out forwards !important;
        }
        .swal2-icon.swal2-error .swal2-x-mark {
            display: none !important;
        }
        .swal2-icon.swal2-error::before {
            content: '' !important;
            position: absolute !important;
            inset: -9px !important;
            border-radius: 50% !important;
            border: 2.5px solid rgba(239, 68, 68, 0.6) !important;
            animation: swal2-pulse-ring 1.8s cubic-bezier(0.215, 0.61, 0.355, 1) infinite !important;
            pointer-events: none !important;
        }
        .swal2-icon.swal2-error::after {
            content: '✕' !important;
            color: #ffffff !important;
            font-size: 2.3rem !important;
            font-weight: 900 !important;
            line-height: 1 !important;
            display: block !important;
            text-shadow: 0 3px 8px rgba(0, 0, 0, 0.4) !important;
            font-family: system-ui, -apple-system, sans-serif !important;
        }

        /* 4. WARNING ICON (Deep Golden Amber + Thick White Exclamation) */
        div.swal2-container .swal2-icon.swal2-warning,
        .swal2-icon.swal2-warning {
            border: 3.5px solid #b45309 !important;
            background: #d97706 !important;
            background-color: #d97706 !important;
            color: #ffffff !important;
            box-shadow: 0 14px 30px -4px rgba(217, 119, 6, 0.6), 0 0 0 6px rgba(245, 158, 11, 0.25) !important;
            animation: swal2-warning-bounce 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards !important;
        }
        .swal2-icon.swal2-warning::before {
            content: '' !important;
            position: absolute !important;
            inset: -9px !important;
            border-radius: 50% !important;
            border: 2.5px solid rgba(245, 158, 11, 0.6) !important;
            animation: swal2-pulse-ring 1.8s cubic-bezier(0.215, 0.61, 0.355, 1) infinite !important;
            pointer-events: none !important;
        }
        .swal2-icon.swal2-warning .swal2-icon-content {
            animation: swal2-warning-wobble 1.5s ease-in-out 0.6s infinite alternate !important;
        }

        /* 5. INFO ICON (Deep Ocean Blue + Thick White i) */
        div.swal2-container .swal2-icon.swal2-info,
        .swal2-icon.swal2-info {
            border: 3.5px solid #0369a1 !important;
            background: #0284c7 !important;
            background-color: #0284c7 !important;
            color: #ffffff !important;
            box-shadow: 0 14px 30px -4px rgba(2, 132, 199, 0.6), 0 0 0 6px rgba(6, 182, 212, 0.25) !important;
            animation: swal2-pop-bounce 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards !important;
        }
        .swal2-icon.swal2-info::before {
            content: '' !important;
            position: absolute !important;
            inset: -9px !important;
            border-radius: 50% !important;
            border: 2.5px solid rgba(6, 182, 212, 0.6) !important;
            animation: swal2-pulse-ring 1.8s cubic-bezier(0.215, 0.61, 0.355, 1) infinite !important;
            pointer-events: none !important;
        }
        .swal2-icon.swal2-info .swal2-icon-content {
            font-style: italic !important;
            animation: swal2-float-wobble 2s ease-in-out 0.5s infinite alternate !important;
        }

        /* Keyframes */
        @keyframes swal2-pop-bounce {
            0% { transform: scale(0); opacity: 0; }
            60% { transform: scale(1.15); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }

        @keyframes swal2-question-flip {
            0% { transform: scale(0) rotateY(180deg); opacity: 0; }
            60% { transform: scale(1.15) rotateY(-15deg); opacity: 1; }
            100% { transform: scale(1) rotateY(0deg); opacity: 1; }
        }

        @keyframes swal2-error-shake {
            0% { transform: scale(0.5); opacity: 0; }
            30% { transform: scale(1.1) translateX(-6px); opacity: 1; }
            50% { transform: scale(1) translateX(6px); }
            70% { transform: scale(1) translateX(-3px); }
            100% { transform: scale(1) translateX(0); opacity: 1; }
        }

        @keyframes swal2-warning-bounce {
            0% { transform: scale(0) rotate(-15deg); opacity: 0; }
            60% { transform: scale(1.18) rotate(8deg); opacity: 1; }
            80% { transform: scale(0.95) rotate(-4deg); }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
        }

        @keyframes swal2-pulse-ring {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.22); opacity: 0; }
            100% { transform: scale(0.95); opacity: 0; }
        }

        @keyframes swal2-check-stroke {
            0% {
                clip-path: polygon(0 0, 0 0, 0 0, 0 0);
                transform: rotate(45deg) scale(0.7) translate(-2px, -3px);
            }
            50% {
                clip-path: polygon(0 0, 100% 0, 100% 0, 0 100%);
            }
            100% {
                clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
                transform: rotate(45deg) scale(1) translate(-2px, -3px);
            }
        }

        @keyframes swal2-float-wobble {
            0% { transform: translateY(0); }
            100% { transform: translateY(-3px); }
        }

        @keyframes swal2-warning-wobble {
            0% { transform: scale(1); }
            100% { transform: scale(1.08); }
        }

        .swal2-confirm {
            border-radius: 0.875rem !important;
            font-weight: 800 !important;
            font-size: 0.85rem !important;
            padding: 0.75rem 1.6rem !important;
            transition: all 0.2s ease !important;
            box-shadow: 0 4px 14px -2px rgba(37, 99, 235, 0.3) !important;
            border: none !important;
            outline: none !important;
            cursor: pointer !important;
        }
    </style>
</head>
<body class="h-full min-h-screen lg:h-screen lg:overflow-hidden bg-slate-50 dark:bg-[#0f172a] font-sans antialiased text-slate-800 dark:text-slate-100 flex flex-col">

    <div class="min-h-screen lg:h-screen lg:max-h-screen lg:overflow-hidden flex flex-col lg:flex-row w-full">
        
        <!-- ========================================================================= -->
        <!-- LEFT HERO SECTION (Desktop Split-Screen Branding Showcase)                -->
        <!-- ========================================================================= -->
        <div class="hidden lg:flex lg:w-1/2 xl:w-[50%] bg-gradient-to-br from-[#0B1120] via-[#111C44] to-[#1E3A8A] text-white p-8 xl:p-12 flex-col justify-between relative overflow-hidden h-full">
            
            <!-- Ambient Glow Effects -->
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-indigo-500/25 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute top-1/2 left-1/3 w-64 h-64 bg-rose-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Top Brand Header -->
            <div class="relative z-10">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-white/95 p-1.5 shadow-xl shadow-blue-950/40 flex items-center justify-center border border-white/20">
                        <img src="<?= $loginLogoUrl ?>" alt="Logo" class="max-h-full max-w-full object-contain">
                    </div>
                    <div>
                        <h2 class="text-base font-black text-white tracking-wider uppercase leading-tight">
                            <?= htmlspecialchars($appSettings['nama_perusahaan'] ?: 'PT. NAKAKIN INDONESIA') ?>
                        </h2>
                        <span class="text-[11px] text-blue-300 font-semibold tracking-wide">Leave & Attendance Management System</span>
                    </div>
                </div>
            </div>

            <!-- Middle Value Props Showcase -->
            <div class="relative z-10 my-auto py-4 space-y-5 max-w-lg">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-300 text-xs font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Multi-Tier Approval System &bull; 15 Departemen</span>
                    </div>
                    <h1 class="text-2xl xl:text-3xl font-black text-white tracking-tight leading-tight">
                        Kemudahan Pengajuan & Pengelolaan Cuti Karyawan Perusahaan
                    </h1>
                    <p class="text-xs xl:text-sm text-slate-300 leading-relaxed">
                        Sistem persetujuan berjenjang dari Leader/Supervisor, Plant Manager, hingga HRD secara transparan, akurat, dan terintegrasi otomatis.
                    </p>
                </div>

                <!-- Feature Highlights -->
                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md space-y-1">
                        <div class="w-7 h-7 rounded-xl bg-blue-500/20 text-blue-300 flex items-center justify-center text-xs font-bold">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div class="text-xs font-bold text-white">Hierarki Approval</div>
                        <div class="text-[10.5px] text-slate-300 leading-snug">Persetujuan 3 tingkat otomatis sesuai peran departemen.</div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md space-y-1">
                        <div class="w-7 h-7 rounded-xl bg-emerald-500/20 text-emerald-300 flex items-center justify-center text-xs font-bold">
                            <i class="fa-solid fa-chart-pie"></i>
                        </div>
                        <div class="text-xs font-bold text-white">Kuota Realtime</div>
                        <div class="text-[10.5px] text-slate-300 leading-snug">Perhitungan sisa hak cuti langsung terpotong saat disetujui.</div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md space-y-1">
                        <div class="w-7 h-7 rounded-xl bg-amber-500/20 text-amber-300 flex items-center justify-center text-xs font-bold">
                            <i class="fa-solid fa-tv"></i>
                        </div>
                        <div class="text-xs font-bold text-white">Papan Kehadiran Live</div>
                        <div class="text-[10.5px] text-slate-300 leading-snug">Display monitor TV perusahaan untuk monitoring status harian.</div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md space-y-1">
                        <div class="w-7 h-7 rounded-xl bg-purple-500/20 text-purple-300 flex items-center justify-center text-xs font-bold">
                            <i class="fa-solid fa-mobile-screen-button"></i>
                        </div>
                        <div class="text-xs font-bold text-white">Web & Mobile Flutter</div>
                        <div class="text-[10.5px] text-slate-300 leading-snug">Akses mudah dari browser desktop maupun smartphone.</div>
                    </div>
                </div>
            </div>

            <!-- Bottom Footer Info -->
            <div class="relative z-10 pt-3 border-t border-white/10 flex items-center justify-between text-xs text-slate-400">
                <span>&copy; <?= date('Y') ?> <?= htmlspecialchars($appSettings['nama_perusahaan'] ?: 'PT. Nakakin Indonesia') ?></span>
                <span class="font-mono text-[11px]">Production Version 2.0</span>
            </div>

        </div>

        <!-- ========================================================================= -->
        <!-- RIGHT LOGIN FORM SECTION (Desktop & Mobile - No Scroll)                   -->
        <!-- ========================================================================= -->
        <div class="flex-1 flex flex-col justify-center items-center p-4 sm:p-8 xl:p-12 relative lg:h-full lg:overflow-y-auto login-scrollbar">
            
            <div class="w-full max-w-md space-y-4 my-auto">
                
                <!-- Mobile Only Top Brand Header (Hidden on Desktop) -->
                <div class="lg:hidden text-center flex flex-col items-center pb-1">
                    <div class="w-14 h-14 rounded-2xl bg-white dark:bg-slate-800 p-2 shadow-md border border-slate-200/80 dark:border-slate-700 flex items-center justify-center mb-2">
                        <img src="<?= $loginLogoUrl ?>" alt="Logo" class="max-h-full max-w-full object-contain">
                    </div>
                    <h1 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight uppercase leading-tight">
                        <?= htmlspecialchars($appSettings['nama_perusahaan'] ?: 'PT. NAKAKIN INDONESIA') ?>
                    </h1>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">
                        Sistem Informasi Cuti Karyawan
                    </p>
                </div>

                <!-- Card Header -->
                <div class="space-y-0.5">
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">Selamat Datang</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Silakan masukkan NIK atau Email terdaftar untuk masuk ke akun Anda</p>
                </div>

                <!-- Error Flash Message -->
                <?php if ($flash && $flash['type'] === 'error'): ?>
                    <div class="p-3 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 flex items-start gap-2 text-xs text-rose-700 dark:text-rose-400 font-medium animate-in fade-in duration-200">
                        <span class="material-symbols-rounded text-rose-600 text-lg flex-shrink-0">error</span>
                        <span class="flex-1 mt-0.5"><?= htmlspecialchars($flash['message']) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Login Form Card -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 sm:p-6 shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-200/80 dark:border-slate-800 space-y-4">
                    <form action="<?= BASE_URL ?>/index.php?page=login-process" method="POST" class="space-y-3.5">
                        
                        <!-- Username / NIK Input -->
                        <div class="space-y-1">
                            <label for="username" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                NIK / Email / Username <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                    <span class="material-symbols-rounded text-lg">person</span>
                                </span>
                                <input type="text" name="username" id="username" required autofocus placeholder="Masukkan NIK atau email"
                                       value="hrd@nakakin.co.id"
                                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/80 text-slate-900 dark:text-white font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-600 transition placeholder:text-slate-400 placeholder:font-normal">
                            </div>
                        </div>

                        <!-- Password Input + Forgot Password Link -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <label for="password" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                    Password <span class="text-rose-500">*</span>
                                </label>
                                <button type="button" onclick="openForgotPasswordModal()" 
                                        class="text-[11px] font-bold text-blue-600 hover:text-blue-700 dark:text-sky-400 dark:hover:text-sky-300 transition hover:underline">
                                    Lupa Password?
                                </button>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                    <span class="material-symbols-rounded text-lg">lock</span>
                                </span>
                                <input type="password" name="password" id="password" required placeholder="Masukkan password Anda"
                                       value="password123"
                                       class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/80 text-slate-900 dark:text-white font-semibold text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-600 transition placeholder:text-slate-400 placeholder:font-normal">
                                <button type="button" id="btnTogglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer">
                                    <span class="material-symbols-rounded text-lg" id="eyeIcon">visibility</span>
                                </button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs shadow-lg shadow-blue-600/30 active:scale-[0.99] transition flex items-center justify-center gap-2 mt-2 cursor-pointer">
                            <span>Masuk ke Akun</span>
                            <span class="material-symbols-rounded text-base">arrow_forward</span>
                        </button>
                    </form>
                </div>

                <!-- Quick Demo Account Select -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-3 sm:p-3.5 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-2">
                    <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                        <span class="material-symbols-rounded text-sm text-blue-600 dark:text-sky-400">touch_app</span>
                        <span>Akun Demo Testing (Klik Cepat):</span>
                    </div>

                    <div class="flex flex-wrap gap-1.5">
                        <!-- Super Admin -->
                        <button type="button" onclick="setDemoAccount('admin@nakakin.co.id', 'password123')"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-pink-50 dark:bg-pink-950/50 border border-pink-200 dark:border-pink-900 text-pink-700 dark:text-pink-300 hover:bg-pink-100 font-bold text-[10.5px] transition cursor-pointer">
                            <span class="w-3.5 h-3.5 rounded-full bg-pink-600 text-white text-[8px] font-black flex items-center justify-center">S</span>
                            <span>Hermawan (Super Admin)</span>
                        </button>

                        <!-- Risma (HRD) -->
                        <button type="button" onclick="setDemoAccount('hrd@nakakin.co.id', 'password123')"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-900 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 font-bold text-[10.5px] transition cursor-pointer">
                            <span class="w-3.5 h-3.5 rounded-full bg-emerald-600 text-white text-[8px] font-black flex items-center justify-center">H</span>
                            <span>Risma (HRD)</span>
                        </button>

                        <!-- Akun Manager -->
                        <button type="button" onclick="setDemoAccount('manager@nakakin.co.id', 'password123')"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-900 text-purple-700 dark:text-purple-300 hover:bg-purple-100 font-bold text-[10.5px] transition cursor-pointer">
                            <span class="w-3.5 h-3.5 rounded-full bg-purple-600 text-white text-[8px] font-black flex items-center justify-center">M</span>
                            <span>Plant Manager</span>
                        </button>

                        <!-- Leader Spv -->
                        <button type="button" onclick="setDemoAccount('ldr.core@nakakin.co.id', 'password123')"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-sky-50 dark:bg-sky-950/50 border border-sky-200 dark:border-sky-900 text-sky-700 dark:text-sky-300 hover:bg-sky-100 font-bold text-[10.5px] transition cursor-pointer">
                            <span class="w-3.5 h-3.5 rounded-full bg-sky-600 text-white text-[8px] font-black flex items-center justify-center">L</span>
                            <span>Leader Spv</span>
                        </button>

                        <!-- Operator -->
                        <button type="button" onclick="setDemoAccount('op1.core@nakakin.co.id', 'password123')"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 font-bold text-[10.5px] transition cursor-pointer">
                            <span class="w-3.5 h-3.5 rounded-full bg-slate-600 text-white text-[8px] font-black flex items-center justify-center">O</span>
                            <span>Operator</span>
                        </button>
                    </div>
                </div>

                <!-- Bottom Links -->
                <div class="text-center pt-1">
                    <a href="<?= BASE_URL ?>/index.php?page=board" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-sky-400 transition">
                        <span class="material-symbols-rounded text-sm text-slate-400">tv</span>
                        <span>Buka Papan Kehadiran Hari Ini (Live TV Board)</span>
                    </a>
                </div>

            </div>

        </div>

    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.all.min.js"></script>
    <script>
        const btnToggle = document.getElementById('btnTogglePassword');
        const passInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        if (btnToggle && passInput && eyeIcon) {
            btnToggle.addEventListener('click', function() {
                const isPassword = passInput.type === 'password';
                passInput.type = isPassword ? 'text' : 'password';
                eyeIcon.innerText = isPassword ? 'visibility_off' : 'visibility';
            });
        }

        function setDemoAccount(username, password) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = password;
        }

        // Forgot Password via WhatsApp Super User / Admin HRD
        function openForgotPasswordModal() {
            const currentUsername = document.getElementById('username').value || '';
            const adminWa = '<?= $adminPhone ?>';

            Swal.fire({
                showConfirmButton: false,
                showCancelButton: false,
                background: 'transparent',
                padding: '0',
                customClass: {
                    popup: 'border-0 bg-transparent shadow-none w-full max-w-md mx-auto p-0'
                },
                html: `
                    <div class="relative bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-7 shadow-2xl border border-slate-100 dark:border-slate-800 text-left overflow-hidden">
                        <!-- Decorative background glow -->
                        <div class="absolute -top-16 -right-16 w-36 h-36 bg-emerald-500/15 rounded-full blur-2xl pointer-events-none"></div>
                        <div class="absolute -bottom-16 -left-16 w-36 h-36 bg-blue-500/10 rounded-full blur-2xl pointer-events-none"></div>

                        <!-- Header Icon Badge -->
                        <div class="text-center mb-5 relative">
                            <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-tr from-emerald-600 via-emerald-500 to-teal-400 text-white flex items-center justify-center text-2xl shadow-xl shadow-emerald-500/25 mb-3 ring-4 ring-emerald-50 dark:ring-emerald-950/40">
                                <i class="fa-brands fa-whatsapp"></i>
                            </div>
                            <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                                Bantuan Lupa Password
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Hubungi Admin HRD / Super User untuk reset akun
                            </p>
                        </div>

                        <!-- Info Banner -->
                        <div class="flex items-start gap-2.5 p-3 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-900 dark:text-amber-200 text-xs mb-4 leading-relaxed">
                            <i class="fa-solid fa-shield-halved text-amber-600 dark:text-amber-400 text-sm mt-0.5 flex-shrink-0"></i>
                            <div class="text-[11.5px]">
                                Demi keamanan data karyawan, reset password diverifikasi dan diproses langsung oleh <strong class="font-semibold">HRD / Super User</strong>.
                            </div>
                        </div>

                        <!-- Form Inputs -->
                        <div class="space-y-3.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    NIK / Email Akun <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-id-card text-xs"></i>
                                    </div>
                                    <input type="text" id="waNikInput" value="${currentUsername.replace(/"/g, '&quot;')}" placeholder="Masukkan NIK atau email terdaftar"
                                           class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 text-slate-900 dark:text-white font-semibold text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white dark:focus:bg-slate-800 focus:border-transparent focus:outline-none transition">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Nama Lengkap <span class="text-[10px] text-slate-400 font-normal lowercase">(opsional)</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-user text-xs"></i>
                                    </div>
                                    <input type="text" id="waNamaInput" placeholder="Contoh: Budi Santoso"
                                           class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 text-slate-900 dark:text-white font-semibold text-xs focus:ring-2 focus:ring-emerald-500 focus:bg-white dark:focus:bg-slate-800 focus:border-transparent focus:outline-none transition">
                                </div>
                            </div>
                        </div>

                        <div id="waErrorMsg" class="hidden text-rose-500 text-[11px] font-semibold mt-2.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span>Harap masukkan NIK atau Email Anda!</span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-5 space-y-2">
                            <button type="button" id="btnSendWA" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-600 via-emerald-500 to-teal-500 hover:from-emerald-500 hover:to-teal-400 active:scale-[0.98] text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/30 hover:shadow-emerald-600/40 transition-all cursor-pointer">
                                <i class="fa-brands fa-whatsapp text-base"></i>
                                <span>Kirim Permintaan ke WhatsApp Admin</span>
                            </button>
                            <button type="button" onclick="Swal.close()" class="w-full py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                Batal
                            </button>
                        </div>
                    </div>
                `,
                didOpen: () => {
                    const btnSend = document.getElementById('btnSendWA');
                    const nikInput = document.getElementById('waNikInput');
                    const namaInput = document.getElementById('waNamaInput');
                    const errorMsg = document.getElementById('waErrorMsg');

                    nikInput.focus();

                    const handleSend = () => {
                        const nik = nikInput.value.trim();
                        const nama = namaInput.value.trim();
                        if (!nik) {
                            errorMsg.classList.remove('hidden');
                            nikInput.focus();
                            return;
                        }
                        errorMsg.classList.add('hidden');
                        
                        const namaText = nama ? `Nama: ${nama}\n` : '';
                        const message = `Halo Super User / Admin HRD PT. Nakakin Indonesia,\n\nSaya ingin meminta bantuan reset password untuk akun E-Cuti Karyawan:\n- NIK / Email: ${nik}\n${namaText}\nMohon bantuannya untuk melakukan reset password akun saya. Terima kasih.`;
                        
                        const waUrl = `https://api.whatsapp.com/send?phone=${adminWa}&text=${encodeURIComponent(message)}`;
                        window.open(waUrl, '_blank');
                        Swal.close();
                    };

                    btnSend.addEventListener('click', handleSend);
                    nikInput.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') handleSend();
                    });
                    namaInput.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') handleSend();
                    });
                }
            });
        }

        <?php if ($flash && $flash['type'] !== 'error'): ?>
            Swal.fire({
                icon: '<?= $flash['type'] === 'warning' ? 'warning' : 'success' ?>',
                title: '<?= $flash['type'] === 'warning' ? 'Perhatian' : 'Berhasil!' ?>',
                text: '<?= addslashes($flash['message']) ?>',
                confirmButtonColor: '#2563eb',
                customClass: { popup: 'rounded-2xl shadow-2xl modern-swal-popup' }
            });
        <?php endif; ?>
    </script>
</body>
</html>
