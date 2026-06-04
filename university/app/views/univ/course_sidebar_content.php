<div class="mb-4">
  <a href="<?= BASE_URL ?>/univ" class="btn btn-sm btn-outline-light mb-3">
    <i class="fa-solid fa-arrow-left"></i> <span class="d-none d-lg-inline">Volver al Panel</span>
  </a>
  <h5 class="fw-bold mb-1 fs-6"><?= htmlspecialchars($enroll['nombre']) ?></h5>
  <div class="progress" style="height: 6px; background-color: #444;">
    <div class="progress-bar bg-success" style="width: <?= $totalPages > 0 ? ($pageNumber / $totalPages) * 100 : 0 ?>%"></div>
  </div>
</div>

<div class="list-group list-group-flush flex-grow-1 overflow-auto" style="max-height: 60vh;">
  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <?php 
      $hasPage = false;
      foreach($pages as $p) { if($p['orden'] == $i) $hasPage = true; }
      $isCurrent = ($i == $pageNumber);
      $isViewed = ($i <= $enroll['pagina_actual']);
    ?>
    <a href="<?= $hasPage ? BASE_URL . "/univ/play/{$enroll['id']}/$i" : '#' ?>" 
       class="list-group-item list-group-item-action bg-transparent border-0 py-2 px-2 d-flex align-items-center <?= $isCurrent ? 'active bg-primary' : ($hasPage ? 'text-white' : 'text-muted disabled') ?>">
      <div class="me-2 position-relative">
        <span class="rounded-circle border border-secondary d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.75rem;">
          <?= $i ?>
        </span>
        <?php if ($isViewed && !$isCurrent): ?>
          <i class="fa-solid fa-circle-check text-success position-absolute" style="bottom: -4px; right: -4px; font-size: 0.7rem; background: white; border-radius: 50%;"></i>
        <?php endif; ?>
      </div>
      <span class="small <?= $isCurrent ? 'fw-bold' : '' ?>"><?= $hasPage ? 'Página ' . $i : '<em>No disponible</em>' ?></span>
    </a>
  <?php endfor; ?>
</div>

<div class="mt-auto pt-3 border-top border-secondary">
  <?php if ($enroll['status'] === 'aprobado'): ?>
    <div class="alert alert-success py-2 text-center small mb-0">
      <i class="fa-solid fa-medal"></i> Aprobado
    </div>
  <?php else: ?>
    <a href="<?= BASE_URL ?>/univ/exam/<?= $enroll['id'] ?>" 
       class="btn <?= ($enroll['pagina_actual'] >= $totalPages) ? 'btn-success' : 'btn-secondary disabled' ?> w-100 fw-bold py-2 shadow-sm small"
       id="btnExamSide">
      <i class="fa-solid fa-file-pen"></i> INICIAR EXAMEN
    </a>
    <?php if ($enroll['pagina_actual'] < $totalPages): ?>
      <div class="text-center small mt-2 text-muted" style="font-size: 0.7rem;">Completa todas las páginas</div>
    <?php endif; ?>
  <?php endif; ?>
</div>
