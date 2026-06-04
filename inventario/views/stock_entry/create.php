<!--
  ===================================================
  = Proyecto: Hotel Atankalama - Sistema de Cocina  =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com.                   =
  = Fecha: <?= date('Y') ?>                         =
  ===================================================
-->
<?php
/**
 * Resumen:
 * Formulario para ingresar stock al inventario.
 * - Selecciona producto
 * - Ingresa cantidad
 * - Suma automáticamente al stock
 * - Registra log de movimiento
 */
?>

<?php
$page_title = 'Ingresar Inventario';
include 'views/layout/header.php';
?>

<body>
<br>
<br>
<main class="container py-4">

    <?php include 'views/layout/navbar.php'; ?>

    <h2 class="mb-4">
        <i class="fas fa-arrow-up me-2"></i><?= $page_title ?>
    </h2>

    <div class="card shadow-sm">
        <div class="card-body">


            <?php if (!empty($_SESSION['success_message'])): ?>
                <div id="flash-success" class="alert alert-success shadow-sm">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            <form method="POST">

                <div class='mb-3'>
                    <label class='form-label'>Producto *</label>

                    <select name='product_id'
                            id='product_id'
                            class='form-control'
                            required>

                        <option value=''>Buscar producto...</option>

                        <?php foreach ($products as $product): ?>
                            <option value="<?=$product['id']?>"
                                    data-stock="<?=$product['quantity']?>"
                                    data-unit="<?=htmlspecialchars($product['unit'])?>">
                                <?=htmlspecialchars($product['name'])?>
                                (<?=$product['quantity']?> <?=htmlspecialchars($product['unit'])?>)
                            </option>
                        <?php endforeach; ?>

                    </select>

                    <div class="form-text" id="stock-info">
                        Seleccione un producto para ver el stock actual
                    </div>
                </div>


                <div class="mb-3">
                    <label class="form-label">Cantidad a Ingresar *</label>
                    <input type="number" name="quantity_added" class="form-control" min="1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ubicación</label>


                    <?php foreach ($locations as $location): ?>
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="radio"
                                   name="location_id"
                                   value="<?=$location['id']?>"
                                   id="loc<?=$location['id']?>"
                                    <?= ($location['name'] === 'Bodega Cocina') ? 'checked' : '' ?>
                                   required>

                            <label class="form-check-label" for="loc<?=$location['id']?>">
                                <?=htmlspecialchars($location['name'])?>
                            </label>
                        </div>
                    <?php endforeach; ?>




                </div>


                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" class="form-control"> Sin datos</textarea>
                </div>

                <div class="alert alert-success">
                    El stock será sumado automáticamente al inventario.
                </div>

                <button class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Registrar Ingreso
                </button>

                <a href="index.php?page=dashboard" class="btn btn-secondary">
                    Cancelar
                </a>

            </form>

        </div>
    </div>

    <?php include 'views/layout/footer.php'; ?>

</main>
<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>

<link href='https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' rel='stylesheet'/>
<link href='https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css' rel='stylesheet'/>

<script src='https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'></script>

<script>
    $(document).ready(function () {

        // Activar Select2
        $('#product_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Buscar producto...',
            allowClear: true,
            width: '100%'
        }).on('select2:open', function () {
            document.querySelector('.select2-search__field').focus();
        });


        const productSelect = document.getElementById('product_id');
        const stockInfo     = document.getElementById('stock-info');
        const unitDisplay   = document.getElementById('unit-display');

        productSelect.addEventListener('change', function () {

            const selectedOption = this.options[this.selectedIndex];

            if (!selectedOption || !selectedOption.value) {
                stockInfo.innerHTML = 'Seleccione un producto para ver el stock actual';
                stockInfo.className = 'form-text';
                unitDisplay.textContent = 'unidad';
                return;
            }

            const stock = selectedOption.dataset.stock;
            const unit  = selectedOption.dataset.unit;

            unitDisplay.textContent = unit;

            // Mostrar stock visualmente
            stockInfo.innerHTML =
                `<i class="fas fa-box me-1 text-primary"></i>
             Stock actual: <strong>${stock} ${unit}</strong>`;

            stockInfo.className = 'form-text text-primary';
        });

    });



        document.addEventListener('DOMContentLoaded', function () {

        /**
         * Cuando cambia el producto seleccionado,
         * mueve el foco automáticamente al campo cantidad.
         */
        const productSelect = document.getElementById('product_id');
        const quantityInput = document.querySelector('input[name="quantity_added"]');

        if (productSelect && quantityInput) {
        productSelect.addEventListener('change', function () {
        setTimeout(function () {
        quantityInput.focus();
        quantityInput.select(); // deja listo para escribir encima
    }, 100);
    });
    }

    });


        //autodesaparecer en 1 segundo
        document.addEventListener('DOMContentLoaded', function () {

        /**
         * Si existe mensaje flash, lo oculta después de 1 segundo
         * y deja el foco listo para seguir ingresando datos.
         */
        const flash = document.getElementById('flash-success');
        const productSelect = document.getElementById('product_id');

        if (flash) {
        setTimeout(function () {
        flash.style.transition = 'opacity 0.3s ease';
        flash.style.opacity = '0';
        setTimeout(function(){
        flash.remove();
    }, 300);
    }, 1000);

        // Dejamos el cursor en producto para siguiente ingreso
        if (productSelect) {
        productSelect.focus();
    }
    }

    });
</script>


</body>
</html>
