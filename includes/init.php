<?php
// Asegúrate que config.php se cargue desde la ruta correcta
$config_path = __DIR__ . '/config.php';
if (!file_exists($config_path)) {
    die("Error: No se pudo encontrar config.php");
}
require_once $config_path;

// Verificar que la conexión existe
if (!isset($conn)) {
    die("Error: Conexión a base de datos no establecida");
}

// Función de autenticación
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . BASE_URL . '/modules/auth/login.php');
        exit();
    }
}
?>