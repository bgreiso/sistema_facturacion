<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

// Lógica para obtener los datos del cliente
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID de cliente no especificado";
    header('Location: list.php');
    exit;
}

$client_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Cliente no encontrado.";
    header('Location: list.php');
    exit;
}

$client = $result->fetch_assoc();
$stmt->close();

require_once dirname(__DIR__, 2) . '/includes/header.php';
?>

<style>
    /* Incluye los estilos de add.php */
    .card-module-form {
        border: none;
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        border-top: 4px solid #f72585; /* Color rosa para clientes */
    }
    
    .card-header-module {
        background: linear-gradient(135deg, #f72585, #b5179e);
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
        border-color: #f72585;
        box-shadow: 0 0 0 0.25rem rgba(247, 37, 133, 0.25);
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
    
    .hint-text {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }
</style>

<div class="container py-4">
    <div class="card card-module-form">
        <div class="card-header card-header-module">
            <h4 class="mb-0"><i class="fas fa-user-edit me-2"></i> Editar Cliente</h4>
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
                <input type="hidden" name="id" value="<?= htmlspecialchars($client['id']) ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label required-field">Nombre Completo</label>
                        <input type="text" class="form-control" id="name" name="name" required
                               value="<?= htmlspecialchars($client['name']) ?>">
                        <div class="invalid-feedback">
                            Por favor ingrese el nombre del cliente.
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="ruc" class="form-label required-field">Cédula/RIF</label>
                        <input type="text" class="form-control" id="ruc" name="ruc" required
                               value="<?= htmlspecialchars($client['ruc']) ?>">
                        <div class="invalid-feedback">
                            Por favor ingrese un RIF o cédula válido.
                        </div>
                        <small class="hint-text">Formato: V-12345678 o J-123456789</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                               value="<?= htmlspecialchars($client['phone']) ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= htmlspecialchars($client['email']) ?>">
                        <div class="invalid-feedback">
                            Por favor ingrese un email válido.
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <label for="address" class="form-label">Dirección</label>
                        <textarea class="form-control" id="address" name="address" rows="2"><?= htmlspecialchars($client['address']) ?></textarea>
                    </div>
                    
                    <div class="col-12 mt-4">
                        <div class="d-flex justify-content-end gap-3">
                            <a href="list.php" class="btn btn-outline-secondary btn-form">
                                <i class="fas fa-times me-2"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning btn-form">
                                <i class="fas fa-save me-2"></i> Actualizar Cliente
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