<?php
require_once __DIR__ . '/../../includes/config.php';

// Limpiar sesión existente
$_SESSION = array();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $input_password = $_POST['password'];
    $hashed_input = sha1($input_password);
    
    // DEBUG TEMPORAL - Quitar después
    error_log("Intento de login: Usuario: $username, Contraseña ingresada: $input_password, Hash generado: $hashed_input");
    
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // DEBUG: Mostrar hash almacenado
        error_log("Hash almacenado para $username: " . $user['password']);
        
        if (sha1($input_password) === $user['password']) {
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['authenticated'] = true;
            
            header('Location: ' . BASE_URL . '/dashboard.php');
            exit();
        } else {
            error_log("Error: Hash no coincide");
            $_SESSION['error'] = "Contraseña incorrecta";
        }
    } else {
        error_log("Error: Usuario no encontrado");
        $_SESSION['error'] = "Usuario no encontrado";
    }
    
    header('Location: ' . BASE_URL . '/modules/auth/login.php');
    exit();
}

header('Location: ' . BASE_URL . '/modules/auth/login.php');
exit();
?>