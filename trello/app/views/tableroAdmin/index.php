<?php require VIEW_PATH . '/layouts/header.php'; ?>

<?php
$colores_rapidos = [
    '#1e3a5f','#3b82f6','#06b6d4','#10b981',
    '#f59e0b','#ef4444','#8b5cf6','#ec4899',
    '#1e293b','#475569',
];
?>

<style>
.btn-color-swatch {
    width: 26px; height: 26px; border-radius: 50%;
    border: 2px solid transparent; cursor: pointer;
    transition: transform .12s, border-color .12s;
    flex-shrink: 0;
}
.btn-color-swatch:hover { transform: scale(1.15); }
.btn-color-swatch.selected { border-color: #fff; outline: 2px solid #3b82f6; outline-offset: 1px; }
</style>

<div class="container-fluid py-4 px-4">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-shield-lock fs-4 text-warning"></i>
      <h5 class="mb-0 text-white">Administración de tableros</h5>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoTablero">
      <i class="bi bi-plus-lg me-1"></i> Nuevo Tablero
    </button>
  </div>

  <div class="row g-3">
    <?php foreach ($tableros as $t): ?>
    <div class="col-12 col-md-6 col-xl-4" id="card-tablero-<?= $t['id'] ?>">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body d-flex align-items-center gap-3">

          <!-- Dot de color (se actualiza al editar) -->
          <div class="tablero-color-dot rounded-circle flex-shrink-0"
               id="dot-<?= $t['id'] ?>"
               style="width:16px;height:16px;background:<?= htmlspecialchars($t['fondo_color']) ?>"></div>

          <div class="flex-grow-1 overflow-hidden">
            <div class="fw-semibold text-truncate tablero-nombre"
                 id="nombre-<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre']) ?></div>
            <div class="text-muted small"><?= htmlspecialchars($t['area_nombre']) ?></div>
          </div>

          <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <span class="badge bg-secondary" title="Total miembros">
              <i class="bi bi-people me-1"></i><?= (int)$t['total_miembros'] ?>
            </span>
            <div class="dropdown">
              <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                      data-bs-toggle="dropdown">
                <i class="bi bi-three-dots-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <button class="dropdown-item btn-editar-tablero"
                          data-id="<?= $t['id'] ?>"
                          data-nombre="<?= htmlspecialchars($t['nombre'], ENT_QUOTES) ?>"
                          data-color="<?= htmlspecialchars($t['fondo_color'], ENT_QUOTES) ?>"
                          data-area="<?= (int)$t['area_id'] ?>">
                    <i class="bi bi-pencil me-2"></i> Editar tablero
                  </button>
                </li>
                <li>
                  <a class="dropdown-item"
                     href="<?= BASE_URL ?>/tableroAdmin/miembros?id=<?= $t['id'] ?>">
                    <i class="bi bi-person-gear me-2"></i> Gestionar miembros
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <button class="dropdown-item text-danger btn-eliminar-tablero"
                          data-id="<?= $t['id'] ?>"
                          data-nombre="<?= htmlspecialchars($t['nombre'], ENT_QUOTES) ?>">
                    <i class="bi bi-trash me-2"></i> Eliminar tablero
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($tableros)): ?>
    <div class="col-12 text-center text-white-50 py-5">
      <i class="bi bi-layout-three-columns fs-1 d-block mb-2"></i>
      No hay tableros creados aún.
    </div>
    <?php endif; ?>
  </div>

</div>

<!-- ══════════════════════════════════════════════
     Modal: CREAR tablero
══════════════════════════════════════════════ -->
<div class="modal fade" id="modalNuevoTablero" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Crear nuevo tablero</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="form-nuevo-tablero">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nombre del tablero</label>
            <input type="text" class="form-control" name="nombre"
                   placeholder="Ej: Mantenimiento Preventivo" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Área</label>
            <select class="form-select" name="area_id" required>
              <option value="">— Seleccionar área —</option>
              <?php foreach ($areas as $a): ?>
                <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Color de fondo</label>
            <?= colorPickerHtml('crear', '#3b82f6', $colores_rapidos) ?>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn-guardar-tablero">
          <i class="bi bi-plus-lg me-1"></i> Crear tablero
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════
     Modal: EDITAR tablero
══════════════════════════════════════════════ -->
<div class="modal fade" id="modalEditarTablero" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Editar tablero</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="form-editar-tablero">
          <input type="hidden" id="edit-tablero-id">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nombre del tablero</label>
            <input type="text" class="form-control" id="edit-tablero-nombre"
                   placeholder="Nombre del tablero" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Área</label>
            <select class="form-select" id="edit-tablero-area" required>
              <option value="">— Seleccionar área —</option>
              <?php foreach ($areas as $a): ?>
                <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Color de fondo</label>
            <?= colorPickerHtml('editar', '#3b82f6', $colores_rapidos) ?>
          </div>
          <!-- Preview en vivo del color seleccionado -->
          <div class="p-3 rounded d-flex align-items-center gap-2 mt-2"
               id="edit-preview"
               style="background:#3b82f6;transition:background .2s">
            <i class="bi bi-layout-three-columns text-white"></i>
            <span class="text-white fw-semibold small" id="edit-preview-nombre">Nombre del tablero</span>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn-guardar-edicion">
          <i class="bi bi-floppy me-1"></i> Guardar cambios
        </button>
      </div>
    </div>
  </div>
</div>

<?php
function colorPickerHtml(string $prefix, string $valorDefecto, array $colores): string {
    $swatches = '';
    foreach ($colores as $c) {
        $swatches .= '<button type="button" class="btn-color-swatch"
                              data-prefix="' . $prefix . '"
                              data-color="' . $c . '"
                              style="background:' . $c . '"></button>';
    }
    return '
    <div class="d-flex gap-2 flex-wrap align-items-center">
        ' . $swatches . '
        <input type="color"
               id="' . $prefix . '-color-input"
               value="' . $valorDefecto . '"
               title="Color personalizado"
               style="width:30px;height:30px;padding:1px;border:2px solid #dee2e6;border-radius:50%;cursor:pointer;background:none">
    </div>
    <input type="hidden" id="' . $prefix . '-fondo-color" value="' . $valorDefecto . '">';
}
?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const BASE = '<?= BASE_URL ?>';

  /* ── Swatches de color (para ambos modales) ──────────── */
  function initSwatches(prefix) {
    const hiddenInput = document.getElementById(prefix + '-fondo-color');
    const colorInput  = document.getElementById(prefix + '-color-input');
    const swatches    = document.querySelectorAll(`.btn-color-swatch[data-prefix="${prefix}"]`);

    function setColor(hex) {
      hiddenInput.value  = hex;
      colorInput.value   = hex;
      swatches.forEach(s => s.classList.toggle('selected', s.dataset.color === hex));
      // Actualizar preview del modal editar
      if (prefix === 'editar') updateEditPreview();
    }

    swatches.forEach(btn => btn.addEventListener('click', () => setColor(btn.dataset.color)));
    colorInput.addEventListener('input', () => setColor(colorInput.value));

    // Seleccionar el primero por defecto (modal crear)
    if (prefix === 'crear') setColor(hiddenInput.value);

    return setColor;
  }

  const setColorCrear  = initSwatches('crear');
  const setColorEditar = initSwatches('editar');

  /* ── Preview en vivo (modal editar) ──────────────────── */
  function updateEditPreview() {
    const color  = document.getElementById('editar-fondo-color').value;
    const nombre = document.getElementById('edit-tablero-nombre').value || 'Nombre del tablero';
    document.getElementById('edit-preview').style.background = color;
    document.getElementById('edit-preview-nombre').textContent = nombre;
  }
  document.getElementById('edit-tablero-nombre')?.addEventListener('input', updateEditPreview);

  /* ── Abrir modal EDITAR con datos del tablero ────────── */
  document.querySelectorAll('.btn-editar-tablero').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('edit-tablero-id').value        = btn.dataset.id;
      document.getElementById('edit-tablero-nombre').value    = btn.dataset.nombre;
      document.getElementById('edit-tablero-area').value      = btn.dataset.area;
      setColorEditar(btn.dataset.color);
      updateEditPreview();
      new bootstrap.Modal(document.getElementById('modalEditarTablero')).show();
    });
  });

  /* ── Guardar edición ─────────────────────────────────── */
  document.getElementById('btn-guardar-edicion').addEventListener('click', async () => {
    const id          = document.getElementById('edit-tablero-id').value;
    const nombre      = document.getElementById('edit-tablero-nombre').value.trim();
    const area_id     = document.getElementById('edit-tablero-area').value;
    const fondo_color = document.getElementById('editar-fondo-color').value;

    if (!nombre || !area_id) { alert('Completa todos los campos'); return; }

    const btn = document.getElementById('btn-guardar-edicion');
    btn.disabled = true;
    const res = await fetchJSON(BASE + '/tableroAdmin/actualizar',
                                { id, nombre, fondo_color, area_id });
    btn.disabled = false;

    if (!res.ok) { alert(res.error || 'Error al guardar'); return; }

    // Actualizar el card en el DOM sin recargar
    document.getElementById('dot-'    + id).style.background = res.fondo_color;
    document.getElementById('nombre-' + id).textContent       = res.nombre;

    bootstrap.Modal.getInstance(document.getElementById('modalEditarTablero')).hide();
  });

  /* ── Crear tablero ───────────────────────────────────── */
  document.getElementById('btn-guardar-tablero').addEventListener('click', async () => {
    const form    = document.getElementById('form-nuevo-tablero');
    const nombre  = form.querySelector('[name=nombre]').value.trim();
    const area_id = form.querySelector('[name=area_id]').value;
    const fondo_color = document.getElementById('crear-fondo-color').value;

    if (!nombre || !area_id) { alert('Por favor completa todos los campos'); return; }

    const btn = document.getElementById('btn-guardar-tablero');
    btn.disabled = true;
    const res = await fetchJSON(BASE + '/tableroAdmin/crear', { nombre, fondo_color, area_id });
    if (res.ok) {
      location.reload();
    } else {
      alert(res.error || 'Error al crear tablero');
      btn.disabled = false;
    }
  });

  /* ── Eliminar tablero ────────────────────────────────── */
  document.querySelectorAll('.btn-eliminar-tablero').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id     = btn.dataset.id;
      const nombre = btn.dataset.nombre;
      if (!confirm(`¿Eliminar el tablero "${nombre}"?\nEsta acción no se puede deshacer.`)) return;
      const res = await fetchJSON(BASE + '/tableroAdmin/eliminar', { id });
      if (res.ok) {
        document.getElementById('card-tablero-' + id).remove();
      } else {
        alert(res.error || 'Error al eliminar tablero');
      }
    });
  });
});
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
