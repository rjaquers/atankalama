<?php
function semaforo_clase(string $fecha): string {
    $diff = (strtotime($fecha) - time()) / 86400;
    if ($diff < 0)  return 'fecha-gris';
    if ($diff < 1)  return 'fecha-rojo';
    if ($diff <= 3) return 'fecha-amarillo';
    return 'fecha-verde';
}
function semaforo_icono(string $fecha): string {
    $diff = (strtotime($fecha) - time()) / 86400;
    if ($diff < 0)  return 'bi-clock-history';
    if ($diff < 1)  return 'bi-exclamation-circle-fill';
    if ($diff <= 3) return 'bi-clock-fill';
    return 'bi-clock';
}

require VIEW_PATH . '/layouts/header.php';
?>

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
      <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">Editor</span>
    <?php else: ?>
      <span class="badge bg-secondary ms-1" style="font-size:.65rem">Solo lectura</span>
    <?php endif; ?>

    <!-- Botones de descarga y archivo -->
    <div class="ms-auto d-flex gap-1 align-items-center">
      <?php if ($puede_editar): ?>
      <div class="position-relative">
        <button class="btn btn-sm btn-outline-light" id="btn-fondo"
                style="font-size:.75rem; border-color: rgba(255,255,255,0.3)"
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
          <button id="btn-sin-foto" class="btn btn-sm btn-outline-secondary w-100 mt-2"
                  style="font-size:.72rem">
            <i class="bi bi-x-circle me-1"></i>Quitar foto
          </button>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
      <button class="btn btn-sm btn-outline-light me-2" id="btn-ver-archivadas"
              style="font-size:.75rem; border-color: rgba(255,255,255,0.3)">
        <i class="bi bi-archive me-1"></i>Archivadas
      </button>
      <a href="<?= BASE_URL ?>/reporte/excel?id=<?= (int)$tablero['id'] ?>"
         class="btn btn-sm btn-outline-success" title="Descargar Excel"
         style="font-size:.75rem">
        <i class="bi bi-file-earmark-excel me-1"></i>Excel
      </a>
      <a href="<?= BASE_URL ?>/reporte/pdf?id=<?= (int)$tablero['id'] ?>"
         target="_blank"
         class="btn btn-sm btn-outline-danger" title="Ver PDF / Imprimir"
         style="font-size:.75rem">
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
            <button class="btn-del-lista"
                    data-id="<?= $lista['id'] ?>"
                    data-nombre="<?= htmlspecialchars($lista['nombre'], ENT_QUOTES) ?>"
                    title="Eliminar columna"
                    style="background:none;border:none;color:#94a3b8;cursor:pointer;padding:0 3px;font-size:1.1rem;line-height:1;display:flex;align-items:center">
              &times;
            </button>
          <?php elseif ($es_basica): ?>
            <span title="Columna protegida — no se puede eliminar"
                  style="font-size:.65rem;color:#94a3b8;cursor:default">&#128274;</span>
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

          <?php
            $tiene_algo = $t['fecha_vencimiento'] || $t['cnt_miembros'] > 0
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
            <?php if ($t['cnt_miembros'] > 0): ?>
              <span class="ic ic-miembros"><i class="bi bi-person"></i> <?= $t['cnt_miembros'] ?></span>
              <div class="kanban-card-avatars ms-auto">
                <?php foreach (($t['miembros_detalle'] ?? []) as $md): ?>
                  <div class="kanban-avatar-sm" style="background:<?= $md['color'] ?>" title="<?= htmlspecialchars($md['nombre']) ?>">
                    <?= $md['iniciales'] ?>
                  </div>
                <?php endforeach; ?>
              </div>
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
        <div class="kanban-card ref-card" data-ref-id="<?= $ref['tarjeta_id'] ?>"
             style="border-left:4px solid <?= htmlspecialchars($ref['tablero_origen_color']) ?>">
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
</div>

<script>
const BASE         = '<?= BASE_URL ?>';
const TABLERO_ID   = <?= (int)$tablero['id'] ?>;
const PUEDE_EDITAR = <?= $puede_editar ? 'true' : 'false' ?>;

document.addEventListener('DOMContentLoaded', () => {
  initTarjetaModal();
  const modalArchivadas = new bootstrap.Modal(document.getElementById('modalArchivadas'));

  setModalHooks({
    onLabelsChanged:    (tid) => refreshKanbanLabels(tid),
    onMiembrosChanged:  (tid) => refreshKanbanMiembros(tid),
    onAdjuntosChanged:  (tid) => refreshKanbanAdjuntos(tid),
    onChecklistChanged: (tid) => refreshKanbanChecklist(tid),
    onCardSaved: (tid, titulo, fecha, completada) => {
      const card = document.querySelector(`.kanban-card[data-id="${tid}"]`);
      if (card) {
        card.querySelector('.kanban-card-title').textContent = titulo;
        card.classList.toggle('is-completed', !!completada);
      }
      hideCardModal();
    },
    onCardArchived: (tid) => {
      const card = document.querySelector(`.kanban-card[data-id="${tid}"]`);
      if (card) {
        const lid = card.closest('.kanban-col-cards').dataset.lista;
        card.remove();
        actualizarContador(lid);
        toggleEmptyHint(lid);
      }
      hideCardModal();
    },
  });

  document.querySelectorAll('.kanban-card:not(.ref-card)').forEach(bindCardClick);

  document.querySelectorAll('.ref-card').forEach(card => {
    card.addEventListener('click', () => openCardModal(card.dataset.refId));
  });

  if (PUEDE_EDITAR) {
    document.querySelectorAll('.kanban-col-cards').forEach(col => {
      Sortable.create(col, {
        group: 'kanban', animation: 150,
        ghostClass: 'card-ghost', chosenClass: 'card-chosen',
        onEnd: handleDragEnd,
      });
    });
  }

  // Lógica de tarjetas archivadas
  document.getElementById('btn-ver-archivadas')?.addEventListener('click', async () => {
    const res = await fetch(BASE + '/tablero/archivadas?id=' + TABLERO_ID).then(r => r.json());
    const lista = document.getElementById('lista-archivadas');
    const msg   = document.getElementById('msg-sin-archivadas');
    
    lista.innerHTML = '';
    if (res.ok && res.archivadas.length > 0) {
      msg.classList.add('d-none');
      res.archivadas.forEach(t => {
        const item = document.createElement('div');
        item.className = 'list-group-item d-flex align-items-center justify-content-between py-3';
        item.innerHTML = `
          <div>
            <div class="fw-bold mb-0">#${t.numero} - ${escHtml(t.titulo)}</div>
            <small class="text-muted">Lista original: ${escHtml(t.lista_nombre)}</small>
          </div>
          <button class="btn btn-sm btn-outline-primary btn-restaurar" data-id="${t.id}">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Restaurar
          </button>
        `;
        lista.appendChild(item);
      });
      
      // Evento restaurar
      lista.querySelectorAll('.btn-restaurar').forEach(btn => {
        btn.addEventListener('click', async () => {
          const tid = btn.dataset.id;
          btn.disabled = true;
          const r = await fetchJSON(BASE + '/tarjeta/desarchivar', { id: tid });
          if (r.ok) {
            location.reload(); 
          } else {
            alert('Error al restaurar: ' + (r.error || 'Desconocido'));
            btn.disabled = false;
          }
        });
      });
    } else {
      msg.classList.remove('d-none');
    }
    modalArchivadas.show();
  });
});

/* ── Agregar tarjeta ─────────────────────────────────── */

document.querySelectorAll('.btn-show-add').forEach(btn => {
  btn.addEventListener('click', () => {
    const lid = btn.dataset.lista;
    document.getElementById('add-form-' + lid).classList.remove('d-none');
    btn.classList.add('d-none');
    document.querySelector('#add-form-' + lid + ' textarea').focus();
  });
});

document.querySelectorAll('.btn-cancel-add').forEach(btn => {
  btn.addEventListener('click', () => closeAddForm(btn.dataset.lista));
});

document.querySelectorAll('.btn-add-card').forEach(btn => {
  btn.addEventListener('click', async () => {
    const lid    = btn.dataset.lista;
    const ta     = document.querySelector('#add-form-' + lid + ' textarea');
    const titulo = ta.value.trim();
    if (!titulo) { ta.focus(); return; }
    btn.disabled = true;
    try {
      const res = await fetchJSON(BASE + '/tarjeta/crear', { lista_id: parseInt(lid), tablero_id: TABLERO_ID, titulo });
      btn.disabled = false;
      if (res.ok) {
        const col = document.getElementById('cards-' + lid);
        col.querySelector('.col-empty-hint')?.remove();
        const div = buildCardEl(res.tarjeta);
        col.appendChild(div);
        bindCardClick(div);
        actualizarContador(lid);
        closeAddForm(lid);
      } else {
        alert('Error: ' + (res.error || 'No se pudo crear la tarjeta') + 
              ' (Status: ' + (res.status || 'unknown') + ')\n\nDetalle: ' + (res.details || 'Sin detalles'));
      }
    } catch (e) {
      btn.disabled = false;
      alert('Error de red o del servidor: ' + e.message);
      console.error(e);
    }
  });
});

function closeAddForm(lid) {
  document.getElementById('add-form-' + lid).classList.add('d-none');
  document.querySelector('#add-form-' + lid + ' textarea').value = '';
  document.getElementById('btn-show-add-' + lid).classList.remove('d-none');
}

function bindCardClick(card) {
  card.addEventListener('click', () => openCardModal(card.dataset.id));
}

/* ── Drag & drop ─────────────────────────────────────── */

async function handleDragEnd(evt) {
  const card      = evt.item;
  const tarjetaId = parseInt(card.dataset.id);
  const lidDest   = evt.to.dataset.lista;
  const lidOrig   = evt.from.dataset.lista;
  const cards     = [...evt.to.querySelectorAll('.kanban-card')];
  const idx       = cards.indexOf(card);
  const prevId    = idx > 0 ? parseInt(cards[idx - 1].dataset.id) : null;
  const nextId    = idx < cards.length - 1 ? parseInt(cards[idx + 1].dataset.id) : null;

  const res = await fetchJSON(BASE + '/tarjeta/mover', {
    id: tarjetaId, lista_id: parseInt(lidDest), prev_id: prevId, next_id: nextId,
  });
  if (!res.ok) {
    alert(res.error || 'Error al mover tarjeta');
    evt.from.insertBefore(card, evt.from.children[evt.oldIndex] || null);
  } else if (lidOrig !== lidDest) {
    actualizarContador(lidOrig);
    actualizarContador(lidDest);
    toggleEmptyHint(lidOrig);
    toggleEmptyHint(lidDest);
  }
}

/* ── Refresh kanban tras cambios en modal ─────────────── */

function refreshKanbanLabels(tarjetaId) {
  const card = document.querySelector(`.kanban-card[data-id="${tarjetaId}"]`);
  if (!card) return;
  const activeBtns = [...document.querySelectorAll('.btn-etq.etq-on')];
  let wrap = card.querySelector('.kanban-labels');
  if (activeBtns.length === 0) { if (wrap) wrap.remove(); return; }
  if (!wrap) {
    wrap = document.createElement('div');
    wrap.className = 'kanban-labels';
    card.insertBefore(wrap, card.firstChild);
  }
  wrap.innerHTML = activeBtns.map(b =>
    `<div class="kanban-label" style="background:${b.dataset.color}" title="${escHtml(b.dataset.nombre)}"></div>`
  ).join('');
}

function refreshKanbanMiembros(tarjetaId) {
  const card = document.querySelector(`.kanban-card[data-id="${tarjetaId}"]`);
  if (!card) return;
  const footer = card.querySelector('.kanban-card-footer');
  const cnt    = document.querySelectorAll('.btn-avatar.avatar-on').length;
  if (!footer) return;
  let ic = footer.querySelector('.ic-miembros');
  if (cnt > 0) {
    if (!ic) { ic = document.createElement('span'); ic.className = 'ic ic-miembros'; footer.appendChild(ic); }
    ic.innerHTML = `<i class="bi bi-person"></i> ${cnt}`;
  } else if (ic) { ic.remove(); }
}

function refreshKanbanAdjuntos(tarjetaId) {
  const card = document.querySelector(`.kanban-card[data-id="${tarjetaId}"]`);
  if (!card) return;
  const cnt    = document.querySelectorAll('.adjunto-item').length;
  const footer = card.querySelector('.kanban-card-footer');
  if (!footer) return;
  let ic = footer.querySelector('.ic-adj');
  if (cnt > 0) {
    if (!ic) { ic = document.createElement('span'); ic.className = 'ic ic-adj'; footer.appendChild(ic); }
    ic.innerHTML = `<i class="bi bi-paperclip"></i> ${cnt}`;
  } else if (ic) { ic.remove(); }
}

function refreshKanbanChecklist(tarjetaId) {
  const card = document.querySelector(`.kanban-card[data-id="${tarjetaId}"]`);
  if (!card) return;
  let total = 0, ok = 0;
  document.querySelectorAll('[id^="cl-items-"]').forEach(ul => {
    total += parseInt(ul.dataset.total) || 0;
    ok    += parseInt(ul.dataset.ok)    || 0;
  });
  const footer = card.querySelector('.kanban-card-footer');
  if (!footer) return;
  let ic = footer.querySelector('.ic-ck');
  if (total > 0) {
    if (!ic) { ic = document.createElement('span'); ic.className = 'ic ic-ck'; footer.insertBefore(ic, footer.firstChild); }
    const pct = Math.round(ok / total * 100);
    ic.innerHTML = `<i class="bi bi-check2-square"></i> ${ok}/${total}
      <span class="ck-bar-wrap"><span class="ck-bar" style="width:${pct}%"></span></span>`;
  } else if (ic) { ic.remove(); }
}

/* ── Helpers kanban ───────────────────────────────────── */

function toggleEmptyHint(lid) {
  const col      = document.getElementById('cards-' + lid);
  const hint     = col.querySelector('.col-empty-hint');
  const hasCards = col.querySelectorAll('.kanban-card').length > 0;
  if (hasCards && hint) hint.remove();
  if (!hasCards && !hint) {
    const d = document.createElement('div');
    d.className = 'col-empty-hint';
    d.textContent = 'Sin tarjetas aún';
    col.appendChild(d);
  }
}

function actualizarContador(lid) {
  const n = document.getElementById('cards-' + lid).querySelectorAll('.kanban-card').length;
  document.getElementById('count-' + lid).textContent = n;
}

function buildCardEl(t) {
  const div = document.createElement('div');
  div.className = 'kanban-card';
  div.dataset.id = t.id;
  div.innerHTML = `<div class="kanban-card-num">#${t.numero}</div>
                   <div class="kanban-card-title">${escHtml(t.titulo)}</div>`;
  return div;
}

/* ── Gestión de columnas (listas) ─────────────────────────── */

// Eliminar columna (botones ya en el DOM)
document.querySelectorAll('.btn-del-lista').forEach(bindDelLista);

function bindDelLista(btn) {
  btn.addEventListener('click', async (e) => {
    e.stopPropagation();
    const listaId  = parseInt(btn.dataset.id);
    const nombre   = btn.dataset.nombre;
    if (!confirm(`¿Eliminar la columna "${nombre}"?\n\nSolo puede eliminarse si está vacía.`)) return;
    const res = await fetchJSON(BASE + '/lista/eliminar', { lista_id: listaId });
    if (!res.ok) { alert(res.error || 'No se pudo eliminar la columna'); return; }
    btn.closest('.kanban-col').remove();
  });
}

// Mostrar/ocultar formulario de nueva columna
document.getElementById('btn-show-col')?.addEventListener('click', () => {
  document.getElementById('add-col-form').classList.remove('d-none');
  document.getElementById('btn-show-col').classList.add('d-none');
  document.getElementById('add-col-input').focus();
});
document.getElementById('btn-cancel-col')?.addEventListener('click', cerrarFormCol);
document.getElementById('add-col-input')?.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') document.getElementById('btn-do-col')?.click();
  if (e.key === 'Escape') cerrarFormCol();
});

function cerrarFormCol() {
  document.getElementById('add-col-form').classList.add('d-none');
  document.getElementById('add-col-input').value = '';
  document.getElementById('btn-show-col').classList.remove('d-none');
}

document.getElementById('btn-do-col')?.addEventListener('click', async () => {
  const input  = document.getElementById('add-col-input');
  const nombre = input.value.trim();
  if (!nombre) { input.focus(); return; }
  const btn = document.getElementById('btn-do-col');
  btn.disabled = true;
  const res = await fetchJSON(BASE + '/lista/crear', { tablero_id: TABLERO_ID, nombre });
  btn.disabled = false;
  if (!res.ok) { alert(res.error || 'Error al crear columna'); return; }

  const newCol = buildColEl(res.lista);
  document.getElementById('col-add-lista').insertAdjacentElement('beforebegin', newCol);

  if (PUEDE_EDITAR) {
    Sortable.create(newCol.querySelector('.kanban-col-cards'), {
      group: 'kanban', animation: 150,
      ghostClass: 'card-ghost', chosenClass: 'card-chosen',
      onEnd: handleDragEnd,
    });
  }
  cerrarFormCol();
});

function buildColEl(lista) {
  const col = document.createElement('div');
  col.className = 'kanban-col';
  col.id = 'col-' + lista.id;
  col.innerHTML = `
    <div class="kanban-col-header">
      <span>${escHtml(lista.nombre)}</span>
      <div class="d-flex align-items-center gap-1">
        <span class="badge-count" id="count-${lista.id}">0</span>
        <button class="btn-del-lista"
                data-id="${lista.id}"
                data-nombre="${escHtml(lista.nombre)}"
                title="Eliminar columna"
                style="background:none;border:none;color:#94a3b8;cursor:pointer;padding:0 3px;font-size:1.1rem;line-height:1;display:flex;align-items:center">
          &times;
        </button>
      </div>
    </div>
    <div class="kanban-col-cards" id="cards-${lista.id}" data-lista="${lista.id}">
      <div class="col-empty-hint">Sin tarjetas aún</div>
    </div>
    <div class="kanban-add-wrap">
      <div class="kanban-add-form d-none" id="add-form-${lista.id}">
        <textarea class="form-control form-control-sm" rows="2"
                  placeholder="Título de la tarjeta..."></textarea>
        <div class="d-flex gap-1 mt-1">
          <button class="btn btn-primary btn-sm flex-grow-1 btn-add-card"
                  data-lista="${lista.id}">Agregar</button>
          <button class="btn btn-outline-secondary btn-sm btn-cancel-add"
                  data-lista="${lista.id}"><i class="bi bi-x"></i></button>
        </div>
      </div>
      <button class="kanban-add-btn btn-show-add"
              id="btn-show-add-${lista.id}"
              data-lista="${lista.id}">
        <i class="bi bi-plus-lg me-1"></i> Agregar tarjeta
      </button>
    </div>`;

  // Bind botón eliminar la nueva columna
  bindDelLista(col.querySelector('.btn-del-lista'));

  // Bind "agregar tarjeta" en nueva columna
  const lid = lista.id;
  col.querySelector('.btn-show-add').addEventListener('click', () => {
    document.getElementById('add-form-' + lid).classList.remove('d-none');
    col.querySelector('.btn-show-add').classList.add('d-none');
    col.querySelector('#add-form-' + lid + ' textarea').focus();
  });
  col.querySelector('.btn-cancel-add').addEventListener('click', () => closeAddForm(lid));
  col.querySelector('.btn-add-card').addEventListener('click', async () => {
    const ta     = col.querySelector('#add-form-' + lid + ' textarea');
    const titulo = ta.value.trim();
    if (!titulo) { ta.focus(); return; }
    const addBtn = col.querySelector('.btn-add-card');
    addBtn.disabled = true;
    const res = await fetchJSON(BASE + '/tarjeta/crear', {
      lista_id: lid, tablero_id: TABLERO_ID, titulo,
    });
    addBtn.disabled = false;
    if (res.ok) {
      const cards = document.getElementById('cards-' + lid);
      cards.querySelector('.col-empty-hint')?.remove();
      const div = buildCardEl(res.tarjeta);
      cards.appendChild(div);
      bindCardClick(div);
      actualizarContador(lid);
      closeAddForm(lid);
    } else {
      alert('Error: ' + (res.error || 'No se pudo crear la tarjeta'));
    }
  });

  return col;
}

function escHtml(s) {
  const d = document.createElement('div');
  d.textContent = s;
  return d.innerHTML;
}

/* ── Panel de fondo ───────────────────────────────────── */

(function () {
  const btn   = document.getElementById('btn-fondo');
  const panel = document.getElementById('panel-fondo');
  if (!btn || !panel) return;

  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    panel.classList.toggle('d-none');
  });

  document.addEventListener('click', (e) => {
    if (!panel.contains(e.target) && e.target !== btn) {
      panel.classList.add('d-none');
    }
  });

  async function aplicarFondo(color, img) {
    const res = await fetchJSON(BASE + '/tablero/fondo', {
      id: TABLERO_ID, fondo_color: color, fondo_imagen: img,
    });
    if (!res.ok) return;

    document.body.style.background = img
      ? `url(${img}) center/cover no-repeat fixed ${color}`
      : color;

    document.querySelector('.kanban-navbar').style.background = color + 'dd';

    const dot = document.querySelector('.tablero-dot');
    if (dot) dot.style.background = color;

    panel.classList.add('d-none');
  }

  panel.querySelectorAll('.fondo-swatch, .fondo-foto').forEach(el => {
    el.addEventListener('click', () => aplicarFondo(el.dataset.color, el.dataset.img));
  });

  document.getElementById('btn-sin-foto')?.addEventListener('click', () => {
    const color = '<?= htmlspecialchars($tablero['fondo_color']) ?>';
    aplicarFondo(color, '');
  });
})();
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
