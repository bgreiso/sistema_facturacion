<?php
// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Debe iniciar sesión para acceder a esta página";
    header('Location: ../modules/auth/login.php');
    exit;
}

// Verificar roles si es necesario
function checkRole($requiredRole) {
    if ($_SESSION['role'] != $requiredRole) {
        $_SESSION['error'] = "No tiene permiso para acceder a esta sección";
        header('Location: ../../dashboard.php');
        exit;
    }
}
?>