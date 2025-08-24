<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add.php');
    exit;
}

// Recoger y sanitizar datos
$name = trim($_POST['name'] ?? '');
$ruc = trim($_POST['ruc'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');

// Validaciones
$errors = [];

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

// Si hay errores, regresar al formulario
if (!empty($errors)) {
    $_SESSION['error'] = implode("<br>", $errors);
    $_SESSION['form_data'] = $_POST;
    header('Location: add.php');
    exit;
}

// Insertar en la base de datos con información de auditoría
$stmt = $conn->prepare("INSERT INTO providers (name, ruc, phone, email, address, created_by, updated_by, action_type) VALUES (?, ?, ?, ?, ?, ?, ?, 'created')");
$stmt->bind_param("sssssii", $name, $ruc, $phone, $email, $address, $_SESSION['user_id'], $_SESSION['user_id']);

if ($stmt->execute()) {
    $_SESSION['success'] = "Proveedor agregado correctamente";
    header('Location: list.php');
} else {
    $_SESSION['error'] = "Error al agregar el proveedor: " . $conn->error;
    $_SESSION['form_data'] = $_POST;
    header('Location: add.php');
}
exit;
?>