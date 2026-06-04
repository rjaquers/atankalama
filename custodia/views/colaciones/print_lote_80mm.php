<?php
declare(strict_types=1);


require_once __DIR__.'/../../connections/config.php';

$adicionalesTxt = '';
if (! empty($adics)) {
    $adicionalesTxt = 'Adicionales: '.implode(', ', array_map(fn($a) => $a['nombre'], $adics));
}
$total = (int)($lote['cantidad'] ?? 0);

// Assets
$qrJs = asset('/js/qrcode.min.js'); // coloca qrcode.min.js en /custodia/js/qrcode.min.js

//$_REQUEST['from_upload_id'] = (int)($_REQUEST['from_upload_id'] ?? 0);
//die($_REQUEST['from_upload_id']);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Imprimir Lote #<?=(int)($lote['id'] ?? 0)?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            color: #000;
        }

        .ticket {
            width: 72mm;
            padding: 4mm;
            border-bottom: 1px dashed #000;
            page-break-after: always;
        }

        .h1 {
            font-size: 16px;
            font-weight: 700;
            text-align: center;
        }

        .h2 {
            font-size: 13px;
            font-weight: 700;
            margin-top: 6px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }

        .small {
            font-size: 11px;
        }

        .mt {
            margin-top: 12px;
        }

        .center {
            text-align: center;
        }

        .code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 12px;
        }

        hr {
            border: 0;
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .qr-wrap {
            text-align: center;
            margin-top: 6px;
        }

        @media print {
            .no-print {
                display: none;
            }
            .qr-wrap {
                display: flex;
                justify-content: center;   /* centra horizontal */
                align-items: center;       /* opcional: centra vertical si hay alto fijo */
            }

            .qr-wrap canvas,
            .qr-wrap img,
            .qr-wrap table {
                display: block;
                margin: 0 auto;            /* por si el renderer usa <table> */
            }
        }
    </style>
    <script src="<?=h($qrJs)?>"></script>
    <script src="<?=h(asset('/js/qrcode.min.js'))?>"></script>
    <script>
        // Fallback si el archivo local no cargó
        if (typeof QRCode === 'undefined') {
            document.write('<script src="https://cdn.jsdelivr.net/npm/qrcodejs2@0.0.2/qrcode.min.js"><\/script>');
        }
    </script>
    <!-- retrasar el codigo de impresion unos segundos -->
    <script src="<?=h(asset('/js/qrcode.min.js'))?>"></script>
    <script>
        // Fallback a CDN si el archivo local no carga
        if (typeof QRCode === 'undefined') {
            document.write('<script src="https://cdn.jsdelivr.net/npm/qrcodejs2@0.0.2/qrcode.min.js"><\/script>');
        }
        // Retrasa la impresión para que el QR alcance a dibujarse
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 400);
        });
    </script>
</head>
<body onload="window.print()">
<?php
$fechaDMY = '';
if (! empty($lote['fecha_servicio'])) {
    $ts = strtotime((string)$lote['fecha_servicio']);
    if ($ts !== false) {
        $fechaDMY = date('d/m/Y', $ts);
    }
}

// en print_lote_80mm.php, antes de iterar:
$total = !empty($vchs) ? count($vchs) : (int)($lote['cantidad'] ?? 0);

?>
<?php foreach ($vchs as $v): ?>
    <?php
    // Generar QR único por voucher


// URL corta: solo el código público
    $scanParam = (string)$v['codigo_publico'];                  // ej: ATK-CL-20250826-6CEF3E
    $scanUrl = APP_URL.'/colaciones/vouchers/scan?d='.rawurlencode($scanParam);
    $qrId = 'qr-'.(int)$v['id'].'-'.(int)$v['numero_en_lote'];

    ?>
    <div class="ticket">

        <div class="h1 center" style="font-size: xxx-large;">
            <?=h($lote['empresa'] ?? '')?>
        </div>

        <div class="mt row" style="font-size: x-large;">
            <div><?=h($lote['servicio'] ?? '')?></div>
            <div>|</div>
            <div><?=h($fechaDMY)?></div>
        </div>

        <hr>

        <?php if (! empty($lote['observaciones'])): ?>
            <div class="center"><strong>OBS:</strong> <?=h($lote['observaciones'])?></div>
            <hr>
        <?php endif; ?>

        <?php if ($adicionalesTxt): ?>
            <div class="center"><?=h($adicionalesTxt)?></div>
            <hr>
        <?php endif; ?>

        <?php if (!empty($v['rut'])): ?>
            <div class='small mt'><strong>Rut:</strong> <?= h($v['rut']) ?>  </div>
        <?php endif; ?>

        <?php if (!empty($v['nombre'])): ?>
            <div class="small mt"><strong>Huésped:</strong> <?= h($v['nombre']) ?>  </div>
        <?php endif; ?>
        <?php if (!empty($v['habitacion'])): ?>
        <div class='small mt'><strong>Hab:</strong> <?= h($v['habitacion']) ?>  </div>

        <?php endif; ?>






        <div class="center mt">
            <strong>Voucher:</strong> <?=(int)$v['numero_en_lote']?>/<?=$total?>
        </div>

        <div class="qr-wrap">
            <strong>Código:</strong> <span class="code"><?=h($v['codigo_publico'])?></span>
        </div>

        <div class="qr-wrap">
            <div id="<?=h($qrId)?>"></div>
        </div>
        <script>
            (function () {
                var el = document.getElementById("<?= h($qrId) ?>");
                if (!el || typeof QRCode === 'undefined') return;
                new QRCode(el, {
                    text: "<?= h($scanUrl) ?>",
                    width: 128,
                    height: 128,
                    correctLevel: QRCode.CorrectLevel.M
                });
            })();
        </script>

        <div class="center mt small">Presente este voucher en el comedor.</div>
    </div>
<?php endforeach; ?>

<style>
    .voucher-field {
        text-align: left;
        font-family: Arial, sans-serif;
        font-size: 14px;
        margin-top: 20px;
    }

    .voucher-field .line {
        border-bottom: 1px dotted #000;
        width: 100%;
        height: 20px; /* alto visual de línea */
        margin: 10px 0;
    }
</style>
</body>
</html>
