<?php require VIEW_PATH . '/layouts/header.php'; ?>

<?php
// Flash messages
if (!empty($_SESSION['flash_error'])):
    $flashErr = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
endif;

$rolActual   = $_SESSION['user_rol'] ?? '';
$esAdmin     = in_array($rolActual, ['Administrador', 'Jefe de Área'], true);
$terminada   = in_array($mant['estado'], ['completada', 'cancelada']);

$tipoBadge = [
    'emergencia' => 'badge bg-danger',
    'correctiva' => 'badge bg-warning text-dark',
    'preventiva' => 'badge bg-info text-dark',
];

// Agrupar archivos por momento
$fotosPorMomento = ['antes' => [], 'durante' => [], 'despues' => [], 'cierre' => []];
foreach ($archivos as $arch) {
    $mom = $arch['momento'] ?? 'durante';
    if (isset($fotosPorMomento[$mom])) {
        $fotosPorMomento[$mom][] = $arch;
    }
}
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/mantencion">Mantención</a></li>
    <li class="breadcrumb-item active text-truncate" style="max-width:220px">
      <?= htmlspecialchars($mant['titulo']) ?>
    </li>
  </ol>
</nav>

<?php if (!empty($flashErr)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <i class="bi bi-exclamation-circle-fill me-2"></i><?= htmlspecialchars($flashErr) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">

  <!-- ===================== COLUMNA PRINCIPAL ===================== -->
  <div class="col-md-8">

    <!-- Tarjeta de detalle -->
    <div class="stat-card mb-4">

      <!-- Título y badges -->
      <div class="d-flex flex-wrap align-items-start gap-2 mb-3">
        <div class="flex-grow-1">
          <h5 class="fw-bold mb-1"><?= htmlspecialchars($mant['titulo']) ?></h5>
          <div class="d-flex flex-wrap gap-2">
            <span class="<?= $tipoBadge[$mant['tipo']] ?? 'badge bg-secondary' ?>">
              <i class="bi bi-wrench me-1"></i><?= ucfirst($mant['tipo']) ?>
            </span>
            <span class="badge-estado badge-<?= htmlspecialchars($mant['estado']) ?>">
              <?= ucfirst(str_replace('_', ' ', $mant['estado'])) ?>
            </span>
            <span class="badge-estado badge-<?= htmlspecialchars($mant['prioridad']) ?>">
              <i class="bi bi-flag-fill me-1"></i><?= ucfirst($mant['prioridad']) ?>
            </span>
          </div>
        </div>
        <?php if (!$terminada): ?>
        <a href="<?= BASE_URL ?>/mantencion/editar/<?= $mant['id'] ?>" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-pencil me-1"></i>Editar
        </a>
        <?php endif; ?>
      </div>

      <!-- Ubicación destacada -->
      <?php if (!empty($mant['ubicacion'])): ?>
      <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded" style="background:#f1f5f9">
        <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
        <span class="fw-semibold"><?= htmlspecialchars($mant['ubicacion']) ?></span>
      </div>
      <?php endif; ?>

      <!-- Descripción -->
      <?php if (!empty($mant['descripcion'])): ?>
      <p class="mb-3 text-muted" style="font-size:14px;white-space:pre-wrap"><?= htmlspecialchars($mant['descripcion']) ?></p>
      <?php endif; ?>

      <!-- Grid de datos -->
      <div class="row g-3 mb-3">
        <div class="col-6 col-md-4">
          <div class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.04em">Área</div>
          <div class="fw-semibold" style="font-size:14px">
            <i class="bi bi-building text-primary me-1"></i>
            <?= htmlspecialchars($mant['area_nombre'] ?? '—') ?>
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.04em">Asignado a</div>
          <div class="fw-semibold" style="font-size:14px">
            <i class="bi bi-person-fill text-primary me-1"></i>
            <?= htmlspecialchars($mant['asignado_nombre'] ?? 'Sin asignar') ?>
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.04em">Creado por</div>
          <div class="fw-semibold" style="font-size:14px">
            <i class="bi bi-person-circle text-muted me-1"></i>
            <?= htmlspecialchars($mant['creador_nombre'] ?? '—') ?>
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.04em">Fecha programada</div>
          <div class="fw-semibold" style="font-size:14px">
            <i class="bi bi-calendar3 text-muted me-1"></i>
            <?= !empty($mant['fecha_programada']) ? date('d/m/Y', strtotime($mant['fecha_programada'])) : '—' ?>
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.04em">Fecha completada</div>
          <div class="fw-semibold" style="font-size:14px">
            <i class="bi bi-calendar-check text-muted me-1"></i>
            <?= !empty($mant['fecha_completada']) ? date('d/m/Y H:i', strtotime($mant['fecha_completada'])) : '—' ?>
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.04em">Creado</div>
          <div class="fw-semibold" style="font-size:14px">
            <i class="bi bi-clock text-muted me-1"></i>
            <?= !empty($mant['created_at']) ? date('d/m/Y', strtotime($mant['created_at'])) : '—' ?>
          </div>
        </div>

        <?php if ($esAdmin): ?>
        <div class="col-6 col-md-4">
          <div class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.04em">Costo estimado</div>
          <div class="fw-semibold" style="font-size:14px">
            <i class="bi bi-currency-dollar text-warning me-1"></i>
            <?= !empty($mant['costo_estimado']) ? '$' . number_format((float)$mant['costo_estimado'], 0, ',', '.') : '—' ?>
          </div>
        </div>
        <div class="col-6 col-md-4">
          <div class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.04em">Costo real</div>
          <div class="fw-semibold" style="font-size:14px">
            <i class="bi bi-currency-dollar text-success me-1"></i>
            <?= !empty($mant['costo_real']) ? '$' . number_format((float)$mant['costo_real'], 0, ',', '.') : '—' ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Foto de cierre + nota -->
      <?php if (!empty($mant['foto_cierre'])): ?>
      <div class="border rounded p-3" style="background:#f0fdf4">
        <div class="fw-semibold mb-2 text-success"><i class="bi bi-check-circle-fill me-2"></i>Foto de cierre</div>
        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($mant['foto_cierre']) ?>"
             alt="Foto de cierre"
             class="rounded img-fluid mb-2"
             style="max-height:280px;object-fit:cover;cursor:pointer"
             data-bs-toggle="modal"
             data-bs-target="#modalFotoGrande"
             data-src="<?= BASE_URL ?>/<?= htmlspecialchars($mant['foto_cierre']) ?>">
        <?php if (!empty($mant['nota_cierre'])): ?>
        <p class="mb-0 text-muted" style="font-size:14px;white-space:pre-wrap">
          <i class="bi bi-chat-right-text me-1"></i><?= htmlspecialchars($mant['nota_cierre']) ?>
        </p>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div><!-- /stat-card detalle -->

    <!-- Galería de fotos agrupadas por momento -->
    <?php
    $momentosLabel = [
        'antes'   => ['label' => 'Antes',    'icon' => 'bi-camera', 'color' => 'warning'],
        'durante' => ['label' => 'Durante',  'icon' => 'bi-camera-reels', 'color' => 'primary'],
        'despues' => ['label' => 'Después',  'icon' => 'bi-camera-video-fill', 'color' => 'info'],
        'cierre'  => ['label' => 'Cierre',   'icon' => 'bi-check2-circle', 'color' => 'success'],
    ];
    $totalFotos = count($archivos);
    ?>
    <?php if ($totalFotos > 0): ?>
    <div class="stat-card mb-4">
      <h6 class="fw-bold mb-3">
        <i class="bi bi-images text-primary me-2"></i>Galería de fotos
        <span class="badge bg-secondary ms-1"><?= $totalFotos ?></span>
      </h6>

      <!-- Tabs de momentos -->
      <ul class="nav nav-pills mb-3 gap-1 flex-wrap" id="fotosTabs" role="tablist">
        <?php $primerTab = true; ?>
        <?php foreach ($momentosLabel as $key => $meta): ?>
          <?php if (empty($fotosPorMomento[$key])) continue; ?>
          <li class="nav-item" role="presentation">
            <button class="nav-link <?= $primerTab ? 'active' : '' ?>"
                    id="tab-<?= $key ?>"
                    data-bs-toggle="pill"
                    data-bs-target="#panel-<?= $key ?>"
                    type="button" role="tab">
              <i class="bi <?= $meta['icon'] ?> me-1"></i><?= $meta['label'] ?>
              <span class="badge bg-<?= $meta['color'] ?> ms-1"><?= count($fotosPorMomento[$key]) ?></span>
            </button>
          </li>
          <?php $primerTab = false; ?>
        <?php endforeach; ?>
      </ul>

      <div class="tab-content" id="fotosTabsContent">
        <?php $primerPanel = true; ?>
        <?php foreach ($momentosLabel as $key => $meta): ?>
          <?php if (empty($fotosPorMomento[$key])) continue; ?>
          <div class="tab-pane fade <?= $primerPanel ? 'show active' : '' ?>"
               id="panel-<?= $key ?>" role="tabpanel">
            <div class="row g-2">
              <?php foreach ($fotosPorMomento[$key] as $foto): ?>
              <div class="col-4 col-sm-3 col-md-3">
                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($foto['ruta']) ?>"
                     alt="Foto <?= $meta['label'] ?>"
                     class="img-fluid rounded w-100"
                     style="height:100px;object-fit:cover;cursor:pointer;border:2px solid #e2e8f0"
                     data-bs-toggle="modal"
                     data-bs-target="#modalFotoGrande"
                     data-src="<?= BASE_URL ?>/<?= htmlspecialchars($foto['ruta']) ?>"
                     data-caption="<?= htmlspecialchars($foto['subido_nombre'] ?? '') ?> — <?= !empty($foto['created_at']) ? date('d/m/Y H:i', strtotime($foto['created_at'])) : '' ?>">
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php $primerPanel = false; ?>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Comentarios -->
    <div class="stat-card mb-4">
      <h6 class="fw-bold mb-3">
        <i class="bi bi-chat-left-dots-fill text-primary me-2"></i>Comentarios
        <span class="badge bg-secondary ms-1"><?= count($comentarios) ?></span>
      </h6>

      <?php if (empty($comentarios)): ?>
        <p class="text-muted mb-3" style="font-size:14px">Aún no hay comentarios en esta mantención.</p>
      <?php else: ?>
        <div class="d-flex flex-column gap-3 mb-4">
          <?php foreach ($comentarios as $com): ?>
          <div class="d-flex gap-3">
            <div class="user-avatar flex-shrink-0" style="width:36px;height:36px;font-size:13px">
              <?php if (!empty($com['autor_foto'])): ?>
                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($com['autor_foto']) ?>" alt="foto">
              <?php else: ?>
                <?= strtoupper(mb_substr($com['autor_nombre'] ?? 'U', 0, 1, 'UTF-8')) ?>
              <?php endif; ?>
            </div>
            <div class="flex-grow-1">
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="fw-semibold" style="font-size:13px"><?= htmlspecialchars($com['autor_nombre'] ?? '—') ?></span>
                <small class="text-muted"><?= !empty($com['created_at']) ? date('d/m/Y H:i', strtotime($com['created_at'])) : '' ?></small>
              </div>
              <div class="p-2 rounded" style="background:#f8fafc;font-size:14px;white-space:pre-wrap">
                <?= htmlspecialchars($com['comentario']) ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Form nuevo comentario -->
      <form method="POST" action="<?= BASE_URL ?>/mantencion/comentar/<?= $mant['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div class="d-flex gap-2">
          <textarea name="comentario"
                    class="form-control"
                    rows="2"
                    placeholder="Escribe un comentario..."
                    style="resize:none;font-size:14px"
                    required></textarea>
          <button type="submit" class="btn btn-primary align-self-end px-3">
            <i class="bi bi-send-fill"></i>
          </button>
        </div>
      </form>
    </div>

  </div><!-- /col-md-8 -->

  <!-- ===================== COLUMNA LATERAL ===================== -->
  <div class="col-md-4">

    <!-- Acciones -->
    <?php if (!$terminada): ?>
    <div class="stat-card mb-4">
      <h6 class="fw-bold mb-3"><i class="bi bi-lightning-fill text-warning me-2"></i>Acciones</h6>
      <div class="d-flex flex-column gap-2">

        <!-- Btn Completar -->
        <button class="btn btn-success"
                data-bs-toggle="modal"
                data-bs-target="#modalCompletar">
          <i class="bi bi-check2-circle me-2"></i>Completar mantención
        </button>

        <!-- Btn Cancelar (solo admin/jefe) -->
        <?php if ($esAdmin): ?>
        <form method="POST" action="<?= BASE_URL ?>/mantencion/cancelar/<?= $mant['id'] ?>"
              onsubmit="return confirm('¿Seguro que deseas cancelar esta mantención?')">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <button type="submit" class="btn btn-outline-danger w-100">
            <i class="bi bi-x-circle me-2"></i>Cancelar mantención
          </button>
        </form>
        <?php endif; ?>

      </div>
    </div>

    <!-- Subir foto -->
    <div class="stat-card mb-4">
      <h6 class="fw-bold mb-3"><i class="bi bi-camera-fill text-primary me-2"></i>Subir foto</h6>
      <form method="POST"
            action="<?= BASE_URL ?>/mantencion/subirFoto/<?= $mant['id'] ?>"
            enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div class="mb-2">
          <label class="form-label fw-semibold" style="font-size:12px">Momento</label>
          <select name="momento" class="form-select form-select-sm">
            <option value="antes">Antes</option>
            <option value="durante" selected>Durante</option>
            <option value="despues">Después</option>
          </select>
        </div>
        <div class="mb-3">
          <input type="file"
                 name="foto"
                 class="form-control form-control-sm"
                 accept="image/*"
                 id="inputFotoPanel"
                 required>
          <div class="photo-preview" id="previewFotoPanel"></div>
        </div>
        <button type="submit" class="btn btn-primary btn-sm w-100">
          <i class="bi bi-upload me-1"></i>Subir foto
        </button>
      </form>
    </div>
    <?php endif; ?>

    <!-- Info de estado -->
    <div class="stat-card mb-4">
      <h6 class="fw-bold mb-3"><i class="bi bi-info-circle-fill text-info me-2"></i>Resumen</h6>
      <div class="d-flex flex-column gap-2" style="font-size:13px">
        <div class="d-flex justify-content-between">
          <span class="text-muted">Estado</span>
          <span class="badge-estado badge-<?= htmlspecialchars($mant['estado']) ?>">
            <?= ucfirst(str_replace('_', ' ', $mant['estado'])) ?>
          </span>
        </div>
        <div class="d-flex justify-content-between">
          <span class="text-muted">Tipo</span>
          <span class="fw-semibold"><?= ucfirst($mant['tipo']) ?></span>
        </div>
        <div class="d-flex justify-content-between">
          <span class="text-muted">Prioridad</span>
          <span class="badge-estado badge-<?= htmlspecialchars($mant['prioridad']) ?>"><?= ucfirst($mant['prioridad']) ?></span>
        </div>
        <div class="d-flex justify-content-between">
          <span class="text-muted">Fotos</span>
          <span class="fw-semibold"><?= count($archivos) ?></span>
        </div>
        <div class="d-flex justify-content-between">
          <span class="text-muted">Comentarios</span>
          <span class="fw-semibold"><?= count($comentarios) ?></span>
        </div>
      </div>
    </div>

  </div><!-- /col-md-4 -->
</div><!-- /row -->

<!-- ============= MODAL: Completar ============= -->
<div class="modal fade" id="modalCompletar" tabindex="-1" aria-labelledby="modalCompletarLabel">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST"
            action="<?= BASE_URL ?>/mantencion/completar/<?= $mant['id'] ?>"
            enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="modalCompletarLabel">
            <i class="bi bi-check2-circle text-success me-2"></i>Completar mantención
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="alert alert-info" style="font-size:13px">
            <i class="bi bi-info-circle-fill me-2"></i>
            La foto de cierre es <strong>obligatoria</strong> para completar la mantención.
          </div>

          <!-- Foto cierre -->
          <div class="mb-3">
            <label class="form-label fw-semibold">
              Foto de cierre <span class="text-danger">*</span>
            </label>
            <input type="file"
                   name="foto_cierre"
                   class="form-control"
                   accept="image/*"
                   id="inputFotoCierre"
                   required>
            <div class="photo-preview" id="previewFotoCierre"></div>
          </div>

          <!-- Nota cierre -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Nota de cierre</label>
            <textarea name="nota_cierre"
                      class="form-control"
                      rows="3"
                      placeholder="Describe el trabajo realizado, materiales usados, observaciones..."></textarea>
          </div>

          <!-- Costo real (solo admin/jefe) -->
          <?php if ($esAdmin): ?>
          <div class="mb-3">
            <label class="form-label fw-semibold">
              <i class="bi bi-currency-dollar text-success me-1"></i>Costo real ($)
            </label>
            <input type="number"
                   name="costo_real"
                   class="form-control"
                   step="1"
                   min="0"
                   placeholder="0"
                   value="<?= htmlspecialchars($mant['costo_estimado'] ?? '') ?>">
          </div>
          <?php endif; ?>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            Cancelar
          </button>
          <button type="submit" class="btn btn-success">
            <i class="bi bi-check2-circle me-1"></i>Confirmar cierre
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============= MODAL: Foto grande ============= -->
<div class="modal fade" id="modalFotoGrande" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-dark border-0">
      <div class="modal-header border-0 pb-0">
        <small class="text-white-50" id="modalFotoCaption"></small>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center p-2">
        <img id="modalFotoGrandeImg" src="" alt="Foto" class="img-fluid rounded" style="max-height:80vh">
      </div>
    </div>
  </div>
</div>

<script>
// Preview foto de cierre
document.getElementById('inputFotoCierre').addEventListener('change', function () {
  var preview = document.getElementById('previewFotoCierre');
  preview.innerHTML = '';
  Array.from(this.files).forEach(function (file) {
    var reader = new FileReader();
    reader.onload = function (e) {
      var img = document.createElement('img');
      img.src = e.target.result;
      preview.appendChild(img);
    };
    reader.readAsDataURL(file);
  });
});

// Preview foto panel lateral
var inputFotoPanel = document.getElementById('inputFotoPanel');
if (inputFotoPanel) {
  inputFotoPanel.addEventListener('change', function () {
    var preview = document.getElementById('previewFotoPanel');
    preview.innerHTML = '';
    Array.from(this.files).forEach(function (file) {
      var reader = new FileReader();
      reader.onload = function (e) {
        var img = document.createElement('img');
        img.src = e.target.result;
        preview.appendChild(img);
      };
      reader.readAsDataURL(file);
    });
  });
}

// Modal foto grande
var modalFotoGrande = document.getElementById('modalFotoGrande');
if (modalFotoGrande) {
  modalFotoGrande.addEventListener('show.bs.modal', function (e) {
    var trigger = e.relatedTarget;
    var src     = trigger ? trigger.getAttribute('data-src') : '';
    var caption = trigger ? (trigger.getAttribute('data-caption') || '') : '';
    document.getElementById('modalFotoGrandeImg').src    = src;
    document.getElementById('modalFotoCaption').textContent = caption;
  });
}
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
