<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

// Verificación del ID
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($productId <= 0) {
    $_SESSION['error'] = "ID de producto inválido";
    header('Location: list.php');
    exit;
}

// Obtener el producto
$sql_select = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql_select);

if (!$stmt) {
    error_log("Error en SELECT: " . $conn->error);
    die("Error técnico. Por favor intente más tarde.");
}

$stmt->bind_param("i", $productId);
if (!$stmt->execute()) {
    error_log("Error ejecutando SELECT: " . $stmt->error);
    die("Error al buscar el producto.");
}

$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    $_SESSION['error'] = "Producto no encontrado";
    header('Location: list.php');
    exit;
}

// Obtener proveedores
$providers = [];
$sql_providers = "SELECT id, name FROM providers ORDER BY name";
$result = $conn->query($sql_providers);
if ($result) {
    $providers = $result->fetch_all(MYSQLI_ASSOC);
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar entradas
    $code = trim($_POST['code'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $cost_price = (float)($_POST['cost_price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $stock_min = (int)($_POST['stock_min'] ?? 5);
    $provider_id = !empty($_POST['provider_id']) ? (int)$_POST['provider_id'] : null;

    // Validaciones
    $errors = [];
    if (empty($code)) $errors[] = "El código es requerido";
    if (empty($description)) $errors[] = "La descripción es requerida";
    if ($price <= 0) $errors[] = "El precio debe ser mayor a 0";
    if ($cost_price < 0) $errors[] = "El precio de costo no puede ser negativo";

    if (empty($errors)) {
        // Consulta UPDATE actualizada con campos de auditoría
        $sql_update = "UPDATE products SET 
                        code = ?, 
                        description = ?, 
                        price = ?, 
                        cost_price = ?,
                        stock = ?, 
                        stock_min = ?, 
                        provider_id = ?, 
                        updated_at = NOW(),
                        updated_by = ?,
                        action_type = 'updated'
                      WHERE id = ?";
        
        $stmt = $conn->prepare($sql_update);
        if (!$stmt) {
            error_log("Error en preparar UPDATE: " . $conn->error);
            $_SESSION['error'] = "Error técnico al preparar la consulta. Detalles: " . $conn->error;
        } else {
            // Ajustado para incluir el ID del usuario que actualiza
            if ($provider_id === null) {
                $stmt->bind_param("ssddiiiii", 
                    $code, $description, 
                    $price, $cost_price,
                    $stock, $stock_min, 
                    $_SESSION['user_id'],
                    $productId
                );
            } else {
                $stmt->bind_param("ssddiiiii", 
                    $code, $description, 
                    $price, $cost_price,
                    $stock, $stock_min, 
                    $provider_id,
                    $_SESSION['user_id'],
                    $productId
                );
            }
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Producto actualizado correctamente";
                header('Location: list.php');
                exit;
            } else {
                error_log("Error ejecutando UPDATE: " . $stmt->error);
                $_SESSION['error'] = "Error al guardar: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

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
    
    .last-updated {
        font-size: 0.85rem;
        color: #6c757d;
        font-style: italic;
    }
</style>

<div class="container py-4">
    <div class="card card-module-form">
        <div class="card-header card-header-module">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-edit me-2"></i> Editar Producto</h4>
                <?php if (!empty($product['updated_at'])): ?>
                <span class="last-updated">
                    Última actualización: <?= date('d/m/Y H:i', strtotime($product['updated_at'])) ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body p-4">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="post" class="needs-validation" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="code" class="form-label required-field">Código del Producto</label>
                        <input type="text" class="form-control" id="code" name="code" 
                               value="<?= htmlspecialchars($product['code']) ?>" required>
                        <div class="invalid-feedback">
                            Por favor ingrese el código del producto.
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="provider_id" class="form-label">Proveedor</label>
                        <select class="form-select" id="provider_id" name="provider_id">
                            <option value="" <?= empty($product['provider_id']) ? 'selected' : '' ?>>Seleccionar proveedor...</option>
                            <?php foreach ($providers as $provider): ?>
                            <option value="<?= $provider['id'] ?>" 
                                <?= $provider['id'] == $product['provider_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($provider['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-12">
                        <label for="description" class="form-label required-field">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="2" 
                                  required><?= htmlspecialchars($product['description']) ?></textarea>
                        <div class="invalid-feedback">
                            Por favor ingrese una descripción del producto.
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="measure_unit" class="form-label required-field">Unidad de Medida</label>
                        <select class="form-select" id="measure_unit" name="measure_unit" required>
                            <option value="unidad" <?= ($product['measure_unit'] ?? 'unidad') == 'unidad' ? 'selected' : '' ?>>Unidad</option>
                            <option value="kg" <?= ($product['measure_unit'] ?? 'unidad') == 'kg' ? 'selected' : '' ?>>Kilogramo (kg)</option>
                        </select>
                        <div class="invalid-feedback">
                            Por favor seleccione la unidad de medida.
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="price" class="form-label required-field">Precio Unitario</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="price" name="price" 
                                value="<?= htmlspecialchars($product['price']) ?>" required>
                        </div>
                        <div class="invalid-feedback">
                            Por favor ingrese un precio válido (mayor a 0).
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="stock" class="form-label required-field">Stock</label>
                        <input type="number" step="0.001" min="0" class="form-control" id="stock" name="stock" 
                            value="<?= htmlspecialchars($product['stock']) ?>" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="min_stock" class="form-label">Stock Mínimo</label>
                        <input type="number" min="0" class="form-control" id="min_stock" name="min_stock" 
                               value="<?= htmlspecialchars($product['min_stock'] ?? 5) ?>">
                    </div>
                    
                    <div class="col-12 mt-4">
                        <div class="d-flex justify-content-end gap-3">
                            <a href="list.php" class="btn btn-outline-secondary btn-form">
                                <i class="fas fa-times me-2"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-form">
                                <i class="fas fa-save me-2"></i> Guardar Cambios
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
