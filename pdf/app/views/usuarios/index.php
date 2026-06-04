<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold mb-0"><i class="bi bi-people-fill text-primary me-2"></i>Usuarios</h4>
    <small class="text-muted">Gestión de usuarios del sistema</small>
  </div>
  <a href="<?= BASE_URL ?>/usuarios/crear" class="btn btn-primary">
    <i class="bi bi-person-plus-fill me-1"></i> Nuevo Usuario
  </a>
</div>

<!-- Buscador -->
<div class="stat-card mb-3">
  <div class="input-group">
    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
    <input type="text" id="buscar" class="form-control border-start-0 ps-0"
           placeholder="Buscar por nombre o email...">
  </div>
</div>

<!-- Tabla -->
<div class="stat-card p-0">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th class="ps-3" style="width:52px">Avatar</th>
          <th>Nombre</th>
          <th class="d-none d-md-table-cell">Email</th>
          <th class="d-none d-lg-table-cell">Área</th>
          <th class="d-none d-md-table-cell">Rol</th>
          <th>Estado</th>
          <th class="d-none d-lg-table-cell">Último acceso</th>
          <th class="pe-3 text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($usuarios)): ?>
          <tr>
            <td colspan="8" class="text-center text-muted py-5">
              <i class="bi bi-people fs-2 d-block mb-2 opacity-25"></i>
              No hay usuarios registrados
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($usuarios as $u): ?>
            <?php
              $inicial = strtoupper(mb_substr($u['nombre'], 0, 1, 'UTF-8'));
              $activo  = (int)$u['estado'] === 1;
            ?>
            <tr>
              <!-- Avatar -->
              <td class="ps-3">
                <div class="user-avatar" style="<?= !empty($u['foto_perfil']) ? '' : 'background:#' . substr(md5($u['nombre']), 0, 6) ?>">
                  <?php if (!empty($u['foto_perfil'])): ?>
                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($u['foto_perfil']) ?>" alt="foto">
                  <?php else: ?>
                    <?= htmlspecialchars($inicial) ?>
                  <?php endif; ?>
                </div>
              </td>

              <!-- Nombre -->
              <td>
                <div class="fw-semibold" style="font-size:14px"><?= htmlspecialchars($u['nombre']) ?></div>
                <small class="text-muted d-md-none"><?= htmlspecialchars($u['email']) ?></small>
              </td>

              <!-- Email -->
              <td class="d-none d-md-table-cell">
                <span class="text-muted" style="font-size:13px"><?= htmlspecialchars($u['email']) ?></span>
              </td>

              <!-- Área -->
              <td class="d-none d-lg-table-cell">
                <?php if (!empty($u['area_nombre'])): ?>
                  <span class="badge bg-light text-secondary border"><?= htmlspecialchars($u['area_nombre']) ?></span>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>

              <!-- Rol -->
              <td class="d-none d-md-table-cell">
                <span class="badge bg-primary-subtle text-primary-emphasis" style="font-size:11px">
                  <?= htmlspecialchars($u['rol_nombre'] ?? '—') ?>
                </span>
              </td>

              <!-- Estado -->
              <td>
                <?php if ($activo): ?>
                  <span class="badge bg-success">Activo</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Inactivo</span>
                <?php endif; ?>
              </td>

              <!-- Último acceso -->
              <td class="d-none d-lg-table-cell">
                <span class="text-muted" style="font-size:13px">
                  <?php if (!empty($u['ultimo_acceso'])): ?>
                    <?= date('d/m/Y H:i', strtotime($u['ultimo_acceso'])) ?>
                  <?php else: ?>
                    Nunca
                  <?php endif; ?>
                </span>
              </td>

              <!-- Acciones -->
              <td class="pe-3 text-end">
                <div class="d-flex gap-1 justify-content-end">
                  <a href="<?= BASE_URL ?>/usuarios/editar/<?= (int)$u['id'] ?>"
                     class="btn btn-sm btn-outline-primary" title="Editar">
                    <i class="bi bi-pencil-fill"></i>
                  </a>

                  <form method="POST"
                        action="<?= BASE_URL ?>/usuarios/toggleEstado/<?= (int)$u['id'] ?>"
                        class="d-inline"
                        onsubmit="return confirm('¿Confirmar cambio de estado?')">
                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                    <button type="submit"
                            class="btn btn-sm <?= $activo ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                            title="<?= $activo ? 'Desactivar' : 'Activar' ?>">
                      <i class="bi bi-<?= $activo ? 'person-dash-fill' : 'person-check-fill' ?>"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.getElementById('buscar').addEventListener('input', function () {
  var q = this.value.toLowerCase();
  document.querySelectorAll('tbody tr').forEach(function (tr) {
    tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
