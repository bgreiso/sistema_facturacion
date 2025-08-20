<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';

// Verificar permisos y método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Acceso no autorizado";
    header('Location: ' . BASE_URL . '/modules/products/list.php');
    exit();
}

// Validar y sanitizar datos
$required_fields = ['code', 'description', 'price', 'stock', 'provider_id'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['error'] = "Todos los campos son requeridos";
        header('Location: ' . BASE_URL . '/modules/products/add.php');
        exit();
    }
}

// Preparar datos
$code = $conn->real_escape_string($_POST['code']);
$description = $conn->real_escape_string($_POST['description']);
$price = (float)$_POST['price'];
$stock = (int)$_POST['stock'];
$provider_id = (int)$_POST['provider_id'];

// Insertar en base de datos
$stmt = $conn->prepare("INSERT INTO products (code, description, price, stock, provider_id) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssdii", $code, $description, $price, $stock, $provider_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Producto agregado correctamente";
    header('Location: ' . BASE_URL . '/modules/products/list.php');
} else {
    $_SESSION['error'] = "Error al agregar producto: " . $conn->error;
    header('Location: ' . BASE_URL . '/modules/products/add.php');
}

$stmt->close();
exit();
?>