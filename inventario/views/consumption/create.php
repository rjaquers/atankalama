<!DOCTYPE html>
<html lang='es'>
<head>
    <?php
    $page_title = 'Registrar Consumo';
    include 'views/layout/header.php'; // SOLO metadatos y links (sin <body> ni <nav>)
    ?>
</head>
<body>

<main class="container py-3 px-2 bg-gradient text-dark" style='background: linear-gradient(135deg, #5c6bc0 0%, #3949ab 100%); min-height: 100vh;'>
    <!-- Navbar (fuera del <head>) -->
    <?php include 'views/layout/navbar.php'; ?>
    <br>
    <div class='d-flex justify-content-between align-items-center mb-4'>
        <h2><i class='fas fa-plus me-2'></i><?=$page_title; ?></h2>
        <a href='index.php' class='btn btn-secondary'>
            <i class='fas fa-arrow-left me-2'></i>Volver
        </a>
    </div>
    <!-- Acciones rápidas -->
    <br>


    <!-- Principals -->
    <div class="row   mb-12">
        <!-- Alertas -->
        <div class="col-12 ">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-warning text-white">
                    <i class="fas fa-bell me-2"></i>Crear producto
                </div>


                <div class='row'>
                    <div class='col-lg-12'>
                        <div class='card'>
                            <div class='card-body'>
                                <form method='POST' id='consumptionForm'>

                                    <div class='mb-3'>
                                        <label for='product_id' class='form-label'>Producto *</label>

                                        <!-- Select de productos -->
                                        <select class='form-control' id='product_id' name='product_id' required>
                                            <option value=''>Seleccionar producto</option>

                                            <?php foreach ($products as $prod): ?>
                                                <?php
                                                $prodId = (int)$prod['id'];
                                                $stock = (int)$prod['quantity'];
                                                $unit = htmlspecialchars($prod['unit'] ?? '', ENT_QUOTES, 'UTF-8');
                                                $name = htmlspecialchars($prod['name'] ?? '', ENT_QUOTES, 'UTF-8');
                                                $minStock = (int)($prod['min_stock'] ?? 0);
                                                ?>

                                                <option value="<?=$prodId?>"
                                                        data-stock="<?=$stock?>"
                                                        data-unit="<?=$unit?>">

                                                    <?=$name?>
                                                    (Stock: <?=$stock?> <?=$unit?>)

                                                    <?php if ($stock <= $minStock): ?>
                                                        - ⚠️ Stock Bajo
                                                    <?php endif; ?>

                                                </option>
                                            <?php endforeach; ?>

                                        </select>
                                    </div>

                                    <div class="row">

                                        <!-- Cantidad -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="quantity_consumed" class="form-label">
                                                    Cantidad a Consumir *
                                                </label>

                                                <div class="input-group">
                                                    <input type="number"
                                                           class="form-control"
                                                           id="quantity_consumed"
                                                           name="quantity_consumed"
                                                           min="1"
                                                           required>

                                                    <span class="input-group-text" id="unit-display">
                        unidad
                    </span>
                                                </div>

                                                <div class="form-text" id="stock-info">
                                                    Seleccione un producto para ver el stock disponible
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Ubicación -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="consumption_location" class="form-label">
                                                    Ubicación del Consumo
                                                </label>

                                                <input type="text"
                                                       class="form-control"
                                                       id="consumption_location"
                                                       name="consumption_location"
                                                       placeholder="Ej: Cocina, Habitación 102, Piso 1">

                                                <div class="form-text">
                                                    Dónde se utilizó/consumió el producto
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- Descripción -->
                                    <div class="mb-3">
                                        <label for="description" class="form-label">
                                            Descripción / Motivo
                                        </label>

                                        <textarea class="form-control"
                                                  id="description"
                                                  name="description"
                                                  rows="3"
                                                  placeholder="Ej: Cambio de sábanas, reposición de insumos"></textarea>
                                    </div>

                                    <!-- Aviso -->
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Importante:</strong>
                                        Al registrar este consumo, la cantidad será descontada automáticamente del stock del producto.
                                    </div>

                                    <!-- Botones -->
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <a href="index.php?page=consumption"
                                           class="btn btn-secondary me-md-2">
                                            Cancelar
                                        </a>

                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save me-2"></i>
                                            Registrar Consumo
                                        </button>
                                    </div>

                                </form>
                            </div>
                        </div>

                    </div>


                </div>
            </div>
        </div>



    </div>

    <?php include 'views/layout/footer.php'; ?>
</main>

<!-- ✅ jQuery (DEBE IR PRIMERO) -->
<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>

<!-- ✅ Select2 CSS -->
<link href='https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' rel='stylesheet'/>
<link href='https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css' rel='stylesheet'/>

<!-- ✅ Select2 JS -->
<script src='https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js'></script>

<script>
    /**
     * =========================================================
     * JS Consumo
     * - Activa Select2
     * - Controla stock visible
     * - Valida antes de enviar
     * =========================================================
     */

    $(document).ready(function () {

        // 🔹 Activar Select2 una sola vez
        $('#product_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Buscar producto...',
            allowClear: true,
            width: '100%'
        });

        const productSelect = document.getElementById('product_id');
        const quantityInput = document.getElementById('quantity_consumed');
        const unitDisplay = document.getElementById('unit-display');
        const stockInfo = document.getElementById('stock-info');
        const form = document.getElementById('consumptionForm');

        /**
         * Actualiza información de stock al cambiar producto
         */
        productSelect.addEventListener('change', function () {

            const selectedOption = this.options[this.selectedIndex];

            if (selectedOption.value) {

                const stock = parseInt(selectedOption.dataset.stock);
                const unit = selectedOption.dataset.unit;

                unitDisplay.textContent = unit;
                quantityInput.max = stock;

                if (stock > 0) {
                    stockInfo.innerHTML =
                        `<i class="fas fa-info-circle text-info me-1"></i>
                     Stock disponible: <strong>${stock} ${unit}</strong>`;
                    stockInfo.className = 'form-text text-info';
                } else {
                    stockInfo.innerHTML =
                        `<i class="fas fa-exclamation-triangle text-danger me-1"></i>
                     Sin stock disponible`;
                    stockInfo.className = 'form-text text-danger';
                }

            } else {

                unitDisplay.textContent = 'unidad';
                quantityInput.max = '';
                stockInfo.innerHTML = 'Seleccione un producto para ver el stock disponible';
                stockInfo.className = 'form-text';
            }
        });

        /**
         * Validación antes de enviar formulario
         */
        form.addEventListener('submit', function (e) {

            const selectedOption = productSelect.options[productSelect.selectedIndex];

            if (!selectedOption.value) return;

            const stock = parseInt(selectedOption.dataset.stock);
            const quantity = parseInt(quantityInput.value);

            if (stock === 0) {
                e.preventDefault();
                alert('No se puede registrar consumo de un producto sin stock.');
                return false;
            }

            if (quantity > stock) {
                e.preventDefault();
                alert('La cantidad a consumir no puede ser mayor al stock disponible.');
                return false;
            }

        });

    });
</script>
</body>
</html>























