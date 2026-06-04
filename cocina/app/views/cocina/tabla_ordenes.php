<?php foreach ($ordenes as $o):
    $fechaHora = new DateTime($o['fecha_hora']);
    $fechaSolicitud = new DateTime($o['fecha_registro']);
    $ahora = new DateTime();
    $diffMinutos = round(($fechaHora->getTimestamp() - $ahora->getTimestamp()) / 60);
    
    // Detección de urgencia por nombre o tiempo
    $nombreOriginal = $o['nombre_huesped'];
    $esUrgenteTexto = stripos($nombreOriginal, 'URGENTE') !== false;
    $nombreLimpio = trim(str_ireplace('URGENTE', '', $nombreOriginal));
    
    $esUrgente = ($diffMinutos <= 5 || $esUrgenteTexto) ? '1' : '0';
    $minDesdeCreacion = round(($ahora->getTimestamp() - $fechaSolicitud->getTimestamp()) / 60);

    // Asignación de diseño según tiempo restante
    if ($diffMinutos < 0) {
        $claseBorde = 'border-dark parpadeo';
        $textoTiempo = '¡Venc ' . abs($diffMinutos) . 'm!';
        $claseInsignia = 'bg-dark text-white fw-bold shadow-sm';
    } elseif ($diffMinutos <= 5 || $esUrgenteTexto) {
        $claseBorde = 'border-danger';
        $textoTiempo = "Falta(n) $diffMinutos m";
        $claseInsignia = 'bg-danger text-white shadow-sm';
    } elseif ($diffMinutos <= 15) {
        $claseBorde = 'border-warning';
        $textoTiempo = "Falta(n) $diffMinutos m";
        $claseInsignia = 'bg-warning text-dark shadow-sm';
    } else {
        $claseBorde = 'border-success';
        $textoTiempo = "Falta(n) $diffMinutos m";
        $claseInsignia = 'bg-success text-white shadow-sm';
    }

    $lugarColor = $o['lugar'] !== 'Comedor' ? 'text-bg-info shadow-sm' : 'text-bg-dark border shadow-sm';
    ?>
    <div id="orden-<?= $o['id'] ?>" class="pro-card border-0 border-start border-4 <?= $claseBorde ?> mb-3"
        data-urgente="<?= $esUrgente ?>" data-vencido="<?= ($diffMinutos < 0 ? 1 : 0) ?>"
        data-min-creacion="<?= $minDesdeCreacion ?>">
        <div class="card-header bg-transparent border-bottom py-2 px-3 d-flex justify-content-between align-items-center"
            style="border-color: var(--color-border) !important;">
            <div class="d-flex align-items-center">
                <?php if ($esUrgenteTexto): ?>
                    <span class="badge bg-danger parpadeo me-2 px-2 py-1 shadow-sm" 
                          style="font-size: 0.7rem; cursor: pointer;" 
                          onclick="triggerUrgentAlert()">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>URGENTE
                    </span>
                <?php endif; ?>
                <span class="badge <?= $lugarColor ?> rounded-pill px-2 py-1 me-2" style="font-size: 0.7rem;">
                    <i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($o['lugar']) ?>
                </span>
                <span class="fw-bold" style="font-size: 0.85rem; max-width: 140px; color: var(--color-primary);">
                    <i class="bi bi-person-circle me-1"
                        style="color: var(--color-cta)"></i><?= htmlspecialchars($nombreLimpio) ?>
                </span>
            </div>
            <div>
                <span class="badge <?= $claseInsignia ?> rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                    <i class="bi bi-clock-history me-1"></i><?= $textoTiempo ?>
                </span>
            </div>
        </div>

        <div class="card-body py-0 px-3 pb-2">
            <div class="row g-2 align-items-stretch">
                <!-- Columna Productos -->
                <div class="col-8">
                    <div class="rounded-3 p-2 h-100 border"
                        style="background-color: var(--color-background); border-color: var(--color-border) !important;">
                        <ul class="list-unstyled mb-0 ms-1" style="font-size: 0.75rem;">
                            <?php foreach ($detallesAgrupados[$o['id']] ?? [] as $detalle): ?>
                                <?php if ($detalle['producto'] !== 'Otro'): ?>
                                    <li class="d-flex align-items-center mb-1 text-truncate">
                                        <i class="bi bi-check2-circle me-1" style="color: var(--color-cta)"></i>
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($detalle['producto']) ?></span>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if (empty($detallesAgrupados[$o['id']])): ?>
                                <li class="text-muted fst-italic"><i class="bi bi-info-circle me-1"></i> Sin productos</li>
                            <?php endif; ?>
                        </ul>
                        <?php if (!empty($o['observaciones'])): ?>
                            <div class="mt-2 pt-2 border-top border-secondary border-opacity-25">
                                <small class="text-warning-emphasis d-block" style="font-size: 0.7rem; line-height: 1.2;">
                                    <i class="bi bi-chat-left-dots-fill me-1"></i><strong>Obs:</strong> <?= htmlspecialchars($o['observaciones']) ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Columna Detalles y Acción -->
                <div class="col-4">
                    <div class="rounded-3 p-2 h-100 border d-flex flex-column justify-content-between"
                        style="background-color: var(--color-background); border-color: var(--color-border) !important;">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted fw-semibold" style="font-size: 0.65rem;"><i
                                        class="bi bi-people-fill me-1"></i>PAX:</span>
                                <span class="fw-bold"
                                    style="font-size: 0.75rem; color: var(--color-primary);"><?= htmlspecialchars($o['cantidad_personas']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1 border-bottom pb-1"
                                style="border-color: var(--color-border) !important;">
                                <span class="text-muted fw-semibold" style="font-size: 0.65rem;"><i
                                        class="bi bi-door-closed-fill me-1"></i>Hab:</span>
                                <span class="fw-bold"
                                    style="font-size: 0.75rem; color: var(--color-primary);"><?= htmlspecialchars($o['habitacion']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="text-muted fw-semibold" style="font-size: 0.65rem;"><i
                                        class="bi bi-clock-fill me-1" style="color: var(--color-cta)"></i>Hora:</span>
                                <span class="fw-bold"
                                    style="font-size: 0.75rem; color: var(--color-cta);"><?= $fechaHora->format('H:i') ?></span>
                            </div>
                        </div>
                        <div class="mt-2 text-end">
                            <button onclick="cerrarOrden(<?= $o['id'] ?>)"
                                class="btn btn-pro-primary w-100 d-flex align-items-center justify-content-center"
                                style="font-size: 0.7rem; padding: 4px 0;">
                                <i class="bi bi-check-lg me-1"></i> Listo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>