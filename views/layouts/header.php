<?php
/**
 * Header Layout (Tailwind CSS Edition)
 * PT. Nakakin Indonesia Leave Management System
 */

if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../../config/database.php';
}
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/session.php';

$currentUser = getCurrentUser();
$flash = getFlash();

// Calculate pending approvals count based on 3-tier hierarchy
$pendingApprovalCount = 0;
if ($currentUser) {
    $userRole = strtolower($currentUser['role'] ?? '');
    $userLevel = (int)($currentUser['level_hierarki'] ?? 1);
    $isHRD = in_array($userRole, ['admin', 'superadmin', 'hrd']) || $userLevel >= 7;
    $isManager = $userRole === 'manager' || ($userLevel >= 5 && $userLevel <= 6);
    $isSpv = in_array($userRole, ['supervisor', 'leader', 'atasan']) || ($userLevel >= 3 && $userLevel <= 4);

    $pdo = getDbConnection();
    if ($isHRD) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM pengajuan_cuti WHERE status = 'pending' AND approval_step = 'pending_hrd'");
        $pendingApprovalCount = (int)$stmt->fetchColumn();
    } elseif ($isManager) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pengajuan_cuti WHERE status = 'pending' AND approval_step = 'pending_manager' AND employee_id != ?");
        $stmt->execute([$currentUser['id']]);
        $pendingApprovalCount = (int)$stmt->fetchColumn();
    } elseif ($isSpv) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM pengajuan_cuti lr
            JOIN karyawan e ON lr.employee_id = e.id
            WHERE lr.status = 'pending' AND lr.approval_step = 'pending_spv' AND e.departemen_id = ? AND e.id != ?
        ");
        $stmt->execute([$currentUser['departemen_id'], $currentUser['id']]);
        $pendingApprovalCount = (int)$stmt->fetchColumn();
    }
}
$appSettings = getAppSettings();
$headerFaviconUrl = !empty($appSettings['favicon']) ? BASE_URL . '/assets/images/' . $appSettings['favicon'] : BASE_URL . '/assets/images/Nakakin.png';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?><?= htmlspecialchars($appSettings['nama_perusahaan'] ?: 'PT. Nakakin Indonesia') ?> - <?= htmlspecialchars($appSettings['singkatan_aplikasi'] ?: 'Sistem Cuti') ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= $headerFaviconUrl ?>">

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
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
                    colors: {
                        nakakin: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            200: '#fecdd3',
                            500: '#f43f5e',
                            600: '#e11d48',
                            700: '#be123c',
                            800: '#9f1239',
                            900: '#881337',
                        },
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#0f172a',
                        }
                    },
                    borderRadius: {
                        '2xl': '1rem',
                        '3xl': '1.5rem',
                        '4xl': '2rem',
                    },
                    boxShadow: {
                        'soft': '0 4px 20px -2px rgba(15, 23, 42, 0.05)',
                        'glow': '0 0 25px -5px rgba(37, 99, 235, 0.3)',
                        'glow-red': '0 0 25px -5px rgba(225, 29, 72, 0.3)',
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome 6.5.1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.min.css">

    <!-- DataTables CSS for Tailwind -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

    <!-- Custom Micro Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">

    <style>
        /* ========================================================
           ULTRA-MODERN SWEETALERT2 MODAL THEME (Bento SaaS Style)
           ======================================================== */
        div.swal2-container {
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            background-color: rgba(15, 23, 42, 0.65) !important;
            z-index: 99999 !important;
        }

        .swal2-popup.modern-swal-popup {
            border-radius: 1.75rem !important;
            padding: 1.75rem !important;
            background: #ffffff !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(226, 232, 240, 0.8) !important;
            font-family: inherit !important;
            max-width: 480px !important;
            width: 90% !important;
        }

        .swal2-title.modern-swal-title {
            font-size: 1.15rem !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            letter-spacing: -0.02em !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .swal2-html-container.modern-swal-html {
            color: #334155 !important;
            font-size: 0.8125rem !important;
            line-height: 1.5 !important;
            margin: 0 !important;
            padding: 0.5rem 0 0 0 !important;
        }

        .swal2-actions.modern-swal-actions {
            display: flex !important;
            gap: 0.625rem !important;
            width: 100% !important;
            margin-top: 1.25rem !important;
            padding: 0 !important;
            justify-content: flex-end !important;
        }

        .swal2-confirm.modern-swal-confirm {
            border-radius: 0.875rem !important;
            font-weight: 800 !important;
            font-size: 0.8125rem !important;
            padding: 0.75rem 1.4rem !important;
            transition: all 0.2s ease !important;
            box-shadow: 0 4px 14px -2px rgba(0, 0, 0, 0.15) !important;
            border: none !important;
            outline: none !important;
            cursor: pointer !important;
        }

        .swal2-cancel.modern-swal-cancel {
            border-radius: 0.875rem !important;
            font-weight: 700 !important;
            font-size: 0.8125rem !important;
            padding: 0.75rem 1.25rem !important;
            background-color: #f1f5f9 !important;
            color: #475569 !important;
            transition: all 0.2s ease !important;
            border: 1px solid #e2e8f0 !important;
            outline: none !important;
            cursor: pointer !important;
        }
        .swal2-cancel.modern-swal-cancel:hover {
            background-color: #e2e8f0 !important;
            color: #0f172a !important;
        }

        .swal2-validation-message {
            background: #fef2f2 !important;
            color: #dc2626 !important;
            border-radius: 0.875rem !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            padding: 0.625rem 1rem !important;
            margin-top: 0.75rem !important;
            border: 1px solid #fecaca !important;
        }

        /* ========================================================
           ULTRA-MODERN DATA TABLES THEME (Bento & SaaS Aesthetic)
           ======================================================== */
        .dataTables_wrapper {
            font-family: inherit;
            color: #334155;
            padding: 0;
        }

        /* Top Controls: Length & Search Bar */
        .dt-toolbar {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: space-between !important;
            width: 100% !important;
            margin-bottom: 1rem !important;
            gap: 1rem !important;
        }

        .dataTables_wrapper .dataTables_length {
            display: flex !important;
            align-items: center !important;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #64748b;
            float: none !important;
            margin: 0 !important;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 0.45rem 1rem;
            font-size: 0.8125rem;
            font-weight: 700;
            color: #1e293b;
            background-color: #f8fafc;
            outline: none;
            cursor: pointer;
            margin: 0 0.4rem;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }

        .dataTables_wrapper .dataTables_length select:focus {
            border-color: #3b82f6;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .dataTables_wrapper .dataTables_filter {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-end !important;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #64748b;
            float: none !important;
            margin: 0 !important;
            margin-left: auto !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 0.5rem 1.15rem;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #1e293b;
            background-color: #f8fafc;
            outline: none;
            width: 230px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #3b82f6;
            background-color: #ffffff;
            width: 270px;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
        }

        /* Modern Clean Table Styling */
        table.dataTable {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            width: 100% !important;
            margin-top: 0.75rem !important;
            margin-bottom: 1.25rem !important;
            border: none !important;
        }

        table.dataTable thead th {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%) !important;
            color: #475569 !important;
            font-size: 0.7rem !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.06em !important;
            padding: 0.95rem 1.15rem !important;
            border-top: 1px solid #e2e8f0 !important;
            border-bottom: 2px solid #cbd5e1 !important;
            border-left: none !important;
            border-right: none !important;
            white-space: nowrap;
        }

        table.dataTable thead th:first-child {
            border-top-left-radius: 1rem;
            border-bottom-left-radius: 1rem;
            border-left: 1px solid #e2e8f0 !important;
        }

        table.dataTable thead th:last-child {
            border-top-right-radius: 1rem;
            border-bottom-right-radius: 1rem;
            border-right: 1px solid #e2e8f0 !important;
        }

        table.dataTable tbody td {
            padding: 1.1rem 1.15rem !important;
            vertical-align: middle !important;
            border-bottom: 1px solid #f1f5f9 !important;
            border-top: none !important;
            border-left: none !important;
            border-right: none !important;
            color: #334155;
            font-size: 0.8125rem;
            background-color: transparent !important;
        }

        table.dataTable tbody tr {
            transition: all 0.15s ease;
        }

        table.dataTable tbody tr:hover td {
            background-color: rgba(241, 245, 249, 0.75) !important;
        }

        table.dataTable.no-footer {
            border-bottom: none !important;
        }

        /* Bottom Controls: Info & Pagination */
        .dt-footer {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: space-between !important;
            width: 100% !important;
            margin-top: 1rem !important;
            padding-top: 0.75rem !important;
            border-top: 1px solid #f1f5f9 !important;
            gap: 1rem !important;
        }

        .dataTables_wrapper .dataTables_info {
            display: flex !important;
            align-items: center !important;
            font-size: 0.75rem;
            font-weight: 600;
            color: #94a3b8;
            margin: 0 !important;
            padding: 0 !important;
            float: none !important;
        }

        .dataTables_wrapper .dataTables_paginate {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-end !important;
            gap: 0.25rem;
            margin: 0 !important;
            padding: 0 !important;
            float: none !important;
            margin-left: auto !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 2.25rem !important;
            height: 2.25rem !important;
            padding: 0 0.75rem !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            border-radius: 0.75rem !important;
            border: 1px solid transparent !important;
            color: #64748b !important;
            background: transparent !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            margin: 0 2px !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            color: #1e293b !important;
            background: #f1f5f9 !important;
            border-color: #e2e8f0 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            color: #ffffff !important;
            background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
            border-color: transparent !important;
            font-weight: 800 !important;
            box-shadow: 0 4px 12px -2px rgba(37, 99, 235, 0.35) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
            color: #cbd5e1 !important;
            background: transparent !important;
            border-color: transparent !important;
            cursor: not-allowed !important;
            opacity: 0.6;
        }

        /* Mini Collapsed Sidebar CSS */
        #mainSidebar {
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        #mainWrapper {
            transition: padding-left 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @media (min-width: 1024px) {
            #mainSidebar.sidebar-collapsed {
                width: 5rem !important; /* 80px */
            }
            #mainSidebar.sidebar-collapsed .sidebar-text,
            #mainSidebar.sidebar-collapsed .sidebar-full-only {
                display: none !important;
            }
            #mainSidebar.sidebar-collapsed .sidebar-brand {
                display: none !important;
            }
            #mainSidebar.sidebar-collapsed .sidebar-profile-card {
                margin-left: 0.5rem !important;
                margin-right: 0.5rem !important;
                padding: 0.5rem !important;
            }
            #mainSidebar.sidebar-collapsed .sidebar-profile-flex {
                justify-content: center !important;
            }
            #mainSidebar.sidebar-collapsed .sidebar-nav-item {
                justify-content: center !important;
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
                gap: 0 !important;
            }
            #mainSidebar.sidebar-collapsed .sidebar-nav-item i {
                margin: 0 !important;
                font-size: 1.15rem !important;
            }
            #mainSidebar.sidebar-collapsed .sidebar-section-divider {
                display: block !important;
            }
            #mainWrapper.sidebar-collapsed {
                padding-left: 5rem !important; /* 80px */
            }
        }
    </style>
</head>
<body class="h-full bg-slate-50 text-slate-800 antialiased font-sans selection:bg-rose-500 selection:text-white">

<div class="min-h-full flex">
    <!-- Sidebar Component -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Mobile Backdrop -->
    <div id="mobileBackdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 hidden transition-opacity"></div>

    <!-- Main Content Wrapper with dynamic responsive padding -->
    <div id="mainWrapper" class="flex-1 flex flex-col min-w-0 min-h-screen transition-all duration-300 lg:pl-72">
        
        <!-- Modern Top Header -->
        <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-slate-200/80 px-4 sm:px-8 py-3.5 flex items-center justify-between transition-all no-print">
            <div class="flex items-center gap-3 sm:gap-4">
                <!-- Universal Sidebar Toggle Button (Desktop & Mobile) -->
                <button type="button" id="sidebarToggleBtn" 
                        class="p-2.5 rounded-xl bg-slate-100/90 hover:bg-slate-200/90 text-slate-700 hover:text-slate-900 border border-slate-200 shadow-xs transition flex items-center justify-center cursor-pointer" 
                        title="Buka / Tutup Sidebar (Slide)">
                    <i class="fa-solid fa-bars-staggered text-base"></i>
                </button>
                
                <div>
                    <h1 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                        <?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard' ?>
                    </h1>
                    <p class="text-xs text-slate-500 font-medium hidden sm:block">PT. Nakakin Indonesia &bull; Precision Machinery & Leave System</p>
                </div>
            </div>

            <div class="flex items-center gap-3 sm:gap-4">
                <!-- Realtime Clock Pill -->
                <div class="hidden md:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-100/80 border border-slate-200/60 text-xs font-semibold text-slate-600 shadow-sm" id="liveClock">
                    <i class="fa-regular fa-clock text-blue-600"></i> Memuat waktu...
                </div>

                <!-- Pending Approval Notification Pill for Atasan -->
                <?php if ($pendingApprovalCount > 0): ?>
                    <a href="<?= BASE_URL ?>/index.php?page=leave-approvals" class="relative flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-sm hover:shadow transition transform active:scale-95">
                        <i class="fa-solid fa-bell"></i>
                        <span><?= $pendingApprovalCount ?> Butuh Approval</span>
                        <span class="absolute -top-1 -right-1 flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-600"></span>
                        </span>
                    </a>
                <?php endif; ?>

                <!-- User Profile Dropdown Pill -->
                <div class="relative" id="userMenuContainer">
                    <button type="button" id="userMenuBtn" class="flex items-center gap-2.5 p-1.5 sm:px-3 rounded-full bg-white hover:bg-slate-100 border border-slate-200/80 shadow-sm transition">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-xs flex items-center justify-center shadow-sm">
                            <?= strtoupper(substr($currentUser['nama_lengkap'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="text-left hidden sm:block">
                            <div class="text-xs font-bold text-slate-800 leading-tight"><?= htmlspecialchars(explode(' ', $currentUser['nama_lengkap'] ?? 'User')[0]) ?></div>
                            <div class="text-[10px] text-slate-500 leading-tight capitalize"><?= htmlspecialchars($currentUser['role'] ?? '') ?></div>
                        </div>
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 hidden sm:block ml-1"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="userDropdown" class="hidden absolute right-0 mt-2 w-64 rounded-2xl bg-white shadow-xl border border-slate-100 py-2 z-50 transform origin-top-right transition-all">
                        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50">
                            <p class="text-xs font-bold text-slate-900 truncate"><?= htmlspecialchars($currentUser['nama_lengkap'] ?? '') ?></p>
                            <p class="text-[11px] text-slate-500"><?= htmlspecialchars($currentUser['nik'] ?? '') ?> &bull; <?= htmlspecialchars($currentUser['nama_dept'] ?? '') ?></p>
                        </div>
                        <div class="py-1">
                            <a href="<?= BASE_URL ?>/index.php?page=profile" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition">
                                <i class="fa-solid fa-user-gear text-slate-400 w-4 text-center"></i> Profil Saya
                            </a>
                            <a href="<?= BASE_URL ?>/index.php?page=leaves-my" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition">
                                <i class="fa-solid fa-clock-rotate-left text-slate-400 w-4 text-center"></i> Riwayat Cuti Saya
                            </a>
                        </div>
                        <div class="border-t border-slate-100 pt-1">
                            <a href="<?= BASE_URL ?>/index.php?page=logout" class="flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 transition">
                                <i class="fa-solid fa-right-from-bracket text-rose-400 w-4 text-center"></i> Keluar (Logout)
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Body (Consistently Full Width) -->
        <main class="flex-1 p-4 sm:p-8 w-full">
