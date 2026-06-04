<!--
  ===================================================
  = Proyecto: Hotel Atankalama - Sistema de Inventario =
  = Autor: Rodrigo Jaque Escobar                      =
  ===================================================
-->
<!DOCTYPE html>
<html lang="es">
<head>
    <?php
    $page_title = 'Ingreso en Lote';
    include 'views/layout/header.php';
    ?>
    <link href='https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' rel='stylesheet'/>
    <link href='https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css' rel='stylesheet'/>
</head>
<body>

<main class="container-fluid p-0 min-vh-100">

    <?php include 'views/layout/navbar.php'; ?>

    <section class="container py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-layer-group me-2 text-success"></i>Ingreso en Lote</h3>
            <a href="index.php?page=dashboard" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Fila de búsqueda + cantidad -->
        <div class="row mb-3">
            <div class="col-md-7">
                <label class="form-label">Producto</label>
                <select id="product_select" class="form-control">
                    <option value="">Buscar producto...</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>"
                                data-stock="<?= $p['quantity'] ?>"
                                data-name="<?= htmlspecialchars($p['name']) ?>">
                            <?= htmlspecialchars($p['name']) ?> (Stock actual: <?= $p['quantity'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Cantidad a ingresar</label>
                <input type="number" id="quantity_input" class="form-control" min="1" placeholder="Ej: 24">
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button type="button" class="btn btn-success w-100" onclick="addItem()">
                    <i class="fas fa-plus me-1"></i> Agregar
                </button>
            </div>
        </div>

        <!-- Tabla de items -->
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Producto</th>
                    <th>Stock actual</th>
                    <th>A ingresar</th>
                    <th>Stock final</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="batch_table">
                <tr id="emptyRow">
                    <td colspan="5" class="text-center text-muted py-3">
                        <i class="fas fa-inbox me-1"></i> Agrega productos para comenzar
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Formulario de confirmación -->
        <form method="POST" onsubmit="return prepareSubmit();">
            <input type="hidden" name="items" id="items_input">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ubicación de ingreso <span class="text-danger">*</span></label>
                    <select name="location_id" required class="form-select">
                        <option value="">Seleccionar ubicación...</option>
                        <?php foreach ($locations as $loc): ?>
                            <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Comentario</label>
                    <input type="text" name="description" class="form-control"
                           placeholder="Ej: Compra proveedor, Devolución cocina">
                </div>
            </div>

            <button class="btn btn-success px-4">
                <i class="fas fa-check me-1"></i> Confirmar Ingreso
            </button>
        </form>

    </section>

    <?php include 'views/layout/footer.php'; ?>

</main>

<script src='https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js'></script>

<script>
    let items = [];

    $(document).ready(function () {
        $('#product_select').select2({
            theme: 'bootstrap-5',
            placeholder: 'Buscar producto...',
            allowClear: true,
            width: '100%'
        });
    });

    function addItem() {
        const select         = $('#product_select');
        const selectedOption = select.find(':selected');
        const productId      = parseInt(select.val(), 10);
        const quantity       = parseInt(document.getElementById('quantity_input').value, 10);
        const stock          = parseInt(selectedOption.data('stock'), 10);

        if (!productId || !quantity || quantity <= 0) {
            alert('Seleccione un producto y una cantidad válida');
            return;
        }

        // Si el producto ya está, sumar cantidad
        const existing = items.findIndex(i => i.product_id === productId);
        if (existing >= 0) {
            items[existing].quantity += quantity;
        } else {
            items.push({
                product_id:    productId,
                name:          selectedOption.data('name'),
                stock_actual:  stock,
                quantity:      quantity
            });
        }

        renderTable();
        select.val(null).trigger('change');
        document.getElementById('quantity_input').value = '';
        document.getElementById('quantity_input').focus();
    }

    function renderTable() {
        const tbody = document.getElementById('batch_table');
        if (items.length === 0) {
            tbody.innerHTML = `<tr id="emptyRow"><td colspan="5" class="text-center text-muted py-3">
                <i class="fas fa-inbox me-1"></i> Agrega productos para comenzar</td></tr>`;
            return;
        }
        tbody.innerHTML = items.map((item, index) => `
            <tr>
                <td>${escapeHtml(item.name)}</td>
                <td class="text-muted">${item.stock_actual}</td>
                <td class="text-success fw-semibold">+${item.quantity}</td>
                <td class="fw-semibold">${item.stock_actual + item.quantity}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(${index})">
                        <i class="fas fa-trash me-1"></i> Eliminar
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function removeItem(index) {
        items.splice(index, 1);
        renderTable();
    }

    function prepareSubmit() {
        if (items.length === 0) {
            alert('Debe agregar al menos un producto');
            return false;
        }
        document.getElementById('items_input').value = JSON.stringify(items);
        return true;
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>

</body>
</html>
