<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

// Consulta con paginación y búsqueda
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Consulta principal
$sql = "SELECT * FROM providers 
        WHERE name LIKE '%$search%' OR ruc LIKE '%$search%' OR email LIKE '%$search%'
        ORDER BY name 
        LIMIT $offset, $per_page";

$providers = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Total de registros para paginación
$total = $conn->query("SELECT COUNT(*) as count FROM providers")->fetch_assoc()['count'];
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
</style>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-truck me-2" style="color: #f8961e;"></i> 
            <span style="color: #212529;">Proveedores</span>
        </h2>
        <div>
            <a href="add.php" class="btn btn-warning btn-module">
                <i class="fas fa-plus me-2"></i> Nuevo Proveedor
            </a>
        </div>
    </div>

    <!-- Barra de búsqueda -->
    <form class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control search-box" placeholder="Buscar por nombre, RIF o email..." value="<?= htmlspecialchars($search) ?>">
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
                            <th class="border-0">Nombre</th>
                            <th class="border-0">RIF</th>
                            <th class="border-0">Teléfono</th>
                            <th class="border-0">Email</th>
                            <th class="border-0 text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($providers)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle me-2"></i> No se encontraron proveedores
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($providers as $provider): ?>
                            <tr>
                                <td class="align-middle"><?= htmlspecialchars($provider['name']) ?></td>
                                <td class="align-middle"><?= htmlspecialchars($provider['ruc']) ?></td>
                                <td class="align-middle"><?= htmlspecialchars($provider['phone']) ?></td>
                                <td class="align-middle"><?= htmlspecialchars($provider['email']) ?></td>
                                <td class="text-end align-middle">
                                    <div class="d-inline-flex">
                                        <a href="edit.php?id=<?= $provider['id'] ?>" class="btn btn-sm btn-outline-primary me-2" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $provider['id'] ?>" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este proveedor?')">
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

            <!-- Paginación -->
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