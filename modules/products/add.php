<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

// Obtener proveedores para el select
$providers = $conn->query("SELECT id, name FROM providers ORDER BY name")->fetch_all(MYSQLI_ASSOC);

require_once dirname(__DIR__, 2) . '/includes/header.php';
?>

<style>
    .card-module-form {
        border: none;
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        border-top: 4px solid #4895ef; /* Color azul para productos */
    }
    
    .card-header-module {
        background: linear-gradient(135deg, #4895ef, #4361ee);
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
        border-color: #4895ef;
        box-shadow: 0 0 0 0.25rem rgba(72, 149, 239, 0.25);
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
    
    .input-group-text {
        background-color: #f8f9fa;
        border-radius: 8px 0 0 8px;
    }
    
    .input-group .form-control {
        border-radius: 0 8px 8px 0;
    }
</style>

<div class="container py-4">
    <div class="card card-module-form">
        <div class="card-header card-header-module">
            <h4 class="mb-0"><i class="fas fa-boxes me-2"></i> Agregar Nuevo Producto</h4>
        </div>
        <div class="card-body p-4">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form action="process_add.php" method="post" class="needs-validation" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="code" class="form-label required-field">Código del Producto</label>
                        <input type="text" class="form-control" id="code" name="code" required placeholder="Ej: PROD-001">
                        <div class="invalid-feedback">
                            Por favor ingrese el código del producto.
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="provider_id" class="form-label required-field">Proveedor</label>
                        <select class="form-select" id="provider_id" name="provider_id" required>
                            <option value="" selected disabled>Seleccionar proveedor...</option>
                            <?php foreach ($providers as $provider): ?>
                            <option value="<?= $provider['id'] ?>"><?= htmlspecialchars($provider['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            Por favor seleccione un proveedor.
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <label for="description" class="form-label required-field">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="2" required placeholder="Descripción detallada del producto"></textarea>
                        <div class="invalid-feedback">
                            Por favor ingrese una descripción del producto.
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="price" class="form-label required-field">Precio Unitario</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" required placeholder="0.00">
                        </div>
                        <div class="invalid-feedback">
                            Por favor ingrese un precio válido.
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="stock" class="form-label">Stock Inicial</label>
                        <input type="number" min="0" class="form-control" id="stock" name="stock" value="0" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="min_stock" class="form-label">Stock Mínimo</label>
                        <input type="number" min="0" class="form-control" id="min_stock" name="min_stock" value="5">
                    </div>
                    
                    <div class="col-12 mt-4">
                        <div class="d-flex justify-content-end gap-3">
                            <a href="list.php" class="btn btn-outline-secondary btn-form">
                                <i class="fas fa-times me-2"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-form">
                                <i class="fas fa-save me-2"></i> Guardar Producto
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
  
  // Obtener todos los formularios a los que queremos aplicar estilos de validación de Bootstrap personalizados
  var forms = document.querySelectorAll('.needs-validation')
  
  // Bucle sobre ellos y evitar el envío
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