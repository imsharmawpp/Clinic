<?php
require_once __DIR__ . '/../config/bootstrap.php';

$action     = sanitize($_GET['action'] ?? 'index');
$controller = new BillingController();
$id         = sanitize_int($_GET['id'] ?? 0);

match ($action) {
    'create'  => $controller->create(),
    'store'   => $controller->store(),
    'show'    => $controller->show($id),
    'payment' => $controller->payment(),
    default   => $controller->index(),
};
