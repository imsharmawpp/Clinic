<?php
// app/helpers.php

// ── CSRF ──────────────────────────────────────────────────────
function csrf_token(): string {
    return $_SESSION['csrf_token'] ?? '';
}

function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token()) . '">';
}

function verify_csrf(): void {
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die(json_encode(['error' => 'Invalid CSRF token']));
    }
}

// ── SANITIZATION ─────────────────────────────────────────────
function e(mixed $v): string {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function sanitize(string $v): string {
    return trim(strip_tags($v));
}

function sanitize_int(mixed $v): int {
    return (int) filter_var($v, FILTER_SANITIZE_NUMBER_INT);
}

function sanitize_float(mixed $v): float {
    return (float) filter_var($v, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

// ── AUTH ──────────────────────────────────────────────────────
function auth(): ?array {
    return $_SESSION['user'] ?? null;
}

function clinic_id(): int {
    return (int)($_SESSION['user']['clinic_id'] ?? 0);
}

function is_logged_in(): bool {
    return isset($_SESSION['user']['id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function has_permission(string $module, string $action): bool {
    $perms = $_SESSION['permissions'] ?? [];
    return in_array($module . '.' . $action, $perms, true);
}

function require_permission(string $module, string $action): void {
    if (!has_permission($module, $action)) {
        http_response_code(403);
        die(view_error(403, 'You do not have permission to perform this action.'));
    }
}

// ── RESPONSE HELPERS ─────────────────────────────────────────
function json_response(mixed $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function view_error(int $code, string $msg): string {
    return "<h3>$code — $msg</h3>";
}

// ── FORMATTING ────────────────────────────────────────────────
function currency(float $amount): string {
    return APP_CURRENCY . number_format($amount, 2);
}

function format_date(string $date, string $fmt = 'd M Y'): string {
    if (empty($date) || $date === '0000-00-00') return '—';
    return date($fmt, strtotime($date));
}

function format_datetime(string $dt): string {
    if (empty($dt)) return '—';
    return date('d M Y, h:i A', strtotime($dt));
}

function age_from_dob(?string $dob): ?int {
    if (empty($dob)) return null;
    return (int) date_diff(date_create($dob), date_create('today'))->y;
}

function generate_id(string $prefix, int $number, int $pad = 6): string {
    return $prefix . '-' . str_pad($number, $pad, '0', STR_PAD_LEFT);
}

// ── PAGINATION ────────────────────────────────────────────────
function paginate(int $total, int $page, int $perPage = PER_PAGE): array {
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page       = max(1, min($page, $totalPages));
    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $page,
        'last'        => $totalPages,
        'offset'      => ($page - 1) * $perPage,
        'has_prev'    => $page > 1,
        'has_next'    => $page < $totalPages,
    ];
}

// ── FLASH MESSAGES ────────────────────────────────────────────
function flash(string $type, string $msg): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $msg];
}

function get_flash(): array {
    $msgs = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $msgs;
}

// ── FILE UPLOAD ───────────────────────────────────────────────
function upload_file(array $file, string $subdir = 'misc'): string|false {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > MAX_UPLOAD_SIZE) return false;

    $allowed = array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_DOC_TYPES);
    $finfo   = new finfo(FILEINFO_MIME_TYPE);
    $mime    = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowed, true)) return false;

    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dir  = UPLOAD_PATH . '/' . $subdir;
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) return false;

    return 'uploads/' . $subdir . '/' . $name;
}

// ── AUDIT ─────────────────────────────────────────────────────
function audit(string $module, string $action, int $recordId = 0, array $old = [], array $new = []): void {
    try {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO audit_logs (clinic_id,user_id,module,action,record_id,old_data,new_data,ip_address,user_agent) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            clinic_id(),
            auth()['id'] ?? null,
            $module,
            $action,
            $recordId ?: null,
            $old ? json_encode($old) : null,
            $new ? json_encode($new) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 300),
        ]);
    } catch (Throwable) {}
}

// ── NEXT SEQUENCE ─────────────────────────────────────────────
function next_sequence(string $table, string $col, int $clinicId): int {
    $db   = Database::getInstance();
    $stmt = $db->prepare("SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX($col,'-',-1) AS UNSIGNED)),0)+1 FROM `$table` WHERE clinic_id=?");
    $stmt->execute([$clinicId]);
    return (int) $stmt->fetchColumn();
}

// ── VIEW RENDERER ─────────────────────────────────────────────
function render(string $view, array $data = []): void {
    extract($data, EXTR_SKIP);
    $file = VIEW_PATH . '/' . $view . '.php';
    if (!file_exists($file)) {
        echo "<p>View not found: $view</p>";
        return;
    }
    include $file;
}
