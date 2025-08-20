<?php
// Configuración de la base de datos
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'sistema_facturacion';

// Establecer conexión MySQLi
$conn = new mysqli('localhost', 'root', '', 'sistema_facturacion');

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Configuración de rutas
define('BASE_URL', 'http://localhost/facturacion');
define('BASE_PATH', realpath(dirname(__FILE__) . '/..'));

// Configuración básica de sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Función simplificada para cargar archivos
function loadInclude($path) {
    require_once BASE_PATH . '/' . ltrim($path, '/');
}
?>