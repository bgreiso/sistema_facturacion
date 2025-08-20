<?php
// includes/db.php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=tu_base_de_datos;charset=utf8', 'usuario', 'contraseña');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}