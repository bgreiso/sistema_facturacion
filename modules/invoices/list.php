<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

// Configuración de paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Consulta con búsqueda y paginación
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sql = "SELECT i.*, c.name as client_name 
        FROM invoices i
        LEFT JOIN clients c ON i.client_id = c.id
        WHERE i.invoice_number LIKE '%$search%' OR c.name LIKE '%$search%'
        ORDER BY i.date DESC, i.id DESC
        LIMIT $offset, $per_page";

$invoices = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Total de facturas para paginación
$total = $conn->query("SELECT COUNT(*) as count FROM invoices")->fetch_assoc()['count'];
$total_pages = ceil($total / $per_page);

require_once dirname(__DIR__, 2) . '/includes/header.php';
?>

<style>
    /* Estilos consistentes con el dashboard */
    .card-module {
        border: none;
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        border-top: 4px solid #4cc9f0; /* Color verde-azul para facturas */
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
    
    .pagination .page-item.active .page-link {
        background-color: #4cc9f0;
        border-color: #4cc9f0;
    }
    
    .pagination .page-link {
        color: #4cc9f0;
    }
    
    .search-box {
        border-radius: 50px;
        padding-left: 20px;
        border: 1px solid #dee2e6;
    }
    
    .search-btn {
        border-top-right-radius: 50px !important;
        border-bottom-right-radius: 50px !important;
    }
    
    .clear-btn {
        border-radius: 50px;
        margin-left: 10px;
    }
    
    .invoice-number {
        font-weight: 600;
        color: #4cc9f0;
    }
    
    .total-amount {
        font-weight: 700;
        color: #2b8a3e;
    }
    
    .action-btn {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
</style>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-file-invoice-dollar me-2" style="color: #4cc9f0;"></i> 
            <span style="color: #212529;">Gestión de Facturas</span>
        </h2>
        <div>
            <a href="create.php" class="btn btn-success btn-module">
                <i class="fas fa-plus me-2"></i> Nueva Factura
            </a>
        </div>
    </div>

    <!-- Barra de búsqueda -->
    <form class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control search-box" placeholder="Buscar por número o cliente..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-primary search-btn" type="submit">
                <i class="fas fa-search me-2"></i> Buscar
            </button>
            <?php if (!empty($search)): ?>
                <a href="list.php" class="btn btn-outline-secondary clear-btn">
                    <i class="fas fa-times me-2"></i> Limpiar
                </a>
            <?php endif; ?>
        </div>
    </form>

    <div class="card card-module">
        <div class="card-body p-4">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-module">
                    <thead>
                        <tr>
                            <th class="border-0">Factura</th>
                            <th class="border-0">Fecha</th>
                            <th class="border-0">Cliente</th>
                            <th class="border-0 text-end">Total</th>
                            <th class="border-0 text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($invoices)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle me-2"></i> No se encontraron facturas
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td class="align-middle">
                                    <span class="invoice-number"><?= htmlspecialchars($invoice['invoice_number']) ?></span>
                                </td>
                                <td class="align-middle"><?= date('d/m/Y', strtotime($invoice['date'])) ?></td>
                                <td class="align-middle"><?= htmlspecialchars($invoice['client_name']) ?></td>
                                <td class="align-middle text-end total-amount">$<?= number_format($invoice['total'], 2) ?></td>
                                <td class="text-end align-middle">
                                    <div class="d-inline-flex">
                                        <a href="view.php?id=<?= $invoice['id'] ?>" class="action-btn btn btn-sm btn-info me-2" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="print.php?id=<?= $invoice['id'] ?>" class="action-btn btn btn-sm btn-secondary" title="Imprimir" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación mejorada -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>" aria-label="Anterior">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php 
                    // Mostrar solo 5 páginas alrededor de la actual
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    if ($start > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=1&search=<?= urlencode($search) ?>">1</a>
                    </li>
                    <?php if ($start > 2): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($end < $total_pages): ?>
                    <?php if ($end < $total_pages - 1): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>"><?= $total_pages ?></a>
                    </li>
                    <?php endif; ?>

                    <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>" aria-label="Siguiente">
                            <i class="fas fa-angle-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
require_once dirname(__DIR__, 2) . '/includes/footer.php';
?>