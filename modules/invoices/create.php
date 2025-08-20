<?php
require_once dirname(__DIR__, 2) . '/includes/init.php';
requireAuth();

// Obtener datos necesarios
$clients = $conn->query("SELECT id, name, ruc FROM clients ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$products = $conn->query("SELECT id, code, description, price, stock FROM products WHERE stock > 0 ORDER BY description")->fetch_all(MYSQLI_ASSOC);

// Generar número de factura
$last_id = $conn->query("SELECT MAX(id) as last_id FROM invoices")->fetch_assoc()['last_id'];
$invoice_number = 'FACT-' . str_pad(($last_id + 1), 6, '0', STR_PAD_LEFT);

require_once dirname(__DIR__, 2) . '/includes/header.php';
?>

<style>
    /* Estilos consistentes con el listado de facturas */
    .card-module {
        border: none;
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        border-top: 4px solid #4cc9f0;
    }
    
    .btn-module {
        border-radius: 50px;
        padding: 8px 20px;
        font-weight: 500;
        transition: all 0.3s;
        border: none;
    }
    
    .btn-module:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .table-module {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    
    .table-module thead th {
        border-bottom: 2px solid #e9ecef;
        background-color: #f8f9fa;
        font-weight: 600;
        color: #495057;
        padding: 12px 15px;
    }
    
    .table-module tbody tr:hover {
        background-color: rgba(76, 201, 240, 0.05);
    }
    
    .table-module tbody td {
        padding: 12px 15px;
        vertical-align: middle;
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
    
    .card-header-module {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 15px 20px;
        font-weight: 600;
    }
</style>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Nueva Factura</h1>
        <a href="list.php" class="btn btn-outline-secondary btn-module">
            <i class="fas fa-arrow-left me-1"></i> Volver al Listado
        </a>
    </div>

    <div class="card card-module mb-4">
        <div class="card-header card-header-module">
            <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Información de la Factura</h5>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form id="invoiceForm" action="process_create.php" method="post">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="invoice_number" class="form-label">Número de Factura</label>
                        <input type="text" class="form-control" id="invoice_number" name="invoice_number" 
                               value="<?= $invoice_number ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label for="date" class="form-label">Fecha*</label>
                        <input type="date" class="form-control" id="date" name="date" 
                               value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="client_id" class="form-label">Cliente*</label>
                        <select class="form-select" id="client_id" name="client_id" required>
                            <option value="">Seleccionar cliente</option>
                            <?php foreach ($clients as $client): ?>
                            <option value="<?= $client['id'] ?>">
                                <?= htmlspecialchars($client['name']) ?> - <?= htmlspecialchars($client['ruc']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <h5 class="mb-3"><i class="fas fa-list"></i> Detalle de Factura</h5>
                
                <div class="row mb-3 g-3">
                    <div class="col-md-5">
                        <label for="product_select" class="form-label">Producto</label>
                        <select class="form-select" id="product_select">
                            <option value="">Seleccionar producto</option>
                            <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>" 
                                data-code="<?= $product['code'] ?>"
                                data-price="<?= $product['price'] ?>"
                                data-stock="<?= $product['stock'] ?>">
                            <?= htmlspecialchars($product['description']) ?> (<?= $product['code'] ?>)
                            - $<?= number_format($product['price'], 2) ?> - Stock: <?= $product['stock'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="item_quantity" class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="item_quantity" min="1" value="1">
                    </div>
                    <div class="col-md-2">
                        <label for="item_price" class="form-label">Precio Unitario</label>
                        <input type="text" class="form-control" id="item_price" readonly>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" id="addItem" class="btn btn-primary btn-module">
                            <i class="fas fa-plus"></i> Agregar Producto
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive mb-4">
                    <table class="table table-module" id="itemsTable">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th width="100">Cantidad</th>
                                <th width="120">P. Unitario</th>
                                <th width="120">Total</th>
                                <th width="50">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="invoiceItems">
                            <!-- Productos se agregarán aquí dinámicamente -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Subtotal:</th>
                                <th id="subtotal">$0.00</th>
                                <th></th>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-end">IVA (16%):</th>
                                <th id="tax">$0.00</th>
                                <th></th>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-end">Total:</th>
                                <th id="total" class="total-amount">$0.00</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <input type="hidden" name="subtotal" id="inputSubtotal" value="0">
                <input type="hidden" name="tax" id="inputTax" value="0">
                <input type="hidden" name="total" id="inputTotal" value="0">
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary btn-module me-md-2">
                        <i class="fas fa-save"></i> Guardar Factura
                    </button>
                    <a href="list.php" class="btn btn-secondary btn-module">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_select');
    const quantityInput = document.getElementById('item_quantity');
    const priceInput = document.getElementById('item_price');
    const addButton = document.getElementById('addItem');
    const invoiceItems = document.getElementById('invoiceItems');
    let itemCounter = 0;

    // Mostrar precio al seleccionar producto (CÓDIGO ORIGINAL FUNCIONAL)
    productSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const price = parseFloat(selectedOption.getAttribute('data-price'));
            priceInput.value = '$' + price.toFixed(2);
            
            // Debug: Ver en consola
            console.log('Producto seleccionado:', {
                id: selectedOption.value,
                price: price,
                stock: selectedOption.getAttribute('data-stock')
            });
        } else {
            priceInput.value = '';
        }
    });

    // Agregar producto al detalle (CÓDIGO ORIGINAL FUNCIONAL)
    addButton.addEventListener('click', function() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        
        if (!selectedOption.value) {
            alert('Por favor seleccione un producto');
            return;
        }

        const productId = selectedOption.value;
        const productCode = selectedOption.getAttribute('data-code');
        const productDesc = selectedOption.text.split(' - Stock')[0];
        const unitPrice = parseFloat(selectedOption.getAttribute('data-price'));
        const stock = parseInt(selectedOption.getAttribute('data-stock'));
        const quantity = parseInt(quantityInput.value);

        // Validaciones
        if (isNaN(quantity)) {
            alert('La cantidad debe ser un número válido');
            return;
        }

        if (quantity < 1) {
            alert('La cantidad debe ser al menos 1');
            return;
        }

        if (quantity > stock) {
            alert(`No hay suficiente stock. Disponible: ${stock}`);
            return;
        }

        // Verificar si el producto ya está en la factura
        const existingItems = document.querySelectorAll(`input[name^="products["][value="${productId}"]`);
        if (existingItems.length > 0) {
            alert('Este producto ya fue agregado a la factura');
            return;
        }

        // Crear nueva fila
        const newRow = document.createElement('tr');
        const totalPrice = unitPrice * quantity;
        
        newRow.innerHTML = `
            <td>${productCode}</td>
            <td>${productDesc}</td>
            <td>${quantity}</td>
            <td>$${unitPrice.toFixed(2)}</td>
            <td>$${totalPrice.toFixed(2)}</td>
            <td>
                <button type="button" class="btn btn-sm btn-danger action-btn remove-item">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <input type="hidden" name="products[${itemCounter}]" value="${productId}">
                <input type="hidden" name="quantities[${itemCounter}]" value="${quantity}">
                <input type="hidden" name="unit_prices[${itemCounter}]" value="${unitPrice}">
            </td>
        `;

        invoiceItems.appendChild(newRow);
        itemCounter++;
        
        // Limpiar selección
        productSelect.selectedIndex = 0;
        quantityInput.value = 1;
        priceInput.value = '';
        
        // Actualizar totales
        updateTotals();
        
        // Agregar evento para eliminar
        newRow.querySelector('.remove-item').addEventListener('click', function() {
            newRow.remove();
            updateTotals();
        });
    });

    // Función para actualizar totales (CÓDIGO ORIGINAL FUNCIONAL)
    function updateTotals() {
        let subtotal = 0;
        const rows = invoiceItems.querySelectorAll('tr');
        
        rows.forEach(row => {
            const priceText = row.querySelector('td:nth-child(4)').textContent;
            const quantityText = row.querySelector('td:nth-child(3)').textContent;
            
            const price = parseFloat(priceText.replace('$', '').replace(',', ''));
            const quantity = parseInt(quantityText);
            
            subtotal += price * quantity;
        });

        const tax = subtotal * 0.16; // IVA 16%
        const total = subtotal + tax;
        
        // Actualizar UI
        document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
        document.getElementById('tax').textContent = `$${tax.toFixed(2)}`;
        document.getElementById('total').textContent = `$${total.toFixed(2)}`;
        
        // Actualizar inputs hidden
        document.getElementById('inputSubtotal').value = subtotal.toFixed(2);
        document.getElementById('inputTax').value = tax.toFixed(2);
        document.getElementById('inputTotal').value = total.toFixed(2);
    }
});
</script>

<?php 
require_once dirname(__DIR__, 2) . '/includes/footer.php';
?>