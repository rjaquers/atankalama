<?php require VIEW_PATH . '/layouts/header.php'; ?>

<?php
$userRol      = $_SESSION['user_rol']  ?? '';
$puedeCrear   = true; // todos los usuarios autenticados pueden crear tareas
$esJefeAdmin  = in_array($userRol, ['Administrador', 'Jefe de Área'], true);

// Helpers de etiqueta
$labelEstado = [
    'pendiente'  => 'Pendiente',
    'en_proceso' => 'En proceso',
    'completada' => 'Completada',
    'cancelada'  => 'Cancelada',
    'urgente'    => 'Urgente',
];
$labelPrioridad = [
    'baja'    => 'Baja',
    'media'   => 'Media',
    'alta'    => 'Alta',
    'urgente' => 'Urgente',
];
?>

<!-- ===== HEADER DE PÁGINA ===== -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h4 class="mb-0 fw-bold">
      <i class="bi bi-clipboard-check-fill text-primary me-2"></i>
      <?= htmlspecialchars($title ?? 'Tareas') ?>
    </h4>
    <small class="text-muted"><?= count($tareas) ?> tarea(s) encontrada(s)</small>
  </div>
  <?php if ($puedeCrear): ?>
  <a href="<?= BASE_URL ?>/tareas/crear" class="btn btn-success">
    <i class="bi bi-plus-lg me-1"></i> Nueva Tarea
  </a>
  <?php endif; ?>
</div>

<!-- ===== FILTROS ===== -->
<?php if (!empty($areas)): ?>
<form method="get" action="<?= BASE_URL ?>/tareas" class="stat-card mb-4">
  <div class="row g-2 align-items-end">
    <div class="col-12 col-sm-6 col-md-3">
      <label class="form-label mb-1 small fw-semibold">Área</label>
      <select name="area_id" class="form-select form-select-sm">
        <option value="">Todas las áreas</option>
        <?php foreach ($areas as $a): ?>
          <option value="<?= $a['id'] ?>" <?= (($_GET['area_id'] ?? '') == $a['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($a['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
      <label class="form-label mb-1 small fw-semibold">Estado</label>
      <select name="estado" class="form-select form-select-sm">
        <option value="">Todos los estados</option>
        <?php foreach ($labelEstado as $val => $lbl): ?>
          <option value="<?= $val ?>" <?= (($_GET['estado'] ?? '') === $val) ? 'selected' : '' ?>>
            <?= $lbl ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
      <label class="form-label mb-1 small fw-semibold">Prioridad</label>
      <select name="prioridad" class="form-select form-select-sm">
        <option value="">Todas</option>
        <?php foreach ($labelPrioridad as $val => $lbl): ?>
          <option value="<?= $val ?>" <?= (($_GET['prioridad'] ?? '') === $val) ? 'selected' : '' ?>>
            <?= $lbl ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12 col-sm-6 col-md-3 d-flex gap-2">
      <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
        <i class="bi bi-funnel-fill me-1"></i> Filtrar
      </button>
      <a href="<?= BASE_URL ?>/tareas" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-x-lg"></i>
      </a>
    </div>
  </div>
</form>
<?php endif; ?>

<!-- ===== FLASH DE ERROR ===== -->
<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <?= htmlspecialchars($_SESSION['flash_error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- ===== TABLA / LISTADO ===== -->
<?php if (empty($tareas)): ?>
  <div class="stat-card text-center py-5">
    <i class="bi bi-clipboard-x fs-1 text-muted"></i>
    <p class="mt-3 text-muted mb-0">No hay tareas que coincidan con los filtros.</p>
    <?php if ($puedeCrear): ?>
      <a href="<?= BASE_URL ?>/tareas/crear" class="btn btn-success mt-3">
        <i class="bi bi-plus-lg me-1"></i> Crear primera tarea
      </a>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="stat-card p-0 overflow-hidden">
    <div class="table-responsive">
      <table class="table table-hover table-cards mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>Título</th>
            <th>Área</th>
            <th>Asignado</th>
            <th>Prioridad</th>
            <th>Estado</th>
            <th>Vence</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tareas as $t): ?>
            <?php
              $vencida  = !empty($t['fecha_limite'])
                          && $t['fecha_limite'] < date('Y-m-d')
                          && !in_array($t['estado'], ['completada','cancelada'], true);
              $estadoCls   = 'badge-' . ($t['estado']   ?? 'pendiente');
              $prioridadCls = 'badge-' . ($t['prioridad'] ?? 'media');
            ?>
            <tr>
              <td data-label="Título">
                <a href="<?= BASE_URL ?>/tareas/ver/<?= $t['id'] ?>"
                   class="fw-semibold text-decoration-none text-dark">
                  <?= htmlspecialchars($t['titulo']) ?>
                </a>
              </td>
              <td data-label="Área">
                <?= htmlspecialchars($t['area_nombre'] ?? '—') ?>
              </td>
              <td data-label="Asignado">
                <?= htmlspecialchars($t['asignado_nombre'] ?? 'Sin asignar') ?>
              </td>
              <td data-label="Prioridad">
                <span class="badge-estado <?= htmlspecialchars($prioridadCls) ?>">
                  <?= htmlspecialchars($labelPrioridad[$t['prioridad']] ?? $t['prioridad']) ?>
                </span>
              </td>
              <td data-label="Estado">
                <span class="badge-estado <?= htmlspecialchars($estadoCls) ?>">
                  <?= htmlspecialchars($labelEstado[$t['estado']] ?? $t['estado']) ?>
                </span>
              </td>
              <td data-label="Vence">
                <?php if (!empty($t['fecha_limite'])): ?>
                  <span class="<?= $vencida ? 'text-danger fw-semibold' : '' ?>">
                    <?= htmlspecialchars(date('d/m/Y', strtotime($t['fecha_limite']))) ?>
                    <?php if ($vencida): ?>
                      <i class="bi bi-exclamation-circle-fill ms-1"></i>
                    <?php endif; ?>
                  </span>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
              <td data-label="Acciones" class="text-end">
                <a href="<?= BASE_URL ?>/tareas/ver/<?= $t['id'] ?>"
                   class="btn btn-sm btn-outline-primary me-1"
                   title="Ver detalle">
                  <i class="bi bi-eye"></i>
                </a>
                <?php if ($esJefeAdmin && !in_array($t['estado'], ['completada','cancelada'], true)): ?>
                  <a href="<?= BASE_URL ?>/tareas/editar/<?= $t['id'] ?>"
                     class="btn btn-sm btn-outline-secondary"
                     title="Editar">
                    <i class="bi bi-pencil"></i>
                  </a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
