<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}

$invoiceId = (int)$_GET['id'];

// Obtener información de la factura
$stmt = $conn->prepare("SELECT i.*, c.name as client_name, c.ruc as client_ruc, 
                        u.username as user_created 
                        FROM invoices i
                        LEFT JOIN clients c ON i.client_id = c.id
                        LEFT JOIN users u ON i.user_id = u.id
                        WHERE i.id = ?");
$stmt->bind_param("i", $invoiceId);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

if (!$invoice) {
    $_SESSION['error'] = "Factura no encontrada";
    header('Location: list.php');
    exit;
}

// Obtener detalles de la factura
$details = $conn->prepare("SELECT d.*, p.code as product_code, p.description as product_description 
                          FROM invoice_details d
                          LEFT JOIN products p ON d.product_id = p.id
                          WHERE d.invoice_id = ?");
$details->bind_param("i", $invoiceId);
$details->execute();
$invoiceDetails = $details->get_result()->fetch_all(MYSQLI_ASSOC);

require_once dirname(__DIR__, 2) . '/includes/header.php';
?>

<style>
    /* Estilos consistentes con el sistema */
    .invoice-header {
        background: linear-gradient(135deg, #4cc9f0, #4895ef);
        color: white;
        padding: 1.5rem;
        border-radius: 10px 10px 0 0;
        margin-bottom: 2rem;
    }
    
    .invoice-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }
    
    .invoice-table {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .invoice-table thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #e9ecef;
    }
    
    .invoice-table tbody tr:hover {
        background-color: rgba(76, 201, 240, 0.05);
    }
    
    .invoice-table tfoot th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .total-row {
        font-size: 1.1rem;
        background-color: rgba(76, 201, 240, 0.1) !important;
    }
    
    .btn-invoice {
        border-radius: 50px;
        padding: 8px 20px;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .btn-invoice:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .client-info, .invoice-info {
        background-color: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .section-title {
        color: #4895ef;
        border-bottom: 2px solid #4895ef;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }
</style>

<div class="container py-4">
    <div class="invoice-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <i class="fas fa-file-invoice-dollar me-2"></i> 
                Factura #<?= htmlspecialchars($invoice['invoice_number']) ?>
            </h2>
            <div>
                <a href="print.php?id=<?= $invoice['id'] ?>" class="btn btn-light btn-invoice" target="_blank">
                    <i class="fas fa-print me-2"></i> Imprimir
                </a>
                <a href="list.php" class="btn btn-light btn-invoice">
                    <i class="fas fa-list me-2"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="invoice-card">
        <div class="card-body p-4">
            <div class="row mb-4 g-4">
                <div class="col-md-6">
                    <div class="client-info">
                        <h5 class="section-title">Datos del Cliente</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nombre:</strong></p>
                                <p><strong>RIF:</strong></p>
                            </div>
                            <div class="col-md-6">
                                <p><?= htmlspecialchars($invoice['client_name']) ?></p>
                                <p><?= htmlspecialchars($invoice['client_ruc']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="invoice-info">
                        <h5 class="section-title">Datos de Factura</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Número:</strong></p>
                                <p><strong>Fecha:</strong></p>
                                <p><strong>Registrada por:</strong></p>
                            </div>
                            <div class="col-md-6">
                                <p><?= htmlspecialchars($invoice['invoice_number']) ?></p>
                                <p><?= date('d/m/Y', strtotime($invoice['date'])) ?></p>
                                <p><?= htmlspecialchars($invoice['user_created']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table invoice-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th class="text-end">Precio Unitario</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoiceDetails as $detail): ?>
                        <tr>
                            <td><?= htmlspecialchars($detail['product_code']) ?></td>
                            <td><?= htmlspecialchars($detail['product_description']) ?></td>
                            <td class="text-end">$<?= number_format($detail['unit_price'], 2) ?></td>
                            <td class="text-end"><?= $detail['quantity'] ?></td>
                            <td class="text-end">$<?= number_format($detail['total_price'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Subtotal:</th>
                            <th class="text-end">$<?= number_format($invoice['subtotal'], 2) ?></th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">IVA (16%):</th>
                            <th class="text-end">$<?= number_format($invoice['tax'], 2) ?></th>
                        </tr>
                        <tr class="total-row">
                            <th colspan="4" class="text-end">Total General:</th>
                            <th class="text-end">$<?= number_format($invoice['total'], 2) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
require_once dirname(__DIR__, 2) . '/includes/footer.php';
?>