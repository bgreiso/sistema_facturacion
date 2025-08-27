<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';

// Verificar permisos y método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Acceso no autorizado";
    header('Location: ' . BASE_URL . '/modules/products/list.php');
    exit();
}

// Validar y sanitizar datos
$required_fields = ['code', 'description', 'price', 'stock', 'provider_id', 'measure_unit'];
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
$stock = (float)$_POST['stock']; // Cambiado a float para soportar decimales
$provider_id = (int)$_POST['provider_id'];
$measure_unit = $conn->real_escape_string($_POST['measure_unit']);
$min_stock = isset($_POST['min_stock']) ? (float)$_POST['min_stock'] : 5;

// Insertar en base de datos con información de auditoría
$stmt = $conn->prepare("INSERT INTO products (code, description, price, stock, provider_id, measure_unit, min_stock, created_by, updated_by, action_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'created')");

if ($stmt === false) {
    $_SESSION['error'] = "Error en la consulta: " . $conn->error;
    header('Location: ' . BASE_URL . '/modules/products/add.php');
    exit();
}

$stmt->bind_param("ssddissii", $code, $description, $price, $stock, $provider_id, $measure_unit, $min_stock, $_SESSION['user_id'], $_SESSION['user_id']);

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