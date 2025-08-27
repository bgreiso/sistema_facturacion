<?php
require_once 'includes/config.php';

$allowed_routes = [
    'dashboard' => '/dashboard.php',
    'login' => '/modules/auth/login.php',
    'products' => '/modules/products/list.php',
    'new_invoice' => '/modules/invoices/create.php',
    'clients' => '/modules/clients/list.php'
];

if(isset($_GET['to']) && array_key_exists($_GET['to'], $allowed_routes)) {
    header('Location: ' . BASE_URL . $allowed_routes[$_GET['to']]);
    exit();
}

header('Location: ' . BASE_URL);
exit();
?>