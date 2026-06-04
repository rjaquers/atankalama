<?php require VIEW_PATH . '/layouts/header.php'; ?>

<?php
$userRol    = $_SESSION['user_rol']  ?? '';
$userId     = (int)($_SESSION['user_id'] ?? 0);
$esJefeAdmin = in_array($userRol, ['Administrador', 'Jefe de Área'], true);

$terminada  = in_array($tarea['estado'], ['completada', 'cancelada'], true);

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
$estadoCls   = 'badge-' . ($tarea['estado']    ?? 'pendiente');
$prioridadCls = 'badge-' . ($tarea['prioridad'] ?? 'media');
?>

<!-- ===== BREADCRUMB ===== -->
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item">
      <a href="<?= BASE_URL ?>/tareas" class="text-decoration-none">
        <i class="bi bi-clipboard-check-fill me-1"></i>Tareas
      </a>
    </li>
    <li class="breadcrumb-item active text-truncate" style="max-width:300px">
      <?= htmlspecialchars($tarea['titulo']) ?>
    </li>
  </ol>
</nav>

<!-- ===== FLASH ===== -->
<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <?= htmlspecialchars($_SESSION['flash_error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>
    <?= htmlspecialchars($_SESSION['flash_success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<div class="row g-4">
  <!-- ===========================
       COL PRINCIPAL (info)
       =========================== -->
  <div class="col-md-8">

    <!-- Info principal -->
    <div class="stat-card mb-4">
      <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
        <h4 class="fw-bold mb-0"><?= htmlspecialchars($tarea['titulo']) ?></h4>
        <div class="d-flex gap-2 flex-wrap">
          <span class="badge-estado <?= htmlspecialchars($estadoCls) ?>">
            <?= htmlspecialchars($labelEstado[$tarea['estado']] ?? $tarea['estado']) ?>
          </span>
          <span class="badge-estado <?= htmlspecialchars($prioridadCls) ?>">
            <i class="bi bi-flag-fill me-1"></i>
            <?= htmlspecialchars($labelPrioridad[$tarea['prioridad']] ?? $tarea['prioridad']) ?>
          </span>
        </div>
      </div>

      <?php if (!empty($tarea['descripcion'])): ?>
        <p class="text-secondary mb-4" style="white-space:pre-wrap"><?= htmlspecialchars($tarea['descripcion']) ?></p>
      <?php else: ?>
        <p class="text-muted fst-italic mb-4">Sin descripción.</p>
      <?php endif; ?>

      <!-- Grid de datos -->
      <div class="row g-3 border-top pt-3">
        <div class="col-6 col-md-4">
          <div class="small text-muted mb-1"><i class="bi bi-building me-1"></i>Área</div>
          <div class="fw-semibold"><?= htmlspecialchars($tarea['area_nombre'] ?? '—') ?></div>
        </div>
        <div class="col-6 col-md-4">
          <div class="small text-muted mb-1"><i class="bi bi-person me-1"></i>Asignado a</div>
          <div class="fw-semibold"><?= htmlspecialchars($tarea['asignado_nombre'] ?? 'Sin asignar') ?></div>
        </div>
        <div class="col-6 col-md-4">
          <div class="small text-muted mb-1"><i class="bi bi-person-check me-1"></i>Creado por</div>
          <div class="fw-semibold"><?= htmlspecialchars($tarea['creador_nombre'] ?? '—') ?></div>
        </div>
        <div class="col-6 col-md-4">
          <div class="small text-muted mb-1"><i class="bi bi-calendar-event me-1"></i>Fecha límite</div>
          <div class="fw-semibold">
            <?php if (!empty($tarea['fecha_limite'])): ?>
              <?php
                $vencida = $tarea['fecha_limite'] < date('Y-m-d') && !$terminada;
              ?>
              <span class="<?= $vencida ? 'text-danger' : '' ?>">
                <?= htmlspecialchars(date('d/m/Y', strtotime($tarea['fecha_limite']))) ?>
                <?php if ($vencida): ?><i class="bi bi-exclamation-circle-fill"></i><?php endif; ?>
              </span>
            <?php else: ?>
              <span class="text-muted">Sin fecha</span>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="small text-muted mb-1"><i class="bi bi-clock me-1"></i>Creada</div>
          <div class="fw-semibold">
            <?= htmlspecialchars(date('d/m/Y H:i', strtotime($tarea['created_at']))) ?>
          </div>
        </div>
        <?php if (!empty($tarea['fecha_completada'])): ?>
        <div class="col-6 col-md-4">
          <div class="small text-muted mb-1"><i class="bi bi-check-circle me-1 text-success"></i>Completada</div>
          <div class="fw-semibold text-success">
            <?= htmlspecialchars(date('d/m/Y H:i', strtotime($tarea['fecha_completada']))) ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Foto de cierre -->
      <?php if (!empty($tarea['foto_cierre'])): ?>
        <div class="border-top mt-4 pt-4">
          <h6 class="fw-bold mb-3"><i class="bi bi-camera-fill text-success me-2"></i>Foto de cierre</h6>
          <a href="<?= BASE_URL ?>/<?= htmlspecialchars($tarea['foto_cierre']) ?>" target="_blank">
            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($tarea['foto_cierre']) ?>"
                 alt="Foto de cierre"
                 class="img-fluid rounded-3 shadow-sm"
                 style="max-height:320px;object-fit:cover">
          </a>
          <?php if (!empty($tarea['nota_cierre'])): ?>
            <p class="text-muted mt-2 mb-0">
              <i class="bi bi-chat-left-text me-1"></i>
              <?= htmlspecialchars($tarea['nota_cierre']) ?>
            </p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- ===== FOTOS ADJUNTAS ===== -->
    <?php if (!empty($archivos)): ?>
    <div class="stat-card mb-4">
      <h6 class="fw-bold mb-3"><i class="bi bi-images me-2 text-primary"></i>Fotos adjuntas (<?= count($archivos) ?>)</h6>
      <div class="photo-preview">
        <?php foreach ($archivos as $arch): ?>
          <a href="<?= BASE_URL ?>/<?= htmlspecialchars($arch['ruta']) ?>" target="_blank" title="Ver imagen">
            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($arch['ruta']) ?>"
                 alt="Foto adjunta"
                 loading="lazy">
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- ===== SUBIR FOTO ADICIONAL ===== -->
    <?php if (!$terminada): ?>
    <div class="stat-card mb-4">
      <h6 class="fw-bold mb-3"><i class="bi bi-upload me-2 text-secondary"></i>Subir foto adicional</h6>
      <form method="post"
            action="<?= BASE_URL ?>/tareas/subirFoto/<?= $tarea['id'] ?>"
            enctype="multipart/form-data"
            class="d-flex gap-2 align-items-end flex-wrap">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <div class="flex-grow-1">
          <input type="file"
                 name="foto"
                 accept="image/*"
                 class="form-control form-control-sm"
                 id="inputFotoAdicional">
        </div>
        <button type="submit" class="btn btn-sm btn-outline-primary">
          <i class="bi bi-upload me-1"></i> Subir
        </button>
      </form>
      <div class="photo-preview mt-2" id="previewAdicional"></div>
    </div>
    <?php endif; ?>

    <!-- ===== COMENTARIOS ===== -->
    <div class="stat-card">
      <h6 class="fw-bold mb-3">
        <i class="bi bi-chat-left-dots me-2 text-primary"></i>
        Comentarios (<?= count($comentarios) ?>)
      </h6>

      <?php if (empty($comentarios)): ?>
        <p class="text-muted fst-italic">Aún no hay comentarios.</p>
      <?php else: ?>
        <div class="d-flex flex-column gap-3 mb-4">
          <?php foreach ($comentarios as $com): ?>
            <?php
              $inicialCom = strtoupper(mb_substr($com['autor_nombre'] ?? 'U', 0, 1, 'UTF-8'));
            ?>
            <div class="d-flex gap-3 align-items-start">
              <div class="user-avatar flex-shrink-0" style="width:36px;height:36px;font-size:14px">
                <?php if (!empty($com['autor_foto'])): ?>
                  <img src="<?= BASE_URL ?>/<?= htmlspecialchars($com['autor_foto']) ?>" alt="avatar">
                <?php else: ?>
                  <?= $inicialCom ?>
                <?php endif; ?>
              </div>
              <div class="flex-grow-1">
                <div class="d-flex align-items-baseline gap-2 mb-1">
                  <span class="fw-semibold" style="font-size:14px">
                    <?= htmlspecialchars($com['autor_nombre'] ?? 'Usuario') ?>
                  </span>
                  <span class="text-muted" style="font-size:11px">
                    <?= htmlspecialchars(date('d/m/Y H:i', strtotime($com['created_at']))) ?>
                  </span>
                </div>
                <div class="p-2 rounded-3"
                     style="background:#f1f5f9;font-size:14px;white-space:pre-wrap">
                  <?= htmlspecialchars($com['comentario']) ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Nuevo comentario -->
      <form method="post"
            action="<?= BASE_URL ?>/tareas/comentar/<?= $tarea['id'] ?>">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <div class="mb-2">
          <textarea name="comentario"
                    rows="3"
                    class="form-control"
                    placeholder="Escribe un comentario..."
                    required></textarea>
        </div>
        <div class="text-end">
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-send-fill me-1"></i> Enviar comentario
          </button>
        </div>
      </form>
    </div>

  </div><!-- /col-md-8 -->

  <!-- ===========================
       SIDEBAR (acciones)
       =========================== -->
  <div class="col-md-4">

    <?php if (!$terminada): ?>
    <div class="stat-card mb-3">
      <h6 class="fw-bold mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Acciones</h6>

      <!-- Botón completar -->
      <button type="button"
              class="btn btn-success w-100 mb-2"
              data-bs-toggle="modal"
              data-bs-target="#modalCompletar">
        <i class="bi bi-check-circle-fill me-2"></i>Completar Tarea
      </button>

      <!-- Botón cancelar (solo jefe/admin) -->
      <?php if ($esJefeAdmin): ?>
      <form method="post"
            action="<?= BASE_URL ?>/tareas/cancelar/<?= $tarea['id'] ?>"
            onsubmit="return confirm('¿Seguro que deseas cancelar esta tarea?')">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <button type="submit" class="btn btn-outline-danger w-100">
          <i class="bi bi-x-circle me-2"></i>Cancelar Tarea
        </button>
      </form>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Editar (jefe/admin y no terminada) -->
    <?php if ($esJefeAdmin && !$terminada): ?>
    <div class="stat-card mb-3">
      <a href="<?= BASE_URL ?>/tareas/editar/<?= $tarea['id'] ?>"
         class="btn btn-outline-secondary w-100">
        <i class="bi bi-pencil-fill me-2"></i>Editar Tarea
      </a>
    </div>
    <?php endif; ?>

    <!-- Resumen rápido -->
    <div class="stat-card">
      <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Resumen</h6>
      <ul class="list-unstyled mb-0 small">
        <li class="mb-2 d-flex justify-content-between">
          <span class="text-muted">Archivos</span>
          <span class="fw-semibold"><?= count($archivos) ?></span>
        </li>
        <li class="mb-2 d-flex justify-content-between">
          <span class="text-muted">Comentarios</span>
          <span class="fw-semibold"><?= count($comentarios) ?></span>
        </li>
        <li class="d-flex justify-content-between">
          <span class="text-muted">Estado</span>
          <span class="badge-estado <?= htmlspecialchars($estadoCls) ?>" style="font-size:11px">
            <?= htmlspecialchars($labelEstado[$tarea['estado']] ?? $tarea['estado']) ?>
          </span>
        </li>
      </ul>
    </div>

  </div><!-- /col-md-4 -->
</div><!-- /row -->

<!-- ======================================
     MODAL COMPLETAR TAREA
     ====================================== -->
<div class="modal fade" id="modalCompletar" tabindex="-1" aria-labelledby="modalCompletarLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post"
            action="<?= BASE_URL ?>/tareas/completar/<?= $tarea['id'] ?>"
            enctype="multipart/form-data"
            id="formCompletar">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold" id="modalCompletarLabel">
            <i class="bi bi-check-circle-fill text-success me-2"></i>Completar Tarea
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="alert alert-info py-2">
            <i class="bi bi-camera-fill me-1"></i>
            <strong>La foto de cierre es obligatoria</strong> para registrar la tarea como completada.
          </div>

          <!-- Foto de cierre -->
          <div class="mb-3">
            <label for="fotoCierre" class="form-label fw-semibold">
              Foto de cierre <span class="text-danger">*</span>
            </label>
            <input type="file"
                   id="fotoCierre"
                   name="foto_cierre"
                   accept="image/*"
                   class="form-control"
                   required>
            <div class="photo-preview mt-2" id="previewCierre"></div>
          </div>

          <!-- Nota de cierre -->
          <div class="mb-3">
            <label for="notaCierre" class="form-label fw-semibold">Nota de cierre</label>
            <textarea id="notaCierre"
                      name="nota_cierre"
                      rows="3"
                      class="form-control"
                      placeholder="Descripción de cómo se resolvió la tarea..."></textarea>
          </div>
        </div>

        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            Cancelar
          </button>
          <button type="submit" class="btn btn-success">
            <i class="bi bi-check-circle-fill me-1"></i>Confirmar cierre
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Preview foto de cierre
document.getElementById('fotoCierre')?.addEventListener('change', function () {
  const preview = document.getElementById('previewCierre');
  preview.innerHTML = '';
  if (this.files && this.files[0]) {
    const img = document.createElement('img');
    img.src = URL.createObjectURL(this.files[0]);
    preview.appendChild(img);
  }
});

// Preview foto adicional
document.getElementById('inputFotoAdicional')?.addEventListener('change', function () {
  const preview = document.getElementById('previewAdicional');
  preview.innerHTML = '';
  if (this.files && this.files[0]) {
    const img = document.createElement('img');
    img.src = URL.createObjectURL(this.files[0]);
    preview.appendChild(img);
  }
});

// Validación: asegurar que foto_cierre no esté vacía antes de enviar
document.getElementById('formCompletar')?.addEventListener('submit', function (e) {
  const foto = document.getElementById('fotoCierre');
  if (!foto || !foto.files || foto.files.length === 0) {
    e.preventDefault();
    alert('Debes adjuntar una foto de cierre para completar la tarea.');
    return false;
  }
});
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
