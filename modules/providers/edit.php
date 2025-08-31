<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

// Validar que se reciba un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID de proveedor no válido.";
    header('Location: list.php');
    exit;
}

$id = (int)$_GET['id'];

// Obtener los datos del proveedor de la base de datos
$stmt = $conn->prepare("SELECT * FROM providers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$provider = $result->fetch_assoc();

if (!$provider) {
    $_SESSION['error'] = "Proveedor no encontrado.";
    header('Location: list.php');
    exit;
}

require_once dirname(__DIR__, 2) . '/includes/header.php';
?>

<style>
    /* Estilos copiados de add.php para consistencia visual */
    .card-module-form {
        border: none;
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        border-top: 4px solid #f8961e;
    }
    .card-header-module {
        background: linear-gradient(135deg, #f8961e, #f3722c);
        color: white;
        padding: 1.25rem 1.5rem;
        border-bottom: none;
    }
    .form-label {
        font-weight: 500;
        color: #495057;
    }
    .form-control {
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }
    .form-control:focus {
        border-color: #f8961e;
        box-shadow: 0 0 0 0.25rem rgba(248, 150, 30, 0.25);
    }
    .btn-form {
        border-radius: 50px;
        padding: 0.5rem 1.5rem;
        font-weight: 500;
    }
    .required-field::after {
        content: ' *';
        color: #dc3545;
    }
</style>

<div class="container py-4">
    <div class="card card-module-form">
        <div class="card-header card-header-module">
            <h4 class="mb-0"><i class="fas fa-edit me-2"></i> Editar Proveedor</h4>
        </div>
        <div class="card-body p-4">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form action="process_edit.php" method="post" class="needs-validation" novalidate>
                <input type="hidden" name="id" value="<?= htmlspecialchars($provider['id']) ?>">
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label required-field">Nombre del Proveedor</label>
                        <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($provider['name']) ?>">
                        <div class="invalid-feedback">
                            Por favor ingrese el nombre del proveedor.
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="ruc" class="form-label required-field">RIF</label>
                        <input type="text" class="form-control" id="ruc" name="ruc" required value="<?= htmlspecialchars($provider['ruc']) ?>">
                        <div class="invalid-feedback">
                            Por favor ingrese el RIF del proveedor.
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Ej: 0412-1234567" value="<?= htmlspecialchars($provider['phone']) ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="proveedor@ejemplo.com" value="<?= htmlspecialchars($provider['email']) ?>">
                        <div class="invalid-feedback">
                            Por favor ingrese un email válido.
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <label for="address" class="form-label">Dirección</label>
                        <textarea class="form-control" id="address" name="address" rows="2" placeholder="Av. Principal, Edificio X, Piso Y"><?= htmlspecialchars($provider['address']) ?></textarea>
                    </div>
                    
                    <div class="col-12 mt-4">
                        <div class="d-flex justify-content-end gap-3">
                            <a href="list.php" class="btn btn-outline-secondary btn-form">
                                <i class="fas fa-times me-2"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning btn-form">
                                <i class="fas fa-save me-2"></i> Actualizar Proveedor
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