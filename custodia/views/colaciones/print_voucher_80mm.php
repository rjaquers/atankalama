<?php
require_once __DIR__.'/../../connections/config.php';
?>
<!doctype html>
<html lang='es'>
<head>
    <meta charset='utf-8'>
    <title>Voucher</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
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
            font-size: 24px;
            font-weight: 700;
            text-align: center;
        }

        .h2 {
            font-size: 18px;
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
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
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
                justify-content: center; /* centra horizontal */
                align-items: center; /* opcional: centra vertical si hay alto fijo */
            }

            .qr-wrap canvas,
            .qr-wrap img,
            .qr-wrap table {
                display: block;
                margin: 0 auto; /* por si el renderer usa <table> */
            }
        }
    </style>

    <script src='/custodia/js/qrcode.min.js'></script>
</head>

<body onload='window.print()'>

<?php
$scanUrl = APP_URL.'/colaciones/vouchers/scan?d='.rawurlencode($voucher['codigo_publico']);
$qrId = 'qr-'.$voucher['id'];
?>

<div class="ticket">

<!--    <div class="h1 center"><h1><?php /*=h($empresa)*/?></h1></div>-->

    <div class='h1 center' style='font-size: xxx-large; background-color: black; color: white'>
        <?=h($empresa)?>
    </div>

    <div class="mt row">
        <div><h2 style="font-size: 18px"><?=h($servicio)?> <br><small><?=h($fechaDMY)?></small></h2></div>

    </div>

    <hr>

    <div class="small mt"><strong>RUT:</strong> <?=h($voucher['guest_rut'])?></div>
    <div class="small mt"><strong>Huésped:</strong> <?=h($voucher['guest_nombre'])?></div>
    <div class="small mt"><strong>Hab:</strong> <?=h($voucher['guest_habitacion'])?></div>

    <div class="center mt">
        <strong>Voucher:</strong> <?=$voucher['numero_en_lote']?>
    </div>

    <div class="qr-wrap">
        <strong>Código:</strong>
        <span class="code"><?=h($voucher['codigo_publico'])?></span>
    </div>

    <div class="qr-wrap">
        <div id="<?=$qrId?>"></div>
    </div>

    <script>
        new QRCode(document.getElementById("<?=$qrId?>"), {
            text: "<?=$scanUrl?>",
            width: 128,
            height: 128,
            correctLevel: QRCode.CorrectLevel.M
        });
    </script>

    <div class="center mt small">
        Presente este voucher en el comedor.
    </div>

</div>




<script>
    window.onload = function () {
        window.print();
        setTimeout(() => window.close(), 1500);
    };
</script>


</body>
</html>


