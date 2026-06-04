<?php
$categorias = $modelo->getAllCategorias();

ob_start();
?>
<div class="row mb-3 align-items-center">
    <div class="col">
        <h4 class="mb-0"><i class="bi bi-columns me-2 text-warning"></i>Categorías (columnas)</h4>
        <small class="text-muted">Definen las columnas de la tabla de precios</small>
    </div>
    <div class="col-auto">
        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalCategoria"
                onclick="abrirModalNuevo()">
            <i class="bi bi-plus-lg me-1"></i>Nueva categoría
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th class="ps-3">Nombre</th>
                    <th class="text-center">Orden</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($categorias)): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">Sin categorías aún.</td></tr>
            <?php else: ?>
                <?php foreach ($categorias as $c): ?>
                <tr>
                    <td class="ps-3 fw-semibold"><?= htmlspecialchars($c['nombre']) ?></td>
                    <td class="text-center"><?= (int)$c['orden'] ?></td>
                    <td class="text-center">
                        <?php if ($c['activo']): ?>
                            <span class="badge badge-activo">Activa</span>
                        <?php else: ?>
                            <span class="badge badge-inactivo">Inactiva</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-secondary"
                                onclick="abrirModalEditar(<?= $c['id'] ?>, <?= htmlspecialchars(json_encode($c['nombre'])) ?>, <?= (int)$c['orden'] ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" action="index.php?page=categorias/toggle" class="d-inline"
                              onsubmit="return confirm('¿Confirmar cambio de estado?')">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <input type="hidden" name="activo" value="<?= $c['activo'] ? 0 : 1 ?>">
                            <button class="btn btn-sm <?= $c['activo'] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                                <i class="bi bi-<?= $c['activo'] ? 'eye-slash' : 'eye' ?>"></i>
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

<!-- Modal crear/editar categoría -->
<div class="modal fade" id="modalCategoria" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="index.php?page=categorias/guardar" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalCat">Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="inputCatId">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre</label>
                    <input type="text" name="nombre" id="inputCatNombre" class="form-control"
                           placeholder="ej: Boutique" required maxlength="100">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Orden</label>
                    <input type="number" name="orden" id="inputCatOrden" class="form-control"
                           value="10" min="0" max="999">
                    <div class="form-text">Número menor aparece primero.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-save me-1"></i>Guardar
                </button>
            </div>
        </form>
    </div>
</div>
<?php
$contenido  = ob_get_clean();
$tituloPagina = 'Categorías';
$scriptExtra = <<<'JS'
<script>
function abrirModalNuevo() {
    document.getElementById('tituloModalCat').textContent = 'Nueva categoría';
    document.getElementById('inputCatId').value    = '';
    document.getElementById('inputCatNombre').value = '';
    document.getElementById('inputCatOrden').value  = 10;
}
function abrirModalEditar(id, nombre, orden) {
    document.getElementById('tituloModalCat').textContent = 'Editar categoría';
    document.getElementById('inputCatId').value    = id;
    document.getElementById('inputCatNombre').value = nombre;
    document.getElementById('inputCatOrden').value  = orden;
    new bootstrap.Modal(document.getElementById('modalCategoria')).show();
}
</script>
JS;

require __DIR__ . '/../layout.php';
