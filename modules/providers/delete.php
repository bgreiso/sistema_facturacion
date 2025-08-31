<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

// Validar que se reciba un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID de proveedor no válido.";
    header('Location: list.php');
    exit;
}

$id = (int)$_GET['id'];

// Preparar y ejecutar la consulta de eliminación
$stmt = $conn->prepare("DELETE FROM providers WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Verificar si alguna fila fue realmente eliminada
    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = "Proveedor eliminado correctamente.";
    } else {
        $_SESSION['error'] = "No se encontró el proveedor para eliminar o ya fue eliminado.";
    }
} else {
    $_SESSION['error'] = "Error al eliminar el proveedor: " . $conn->error;
}

// Redirigir de vuelta a la lista
header('Location: list.php');
exit;
?>