<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-4 no-print">
            <div class="d-flex align-items-center">
                <a href="<?= BASE_URL ?>/reportes" class="btn btn-link text-decoration-none ps-0">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <h2 class="mb-0 ms-2 fw-bold">Detalle de Auditoría</h2>
            </div>
            <button onclick="window.print()" class="btn btn-outline-dark">
                <i class="bi bi-printer me-2"></i> Imprimir Reporte
            </button>
        </div>

        <div class="card shadow-sm border-0 mb-4 overflow-hidden">
            <div class="card-header bg-dark text-white p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <small class="text-white-50 text-uppercase ls-1">Reporte de Evaluación #
                            <?= $report['id'] ?>
                        </small>
                        <h4 class="mb-0 fw-bold">
                            <?= htmlspecialchars($report['checklist_nombre'] ?? 'N/A') ?>
                        </h4>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <span class="badge bg-primary px-3 py-2">
                            <?= htmlspecialchars($report['area'] ?? 'General') ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 bg-light border-bottom">
                <div class="row g-3">
                    <div class="col-sm-6 col-md-3">
                        <label class="small text-muted d-block">Evaluado</label>
                        <span class="fw-bold">
                            <?= htmlspecialchars($report['evaluado_nombre'] . ' ' . $report['evaluado_apellido']) ?>
                        </span>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label class="small text-muted d-block">Ejecutado por</label>
                        <span class="fw-bold">
                            <?= htmlspecialchars($report['ejecutado_por'] ?? 'Encuesta Pública') ?>
                        </span>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label class="small text-muted d-block">Fecha de Evaluación</label>
                        <span class="fw-bold">
                            <?= date('d/m/Y H:i', strtotime($report['fecha_evaluacion'])) ?>
                        </span>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label class="small text-muted d-block">Estado</label>
                        <span class="badge bg-success">Completado</span>
                    </div>
                </div>

                <div class="row g-3 mt-2 border-top pt-3">
                    <?php
                    $duracion_detallada = "Sin datos";
                    if (!empty($report['fecha_inicio']) && !empty($report['fecha_fin'])) {
                        $inicio = new DateTime($report['fecha_inicio']);
                        $fin = new DateTime($report['fecha_fin']);
                        $intervalo = $inicio->diff($fin);

                        $partes = [];
                        if ($intervalo->h > 0)
                            $partes[] = $intervalo->h . ($intervalo->h == 1 ? " hora" : " horas");
                        if ($intervalo->i > 0)
                            $partes[] = $intervalo->i . ($intervalo->i == 1 ? " minuto" : " minutos");
                        if ($intervalo->s > 0)
                            $partes[] = $intervalo->s . ($intervalo->s == 1 ? " segundo" : " segundos");

                        $duracion_detallada = !empty($partes) ? implode(", ", $partes) : "0 segundos";
                    }
                    ?>
                    <div class="col-sm-6 col-md-3">
                        <label class="small text-muted d-block">Inicio de Auditoría</label>
                        <span class="fw-bold text-dark">
                            <?= !empty($report['fecha_inicio']) ? date('d/m/Y H:i:s', strtotime($report['fecha_inicio'])) : 'Sin datos' ?>
                        </span>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label class="small text-muted d-block">Fin de Auditoría</label>
                        <span class="fw-bold text-dark">
                            <?= !empty($report['fecha_fin']) ? date('d/m/Y H:i:s', strtotime($report['fecha_fin'])) : 'Sin datos' ?>
                        </span>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <label class="small text-muted d-block">Duración</label>
                        <span class="fw-bold text-primary">
                            <i class="bi bi-clock me-1"></i><?= $duracion_detallada ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Dashboard de Cumplimiento -->
            <div class="card-body p-4 border-bottom">
                <div class="row align-items-center">
                    <div class="col-md-4 text-center border-end">
                        <div class="compliance-circle mb-2 mx-auto">
                            <?php
                            $stats = $report['stats'];
                            $colorClass = 'text-danger';
                            if ($stats['cumplimiento'] >= 80)
                                $colorClass = 'text-success';
                            elseif ($stats['cumplimiento'] >= 50)
                                $colorClass = 'text-warning';
                            ?>
                            <span class="h1 mb-0 fw-black <?= $colorClass ?>"><?= $stats['cumplimiento'] ?>%</span>
                        </div>
                        <div class="text-uppercase small fw-bold text-muted mt-2">Nivel de Cumplimiento</div>
                    </div>
                    <div class="col-md-8 ps-md-5">
                        <div class="row g-4">
                            <div class="col-6 col-md-4">
                                <div class="stats-item">
                                    <h4 class="mb-0 fw-bold text-success"><?= $stats['total_si'] ?></h4>
                                    <div class="text-muted small">Respuestas SÍ</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="stats-item">
                                    <h4 class="mb-0 fw-bold text-danger"><?= $stats['total_no'] ?></h4>
                                    <div class="text-muted small">Respuestas NO</div>
                                </div>
                            </div>
                            <?php if ($stats['promedio_numerico'] > 0): ?>
                                <div class="col-12 col-md-4">
                                    <div class="stats-item">
                                        <h4 class="mb-0 fw-bold text-primary"><?= $stats['promedio_numerico'] ?> <small
                                                class="text-muted" style="font-size: 0.6em">/ 10</small></h4>
                                        <div class="text-muted small">Promedio Eval.</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="progress mt-4" style="height: 10px; border-radius: 5px;">
                            <?php
                            $barClass = 'bg-danger';
                            if ($stats['cumplimiento'] >= 80)
                                $barClass = 'bg-success';
                            elseif ($stats['cumplimiento'] >= 50)
                                $barClass = 'bg-warning';
                            ?>
                            <div class="progress-bar <?= $barClass ?>" role="progressbar"
                                style="width: <?= $stats['cumplimiento'] ?>%"
                                aria-valuenow="<?= $stats['cumplimiento'] ?>" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mt-1">
                            <span>0%</span>
                            <span>50%</span>
                            <span>100%</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-striped align-middle mb-0">
                        <thead class="bg-white border-bottom">
                            <tr>
                                <th class="ps-4 py-3 text-muted small" style="width: 50px;">#</th>
                                <th class="py-3 text-muted small">Pregunta / Requerimiento</th>
                                <th class="py-3 text-muted small text-center" style="width: 200px;">Resultado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $currentGroup = null; ?>
                            <?php foreach ($report['respuestas'] as $index => $row): ?>
                                <?php if (!empty($row['grupo']) && $row['grupo'] !== $currentGroup): ?>
                                    <tr>
                                        <td colspan="3"
                                            class="bg-light fw-bold text-primary py-3 px-4 border-bottom text-uppercase">
                                            <i class="bi bi-folder2-open me-2"></i><?= htmlspecialchars($row['grupo']) ?>
                                        </td>
                                    </tr>
                                    <?php $currentGroup = $row['grupo']; ?>
                                <?php endif; ?>
                                <tr>
                                    <td class="ps-4 text-muted">
                                        <?= $index + 1 ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold px-2">
                                            <?= htmlspecialchars($row['pregunta']) ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['tipo_respuesta'] === 'boolean'): ?>
                                            <?php if ($row['respuesta_boolean'] !== null && (int) $row['respuesta_boolean'] === 1): ?>
                                                <span
                                                    class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">SÍ</span>
                                            <?php elseif ($row['respuesta_boolean'] !== null && (int) $row['respuesta_boolean'] === 0): ?>
                                                <span
                                                    class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill">NO</span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border px-3 py-2 rounded-pill">N/A</span>
                                            <?php endif; ?>

                                        <?php elseif ($row['tipo_respuesta'] === 'numeric_scale'): ?>
                                            <div class="h5 mb-0 fw-bold text-primary">
                                                <?= $row['respuesta_numerica'] ?>
                                            </div>

                                        <?php elseif ($row['tipo_respuesta'] === 'foto'): ?>
                                            <?php if (!empty($row['respuesta_foto'])): ?>
                                                <?php $fotos = json_decode($row['respuesta_foto'], true) ?? []; ?>
                                                <div class="d-flex flex-wrap gap-2 justify-content-center">
                                                    <?php foreach ($fotos as $foto): ?>
                                                        <a href="<?= BASE_URL ?>/<?= htmlspecialchars($foto) ?>" target="_blank">
                                                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($foto) ?>"
                                                                class="img-thumbnail"
                                                                style="max-height:80px;max-width:120px;object-fit:cover;"
                                                                alt="Evidencia fotográfica">
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted small fst-italic">Sin foto adjunta</span>
                                            <?php endif; ?>

                                        <?php else: ?>
                                            <div class="bg-light p-2 rounded small text-start">
                                                <?= !empty($row['respuesta_texto']) ? nl2br(htmlspecialchars($row['respuesta_texto'])) : '<i class="text-muted">Sin observación</i>' ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-light p-4 rounded card border-0 shadow-sm mt-4 no-print">
            <h5><i class="bi bi-info-circle me-2"></i> Resumen de auditoría</h5>
            <p class="text-muted small mb-0">Este reporte es un documento oficial generado automáticamente por el
                sistema de Gestión Hotelera. Cualquier modificación física debe ser reportada a la gerencia de área.</p>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            background-color: white !important;
        }

        .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
        }
    }

    .ls-1 {
        letter-spacing: 1px;
    }

    .bg-success-subtle {
        background-color: #d1e7dd;
    }

    .bg-danger-subtle {
        background-color: #f8d7da;
    }

    .fw-black {
        font-weight: 900;
        font-size: 3.5rem;
    }

    .stats-item {
        padding: 10px;
        border-radius: 8px;
        background-color: #f8f9fa;
        transition: transform 0.2s;
    }

    .stats-item:hover {
        transform: translateY(-2px);
    }
</style>