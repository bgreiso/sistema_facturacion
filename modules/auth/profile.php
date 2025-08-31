<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

// Lógica para obtener los datos del usuario actual
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, role FROM users WHERE id = ?");

// Manejo del error de la consulta
if ($stmt === false) {
    $_SESSION['error'] = "Error al preparar la consulta: " . $conn->error;
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['error'] = "No se encontraron datos de usuario.";
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

require_once dirname(__DIR__, 2) . '/includes/header.php';
?>

<style>
    /* Estilos del módulo de formulario */
    .card-module-form {
        border: none;
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        border-top: 4px solid var(--primary-color);
    }
    
    .card-header-module {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 1.25rem 1.5rem;
        border-bottom: none;
    }
    
    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .form-control, .form-select {
        border-radius: 8px;
        padding: 0.5rem 1rem;
        border: 1px solid #dee2e6;
        transition: all 0.3s;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
    }
    
    .btn-form {
        border-radius: 50px;
        padding: 0.5rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .btn-form:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .required-field::after {
        content: ' *';
        color: #dc3545;
    }
</style>

<div class="container py-4">
    <div class="card card-module-form">
        <div class="card-header card-header-module">
            <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i> Mi Perfil</h4>
        </div>
        <div class="card-body p-4">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form action="process_profile.php" method="post" class="needs-validation" novalidate>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="username" class="form-label required-field">Nombre de Usuario</label>
                        <input type="text" class="form-control" id="username" name="username" required
                               value="<?= htmlspecialchars($user['username']) ?>">
                        <div class="invalid-feedback">
                            Por favor ingrese un nombre de usuario.
                        </div>
                    </div>
                    
                    <div class="col-12 mt-4">
                        <h5 class="mb-3"><i class="fas fa-lock me-2"></i> Cambiar Contraseña (Opcional)</h5>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="current_password" class="form-label">Contraseña Actual</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>

                    <div class="col-md-6">
                        <label for="new_password" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                        <div class="invalid-feedback">
                            La nueva contraseña debe tener al menos 8 caracteres.
                        </div>
                    </div>
                    
                    <div class="col-12 mt-4">
                        <div class="d-flex justify-content-end gap-3">
                            <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-secondary btn-form">
                                <i class="fas fa-times me-2"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-form">
                                <i class="fas fa-save me-2"></i> Actualizar Perfil
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Validación de formulario con Bootstrap
(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>

<?php 
require_once dirname(__DIR__, 2) . '/includes/footer.php';
?>