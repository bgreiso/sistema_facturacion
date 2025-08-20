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

// Verificar si el cliente ya existe
$stmt_check = $conn->prepare("SELECT id FROM clients WHERE ruc = ?");
$stmt_check->bind_param("s", $ruc);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    $errors[] = "Ya existe un cliente con esta cédula/RIF";
}

// Si hay errores, regresar al formulario
if (!empty($errors)) {
    $_SESSION['error'] = implode("<br>", $errors);
    $_SESSION['form_data'] = $_POST;
    header('Location: add.php');
    exit;
}

// Insertar en la base de datos
$stmt = $conn->prepare("INSERT INTO clients (name, ruc, phone, email, address) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $ruc, $phone, $email, $address);

if ($stmt->execute()) {
    $_SESSION['success'] = "Cliente agregado correctamente";
    header('Location: list.php');
} else {
    $_SESSION['error'] = "Error al agregar el cliente: " . $conn->error;
    $_SESSION['form_data'] = $_POST;
    header('Location: add.php');
}
exit;
?>