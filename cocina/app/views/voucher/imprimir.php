<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Vouchers — <?= htmlspecialchars($comanda['nombre_hotel']) ?></title>
    <link href="<?= BASE_URL ?>public/static/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <?php
    $dias  = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    $meses = ['enero','febrero','marzo','abril','mayo','junio','julio',
              'agosto','septiembre','octubre','noviembre','diciembre'];
    $ts    = strtotime($comanda['fecha']);
    $fechaTexto = $dias[date('w',$ts)] . ' ' . date('j',$ts) . ' de '
                . $meses[(int)date('n',$ts)-1] . ' de ' . date('Y',$ts);
    $etiqueta   = VoucherModel::etiquetaServicio($comanda['tipo_servicio']);
    $hora       = $comanda['hora_servicio'] ? substr($comanda['hora_servicio'],0,5).' hrs' : '';
    $logoUrl    = BASE_URL . 'public/static/img/logoAtankalama.png';
    ?>

    <link rel="stylesheet" href="<?= BASE_URL ?>public/static/voucher/imprimir.css">
</head>
<body>

    <div class="toolbar">
        <button id="btnImprimir" class="btn btn-primary btn-sm px-4 shadow-sm">
            <i class="bi bi-printer me-1"></i>Imprimir
        </button>
        <button id="btnFiltroNuevos" class="btn btn-outline-warning btn-sm px-3 shadow-sm">
            <i class="bi bi-funnel me-1"></i>Solo sin imprimir
        </button>
        <span class="text-muted small" id="contadorVouchers">
            <strong><?= htmlspecialchars($comanda['nombre_hotel']) ?></strong> &mdash;
            <?= $etiqueta ?> &mdash; <span id="nVisible"><?= count($clientes) + count($genericos) ?></span> vouchers
        </span>
        <a href="index.php?page=voucher/clientes/<?= $comanda['id'] ?>" class="btn btn-outline-secondary btn-sm ms-auto">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>

    <div class="vouchers-container">

        <?php
        $total = count($clientes) + count($genericos);
        $n     = 0;
        ?>

        <!-- Vouchers Nominales -->
        <?php foreach ($clientes as $c):
            $n++;
            $urlQr = BASE_URL . 'index.php?page=voucher/ver/' . $c['codigo'];
        ?>
        <div class="voucher-thermal" data-impreso="<?= $c['impreso'] ? '1' : '0' ?>">
            <img src="<?= $logoUrl ?>" class="logo-thermal" alt="Logo">
            <div class="hotel-name">HOTEL ATANKALAMA</div>
            <div class="service-type"><?= $etiqueta ?></div>
            
            <div class="voucher-info">
                <div class="v-nombre"><?= htmlspecialchars(mb_strtoupper($c['nombre'])) ?></div>
                <?php if ($c['empresa'] || $c['rut']): ?>
                    <div class="v-empresa">
                        <?= htmlspecialchars($c['empresa'] ?? '') ?>
                        <?= $c['rut'] ? ' · RUT '.$c['rut'] : '' ?>
                    </div>
                <?php endif; ?>
                <div class="v-fecha"><?= mb_strtoupper($fechaTexto) ?></div>
                <?php if (!empty($projectName)): ?>
                    <div class="v-proyecto" style="font-size:10pt; font-weight:bold; text-transform:uppercase; margin: 2mm 0; letter-spacing:1pt; border-bottom: 1pt dashed #000; padding-bottom: 2mm;"><?= htmlspecialchars($projectName) ?></div>
                <?php endif; ?>
                <?php if ($hora): ?>
                    <div class="v-hora">HORA: <?= $hora ?></div>
                <?php endif; ?>
            </div>

            <?php if (!empty($comanda['observaciones'])): ?>
            <div class="v-obs"><strong>OBS:</strong> <?= htmlspecialchars($comanda['observaciones']) ?></div>
            <?php endif; ?>

            <div class="qr-container">
                <div id="qr-<?= $c['codigo'] ?>" style="display:inline-block;"></div>
                <div class="v-codigo"><?= $c['codigo'] ?></div>
            </div>

            <div class="v-counter">VOUCHER <?= $n ?> / <?= $total ?></div>
            <div class="v-footer">
                Voucher Personalizado · No Transferible<br>
                Válido solo para la fecha indicada.
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Vouchers Genéricos -->
        <?php foreach ($genericos as $g):
            $n++;
            $urlQr = BASE_URL . 'index.php?page=voucher/ver/' . $g['codigo'];
        ?>
        <div class="voucher-thermal" data-impreso="<?= $g['impreso'] ? '1' : '0' ?>">
            <img src="<?= $logoUrl ?>" class="logo-thermal" alt="Logo">
            <div class="hotel-name">HOTEL ATANKALAMA</div>
            <div class="service-type"><?= $etiqueta ?> · #<?= $g['numero'] ?></div>
            
            <div class="voucher-info">
                <div class="v-nombre">VALE CONSUMO</div>
                <?php if ($comanda['nombre_empresa']): ?>
                    <div class="v-empresa"><?= htmlspecialchars($comanda['nombre_empresa']) ?></div>
                <?php endif; ?>
                <div class="v-fecha"><?= mb_strtoupper($fechaTexto) ?></div>
                <?php if (!empty($projectName)): ?>
                    <div class="v-proyecto" style="font-size:10pt; font-weight:bold; text-transform:uppercase; margin: 2mm 0; letter-spacing:1pt; border-bottom: 1pt dashed #000; padding-bottom: 2mm;"><?= htmlspecialchars($projectName) ?></div>
                <?php endif; ?>
                <?php if ($hora): ?>
                    <div class="v-hora">HORA: <?= $hora ?></div>
                <?php endif; ?>
            </div>

            <?php if (!empty($comanda['observaciones'])): ?>
            <div class="v-obs"><strong>OBS:</strong> <?= htmlspecialchars($comanda['observaciones']) ?></div>
            <?php endif; ?>

            <div class="qr-container">
                <div id="qr-<?= $g['codigo'] ?>" style="display:inline-block;"></div>
                <div class="v-codigo"><?= $g['codigo'] ?></div>
            </div>

            <div class="v-counter">VOUCHER <?= $n ?> / <?= $total ?></div>
            <div class="v-footer">
                Voucher Genérico · Portador<br>
                Válido solo para la fecha indicada.
            </div>
        </div>
        <?php endforeach; ?>

    </div>

    <script>
    const COMANDA_ID = <?= (int)$comanda['id'] ?>;
    const QR_URLS    = <?= json_encode(array_merge(
        array_map(function($c) { return ['id' => $c['codigo'], 'url' => BASE_URL . 'index.php?page=voucher/ver/' . $c['codigo']]; }, $clientes),
        array_map(function($g) { return ['id' => $g['codigo'], 'url' => BASE_URL . 'index.php?page=voucher/ver/' . $g['codigo']]; }, $genericos)
    )) ?>;
    </script>
    <script src="<?= BASE_URL ?>public/static/voucher/imprimir.js"></script>

</body>
</html>
