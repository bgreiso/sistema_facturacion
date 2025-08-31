<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

require_once dirname(__DIR__, 2) . '/includes/header.php';
?>

<style>
    .settings-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
        border-top: 4px solid #007bff;
    }
    .settings-header {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 1.5rem;
        border-bottom: none;
    }
    .settings-list .list-group-item {
        border-left: none;
        border-right: none;
    }
    .settings-list .list-group-item:last-child {
        border-bottom: none;
    }
</style>

<div class="container py-4">
    <div class="card settings-card">
        <div class="card-header settings-header">
            <h4 class="mb-0"><i class="fas fa-cog me-2"></i> Configuración de Cuenta</h4>
        </div>
        <div class="card-body p-4">
            <h5 class="mb-3">Detalles de la cuenta</h5>
            <ul class="list-group list-group-flush settings-list">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <strong>Nombre de Usuario:</strong>
                    <span><?= htmlspecialchars($_SESSION['username']) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <strong>Rol:</strong>
                    <span><?= htmlspecialchars($_SESSION['role']) ?></span>
                </li>
            </ul>
            
            <div class="mt-4">
                <a href="profile.php" class="btn btn-primary btn-form">
                    <i class="fas fa-user-edit me-2"></i> Editar Perfil
                </a>
            </div>

            <hr class="my-4">
            
            <h5 class="mb-3">Acciones de Seguridad</h5>
            <a href="<?= BASE_URL ?>/modules/auth/logout.php" class="btn btn-outline-danger btn-form">
                <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
            </a>
        </div>
    </div>
</div>

<?php 
require_once dirname(__DIR__, 2) . '/includes/footer.php';
?>