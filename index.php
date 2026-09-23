<?php
/**
 * Main Application Router & Entry Point
 * PT. Nakakin Indonesia Leave Management System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/config/session.php';

// Load Controllers
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/LeaveController.php';
require_once __DIR__ . '/controllers/EmployeeController.php';
require_once __DIR__ . '/controllers/QuotaController.php';
require_once __DIR__ . '/controllers/ReportController.php';
require_once __DIR__ . '/controllers/SettingController.php';

$page = $_GET['page'] ?? 'home';

// Route Definitions
switch ($page) {
    // Public Today's Attendance & Leave Board (Front Page / TV Display)
    case 'home':
    case 'public':
    case 'board':
        require __DIR__ . '/views/public/board.php';
        break;

    // Auth Routes
    case 'login':
        require __DIR__ . '/views/auth/login.php';
        break;

    case 'login-process':
        (new AuthController())->login();
        break;

    case 'logout':
        (new AuthController())->logout();
        break;

    // Dashboard
    case 'dashboard':
        requireLogin();
        require __DIR__ . '/views/dashboard/index.php';
        break;

    // Leave Management
    case 'leave-create':
        requireLogin();
        require __DIR__ . '/views/leaves/create.php';
        break;

    case 'leave-submit':
        (new LeaveController())->submit();
        break;

    case 'leaves-my':
        requireLogin();
        require __DIR__ . '/views/leaves/index.php';
        break;

    case 'leave-detail':
        requireLogin();
        require __DIR__ . '/views/leaves/detail.php';
        break;

    case 'leave-approvals':
        requireLogin();
        requireRole(['atasan', 'admin']);
        require __DIR__ . '/views/leaves/approvals.php';
        break;

    case 'leaves-team':
        requireLogin();
        requireRole(['atasan', 'admin']);
        require __DIR__ . '/views/leaves/team.php';
        break;

    case 'leave-action':
        $action = $_GET['action'] ?? '';
        $leaveCtrl = new LeaveController();
        if ($action === 'approve') {
            $leaveCtrl->approve();
        } elseif ($action === 'reject') {
            $leaveCtrl->reject();
        } elseif ($action === 'cancel') {
            $leaveCtrl->cancel();
        } else {
            header('Location: ' . BASE_URL . '/index.php?page=dashboard');
        }
        break;

    case 'leave-print':
        require __DIR__ . '/views/leaves/print_leave.php';
        break;

    // Quota Management (HRD)
    case 'quotas':
        requireRole('admin');
        require __DIR__ . '/views/quotas/index.php';
        break;

    case 'quota-adjust':
        (new QuotaController())->adjust();
        break;

    case 'quota-batch-reset':
        (new QuotaController())->batchReset();
        break;

    // Employee Management (HRD)
    case 'employees':
        requireRole('admin');
        require __DIR__ . '/views/employees/index.php';
        break;

    case 'employee-create':
        requireRole('admin');
        require __DIR__ . '/views/employees/create.php';
        break;

    case 'employee-save':
        (new EmployeeController())->save();
        break;

    case 'employee-edit':
        requireRole('admin');
        require __DIR__ . '/views/employees/edit.php';
        break;

    case 'employee-update':
        (new EmployeeController())->update();
        break;

    case 'employee-delete':
        (new EmployeeController())->delete();
        break;

    // Profile Management
    case 'profile':
        requireLogin();
        require __DIR__ . '/views/employees/profile.php';
        break;

    case 'profile-update':
        (new EmployeeController())->updateProfile();
        break;

    // Reports (HRD)
    case 'reports':
        requireRole('admin');
        require __DIR__ . '/views/reports/index.php';
        break;

    case 'report-export':
        (new ReportController())->exportExcel();
        break;

    // Application & Company Settings (HRD Admin)
    case 'settings':
        (new SettingController())->index();
        break;

    case 'settings-update':
        (new SettingController())->update();
        break;

    default:
        require __DIR__ . '/views/public/board.php';
        break;
}
