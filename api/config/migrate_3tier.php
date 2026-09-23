<?php
/**
 * Database Migration for 3-Tier Multi-Level Approval
 */
require_once __DIR__ . '/../../config/database.php';

$pdo = getDbConnection();

$columns = [
    'approval_step' => "VARCHAR(30) NOT NULL DEFAULT 'pending_spv' COMMENT 'pending_spv, pending_manager, pending_hrd, approved, rejected, cancelled'",
    'spv_id' => "INT NULL",
    'spv_at' => "DATETIME NULL",
    'spv_notes' => "TEXT NULL",
    'manager_id' => "INT NULL",
    'manager_at' => "DATETIME NULL",
    'manager_notes' => "TEXT NULL",
    'hrd_id' => "INT NULL",
    'hrd_at' => "DATETIME NULL",
    'hrd_notes' => "TEXT NULL",
    'notif_read' => "TINYINT(1) NOT NULL DEFAULT 0",
];

foreach ($columns as $name => $def) {
    try {
        $check = $pdo->query("SHOW COLUMNS FROM pengajuan_cuti LIKE '$name'");
        if ($check->rowCount() === 0) {
            $pdo->exec("ALTER TABLE pengajuan_cuti ADD COLUMN `$name` $def");
            echo "Added column: $name\n";
        } else {
            echo "Column already exists: $name\n";
        }
    } catch (Exception $e) {
        echo "Error on $name: " . $e->getMessage() . "\n";
    }
}

// Update existing pending records if any
$pdo->exec("UPDATE pengajuan_cuti SET approval_step = 'pending_spv' WHERE status = 'pending' AND (approval_step IS NULL OR approval_step = '')");
$pdo->exec("UPDATE pengajuan_cuti SET approval_step = status WHERE status IN ('approved', 'rejected', 'cancelled')");

echo "Migration finished successfully!\n";
