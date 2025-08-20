<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}

$invoiceId = (int)$_GET['id'];

// Obtener información de la factura (misma consulta que en view.php)
$stmt = $conn->prepare("SELECT i.*, c.name as client_name, c.ruc as client_ruc, c.phone as client_phone, 
                        c.email as client_email, c.address as client_address,
                        u.username as user_created 
                        FROM invoices i
                        LEFT JOIN clients c ON i.client_id = c.id
                        LEFT JOIN users u ON i.user_id = u.id
                        WHERE i.id = ?");
$stmt->bind_param("i", $invoiceId);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

if (!$invoice) {
    die("Factura no encontrada");
}

// Obtener detalles de la factura
$details = $conn->prepare("SELECT d.*, p.code as product_code, p.description as product_description 
                          FROM invoice_details d
                          LEFT JOIN products p ON d.product_id = p.id
                          WHERE d.invoice_id = ?");
$details->bind_param("i", $invoiceId);
$details->execute();
$invoiceDetails = $details->get_result()->fetch_all(MYSQLI_ASSOC);

// Configurar cabecera para PDF
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura <?= $invoice['invoice_number'] ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .invoice-header { margin-bottom: 20px; }
        .company-info { float: left; width: 50%; }
        .invoice-info { float: right; width: 45%; text-align: right; }
        .clear { clear: both; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .totals { float: right; width: 300px; margin-top: 20px; }
        .footer { margin-top: 50px; font-size: 10px; text-align: center; }
        @media print {
            .no-print { display: none; }
            body { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <div class="company-info">
            <h2>PESCADERÍA CON LA NOCHE Y EL SABOR DEL MAR, C.A</h2>
            <p>RIF: J-40565600-0</p>
            <p>Dirección: Av. Principal Local Nro 3 Sector La Boca de Ocumare de la Costa
Ocumare de la Costa, Zona postal 2112, Aragua
</p>
            <p>Teléfono: 04145904471</p>
            <p>Email: pconlanoche@gmail.com</p>
        </div>
        <div class="invoice-info">
            <h2>FACTURA</h2>
            <p><strong>N°:</strong> <?= $invoice['invoice_number'] ?></p>
            <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($invoice['date'])) ?></p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="client-info">
        <h3>Datos del Cliente</h3>
        <p><strong>Nombre:</strong> <?= $invoice['client_name'] ?></p>
        <p><strong>RIF/Cédula:</strong> <?= $invoice['client_ruc'] ?></p>
        <p><strong>Dirección:</strong> <?= $invoice['client_address'] ?></p>
        <p><strong>Teléfono:</strong> <?= $invoice['client_phone'] ?></p>
        <p><strong>Email:</strong> <?= $invoice['client_email'] ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th class="text-right">Precio Unitario</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($invoiceDetails as $detail): ?>
            <tr>
                <td><?= $detail['product_code'] ?></td>
                <td><?= $detail['product_description'] ?></td>
                <td class="text-right">$<?= number_format($detail['unit_price'], 2) ?></td>
                <td class="text-right"><?= $detail['quantity'] ?></td>
                <td class="text-right">$<?= number_format($detail['total_price'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <th>Subtotal:</th>
                <td class="text-right">$<?= number_format($invoice['subtotal'], 2) ?></td>
            </tr>
            <tr>
                <th>IVA (16%):</th>
                <td class="text-right">$<?= number_format($invoice['tax'], 2) ?></td>
            </tr>
            <tr>
                <th>TOTAL:</th>
                <td class="text-right">$<?= number_format($invoice['total'], 2) ?></td>
            </tr>
        </table>
    </div>
    <div class="clear"></div>

    <div class="footer">
        <p>Gracias por su compra</p>
        <p>Factura generada por: <?= $invoice['user_created'] ?> el <?= date('d/m/Y H:i:s') ?></p>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">Imprimir Factura</button>
        <button onclick="window.close()" class="btn btn-secondary">Cerrar</button>
    </div>
</body>
</html>