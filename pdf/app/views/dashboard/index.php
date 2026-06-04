<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-start mb-4">
  <div>
    <h4 class="fw-bold mb-0">
      <?php
      $hora = (int)date('H');
      if ($hora < 12) echo 'Buenos días';
      elseif ($hora < 19) echo 'Buenas tardes';
      else echo 'Buenas noches';
      ?>,
      <?= htmlspecialchars(explode(' ', $_SESSION['user_nombre'] ?? 'Usuario')[0]) ?>
    </h4>
    <p class="text-muted small mb-0"><?= date('l d \d\e F, Y') ?></p>
  </div>
  <a href="<?= BASE_URL ?>/chat" class="btn btn-primary d-none d-md-inline-flex align-items-center gap-2">
    <i class="bi bi-chat-dots-fill"></i> Ir al Chat
  </a>
</div>

<!-- ---- STAT CARDS ---- -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="stat-value text-primary"><?= (int)($stats['usuarios_activos'] ?? 0) ?></div>
          <div class="stat-label">Usuarios activos</div>
        </div>
        <div class="stat-icon" style="background:#dbeafe">
          <i class="bi bi-people-fill text-primary"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="stat-value text-info"><?= (int)($stats['mensajes_hoy'] ?? 0) ?></div>
          <div class="stat-label">Mensajes hoy</div>
        </div>
        <div class="stat-icon" style="background:#cffafe">
          <i class="bi bi-chat-dots-fill text-info"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="stat-value text-warning"><?= (int)($stats['tareas']['pendientes'] ?? 0) ?></div>
          <div class="stat-label">Tareas pendientes</div>
        </div>
        <div class="stat-icon" style="background:#fef9c3">
          <i class="bi bi-clipboard-check-fill text-warning"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="stat-value text-danger"><?= (int)($stats['mantencion']['pendientes'] ?? 0) ?></div>
          <div class="stat-label">Mant. pendiente</div>
        </div>
        <div class="stat-icon" style="background:#fee2e2">
          <i class="bi bi-wrench-adjustable-circle-fill text-danger"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ---- ACCESO RÁPIDO ---- -->
<div class="stat-card mb-4">
  <h6 class="fw-semibold mb-3">Acceso rápido</h6>
  <div class="d-flex flex-wrap gap-2">
    <a href="<?= BASE_URL ?>/chat" class="btn btn-primary btn-sm">
      <i class="bi bi-chat-dots-fill"></i> Chat
    </a>
    <a href="<?= BASE_URL ?>/tareas/crear" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-plus-circle"></i> Nueva Tarea
    </a>
    <a href="<?= BASE_URL ?>/mantencion/crear" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-plus-circle"></i> Nueva Mantención
    </a>
    <?php if (($_SESSION['user_rol'] ?? '') === 'Administrador'): ?>
    <a href="<?= BASE_URL ?>/usuarios/crear" class="btn btn-outline-dark btn-sm">
      <i class="bi bi-person-plus"></i> Nuevo Usuario
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- ---- RESUMEN TAREAS + MANTENCIÓN ---- -->
<div class="row g-3">
  <div class="col-md-6">
    <div class="stat-card h-100">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-semibold mb-0">
          <i class="bi bi-clipboard-check-fill text-primary me-1"></i> Tareas
        </h6>
        <a href="<?= BASE_URL ?>/tareas" class="btn btn-sm btn-outline-primary">Ver todas</a>
      </div>
      <div class="row g-2 text-center">
        <div class="col-4">
          <div class="rounded p-2" style="background:#fef9c3">
            <div class="fw-bold fs-5"><?= (int)($stats['tareas']['pendientes'] ?? 0) ?></div>
            <small class="text-muted">Pendientes</small>
          </div>
        </div>
        <div class="col-4">
          <div class="rounded p-2" style="background:#dbeafe">
            <div class="fw-bold fs-5"><?= (int)($stats['tareas']['en_proceso'] ?? 0) ?></div>
            <small class="text-muted">En proceso</small>
          </div>
        </div>
        <div class="col-4">
          <div class="rounded p-2" style="background:#d1fae5">
            <div class="fw-bold fs-5"><?= (int)($stats['tareas']['completadas'] ?? 0) ?></div>
            <small class="text-muted">Completadas</small>
          </div>
        </div>
      </div>
      <?php if (!empty($stats['mis_tareas'])): ?>
      <hr class="my-3">
      <p class="mb-2 text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Mis tareas asignadas</p>
      <div class="d-flex gap-2">
        <span class="badge rounded-pill px-3" style="background:#fef9c3;color:#92400e;font-size:12px">
          <?= (int)($stats['mis_tareas']['pendientes'] ?? 0) ?> pendientes
        </span>
        <span class="badge rounded-pill px-3" style="background:#dbeafe;color:#1e40af;font-size:12px">
          <?= (int)($stats['mis_tareas']['en_proceso'] ?? 0) ?> en proceso
        </span>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-md-6">
    <div class="stat-card h-100">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-semibold mb-0">
          <i class="bi bi-wrench-adjustable-circle-fill text-danger me-1"></i> Mantención
        </h6>
        <a href="<?= BASE_URL ?>/mantencion" class="btn btn-sm btn-outline-danger">Ver todas</a>
      </div>
      <div class="row g-2 text-center">
        <div class="col-4">
          <div class="rounded p-2" style="background:#fef9c3">
            <div class="fw-bold fs-5"><?= (int)($stats['mantencion']['pendientes'] ?? 0) ?></div>
            <small class="text-muted">Pendientes</small>
          </div>
        </div>
        <div class="col-4">
          <div class="rounded p-2" style="background:#dbeafe">
            <div class="fw-bold fs-5"><?= (int)($stats['mantencion']['en_proceso'] ?? 0) ?></div>
            <small class="text-muted">En proceso</small>
          </div>
        </div>
        <div class="col-4">
          <div class="rounded p-2" style="background:#d1fae5">
            <div class="fw-bold fs-5"><?= (int)($stats['mantencion']['completadas'] ?? 0) ?></div>
            <small class="text-muted">Completadas</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
