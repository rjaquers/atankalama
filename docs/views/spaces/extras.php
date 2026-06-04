<?php require VIEW_PATH . "/layouts/header.php"; ?>

<!-- Mensajes flash -->
<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <i class="fa-solid fa-exclamation-circle"></i> <?= $_SESSION['flash_error'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h3><i class="fa-solid fa-puzzle-piece text-primary"></i> Catálogo de Extras</h3>
    <p class="text-muted mb-0">Extras cobrables que se agregan a reservas de espacios</p>
  </div>
  <a href="<?= BASE_URL ?>/spaces" class="btn btn-outline-secondary btn-sm">
    <i class="fa-solid fa-arrow-left"></i> Volver a Espacios
  </a>
</div>

<div class="row g-4">
  <!-- Formulario nuevo extra -->
  <div class="col-lg-4">
    <div class="card border-primary">
      <div class="card-header bg-primary text-white">
        <h6 class="mb-0"><i class="fa-solid fa-plus"></i> Agregar Extra</h6>
      </div>
      <div class="card-body">
        <form method="post" action="<?= BASE_URL ?>/spaces/storeExtra">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <div class="mb-3">
            <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required placeholder="Ej: Coffee Break">
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Descripción</label>
            <input type="text" name="description" class="form-control" placeholder="Detalle opcional">
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Tipo de Cobro</label>
            <select name="charge_type" class="form-select">
              <option value="fijo">💰 Fijo</option>
              <option value="por_unidad">📦 Por Unidad</option>
              <option value="por_hora">🕐 Por Hora</option>
              <option value="por_dia">📅 Por Día</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Valor Unitario</label>
            <div class="input-group">
              <span class="input-group-text">$</span>
              <input type="number" name="unit_price" class="form-control" step="100" value="0">
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100">
            <i class="fa-solid fa-save"></i> Guardar Extra
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Lista de extras -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fa-solid fa-list"></i> Extras Registrados (<?= count($extras) ?>)</h6>
      </div>
      <div class="card-body">
        <?php if (empty($extras)): ?>
          <p class="text-muted mb-0">No hay extras registrados</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Nombre</th>
                  <th>Tipo de Cobro</th>
                  <th class="text-end">Valor</th>
                  <th class="text-center">Estado</th>
                  <th class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $chargeLabels = ['fijo' => '💰 Fijo', 'por_unidad' => '📦 Por Unidad', 'por_hora' => '🕐 Por Hora', 'por_dia' => '📅 Por Día'];
                foreach ($extras as $e):
                ?>
                  <tr>
                    <td>
                      <strong><?= htmlspecialchars($e['name']) ?></strong>
                      <?php if (!empty($e['description'])): ?>
                        <br><small class="text-muted"><?= htmlspecialchars($e['description']) ?></small>
                      <?php endif; ?>
                    </td>
                    <td><?= $chargeLabels[$e['charge_type']] ?? $e['charge_type'] ?></td>
                    <td class="text-end fw-bold">$<?= number_format((float)$e['unit_price'], 0, ',', '.') ?></td>
                    <td class="text-center">
                      <?php if ($e['active']): ?>
                        <span class="badge bg-success">Activo</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Inactivo</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $e['id'] ?>">
                        <i class="fa-solid fa-pen"></i>
                      </button>
                    </td>
                  </tr>

                  <!-- Modal editar -->
                  <div class="modal fade" id="modalEdit<?= $e['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <form method="post" action="<?= BASE_URL ?>/spaces/updateExtra/<?= $e['id'] ?>">
                          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                          <div class="modal-header">
                            <h5 class="modal-title">Editar: <?= htmlspecialchars($e['name']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <div class="mb-3">
                              <label class="form-label">Nombre</label>
                              <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($e['name']) ?>" required>
                            </div>
                            <div class="mb-3">
                              <label class="form-label">Descripción</label>
                              <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($e['description'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                              <label class="form-label">Tipo de Cobro</label>
                              <select name="charge_type" class="form-select">
                                <option value="fijo" <?= $e['charge_type'] === 'fijo' ? 'selected' : '' ?>>Fijo</option>
                                <option value="por_unidad" <?= $e['charge_type'] === 'por_unidad' ? 'selected' : '' ?>>Por Unidad</option>
                                <option value="por_hora" <?= $e['charge_type'] === 'por_hora' ? 'selected' : '' ?>>Por Hora</option>
                                <option value="por_dia" <?= $e['charge_type'] === 'por_dia' ? 'selected' : '' ?>>Por Día</option>
                              </select>
                            </div>
                            <div class="mb-3">
                              <label class="form-label">Valor</label>
                              <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="unit_price" class="form-control" step="100" value="<?= (float)$e['unit_price'] ?>">
                              </div>
                            </div>
                            <div class="mb-3">
                              <label class="form-label">Estado</label>
                              <select name="active" class="form-select">
                                <option value="1" <?= $e['active'] ? 'selected' : '' ?>>Activo</option>
                                <option value="0" <?= !$e['active'] ? 'selected' : '' ?>>Inactivo</option>
                              </select>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
