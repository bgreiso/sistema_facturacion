<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';

// Validación de sesión
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true || 
    !isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . '/modules/auth/login.php');
    exit();
}

// Verificar autenticación antes de cualquier salida
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/modules/auth/login.php');
    exit(); // Asegúrate de salir después de redireccionar
}

// Inicializar arrays para evitar errores
$stats = [
    'total_products' => 0,
    'total_invoices' => 0,
    'total_clients' => 0,
    'total_providers' => 0,
    'low_stock_products' => 0
];
$recent_invoices = [];
$recent_products = [];
$recent_clients = [];
$error_db = null;

// Verificar conexión
if (!isset($conn) || $conn->connect_error) {
    $error_db = "Error de conexión a la base de datos";
} else {
    // Función segura para ejecutar consultas
    function executeQuery($conn, $sql) {
        $result = $conn->query($sql);
        if ($result === false) {
            error_log("Error en consulta SQL: " . $conn->error);
            return false;
        }
        return $result;
    }

    // Obtener estadísticas
    $queries = [
        'total_products' => "SELECT COUNT(*) as total FROM products",
        'total_invoices' => "SELECT COUNT(*) as total FROM invoices",
        'total_clients' => "SELECT COUNT(*) as total FROM clients",
        'total_providers' => "SELECT COUNT(*) as total FROM providers",
        'low_stock_products' => "SELECT COUNT(*) as total FROM products WHERE stock < stock_min"
    ];

    foreach ($queries as $key => $sql) {
        $result = executeQuery($conn, $sql);
        if ($result !== false) {
            $stats[$key] = $result->fetch_assoc()['total'];
            $result->free();
        }
    }

    // Obtener actividad reciente
    $recent_queries = [
        'invoices' => "SELECT invoice_number, created_at FROM invoices ORDER BY created_at DESC LIMIT 3",
        'products' => "SELECT name, updated_at FROM products ORDER BY updated_at DESC LIMIT 2",
        'clients' => "SELECT name, created_at FROM clients ORDER BY created_at DESC LIMIT 1"
    ];

    foreach ($recent_queries as $key => $sql) {
        $result = executeQuery($conn, $sql);
        if ($result !== false) {
            ${'recent_'.$key} = $result->fetch_all(MYSQLI_ASSOC);
            $result->free();
        }
    }
}
?>

<style>
    .dashboard-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2rem;
        border-radius: 10px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(67, 97, 238, 0.15);
    }
    
    .card-module {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
        height: 100%;
    }
    
    .card-module:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
    }
    
    .card-module .card-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        color: var(--primary-color);
    }
    
    .btn-module {
        border-radius: 50px;
        padding: 8px 20px;
        font-weight: 500;
        transition: all 0.3s;
        border: none;
    }
    
    .module-products {
        border-top: 4px solid var(--accent-color);
    }
    
    .module-invoices {
        border-top: 4px solid var(--success-color);
    }
    
    .module-clients {
        border-top: 4px solid var(--danger-color);
    }
    
    .module-providers {
        border-top: 4px solid var(--warning-color);
    }
    
    .user-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: var(--primary-color);
        margin-right: 15px;
    }
    
    .welcome-section {
        display: flex;
        align-items: center;
    }
    
    .stats-card {
        background: white;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }
    
    .stats-value {
        font-size: 1.8rem;
        font-weight: 700;
    }
    
    .stats-label {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .recent-activity-item {
        border-left: 3px solid var(--primary-color);
        padding-left: 15px;
        margin-bottom: 15px;
    }
    
    .recent-activity-time {
        font-size: 0.8rem;
        color: #6c757d;
    }
</style>

<!-- Contenido del dashboard -->
<div class="dashboard-header animate__animated animate__fadeIn">
    <div class="welcome-section">
        <div class="user-avatar">
            <?= strtoupper(substr(htmlspecialchars($_SESSION['username']), 0, 1)); ?>
        </div>
        <div>
            <h2 class="mb-1">Bienvenido, <?= htmlspecialchars($_SESSION['username']) ?></h2>
            <p class="mb-0"><span class="badge bg-light text-dark"><?= htmlspecialchars($_SESSION['role']) ?></span></p>
        </div>
    </div>
</div>

<!-- Estadísticas rápidas con datos reales -->
<div class="row mb-4 animate__animated animate__fadeIn animate__delay-1s">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-value text-primary"><?= $stats['total_products'] ?></div>
            <div class="stats-label">Productos</div>
            <?php if($stats['low_stock_products'] > 0): ?>
                <small class="text-danger"><i class="fas fa-exclamation-circle"></i> <?= $stats['low_stock_products'] ?> con stock bajo</small>
            <?php else: ?>
                <small class="text-success"><i class="fas fa-check-circle"></i> Stock OK</small>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-value text-success"><?= $stats['total_invoices'] ?></div>
            <div class="stats-label">Facturas</div>
            <small class="text-muted">Total registradas</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-value text-danger"><?= $stats['total_clients'] ?></div>
            <div class="stats-label">Clientes</div>
            <small class="text-muted">Clientes activos</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-value text-warning"><?= $stats['total_providers'] ?></div>
            <div class="stats-label">Proveedores</div>
            <small class="text-muted">Proveedores registrados</small>
        </div>
    </div>
</div>

<!-- Módulos principales -->
<div class="row animate__animated animate__fadeIn animate__delay-2s">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card card-module module-products">
            <div class="card-body text-center py-4">
                <div class="card-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <h5 class="card-title">Productos</h5>
                <p class="text-muted small mb-3">Gestión de inventario y productos</p>
                <a href="<?= BASE_URL ?>/modules/products/list.php" class="btn btn-primary btn-module">
                    Administrar <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card card-module module-invoices">
            <div class="card-body text-center py-4">
                <div class="card-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <h5 class="card-title">Facturas</h5>
                <p class="text-muted small mb-3">Registro y gestión de facturas</p>
                <a href="<?= BASE_URL ?>/modules/invoices/list.php" class="btn btn-success btn-module">
                    Administrar <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card card-module module-clients">
            <div class="card-body text-center py-4">
                <div class="card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h5 class="card-title">Clientes</h5>
                <p class="text-muted small mb-3">Gestión de clientes</p>
                <a href="<?= BASE_URL ?>/modules/clients/list.php" class="btn btn-danger btn-module">
                    Administrar <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card card-module module-providers">
            <div class="card-body text-center py-4">
                <div class="card-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h5 class="card-title">Proveedores</h5>
                <p class="text-muted small mb-3">Gestión de proveedores</p>
                <a href="<?= BASE_URL ?>/modules/providers/list.php" class="btn btn-warning btn-module">
                    Administrar <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Sección de actividad reciente con datos reales -->
<div class="row mt-4 animate__animated animate__fadeIn animate__delay-3s">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-chart-line text-primary me-2"></i> Actividad Reciente</h5>
                <div class="mt-3">
                    <?php foreach($recent_invoices as $invoice): ?>
                        <div class="recent-activity-item">
                            <p class="mb-1">Nueva factura creada <strong>#<?= htmlspecialchars($invoice['invoice_number']) ?></strong></p>
                            <p class="recent-activity-time">
                                <?= date('d M Y, H:i', strtotime($invoice['created_at'])) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php foreach($recent_products as $product): ?>
                        <div class="recent-activity-item">
                            <p class="mb-1">Producto <strong>"<?= htmlspecialchars($product['name']) ?>"</strong> actualizado</p>
                            <p class="recent-activity-time">
                                <?= date('d M Y, H:i', strtotime($product['updated_at'])) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php foreach($recent_clients as $client): ?>
                        <div class="recent-activity-item">
                            <p class="mb-1">Nuevo cliente registrado: <strong><?= htmlspecialchars($client['name']) ?></strong></p>
                            <p class="recent-activity-time">
                                <?= date('d M Y, H:i', strtotime($client['created_at'])) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-bell text-warning me-2"></i> Notificaciones</h5>
                <div class="mt-3">
                    <?php if($stats['low_stock_products'] > 0): ?>
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> 
                            <?= $stats['low_stock_products'] ?> producto(s) con stock bajo
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i> 
                            Todos los productos tienen stock suficiente
                        </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i> 
                        Sistema funcionando correctamente
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animación al pasar el mouse por los módulos
        const cards = document.querySelectorAll('.card-module');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.classList.add('animate__pulse');
            });
            card.addEventListener('mouseleave', function() {
                this.classList.remove('animate__pulse');
            });
        });
    });
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>