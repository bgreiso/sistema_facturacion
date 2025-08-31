<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = trim($_POST['username'] ?? '');
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

$errors = [];

if (empty($username)) {
    $errors[] = "El nombre de usuario es requerido.";
}

// Lógica de cambio de contraseña
if (!empty($new_password)) {
    if (empty($current_password)) {
        $errors[] = "Debe ingresar la contraseña actual para cambiarla.";
    } elseif (strlen($new_password) < 8) {
        $errors[] = "La nueva contraseña debe tener al menos 8 caracteres.";
    } else {
        // Verificar la contraseña actual
        $stmt_check_password = $conn->prepare("SELECT password FROM users WHERE id = ?");
        if ($stmt_check_password === false) {
             $_SESSION['error'] = "Error al preparar la verificación de contraseña: " . $conn->error;
             header('Location: profile.php');
             exit;
        }
        $stmt_check_password->bind_param("i", $user_id);
        $stmt_check_password->execute();
        $result = $stmt_check_password->get_result();
        $user_data = $result->fetch_assoc();

        if (!password_verify($current_password, $user_data['password'])) {
            $errors[] = "La contraseña actual es incorrecta.";
        }
    }
}

// Verificar si el nuevo nombre de usuario ya existe en otro registro
$stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
if ($stmt_check === false) {
     $_SESSION['error'] = "Error al preparar la verificación de usuario: " . $conn->error;
     header('Location: profile.php');
     exit;
}
$stmt_check->bind_param("si", $username, $user_id);
$stmt_check->execute();
$stmt_check->store_result();
if ($stmt_check->num_rows > 0) {
    $errors[] = "El nombre de usuario ya está en uso por otra cuenta.";
}

if (!empty($errors)) {
    $_SESSION['error'] = implode("<br>", $errors);
    header('Location: profile.php');
    exit;
}

// Actualizar el perfil
if (!empty($new_password)) {
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $hashed_password, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
    $stmt->bind_param("si", $username, $user_id);
}

if ($stmt->execute()) {
    $_SESSION['success'] = "Perfil actualizado correctamente.";
    $_SESSION['username'] = $username; // Actualizar el nombre de usuario en la sesión
    header('Location: profile.php');
} else {
    $_SESSION['error'] = "Error al actualizar el perfil: " . $conn->error;
    header('Location: profile.php');
}
exit;
?>