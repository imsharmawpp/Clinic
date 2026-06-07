<?php
require_once __DIR__ . '/../config/bootstrap.php';

$action     = sanitize($_GET['action'] ?? 'index');
$controller = new DoctorController();
$id         = sanitize_int($_GET['id'] ?? 0);

match ($action) {
    'create' => $controller->create(),
    'store'  => $controller->store(),
    'show'   => $controller->show($id),
    'edit'   => $controller->edit($id),
    'update' => $controller->update($id),
    default  => $controller->index(),
};
