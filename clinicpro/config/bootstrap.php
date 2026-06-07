<?php
// config/bootstrap.php

require_once __DIR__ . '/app.php';
require_once __DIR__ . '/database.php';

// Error handling
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Autoloader (PSR-4 style, no Composer needed for this project)
spl_autoload_register(function (string $class): void {
    $map = [
        'Controller' => APP_PATH . '/controllers/',
        'Model'      => APP_PATH . '/models/',
        'Service'    => APP_PATH . '/services/',
        'Repository' => APP_PATH . '/repositories/',
        'Middleware' => APP_PATH . '/middleware/',
    ];
    foreach ($map as $suffix => $dir) {
        if (str_ends_with($class, $suffix) || str_starts_with($class, $suffix)) {
            $file = $dir . $class . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
    // Helpers and misc
    $file = APP_PATH . '/' . $class . '.php';
    if (file_exists($file)) require_once $file;
});

// Session
ini_set('session.name', SESSION_NAME);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
if (!session_id()) session_start();

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Load helpers
require_once APP_PATH . '/helpers.php';

// Load all needed files
foreach (glob(APP_PATH . '/models/*.php') as $f)      require_once $f;
foreach (glob(APP_PATH . '/repositories/*.php') as $f) require_once $f;
foreach (glob(APP_PATH . '/services/*.php') as $f)     require_once $f;
foreach (glob(APP_PATH . '/middleware/*.php') as $f)   require_once $f;
foreach (glob(APP_PATH . '/controllers/*.php') as $f)  require_once $f;
