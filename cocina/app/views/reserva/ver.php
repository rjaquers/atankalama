<!DOCTYPE html>
<html lang='es'>
<head>
    <?php include(ROOT_PATH . '../public/static/templates/head.php'); ?>
</head>
<body class='pro-body'>
    <?php include(ROOT_PATH . '../public/static/templates/menu.php'); ?>

    <div class='container py-4'>

        <!-- Cabecera -->
        <div class="d-flex justify-content-between align-items-start mb-4 pb-2 border-bottom"
             style="border-color:var(--color-border)!important;">
            <div>
                <h2 class="mb-1 fw-bold">
                    <i class="bi bi-calendar-range me-2" style="color:var(--color-cta)"></i>
                    <?= htmlspecialchars($reserva['nombre']) ?>
                </h2>
                <div class="d-flex gap-3 align-items-center text-muted small mt-1 flex-wrap">
                    <?php if ($reserva['nombre_empresa']): ?>
                    <span><i class="bi bi-building me-1"></i><?= htmlspecialchars($reserva['nombre_empresa']) ?></span>
                    <?php endif; ?>
                    <span>
                        <i class="bi bi-calendar3 me-1"></i>
                        <?= date('d/m/Y', strtotime($reserva['fecha_desde'])) ?>
                        —
                        <?= date('d/m/Y', strtotime($reserva['fecha_hasta'])) ?>
                    </span>
                    <span><i class="bi bi-layers me-1"></i><?= count($reserva['comandas']) ?> días</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="index.php?page=reserva/logCambios/<?= $reserva['id'] ?>"
                   class="btn btn-outline-secondary px-3">
                    <i class="bi bi-clock-history me-1"></i>Log de cambios
                </a>
                <a href="index.php?page=comanda/listado" class="btn btn-outline-secondary px-3">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>

        <?php if ($ok === 'creada'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>Reserva creada correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($reserva['observaciones']): ?>
        <div class="alert alert-light border mb-4">
            <i class="bi bi-sticky me-2 text-warning"></i><?= nl2br(htmlspecialchars($reserva['observaciones'])) ?>
        </div>
        <?php endif; ?>

        <!-- Tabla de días -->
        <?php if (empty($reserva['comandas'])): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-calendar-x" style="font-size:2.5rem;opacity:.3;"></i>
            <p class="mt-3 mb-0">No hay comandas vinculadas a esta reserva.</p>
        </div>
        <?php else: ?>

        <?php
            $dias   = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
            $totalNom = 0; $totalGen = 0; $totalPer = 0;
        ?>

        <div class="pro-card border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4">Día</th>
                            <th>Servicio</th>
                            <th>Hotel</th>
                            <th class="text-center">Personas</th>
                            <th class="text-center">Nominales</th>
                            <th class="text-center">Genéricos</th>
                            <th class="text-center">Última impresión</th>
                            <th class="text-center px-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($reserva['comandas'] as $c):
                        $ts          = strtotime($c['fecha']);
                        $nom         = (int) $c['total_nominales'];
                        $gen         = (int) $c['total_genericos'];
                        $per         = (int) $c['cantidad_personas'];
                        $totalNom   += $nom;
                        $totalGen   += $gen;
                        $totalPer   += $per;
                        $totalVouch  = $nom + $gen;
                        $hoyEsFecha  = date('Y-m-d') === $c['fecha'];
                    ?>
                    <tr class="<?= $hoyEsFecha ? 'table-warning' : '' ?>">
                        <td class="px-4 fw-semibold">
                            <?= $dias[date('w', $ts)] ?> <?= date('d/m/Y', $ts) ?>
                            <?php if ($hoyEsFecha): ?>
                            <span class="badge bg-warning text-dark ms-1">HOY</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= VoucherModel::colorServicio($c['tipo_servicio']) ?>">
                                <?= VoucherModel::etiquetaServicio($c['tipo_servicio']) ?>
                            </span>
                            <?php if ($c['hora_servicio']): ?>
                            <span class="text-muted small ms-1"><?= substr($c['hora_servicio'],0,5) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small"><?= htmlspecialchars($c['nombre_hotel']) ?></td>
                        <td class="text-center fw-semibold"><?= $per ?></td>
                        <td class="text-center">
                            <?php if ($nom > 0): ?>
                            <span class="badge bg-primary"><?= $nom ?></span>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($gen > 0): ?>
                            <span class="badge bg-warning text-dark"><?= $gen ?></span>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center small text-muted">
                            <?php if ($c['ultima_impresion']): ?>
                            <i class="bi bi-printer-fill text-success me-1"></i>
                            <?= date('d/m H:i', strtotime($c['ultima_impresion'])) ?>
                            <?php else: ?>
                            <span class="text-muted">Sin imprimir</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center px-4">
                            <a href="index.php?page=voucher/clientes/<?= $c['id'] ?>"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-ticket-perforated me-1"></i>Vouchers
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td class="px-4" colspan="3">Totales</td>
                            <td class="text-center"><?= $totalPer ?></td>
                            <td class="text-center"><span class="badge bg-primary"><?= $totalNom ?></span></td>
                            <td class="text-center"><span class="badge bg-warning text-dark"><?= $totalGen ?></span></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Desvincular / agregar comandas -->
        <div class="mt-4">
            <details>
                <summary class="text-muted small" style="cursor:pointer;">
                    <i class="bi bi-gear me-1"></i>Gestionar comandas vinculadas
                </summary>
                <div class="mt-3 pro-card border-0 p-4">
                    <h6 class="fw-bold mb-3">Desvincular una comanda de esta reserva</h6>
                    <?php foreach ($reserva['comandas'] as $c):
                        $ts = strtotime($c['fecha']);
                    ?>
                    <form method="POST" action="index.php?page=reserva/desvincular"
                          class="d-inline-block me-2 mb-2"
                          onsubmit="return confirm('¿Desvincular el día <?= date('d/m/Y', $ts) ?> de esta reserva?')">
                        <input type="hidden" name="reserva_id" value="<?= $reserva['id'] ?>">
                        <input type="hidden" name="comanda_id" value="<?= $c['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-x-circle me-1"></i>
                            <?= $dias[date('w', $ts)] ?> <?= date('d/m', $ts) ?>
                        </button>
                    </form>
                    <?php endforeach; ?>
                </div>
            </details>
        </div>

        <?php endif; ?>

    </div>

    <?php include(ROOT_PATH . '../public/static/templates/footer.php'); ?>
</body>
</html>
