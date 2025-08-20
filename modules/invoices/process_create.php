<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header('Location: ../../dashboard.php');
    exit;
}

// Validar datos básicos
if (empty($_POST['client_id']) || empty($_POST['date']) || empty($_POST['products'])) {
    $_SESSION['error'] = "Todos los campos son obligatorios";
    header('Location: create.php');
    exit;
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // Insertar factura
    $stmt = $conn->prepare("INSERT INTO invoices (invoice_number, client_id, date, subtotal, tax, total, user_id) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisdddi", 
        $_POST['invoice_number'],
        $_POST['client_id'],
        $_POST['date'],
        $_POST['subtotal'],
        $_POST['tax'],
        $_POST['total'],
        $_SESSION['user_id']
    );
    $stmt->execute();
    $invoice_id = $stmt->insert_id;
    $stmt->close();
    
    // Insertar detalles
    $stmt = $conn->prepare("INSERT INTO invoice_details (invoice_id, product_id, quantity, unit_price, total_price) 
                           VALUES (?, ?, ?, ?, ?)");
    
    foreach ($_POST['products'] as $index => $product_id) {
        $quantity = $_POST['quantities'][$index];
        $unit_price = $_POST['unit_prices'][$index];
        $total_price = $quantity * $unit_price;
        
        $stmt->bind_param("iiidd", $invoice_id, $product_id, $quantity, $unit_price, $total_price);
        $stmt->execute();
        
        // Actualizar stock
        $update = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $update->bind_param("ii", $quantity, $product_id);
        $update->execute();
        $update->close();
    }
    
    $stmt->close();
    $conn->commit();
    
    $_SESSION['success'] = "Factura creada correctamente con número: " . $_POST['invoice_number'];
    header('Location: view.php?id=' . $invoice_id);
    
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Error al crear la factura: " . $e->getMessage();
    header('Location: create.php');
}
?>