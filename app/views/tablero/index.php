<?php
// Helpers de semáforo
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
    <i class="bi bi-kanban fs-1 d-block mb-3"></i>
    No hay tableros disponibles.
  </div>
  <?php require VIEW_PATH . '/layouts/footer.php'; return; ?>
<?php endif; ?>

<!-- ── Área del tablero ────────────────────────────────────────────────────── -->
<div class="kanban-board-area">

  <!-- Strip de nombre -->
  <div class="tablero-strip">
    <span style="width:14px;height:14px;border-radius:3px;background:<?= $tablero['fondo_color'] ?>;display:inline-block;flex-shrink:0"></span>
    <h6><?= htmlspecialchars($tablero['nombre']) ?></h6>
    <?php if ($puede_editar): ?>
      <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">Editor</span>
    <?php else: ?>
      <span class="badge bg-secondary ms-1" style="font-size:.65rem">Solo lectura</span>
    <?php endif; ?>
  </div>

  <!-- Columnas kanban -->
  <div class="kanban-scroll">

    <?php foreach ($listas as $lista): ?>
    <div class="kanban-col">

      <div class="kanban-col-header">
        <span><?= htmlspecialchars($lista['nombre']) ?></span>
        <span class="badge-count"><?= count($lista['tarjetas']) ?></span>
      </div>

      <div class="kanban-col-cards">
        <?php foreach ($lista['tarjetas'] as $t): ?>
        <div class="kanban-card">

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

          <!-- Pie de tarjeta -->
          <?php
            $tiene_algo = $t['fecha_vencimiento'] || $t['cnt_miembros'] > 0
                       || $t['cnt_adjuntos'] > 0 || $t['cnt_comentarios'] > 0
                       || $t['items_total'] > 0;
          ?>
          <?php if ($tiene_algo): ?>
          <div class="kanban-card-footer">

            <?php if ($t['fecha_vencimiento']): ?>
              <?php $cls = semaforo_clase($t['fecha_vencimiento']); ?>
              <?php $ico = semaforo_icono($t['fecha_vencimiento']); ?>
              <span class="ic <?= $cls ?>" title="Vence: <?= $t['fecha_vencimiento'] ?>">
                <i class="bi <?= $ico ?>"></i>
                <?= date('d/m', strtotime($t['fecha_vencimiento'])) ?>
              </span>
            <?php endif; ?>

            <?php if ($t['items_total'] > 0): ?>
              <?php $pct = round($t['items_ok'] / $t['items_total'] * 100); ?>
              <span class="ic" title="Checklist <?= $t['items_ok'] ?>/<?= $t['items_total'] ?>">
                <i class="bi bi-check2-square"></i>
                <?= $t['items_ok'] ?>/<?= $t['items_total'] ?>
                <span class="ck-bar-wrap"><span class="ck-bar" style="width:<?= $pct ?>%"></span></span>
              </span>
            <?php endif; ?>

            <?php if ($t['cnt_comentarios'] > 0): ?>
              <span class="ic" title="Comentarios">
                <i class="bi bi-chat-left-text"></i> <?= $t['cnt_comentarios'] ?>
              </span>
            <?php endif; ?>

            <?php if ($t['cnt_adjuntos'] > 0): ?>
              <span class="ic" title="Adjuntos">
                <i class="bi bi-paperclip"></i> <?= $t['cnt_adjuntos'] ?>
              </span>
            <?php endif; ?>

            <?php if ($t['cnt_miembros'] > 0): ?>
              <span class="ic" title="Miembros">
                <i class="bi bi-person"></i> <?= $t['cnt_miembros'] ?>
              </span>
            <?php endif; ?>

          </div>
          <?php endif; ?>

        </div>
        <?php endforeach; ?>

        <?php if (empty($lista['tarjetas'])): ?>
          <div class="text-center text-muted py-3" style="font-size:.75rem">Sin tarjetas</div>
        <?php endif; ?>
      </div><!-- /kanban-col-cards -->

    </div><!-- /kanban-col -->
    <?php endforeach; ?>

  </div><!-- /kanban-scroll -->
</div><!-- /kanban-board-area -->

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
