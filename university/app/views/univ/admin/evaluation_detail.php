<?php require VIEW_PATH . "/layouts/header.php"; ?>

<div class="mb-3">
  <button onclick="history.back()" class="btn btn-sm btn-outline-secondary">
    <i class="fa-solid fa-arrow-left"></i> Volver al Historial
  </button>
</div>

<div class="card mb-4 shadow-sm">
    <div class="card-body bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Detalle del Examen</h4>
                <p class="text-muted mb-0 small">Intento #<?= $evaluation['numero_intento'] ?> — Finalizado el <?= date('d/m/Y H:i', strtotime($evaluation['fecha_fin'])) ?></p>
            </div>
            <div class="text-end">
                <span class="fs-4 fw-bold <?= $evaluation['aprobado'] ? 'text-success' : 'text-danger' ?>">
                    <?= $evaluation['score'] ?>%
                </span>
                <div class="small fw-bold <?= $evaluation['aprobado'] ? 'text-success' : 'text-danger' ?>">
                    <?= $evaluation['aprobado'] ? 'APROBADO' : 'REPROBADO' ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <h5 class="mb-3">Revisión de Preguntas</h5>
        
        <?php foreach ($details as $index => $d): ?>
            <div class="card mb-3 shadow-sm <?= $d['es_correcta'] ? 'border-success' : 'border-danger' ?>">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Pregunta <?= $index + 1 ?></span>
                    <?php if ($d['es_correcta']): ?>
                        <span class="badge bg-success"><i class="fa-solid fa-check"></i> Correcta</span>
                    <?php else: ?>
                        <span class="badge bg-danger"><i class="fa-solid fa-xmark"></i> Incorrecta</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <p class="fs-6"><?= htmlspecialchars($d['texto_pregunta']) ?></p>
                    
                    <div class="mt-3">
                        <div class="p-2 rounded mb-2 <?= $d['es_correcta'] ? 'bg-success-subtle border border-success' : 'bg-danger-subtle border border-danger' ?>">
                            <div class="small text-muted">Respuesta del alumno:</div>
                            <div class="fw-bold"><?= htmlspecialchars($d['respuesta_elegida'] ?? 'No respondió') ?></div>
                        </div>
                        
                        <?php if (!$d['es_correcta']): ?>
                            <div class="p-2 rounded bg-light border">
                                <div class="small text-muted">Respuesta correcta:</div>
                                <div class="fw-bold text-success"><?= htmlspecialchars($d['respuesta_correcta']) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.bg-success-subtle { background-color: #d1e7dd; }
.bg-danger-subtle { background-color: #f8d7da; }
</style>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
