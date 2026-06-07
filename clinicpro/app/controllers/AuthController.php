<?php
// app/controllers/AuthController.php

class AuthController {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function showLogin(): void {
        if (is_logged_in()) redirect(APP_URL . '/dashboard.php');
        render('auth/login', ['title' => 'Login — ' . APP_NAME]);
    }

    public function login(): void {
        verify_csrf();

        $email    = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            flash('error', 'Email and password are required.');
            redirect(APP_URL . '/login.php');
        }

        // Fetch user
        $stmt = $this->db->prepare("
            SELECT u.*, r.slug AS role_slug, c.name AS clinic_name, c.status AS clinic_status
            FROM users u
            JOIN roles r ON r.id = u.role_id
            JOIN clinics c ON c.id = u.clinic_id
            WHERE u.email = ? LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Lock check
        if ($user && $user['locked_until'] && strtotime($user['locked_until']) > time()) {
            flash('error', 'Account locked. Try again after ' . format_datetime($user['locked_until']));
            redirect(APP_URL . '/login.php');
        }

        if (!$user || !password_verify($password, $user['password'])) {
            if ($user) {
                $attempts = $user['failed_attempts'] + 1;
                $lockUntil = $attempts >= 5 ? date('Y-m-d H:i:s', strtotime('+15 minutes')) : null;
                $this->db->prepare("UPDATE users SET failed_attempts=?, locked_until=? WHERE id=?")
                    ->execute([$attempts, $lockUntil, $user['id']]);
            }
            flash('error', 'Invalid email or password.');
            redirect(APP_URL . '/login.php');
        }

        if ($user['status'] !== 'active') {
            flash('error', 'Your account is inactive. Contact administrator.');
            redirect(APP_URL . '/login.php');
        }

        if ($user['clinic_status'] !== 'active') {
            flash('error', 'Clinic account is suspended. Contact support.');
            redirect(APP_URL . '/login.php');
        }

        // Reset failed attempts
        $this->db->prepare("UPDATE users SET failed_attempts=0, locked_until=NULL, last_login_at=NOW(), last_login_ip=? WHERE id=?")
            ->execute([$_SERVER['REMOTE_ADDR'] ?? null, $user['id']]);

        // Set session
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'          => $user['id'],
            'name'        => $user['name'],
            'email'       => $user['email'],
            'role_id'     => $user['role_id'],
            'role'        => $user['role_slug'],
            'clinic_id'   => $user['clinic_id'],
            'clinic_name' => $user['clinic_name'],
            'avatar'      => $user['avatar'],
            'is_super'    => (bool) $user['is_super_admin'],
        ];

        // Load permissions
        unset($_SESSION['permissions']);
        AuthMiddleware::loadPermissions($user['role_id']);

        audit('auth', 'login', $user['id']);
        redirect(APP_URL . '/dashboard.php');
    }

    public function logout(): void {
        audit('auth', 'logout', auth()['id'] ?? 0);
        session_destroy();
        redirect(APP_URL . '/login.php');
    }
}
