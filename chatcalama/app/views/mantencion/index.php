<?php require VIEW_PATH . '/layouts/header.php'; ?>

<?php
// Flash messages
if (!empty($_SESSION['flash_error'])):
    $flashErr = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
endif;
if (!empty($_SESSION['flash_ok'])):
    $flashOk = $_SESSION['flash_ok'];
    unset($_SESSION['flash_ok']);
endif;

$rolActual = $_SESSION['user_rol'] ?? '';
$esAdmin   = in_array($rolActual, ['Administrador', 'Jefe de Área'], true);

$tipoBadge = [
    'emergencia' => 'badge bg-danger',
    'correctiva' => 'badge bg-warning text-dark',
    'preventiva' => 'badge bg-info text-dark',
];
$tipoIcono = [
    'emergencia' => 'bi-exclamation-octagon-fill',
    'correctiva' => 'bi-wrench',
    'preventiva' => 'bi-calendar-check',
];
?>

<!-- Header de página -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-bold mb-0"><i class="bi bi-wrench-adjustable-circle-fill text-primary me-2"></i>Mantención</h4>
    <small class="text-muted">Gestión de órdenes de trabajo y mantención del hotel</small>
  </div>
  <a href="<?= BASE_URL ?>/mantencion/crear" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i> Nueva Mantención
  </a>
</div>

<?php if (!empty($flashErr)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <i class="bi bi-exclamation-circle-fill me-2"></i><?= htmlspecialchars($flashErr) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (!empty($flashOk)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($flashOk) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filtros -->
<div class="stat-card mb-4">
  <form method="GET" action="<?= BASE_URL ?>/mantencion" class="row g-2 align-items-end">
    <div class="col-6 col-md-2">
      <label class="form-label fw-semibold" style="font-size:12px">Tipo</label>
      <select name="tipo" class="form-select form-select-sm">
        <option value="">Todos</option>
        <option value="correctiva"  <?= (($_GET['tipo'] ?? '') === 'correctiva')  ? 'selected' : '' ?>>Correctiva</option>
        <option value="preventiva"  <?= (($_GET['tipo'] ?? '') === 'preventiva')  ? 'selected' : '' ?>>Preventiva</option>
        <option value="emergencia"  <?= (($_GET['tipo'] ?? '') === 'emergencia')  ? 'selected' : '' ?>>Emergencia</option>
      </select>
    </div>
    <div class="col-6 col-md-2">
      <label class="form-label fw-semibold" style="font-size:12px">Estado</label>
      <select name="estado" class="form-select form-select-sm">
        <option value="">Todos</option>
        <option value="pendiente"   <?= (($_GET['estado'] ?? '') === 'pendiente')   ? 'selected' : '' ?>>Pendiente</option>
        <option value="en_proceso"  <?= (($_GET['estado'] ?? '') === 'en_proceso')  ? 'selected' : '' ?>>En Proceso</option>
        <option value="completada"  <?= (($_GET['estado'] ?? '') === 'completada')  ? 'selected' : '' ?>>Completada</option>
        <option value="cancelada"   <?= (($_GET['estado'] ?? '') === 'cancelada')   ? 'selected' : '' ?>>Cancelada</option>
      </select>
    </div>
    <div class="col-6 col-md-2">
      <label class="form-label fw-semibold" style="font-size:12px">Prioridad</label>
      <select name="prioridad" class="form-select form-select-sm">
        <option value="">Todas</option>
        <option value="urgente" <?= (($_GET['prioridad'] ?? '') === 'urgente') ? 'selected' : '' ?>>Urgente</option>
        <option value="alta"    <?= (($_GET['prioridad'] ?? '') === 'alta')    ? 'selected' : '' ?>>Alta</option>
        <option value="media"   <?= (($_GET['prioridad'] ?? '') === 'media')   ? 'selected' : '' ?>>Media</option>
        <option value="baja"    <?= (($_GET['prioridad'] ?? '') === 'baja')    ? 'selected' : '' ?>>Baja</option>
      </select>
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label fw-semibold" style="font-size:12px">Área</label>
      <select name="area_id" class="form-select form-select-sm">
        <option value="">Todas las áreas</option>
        <?php foreach ($areas as $area): ?>
          <option value="<?= $area['id'] ?>" <?= (($_GET['area_id'] ?? '') == $area['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($area['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12 col-md-3 d-flex gap-2">
      <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
        <i class="bi bi-funnel-fill me-1"></i> Filtrar
      </button>
      <a href="<?= BASE_URL ?>/mantencion" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-x-lg"></i>
      </a>
    </div>
  </form>
</div>

<!-- Tabla -->
<?php if (empty($mantenciones)): ?>
  <div class="stat-card text-center py-5">
    <i class="bi bi-wrench-adjustable-circle text-muted" style="font-size:3rem"></i>
    <p class="text-muted mt-3 mb-2">No hay órdenes de mantención registradas.</p>
    <a href="<?= BASE_URL ?>/mantencion/crear" class="btn btn-primary btn-sm">
      <i class="bi bi-plus-lg me-1"></i> Crear primera mantención
    </a>
  </div>
<?php else: ?>
<div class="stat-card p-0 overflow-hidden">
  <div class="table-responsive">
    <table class="table table-hover table-cards mb-0 align-middle">
      <thead class="table-light">
        <tr>
          <th class="ps-3">Título</th>
          <th>Tipo</th>
          <th>Ubicación</th>
          <th>Área</th>
          <th>Asignado</th>
          <th>Estado</th>
          <th>Prioridad</th>
          <th>Fecha prog.</th>
          <th class="pe-3">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($mantenciones as $m): ?>
        <tr>
          <td class="ps-3" data-label="Título">
            <span class="fw-semibold" style="font-size:14px"><?= htmlspecialchars($m['titulo']) ?></span>
            <?php if (!empty($m['ubicacion'])): ?>
              <br><small class="text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($m['ubicacion']) ?></small>
            <?php endif; ?>
          </td>
          <td data-label="Tipo">
            <?php $tb = $tipoBadge[$m['tipo']] ?? 'badge bg-secondary'; ?>
            <?php $ti = $tipoIcono[$m['tipo']] ?? 'bi-wrench'; ?>
            <span class="<?= $tb ?>">
              <i class="bi <?= $ti ?> me-1"></i><?= ucfirst($m['tipo']) ?>
            </span>
          </td>
          <td data-label="Ubicación">
            <small><?= htmlspecialchars($m['ubicacion'] ?? '—') ?></small>
          </td>
          <td data-label="Área">
            <small><?= htmlspecialchars($m['area_nombre'] ?? '—') ?></small>
          </td>
          <td data-label="Asignado">
            <small><?= htmlspecialchars($m['asignado_nombre'] ?? 'Sin asignar') ?></small>
          </td>
          <td data-label="Estado">
            <span class="badge-estado badge-<?= htmlspecialchars($m['estado']) ?>">
              <?= ucfirst(str_replace('_', ' ', $m['estado'])) ?>
            </span>
          </td>
          <td data-label="Prioridad">
            <span class="badge-estado badge-<?= htmlspecialchars($m['prioridad']) ?>">
              <?= ucfirst($m['prioridad']) ?>
            </span>
          </td>
          <td data-label="Fecha prog.">
            <small class="text-muted">
              <?= !empty($m['fecha_programada']) ? date('d/m/Y', strtotime($m['fecha_programada'])) : '—' ?>
            </small>
          </td>
          <td class="pe-3" data-label="Acciones">
            <div class="d-flex gap-1">
              <a href="<?= BASE_URL ?>/mantencion/ver/<?= $m['id'] ?>"
                 class="btn btn-sm btn-outline-primary"
                 title="Ver detalle">
                <i class="bi bi-eye"></i>
              </a>
              <?php if (!in_array($m['estado'], ['completada', 'cancelada'])): ?>
              <a href="<?= BASE_URL ?>/mantencion/editar/<?= $m['id'] ?>"
                 class="btn btn-sm btn-outline-secondary"
                 title="Editar">
                <i class="bi bi-pencil"></i>
              </a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<small class="text-muted mt-2 d-block">
  <?= count($mantenciones) ?> registro<?= count($mantenciones) !== 1 ? 's' : '' ?> encontrado<?= count($mantenciones) !== 1 ? 's' : '' ?>
</small>
<?php endif; ?>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
