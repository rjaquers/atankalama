<?php
$tipos = $modelo->getAllTipos();

ob_start();
?>
<div class="row mb-3 align-items-center">
    <div class="col">
        <h4 class="mb-0"><i class="bi bi-list-ul me-2 text-warning"></i>Tipos de habitación (filas)</h4>
        <small class="text-muted">Definen las filas de la tabla de precios</small>
    </div>
    <div class="col-auto">
        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalTipo"
                onclick="abrirModalNuevo()">
            <i class="bi bi-plus-lg me-1"></i>Nuevo tipo
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
            <?php if (empty($tipos)): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">Sin tipos aún.</td></tr>
            <?php else: ?>
                <?php foreach ($tipos as $t): ?>
                <tr>
                    <td class="ps-3 fw-semibold"><?= htmlspecialchars($t['nombre']) ?></td>
                    <td class="text-center"><?= (int)$t['orden'] ?></td>
                    <td class="text-center">
                        <?php if ($t['activo']): ?>
                            <span class="badge badge-activo">Activo</span>
                        <?php else: ?>
                            <span class="badge badge-inactivo">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-secondary"
                                onclick="abrirModalEditar(<?= $t['id'] ?>, <?= htmlspecialchars(json_encode($t['nombre'])) ?>, <?= (int)$t['orden'] ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" action="index.php?page=tipos/toggle" class="d-inline"
                              onsubmit="return confirm('¿Confirmar cambio de estado?')">
                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                            <input type="hidden" name="activo" value="<?= $t['activo'] ? 0 : 1 ?>">
                            <button class="btn btn-sm <?= $t['activo'] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                                <i class="bi bi-<?= $t['activo'] ? 'eye-slash' : 'eye' ?>"></i>
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

<!-- Modal crear/editar tipo -->
<div class="modal fade" id="modalTipo" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="index.php?page=tipos/guardar" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalTipo">Tipo de habitación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="inputTipoId">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre</label>
                    <input type="text" name="nombre" id="inputTipoNombre" class="form-control"
                           placeholder="ej: Doble o Matrimonial" required maxlength="100">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Orden</label>
                    <input type="number" name="orden" id="inputTipoOrden" class="form-control"
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
$tituloPagina = 'Tipos de habitación';
$scriptExtra = <<<'JS'
<script>
function abrirModalNuevo() {
    document.getElementById('tituloModalTipo').textContent = 'Nuevo tipo';
    document.getElementById('inputTipoId').value    = '';
    document.getElementById('inputTipoNombre').value = '';
    document.getElementById('inputTipoOrden').value  = 10;
}
function abrirModalEditar(id, nombre, orden) {
    document.getElementById('tituloModalTipo').textContent = 'Editar tipo';
    document.getElementById('inputTipoId').value    = id;
    document.getElementById('inputTipoNombre').value = nombre;
    document.getElementById('inputTipoOrden').value  = orden;
    new bootstrap.Modal(document.getElementById('modalTipo')).show();
}
</script>
JS;

require __DIR__ . '/../layout.php';
