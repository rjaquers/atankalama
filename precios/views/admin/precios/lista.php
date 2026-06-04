<?php
$categorias = $modelo->getCategorias();
$tipos      = $modelo->getTipos();
$grilla     = $modelo->getGrilla();

ob_start();
?>
<div class="row mb-3 align-items-center">
    <div class="col">
        <h4 class="mb-0"><i class="bi bi-grid-3x3-gap me-2 text-warning"></i>Grilla de precios</h4>
        <small class="text-muted">Haz clic en cualquier celda para editar el precio</small>
    </div>
    <div class="col-auto">
        <a href="../../index.php" target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-eye me-1"></i>Ver pantalla pública
        </a>
    </div>
</div>

<?php if (empty($categorias) || empty($tipos)): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Debes agregar al menos una <a href="index.php?page=categorias/lista">categoría</a>
        y un <a href="index.php?page=tipos/lista">tipo de habitación</a> para mostrar la grilla.
    </div>
<?php else: ?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 text-center align-middle">
                <thead>
                    <tr>
                        <th class="text-start ps-3">Tipo</th>
                        <?php foreach ($categorias as $cat): ?>
                            <th><?= htmlspecialchars($cat['nombre']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tipos as $tipo): ?>
                    <tr>
                        <td class="text-start ps-3 fw-semibold"><?= htmlspecialchars($tipo['nombre']) ?></td>
                        <?php foreach ($categorias as $cat): ?>
                            <?php $precio = $grilla[$tipo['id']][$cat['id']] ?? '--'; ?>
                            <td class="precio-celda"
                                data-tipo-id="<?= $tipo['id'] ?>"
                                data-cat-id="<?= $cat['id'] ?>"
                                data-precio="<?= htmlspecialchars($precio) ?>"
                                data-tipo-nombre="<?= htmlspecialchars($tipo['nombre']) ?>"
                                data-cat-nombre="<?= htmlspecialchars($cat['nombre']) ?>"
                                title="Clic para editar">
                                <span class="precio-valor"><?= htmlspecialchars($precio) ?></span>
                                <i class="bi bi-pencil ms-1 text-secondary opacity-50" style="font-size:.7rem"></i>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal editar precio -->
<div class="modal fade" id="modalEditarPrecio" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Editar precio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2" id="labelCeldaModal"></p>
                <label class="form-label fw-semibold">Precio</label>
                <input type="text" id="inputPrecioModal" class="form-control form-control-lg text-center"
                       placeholder="$00.000 o --">
                <div class="form-text">Usa formato <code>$77.350</code> o <code>--</code> para sin precio.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnGuardarPrecio">
                    <i class="bi bi-save me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$contenido  = ob_get_clean();
$tituloPagina = 'Grilla de precios';
$scriptExtra = <<<'JS'
<script>
const modal     = new bootstrap.Modal(document.getElementById('modalEditarPrecio'));
const inputPrecio = document.getElementById('inputPrecioModal');
let celdaActiva = null;

document.querySelectorAll('.precio-celda').forEach(td => {
    td.addEventListener('click', () => {
        celdaActiva = td;
        document.getElementById('labelCeldaModal').textContent =
            td.dataset.tipoNombre + ' · ' + td.dataset.catNombre;
        inputPrecio.value = td.dataset.precio;
        modal.show();
        setTimeout(() => { inputPrecio.select(); }, 300);
    });
});

inputPrecio.addEventListener('keydown', e => {
    if (e.key === 'Enter') document.getElementById('btnGuardarPrecio').click();
});

document.getElementById('btnGuardarPrecio').addEventListener('click', () => {
    if (!celdaActiva) return;
    const btn = document.getElementById('btnGuardarPrecio');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando…';

    fetch('index.php?page=precios/guardar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            tipo_id:     celdaActiva.dataset.tipoId,
            categoria_id: celdaActiva.dataset.catId,
            precio:      inputPrecio.value
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            celdaActiva.querySelector('.precio-valor').textContent = data.precio;
            celdaActiva.dataset.precio = data.precio;
            modal.hide();
        } else {
            alert('Error: ' + (data.error ?? 'desconocido'));
        }
    })
    .catch(() => alert('Error de red'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save me-1"></i>Guardar';
    });
});
</script>
JS;

require __DIR__ . '/../layout.php';
