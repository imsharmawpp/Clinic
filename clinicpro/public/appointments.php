<?php
require_once __DIR__ . '/../config/bootstrap.php';

$action     = sanitize($_GET['action'] ?? 'index');
$controller = new AppointmentController();
$id         = sanitize_int($_GET['id'] ?? 0);

match ($action) {
    'create'       => $controller->create(),
    'store'        => $controller->store(),
    'updateStatus' => $controller->updateStatus(),
    'slots'        => $controller->slots(),
    default        => $controller->index(),
};
