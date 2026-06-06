<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher — <?= htmlspecialchars($cliente['nombre']) ?></title>
    <link href="<?= BASE_URL ?>public/static/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <?php
    $dias  = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    $meses = ['enero','febrero','marzo','abril','mayo','junio','julio',
              'agosto','septiembre','octubre','noviembre','diciembre'];
    $ts         = strtotime($comanda['fecha']);
    $fechaTexto = $dias[date('w',$ts)] . ' ' . date('j',$ts) . ' de '
                . $meses[(int)date('n',$ts)-1] . ' de ' . date('Y',$ts);
    $etiqueta   = VoucherModel::etiquetaServicio($comanda['tipo_servicio']);
    $hora       = $comanda['hora_servicio'] ? substr($comanda['hora_servicio'],0,5).' hrs' : '';
    $logoUrl    = BASE_URL . 'public/static/img/logoAtankalama.png';
    $urlQr      = BASE_URL . 'index.php?page=voucher/ver/' . $cliente['codigo'];
    ?>

    <link rel="stylesheet" href="<?= BASE_URL ?>public/static/voucher/imprimir_uno.css">
</head>
<body>

    <div class="toolbar">
        <button id="btnImprimir" class="btn btn-primary btn-sm px-4 shadow-sm">
            <i class="bi bi-printer me-1"></i>Imprimir
        </button>
        <span class="text-muted small">
            <strong><?= htmlspecialchars($cliente['nombre']) ?></strong> &mdash; <?= $etiqueta ?>
        </span>
        <a href="index.php?page=voucher/clientes/<?= $comanda['id'] ?>" class="btn btn-outline-secondary btn-sm ms-auto">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>

    <div class="vouchers-container">
        <div class="voucher-thermal">
            <img src="<?= $logoUrl ?>" class="logo-thermal" alt="Logo">
            <div class="hotel-name">HOTEL ATANKALAMA</div>
            <div class="service-type"><?= $etiqueta ?></div>

            <div class="voucher-info">
                <div class="v-nombre"><?= htmlspecialchars(mb_strtoupper($cliente['nombre'])) ?></div>
                <?php if ($cliente['empresa']): ?>
                    <div class="v-empresa">
                        <?= htmlspecialchars($cliente['empresa']) ?>
                    </div>
                <?php endif; ?>
                <div class="v-fecha"><?= mb_strtoupper($fechaTexto) ?></div>
                <?php if ($hora): ?>
                    <div class="v-hora">HORA: <?= $hora ?></div>
                <?php endif; ?>
            </div>

            <?php if (!empty($comanda['observaciones'])): ?>
            <div class="v-obs"><strong>OBS:</strong> <?= htmlspecialchars($comanda['observaciones']) ?></div>
            <?php endif; ?>

            <div class="qr-container">
                <div id="qr-voucher" style="display:inline-block;"></div>
                <div class="v-codigo"><?= $cliente['codigo'] ?></div>
            </div>

            <div class="v-footer">
                Voucher Personalizado · No Transferible<br>
                Válido solo para la fecha indicada.
            </div>
        </div>
    </div>

    <script>const VOUCHER_QR_URL = '<?= addslashes($urlQr) ?>';</script>
    <script src="<?= BASE_URL ?>public/static/voucher/imprimir_uno.js"></script>

</body>
</html>
