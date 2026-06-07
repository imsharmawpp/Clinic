<?php
require_once __DIR__ . '/../config/bootstrap.php';

$action     = sanitize($_GET['action'] ?? 'index');
$controller = new SettingsController();

match ($action) {
    'updateClinic' => $controller->updateClinic(),
    'createUser'   => $controller->createUser(),
    'toggleUser'   => $controller->toggleUser(),
    default        => $controller->index(),
};
