<?php require VIEW_PATH . '/layouts/header.php'; ?>

<?php if (!$tablero): ?>
  <div class="no-board-msg">
    <i class="bi bi-grid-3x3-gap-fill fs-1 d-block mb-3"></i>
    No hay tableros disponibles.
  </div>
  <?php require VIEW_PATH . '/layouts/footer.php'; return; ?>
<?php endif; ?>

<div class="kanban-board-area">

  <!-- Strip de nombre del tablero -->
  <div class="tablero-strip">
    <span class="tablero-dot" style="background:<?= $tablero['fondo_color'] ?>"></span>
    <h6><?= htmlspecialchars($tablero['nombre']) ?></h6>
    <?php if ($puede_editar): ?>
      <span class="badge bg-warning text-dark ms-1 badge-editor">Editor</span>
    <?php else: ?>
      <span class="badge bg-secondary ms-1 badge-editor">Solo lectura</span>
    <?php endif; ?>

    <!-- Botones de descarga y archivo -->
    <div class="ms-auto d-flex gap-1 align-items-center">
      <?php if ($puede_editar): ?>
      <div class="position-relative">
        <button class="btn btn-sm btn-outline-light btn-board-action" id="btn-fondo"
                title="Cambiar fondo del tablero">
          <i class="bi bi-image me-1"></i>Fondo
        </button>
        <!-- Panel de fondo -->
        <div id="panel-fondo" class="fondo-panel d-none">
          <div class="fondo-panel-title">Colores</div>
          <div class="fondo-colores">
            <?php
            $colores = [
              '#1e3a5f' => 'Azul noche',
              '#1a3a2f' => 'Verde selva',
              '#3a1520' => 'Burdeos',
              '#1a2332' => 'Carbón',
              '#2d1b4e' => 'Púrpura',
              '#2d2010' => 'Cobre',
              '#0f2d2d' => 'Teal oscuro',
              '#2d1a24' => 'Rosa oscuro',
            ];
            foreach ($colores as $hex => $nombre): ?>
              <button class="fondo-swatch"
                      data-color="<?= $hex ?>"
                      data-img=""
                      style="background:<?= $hex ?>"
                      title="<?= $nombre ?>"></button>
            <?php endforeach; ?>
          </div>
          <div class="fondo-panel-title mt-2">Fotos · Hotelería</div>
          <div class="fondo-fotos">
            <?php
            $fotos = [
              ['id' => '1571896349842-33c89424de2d', 'label' => 'Lobby de lujo',    'color' => '#1a2030'],
              ['id' => '1566073771259-369c2de3a98e', 'label' => 'Piscina exterior', 'color' => '#0d2a35'],
              ['id' => '1542314831-068cd1dbfeeb', 'label' => 'Hotel exterior',    'color' => '#1a1e2e'],
              ['id' => '1631049307264-da0ec9d70304', 'label' => 'Habitación lujo',  'color' => '#201a15'],
              ['id' => '1540555700478-4be290a304c9', 'label' => 'Spa y relax',      'color' => '#152020'],
              ['id' => '1414235077428-338989a2e8c0', 'label' => 'Restaurante',      'color' => '#1a1205'],
              ['id' => '1520250497591-112f2f40a3f4', 'label' => 'Resort playa',     'color' => '#0a1e2d'],
              ['id' => '1464822759023-fed622ff2c3b', 'label' => 'Hotel montaña',    'color' => '#101a10'],
              ['id' => '1510812431401-41d2bd2722f3', 'label' => 'Cava de vinos',    'color' => '#1a0a10'],
            ];
            foreach ($fotos as $f):
              $thumb = 'https://images.unsplash.com/photo-' . $f['id'] . '?w=200&q=70&auto=format';
              $full  = 'https://images.unsplash.com/photo-' . $f['id'] . '?w=1600&q=80&auto=format';
            ?>
              <button class="fondo-foto"
                      data-color="<?= $f['color'] ?>"
                      data-img="<?= $full ?>"
                      style="background-image:url(<?= $thumb ?>)"
                      title="<?= $f['label'] ?>"></button>
            <?php endforeach; ?>
          </div>
          <?php $img_actual = $tablero['fondo_imagen'] ?? ''; ?>
          <?php if ($img_actual): ?>
          <button id="btn-sin-foto" class="btn btn-sm btn-outline-secondary w-100 mt-2" style="font-size:.72rem">
            <i class="bi bi-x-circle me-1"></i>Quitar foto
          </button>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
      <button class="btn btn-sm btn-outline-light me-2 btn-board-action" id="btn-ver-archivadas">
        <i class="bi bi-archive me-1"></i>Archivadas
      </button>
      <a href="<?= BASE_URL ?>/reporte/excel?id=<?= (int)$tablero['id'] ?>"
         class="btn btn-sm btn-outline-success btn-board-action" title="Descargar Excel">
        <i class="bi bi-file-earmark-excel me-1"></i>Excel
      </a>
      <a href="<?= BASE_URL ?>/reporte/pdf?id=<?= (int)$tablero['id'] ?>"
         target="_blank"
         class="btn btn-sm btn-outline-danger btn-board-action" title="Ver PDF / Imprimir">
        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
      </a>
    </div>
  </div>

  <!-- Columnas -->
  <div class="kanban-scroll">

    <?php foreach ($listas as $lista): ?>
    <div class="kanban-col">

      <?php $es_basica = (bool)($lista['es_basica'] ?? false); ?>
      <div class="kanban-col-header">
        <span><?= htmlspecialchars($lista['nombre']) ?></span>
        <div class="d-flex align-items-center gap-1">
          <span class="badge-count" id="count-<?= $lista['id'] ?>"><?= count($lista['tarjetas']) ?></span>
          <?php if ($puede_editar && !$es_basica): ?>
            <button class="btn-del-lista btn-del-col"
                    data-id="<?= $lista['id'] ?>"
                    data-nombre="<?= htmlspecialchars($lista['nombre'], ENT_QUOTES) ?>"
                    title="Eliminar columna">
              &times;
            </button>
          <?php elseif ($es_basica): ?>
            <span title="Columna protegida — no se puede eliminar" class="col-protected-icon">&#128274;</span>
          <?php endif; ?>
        </div>
      </div>

      <div class="kanban-col-cards" id="cards-<?= $lista['id'] ?>" data-lista="<?= $lista['id'] ?>">

        <?php foreach ($lista['tarjetas'] as $t): ?>
        <?php $es_terminada = (bool)($t['completada'] ?? false); ?>
        <div class="kanban-card <?= $es_terminada ? 'is-completed' : '' ?>" data-id="<?= $t['id'] ?>">

          <?php if (!empty($t['etiquetas'])): ?>
          <div class="kanban-labels">
            <?php foreach ($t['etiquetas'] as $e): ?>
              <div class="kanban-label" style="background:<?= htmlspecialchars($e['color']) ?>"
                   title="<?= htmlspecialchars($e['nombre']) ?>"></div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <div class="kanban-card-num">#<?= $t['numero'] ?></div>
          <div class="kanban-card-title"><?= htmlspecialchars($t['titulo']) ?></div>

          <?php if (!empty($t['miembros_detalle'])): ?>
          <div class="kanban-card-avatars-row">
            <?php foreach ($t['miembros_detalle'] as $md): ?>
              <div class="kanban-avatar" style="background:<?= htmlspecialchars($md['color']) ?>" title="<?= htmlspecialchars($md['nombre']) ?>">
                <?= htmlspecialchars($md['iniciales']) ?>
              </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <?php
            $tiene_algo = $t['fecha_vencimiento']
                       || $t['cnt_adjuntos'] > 0 || $t['cnt_comentarios'] > 0
                       || $t['items_total'] > 0;
          ?>
          <?php if ($tiene_algo): ?>
          <div class="kanban-card-footer">
            <?php if ($t['fecha_vencimiento']): ?>
              <?php $cls = semaforo_clase($t['fecha_vencimiento']); ?>
              <span class="ic <?= $cls ?>">
                <i class="bi <?= semaforo_icono($t['fecha_vencimiento']) ?>"></i>
                <?= date('d/m', strtotime($t['fecha_vencimiento'])) ?>
              </span>
            <?php endif; ?>
            <?php if ($t['items_total'] > 0): ?>
              <?php $pct = round($t['items_ok'] / $t['items_total'] * 100); ?>
              <span class="ic">
                <i class="bi bi-check2-square"></i>
                <?= $t['items_ok'] ?>/<?= $t['items_total'] ?>
                <span class="ck-bar-wrap"><span class="ck-bar" style="width:<?= $pct ?>%"></span></span>
              </span>
            <?php endif; ?>
            <?php if ($t['cnt_adjuntos'] > 0): ?>
              <span class="ic"><i class="bi bi-paperclip"></i> <?= $t['cnt_adjuntos'] ?></span>
            <?php endif; ?>
            <?php if ($t['cnt_comentarios'] > 0): ?>
              <span class="ic ic-comm"><i class="bi bi-chat-left-text"></i> <span class="cnt"><?= $t['cnt_comentarios'] ?></span></span>
            <?php endif; ?>
          </div>
          <?php endif; ?>

        </div>
        <?php endforeach; ?>

        <?php if (empty($lista['tarjetas']) && empty($lista['referencias'])): ?>
          <div class="col-empty-hint">Sin tarjetas aún</div>
        <?php endif; ?>

        <!-- Tarjetas referenciadas desde otros tableros -->
        <?php foreach ($lista['referencias'] as $ref): ?>
        <div class="kanban-card ref-card kanban-card-ref" data-ref-id="<?= $ref['tarjeta_id'] ?>"
             style="border-left-color:<?= htmlspecialchars($ref['tablero_origen_color']) ?>">
          <div class="ref-badge">
            <i class="bi bi-arrow-left-right me-1"></i>
            <?= htmlspecialchars($ref['tablero_origen_nombre']) ?>
          </div>
          <div class="kanban-card-num">#<?= $ref['numero'] ?></div>
          <div class="kanban-card-title"><?= htmlspecialchars($ref['titulo']) ?></div>
          <?php if ($ref['fecha_vencimiento']): ?>
          <div class="kanban-card-footer">
            <span class="ic"><i class="bi bi-clock"></i> <?= date('d/m', strtotime($ref['fecha_vencimiento'])) ?></span>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>

      </div><!-- /kanban-col-cards -->

      <!-- Formulario inline para agregar tarjeta -->
      <?php if ($puede_editar): ?>
      <div class="kanban-add-wrap">
        <div class="kanban-add-form d-none" id="add-form-<?= $lista['id'] ?>">
          <textarea class="form-control form-control-sm" rows="2"
                    placeholder="Título de la tarjeta..."></textarea>
          <div class="d-flex gap-1 mt-1">
            <button class="btn btn-primary btn-sm flex-grow-1 btn-add-card"
                    data-lista="<?= $lista['id'] ?>">Agregar</button>
            <button class="btn btn-outline-secondary btn-sm btn-cancel-add"
                    data-lista="<?= $lista['id'] ?>"><i class="bi bi-x"></i></button>
          </div>
        </div>
        <button class="kanban-add-btn btn-show-add"
                id="btn-show-add-<?= $lista['id'] ?>"
                data-lista="<?= $lista['id'] ?>">
          <i class="bi bi-plus-lg me-1"></i> Agregar tarjeta
        </button>
      </div>
      <?php endif; ?>

    </div><!-- /kanban-col -->
    <?php endforeach; ?>

    <!-- Widget para agregar nueva columna (solo editores) -->
    <?php if ($puede_editar): ?>
    <div class="kanban-col" id="col-add-lista"
         style="background:rgba(255,255,255,.12);border:2px dashed rgba(255,255,255,.3);min-height:60px;justify-content:flex-start;">
      <div class="kanban-add-col-form d-none p-2" id="add-col-form">
        <input type="text" class="form-control form-control-sm mb-2"
               id="add-col-input" placeholder="Nombre de la columna…" maxlength="60"
               style="background:#fff">
        <div class="d-flex gap-1">
          <button class="btn btn-primary btn-sm flex-grow-1" id="btn-do-col">Agregar</button>
          <button class="btn btn-outline-secondary btn-sm" id="btn-cancel-col">
            <i class="bi bi-x"></i>
          </button>
        </div>
      </div>
      <button class="kanban-add-btn" id="btn-show-col"
              style="color:rgba(255,255,255,.85);margin:10px 8px;width:calc(100% - 16px)">
        <i class="bi bi-plus-lg me-1"></i> Agregar columna
      </button>
    </div>
    <?php endif; ?>

  </div><!-- /kanban-scroll -->
</div><!-- /kanban-board-area -->

<!-- Modal de tarjeta -->
<div class="modal fade" id="tarjetaModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content" id="tarjetaModalContent">
      <div class="modal-body text-center py-5">
        <div class="spinner-border text-secondary" role="status"></div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tarjetas Archivadas -->
<div class="modal fade" id="modalArchivadas" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-archive me-2"></i>Tarjetas Archivadas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0" style="max-height: 70vh; overflow-y: auto;">
        <div class="list-group list-group-flush" id="lista-archivadas">
          <!-- Se llena vía JS -->
        </div>
        <div id="msg-sin-archivadas" class="text-center py-5 d-none">
          <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
          <span class="text-muted">No hay tarjetas archivadas en este tablero.</span>
        </div>
      </div>
    </div>
  </div>
</div><!-- /modalArchivadas -->

<!-- Menú Contextual de Tarjeta -->
<div id="card-context-menu" class="context-menu d-none">
  <div class="context-menu-item" id="ctx-completar">
    <i class="bi bi-check-circle me-2"></i><span>Marcar como terminada</span>
  </div>
  <div class="context-menu-item has-submenu" id="ctx-miembros">
    <i class="bi bi-person-plus me-2"></i><span>Asignar miembro</span>
    <div class="context-submenu d-none" id="ctx-miembros-list">
      <div class="p-2 text-center text-muted small"><div class="spinner-border spinner-border-sm"></div></div>
    </div>
  </div>
  <div class="context-menu-divider"></div>
    <i class="bi bi-archive me-2 text-danger"></i><span class="text-danger">Archivar tarjeta</span>
  </div>
  <div class="context-menu-divider"></div>
  <div class="context-menu-item" id="ctx-abrir">
    <i class="bi bi-pencil-square me-2"></i>Ver detalles...
  </div>
</div>

<?php
ob_start();
?>
<script>
  const BASE         = '<?= BASE_URL ?>';
  const TABLERO_ID   = <?= (int)$tablero['id'] ?>;
  const PUEDE_EDITAR = <?= $puede_editar ? 'true' : 'false' ?>;
  window.PUEDE_EDITAR = PUEDE_EDITAR;
</script>
<script src="<?= BASE_URL ?>/assets/js/tablero.js?v=<?= APP_VERSION ?>"></script>
<?php
$js_extra = ob_get_clean();
require VIEW_PATH . '/layouts/footer.php';
?>
