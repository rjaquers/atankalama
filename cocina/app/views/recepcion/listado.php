<!DOCTYPE html>
<html lang='es'>

<head>
    <?php include(ROOT_PATH . '../public/static/templates/head.php'); ?>
</head>

<body class='pro-body'>
    <?php include(ROOT_PATH . '../public/static/templates/menu.php'); ?>

    <div class='container py-4'>

        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom"
            style="border-color: var(--color-border) !important;">
            <h2 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2" style="color: var(--color-cta)"></i>Listado de Órdenes</h2>
            <a href="index.php?page=recepcion/particular" class="btn btn-pro-action px-3" style="width: auto;"><i
                    class="bi bi-plus-circle me-1"></i>Nueva Solicitud</a>
        </div>

        <div class="pro-card border-0 mb-4">
            <div class="card-header bg-transparent py-3 px-4" style="border-bottom: 1px solid var(--color-border);">
                <h5 class="fw-bold mb-0" style="color: var(--color-primary);"><i class="bi bi-table me-2"
                        style="color: var(--color-cta)"></i>Historial de Pedidos</h5>
            </div>
            <div class="card-body px-4 pb-4 pt-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tablaOrdenes">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">ID</th>
                                <th class="border-0">Fecha / Hora</th>
                                <th class="border-0">Hab.</th>
                                <th class="border-0">Lugar</th>
                                <th class="border-0">Solicitante</th>
                                <th class="border-0">Personas</th>
                                <th class="border-0 text-end">Total</th>
                                <th class="border-0 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ordenes as $o): ?>
                                <tr>
                                    <td class="fw-bold">#<?= $o['id'] ?></td>
                                    <td class="text-muted small">
                                        <?= date('d/m/Y H:i', strtotime($o['fecha_hora'])) ?>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($o['habitacion']) ?></span></td>
                                    <td><?= htmlspecialchars($o['lugar']) ?></td>
                                    <td>
                                        <?php if (($o['tipo_solicitante'] ?? 'particular') === 'empresa'): ?>
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 me-1">
                                                <i class="bi bi-building me-1"></i><?= htmlspecialchars($o['nombre_empresa'] ?? 'Empresa') ?>
                                            </span>
                                            <?php if (!empty($o['nombre_contacto'])): ?>
                                                <small class="text-muted"><?= htmlspecialchars($o['nombre_contacto']) ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?= htmlspecialchars($o['nombre_huesped'] ?? 'N/A') ?>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($o['email_respaldo'])): ?>
                                            <div class="mt-1 small text-muted italic">
                                                <i class="bi bi-chat-left-dots me-1"></i><?= htmlspecialchars($o['email_respaldo']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= $o['cantidad_personas'] ?></td>
                                    <td class="text-end fw-bold text-primary">$<?= number_format($o['total'], 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="index.php?page=recepcion/imprimir&id=<?= $o['id'] ?>"
                                               class="btn btn-sm btn-outline-primary shadow-sm"
                                               title="Imprimir Comprobante">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!--footer-->
    <?php include(ROOT_PATH . '../public/static/templates/footer.php'); ?>
    <!--footer-->

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tablaOrdenes').DataTable({
                "order": [[ 0, "desc" ]],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                "pageLength": 25,
                "responsive": true
            });
        });
    </script>

</body>

</html>
