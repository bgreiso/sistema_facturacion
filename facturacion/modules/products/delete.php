<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID de producto no proporcionado";
    header('Location: list.php');
    exit;
}

$productId = (int)$_GET['id'];

// Verificar si el producto existe
$stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $_SESSION['error'] = "Producto no encontrado";
    header('Location: list.php');
    exit;
}

// Eliminar el producto con información de auditoría
// Primero actualizamos para registrar quién eliminó el producto
$update = $conn->prepare("UPDATE products SET updated_by = ?, action_type = 'deleted' WHERE id = ?");
$update->bind_param("ii", $_SESSION['user_id'], $productId);
$update->execute();
$update->close();

// Luego procedemos con la eliminación
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);

if ($stmt->execute()) {
    $_SESSION['success'] = "Producto eliminado correctamente";
} else {
    $_SESSION['error'] = "Error al eliminar el producto: " . $conn->error;
}

header('Location: list.php');
exit;
?>