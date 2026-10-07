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
        // HRD as top monitoring level tracks all pending leaves company-wide
        $stmt = $pdo->query("SELECT COUNT(*) FROM pengajuan_cuti WHERE status = 'pending'");
        $pendingApprovalCount = (int)$stmt->fetchColumn();
    } elseif ($isManager) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pengajuan_cuti WHERE status = 'pending' AND (approval_step = 'pending_manager' OR employee_id != ?)");
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
<html lang="id" class="h-full bg-slate-50 dark:bg-[#0F172A]">
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

    <!-- Dark Mode Initializer (Prevents Flash) -->
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

    <!-- Google Material Symbols Rounded (Exact Flutter Icons) -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.min.css">

    <!-- DataTables CSS for Tailwind -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

    <!-- Custom Micro Styles & Dark Theme -->
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
            max-width: 480px !important;
            width: 100% !important;
        }

        html.dark .swal2-popup, html.dark .swal2-popup.modern-swal-popup {
            background: #1e293b !important;
            border: 1px solid #334155 !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(51, 65, 85, 0.8) !important;
            color: #f8fafc !important;
        }

        .swal2-title, .swal2-title.modern-swal-title {
            font-size: 1.15rem !important;
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
            font-size: 0.85rem !important;
            line-height: 1.55 !important;
            margin: 0 !important;
            padding: 0.5rem 0 0 0 !important;
        }

        html.dark .swal2-html-container, html.dark .swal2-html-container.modern-swal-html {
            color: #cbd5e1 !important;
        }

        .swal2-input, .swal2-textarea, .swal2-select {
            border-radius: 0.875rem !important;
            border: 1px solid #cbd5e1 !important;
            font-size: 0.875rem !important;
            padding: 0.75rem 1rem !important;
            background: #f8fafc !important;
            color: #0f172a !important;
            box-shadow: none !important;
            transition: all 0.2s ease !important;
        }

        html.dark .swal2-input, html.dark .swal2-textarea, html.dark .swal2-select {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        .swal2-actions, .swal2-actions.modern-swal-actions {
            display: flex !important;
            gap: 0.625rem !important;
            width: 100% !important;
            margin-top: 1.25rem !important;
            padding: 0 !important;
            justify-content: flex-end !important;
        }

        .swal2-confirm, .swal2-confirm.modern-swal-confirm {
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

        /* ========================================================
           ULTRA-HIGH CONTRAST SWEETALERT2 ICONS (Bold & Vivid)
           ======================================================== */
        div.swal2-container .swal2-icon.swal2-hidden,
        div.swal2-container .swal2-icon[style*="display: none"],
        .swal2-icon.swal2-hidden,
        .swal2-icon[style*="display: none"],
        div.swal2-container .swal2-icon:not(.swal2-success):not(.swal2-error):not(.swal2-warning):not(.swal2-info):not(.swal2-question) {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            width: 0 !important;
            margin: 0 !important;
            border: none !important;
        }

        div.swal2-container .swal2-icon.swal2-success,
        div.swal2-container .swal2-icon.swal2-error,
        div.swal2-container .swal2-icon.swal2-warning,
        div.swal2-container .swal2-icon.swal2-info,
        div.swal2-container .swal2-icon.swal2-question,
        .swal2-icon.swal2-success,
        .swal2-icon.swal2-error,
        .swal2-icon.swal2-warning,
        .swal2-icon.swal2-info,
        .swal2-icon.swal2-question {
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

        .swal2-cancel, .swal2-cancel.modern-swal-cancel {
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
        .swal2-cancel:hover, .swal2-cancel.modern-swal-cancel:hover {
            background-color: #e2e8f0 !important;
            color: #0f172a !important;
        }

        html.dark .swal2-cancel {
            background-color: #334155 !important;
            color: #e2e8f0 !important;
            border-color: #475569 !important;
        }
        html.dark .swal2-cancel:hover {
            background-color: #475569 !important;
            color: #ffffff !important;
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

        html.dark .swal2-validation-message {
            background: rgba(220, 38, 38, 0.2) !important;
            color: #fca5a5 !important;
            border-color: rgba(220, 38, 38, 0.4) !important;
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

        /* Modern Clean Table Styling (Light Mode) */
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

        html:not(.dark) table.dataTable tbody tr:hover td,
        html:not(.dark) table.dataTable tbody tr:hover > *,
        html:not(.dark) table tbody tr:hover td {
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

        /* ========================================================
           AUTHORITATIVE FLUTTER-IDENTICAL DARK MODE OVERRIDES
           ======================================================== */
        html.dark,
        html.dark body,
        html.dark #mainWrapper,
        html.dark main {
            background-color: #121824 !important;
            color: #f8fafc !important;
        }

        html.dark .bg-white,
        html.dark .bg-white\/95,
        html.dark .bg-white\/90,
        html.dark .bg-white\/80,
        html.dark .bg-white\/70,
        html.dark .card,
        html.dark .panel {
            background-color: #1e293b !important;
            color: #f8fafc !important;
        }

        html.dark .bg-slate-50,
        html.dark .bg-slate-50\/50,
        html.dark .bg-slate-50\/40,
        html.dark .bg-slate-50\/70,
        html.dark .bg-slate-50\/80,
        html.dark .bg-slate-50\/90,
        html.dark .bg-gray-50,
        html.dark .bg-gray-100 {
            background-color: #0f172a !important;
        }

        html.dark .bg-slate-100,
        html.dark .bg-slate-100\/90,
        html.dark .bg-slate-100\/80,
        html.dark .bg-slate-200,
        html.dark .bg-slate-200\/60 {
            background-color: #1e293b !important;
            color: #e2e8f0 !important;
        }

        html.dark .border-slate-100,
        html.dark .border-slate-200,
        html.dark .border-slate-200\/90,
        html.dark .border-slate-200\/80,
        html.dark .border-slate-200\/70,
        html.dark .border-slate-200\/60,
        html.dark .border-slate-300,
        html.dark .border-slate-400 {
            border-color: #334155 !important;
        }

        html.dark .divide-slate-100 > :not([hidden]) ~ :not([hidden]),
        html.dark .divide-slate-200 > :not([hidden]) ~ :not([hidden]) {
            border-color: #334155 !important;
        }

        html.dark .text-slate-900,
        html.dark .text-slate-800,
        html.dark .text-gray-900,
        html.dark .text-gray-800,
        html.dark .text-blue-900 {
            color: #ffffff !important;
        }

        html.dark .text-slate-700,
        html.dark .text-slate-600,
        html.dark .text-gray-700,
        html.dark .text-gray-600 {
            color: #cbd5e1 !important;
        }

        html.dark .text-slate-500,
        html.dark .text-slate-400,
        html.dark .text-gray-500,
        html.dark .text-gray-400 {
            color: #94a3b8 !important;
        }

        html.dark .text-blue-600,
        html.dark .text-blue-700,
        html.dark .text-sky-600 {
            color: #38bdf8 !important;
        }

        html.dark input[type="text"]:not(.bg-transparent),
        html.dark input[type="password"]:not(.bg-transparent),
        html.dark input[type="email"]:not(.bg-transparent),
        html.dark input[type="date"]:not(.bg-transparent),
        html.dark input[type="number"]:not(.bg-transparent),
        html.dark input[type="search"]:not(.bg-transparent),
        html.dark select:not(.bg-transparent),
        html.dark textarea:not(.bg-transparent) {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        html.dark input.bg-transparent,
        html.dark input[type="date"].bg-transparent,
        html.dark input[type="text"].bg-transparent,
        html.dark input[type="search"].bg-transparent {
            background-color: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }

        html.dark input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(0.8) sepia(100%) saturate(300%) hue-rotate(170deg);
            opacity: 0.85;
            cursor: pointer;
        }

        html.dark #mobileAppBottomBar {
            background-color: rgba(30, 41, 59, 0.98) !important;
            border-top-color: #334155 !important;
        }

        html.dark header {
            background-color: rgba(30, 41, 59, 0.95) !important;
            border-bottom-color: #334155 !important;
        }

        html.dark #userMenuBtn,
        html.dark #userDropdown {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }

        /* Comprehensive Dark Mode Tables & DataTables Rules */
        html.dark table.dataTable,
        html.dark table {
            color: #cbd5e1 !important;
            border-color: #334155 !important;
            background-color: transparent !important;
        }

        html.dark table.dataTable thead th,
        html.dark table thead th {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%) !important;
            background-color: #0f172a !important;
            color: #94a3b8 !important;
            border-top: 1px solid #334155 !important;
            border-bottom: 2px solid #334155 !important;
            border-left-color: #334155 !important;
            border-right-color: #334155 !important;
        }

        html.dark table.dataTable thead th:first-child,
        html.dark table thead th:first-child {
            border-left: 1px solid #334155 !important;
        }

        html.dark table.dataTable thead th:last-child,
        html.dark table thead th:last-child {
            border-right: 1px solid #334155 !important;
        }

        html.dark table.dataTable tbody td,
        html.dark table tbody td,
        html.dark table.dataTable > tbody > tr > td,
        html.dark table.dataTable > tbody > tr > th,
        html.dark table.dataTable > tbody > tr > * {
            border-bottom: 1px solid #334155 !important;
            color: #cbd5e1 !important;
            background-color: transparent !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        html.dark table.dataTable tbody tr,
        html.dark table tbody tr,
        html.dark table.dataTable > tbody > tr {
            background-color: transparent !important;
            background: transparent !important;
            box-shadow: none !important;
            color: #cbd5e1 !important;
        }

        /* Row Hover Highlight in Dark Mode (Elegant Deep Navy-Slate) */
        html.dark table.dataTable tbody tr:hover td,
        html.dark table.dataTable tbody tr:hover,
        html.dark table.dataTable tbody tr:hover > *,
        html.dark table.dataTable > tbody > tr:hover > *,
        html.dark table.dataTable > tbody > tr:hover > td,
        html.dark table tbody tr:hover td,
        html.dark table tbody tr:hover,
        html.dark tr.hover\:bg-slate-50\/60:hover,
        html.dark tr.hover\:bg-slate-50:hover,
        html.dark tr:hover {
            background-color: #1e293b !important;
            background: #1e293b !important;
            box-shadow: none !important;
            transition: background-color 0.15s ease-in-out !important;
        }

        html.dark table.dataTable.stripe tbody tr.odd,
        html.dark table.dataTable.display tbody tr.odd,
        html.dark table.dataTable.stripe tbody tr.even,
        html.dark table.dataTable.display tbody tr.even,
        html.dark table.dataTable > tbody > tr.odd > *,
        html.dark table.dataTable > tbody > tr.even > *,
        html.dark table.dataTable.display > tbody > tr.odd > *,
        html.dark table.dataTable.display > tbody > tr.even > *,
        html.dark table.dataTable.order-column.stripe tbody tr.odd > .sorting_1,
        html.dark table.dataTable.order-column.stripe tbody tr.even > .sorting_1 {
            background-color: transparent !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        html.dark table.dataTable.stripe tbody tr.odd:hover > *,
        html.dark table.dataTable.display tbody tr.odd:hover > *,
        html.dark table.dataTable.stripe tbody tr.even:hover > *,
        html.dark table.dataTable.display tbody tr.even:hover > * {
            background-color: #1e293b !important;
            background: #1e293b !important;
            box-shadow: none !important;
        }

        /* Dark Mode Badges in Tables & Lists */
        html.dark .bg-blue-50 {
            background-color: rgba(30, 58, 138, 0.4) !important;
            color: #60a5fa !important;
            border-color: rgba(59, 130, 246, 0.3) !important;
        }
        html.dark .bg-emerald-50 {
            background-color: rgba(6, 78, 59, 0.4) !important;
            color: #34d399 !important;
            border-color: rgba(16, 185, 129, 0.3) !important;
        }
        html.dark .bg-rose-50 {
            background-color: rgba(136, 19, 55, 0.4) !important;
            color: #fb7185 !important;
            border-color: rgba(244, 63, 94, 0.3) !important;
        }
        html.dark .bg-amber-50,
        html.dark .bg-\[\#FFF7ED\] {
            background-color: rgba(120, 53, 15, 0.4) !important;
            color: #fbbf24 !important;
            border-color: rgba(245, 158, 11, 0.3) !important;
        }
        html.dark .bg-purple-50 {
            background-color: rgba(88, 28, 135, 0.4) !important;
            color: #c084fc !important;
            border-color: rgba(168, 85, 247, 0.3) !important;
        }

        html.dark .dataTables_wrapper .dataTables_length select,
        html.dark .dataTables_wrapper .dataTables_filter input {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        html.dark .dt-footer {
            border-top-color: #334155 !important;
        }

        html.dark .dataTables_wrapper .dataTables_info {
            color: #94a3b8 !important;
        }

        html.dark .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #94a3b8 !important;
            border-color: #334155 !important;
            background: #0f172a !important;
        }

        html.dark .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            color: #ffffff !important;
            background: #1e293b !important;
            border-color: #3b82f6 !important;
        }

        html.dark .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #2563eb !important;
            color: #ffffff !important;
            border-color: #2563eb !important;
        }

        /* Pill & Badge dark mode palette */
        html.dark .bg-amber-50,
        html.dark .bg-amber-100 {
            background-color: rgba(245, 158, 11, 0.15) !important;
            color: #fbbf24 !important;
            border-color: rgba(245, 158, 11, 0.3) !important;
        }
        html.dark .bg-blue-50,
        html.dark .bg-blue-100 {
            background-color: rgba(59, 130, 246, 0.15) !important;
            color: #38bdf8 !important;
            border-color: rgba(59, 130, 246, 0.3) !important;
        }
        html.dark .bg-emerald-50,
        html.dark .bg-emerald-100 {
            background-color: rgba(16, 185, 129, 0.15) !important;
            color: #34d399 !important;
            border-color: rgba(16, 185, 129, 0.3) !important;
        }
        html.dark .bg-purple-50,
        html.dark .bg-purple-100 {
            background-color: rgba(168, 85, 247, 0.15) !important;
            color: #c084fc !important;
            border-color: rgba(168, 85, 247, 0.3) !important;
        }
        html.dark .bg-rose-50,
        html.dark .bg-rose-100,
        html.dark .bg-red-50,
        html.dark .bg-red-100 {
            background-color: rgba(244, 63, 94, 0.15) !important;
            color: #fda4af !important;
            border-color: rgba(244, 63, 94, 0.3) !important;
        }

        html.dark span.bg-slate-100,
        html.dark td span.bg-slate-100 {
            background-color: #0f172a !important;
            color: #cbd5e1 !important;
            border-color: #334155 !important;
        }

        /* ========================================================================= */
        /* ULTRA-MODERN BENTO SWEETALERT2 MODAL SYSTEM (LIGHT & DARK MODE)            */
        /* ========================================================================= */
        .swal2-container {
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            background: rgba(15, 23, 42, 0.6) !important;
            padding: 1rem !important;
        }

        .swal2-popup {
            border-radius: 1.5rem !important;
            padding: 1.75rem 1.5rem !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(226, 232, 240, 0.8) !important;
            background: #ffffff !important;
            color: #0f172a !important;
            width: 100% !important;
            max-width: 24rem !important;
        }

        html.dark .swal2-popup {
            background: #1e293b !important;
            color: #f8fafc !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(51, 65, 85, 0.8) !important;
        }

        .swal2-title {
            font-size: 1.15rem !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            letter-spacing: -0.025em !important;
            padding: 0.5rem 0 0.25rem !important;
        }

        html.dark .swal2-title {
            color: #ffffff !important;
        }

        .swal2-html-container {
            font-size: 0.8125rem !important;
            line-height: 1.5 !important;
            color: #64748b !important;
            margin: 0.5rem 0 1.25rem !important;
        }

        html.dark .swal2-html-container {
            color: #94a3b8 !important;
        }

        .swal2-icon {
            width: 3.5rem !important;
            height: 3.5rem !important;
            margin: 0.5rem auto 0.75rem !important;
            border-width: 3px !important;
        }

        .swal2-icon.swal2-question {
            border-color: #3b82f6 !important;
            color: #2563eb !important;
            background: #eff6ff !important;
        }
        html.dark .swal2-icon.swal2-question {
            border-color: #60a5fa !important;
            color: #93c5fd !important;
            background: rgba(30, 58, 138, 0.3) !important;
        }

        .swal2-icon.swal2-warning {
            border-color: #f59e0b !important;
            color: #d97706 !important;
            background: #fffbeb !important;
        }
        html.dark .swal2-icon.swal2-warning {
            border-color: #fbbf24 !important;
            color: #fde68a !important;
            background: rgba(120, 53, 15, 0.3) !important;
        }

        .swal2-icon.swal2-success {
            border-color: #10b981 !important;
            color: #059669 !important;
            background: #ecfdf5 !important;
        }
        html.dark .swal2-icon.swal2-success {
            border-color: #34d399 !important;
            color: #6ee7b7 !important;
            background: rgba(6, 78, 59, 0.3) !important;
        }

        .swal2-icon.swal2-error {
            border-color: #ef4444 !important;
            color: #dc2626 !important;
            background: #fef2f2 !important;
        }
        html.dark .swal2-icon.swal2-error {
            border-color: #f87171 !important;
            color: #fca5a5 !important;
            background: rgba(127, 29, 29, 0.3) !important;
        }

        .swal2-actions {
            gap: 0.5rem !important;
            margin-top: 0.5rem !important;
            width: 100% !important;
            display: flex !important;
            justify-content: center !important;
        }

        .swal2-confirm, .swal2-cancel {
            border-radius: 0.875rem !important;
            font-weight: 700 !important;
            font-size: 0.8125rem !important;
            padding: 0.65rem 1.15rem !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex: 1 !important;
            max-width: 10rem !important;
        }

        .swal2-confirm:hover, .swal2-cancel:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 8px 12px -2px rgba(0, 0, 0, 0.15) !important;
        }

        .swal2-confirm:active, .swal2-cancel:active {
            transform: translateY(0) !important;
        }

        .swal2-cancel {
            background-color: #f1f5f9 !important;
            color: #64748b !important;
            border: 1px solid #e2e8f0 !important;
        }
        html.dark .swal2-cancel {
            background-color: #334155 !important;
            color: #cbd5e1 !important;
            border: 1px solid #475569 !important;
        }
        .swal2-cancel:hover {
            background-color: #e2e8f0 !important;
            color: #334155 !important;
        }
        html.dark .swal2-cancel:hover {
            background-color: #475569 !important;
            color: #f8fafc !important;
        }
    </style>
</head>
<body class="h-full bg-slate-50 dark:bg-[#121824] text-slate-800 dark:text-slate-100 antialiased font-sans selection:bg-rose-500 selection:text-white">

<div class="min-h-full flex">
    <!-- Sidebar Component -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Mobile Backdrop -->
    <div id="mobileBackdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 hidden transition-opacity"></div>

    <!-- Main Content Wrapper with dynamic responsive padding -->
    <div id="mainWrapper" class="flex-1 flex flex-col min-w-0 min-h-screen transition-all duration-300 lg:pl-72">
        
        <!-- Modern Top Header (100% Synced with Flutter Mobile AppBar & Clean Layout) -->
        <?php 
        $headerCurrPage = $_GET['page'] ?? 'dashboard';
        $isSubPage = ($headerCurrPage !== 'dashboard');

        // Determine back button target
        $backUrl = BASE_URL . '/index.php?page=dashboard';
        if ($headerCurrPage === 'profile-detail') {
            $backUrl = BASE_URL . '/index.php?page=profile';
        } elseif ($headerCurrPage === 'leave-detail') {
            $backUrl = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'leave-approvals') !== false 
                ? BASE_URL . '/index.php?page=leave-approvals' 
                : BASE_URL . '/index.php?page=leaves-my';
        } elseif (in_array($headerCurrPage, ['employee-create', 'employee-edit', 'employee-detail', 'employee-template'])) {
            $backUrl = BASE_URL . '/index.php?page=employees';
        }

        // Determine which right action buttons to show per screen
        $showBell = ($headerCurrPage === 'dashboard');
        $showRefresh = in_array($headerCurrPage, ['dashboard', 'leaves-my', 'leaves', 'leave-approvals', 'employees', 'quotas', 'team-attendance', 'board']);
        ?>
        <header class="sticky top-0 z-30 bg-white/95 dark:bg-[#1e293b]/95 backdrop-blur-xl border-b border-slate-200/80 dark:border-slate-800 px-3.5 sm:px-8 py-3 flex items-center justify-between transition-all no-print">
            <div class="flex items-center gap-2 sm:gap-4 min-w-0">
                <!-- Desktop Mini-Sidebar Toggle Button (Hidden on Mobile) -->
                <button type="button" id="sidebarToggleBtn" 
                        class="hidden lg:flex p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white border border-slate-200 dark:border-slate-700 shadow-2xs transition items-center justify-center cursor-pointer flex-shrink-0" 
                        title="Buka / Tutup Sidebar">
                    <i class="fa-solid fa-bars-staggered text-sm sm:text-base"></i>
                </button>
                
                <!-- Flutter-Style Mobile Back Button (Visible on Mobile Subpages) -->
                <?php if ($isSubPage): ?>
                    <a href="<?= $backUrl ?>" 
                       class="lg:hidden p-2 rounded-xl text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition flex items-center justify-center -ml-1 flex-shrink-0" 
                       title="Kembali">
                        <i class="fa-solid fa-arrow-left text-base"></i>
                    </a>
                <?php endif; ?>

                <div class="flex items-center gap-2.5 min-w-0">
                    <?php if (!$isSubPage): ?>
                        <div class="w-8 h-8 rounded-xl bg-white border border-slate-200/80 dark:border-slate-700 p-1 flex items-center justify-center shadow-2xs flex-shrink-0">
                            <img src="<?= $headerFaviconUrl ?>" alt="Nakakin" class="h-5 w-auto object-contain">
                        </div>
                    <?php endif; ?>
                    <div class="min-w-0">
                        <h1 class="text-sm sm:text-lg font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-1.5 leading-tight truncate">
                            <?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard' ?>
                        </h1>
                        <p class="text-[11px] text-slate-400 font-semibold hidden sm:block truncate">PT. Nakakin Indonesia &bull; Leave & Attendance System</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-1.5 sm:gap-3 flex-shrink-0">
                <!-- Realtime Clock Pill (Desktop Only) -->
                <div class="hidden md:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-100/80 dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 shadow-sm" id="liveClock">
                    <i class="fa-regular fa-clock text-blue-600 dark:text-sky-400"></i> Memuat waktu...
                </div>

                <!-- Desktop Dark Mode Toggle Button (Beside Clock) -->
                <button type="button" onclick="toggleGlobalDarkMode()" id="desktopDarkModeBtn"
                        class="hidden md:flex items-center justify-center p-2 rounded-xl bg-slate-100/80 hover:bg-slate-200/80 dark:bg-slate-800/80 dark:hover:bg-slate-700/80 border border-slate-200/60 dark:border-slate-700 text-slate-700 dark:text-slate-300 shadow-sm transition cursor-pointer" 
                        title="Beralih Tema Gelap / Terang">
                    <span class="material-symbols-rounded text-lg text-amber-500 dark:hidden">dark_mode</span>
                    <span class="material-symbols-rounded text-lg text-amber-400 hidden dark:inline">light_mode</span>
                </button>

                <!-- 1. Smart Bell Notification Icon with Flutter-Style Red Badge (Dashboard Only) -->
                <?php if ($showBell): ?>
                    <a href="<?= BASE_URL ?>/index.php?page=leave-approvals" 
                       class="relative p-2 rounded-xl text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition flex items-center justify-center" 
                       title="Persetujuan & Notifikasi Cuti">
                        <i class="fa-solid fa-bell text-base sm:text-lg"></i>
                        <?php if ($pendingApprovalCount > 0): ?>
                            <span class="absolute top-1 right-1 min-w-[17px] h-[17px] px-1 bg-[#FF3B30] text-white text-[9.5px] font-black rounded-full flex items-center justify-center shadow-sm border border-white dark:border-slate-800">
                                <?= $pendingApprovalCount > 99 ? '99+' : $pendingApprovalCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <!-- 2. Refresh Button (Identical to Flutter AppBar Refresh - Per Screen) -->
                <?php if ($showRefresh): ?>
                    <button type="button" onclick="location.reload()" 
                            class="p-2 rounded-xl text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition flex items-center justify-center cursor-pointer" 
                            title="Segarkan Halaman">
                        <i class="fa-solid fa-rotate-right text-sm sm:text-base"></i>
                    </button>
                <?php endif; ?>

                <!-- 3. Profile Avatar & Dropdown Menu (Hidden on Mobile, Visible on Desktop) -->
                <?php if ($currentUser): ?>
                    <div class="hidden sm:block relative ml-1 sm:ml-2">
                        <button type="button" id="userMenuBtn" 
                                class="flex items-center gap-2 sm:gap-2.5 p-1 sm:px-2.5 sm:py-1.5 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800 border border-transparent hover:border-slate-200 dark:hover:border-slate-700 transition cursor-pointer">
                            <div class="w-8 h-8 sm:w-8 sm:h-8 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 shadow-sm border border-white dark:border-slate-700">
                                <?= strtoupper(substr($currentUser['nama_lengkap'] ?? 'U', 0, 1)) ?>
                            </div>
                            <div class="hidden xl:block text-left min-w-0 max-w-[120px]">
                                <div class="text-xs font-bold text-slate-900 dark:text-white truncate leading-tight">
                                    <?= htmlspecialchars(explode(' ', $currentUser['nama_lengkap'] ?? 'User')[0]) ?>
                                </div>
                                <div class="text-[10px] text-slate-400 font-semibold uppercase truncate">
                                    <?= htmlspecialchars($currentUser['role'] ?? 'User') ?>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 hidden sm:block"></i>
                        </button>

                        <!-- User Dropdown Menu -->
                        <div id="userDropdown" class="hidden absolute right-0 mt-2 w-64 rounded-2xl bg-white dark:bg-[#1e293b] border border-slate-200/90 dark:border-slate-800 shadow-2xl p-2 z-50 animate-in fade-in zoom-in-95 duration-150">
                            
                            <!-- Dropdown User Info Card -->
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-sm flex items-center justify-center flex-shrink-0 shadow-sm">
                                        <?= strtoupper(substr($currentUser['nama_lengkap'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs font-black text-slate-900 dark:text-white truncate">
                                            <?= htmlspecialchars($currentUser['nama_lengkap'] ?? 'User') ?>
                                        </div>
                                        <div class="text-[10.5px] text-slate-400 truncate">
                                            <?= htmlspecialchars($currentUser['nama_dept'] ?? '-') ?> &bull; <?= htmlspecialchars($currentUser['nik'] ?? '') ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2.5 pt-2 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between">
                                    <span class="text-[10px] text-slate-400 font-semibold">Jabatan</span>
                                    <span class="px-2 py-0.5 rounded-md bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 text-[10px] font-bold">
                                        <?= htmlspecialchars($currentUser['nama_jabatan'] ?? 'Karyawan') ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Dropdown Navigation Links -->
                            <div class="space-y-0.5 text-xs">
                                <a href="<?= BASE_URL ?>/index.php?page=profile" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-sky-400 transition font-semibold">
                                    <i class="fa-solid fa-user-gear text-slate-400 w-4 text-center"></i>
                                    <span>Lihat Profil Saya</span>
                                </a>
                                <a href="<?= BASE_URL ?>/index.php?page=leave-create" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-rose-600 dark:hover:text-rose-400 transition font-semibold">
                                    <i class="fa-solid fa-calendar-plus text-slate-400 w-4 text-center"></i>
                                    <span>Ajukan Cuti Baru</span>
                                </a>
                                <a href="<?= BASE_URL ?>/index.php?page=leaves-my" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-sky-400 transition font-semibold">
                                    <i class="fa-solid fa-clock-rotate-left text-slate-400 w-4 text-center"></i>
                                    <span>Riwayat Cuti Saya</span>
                                </a>
                                <?php if (in_array(strtolower($currentUser['role'] ?? ''), ['admin', 'superadmin'])): ?>
                                    <a href="<?= BASE_URL ?>/index.php?page=settings" 
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition font-semibold">
                                        <i class="fa-solid fa-sliders text-slate-400 w-4 text-center"></i>
                                        <span>Pengaturan Sistem</span>
                                    </a>
                                <?php endif; ?>
                            </div>

                            <!-- Logout Divider & Button -->
                            <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                                <a href="javascript:void(0)" onclick="confirmLogout()" 
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition font-bold text-xs cursor-pointer">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-rose-500 w-4 text-center"></i>
                                    <span>Keluar (Logout)</span>
                                </a>
                            </div>

                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <!-- Main Content Body (Responsive & App-Like Spacing) -->
        <main class="flex-1 p-3.5 sm:p-8 w-full pb-28 lg:pb-8">
