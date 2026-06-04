<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comandas — <?= date('d/m/Y', strtotime($fecha)) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            background: #fff;
            color: #000;
            padding: 20px;
        }
        .no-print {
            margin-bottom: 20px;
        }
        h1 { font-size: 1.4rem; font-weight: bold; }
        h4 { font-size: 1rem; font-weight: bold; margin: 16px 0 6px; border-bottom: 2px solid #333; padding-bottom: 4px; }
        h5 { font-size: 0.9rem; font-weight: bold; margin: 10px 0 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 12px; }
        th { background: #f0f0f0; border: 1px solid #bbb; padding: 5px 8px; text-align: left; font-weight: bold; }
        td { border: 1px solid #ccc; padding: 5px 8px; vertical-align: top; }
        .pax-badge { display: inline-block; background: #333; color: #fff; border-radius: 4px; padding: 1px 8px; font-size: 11px; font-weight: bold; }
        .hotel-header { display: flex; justify-content: space-between; align-items: center; }
        .tipo-section { margin-bottom: 28px; page-break-inside: avoid; }
        .hoteles-row { display: flex; gap: 20px; align-items: flex-start; }
        .hotel-col { flex: 1; min-width: 0; }
        .hotel-block { margin-bottom: 4px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .tipo-section { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" class="btn btn-primary btn-sm me-2">
        Imprimir
    </button>
    <a href="index.php?page=comanda/listado&fecha=<?= urlencode($fecha) ?>" class="btn btn-outline-secondary btn-sm">
        Volver al listado
    </a>
</div>

<h1>Comandas — <?= date('d/m/Y', strtotime($fecha)) ?></h1>

<?php
$fechaSiguiente = date('d/m/Y', strtotime($fecha . ' +1 day'));

$tipos = [
    'almuerzo'          => ['label' => 'Almuerzos',             'fecha' => date('d/m/Y', strtotime($fecha))],
    'cena'              => ['label' => 'Cenas',                 'fecha' => date('d/m/Y', strtotime($fecha))],
    'colacion'          => ['label' => 'Colaciones',            'fecha' => date('d/m/Y', strtotime($fecha))],
    'colacion_especial' => ['label' => 'Colaciones Especiales', 'fecha' => date('d/m/Y', strtotime($fecha))],
    'desayuno'          => ['label' => 'Desayunos',             'fecha' => $fechaSiguiente],
];

$porTipo = [];
foreach ($comandas as $c) {
    $porTipo[$c['tipo_servicio']][] = $c;
}

if (empty($comandas)):
?>
    <p><em>No hay comandas registradas para esta fecha.</em></p>
<?php endif; ?>

<?php foreach ($tipos as $tipo => $meta): ?>
    <?php if (empty($porTipo[$tipo])): continue; endif; ?>

    <div class="tipo-section">
        <h4><?= $meta['label'] ?> — <?= $meta['fecha'] ?></h4>

        <div class="hoteles-row">
        <?php
        $hoteles = ['Atankalama', 'Atankalama Inn'];
        foreach ($hoteles as $hotelName):
            $filas = array_filter($porTipo[$tipo], fn($c) => $c['nombre_hotel'] === $hotelName);
            if (empty($filas)): continue; endif;
            $totalPax = array_sum(array_column($filas, 'cantidad_personas'));
        ?>
        <div class="hotel-col">
            <div class="hotel-block">
                <div class="hotel-header">
                    <h5><?= strtoupper($hotelName) ?></h5>
                    <span class="pax-badge"><?= $totalPax ?> PAX</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Empresa / Solicitante</th>
                            <th style="width:70px; text-align:center;">Personas</th>
                            <th style="width:80px; text-align:center;">Hora</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filas as $c): ?>
                        <tr>
                            <td>
                                <?php if ($c['tipo_solicitante'] === 'empresa'): ?>
                                    <strong><?= htmlspecialchars($c['nombre_empresa'] ?: ($c['nombre_empresa_oficial'] ?? '—')) ?></strong>
                                    <?php if ($c['nombre_contacto']): ?>
                                        <br><small><?= htmlspecialchars($c['nombre_contacto']) ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($c['nombre_contacto'] ?: 'Particular') ?>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center; font-weight:bold; font-size:14px;">
                                <?= $c['cantidad_personas'] ?>
                            </td>
                            <td style="text-align:center;">
                                <?= $c['hora_servicio'] ? substr($c['hora_servicio'], 0, 5) . ' hrs' : '—' ?>
                            </td>
                            <td><?= htmlspecialchars($c['observaciones'] ?: '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

</body>
</html>
