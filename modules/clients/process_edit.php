<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header('Location: list.php');
    exit;
}

// Recoger y sanitizar datos
$id = trim($_POST['id']);
$name = trim($_POST['name'] ?? '');
$ruc = trim($_POST['ruc'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');

// Validaciones
$errors = [];

if (empty($name)) {
    $errors[] = "El nombre del cliente es requerido";
}

if (empty($ruc)) {
    $errors[] = "La cédula/RIF es requerido";
} elseif (!preg_match('/^[VJEG][-]\d{5,9}([-]\d{1})?$/', $ruc)) {
    $errors[] = "Formato de cédula/RIF no válido (Ej: V-12345678 o J-12345678-9)";
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "El formato del email no es válido";
}

// Verificar si el nuevo RUC ya existe para otro cliente
$stmt_check = $conn->prepare("SELECT id FROM clients WHERE ruc = ? AND id != ?");
$stmt_check->bind_param("si", $ruc, $id);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    $errors[] = "Ya existe un cliente con esta cédula/RIF";
}

// Si hay errores, regresar al formulario de edición
if (!empty($errors)) {
    $_SESSION['error'] = implode("<br>", $errors);
    $_SESSION['form_data'] = $_POST;
    header('Location: edit.php?id=' . $id);
    exit;
}

// Insertar en la base de datos con información de auditoría
$stmt = $conn->prepare("UPDATE clients SET name = ?, ruc = ?, phone = ?, email = ?, address = ?, updated_by = ?, action_type = 'updated' WHERE id = ?");
$stmt->bind_param("sssssii", $name, $ruc, $phone, $email, $address, $_SESSION['user_id'], $id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Cliente actualizado correctamente";
    header('Location: list.php');
} else {
    $_SESSION['error'] = "Error al actualizar el cliente: " . $conn->error;
    $_SESSION['form_data'] = $_POST;
    header('Location: edit.php?id=' . $id);
}
exit;
?>