<?php require VIEW_PATH . "/layouts/header.php"; ?>

<style>
/* Ajustes para el reproductor de cursos */
.course-player-container {
    margin: -1.5rem;
    height: calc(100vh - 56px); /* 56px es aprox la altura del navbar */
    overflow: hidden;
}

@media (max-width: 767.98px) {
    .course-player-container {
        height: auto;
        overflow: visible;
        margin: -1rem;
    }
    .sidebar-desktop {
        display: none !important;
    }
}

@media (min-width: 768px) {
    .mobile-nav-bar {
        display: none !important;
    }
}

/* Estilo específico para contenido inyectado */
.course-content img { max-width: 100%; height: auto; border-radius: 8px; margin: 1rem 0; }
.course-content ul, .course-content ol { margin-bottom: 1.5rem; }
.course-content iframe { border-radius: 12px; }

.sidebar-scroll {
    overflow-y: auto;
    height: 100%;
}
</style>

<!-- Barra de navegación móvil (solo visible en celular) -->
<div class="mobile-nav-bar bg-dark text-white p-3 d-flex justify-content-between align-items-center shadow-sm">
    <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
        <i class="fa-solid fa-bars"></i> Contenido
    </button>
    <div class="small fw-bold text-truncate ms-2"><?= htmlspecialchars($enroll['nombre']) ?></div>
    <div class="badge bg-success ms-2"><?= $pageNumber ?> / <?= $totalPages ?></div>
</div>

<div class="row g-0 course-player-container">
  <!-- Sidebar de Navegación (Desktop) -->
  <div class="col-md-3 bg-dark text-white p-3 d-flex flex-column h-100 sidebar-desktop">
    <?php include "course_sidebar_content.php"; ?>
  </div>

  <!-- Contenido de la Página -->
  <div class="col-md-9 bg-white h-100 overflow-auto p-4 p-md-5">
    <div class="mx-auto" style="max-width: 900px;">
      <?php if ($currentPage): ?>
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
          <h2 class="m-0 fw-bold fs-3"><?= htmlspecialchars($currentPage['titulo']) ?></h2>
          <div id="timerContainer" class="badge bg-light text-dark border p-2" style="display: none;">
            <i class="fa-regular fa-clock"></i> <span class="d-none d-sm-inline">Espera</span> <span id="timeLeft">0</span>s
          </div>
        </div>

        <div class="course-content mb-5">
          <?php if ($currentPage['tipo'] == 'video'): ?>
            <div class="ratio ratio-16x9 mb-4 shadow rounded bg-black">
              <?= $currentPage['contenido'] ?>
            </div>
          <?php elseif ($currentPage['tipo'] == 'pdf'): ?>
            <div class="mb-4">
              <a href="<?= $currentPage['contenido'] ?>" target="_blank" class="btn btn-outline-danger mb-3 w-100 w-md-auto">
                <i class="fa-solid fa-file-pdf"></i> Abrir PDF
              </a>
              <div class="ratio ratio-4x3 d-none d-md-block">
                <iframe src="<?= $currentPage['contenido'] ?>" class="border rounded shadow-sm"></iframe>
              </div>
              <div class="alert alert-info d-md-none small">
                El visor de PDF integrado está optimizado para computadoras. En celular se recomienda usar el botón "Abrir PDF".
              </div>
            </div>
          <?php else: ?>
            <div class="fs-5 lh-lg">
              <?= $currentPage['contenido'] ?>
            </div>
          <?php endif; ?>
        </div>

        <?php if ($pageNumber == $totalPages): ?>
          <!-- Opciones Finales al llegar a la última página -->
          <div class="card border-primary shadow-sm mt-5">
            <div class="card-body p-4 text-center">
              <h4 class="fw-bold text-primary mb-3">¡Has llegado al final!</h4>
              <p class="text-muted mb-4 small">¿Qué deseas hacer a continuación?</p>
              <div class="d-flex flex-column flex-md-row justify-content-center gap-2">
                <a href="<?= BASE_URL ?>/univ/play/<?= $enroll['id'] ?>/1" class="btn btn-outline-primary px-4">
                  <i class="fa-solid fa-rotate-left"></i> Repetir
                </a>
                <a href="<?= BASE_URL ?>/univ/exam/<?= $enroll['id'] ?>" class="btn btn-success px-4 fw-bold" id="btnFinalExam">
                  <i class="fa-solid fa-file-pen"></i> Iniciar Evaluación
                </a>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between pt-4 mt-5 border-top">
          <?php if ($pageNumber > 1): ?>
            <a href="<?= BASE_URL ?>/univ/play/<?= $enroll['id'] ?>/<?= $pageNumber - 1 ?>" class="btn btn-outline-dark">
              <i class="fa-solid fa-arrow-left"></i> <span class="d-none d-sm-inline">Anterior</span>
            </a>
          <?php else: ?>
            <div></div>
          <?php endif; ?>

          <?php if ($pageNumber < $totalPages): ?>
            <a href="<?= BASE_URL ?>/univ/play/<?= $enroll['id'] ?>/<?= $pageNumber + 1 ?>" 
               class="btn btn-primary px-4 fw-bold" 
               id="btnNext">
              <span class="d-none d-sm-inline">Siguiente</span> <i class="fa-solid fa-arrow-right"></i>
            </a>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="text-center py-5 mt-5">
          <i class="fa-solid fa-hourglass-start fa-4x text-muted mb-4"></i>
          <h3>Esta página aún no tiene contenido.</h3>
          <p class="text-muted">Contacta al administrador para completar este curso.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Sidebar Offcanvas para Móvil -->
<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
  <div class="offcanvas-header border-bottom border-secondary">
    <h5 class="offcanvas-title" id="offcanvasSidebarLabel">Contenido del Curso</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body p-0">
    <div class="p-3">
        <?php include "course_sidebar_content.php"; ?>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const minSeconds = <?= (int)($currentPage['tiempo_minimo_segundos'] ?? 0) ?>;
    if (minSeconds > 0) {
        const btnNext = document.getElementById('btnNext');
        const btnFinalExam = document.getElementById('btnFinalExam');
        const btnExamSide = document.getElementById('btnExamSide');
        
        const disableBtn = (btn) => {
            if (btn) {
                btn.classList.add('disabled');
                btn.style.pointerEvents = 'none';
                btn.style.opacity = '0.6';
            }
        };

        const enableBtn = (btn) => {
            if (btn) {
                btn.classList.remove('disabled');
                btn.style.pointerEvents = 'auto';
                btn.style.opacity = '1';
            }
        };

        disableBtn(btnNext);
        disableBtn(btnFinalExam);
        disableBtn(btnExamSide);

        const timerContainer = document.getElementById('timerContainer');
        const timeLeftSpan = document.getElementById('timeLeft');
        if (timerContainer) timerContainer.style.display = 'block';
        
        let remaining = minSeconds;
        if (timeLeftSpan) timeLeftSpan.innerText = remaining;

        const interval = setInterval(() => {
            remaining--;
            if (timeLeftSpan) timeLeftSpan.innerText = remaining;
            if (remaining <= 0) {
                clearInterval(interval);
                enableBtn(btnNext);
                enableBtn(btnFinalExam);
                enableBtn(btnExamSide);
                if (timerContainer) timerContainer.style.display = 'none';
            }
        }, 1000);
    }
});
</script>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
