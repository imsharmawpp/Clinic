<?php
// app/middleware/AuthMiddleware.php

class AuthMiddleware {

    public static function check(): void {
        if (!is_logged_in()) {
            if (self::isAjax()) {
                json_response(['error' => 'Unauthenticated', 'redirect' => APP_URL . '/login.php'], 401);
            }
            redirect(APP_URL . '/login.php');
        }
    }

    public static function loadPermissions(int $roleId): void {
        if (isset($_SESSION['permissions'])) return;

        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT CONCAT(p.module,'.',p.action) AS perm
            FROM role_permissions rp
            JOIN permissions p ON p.id = rp.permission_id
            WHERE rp.role_id = ?
        ");
        $stmt->execute([$roleId]);
        $_SESSION['permissions'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private static function isAjax(): bool {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }
}
