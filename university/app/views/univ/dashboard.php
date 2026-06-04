<?php require VIEW_PATH . "/layouts/header.php"; ?>

<div class="row g-3 mb-4">
  <div class="col-md-8">
    <h3 class="m-0"><i class="fa-solid fa-graduation-cap"></i> Mi Universidad</h3>
    <p class="text-muted">Bienvenido a tu panel de capacitación continua.</p>
  </div>
  <div class="col-md-4 text-md-end">
    <div class="card bg-primary text-white shadow-sm">
      <div class="card-body py-2 px-3">
        <div class="small">Créditos Acumulados</div>
        <div class="h4 m-0"><?= (int)$totalCredits ?> <i class="fa-solid fa-star text-warning"></i></div>
      </div>
    </div>
  </div>
</div>

<!-- Anuncio de Novedades v6.1.0 -->
<div id="announcement-v61" class="alert alert-info border-info shadow-sm mb-4 alert-dismissible fade show" role="alert">
  <div class="d-flex align-items-center">
    <div class="me-3 fs-3">
      <i class="fa-solid fa-bullhorn animate__animated animate__tada animate__infinite"></i>
    </div>
    <div>
      <h5 class="alert-heading fw-bold mb-1">¡Nuevas mejoras disponibles! (v6.1)</h5>
      <p class="mb-0 small">
        Hemos actualizado el sistema: Ahora puedes descargar <strong>Certificados de Aprobación</strong> con vigencia de 6 meses, ver tus <strong>Mejores Puntajes</strong> directamente y revisar el <strong>Historial Detallado</strong> de tus exámenes para ver en qué fallaste.
      </p>
    </div>
  </div>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" onclick="dismissAnnouncement()"></button>
</div>

<div class="row g-3">
  <?php foreach ($enrollments as $e): ?>
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-body d-flex flex-column">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <span class="badge bg-light text-dark border small">
              <?php if ($e['tipo'] == 'obligatorio_legal'): ?>
                <i class="fa-solid fa-scale-balanced text-danger"></i> Legal
              <?php elseif ($e['tipo'] == 'obligatorio_area'): ?>
                <i class="fa-solid fa-briefcase text-warning"></i> Área
              <?php else: ?>
                <i class="fa-solid fa-lightbulb text-info"></i> Opcional
              <?php endif; ?>
            </span>
            <span class="text-muted small"><?= $e['creditos'] ?> pts</span>
          </div>
          
          <h5 class="card-title fw-bold"><?= htmlspecialchars($e['nombre']) ?></h5>
          <p class="card-text text-muted small flex-grow-1">
            <?= htmlspecialchars(substr($e['descripcion'], 0, 100)) ?>...
          </p>

          <div class="mt-3">
            <?php if ($e['status'] === 'aprobado'): ?>
              <div class="alert alert-success py-2 mb-2 d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-check-circle"></i> Aprobado</span>
                <a href="<?= BASE_URL ?>/univ/play/<?= $e['id'] ?>" class="btn btn-sm btn-outline-success">Repasar</a>
              </div>
              <a href="<?= BASE_URL ?>/univ/certificate/<?= $e['id'] ?>" class="btn btn-sm btn-outline-primary w-100 fw-bold" target="_blank">
                <i class="fa-solid fa-certificate"></i> Ver Certificado
              </a>
            <?php elseif ($e['status'] === 'bloqueado'): ?>
              <div class="alert alert-danger py-2 mb-0">
                <i class="fa-solid fa-lock"></i> Intentos agotados. Contacta a RRHH.
              </div>
            <?php else: ?>
              <div class="d-flex align-items-center justify-content-between">
                <div class="w-100 me-2">
                  <div class="progress" style="height: 10px;">
                    <?php 
                      // Obtener total de páginas para el progreso dinámico
                      $db = (new Database())->connect();
                      $stmtP = $db->prepare("SELECT COUNT(*) as total FROM univ_pages WHERE course_id = ?");
                      $stmtP->bind_param("i", $e['course_id']);
                      $stmtP->execute();
                      $tPages = $stmtP->get_result()->fetch_assoc()['total'] ?? 1;
                    ?>
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= ($e['pagina_actual'] / ($tPages ?: 1)) * 100 ?>%"></div>
                  </div>
                  <div class="small text-muted mt-1"><?= (int)$e['pagina_actual'] ?> / <?= $tPages ?> páginas</div>
                </div>
                <a href="<?= BASE_URL ?>/univ/play/<?= $e['id'] ?>" class="btn btn-primary px-3">
                  <?= $e['status'] === 'en_progreso' ? 'Continuar' : 'Iniciar' ?>
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if (empty($enrollments)): ?>
    <div class="col-12">
      <div class="card bg-light border-0 py-5 text-center">
        <i class="fa-solid fa-book-open fa-3x text-muted mb-3"></i>
        <h5>No tienes cursos asignados por el momento.</h5>
        <p class="text-muted">Consulta con tu jefatura o RRHH.</p>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
function dismissAnnouncement() {
    localStorage.setItem('announcement_v61_dismissed', 'true');
}

document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('announcement_v61_dismissed') === 'true') {
        const announcement = document.getElementById('announcement-v61');
        if (announcement) announcement.remove();
    }
});
</script>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
