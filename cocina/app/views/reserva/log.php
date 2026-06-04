<!DOCTYPE html>
<html lang='es'>
<head>
    <?php include(ROOT_PATH . '../public/static/templates/head.php'); ?>
</head>
<body class='pro-body'>
    <?php include(ROOT_PATH . '../public/static/templates/menu.php'); ?>

    <div class='container py-4'>

        <div class="d-flex justify-content-between align-items-start mb-4 pb-2 border-bottom"
             style="border-color:var(--color-border)!important;">
            <div>
                <h2 class="mb-1 fw-bold">
                    <i class="bi bi-clock-history me-2" style="color:var(--color-cta)"></i>
                    Log de cambios
                </h2>
                <p class="text-muted small mb-0">
                    Reserva: <strong><?= htmlspecialchars($reserva['nombre']) ?></strong>
                </p>
            </div>
            <a href="index.php?page=reserva/ver/<?= $reserva['id'] ?>"
               class="btn btn-outline-secondary px-3">
                <i class="bi bi-arrow-left me-1"></i>Volver a la reserva
            </a>
        </div>

        <?php if (empty($cambios)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-journal-x" style="font-size:2.5rem;opacity:.3;"></i>
            <p class="mt-3 mb-0">No hay cambios registrados para esta reserva.</p>
        </div>
        <?php else: ?>

        <div class="pro-card border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4">Fecha y hora</th>
                            <th>Día afectado</th>
                            <th>Campo</th>
                            <th>Valor anterior</th>
                            <th>Valor nuevo</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cambios as $c):
                        $dias = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
                        $ts   = strtotime($c['fecha']);
                    ?>
                    <tr>
                        <td class="px-4 text-muted small">
                            <?= date('d/m/Y H:i', strtotime($c['created_at'])) ?>
                        </td>
                        <td class="fw-semibold small">
                            <?= $dias[date('w', $ts)] ?> <?= date('d/m/Y', $ts) ?>
                            <div class="text-muted" style="font-size:.75rem;">
                                <?= VoucherModel::etiquetaServicio($c['tipo_servicio']) ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border" style="font-size:.78rem;">
                                <?= CambioLogModel::etiquetaCampo($c['campo']) ?>
                            </span>
                        </td>
                        <td class="text-muted small">
                            <?php if ($c['valor_anterior'] !== null): ?>
                            <span class="text-danger"><?= htmlspecialchars($c['valor_anterior']) ?></span>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="small">
                            <?php if ($c['valor_nuevo'] !== null): ?>
                            <span class="text-success fw-semibold"><?= htmlspecialchars($c['valor_nuevo']) ?></span>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small">
                            <i class="bi bi-person me-1"></i><?= htmlspecialchars($c['email_usuario']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php endif; ?>

    </div>

    <?php include(ROOT_PATH . '../public/static/templates/footer.php'); ?>
</body>
</html>
