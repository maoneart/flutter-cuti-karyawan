<?php
/**
 * Permission Helper & Model (Standalone for API)
 */

if (!class_exists('Permission')) {
    class Permission {
        public static function getAllMatrix($pdo = null) {
            if (!$pdo) $pdo = getDbConnection();
            $stmt = $pdo->query("SELECT * FROM role_permissions ORDER BY category ASC, id ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public static function getByRole($role, $pdo = null) {
            if (!$pdo) $pdo = getDbConnection();
            $stmt = $pdo->prepare("SELECT permission_key, permission_name, category, description, is_granted FROM role_permissions WHERE role = ?");
            $stmt->execute([$role]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $permissions = [];
            foreach ($rows as $r) {
                $permissions[$r['permission_key']] = (int)$r['is_granted'] === 1;
            }
            return [
                'role' => $role,
                'map' => $permissions,
                'list' => $rows
            ];
        }

        public static function can($role, $permissionKey, $pdo = null) {
            if (!$pdo) $pdo = getDbConnection();
            if ($role === 'superadmin' || $role === 'admin') return true;

            $stmt = $pdo->prepare("SELECT is_granted FROM role_permissions WHERE role = ? AND permission_key = ? LIMIT 1");
            $stmt->execute([$role, $permissionKey]);
            $val = $stmt->fetchColumn();
            return $val !== false && (int)$val === 1;
        }

        public static function updateMatrix($matrix, $pdo = null) {
            if (!$pdo) $pdo = getDbConnection();
            $roles = ['operator', 'staff', 'leader', 'supervisor', 'manager', 'hrd', 'superadmin'];
            
            $stmtKeys = $pdo->query("SELECT DISTINCT permission_key FROM role_permissions");
            $keys = $stmtKeys->fetchAll(PDO::FETCH_COLUMN);

            $pdo->beginTransaction();
            try {
                $updateStmt = $pdo->prepare("UPDATE role_permissions SET is_granted = ? WHERE role = ? AND permission_key = ?");
                
                foreach ($roles as $role) {
                    foreach ($keys as $key) {
                        $isGranted = isset($matrix[$role][$key]) && ($matrix[$role][$key] === '1' || $matrix[$role][$key] === 1 || $matrix[$role][$key] === true) ? 1 : 0;
                        $updateStmt->execute([$isGranted, $role, $key]);
                    }
                }
                $pdo->commit();
                return true;
            } catch (Exception $e) {
                $pdo->rollBack();
                return false;
            }
        }

        public static function toggle($role, $permissionKey, $isGranted, $pdo = null) {
            if (!$pdo) $pdo = getDbConnection();
            $stmt = $pdo->prepare("UPDATE role_permissions SET is_granted = ? WHERE role = ? AND permission_key = ?");
            return $stmt->execute([$isGranted ? 1 : 0, $role, $permissionKey]);
        }
    }
}
