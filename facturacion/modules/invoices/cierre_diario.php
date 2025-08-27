<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

// Verificar permisos (solo administradores y cajeros)
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'cashier') {
    $_SESSION['error'] = "No tiene permisos para acceder a esta función";
    header('Location: list.php');
    exit;
}

// Obtener la fecha para el reporte (por defecto hoy)
$report_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Consulta para obtener las facturas del día
$sql = "SELECT i.*, c.name as client_name, c.ruc as client_ruc, 
               u.username as cashier_name
        FROM invoices i
        LEFT JOIN clients c ON i.client_id = c.id
        LEFT JOIN users u ON i.user_id = u.id
        WHERE i.date = ?
        ORDER BY i.created_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $report_date);
$stmt->execute();
$invoices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calcular totales
$total_facturas = count($invoices);
$total_ventas = 0;
$total_iva = 0;
$total_subtotal = 0;

foreach ($invoices as $invoice) {
    $total_ventas += $invoice['total'];
    $total_iva += $invoice['tax'];
    $total_subtotal += $invoice['subtotal'];
}

require_once dirname(__DIR__, 2) . '/includes/header.php';
?>

<style>
    .card-module {
        border: none;
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        border-top: 4px solid #4cc9f0;
    }
    
    .btn-module {
        border-radius: 50px;
        padding: 8px 20px;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .btn-module:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .table-module {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .table-module thead th {
        border-bottom: 2px solid #e9ecef;
        background-color: #f8f9fa;
        font-weight: 600;
        color: #495057;
    }
    
    .table-module tbody tr:hover {
        background-color: rgba(76, 201, 240, 0.05);
    }
    
    .summary-card {
        background: linear-gradient(135deg, #4cc9f0, #4895ef);
        color: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .summary-value {
        font-size: 1.5rem;
        font-weight: 700;
    }
    
    @media print {
        .no-print {
            display: none !important;
        }
        .summary-card {
            background: #4cc9f0 !important;
            -webkit-print-color-adjust: exact;
        }
    }
</style>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4"><i class="fas fa-calculator me-2 text-primary"></i>Cierre Diario</h1>
        <a href="list.php" class="btn btn-outline-secondary btn-module no-print">
            <i class="fas fa-arrow-left me-1"></i> Volver al Listado
        </a>
    </div>

    <div class="card card-module mb-4">
        <div class="card-header card-header-module">
            <h5 class="card-title mb-0"><i class="fas fa-calendar-alt me-2 text-primary"></i>Seleccionar Fecha</h5>
        </div>
        <div class="card-body no-print">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="date" class="form-label">Fecha del Reporte</label>
                    <input type="date" class="form-control" id="date" name="date" 
                           value="<?= $report_date ?>" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-module">
                        <i class="fas fa-search me-1"></i> Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if ($report_date): ?>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="summary-card text-center">
                <h6><i class="fas fa-receipt me-1"></i> Total Facturas</h6>
                <div class="summary-value"><?= $total_facturas ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card text-center">
                <h6><i class="fas fa-money-bill-wave me-1"></i> Total Ventas</h6>
                <div class="summary-value">$<?= number_format($total_ventas, 2) ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card text-center">
                <h6><i class="fas fa-calendar-day me-1"></i> Fecha</h6>
                <div class="summary-value"><?= date('d/m/Y', strtotime($report_date)) ?></div>
            </div>
        </div>
    </div>

    <div class="card card-module mb-4">
        <div class="card-header card-header-module d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>
                Facturas del <?= date('d/m/Y', strtotime($report_date)) ?>
            </h5>
            <div class="no-print">
                <button onclick="window.print()" class="btn btn-sm btn-outline-primary me-2">
                    <i class="fas fa-print me-1"></i> Imprimir
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-module">
                    <thead>
                        <tr>
                            <th>N° Factura</th>
                            <th>Cliente</th>
                            <th>RIF/Cédula</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end">IVA</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Hora</th>
                            <th class="text-center">Cajero</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($invoices)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle me-2"></i> No hay facturas para esta fecha
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><?= $invoice['invoice_number'] ?></td>
                                <td><?= htmlspecialchars($invoice['client_name']) ?></td>
                                <td><?= $invoice['client_ruc'] ?></td>
                                <td class="text-end">$<?= number_format($invoice['subtotal'], 2) ?></td>
                                <td class="text-end">$<?= number_format($invoice['tax'], 2) ?></td>
                                <td class="text-end">$<?= number_format($invoice['total'], 2) ?></td>
                                <td class="text-center"><?= date('H:i', strtotime($invoice['created_at'])) ?></td>
                                <td class="text-center"><?= $invoice['cashier_name'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-active">
                            <th colspan="3" class="text-end">TOTALES:</th>
                            <th class="text-end">$<?= number_format($total_subtotal, 2) ?></th>
                            <th class="text-end">$<?= number_format($total_iva, 2) ?></th>
                            <th class="text-end">$<?= number_format($total_ventas, 2) ?></th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="row no-print">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Resumen del día:</strong> Se emitieron <?= $total_facturas ?> facturas con un total de ventas de $<?= number_format($total_ventas, 2) ?>.
                El IVA recaudado fue de $<?= number_format($total_iva, 2) ?>.
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php 
require_once dirname(__DIR__, 2) . '/includes/footer.php';
?>