<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

// Consulta más robusta
$sql = "SELECT p.*, IFNULL(pr.name, 'No asignado') as provider_name 
        FROM products p 
        LEFT JOIN providers pr ON p.provider_id = pr.id
        ORDER BY p.description";

$result = $conn->query($sql);

if ($result === false) {
    // Si hay error, mostrar productos aunque falle la relación con providers
    $sql = "SELECT *, 'Proveedor no disponible' as provider_name FROM products ORDER BY description";
    $result = $conn->query($sql);
    
    if ($result === false) {
        die("Error al cargar productos: " . $conn->error);
    }
}

$products = $result->fetch_all(MYSQLI_ASSOC);

// Paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Búsqueda
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$sql = "SELECT p.*, pr.name as provider_name 
        FROM products p 
        LEFT JOIN providers pr ON p.provider_id = pr.id
        WHERE p.description LIKE '%$search%' OR p.code LIKE '%$search%'
        ORDER BY p.description
        LIMIT $offset, $per_page";

$products = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Total de registros para paginación
$total = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
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
        border-top: 4px solid #4895ef; /* Color azul para productos */
    }
    
    .btn-module {
        border-radius: 50px;
        padding: 8px 20px;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .btn-module:hover {
        transform: translateY(-2px);
    }
    
    .table-module {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .table-module thead th {
        border-bottom: 2px solid #e9ecef;
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .table-module tbody tr:hover {
        background-color: rgba(67, 97, 238, 0.05);
    }
    
    .pagination .page-item.active .page-link {
        background-color: #4361ee;
        border-color: #4361ee;
    }
    
    .pagination .page-link {
        color: #4361ee;
    }
    
    .search-box {
        border-radius: 50px;
        padding-left: 20px;
    }
    
    .search-btn {
        border-top-right-radius: 50px !important;
        border-bottom-right-radius: 50px !important;
    }
    
    .clear-btn {
        border-radius: 50px;
        margin-left: 10px;
    }
    
    .stock-badge {
        font-size: 0.85rem;
        padding: 5px 10px;
        border-radius: 50px;
        min-width: 50px;
        text-align: center;
        display: inline-block;
    }
</style>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-boxes me-2" style="color: #4895ef;"></i> 
            <span style="color: #212529;">Gestión de Productos</span>
        </h2>
        <div>
            <a href="add.php" class="btn btn-primary btn-module">
                <i class="fas fa-plus me-2"></i> Nuevo Producto
            </a>
        </div>
    </div>

    <!-- Barra de búsqueda -->
    <form class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control search-box" placeholder="Buscar por código o descripción..." value="<?= htmlspecialchars($search) ?>">
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
                            <th class="border-0">Código</th>
                            <th class="border-0">Descripción</th>
                            <th class="border-0 text-end">Precio</th>
                            <th class="border-0 text-center">Stock</th>
                            <th class="border-0">Proveedor</th>
                            <th class="border-0 text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle me-2"></i> No se encontraron productos
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="align-middle">
                                    <span class="badge bg-light text-dark"><?= htmlspecialchars($product['code']) ?></span>
                                </td>
                                <td class="align-middle"><?= htmlspecialchars($product['description']) ?></td>
                                <td class="align-middle text-end">
                                    <span class="fw-bold">$<?= number_format($product['price'], 2) ?></span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="stock-badge bg-<?= $product['stock'] > ($product['min_stock'] ?? 10) ? 'success' : ($product['stock'] > 0 ? 'warning' : 'danger') ?>">
                                        <?= $product['measure_unit'] == 'kg' ? number_format($product['stock'], 2) . ' kg' : (int)$product['stock'] ?>
                                        <?php if($product['stock'] <= ($product['min_stock'] ?? 10) && $product['stock'] > 0): ?>
                                            <i class="fas fa-exclamation-circle ms-1"></i>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="align-middle"><?= htmlspecialchars($product['provider_name'] ?? 'N/A') ?></td>
                                <td class="text-end align-middle">
                                    <div class="d-inline-flex">
                                        <a href="edit.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-outline-primary me-2" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este producto?')">
                                            <i class="fas fa-trash-alt"></i>
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