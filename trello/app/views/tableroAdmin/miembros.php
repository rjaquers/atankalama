<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="container-fluid py-4 px-4" style="max-width:860px">

  <!-- Encabezado -->
  <div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
    <a href="<?= BASE_URL ?>/tableroAdmin" class="btn btn-sm btn-outline-light">
      <i class="bi bi-arrow-left"></i>
    </a>
    <div class="rounded-circle"
         style="width:14px;height:14px;background:<?= htmlspecialchars($tablero['fondo_color']) ?>"></div>
    <h5 class="mb-0 text-white"><?= htmlspecialchars($tablero['nombre']) ?></h5>
    <span class="badge bg-secondary ms-1"><?= htmlspecialchars($tablero['area_nombre']) ?></span>
  </div>

  <!-- Agregar usuario -->
  <?php if (!empty($usuarios_disponibles)): ?>
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-bottom-0 pt-3 pb-0 fw-semibold">
      <i class="bi bi-person-plus me-1 text-success"></i> Agregar usuario
    </div>
    <div class="card-body">
      <div class="d-flex gap-2 flex-wrap align-items-end">
        <div class="flex-grow-1" style="min-width:200px">
          <label class="form-label small text-muted mb-1">Usuario</label>
          <select class="form-select form-select-sm" id="sel-usuario">
            <?php foreach ($usuarios_disponibles as $u): ?>
            <option value="<?= $u['id'] ?>">
              <?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?>
              (<?= htmlspecialchars($u['email']) ?>)
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="form-label small text-muted mb-1">Permiso</label>
          <select class="form-select form-select-sm" id="sel-permiso" style="width:auto">
            <option value="0">Solo lectura</option>
            <option value="1">Editor</option>
          </select>
        </div>
        <button class="btn btn-success btn-sm" id="btn-agregar">
          <i class="bi bi-plus-lg me-1"></i> Agregar
        </button>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Lista de miembros -->
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-bottom-0 pt-3 pb-0 fw-semibold">
      <i class="bi bi-people me-1 text-primary"></i>
      Miembros con acceso
      <span class="badge bg-secondary ms-1" id="badge-total"><?= count($miembros) ?></span>
    </div>
    <div class="card-body p-0">
      <table class="table table-sm mb-0 align-middle" id="tabla-miembros">
        <thead class="table-light">
          <tr>
            <th class="ps-3">Usuario</th>
            <th>Email</th>
            <th class="text-center">Permiso</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
        <tbody id="tbody-miembros">
          <?php if (empty($miembros)): ?>
          <tr id="fila-vacia">
            <td colspan="4" class="text-center text-muted py-4">
              <i class="bi bi-person-x d-block fs-3 mb-1"></i>
              Ningún usuario tiene acceso aún.
            </td>
          </tr>
          <?php endif; ?>
          <?php foreach ($miembros as $m): ?>
          <tr id="fila-<?= $m['id'] ?>">
            <td class="ps-3 fw-semibold">
              <?= htmlspecialchars($m['nombre'] . ' ' . $m['apellido']) ?>
            </td>
            <td class="text-muted small"><?= htmlspecialchars($m['email']) ?></td>
            <td class="text-center">
              <button class="btn btn-sm btn-permiso <?= $m['puede_editar'] ? 'btn-warning' : 'btn-outline-secondary' ?>"
                      data-uid="<?= $m['id'] ?>"
                      title="<?= $m['puede_editar'] ? 'Editor — clic para cambiar a Solo lectura' : 'Solo lectura — clic para hacer Editor' ?>">
                <?php if ($m['puede_editar']): ?>
                  <i class="bi bi-pencil-fill me-1"></i> Editor
                <?php else: ?>
                  <i class="bi bi-eye me-1"></i> Solo lectura
                <?php endif; ?>
              </button>
            </td>
            <td class="text-center">
              <button class="btn btn-sm btn-outline-danger btn-revocar" data-uid="<?= $m['id'] ?>"
                      title="Quitar acceso">
                <i class="bi bi-person-dash"></i>
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script>
const BASE       = '<?= BASE_URL ?>';
const TABLERO_ID = <?= (int)$tablero['id'] ?>;

/* ── Agregar usuario ─────────────────────────────────── */
document.getElementById('btn-agregar')?.addEventListener('click', async () => {
  const uid  = parseInt(document.getElementById('sel-usuario').value);
  const pe   = parseInt(document.getElementById('sel-permiso').value);
  const sel  = document.getElementById('sel-usuario');
  const opt  = sel.options[sel.selectedIndex];

  const res = await fetchJSON(BASE + '/tableroAdmin/asignar', {
    tablero_id: TABLERO_ID, usuario_id: uid, puede_editar: pe === 1
  });
  if (!res.ok) { alert(res.error || 'Error al agregar'); return; }

  // Quitar de disponibles y agregar a la tabla
  opt.remove();
  if (!document.getElementById('sel-usuario').options.length) {
    document.querySelector('.card:first-of-type')?.remove();
  }

  const nombreCompleto = opt.text.split(' (')[0];
  const emailStr       = opt.text.match(/\((.+)\)/)?.[1] ?? '';
  agregarFilaTabla(uid, nombreCompleto, emailStr, pe === 1);
  actualizarTotal(1);
});

function agregarFilaTabla(uid, nombre, email, puedeEditar) {
  document.getElementById('fila-vacia')?.remove();

  const tr = document.createElement('tr');
  tr.id = 'fila-' + uid;
  tr.innerHTML = `
    <td class="ps-3 fw-semibold">${escHtml(nombre)}</td>
    <td class="text-muted small">${escHtml(email)}</td>
    <td class="text-center">
      <button class="btn btn-sm btn-permiso ${puedeEditar ? 'btn-warning' : 'btn-outline-secondary'}"
              data-uid="${uid}"
              title="${puedeEditar ? 'Editor — clic para cambiar a Solo lectura' : 'Solo lectura — clic para hacer Editor'}">
        ${puedeEditar
          ? '<i class="bi bi-pencil-fill me-1"></i> Editor'
          : '<i class="bi bi-eye me-1"></i> Solo lectura'}
      </button>
    </td>
    <td class="text-center">
      <button class="btn btn-sm btn-outline-danger btn-revocar" data-uid="${uid}" title="Quitar acceso">
        <i class="bi bi-person-dash"></i>
      </button>
    </td>`;
  document.getElementById('tbody-miembros').appendChild(tr);
  bindFila(tr);
}

/* ── Toggle permiso ──────────────────────────────────── */
function bindFila(tr) {
  tr.querySelector('.btn-permiso')?.addEventListener('click', async function() {
    const uid = parseInt(this.dataset.uid);
    const res = await fetchJSON(BASE + '/tableroAdmin/toggleEditar', {
      tablero_id: TABLERO_ID, usuario_id: uid
    });
    if (!res.ok) { alert(res.error || 'Error'); return; }
    const pe = res.puede_editar;
    this.className = 'btn btn-sm btn-permiso ' + (pe ? 'btn-warning' : 'btn-outline-secondary');
    this.title     = pe ? 'Editor — clic para cambiar a Solo lectura' : 'Solo lectura — clic para hacer Editor';
    this.innerHTML = pe
      ? '<i class="bi bi-pencil-fill me-1"></i> Editor'
      : '<i class="bi bi-eye me-1"></i> Solo lectura';
  });

  tr.querySelector('.btn-revocar')?.addEventListener('click', async function() {
    if (!confirm('¿Quitar acceso a este usuario?')) return;
    const uid = parseInt(this.dataset.uid);
    const res = await fetchJSON(BASE + '/tableroAdmin/revocar', {
      tablero_id: TABLERO_ID, usuario_id: uid
    });
    if (!res.ok) { alert(res.error || 'Error'); return; }
    document.getElementById('fila-' + uid)?.remove();
    actualizarTotal(-1);
    if (!document.querySelector('#tbody-miembros tr')) {
      const tr = document.createElement('tr');
      tr.id = 'fila-vacia';
      tr.innerHTML = `<td colspan="4" class="text-center text-muted py-4">
        <i class="bi bi-person-x d-block fs-3 mb-1"></i>
        Ningún usuario tiene acceso aún.</td>`;
      document.getElementById('tbody-miembros').appendChild(tr);
    }
  });
}

function actualizarTotal(delta) {
  const badge = document.getElementById('badge-total');
  badge.textContent = parseInt(badge.textContent) + delta;
}

// Bind filas existentes
document.querySelectorAll('#tbody-miembros tr[id^="fila-"]').forEach(bindFila);
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
