<!DOCTYPE html>
<html lang='es'>
<head>
    <?php include(ROOT_PATH . '../public/static/templates/head.php'); ?>
</head>
<body class='pro-body'>
    <?php include(ROOT_PATH . '../public/static/templates/menu.php'); ?>

    <div class='container py-4'>

        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom"
             style="border-color:var(--color-border)!important;">
            <div>
                <h2 class="mb-1 fw-bold">
                    <i class="bi bi-calendar-range me-2" style="color:var(--color-cta)"></i>
                    Nueva Reserva Multi-día
                </h2>
                <p class="text-muted small mb-0">Agrupa comandas del mismo cliente en días consecutivos.</p>
            </div>
            <a href="index.php?page=comanda/listado" class="btn btn-outline-secondary px-3">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </a>
        </div>

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="row g-4">

                <!-- Datos de la reserva -->
                <div class="col-lg-4">
                    <div class="pro-card border-0 h-100">
                        <div class="card-header bg-transparent py-3 px-4"
                             style="border-bottom:1px solid var(--color-border);">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-info-circle me-2 text-primary"></i>Datos de la reserva
                            </h5>
                        </div>
                        <div class="card-body px-4 py-3">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">
                                    Nombre / Referencia <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nombre" class="form-control shadow-none"
                                       placeholder="Ej: Grupo Minera XYZ — Mayo 2026" required
                                       value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Empresa <span class="text-muted">(opcional)</span></label>
                                <input type="text" name="nombre_empresa" class="form-control shadow-none"
                                       placeholder="Nombre de la empresa"
                                       value="<?= htmlspecialchars($_POST['nombre_empresa'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Observaciones <span class="text-muted">(opcional)</span></label>
                                <textarea name="observaciones" class="form-control shadow-none" rows="3"
                                          placeholder="Notas internas..."><?= htmlspecialchars($_POST['observaciones'] ?? '') ?></textarea>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-pro-action w-100">
                                    <i class="bi bi-check-circle me-1"></i>Crear Reserva
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selección de comandas -->
                <div class="col-lg-8">
                    <div class="pro-card border-0">
                        <div class="card-header bg-transparent py-3 px-4 d-flex justify-content-between align-items-center"
                             style="border-bottom:1px solid var(--color-border);">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-list-check me-2 text-success"></i>
                                Comandas disponibles
                                <span class="badge bg-secondary ms-2"><?= count($comandasSinReserva) ?></span>
                            </h5>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnSelTodas">
                                    Seleccionar todas
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDeselTodas">
                                    Limpiar
                                </button>
                            </div>
                        </div>

                        <?php if (empty($comandasSinReserva)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x" style="font-size:2.5rem;opacity:.3;"></i>
                            <p class="mt-3 mb-0 small">No hay comandas disponibles sin reserva asignada.</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4 text-center" style="width:48px;"></th>
                                        <th>Fecha</th>
                                        <th>Servicio</th>
                                        <th>Hotel</th>
                                        <th>Empresa</th>
                                        <th class="text-center">Personas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($comandasSinReserva as $c):
                                        $checked = in_array($c['id'], (array)($_POST['comandas'] ?? [])) ? 'checked' : '';
                                    ?>
                                    <tr class="fila-comanda <?= $checked ? 'table-primary' : '' ?>"
                                        data-id="<?= $c['id'] ?>">
                                        <td class="px-4 text-center">
                                            <input type="checkbox" name="comandas[]"
                                                   value="<?= $c['id'] ?>"
                                                   class="form-check-input chk-comanda"
                                                   <?= $checked ?>>
                                        </td>
                                        <td class="fw-semibold">
                                            <?php
                                                $diasSemana = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
                                                $ts = strtotime($c['fecha']);
                                                echo $diasSemana[date('w', $ts)] . ' ' . date('d/m/Y', $ts);
                                            ?>
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
                                        <td class="text-muted small"><?= htmlspecialchars($c['nombre_empresa'] ?? '—') ?></td>
                                        <td class="text-center fw-semibold"><?= (int)$c['cantidad_personas'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </form>

    </div>

    <?php include(ROOT_PATH . '../public/static/templates/footer.php'); ?>

    <script>
        // Resaltar filas al marcar checkbox
        document.querySelectorAll('.chk-comanda').forEach(chk => {
            chk.addEventListener('change', function () {
                this.closest('tr').classList.toggle('table-primary', this.checked);
            });
        });

        document.getElementById('btnSelTodas').addEventListener('click', function () {
            document.querySelectorAll('.chk-comanda').forEach(chk => {
                chk.checked = true;
                chk.closest('tr').classList.add('table-primary');
            });
        });

        document.getElementById('btnDeselTodas').addEventListener('click', function () {
            document.querySelectorAll('.chk-comanda').forEach(chk => {
                chk.checked = false;
                chk.closest('tr').classList.remove('table-primary');
            });
        });
    </script>
</body>
</html>
