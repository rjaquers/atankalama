<?php
// ── Helpers de fecha en español ───────────────────────────────────────────────
function diaNombreCompleto(string $fecha): string {
    $dias  = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    $meses = ['enero','febrero','marzo','abril','mayo','junio','julio',
              'agosto','septiembre','octubre','noviembre','diciembre'];
    $ts = strtotime($fecha);
    return $dias[date('w',$ts)] . ' ' . date('j',$ts) . ' de ' . $meses[(int)date('n',$ts)-1] . ' de ' . date('Y',$ts);
}

function tablaComanda(array $filas, bool $conLlevar = false, bool $conObservaciones = false, bool $mostrarHotel = true): void {
    if (empty($filas)): ?>
        <p class="text-muted small text-center py-3 mb-0">
            <i class="bi bi-inbox me-1"></i>Sin registros.
        </p>
    <?php return; endif; ?>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 align-middle" style="font-size:.88rem;">
            <thead class="table-light">
                <tr>
                    <?php if ($mostrarHotel): ?><th class="px-3">Hotel</th><?php endif; ?>
                    <th class="<?= $mostrarHotel ? '' : 'px-3' ?>">Empresa / Solicitante</th>
                    <th class="text-center">Personas</th>
                    <th class="text-center">Hora</th>
                    <?php if ($conLlevar): ?><th class="text-center">Consumo</th><?php endif; ?>
                    <?php if ($conObservaciones): ?><th>Observación</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($filas as $f): ?>
                <tr>
                    <?php if ($mostrarHotel): ?><td class="px-3 fw-semibold"><?= htmlspecialchars($f['nombre_hotel']) ?></td><?php endif; ?>
                    <td class="<?= $mostrarHotel ? '' : 'px-3' ?>">
                        <?php if ($f['tipo_solicitante'] === 'empresa'): ?>
                            <span class="fw-semibold"><?= htmlspecialchars($f['nombre_empresa'] ?: '—') ?></span>
                            <?php if ($f['nombre_contacto']): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($f['nombre_contacto']) ?></small>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($f['nombre_contacto'] ?: 'Particular') ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center fw-bold fs-6"><?= (int)$f['cantidad_personas'] ?></td>
                    <td class="text-center small">
                        <?= $f['hora_servicio'] ? substr($f['hora_servicio'],0,5).' hrs' : '<span class="text-muted">—</span>' ?>
                    </td>
                    <?php if ($conLlevar): ?>
                    <td class="text-center">
                        <?php if ((int)$f['es_para_llevar']): ?>
                            <span class="badge bg-warning text-dark"><i class="bi bi-bag-fill me-1"></i>Llevar</span>
                        <?php else: ?>
                            <span class="badge bg-success"><i class="bi bi-house-door-fill me-1"></i>Hotel</span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                    <?php if ($conObservaciones): ?>
                    <td class="small text-muted"><?= htmlspecialchars($f['observaciones'] ?: '—') ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php }

function resumenComanda(array $resumen, int $total, array $filas = [], bool $conTipos = false): void { ?>
    <div class="px-3 pb-3 pt-2 border-top" style="background:var(--color-surface);">
        <?php if ($conTipos && !empty($filas)):
            $totEmpresa   = array_sum(array_column(array_filter($filas, fn($f) => $f['tipo_solicitante']==='empresa'),  'cantidad_personas'));
            $totParticular = array_sum(array_column(array_filter($filas, fn($f) => $f['tipo_solicitante']==='particular'), 'cantidad_personas'));
            $totLlevar    = array_sum(array_column(array_filter($filas, fn($f) => (int)$f['es_para_llevar']===1), 'cantidad_personas'));
            $totHotel     = array_sum(array_column(array_filter($filas, fn($f) => (int)$f['es_para_llevar']===0), 'cantidad_personas'));
        ?>
        <div class="d-flex flex-wrap gap-2 mb-2 mt-2">
            <span class="badge bg-primary px-3"><i class="bi bi-building me-1"></i>Empresa: <?= $totEmpresa ?></span>
            <span class="badge bg-secondary px-3"><i class="bi bi-person me-1"></i>Particular: <?= $totParticular ?></span>
            <span class="badge bg-success px-3"><i class="bi bi-house-door-fill me-1"></i>En hotel: <?= $totHotel ?></span>
            <span class="badge bg-warning text-dark px-3"><i class="bi bi-bag-fill me-1"></i>Para llevar: <?= $totLlevar ?></span>
        </div>
        <?php endif; ?>
        <div class="d-flex flex-wrap gap-2 mt-1">
            <?php foreach ($resumen as $r): ?>
                <span class="badge px-3" style="background:var(--color-cta);color:#fff;">
                    <?= htmlspecialchars($r['nombre_hotel']) ?>: <?= (int)$r['total_personas'] ?>
                </span>
            <?php endforeach; ?>
            <span class="badge bg-dark px-3">
                <i class="bi bi-people-fill me-1"></i>Total: <?= $total ?>
            </span>
        </div>
    </div>
<?php }
?>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- PANEL 1: Almuerzos / Colaciones del día                    -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="comanda-panel" data-panel="0">
    <?php
    $tieneAlmuerzo = !empty($almuerzos);
    $tieneColacion = !empty($colaciones);
    $totalPanel1   = $totalAlmuerzos + $totalColaciones;
    ?>

    <?php if (!$tieneAlmuerzo && !$tieneColacion): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-sun" style="font-size:2.5rem;opacity:.4;"></i>
            <p class="mt-3 mb-0">Sin almuerzos ni colaciones registrados para hoy.</p>
        </div>
    <?php else: 
        // Agrupar todo por hotel
        $items_atan = array_merge(
            array_filter($almuerzos,  fn($f) => $f['nombre_hotel'] === 'Atankalama'),
            array_filter($colaciones, fn($f) => $f['nombre_hotel'] === 'Atankalama')
        );
        $items_inn = array_merge(
            array_filter($almuerzos,  fn($f) => $f['nombre_hotel'] === 'Atankalama Inn'),
            array_filter($colaciones, fn($f) => $f['nombre_hotel'] === 'Atankalama Inn')
        );

        $tot_atan = array_sum(array_column($items_atan, 'cantidad_personas'));
        $tot_inn  = array_sum(array_column($items_inn, 'cantidad_personas'));
    ?>
    <!-- Resumen General al inicio -->
    <div class="pro-card border-0 p-3 d-flex justify-content-between align-items-center shadow-sm mb-3" style="background: #f8fafc;">
        <div class="d-flex gap-4 align-items-center">
            <h5 class="text-muted fw-bold mb-0" style="font-size: 0.9rem; letter-spacing: 0.05em;">TOTAL HOY</h5>
            <div class="d-flex gap-2">
                <span class="badge bg-white text-primary border px-4 py-2 fs-6 shadow-sm">Atankalama: <strong class="fs-5"><?= $tot_atan ?></strong></span>
                <span class="badge bg-white text-info border px-4 py-2 fs-6 shadow-sm">Atankalama Inn: <strong class="fs-5"><?= $tot_inn ?></strong></span>
            </div>
        </div>
        <span class="badge bg-dark px-4 py-2 shadow" style="border-radius: 12px; font-size: 1.4rem;">
            <i class="bi bi-people-fill me-2"></i><?= $totalPanel1 ?> <small class="fs-6 opacity-75">PAX</small>
        </span>
    </div>

    <div class="row g-3 mb-3">
        <!-- Columna Atankalama -->
        <div class="col-md-6">
            <div class="pro-card border-0 h-100 shadow-sm" style="border-top: 4px solid var(--color-cta) !important;">
                <div class="card-header bg-transparent py-2 px-3 d-flex justify-content-between align-items-center" style="border-bottom:1px solid var(--color-border);">
                    <h6 class="fw-bold mb-0" style="color:var(--color-primary);">
                        <i class="bi bi-building me-2 text-primary"></i>ATANKALAMA
                    </h6>
                    <span class="badge bg-primary rounded-pill px-3 py-2 fs-6 shadow-sm"><?= $tot_atan ?> PAX</span>
                </div>
                <div style="min-height: 100px;">
                    <?php tablaComanda($items_atan, false, true, false); ?>
                </div>
            </div>
        </div>

        <!-- Columna Atankalama Inn -->
        <div class="col-md-6">
            <div class="pro-card border-0 h-100 shadow-sm" style="border-top: 4px solid #06b6d4 !important;">
                <div class="card-header bg-transparent py-2 px-3 d-flex justify-content-between align-items-center" style="border-bottom:1px solid var(--color-border);">
                    <h6 class="fw-bold mb-0" style="color:var(--color-primary);">
                        <i class="bi bi-building-fill me-2 text-info"></i>ATANKALAMA INN
                    </h6>
                    <span class="badge bg-info rounded-pill px-3 py-2 fs-6 shadow-sm"><?= $tot_inn ?> PAX</span>
                </div>
                <div style="min-height: 100px;">
                    <?php tablaComanda($items_inn, false, true, false); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- PANEL 2: Cenas del día                                     -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="comanda-panel" data-panel="1" style="display:none;">
    <?php if (empty($cenas)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-moon-stars" style="font-size:2.5rem;opacity:.4;"></i>
            <p class="mt-3 mb-0">Sin cenas registradas para hoy.</p>
        </div>
    <?php else: 
        $cen_atan = array_filter($cenas, fn($f) => $f['nombre_hotel'] === 'Atankalama');
        $cen_inn  = array_filter($cenas, fn($f) => $f['nombre_hotel'] === 'Atankalama Inn');
        $ct_atan  = array_sum(array_column($cen_atan, 'cantidad_personas'));
        $ct_inn   = array_sum(array_column($cen_inn, 'cantidad_personas'));
    ?>
    <!-- Resumen General -->
    <div class="pro-card border-0 p-3 d-flex justify-content-between align-items-center shadow-sm mb-3" style="background: #f8fafc;">
        <div class="d-flex gap-4 align-items-center">
            <h5 class="text-muted fw-bold mb-0" style="font-size: 0.9rem; letter-spacing: 0.05em;">TOTAL CENAS</h5>
            <div class="d-flex gap-2">
                <span class="badge bg-white text-primary border px-4 py-2 fs-6 shadow-sm">Atankalama: <strong class="fs-5"><?= $ct_atan ?></strong></span>
                <span class="badge bg-white text-info border px-4 py-2 fs-6 shadow-sm">Atankalama Inn: <strong class="fs-5"><?= $ct_inn ?></strong></span>
            </div>
        </div>
        <span class="badge bg-dark px-4 py-2 shadow" style="border-radius: 12px; font-size: 1.4rem;">
            <i class="bi bi-people-fill me-2"></i><?= $totalCenas ?> <small class="fs-6 opacity-75">PAX</small>
        </span>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="pro-card border-0 h-100 shadow-sm" style="border-top: 4px solid var(--color-cta) !important;">
                <div class="card-header bg-transparent py-2 px-3 d-flex justify-content-between align-items-center" style="border-bottom:1px solid var(--color-border);">
                    <h6 class="fw-bold mb-0" style="color:var(--color-primary);">
                        <i class="bi bi-building me-2 text-primary"></i>ATANKALAMA
                    </h6>
                    <span class="badge bg-primary rounded-pill px-3 py-2 fs-6 shadow-sm"><?= $ct_atan ?> PAX</span>
                </div>
                <div style="min-height: 100px;">
                    <?php tablaComanda($cen_atan, false, true, false); ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="pro-card border-0 h-100 shadow-sm" style="border-top: 4px solid #06b6d4 !important;">
                <div class="card-header bg-transparent py-2 px-3 d-flex justify-content-between align-items-center" style="border-bottom:1px solid var(--color-border);">
                    <h6 class="fw-bold mb-0" style="color:var(--color-primary);">
                        <i class="bi bi-building-fill me-2 text-info"></i>ATANKALAMA INN
                    </h6>
                    <span class="badge bg-info rounded-pill px-3 py-2 fs-6 shadow-sm"><?= $ct_inn ?> PAX</span>
                </div>
                <div style="min-height: 100px;">
                    <?php tablaComanda($cen_inn, false, true, false); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- PANEL 3: Colaciones Especiales                             -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="comanda-panel" data-panel="2" style="display:none;">
    <?php if (empty($especiales)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-star" style="font-size:2.5rem;opacity:.4;"></i>
            <p class="mt-3 mb-0">Sin colaciones especiales registradas para hoy.</p>
        </div>
    <?php else: 
        $esp_atan = array_filter($especiales, fn($f) => $f['nombre_hotel'] === 'Atankalama');
        $esp_inn  = array_filter($especiales, fn($f) => $f['nombre_hotel'] === 'Atankalama Inn');
        $et_atan  = array_sum(array_column($esp_atan, 'cantidad_personas'));
        $et_inn   = array_sum(array_column($esp_inn, 'cantidad_personas'));
    ?>
    <!-- Resumen General -->
    <div class="pro-card border-0 p-3 d-flex justify-content-between align-items-center shadow-sm mb-3" style="background: #f8fafc;">
        <div class="d-flex gap-4 align-items-center">
            <h5 class="text-muted fw-bold mb-0" style="font-size: 0.9rem; letter-spacing: 0.05em;">TOTAL ESPECIALES</h5>
            <div class="d-flex gap-2">
                <span class="badge bg-white text-primary border px-4 py-2 fs-6 shadow-sm">Atankalama: <strong class="fs-5"><?= $et_atan ?></strong></span>
                <span class="badge bg-white text-info border px-4 py-2 fs-6 shadow-sm">Atankalama Inn: <strong class="fs-5"><?= $et_inn ?></strong></span>
            </div>
        </div>
        <span class="badge bg-dark px-4 py-2 shadow" style="border-radius: 12px; font-size: 1.4rem;">
            <i class="bi bi-people-fill me-2"></i><?= $totalEspeciales ?> <small class="fs-6 opacity-75">PAX</small>
        </span>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="pro-card border-0 h-100 shadow-sm" style="border-top: 4px solid var(--color-cta) !important;">
                <div class="card-header bg-transparent py-2 px-3 d-flex justify-content-between align-items-center" style="border-bottom:1px solid var(--color-border);">
                    <h6 class="fw-bold mb-0" style="color:var(--color-primary);">
                        <i class="bi bi-building me-2 text-primary"></i>ATANKALAMA
                    </h6>
                    <span class="badge bg-primary rounded-pill px-3 py-2 fs-6 shadow-sm"><?= $et_atan ?> PAX</span>
                </div>
                <div style="min-height: 100px;">
                    <?php tablaComanda($esp_atan, false, true, false); ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="pro-card border-0 h-100 shadow-sm" style="border-top: 4px solid #06b6d4 !important;">
                <div class="card-header bg-transparent py-2 px-3 d-flex justify-content-between align-items-center" style="border-bottom:1px solid var(--color-border);">
                    <h6 class="fw-bold mb-0" style="color:var(--color-primary);">
                        <i class="bi bi-building-fill me-2 text-info"></i>ATANKALAMA INN
                    </h6>
                    <span class="badge bg-info rounded-pill px-3 py-2 fs-6 shadow-sm"><?= $et_inn ?> PAX</span>
                </div>
                <div style="min-height: 100px;">
                    <?php tablaComanda($esp_inn, false, true, false); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- PANEL 4: Desayunos mañana                                  -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="comanda-panel" data-panel="3" style="display:none;">
    <?php
    $des_atan       = array_filter($desayunosManana ?? [], fn($f) => $f['nombre_hotel'] === 'Atankalama');
    $des_inn        = array_filter($desayunosManana ?? [], fn($f) => $f['nombre_hotel'] === 'Atankalama Inn');
    $tot_atan       = array_sum(array_column($des_atan, 'cantidad_personas'));
    $tot_inn        = array_sum(array_column($des_inn, 'cantidad_personas'));
    $hayComandas    = !empty($desayunosManana);
    $hayMasivo      = !empty($masivoAtan ?? []) || !empty($masivoInn ?? []);
    $totalCombinado = ($totalDesayunos ?? 0) + ($totalMasivoAtan ?? 0) + ($totalMasivoInn ?? 0);

    if (!$hayComandas && !$hayMasivo): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-sun" style="font-size:2.5rem;opacity:.4;"></i>
            <p class="mt-3 mb-0">Sin desayunos registrados para mañana.</p>
        </div>
    <?php else: ?>

    <!-- Resumen General -->
    <div class="pro-card border-0 p-3 d-flex justify-content-between align-items-center shadow-sm mb-3" style="background: #f8fafc;">
        <div class="d-flex gap-4 align-items-center">
            <h5 class="text-muted fw-bold mb-0" style="font-size: 0.9rem; letter-spacing: 0.05em;">TOTAL MAÑANA</h5>
            <div class="d-flex gap-2">
                <span class="badge bg-white text-primary border px-4 py-2 fs-6 shadow-sm">
                    Atankalama: <strong class="fs-5"><?= $tot_atan + ($totalMasivoAtan ?? 0) ?></strong>
                </span>
                <span class="badge bg-white text-info border px-4 py-2 fs-6 shadow-sm">
                    Atankalama Inn: <strong class="fs-5"><?= $tot_inn + ($totalMasivoInn ?? 0) ?></strong>
                </span>
            </div>
        </div>
        <span class="badge bg-dark px-4 py-2 shadow" style="border-radius: 12px; font-size: 1.4rem;">
            <i class="bi bi-people-fill me-2"></i><?= $totalCombinado ?> <small class="fs-6 opacity-75">PAX</small>
        </span>
    </div>

    <?php if ($hayComandas): ?>
    <!-- Sección: Comandas de Desayuno (formulario clásico) -->
    <p class="text-muted fw-bold small mb-2" style="font-size:.72rem; letter-spacing:.06em;">
        <i class="bi bi-file-text me-1"></i>COMANDAS PROGRAMADAS
    </p>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="pro-card border-0 h-100 shadow-sm" style="border-top: 4px solid var(--color-cta) !important;">
                <div class="card-header bg-transparent py-2 px-3 d-flex justify-content-between align-items-center" style="border-bottom:1px solid var(--color-border);">
                    <h6 class="fw-bold mb-0" style="color:var(--color-primary);"><i class="bi bi-building me-2 text-primary"></i>ATANKALAMA</h6>
                    <span class="badge bg-primary rounded-pill px-3 py-2 fs-6 shadow-sm"><?= $tot_atan ?> PAX</span>
                </div>
                <div style="min-height: 60px;"><?php tablaComanda($des_atan, true, true, false); ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="pro-card border-0 h-100 shadow-sm" style="border-top: 4px solid #06b6d4 !important;">
                <div class="card-header bg-transparent py-2 px-3 d-flex justify-content-between align-items-center" style="border-bottom:1px solid var(--color-border);">
                    <h6 class="fw-bold mb-0" style="color:var(--color-primary);"><i class="bi bi-building-fill me-2 text-info"></i>ATANKALAMA INN</h6>
                    <span class="badge bg-info rounded-pill px-3 py-2 fs-6 shadow-sm"><?= $tot_inn ?> PAX</span>
                </div>
                <div style="min-height: 60px;"><?php tablaComanda($des_inn, true, true, false); ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($hayMasivo): ?>
    <!-- Sección: Desayunos Masivos (nueva tabla) -->
    <?php if ($hayComandas): ?><hr class="my-2" style="border-color:#e2e8f0;"><?php endif; ?>
    <p class="text-muted fw-bold small mb-2" style="font-size:.72rem; letter-spacing:.06em;">
        <i class="bi bi-table me-1"></i>DESAYUNO MASIVO
    </p>
    <div class="row g-3 mb-3">
        <?php foreach ([['Atankalama', $masivoAtan ?? [], $totalMasivoAtan ?? 0, 'primary', 'bi-building'], ['Atankalama Inn', $masivoInn ?? [], $totalMasivoInn ?? 0, 'info', 'bi-building-fill']] as [$hNombre, $hFilas, $hTotal, $hColor, $hIcon]): ?>
        <div class="col-md-6">
            <div class="pro-card border-0 h-100 shadow-sm" style="border-top: 4px solid <?= $hColor === 'primary' ? 'var(--color-cta)' : '#06b6d4' ?> !important;">
                <div class="card-header bg-transparent py-2 px-3 d-flex justify-content-between align-items-center" style="border-bottom:1px solid var(--color-border);">
                    <h6 class="fw-bold mb-0" style="color:var(--color-primary);">
                        <i class="bi <?= $hIcon ?> me-2 text-<?= $hColor ?>"></i><?= strtoupper($hNombre) ?>
                    </h6>
                    <span class="badge bg-<?= $hColor ?> rounded-pill px-3 py-2 fs-6 shadow-sm"><?= $hTotal ?> PAX</span>
                </div>
                <div style="min-height: 60px;">
                    <?php if (empty($hFilas)): ?>
                        <p class="text-muted small text-center py-3 mb-0"><i class="bi bi-inbox me-1"></i>Sin registros.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle" style="font-size:.87rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-3">Empresa</th>
                                    <th class="text-muted" style="font-size:.75rem;">Proyecto</th>
                                    <th class="text-center">PAX</th>
                                    <th>Obs.</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($hFilas as $mf): ?>
                                <tr>
                                    <td class="px-3 fw-semibold"><?= htmlspecialchars($mf['nombre_empresa']) ?></td>
                                    <td class="text-muted small"><?= htmlspecialchars($mf['nombre_proyecto'] ?: '—') ?></td>
                                    <td class="text-center fw-bold fs-6"><?= (int)$mf['cantidad'] ?></td>
                                    <td class="small text-muted"><?= htmlspecialchars($mf['observaciones'] ?: '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>
