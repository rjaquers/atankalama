<?php
$avatar_colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4'];

function urgencia_badge(array $t): string {
    $hoy    = date('Y-m-d');
    $semana = date('Y-m-d', strtotime('+7 days'));
    $fv     = $t['fecha_vencimiento'] ? substr($t['fecha_vencimiento'], 0, 10) : null;
    if (!$fv) return '';
    $label = date('d/m', strtotime($fv));
    if ($fv < $hoy)       return "<span class='badge badge-vencida'><i class='bi bi-exclamation-circle me-1'></i>$label</span>";
    if ($fv === $hoy)     return "<span class='badge badge-hoy'><i class='bi bi-clock me-1'></i>Hoy</span>";
    if ($fv <= $semana)   return "<span class='badge badge-semana'><i class='bi bi-calendar-week me-1'></i>$label</span>";
    return "<span class='badge badge-futura'><i class='bi bi-calendar me-1'></i>$label</span>";
}
?>
<?php include VIEW_PATH . '/layouts/header.php'; ?>

<style>
.mt-page { margin-top: 64px; }
.filtro-bar { background: #1a2540; border-bottom: 1px solid #ffffff18; }
.filtro-btn { background: transparent; border: none; color: #8ba1c4; padding: .45rem 1rem;
              font-size: .82rem; border-radius: 6px; transition: all .15s; cursor: pointer; }
.filtro-btn:hover  { background: #ffffff18; color: #fff; }
.filtro-btn.active { background: #3b82f6; color: #fff; }
.filtro-btn .badge-count { background: #ffffff30; border-radius: 99px; padding: 1px 7px;
                            font-size: .7rem; margin-left: 4px; }
.filtro-btn.active .badge-count { background: #ffffff40; }

.tablero-header { border-radius: 8px 8px 0 0; padding: .55rem 1rem; font-weight: 700;
                  font-size: .85rem; color: #fff; letter-spacing: .02em; }
.tablero-block  { border-radius: 8px; overflow: hidden; border: 1px solid #dee2e6; margin-bottom: 1.5rem; }
.tarea-row      { display: flex; align-items: center; gap: .75rem; padding: .65rem 1rem;
                  background: #fff; border-bottom: 1px solid #f1f3f6;
                  cursor: pointer; transition: background .12s; }
.tarea-row:last-child { border-bottom: none; }
.tarea-row:hover { background: #f7f9fc; }
.tarea-titulo   { font-size: .875rem; font-weight: 500; color: #1e293b; flex: 1; min-width: 0;
                  white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tarea-lista    { font-size: .72rem; color: #64748b; white-space: nowrap; }
.cl-pill        { font-size: .7rem; color: #64748b; white-space: nowrap; background: #f1f5f9;
                  border-radius: 99px; padding: 2px 8px; }
.cl-pill.completo { color: #16a34a; background: #dcfce7; }

.badge-vencida { background: #fee2e2; color: #b91c1c; font-size: .7rem; border-radius: 99px; padding: 2px 8px; }
.badge-hoy     { background: #fef3c7; color: #b45309; font-size: .7rem; border-radius: 99px; padding: 2px 8px; }
.badge-semana  { background: #dbeafe; color: #1d4ed8; font-size: .7rem; border-radius: 99px; padding: 2px 8px; }
.badge-futura  { background: #f1f5f9; color: #475569; font-size: .7rem; border-radius: 99px; padding: 2px 8px; }

.empty-state   { text-align: center; padding: 4rem 1rem; color: #94a3b8; }
.empty-state i { font-size: 3rem; display: block; margin-bottom: 1rem; }
</style>

<!-- Barra de filtros -->
<div class="filtro-bar sticky-top mt-page">
  <div class="container-fluid px-3 d-flex align-items-center gap-1 flex-wrap py-1">
    <button class="filtro-btn active" data-filtro="todas">
      Todas <span class="badge-count"><?= $conteos['todas'] ?></span>
    </button>
    <?php if ($conteos['vencida'] > 0): ?>
    <button class="filtro-btn" data-filtro="vencida">
      <i class="bi bi-exclamation-circle me-1"></i>Vencidas
      <span class="badge-count"><?= $conteos['vencida'] ?></span>
    </button>
    <?php endif; ?>
    <button class="filtro-btn" data-filtro="hoy">
      Hoy <span class="badge-count"><?= $conteos['hoy'] ?></span>
    </button>
    <button class="filtro-btn" data-filtro="semana">
      Esta semana <span class="badge-count"><?= $conteos['semana'] ?></span>
    </button>
    <button class="filtro-btn" data-filtro="sin_fecha">
      Sin fecha <span class="badge-count"><?= $conteos['sin_fecha'] ?></span>
    </button>
    <button class="filtro-btn" data-filtro="futura">
      Próximas <span class="badge-count"><?= $conteos['futura'] ?></span>
    </button>
  </div>
</div>

<div class="container-fluid px-3 py-4" style="max-width:900px;margin:0 auto">

  <?php if (empty($tareas)): ?>
    <div class="empty-state">
      <i class="bi bi-person-check"></i>
      <p class="fw-semibold">No tienes tareas asignadas en ningún tablero.</p>
      <p class="small">Cuando alguien te agregue como miembro de una tarjeta, aparecerá aquí.</p>
    </div>
  <?php else: ?>

    <!-- Vista por tablero (agrupada) -->
    <div id="vista-tableros">
      <?php foreach ($por_tablero as $tablero_id => $grupo): ?>
      <div class="tablero-block" data-tablero="<?= $tablero_id ?>">
        <div class="tablero-header" style="background:<?= htmlspecialchars($grupo['fondo_color']) ?>">
          <i class="bi bi-layout-three-columns me-2"></i>
          <?= htmlspecialchars($grupo['nombre']) ?>
          <span style="font-weight:400;opacity:.75;margin-left:.5rem"><?= count($grupo['tarjetas']) ?> tarea<?= count($grupo['tarjetas']) !== 1 ? 's' : '' ?></span>
        </div>
        <?php foreach ($grupo['tarjetas'] as $t): ?>
        <?php
          $cl_total  = (int)$t['items_total'];
          $cl_ok     = (int)$t['items_ok'];
          $cl_pct    = $cl_total ? round($cl_ok / $cl_total * 100) : 0;
        ?>
        <div class="tarea-row"
             data-id="<?= $t['id'] ?>"
             data-urgencia="<?= $t['urgencia'] ?>"
             onclick="openCardModal(<?= $t['id'] ?>)">

          <!-- Indicador de color urgencia (barra izquierda) -->
          <?php
            $bar_color = match($t['urgencia']) {
              'vencida'  => '#ef4444',
              'hoy'      => '#f59e0b',
              'semana'   => '#3b82f6',
              default    => '#e2e8f0',
            };
          ?>
          <div style="width:3px;min-height:36px;border-radius:2px;background:<?= $bar_color ?>;flex-shrink:0"></div>

          <!-- Título -->
          <div class="flex-grow-1 min-w-0">
            <div class="tarea-titulo"><?= htmlspecialchars($t['titulo']) ?></div>
            <div class="tarea-lista">
              <i class="bi bi-columns-gap me-1" style="font-size:.65rem"></i><?= htmlspecialchars($t['lista_nombre']) ?>
            </div>
          </div>

          <!-- Checklist -->
          <?php if ($cl_total > 0): ?>
          <span class="cl-pill <?= $cl_pct >= 100 ? 'completo' : '' ?>">
            <i class="bi bi-check2-square me-1"></i><?= $cl_ok ?>/<?= $cl_total ?>
          </span>
          <?php endif; ?>

          <!-- Fecha -->
          <?= urgencia_badge($t) ?>

          <!-- Flecha -->
          <i class="bi bi-chevron-right text-muted" style="font-size:.7rem;flex-shrink:0"></i>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <div id="empty-filtro" class="empty-state d-none">
      <i class="bi bi-funnel"></i>
      <p class="fw-semibold">No hay tareas para este filtro.</p>
    </div>

  <?php endif; ?>
</div>

<!-- Modal de tarjeta (reutiliza el mismo modal del kanban) -->
<div class="modal fade" id="tarjetaModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content" id="tarjetaModalContent"></div>
  </div>
</div>

<?php
$js_extra = <<<JS
<script>
const BASE = <?= json_encode(BASE_URL) ?>;

document.addEventListener('DOMContentLoaded', () => {
    initTarjetaModal();

    // Filtros
    document.querySelectorAll('.filtro-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const filtro = btn.dataset.filtro;
            filtrarTareas(filtro);
        });
    });
});

function filtrarTareas(filtro) {
    let visibles = 0;
    document.querySelectorAll('.tarea-row').forEach(row => {
        const ok = filtro === 'todas' || row.dataset.urgencia === filtro;
        row.style.display = ok ? '' : 'none';
        if (ok) visibles++;
    });

    // Ocultar tablero completo si no tiene tareas visibles
    document.querySelectorAll('.tablero-block').forEach(blk => {
        const hayVisibles = [...blk.querySelectorAll('.tarea-row')].some(r => r.style.display !== 'none');
        blk.style.display = hayVisibles ? '' : 'none';
    });

    document.getElementById('empty-filtro').classList.toggle('d-none', visibles > 0);
}

// Al guardar/archivar desde el modal, refrescar la página para reflejar cambios
setModalHooks({
    onCardSaved:    () => location.reload(),
    onCardArchived: () => location.reload(),
});
</script>
JS;
?>

<?php include VIEW_PATH . '/layouts/footer.php'; ?>
