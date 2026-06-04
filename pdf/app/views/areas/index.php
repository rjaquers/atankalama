<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold mb-0"><i class="bi bi-building text-primary me-2"></i>Áreas</h4>
    <small class="text-muted">Gestión de áreas del hotel</small>
  </div>
  <a href="<?= BASE_URL ?>/areas/crear" class="btn btn-primary">
    <i class="bi bi-plus-circle-fill me-1"></i> Nueva Área
  </a>
</div>

<?php if (empty($areas)): ?>
  <div class="stat-card text-center py-5">
    <i class="bi bi-building fs-1 text-muted opacity-25 d-block mb-3"></i>
    <p class="text-muted mb-0">No hay áreas registradas.</p>
    <a href="<?= BASE_URL ?>/areas/crear" class="btn btn-primary mt-3">
      <i class="bi bi-plus-circle-fill me-1"></i> Crear primera área
    </a>
  </div>
<?php else: ?>
  <div class="stat-card p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3" style="width:44px">Color</th>
            <th style="width:44px">Icono</th>
            <th>Nombre</th>
            <th class="d-none d-md-table-cell">Descripción</th>
            <th class="text-center" style="width:90px">Usuarios</th>
            <th style="width:90px">Estado</th>
            <th class="pe-3 text-end" style="width:120px">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($areas as $area): ?>
            <tr>
              <!-- Color -->
              <td class="ps-3">
                <div style="width:24px;height:24px;border-radius:5px;background:<?= htmlspecialchars($area['color'] ?? '#3B82F6') ?>;border:1px solid rgba(0,0,0,.1)"></div>
              </td>

              <!-- Icono -->
              <td>
                <?php if (!empty($area['icono'])): ?>
                  <i class="bi bi-<?= htmlspecialchars($area['icono']) ?> fs-5" style="color:<?= htmlspecialchars($area['color'] ?? '#3B82F6') ?>"></i>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>

              <!-- Nombre -->
              <td>
                <span class="fw-semibold" style="font-size:14px"><?= htmlspecialchars($area['nombre']) ?></span>
              </td>

              <!-- Descripción -->
              <td class="d-none d-md-table-cell">
                <span class="text-muted" style="font-size:13px">
                  <?= htmlspecialchars($area['descripcion'] ?? '') ?: '—' ?>
                </span>
              </td>

              <!-- Usuarios -->
              <td class="text-center">
                <span class="badge bg-primary-subtle text-primary-emphasis">
                  <i class="bi bi-people me-1"></i><?= (int)($area['usuarios'] ?? 0) ?>
                </span>
              </td>

              <!-- Estado -->
              <td>
                <?php if (($area['estado'] ?? '') === 'activo'): ?>
                  <span class="badge bg-success">Activo</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Inactivo</span>
                <?php endif; ?>
              </td>

              <!-- Acciones -->
              <td class="pe-3 text-end">
                <div class="d-flex gap-1 justify-content-end">
                  <?php if (($area['estado'] ?? '') === 'activo' && ($area['usuarios'] ?? 0) > 0): ?>
                    <a href="<?= BASE_URL ?>/chat/grupoArea/<?= (int)$area['id'] ?>"
                       class="btn btn-sm btn-outline-success" title="Abrir chat de área">
                      <i class="bi bi-chat-dots-fill"></i>
                    </a>
                  <?php endif; ?>

                  <a href="<?= BASE_URL ?>/areas/editar/<?= (int)$area['id'] ?>"
                     class="btn btn-sm btn-outline-primary" title="Editar">
                    <i class="bi bi-pencil-fill"></i>
                  </a>

                  <form method="POST"
                        action="<?= BASE_URL ?>/areas/toggle/<?= (int)$area['id'] ?>"
                        class="d-inline"
                        onsubmit="return confirm('¿Confirmar cambio de estado?')">
                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                    <button type="submit"
                            class="btn btn-sm <?= ($area['estado'] ?? '') === 'activo' ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                            title="<?= ($area['estado'] ?? '') === 'activo' ? 'Desactivar' : 'Activar' ?>">
                      <i class="bi bi-<?= ($area['estado'] ?? '') === 'activo' ? 'pause-circle' : 'play-circle' ?>-fill"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
