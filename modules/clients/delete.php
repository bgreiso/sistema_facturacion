<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID de cliente no especificado";
    header('Location: list.php');
    exit;
}

$client_id = $_GET['id'];

// Preparar y ejecutar la consulta de eliminación
$stmt = $conn->prepare("DELETE FROM clients WHERE id = ?");
$stmt->bind_param("i", $client_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Cliente eliminado correctamente";
} else {
    $_SESSION['error'] = "Error al eliminar el cliente: " . $conn->error;
}

$stmt->close();
header('Location: list.php');
exit;
?>