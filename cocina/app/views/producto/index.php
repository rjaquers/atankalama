<!DOCTYPE html>
<html lang='es'>

<head>
    <?php include(ROOT_PATH . '../public/static/templates/head.php'); ?>
</head>

<body>
    <?php include(ROOT_PATH . '../public/static/templates/menu.php'); ?>

    <div class='container my-5'>
        <!-- Header Section -->
        <div class="row align-items-center mb-4">
            <div class="col">
                <h2 class="mb-0">Administración de Productos</h2>
                <p class="text-muted mt-1 mb-0" style="font-size: 0.875rem;">Gestiona el catálogo de productos de tu
                    negocio</p>
            </div>
            <div class="col-auto">
                <a href="index.php?page=producto/crear" class="btn-pro-primary">
                    <i class="bi bi-plus-lg"></i>
                    <span>Nuevo Producto</span>
                </a>
            </div>
        </div>

        <!-- Success Alert -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-pro alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div>
                    <?php if ($_GET['success'] == 'creado')
                        echo '<strong>¡Éxito!</strong> Producto creado correctamente.'; ?>
                    <?php if ($_GET['success'] == 'actualizado')
                        echo '<strong>¡Actualizado!</strong> Producto actualizado correctamente.'; ?>
                    <?php if ($_GET['success'] == 'eliminado')
                        echo '<strong>¡Eliminado!</strong> Producto eliminado correctamente.'; ?>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Table Card -->
        <div class="pro-card">
            <div class="table-responsive">
                <table class="table table-borderless pro-table mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th class="text-end" style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $index => $producto): ?>
                            <tr>
                                <td><span
                                        class="fw-semibold text-dark">#<?= str_pad($producto['producto_id'], 4, '0', STR_PAD_LEFT) ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($producto['nombre']) ?></div>
                                </td>
                                <td><span class="fw-medium">$<?= number_format($producto['precio'], 0, ',', '.') ?></span>
                                </td>
                                <td>
                                    <?php if ($producto['activo']): ?>
                                        <span class="badge-pro active">Activo</span>
                                    <?php else: ?>
                                        <span class="badge-pro inactive">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="index.php?page=producto/editar&id=<?= $producto['producto_id'] ?>"
                                            class="btn-pro-action edit" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="index.php?page=producto/eliminar&id=<?= $producto['producto_id'] ?>"
                                            class="btn-pro-action delete"
                                            onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?')"
                                            title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($productos)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-box-seam d-block mb-3" style="font-size: 2rem; color: #cbd5e1;"></i>
                                    No se encontraron productos en el catálogo.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include(ROOT_PATH . '../public/static/templates/footer.php'); ?>
</body>

</html>