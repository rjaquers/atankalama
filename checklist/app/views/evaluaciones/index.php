<div class="mb-4 text-center">
    <h2 class="fw-bold">Iniciar Evaluación</h2>
    <p class="text-muted">Seleccione un checklist para comenzar la auditoría</p>
</div>

<div class="row g-3">
    <?php if (empty($checklists)): ?>
        <div class="col-12 text-center py-5">
            <div class="card border-0 shadow-sm p-5">
                <i class="bi bi-clipboard-x display-1 text-muted mb-3"></i>
                <h5>No hay checklists disponibles</h5>
                <p class="text-muted">Debe crear un checklist en la sección de gestión antes de evaluar.</p>
                <a href="<?= BASE_URL ?>/checklists" class="btn btn-primary mt-3">Ir a Gestión</a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($checklists as $item): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm hover-shadow transition">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <?php $color = getAreaColor($item['area']); ?>
                            <span class="badge bg-<?= $color ?>-subtle text-<?= $color ?> border border-<?= $color ?>-subtle">
                                <?= htmlspecialchars($item['area']) ?>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold mb-3">
                            <?= htmlspecialchars($item['nombre']) ?>
                        </h5>
                        <p class="card-text text-muted small flex-grow-1">
                            <i class="bi bi-person me-1"></i> Creado por:
                            <?= htmlspecialchars($item['created_by']) ?>
                        </p>
                        <div class="mt-3">
                            <a href="<?= BASE_URL ?>/evaluaciones/ejecutar?id=<?= $item['id'] ?>"
                                class="btn btn-primary w-100 rounded-pill">
                                Comenzar <i class="bi bi-play-fill ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
    }

    .transition {
        transition: all 0.3s ease;
    }
</style>