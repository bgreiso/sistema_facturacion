</div> <!-- Cierre del container principal -->

<footer class="footer-module mt-auto">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0">
                    <i class="fas fa-file-invoice me-2" style="color: #4361ee;"></i>
                    <span>Sistema de Facturación</span>
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <p class="mb-0">
                    <span class="d-none d-md-inline">Desarrollado por </span>
                    <strong>Greiso Briceño</strong> &copy; <?= date('Y') ?>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle con Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Font Awesome -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

<!-- Scripts personalizados -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Activar tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Activar popovers
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    });
</script>

</body>
</html>