<?php require VIEW_PATH . "/layouts/header.php"; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <?php if ($result['aprobado']): ?>
                        <div class="mb-4">
                            <i class="fa-solid fa-circle-check text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="fw-bold text-success mb-2">¡Felicitaciones!</h2>
                        <h4 class="mb-4">Has aprobado el curso</h4>
                    <?php else: ?>
                        <div class="mb-4">
                            <i class="fa-solid fa-circle-xmark text-danger" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="fw-bold text-danger mb-2">No aprobado</h2>
                        <h4 class="mb-4">Tu puntaje no fue suficiente</h4>
                    <?php endif; ?>

                    <div class="bg-light rounded p-4 mb-4">
                        <div class="row">
                            <div class="col-6 border-end">
                                <div class="small text-muted">Puntaje</div>
                                <div class="h3 m-0"><?= $result['score'] ?>%</div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Correctas</div>
                                <div class="h3 m-0"><?= $result['correctas'] ?> / <?= $result['totales'] ?></div>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted mb-5">
                        Curso: <strong><?= htmlspecialchars($enroll['nombre']) ?></strong><br>
                        Mínimo requerido para aprobar: <?= $enroll['min_score_to_approve'] ?>%
                    </p>

                    <div class="d-grid gap-2">
                        <?php if ($result['aprobado']): ?>
                            <a href="<?= BASE_URL ?>/univ/certificate/<?= $enroll['id'] ?>" target="_blank" class="btn btn-success btn-lg">
                                <i class="fa-solid fa-certificate"></i> Ver Certificado
                            </a>
                            <a href="<?= BASE_URL ?>/univ" class="btn btn-outline-primary">Volver a mis cursos</a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/univ/play/<?= $enroll['id'] ?>" class="btn btn-warning btn-lg">Repasar contenido</a>
                            <a href="<?= BASE_URL ?>/univ" class="btn btn-outline-secondary">Volver al panel</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
