<?php
require_once __DIR__ . '/../config/bootstrap.php';

$action     = sanitize($_GET['action'] ?? 'index');
$controller = new LabController();
$id         = sanitize_int($_GET['id'] ?? 0);

match ($action) {
    'create'        => $controller->create(),
    'store'         => $controller->store(),
    'show'          => $controller->show($id),
    'updateStatus'  => $controller->updateStatus(),
    'saveResults'   => $controller->saveResults(),
    'tests'         => $controller->tests(),
    default         => $controller->index(),
};
