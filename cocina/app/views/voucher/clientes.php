<!DOCTYPE html>
<html lang='es'>
<head>
    <?php include(ROOT_PATH . '../public/static/templates/head.php'); ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/static/voucher/clientes.css">
</head>
<body class='pro-body'>
    <?php include(ROOT_PATH . '../public/static/templates/menu.php'); ?>

    <div class='container py-4'>

        <!-- Cabecera -->
        <div class="d-flex justify-content-between align-items-start mb-4 pb-2 border-bottom"
             style="border-color:var(--color-border)!important;">
            <div>
                <h2 class="mb-1 fw-bold">
                    <i class="bi bi-ticket-perforated me-2" style="color:var(--color-cta)"></i>
                    Vouchers — <?= htmlspecialchars($comanda['nombre_hotel']) ?>
                </h2>
                <div class="d-flex gap-2 align-items-center flex-wrap mt-1">
                    <span class="badge bg-<?= VoucherModel::colorServicio($comanda['tipo_servicio']) ?> px-3">
                        <?= VoucherModel::etiquetaServicio($comanda['tipo_servicio']) ?>
                    </span>
                    <span class="text-muted small">
                        <i class="bi bi-calendar3 me-1"></i>
                        <?= date('d/m/Y', strtotime($comanda['fecha'])) ?>
                    </span>
                    <?php if ($comanda['hora_servicio']): ?>
                    <span class="text-muted small">
                        <i class="bi bi-clock me-1"></i>
                        <?= substr($comanda['hora_servicio'], 0, 5) ?> hrs
                    </span>
                    <?php endif; ?>
                    <span class="text-muted small">
                        <i class="bi bi-people me-1"></i><?= (int)$comanda['cantidad_personas'] ?> personas
                    </span>
                    <?php if (!empty($comanda['nombre_empresa'])): ?>
                    <span class="text-muted small border-start ps-2">
                        <i class="bi bi-building me-1"></i><?= htmlspecialchars($comanda['nombre_empresa']) ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary px-3" data-bs-toggle="modal" data-bs-target="#modalEditarComanda">
                    <i class="bi bi-pencil me-1"></i>Editar comanda
                </button>
                <a href="index.php?page=voucher/imprimir/<?= $comanda['id'] ?>"
                   class="btn btn-success px-3" target="_blank">
                    <i class="bi bi-printer me-1"></i>Imprimir vouchers
                </a>
                <a href="index.php?page=comanda/listado" class="btn btn-outline-secondary px-3">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════ -->
        <!-- SELECTOR DE DÍAS (solo si pertenece a una reserva) -->
        <!-- ══════════════════════════════════════════════════ -->
        <?php if ($reserva): ?>
        <div class="mb-4 p-3 rounded-3" style="background:var(--bs-light);border:1px solid var(--color-border);">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="text-muted small fw-semibold me-1">
                    <i class="bi bi-calendar-range me-1"></i>
                    <a href="index.php?page=reserva/ver/<?= $reserva['id'] ?>"
                       class="text-decoration-none text-muted">
                        <?= htmlspecialchars($reserva['nombre']) ?>
                    </a>
                    &nbsp;/
                </span>
                <button type="button" class="btn btn-sm btn-outline-success ms-auto"
                        data-bs-toggle="modal" data-bs-target="#modalAgregarDia">
                    <i class="bi bi-plus-circle me-1"></i>Agregar día
                </button>
                <?php
                $diasSemana = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
                foreach ($reserva['comandas'] as $dia):
                    $esDiaActual = (int)$dia['id'] === $comandaId;
                    $ts          = strtotime($dia['fecha']);
                    $etiqueta    = $diasSemana[date('w', $ts)] . ' ' . date('d/m', $ts);
                    $totalDia    = (int)$dia['total_nominales'] + (int)$dia['total_genericos'];
                ?>
                <?php if ($esDiaActual): ?>
                <span class="badge bg-primary px-3 py-2" style="font-size:.82rem;">
                    <i class="bi bi-calendar-check me-1"></i><?= $etiqueta ?>
                </span>
                <?php else: ?>
                <a href="index.php?page=voucher/clientes/<?= $dia['id'] ?>"
                   class="badge bg-white text-dark border px-3 py-2 text-decoration-none"
                   style="font-size:.82rem;">
                    <i class="bi bi-calendar3 me-1"></i><?= $etiqueta ?>
                    <?php if ($totalDia > 0): ?>
                    <span class="ms-1 text-muted">(<?= $totalDia ?>)</span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($ok === 'agregado'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>Cliente agregado correctamente.
                <?php if ($propagados > 0): ?>
                    <br><small><i class="bi bi-arrow-repeat me-1"></i>Se copiaron los datos a <strong><?= $propagados ?></strong> día(s) siguiente(s) de la reserva.</small>
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($ok === 'editado'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-pencil-square me-2"></i>Cliente actualizado correctamente.
                <?php if ($propagados > 0): ?>
                    <br><small><i class="bi bi-arrow-repeat me-1"></i>El cambio se aplicó a <strong><?= $propagados ?></strong> día(s) siguiente(s) de la reserva.</small>
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($ok === 'eliminado'): ?>
            <div class="alert alert-warning alert-dismissible fade show">
                <i class="bi bi-trash me-2"></i>Cliente eliminado.
                <?php if ($propagados > 0): ?>
                    <br><small><i class="bi bi-arrow-repeat me-1"></i>También se eliminó de <strong><?= $propagados ?></strong> día(s) siguiente(s) de la reserva.</small>
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($ok === 'importado'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-file-earmark-excel me-2"></i>
                Importación completada: <strong><?= $insertados ?></strong> clientes agregados.
                <?php if ($propagados > 0): ?>
                    <br><small><i class="bi bi-arrow-repeat me-1"></i>Se replicó la lista completa en <strong><?= $propagados ?></strong> día(s) siguiente(s) de la reserva.</small>
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

        <?php elseif ($ok === 'cantidad'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-people me-2"></i>Cantidad de personas actualizada.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($ok === 'comanda_editada'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>Comanda actualizada correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($ok === 'dia_agregado'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-plus-circle me-2"></i>Día agregado a la reserva correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($ok === 'impresiones_reseteadas'): ?>
            <div class="alert alert-info alert-dismissible fade show">
                <i class="bi bi-arrow-counterclockwise me-2"></i>Contador de impresiones reseteado a cero.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($ok === 'genericos'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>Vouchers genéricos regenerados correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($ok === 'genericos_agregados'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-plus-circle me-2"></i>Vouchers genéricos agregados. Los anteriores se mantienen intactos.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($errorImport === 'formato'): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i>Formato no válido. Use .xlsx, .xls u .ods.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($errorImport === 'upload'): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i>Error al subir el archivo. Intente nuevamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">

            <!-- ══════════════════════════════════════════════════ -->
            <!-- COLUMNA IZQUIERDA: Clientes nominales             -->
            <!-- ══════════════════════════════════════════════════ -->
            <div class="col-lg-8">

                <!-- Tabla de clientes -->
                <div class="pro-card border-0 mb-4">
                    <div class="card-header bg-transparent py-3 px-4 d-flex justify-content-between align-items-center"
                         style="border-bottom:1px solid var(--color-border);">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-person-badge me-2 text-primary"></i>
                            Clientes Nominales
                            <span class="badge bg-primary ms-2"><?= count($clientes) ?></span>
                        </h5>
                    </div>

                    <?php if (empty($clientes)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-person-x" style="font-size:2.5rem;opacity:.3;"></i>
                            <p class="mt-3 mb-0 small">Sin clientes registrados. Agréguelos manualmente o importe un Excel.</p>
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0 align-middle tabla-clientes">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width:36px;">#</th>
                                    <th>Nombre</th>
                                    <th>RUT</th>
                                    <th class="text-center">Código</th>
                                    <th class="text-center">Imp.</th>
                                    <th class="text-center px-2">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($clientes as $c): ?>
                                <?php $veces = (int)($c['veces_impreso'] ?? 0); ?>
                                <tr>
                                    <td class="ps-3 text-muted"><?= $i++ ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($c['nombre']) ?></td>
                                    <td class="text-muted"><?= htmlspecialchars($c['rut'] ?? '—') ?></td>
                                    <td class="text-center">
                                        <code><?= htmlspecialchars($c['codigo']) ?></code>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($veces === 0): ?>
                                            <span class="badge bg-secondary">0</span>
                                        <?php elseif ($veces === 1): ?>
                                            <span class="badge bg-success">1</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark" title="Impreso <?= $veces ?> veces">
                                                <?= $veces ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center px-2">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="index.php?page=voucher/imprimirUno/<?= $c['codigo'] ?>"
                                               class="btn btn-sm <?= $veces > 0 ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                                               target="_blank"
                                               title="<?= $veces > 0 ? "Reimprimir (ya impreso {$veces} vez)" : 'Imprimir voucher' ?>">
                                                <i class="bi bi-printer<?= $veces > 0 ? '-fill' : '' ?>"></i>
                                            </a>
                                            <?php if ($veces > 0): ?>
                                            <form method="POST" action="index.php?page=voucher/resetearImpresiones"
                                                  onsubmit="return confirm('¿Resetear impresiones de <?= htmlspecialchars(addslashes($c['nombre'])) ?> a cero?')">
                                                <input type="hidden" name="id"         value="<?= $c['id'] ?>">
                                                <input type="hidden" name="comanda_id" value="<?= $comanda['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Resetear impresiones a 0">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                            <?php if ($veces === 0): ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-editar-cliente"
                                                    data-id="<?= $c['id'] ?>"
                                                    data-nombre="<?= htmlspecialchars($c['nombre']) ?>"
                                                    data-rut="<?= htmlspecialchars($c['rut'] ?? '') ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-cliente"
                                                    data-id="<?= $c['id'] ?>"
                                                    data-nombre="<?= htmlspecialchars($c['nombre']) ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary disabled"
                                                    title="No se puede editar: voucher ya impreso" disabled>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary disabled"
                                                    title="No se puede eliminar: voucher ya impreso" disabled>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Agregar cliente manual -->
                <div class="pro-card border-0 mb-4">
                    <div class="card-header bg-transparent py-3 px-4"
                         style="border-bottom:1px solid var(--color-border);">
                        <h6 class="fw-bold mb-0">
                            <i class="bi bi-person-plus me-2 text-success"></i>Agregar cliente
                        </h6>
                    </div>
                    <div class="card-body px-4 py-3">
                        <form method="POST" action="index.php?page=voucher/agregar">
                            <input type="hidden" name="comanda_id" value="<?= $comanda['id'] ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">RUT <span class="text-muted">(opcional)</span></label>
                                    <input type="text" name="rut" id="rut-input" class="form-control border-0 shadow-none bg-light"
                                           placeholder="12.345.678-9" maxlength="12">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" name="nombre" class="form-control border-0 shadow-none bg-light"
                                           placeholder="Juan Pérez" required>
                                </div>
                            </div>
                            
                            <?php if ($comandasFuturas > 0): ?>
                            <div class="mt-3 p-2 rounded bg-warning bg-opacity-10 border border-warning border-opacity-25">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="propagar" value="1" id="propagarCheckManual" checked>
                                    <label class="form-check-label small fw-bold text-dark" for="propagarCheckManual">
                                        <i class="bi bi-arrow-repeat me-1"></i> Copiar este cliente a los <strong><?= $comandasFuturas ?></strong> días siguientes de esta reserva.
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="mt-3 text-end">
                                <button type="submit" class="btn btn-pro-action px-4" style="width:auto;">
                                    <i class="bi bi-plus-circle me-1"></i>Agregar Cliente
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Importar Excel -->
                <div class="pro-card border-0">
                    <div class="card-header bg-transparent py-3 px-4"
                         style="border-bottom:1px solid var(--color-border);">
                        <h6 class="fw-bold mb-0">
                            <i class="bi bi-file-earmark-excel me-2 text-success"></i>Importar desde Excel
                        </h6>
                    </div>
                    <div class="card-body px-4 py-3">
                        <p class="text-muted small mb-3">
                            El archivo debe tener columnas en este orden:
                            <strong>A=RUT</strong>, <strong>B=Nombre</strong>.
                            La empresa será asignada automáticamente (<strong><?= htmlspecialchars($comanda['nombre_empresa'] ?: 'Particular') ?></strong>).
                            La primera fila se ignora. Formatos: .xlsx, .xls, .ods
                        </p>
                        <form method="POST" action="index.php?page=voucher/importar" enctype="multipart/form-data">
                            <input type="hidden" name="comanda_id" value="<?= $comanda['id'] ?>">

                            <div class="mb-3">
                                <input type="file" name="archivo" class="form-control border-0 shadow-none bg-light"
                                       accept=".xlsx,.xls,.ods" required>
                            </div>

                            <?php if ($comandasFuturas > 0): ?>
                            <div class="mb-3 p-2 rounded bg-info bg-opacity-10 border border-info border-opacity-25">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="propagar" value="1" id="propagarCheckExcel">
                                    <label class="form-check-label small text-dark" for="propagarCheckExcel">
                                        <i class="bi bi-arrow-repeat me-1"></i>
                                        Copiar esta lista a los <strong><?= $comandasFuturas ?></strong> días siguientes de la reserva
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>

                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-upload me-1"></i>Importar Excel
                            </button>
                        </form>
                    </div>
                </div>

            </div>

            <!-- ══════════════════════════════════════════════════ -->
            <!-- COLUMNA DERECHA: Vouchers genéricos               -->
            <!-- ══════════════════════════════════════════════════ -->
            <div class="col-lg-4">

                <!-- Archivos Adjuntos (Respaldos) -->
                <?php if (!empty($respaldos)): ?>
                <div class="pro-card border-0 mb-4">
                    <div class="card-header bg-transparent py-3 px-4"
                         style="border-bottom:1px solid var(--color-border);">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-paperclip me-2 text-info"></i>
                            Archivos Adjuntos
                        </h5>
                    </div>
                    <div class="card-body px-4 py-3">
                        <div class="list-group list-group-flush">
                            <?php foreach ($respaldos as $r): ?>
                            <?php 
                                $ext = strtolower(pathinfo($r['ruta_archivo'], PATHINFO_EXTENSION));
                                $icon = match($ext) {
                                    'pdf' => 'bi-file-earmark-pdf text-danger',
                                    'jpg', 'jpeg', 'png', 'gif', 'webp' => 'bi-file-earmark-image text-primary',
                                    default => 'bi-file-earmark-text text-secondary'
                                };
                                $url = BASE_URL . 'public/' . $r['ruta_archivo'];
                            ?>
                            <a href="<?= $url ?>" 
                               target="_blank" 
                               class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center gap-3">
                                <i class="bi <?= $icon ?> fs-4"></i>
                                <div class="flex-grow-1 text-truncate">
                                    <p class="mb-0 fw-semibold small">Ver adjunto</p>
                                    <small class="text-muted"><?= strtoupper($ext) ?></small>
                                </div>
                                <i class="bi bi-box-arrow-up-right text-muted small"></i>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="pro-card border-0">
                    <div class="card-header bg-transparent py-3 px-4"
                         style="border-bottom:1px solid var(--color-border);">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-ticket me-2 text-warning"></i>
                            Vouchers Genéricos
                            <span class="badge bg-warning text-dark ms-2"><?= count($genericos) ?></span>
                        </h5>
                    </div>
                    <div class="card-body px-4 py-3">
                        <p class="text-muted small mb-3">
                            Vouchers sin nombre, para personas que no requieren identificación.
                            Se generan N vouchers con código QR único cada uno.
                        </p>

                        <?php if (!empty($genericos)): ?>
                        <div class="mb-3">
                            <p class="small mb-1 text-muted">Vouchers generados:</p>
                            <div class="d-flex flex-wrap gap-1">
                                <?php foreach ($genericos as $g): ?>
                                <span class="badge bg-secondary" style="font-size:.7rem;">
                                    #<?= $g['numero'] ?> · <code style="font-size:.7rem;color:#fff;"><?= substr($g['codigo'], 0, 8) ?>…</code>
                                    <?php if ($g['canjeado']): ?>
                                    <i class="bi bi-check-circle-fill text-success ms-1"></i>
                                    <?php endif; ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <label class="form-label small fw-semibold">Cantidad de vouchers</label>
                        <div class="d-flex gap-2 mb-2">
                            <input type="number" id="cantidadGenericos" class="form-control border-0 shadow-none"
                                   min="1" max="500"
                                   value="<?= max(1, (int)$comanda['cantidad_personas'] - count($clientes) - count($genericos)) ?>">
                        </div>
                        <div class="d-flex gap-2">
                            <!-- Agregar: suma sin borrar los existentes -->
                            <form method="POST" action="index.php?page=voucher/agregarGenericos" class="flex-fill">
                                <input type="hidden" name="comanda_id" value="<?= $comanda['id'] ?>">
                                <input type="hidden" name="cantidad"   id="cantidadAgregar">
                                <button type="submit" class="btn btn-success w-100"
                                        onclick="document.getElementById('cantidadAgregar').value = document.getElementById('cantidadGenericos').value">
                                    <i class="bi bi-plus-circle me-1"></i>Agregar
                                </button>
                            </form>
                            <!-- Regenerar: borra todos y crea nuevos -->
                            <form method="POST" action="index.php?page=voucher/generarGenericos" class="flex-fill">
                                <input type="hidden" name="comanda_id" value="<?= $comanda['id'] ?>">
                                <input type="hidden" name="cantidad"   id="cantidadRegenerar">
                                <button type="submit" class="btn btn-warning w-100"
                                        onclick="document.getElementById('cantidadRegenerar').value = document.getElementById('cantidadGenericos').value; return confirm('¿Regenerar? Los vouchers genéricos anteriores quedarán inválidos.')">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Regenerar
                                </button>
                            </form>
                        </div>

                        <?php if (!empty($genericos)): ?>
                        <hr class="my-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Total canjeados:</span>
                            <span class="badge bg-success">
                                <?= count(array_filter($genericos, fn($g) => $g['canjeado'])) ?>
                                / <?= count($genericos) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Resumen rápido -->
                <div class="pro-card border-0 mt-3">
                    <div class="card-body px-4 py-3">
                        <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart me-2 text-primary"></i>Resumen</h6>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Personas comanda</span>
                            <form method="POST" action="index.php?page=reserva/editarCantidad"
                                  class="d-flex align-items-center gap-1">
                                <input type="hidden" name="comanda_id" value="<?= $comanda['id'] ?>">
                                <input type="number" name="cantidad"
                                       value="<?= (int)$comanda['cantidad_personas'] ?>"
                                       min="1" max="999"
                                       class="form-control form-control-sm border shadow-none text-end fw-bold"
                                       style="width:62px;">
                                <button type="submit" class="btn btn-sm btn-outline-primary py-0 px-2"
                                        title="Guardar nueva cantidad">
                                    <i class="bi bi-check2"></i>
                                </button>
                            </form>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Clientes nominales</span>
                            <strong class="text-primary"><?= count($clientes) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Vouchers genéricos</span>
                            <strong class="text-warning"><?= count($genericos) ?></strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small fw-semibold">Total vouchers</span>
                            <strong class="text-dark"><?= count($clientes) + count($genericos) ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Comentarios / Observaciones -->
                <div class="pro-card border-0 mt-3">
                    <div class="card-header bg-transparent py-2 px-4"
                         style="border-bottom:1px solid var(--color-border);">
                        <h6 class="fw-bold mb-0">
                            <i class="bi bi-chat-left-text me-2 text-secondary"></i>Comentarios
                        </h6>
                    </div>
                    <div class="card-body px-4 py-3">
                        <form method="POST" action="index.php?page=voucher/guardarEdicionComanda">
                            <input type="hidden" name="id"               value="<?= $comanda['id'] ?>">
                            <input type="hidden" name="redir"            value="voucher/clientes/<?= $comanda['id'] ?>&ok=comanda_editada">
                            <input type="hidden" name="nombre_hotel"     value="<?= htmlspecialchars($comanda['nombre_hotel']) ?>">
                            <input type="hidden" name="hora_servicio"    value="<?= $comanda['hora_servicio'] ? substr($comanda['hora_servicio'], 0, 5) : '' ?>">
                            <input type="hidden" name="cantidad_personas" value="<?= (int)$comanda['cantidad_personas'] ?>">
                            <input type="hidden" name="es_para_llevar"   value="<?= (int)$comanda['es_para_llevar'] ?>">
                            <input type="hidden" name="nombre_empresa"   value="<?= htmlspecialchars($comanda['nombre_empresa'] ?? '') ?>">
                            <input type="hidden" name="nombre_contacto"  value="<?= htmlspecialchars($comanda['nombre_contacto'] ?? '') ?>">

                            <div class="d-flex flex-wrap gap-1 mb-2">
                                <?php foreach (['Turno Día','Turno Noche','Ensalada de fruta','Hipocalórico','Cena Especial'] as $tag): ?>
                                <button type="button" class="btn btn-xs btn-light border text-muted fw-bold px-2 py-0"
                                        style="font-size:0.65rem; border-radius:6px;"
                                        onclick="addObs(this.textContent)">+ <?= $tag ?></button>
                                <?php endforeach; ?>
                            </div>
                            <textarea name="observaciones" id="obsTextarea" class="form-control form-control-sm border-0 shadow-sm bg-light mb-2"
                                      rows="3" placeholder="Sin comentarios..."
                                      style="font-size:0.82rem; resize:none;"><?= htmlspecialchars($comanda['observaciones'] ?? '') ?></textarea>
                            <div class="text-end">
                                <button type="submit" class="btn btn-sm btn-primary px-3">
                                    <i class="bi bi-save me-1"></i>Guardar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Enlace kiosko -->
                <div class="pro-card border-0 mt-3 p-3">
                    <p class="small text-muted mb-2">
                        <i class="bi bi-display me-1"></i><strong>Pantalla Kiosko</strong><br>
                        Para que los comensales impriman su voucher con su RUT:
                    </p>
                    <a href="<?= BASE_URL ?>public/index.php?page=voucher/kiosko" target="_blank"
                       class="btn btn-outline-primary w-100 btn-sm">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Abrir Kiosko
                    </a>
                </div>

            </div>

        </div>

        <!-- ══════════════════════════════════════════════════ -->
        <!-- HISTORIAL DE CAMBIOS                              -->
        <!-- ══════════════════════════════════════════════════ -->
        <div class="mt-4">
            <div class="pro-card border-0">
                <div class="card-header bg-transparent py-3 px-4 d-flex justify-content-between align-items-center"
                     style="border-bottom:1px solid var(--color-border);">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-clock-history me-2 text-secondary"></i>
                        Historial de cambios
                        <?php if ($reserva): ?>
                        <span class="text-muted fw-normal small ms-1">— reserva completa</span>
                        <?php endif; ?>
                    </h5>
                    <?php if ($reserva): ?>
                    <a href="index.php?page=reserva/logCambios/<?= $reserva['id'] ?>"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-up-right-square me-1"></i>Ver página completa
                    </a>
                    <?php endif; ?>
                </div>

                <?php if (empty($cambios)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-journal-x" style="font-size:1.8rem;opacity:.3;"></i>
                    <p class="mt-2 mb-0 small">Sin cambios registrados todavía.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">Fecha y hora</th>
                                <?php if ($reserva): ?>
                                <th>Día</th>
                                <?php endif; ?>
                                <th>Cambio</th>
                                <th>Antes</th>
                                <th>Después</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $diasSemana = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
                        foreach ($cambios as $h):
                        ?>
                        <tr>
                            <td class="px-4 text-muted small text-nowrap">
                                <?= date('d/m/Y H:i', strtotime($h['created_at'])) ?>
                            </td>
                            <?php if ($reserva): ?>
                            <td class="small text-nowrap">
                                <?php if (!empty($h['fecha'])): ?>
                                <?= $diasSemana[date('w', strtotime($h['fecha']))] ?>
                                <?= date('d/m', strtotime($h['fecha'])) ?>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                            <td>
                                <span class="badge bg-light text-dark border" style="font-size:.75rem;">
                                    <?= CambioLogModel::etiquetaCampo($h['campo']) ?>
                                </span>
                            </td>
                            <td class="small">
                                <?php if ($h['valor_anterior'] !== null): ?>
                                <span class="text-danger"><?= htmlspecialchars($h['valor_anterior']) ?></span>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="small">
                                <?php if ($h['valor_nuevo'] !== null): ?>
                                <span class="text-success fw-semibold"><?= htmlspecialchars($h['valor_nuevo']) ?></span>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted text-nowrap">
                                <i class="bi bi-person me-1"></i>
                                <?= htmlspecialchars(explode('@', $h['email_usuario'])[0]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <?php include(ROOT_PATH . '../public/static/templates/footer.php'); ?>

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- MODAL: Agregar Día a la Reserva                       -->
    <!-- ══════════════════════════════════════════════════════ -->
    <?php if ($reserva): ?>
    <div class="modal fade" id="modalAgregarDia" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-plus-circle me-2 text-success"></i>Agregar día a la reserva
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="index.php?page=reserva/agregarDia">
                    <div class="modal-body px-4 py-3">
                        <input type="hidden" name="reserva_id"        value="<?= $reserva['id'] ?>">
                        <input type="hidden" name="comanda_origen_id" value="<?= $comanda['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Fecha <span class="text-danger">*</span></label>
                            <input type="date" name="fecha" class="form-control shadow-none" required
                                   min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Hora de servicio <span class="text-muted">(opcional)</span></label>
                            <input type="time" name="hora_servicio" class="form-control shadow-none"
                                   value="<?= $comanda['hora_servicio'] ? substr($comanda['hora_servicio'], 0, 5) : '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Cantidad de personas</label>
                            <input type="number" name="cantidad_personas" class="form-control shadow-none"
                                   value="<?= (int)$comanda['cantidad_personas'] ?>" min="1" required>
                        </div>
                        <div class="p-2 rounded bg-light small text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Se hereda: <strong><?= VoucherModel::etiquetaServicio($comanda['tipo_servicio']) ?></strong>
                            · <?= htmlspecialchars($comanda['nombre_hotel']) ?>
                            <?= $comanda['nombre_empresa'] ? '· ' . htmlspecialchars($comanda['nombre_empresa']) : '' ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success px-4">
                            <i class="bi bi-plus-circle me-1"></i>Agregar día
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- MODAL: Editar Comanda                                 -->
    <!-- ══════════════════════════════════════════════════════ -->
    <div class="modal fade" id="modalEditarComanda" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>Editar Comanda
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="index.php?page=voucher/guardarEdicionComanda">
                    <div class="modal-body px-4 py-3">
                        <input type="hidden" name="id" value="<?= $comanda['id'] ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Hotel</label>
                                <select name="nombre_hotel" class="form-select shadow-none">
                                    <option value="Atankalama" <?= $comanda['nombre_hotel'] === 'Atankalama' ? 'selected' : '' ?>>Atankalama</option>
                                    <option value="Atankalama Inn" <?= $comanda['nombre_hotel'] === 'Atankalama Inn' ? 'selected' : '' ?>>Atankalama Inn</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Hora de servicio</label>
                                <input type="time" name="hora_servicio" class="form-control shadow-none" 
                                       value="<?= $comanda['hora_servicio'] ? substr($comanda['hora_servicio'], 0, 5) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Cantidad de personas</label>
                                <input type="number" name="cantidad_personas" class="form-control shadow-none" 
                                       value="<?= (int)$comanda['cantidad_personas'] ?>" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Para llevar</label>
                                <select name="es_para_llevar" class="form-select shadow-none">
                                    <option value="0" <?= (int)$comanda['es_para_llevar'] === 0 ? 'selected' : '' ?>>No</option>
                                    <option value="1" <?= (int)$comanda['es_para_llevar'] === 1 ? 'selected' : '' ?>>Sí</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Empresa</label>
                                <input type="text" name="nombre_empresa" class="form-control shadow-none" 
                                       value="<?= htmlspecialchars($comanda['nombre_empresa'] ?? '') ?>" placeholder="Ej: Minera Escondida">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nombre de contacto</label>
                                <input type="text" name="nombre_contacto" class="form-control shadow-none" 
                                       value="<?= htmlspecialchars($comanda['nombre_contacto'] ?? '') ?>" placeholder="Ej: Juan Pérez">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Observaciones</label>
                                <textarea name="observaciones" class="form-control shadow-none" rows="3"><?= htmlspecialchars($comanda['observaciones'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i>Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- MODAL: Editar Cliente                                 -->
    <!-- ══════════════════════════════════════════════════════ -->
    <div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>Editar Cliente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="index.php?page=voucher/editar">
                    <div class="modal-body px-4 py-3">
                        <input type="hidden" name="id"         id="edit_id">
                        <input type="hidden" name="comanda_id" value="<?= $comanda['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">RUT <span class="text-muted">(opcional)</span></label>
                            <input type="text" name="rut" id="edit_rut" class="form-control shadow-none"
                                   placeholder="12.345.678-9" maxlength="12">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="edit_nombre" class="form-control shadow-none"
                                   placeholder="Juan Pérez" required>
                        </div>

                        <?php if ($comandasFuturas > 0): ?>
                        <div class="p-2 rounded bg-warning bg-opacity-10 border border-warning border-opacity-25">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" name="propagar" value="1" id="editPropagar">
                                <label class="form-check-label small fw-bold text-dark" for="editPropagar">
                                    <i class="bi bi-arrow-repeat me-1"></i>
                                    Aplicar cambio a los <strong><?= $comandasFuturas ?></strong> día(s) siguiente(s) de la reserva
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i>Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- MODAL: Eliminar Cliente                               -->
    <!-- ══════════════════════════════════════════════════════ -->
    <div class="modal fade" id="modalEliminarCliente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-trash me-2 text-danger"></i>Eliminar Cliente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="index.php?page=voucher/eliminar">
                    <div class="modal-body px-4 py-3">
                        <input type="hidden" name="id"         id="eliminar_id">
                        <input type="hidden" name="comanda_id" value="<?= $comanda['id'] ?>">

                        <p class="mb-3">
                            ¿Eliminar a <strong id="eliminar_nombre_display"></strong>?
                        </p>

                        <?php if ($comandasFuturas > 0): ?>
                        <div class="p-2 rounded bg-danger bg-opacity-10 border border-danger border-opacity-25">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" name="propagar" value="1" id="eliminarPropagar">
                                <label class="form-check-label small fw-bold text-dark" for="eliminarPropagar">
                                    <i class="bi bi-arrow-repeat me-1"></i>
                                    Eliminar también de los <strong><?= $comandasFuturas ?></strong> día(s) siguiente(s) de la reserva
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-trash me-1"></i>Eliminar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>public/static/voucher/clientes.js"></script>
</body>
</html>
