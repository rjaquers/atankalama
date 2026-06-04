<?php include __DIR__ . '/../../includes/header.php'; ?>
<?php include __DIR__.'/../../includes/inc_proyect.php'; ?>
<?php
// Resumen:
// Página que lista los servicios/adicionales (colacion_adicional) y permite crear, editar y eliminar.
// Incluye protección: no permite eliminar si el servicio está asociado a lotes.
// Fin resumen.
?>

<div class="container py-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="m-0">Servicios</h3>
        <a class="btn btn-primary" href="<?= url('/servicios/nuevo') ?>">
            <i class="fas fa-plus me-1"></i> Nuevo
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <?= htmlspecialchars($flash['msg']) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                <tr>
                    <th style="width:90px">ID</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th style="width:220px" class="text-end">Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">Sin servicios todavía.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td><?= (int)$it['id'] ?></td>
                            <td><?= htmlspecialchars($it['nombre']) ?></td>
                            <td>
                                <?php
                                switch ((int)$it['tipo']) {
                                    case 1:
                                        echo '<span class="badge bg-primary">Principal</span>';
                                        break;
                                    case 2:
                                        echo '<span class="badge bg-secondary">Acompañamiento</span>';
                                        break;
                                    case 3:
                                        echo '<span class="badge bg-info text-dark">Opcional</span>';
                                        break;
                                    default:
                                        echo '<span class="badge bg-dark">Desconocido</span>';
                                }
                                ?>
                            </td>



                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary"
                                   href="<?= url('/servicios/editar/'.(int)$it['id']) ?>">
                                    <i class="fas fa-pen me-1"></i> Editar
                                </a>

                                <form method="post" action="<?= url('/servicios/eliminar') ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('¿Eliminar este servicio? (Solo si no está en uso)');">
                                    <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">
                                        <i class="fas fa-trash me-1"></i> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>