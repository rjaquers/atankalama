<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold fs-4">Historial de Evaluaciones</h2>
    <a href="<?= BASE_URL ?>/reportes/stats" class="btn btn-primary">
        <i class="bi bi-bar-chart-line me-2"></i>Ver Estadísticas Globales
    </a>
</div>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0;
    }
    .table.dataTable {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }
    .dataTables_filter input {
        border-radius: 20px;
        padding: 0.5rem 1rem;
        border: 1px solid #dee2e6;
    }
    .dataTables_length select {
        border-radius: 10px;
        border: 1px solid #dee2e6;
    }
    .card-body.p-0 .dataTables_wrapper {
        padding: 1.5rem 0;
    }
    .dataTables_info, .dataTables_paginate {
        padding: 1rem 1.5rem !important;
    }
    .dataTables_filter, .dataTables_length {
        padding: 0 1.5rem 1rem 1.5rem !important;
    }
</style>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="evaluationsTable" class="table table-hover align-middle mb-0 w-100">
                <thead class="bg-light text-muted small">
                    <tr>
                        <th class="ps-4">Folio</th>
                        <th>Evaluado</th>
                        <th>Checklist</th>
                        <th>Área</th>
                        <th>Fecha</th>
                        <th>Ejecutado por</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($evaluaciones)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-folder-x display-4 text-muted mb-3 d-block"></i>
                                <span class="text-muted">No hay evaluaciones registradas.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($evaluaciones as $row): ?>
                            <tr>
                                <td class="ps-4"><span class="text-muted">#<?= $row['id'] ?></span></td>
                                <td>
                                    <div class="fw-bold">
                                        <?= htmlspecialchars($row['evaluado_nombre'] . ' ' . $row['evaluado_apellido']) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($row['checklist_nombre']) ?></td>
                                <td>
                                    <?php $color = getAreaColor($row['area']); ?>
                                    <span class="badge bg-<?= $color ?>-subtle text-<?= $color ?> border border-<?= $color ?>-subtle"><?= htmlspecialchars($row['area']) ?></span>
                                </td>
                                <td>
                                    <small
                                        class="text-muted"><?= date('d/m/Y H:i', strtotime($row['fecha_evaluacion'])) ?></small>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($row['ejecutado_por'] ?? 'Encuesta Pública') ?></small>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end align-items-center">
                                        <a href="<?= BASE_URL ?>/reportes/ver?id=<?= $row['id'] ?>"
                                            class="btn btn-sm btn-primary rounded-pill px-3 me-2">
                                            <i class="bi bi-eye"></i> Ver Detalle
                                        </a>
                                        <button onclick="deleteReport(<?= $row['id'] ?>)"
                                            class="btn btn-sm btn-outline-danger border-0" title="Eliminar Reporte">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    async function deleteReport(id) {
        if (!confirm('¿Está seguro de eliminar esta evaluación? Esta acción no se puede deshacer.')) return;

        try {
            const res = await fetch(`<?= BASE_URL ?>/api/reportes/eliminar/${id}`, {
                method: 'POST'
            });
            const data = await res.json();
            if (res.ok && data.success) {
                location.reload();
            } else {
                alert(data.error || 'Ocurrió un error al eliminar la evaluación.');
            }
        } catch (err) {
            alert("Error de conexión");
        }
    }
</script>

<!-- DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#evaluationsTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            order: [[0, 'desc']], // Ordenar por Folio desc por defecto
            pageLength: 25,
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            columnDefs: [
                { orderable: false, targets: 6 } // Desactivar orden en columna Acciones
            ]
        });
    });
</script>