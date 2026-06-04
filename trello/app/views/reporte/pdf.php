<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reporte — <?= htmlspecialchars($tablero['nombre']) ?></title>
<?php
$color      = $tablero['fondo_color'] ?? '#1e3a5f';
$hoy        = date('Y-m-d');
$semana     = date('Y-m-d', strtotime('+7 days'));
$total_t    = 0; $total_v = 0; $total_ci = 0; $total_co = 0;
foreach ($listas as $l) {
    $total_t += count($l['tarjetas']);
    foreach ($l['tarjetas'] as $t) {
        $fv2 = $t['fecha_vencimiento'] ? substr($t['fecha_vencimiento'], 0, 10) : null;
        if ($fv2 && $fv2 < $hoy) $total_v++;
        $total_ci += (int)$t['items_total'];
        $total_co += (int)$t['items_ok'];
    }
}
$pct_cl = $total_ci ? round($total_co/$total_ci*100) : null;
?>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #1e293b; background: #fff; }

/* ── Pantalla ── */
.screen-bar {
    position: fixed; top: 0; left: 0; right: 0; z-index: 999;
    background: #1e293b; color: #fff; padding: 10px 20px;
    display: flex; align-items: center; gap: 12px;
}
.screen-bar a { color: #94a3b8; font-size: 12px; text-decoration: none; }
.screen-bar a:hover { color: #fff; }
.btn-print {
    margin-left: auto;
    background: <?= htmlspecialchars($color) ?>; color: #fff; border: none;
    padding: 8px 20px; border-radius: 6px; font-size: 13px; font-weight: 600;
    cursor: pointer;
}
.report-wrap { margin-top: 50px; padding: 24px; max-width: 960px; margin-left: auto; margin-right: auto; }

/* ── Cabecera del reporte ── */
.rep-header {
    background: <?= htmlspecialchars($color) ?>; color: #fff;
    border-radius: 10px 10px 0 0; padding: 20px 24px 16px;
}
.rep-header h1 { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
.rep-header .meta { font-size: 11px; opacity: .8; }

/* ── Estadísticas ── */
.stats-bar {
    display: flex; gap: 0; border: 1px solid #e2e8f0; border-top: none;
    border-radius: 0 0 10px 10px; overflow: hidden; margin-bottom: 24px;
}
.stat-box {
    flex: 1; text-align: center; padding: 14px 8px; border-right: 1px solid #e2e8f0;
}
.stat-box:last-child { border-right: none; }
.stat-box .val { font-size: 22px; font-weight: 700; color: <?= htmlspecialchars($color) ?>; line-height: 1; }
.stat-box .lbl { font-size: 10px; color: #64748b; margin-top: 3px; text-transform: uppercase; letter-spacing: .06em; }
.stat-box.danger .val { color: #dc2626; }

/* ── Sección de lista ── */
.lista-section { margin-bottom: 28px; break-inside: avoid; }
.lista-title {
    font-size: 12px; font-weight: 700; padding: 7px 12px;
    background: <?= htmlspecialchars($color) ?>22;
    border-left: 4px solid <?= htmlspecialchars($color) ?>;
    color: <?= htmlspecialchars($color) ?>; margin-bottom: 0;
}

/* ── Tabla de tarjetas ── */
table.tarjetas {
    width: 100%; border-collapse: collapse; font-size: 10.5px;
}
table.tarjetas th {
    background: #f1f5f9; color: #475569; font-weight: 700; text-align: left;
    padding: 6px 8px; border-bottom: 2px solid #e2e8f0;
    font-size: 9.5px; text-transform: uppercase; letter-spacing: .05em;
}
table.tarjetas td {
    padding: 7px 8px; vertical-align: top; border-bottom: 1px solid #f1f5f9;
}
table.tarjetas tr:last-child td { border-bottom: none; }
table.tarjetas tr:hover td { background: #f8fafc; }
.num-cell  { color: #94a3b8; font-size: 10px; width: 36px; }
.titulo-cell { font-weight: 600; color: #1e293b; }
.desc-cell { color: #64748b; font-size: 10px; max-width: 200px; }
.badge-pill {
    display: inline-block; border-radius: 99px; padding: 1px 7px;
    font-size: 9.5px; font-weight: 600; margin: 1px;
}
.badge-vencida { background: #fee2e2; color: #b91c1c; }
.badge-hoy     { background: #fef3c7; color: #b45309; }
.badge-semana  { background: #dbeafe; color: #1d4ed8; }
.badge-futura  { background: #f1f5f9; color: #475569; }
.cl-bar-wrap { display: inline-block; width: 40px; height: 5px; background: #e2e8f0; border-radius: 3px; vertical-align: middle; margin-left: 4px; }
.cl-bar-fill { height: 100%; border-radius: 3px; background: #3b82f6; }
.cl-bar-fill.done { background: #22c55e; }
.etq-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 2px; }
.empty-lista { color: #94a3b8; font-style: italic; padding: 8px 12px; font-size: 10px; }
.member-chip {
    display: inline-block; width: 20px; height: 20px; border-radius: 50%;
    font-size: 8px; font-weight: 700; color: #fff; text-align: center;
    line-height: 20px; margin-right: 2px;
}

/* ── Footer del reporte ── */
.rep-footer { margin-top: 32px; text-align: center; color: #94a3b8; font-size: 9px; padding-top: 12px; border-top: 1px solid #e2e8f0; }

/* ── CSS de impresión ── */
@media print {
    .screen-bar { display: none !important; }
    .report-wrap { margin-top: 0 !important; padding: 0 !important; max-width: 100% !important; }
    .rep-header { border-radius: 0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .stats-bar  { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .lista-title { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    table.tarjetas th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .badge-pill { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .cl-bar-fill { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .member-chip { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .lista-section { page-break-inside: avoid; }
}
</style>
</head>
<body>

<!-- Barra de pantalla (se oculta al imprimir) -->
<div class="screen-bar">
    <a href="<?= BASE_URL ?>/tablero/ver?id=<?= (int)$tablero['id'] ?>">
        &#8592; Volver al tablero
    </a>
    <span style="color:#475569">|</span>
    <span style="font-size:12px;color:#94a3b8">
        <?= htmlspecialchars($tablero['nombre']) ?> — Reporte PDF
    </span>
    <button class="btn-print" onclick="window.print()">
        &#128438; Imprimir / Guardar como PDF
    </button>
</div>

<div class="report-wrap">

    <!-- Cabecera -->
    <div class="rep-header">
        <h1><?= htmlspecialchars($tablero['nombre']) ?></h1>
        <div class="meta">
            Área: <?= htmlspecialchars($tablero['area_nombre'] ?? '') ?>
            &nbsp;|&nbsp; Generado: <?= date('d/m/Y \a \l\a\s H:i') ?> hrs
            &nbsp;|&nbsp; © <?= date('Y') ?> Rodrigo Jaque Escobar — Hotel Atankalama
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="stats-bar">
        <div class="stat-box">
            <div class="val"><?= $total_t ?></div>
            <div class="lbl">Tarjetas totales</div>
        </div>
        <div class="stat-box">
            <div class="val"><?= count($listas) ?></div>
            <div class="lbl">Listas</div>
        </div>
        <div class="stat-box <?= $total_v > 0 ? 'danger' : '' ?>">
            <div class="val"><?= $total_v ?></div>
            <div class="lbl">Vencidas</div>
        </div>
        <div class="stat-box">
            <div class="val"><?= $pct_cl !== null ? $pct_cl . '%' : '—' ?></div>
            <div class="lbl">Checklist completado</div>
        </div>
        <div class="stat-box">
            <div class="val"><?= date('d/m/Y') ?></div>
            <div class="lbl">Fecha de corte</div>
        </div>
    </div>

    <!-- Listas y tarjetas -->
    <?php foreach ($listas as $lista): ?>
    <div class="lista-section">
        <div class="lista-title">
            <?= htmlspecialchars($lista['nombre']) ?>
            <span style="font-weight:400;opacity:.7">(<?= count($lista['tarjetas']) ?>)</span>
        </div>

        <?php if (empty($lista['tarjetas'])): ?>
            <div class="empty-lista">Sin tarjetas en esta lista.</div>
        <?php else: ?>
        <table class="tarjetas">
            <thead>
                <tr>
                    <th class="num-cell">#</th>
                    <th style="min-width:160px">Título</th>
                    <th>Descripción</th>
                    <th>Miembros</th>
                    <th>Etiquetas</th>
                    <th>Vencimiento</th>
                    <th>Checklist</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($lista['tarjetas'] as $t):
                $fv    = $t['fecha_vencimiento'] ? substr($t['fecha_vencimiento'], 0, 10) : null;
                $fv_lbl = $fv ? date('d/m/Y', strtotime($fv)) : '';
                if (!$fv) $urg = '';
                elseif ($fv < $hoy)    $urg = 'vencida';
                elseif ($fv === $hoy)  $urg = 'hoy';
                elseif ($fv <= $semana) $urg = 'semana';
                else                   $urg = 'futura';

                $cl_pct = $t['items_total'] > 0
                    ? round($t['items_ok'] / $t['items_total'] * 100) : null;
                $desc = trim(preg_replace('/\s+/', ' ', $t['descripcion'] ?? ''));
                if (strlen($desc) > 120) $desc = substr($desc, 0, 117) . '…';
            ?>
            <tr>
                <td class="num-cell"><?= (int)$t['numero'] ?></td>
                <td class="titulo-cell"><?= htmlspecialchars($t['titulo']) ?></td>
                <td class="desc-cell"><?= htmlspecialchars($desc) ?></td>
                <td>
                    <?php foreach (($t['miembros_detalle'] ?? []) as $m): ?>
                        <span class="member-chip" style="background:<?= htmlspecialchars($m['color']) ?>"
                              title="<?= htmlspecialchars($m['nombre']) ?>">
                            <?= htmlspecialchars($m['iniciales']) ?>
                        </span>
                    <?php endforeach; ?>
                </td>
                <td>
                    <?php foreach (($t['etiquetas'] ?? []) as $e): ?>
                        <span class="etq-dot" style="background:<?= htmlspecialchars($e['color']) ?>"
                              title="<?= htmlspecialchars($e['nombre']) ?>"></span>
                        <span style="font-size:9.5px;color:#475569"><?= htmlspecialchars($e['nombre']) ?></span><br>
                    <?php endforeach; ?>
                </td>
                <td>
                    <?php if ($fv_lbl): ?>
                    <span class="badge-pill badge-<?= $urg ?>"><?= $fv_lbl ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($cl_pct !== null): ?>
                    <span style="font-size:10px;color:<?= $cl_pct >= 100 ? '#16a34a' : '#1d4ed8' ?>">
                        <?= $t['items_ok'] ?>/<?= $t['items_total'] ?>
                    </span>
                    <span class="cl-bar-wrap">
                        <span class="cl-bar-fill <?= $cl_pct >= 100 ? 'done' : '' ?>"
                              style="width:<?= $cl_pct ?>%"></span>
                    </span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <div class="rep-footer">
        © <?= date('Y') ?> Rodrigo Jaque Escobar — Todos los derechos reservados.
        Se concede uso operacional de esta aplicación. El código fuente y la aplicación permanecen como propiedad exclusiva del autor.
    </div>

</div>

<script>
// Iniciar impresión automáticamente solo si viene del parámetro ?autoprint=1
if (new URLSearchParams(location.search).get('autoprint') === '1') {
    window.addEventListener('load', () => setTimeout(() => window.print(), 600));
}
</script>
</body>
</html>
