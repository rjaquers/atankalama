<!DOCTYPE html>
<html lang='es'>
<head>
    <?php
    $page_title = 'Gestión de Productos';
    include 'views/layout/header.php'; // SOLO metadatos y links (sin <body> ni <nav>)
    ?>
</head>
<body>

<main class="container py-3 px-2 bg-gradient text-dark" style='background: linear-gradient(135deg, #5c6bc0 0%, #3949ab 100%); min-height: 100vh;'>
    <!-- Navbar (fuera del <head>) -->
    <?php include 'views/layout/navbar.php'; ?>
    <br>
    <div class='d-flex justify-content-between align-items-center mb-4'>
        <h2><i class='fas fa-plus me-2'></i><?=$page_title;?></h2>
        <a href='index.php' class='btn btn-secondary'>
            <i class='fas fa-arrow-left me-2'></i>Volver
        </a>

        <div class='d-flex justify-content-between align-items-center mb-4'>

            <?php if (isAdmin()): ?>
                <a href='index.php?page=products&action=create' class='btn btn-success mb-3'>
                    <i class='fas fa-plus me-2'></i>Nuevo Producto
                </a>
            <?php endif; ?>
        </div>
    </div>
    <!-- Acciones rápidas -->
    <br>


    <!-- Principals -->
    <div class="row   mb-12">
        <!-- Alertas -->
        <div class='row justify-content-center'>
            <div class='card shadow-sm'>
                <div class='card-body'>

                    <!-- 🔍 FILTROS PERSONALIZADOS -->
                    <div class='row mb-3 g-2'>
                        <div class='col-md-3'>
                            <label class='form-label fw-bold'>Buscar por nombre</label>
                            <input type='text' id='filtroNombre' class='form-control' placeholder='Ej: Cloro, Shampoo...'>
                        </div>
                        <div class='col-md-3'>
                            <label class='form-label fw-bold'>Filtrar por categoría</label>
                            <select id='filtroCategoria' class='form-select'>
                                <option value=''>Todas las categorías</option>
                                <?php
                                // 🧩 Generar lista única de categorías desde $products
                                $categorias = [];
                                foreach ($products as $p) {
                                    if (! in_array($p['category_name'], $categorias)) {
                                        $categorias[] = $p['category_name'];
                                    }
                                }
                                sort($categorias);
                                foreach ($categorias as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Filtrar por estado</label>
                            <select id="filtroEstado" class="form-select">
                                <option value="">Todos</option>
                                <option value="Activo">Activo</option>
                                <option value="Inactivo">Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <!-- 🧹 Botón limpiar filtros -->
                            <button id="btnLimpiar" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-undo"></i> Limpiar filtros
                            </button>
                        </div>
                    </div>

                    <!-- 📊 Contador dinámico -->
                    <div class="alert alert-info py-2 mb-3" id="contadorProductos">
                        <i class="fas fa-info-circle"></i> Cargando información...
                    </div>

                    <?php if (!empty($_SESSION['flash_success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['flash_success']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['flash_success']); ?>
                    <?php endif; ?>

                    <?php if (!empty($_SESSION['flash_error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['flash_error']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['flash_error']); ?>
                    <?php endif; ?>


                    <div class="table-responsive">
                        <table id="tablaProductos" class="table table-hover align-middle">
                            <thead class="table-dark">
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Ubicación</th>
                                <th>Stock</th>
                                <th>Unidad</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr class="<?php echo $product['quantity'] <= $product['min_stock'] ? 'table-warning' : ''; ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        <?php if ($product['description']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($product['description']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-primary"><?php echo htmlspecialchars($product['category_name']); ?></span></td>
                                    <td><small><?php echo htmlspecialchars($product['location_name']); ?></small></td>
                                    <td>
                                        <?php if ($product['quantity'] == 0): ?>
                                            <span class="badge bg-danger">Sin stock</span>
                                        <?php elseif ($product['quantity'] <= $product['min_stock']): ?>
                                            <span class="badge bg-warning text-dark"><?php echo $product['quantity']; ?> (Bajo)</span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><?php echo $product['quantity']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['unit']); ?></td>
                                    <td>
                                        <?php if ($product['status'] === 'active'): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="index.php?page=products&action=view&id=<?php echo $product['id']; ?>"
                                               class="btn btn-info btn-sm" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="index.php?page=products&action=update_stock&id=<?php echo $product['id']; ?>"
                                               class="btn btn-warning btn-sm" title="Actualizar stock">
                                                <i class="fas fa-edit"></i>
                                            </a>


                                      <!--      <a href="index.php?page=products&action=duplicate&id=<?php /*=(int)$product['id']*/?>"
                                               class='btn btn-sm btn-success'
                                               title='Duplicar Producto'>
                                                <i class='fas fa-copy'></i>
                                            </a>-->

                                            <?php if (isAdmin()): ?>
                                                <a href="index.php?page=products&action=edit&id=<?php echo $product['id']; ?>"
                                                   class="btn btn-primary btn-sm" title="Editar">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                                <a href="index.php?page=products&action=delete&id=<?php echo $product['id']; ?>"
                                                   class="btn btn-danger btn-sm delete-confirm" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php include 'views/layout/footer.php'; ?>
</main>
<!--//Adicionales de la págona-->

<!-- 🔽 Integración DataTables + Bootstrap -->
<link rel='stylesheet' href='https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css'>
<script src='https://code.jquery.com/jquery-3.7.1.min.js'></script>
<script src='https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js'></script>
<script src='https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js'></script>

<script>
    /**
     * 🧠 Inicializa la tabla de productos con DataTables, filtros personalizados y contador dinámico
     * @autor: Rodrigo Jaque · Hotel Atankalama
     * @compatibilidad: PHP 7.4 / Bootstrap 5 / DataTables 1.13
     */
    $(document).ready(function () {

        // Inicializar DataTable
        var tabla = $('#tablaProductos').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            order: [[0, 'asc']],
            responsive: true
        });

        // Función para actualizar el contador dinámico
        function actualizarContador() {
            var info = tabla.page.info();
            var total = info.recordsTotal;
            var filtrados = info.recordsDisplay;

            var activos = 0;
            var inactivos = 0;

            // Contar activos/inactivos visibles
            tabla.rows({filter: 'applied'}).every(function () {
                var estado = $(this.node()).find('td:eq(5)').text().trim();
                if (estado === 'Activo') activos++;
                else if (estado === 'Inactivo') inactivos++;
            });

            $('#contadorProductos').html(
                `<strong>Mostrando ${filtrados}</strong> productos (${activos} activos, ${inactivos} inactivos)
             de un total de <strong>${total}</strong> registrados.`
            );
        }

        // Actualizar al cargar y cada vez que cambie un filtro
        tabla.on('draw', actualizarContador);
        actualizarContador();

        // Filtros personalizados
        $('#filtroNombre').on('keyup', function () {
            tabla.column(0).search(this.value).draw();
        });

        $('#filtroCategoria').on('change', function () {
            tabla.column(1).search(this.value).draw();
        });

        $('#filtroEstado').on('change', function () {
            var val = this.value;
            if (val === 'Activo') {
                tabla.column(5).search('Activo').draw();
            } else if (val === 'Inactivo') {
                tabla.column(5).search('Inactivo').draw();
            } else {
                tabla.column(5).search('').draw();
            }
        });

        // 🧹 Botón para limpiar filtros
        $('#btnLimpiar').on('click', function () {
            $('#filtroNombre').val('');
            $('#filtroCategoria').val('');
            $('#filtroEstado').val('');
            tabla.search('').columns().search('').draw();
        });
    });
</script>
</body>
</html>









