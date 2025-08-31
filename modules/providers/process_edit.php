<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: list.php');
    exit;
}

// Recoger y sanitizar datos
$id = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$ruc = trim($_POST['ruc'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');

// Validaciones
$errors = [];

if (empty($id)) {
    $errors[] = "ID de proveedor no válido.";
}

if (empty($name)) {
    $errors[] = "El nombre del proveedor es requerido";
}

if (empty($ruc)) {
    $errors[] = "El RIF es requerido";
} elseif (!preg_match('/^[JVEG][-]\d{8}[-]\d{1}$/', $ruc)) {
    $errors[] = "El formato del RIF no es válido (Ejemplo: J-12345678-9)";
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "El formato del email no es válido";
}

// Si hay errores, regresar al formulario de edición
if (!empty($errors)) {
    $_SESSION['error'] = implode("<br>", $errors);
    header("Location: edit.php?id=$id");
    exit;
}

// Actualizar en la base de datos con información de auditoría
$stmt = $conn->prepare("UPDATE providers SET name = ?, ruc = ?, phone = ?, email = ?, address = ?, updated_by = ?, action_type = 'updated' WHERE id = ?");

// ----- LÍNEA CORREGIDA -----
// Se cambió "sssssiii" (8 caracteres) por "sssssii" (7 caracteres) para que coincida con las 7 variables.
$stmt->bind_param("sssssii", $name, $ruc, $phone, $email, $address, $_SESSION['user_id'], $id);


if ($stmt->execute()) {
    $_SESSION['success'] = "Proveedor actualizado correctamente";
    header('Location: list.php');
} else {
    // ----- LÍNEA MEJORADA PARA DEPURACIÓN -----
    // Usamos $stmt->error en lugar de $conn->error para obtener un mensaje más preciso.
    $_SESSION['error'] = "Error al actualizar el proveedor: " . $stmt->error;
    header("Location: edit.php?id=$id");
}
exit;
?>