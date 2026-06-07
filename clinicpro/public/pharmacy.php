<?php
require_once __DIR__ . '/../config/bootstrap.php';

$action     = sanitize($_GET['action'] ?? 'index');
$controller = new PharmacyController();

match ($action) {
    'add_medicine'   => $controller->addMedicine(),
    'storeMedicine'  => $controller->storeMedicine(),
    'add_stock'      => $controller->addStock(),
    'storeStock'     => $controller->storeStock(),
    'search'         => $controller->search(),
    default          => $controller->index(),
};
