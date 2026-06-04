<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Imprimir Ticket </title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @page {
            size: 80mm auto;
            margin: 4mm;
        }

        body {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            color: #000;
        }

        .wrap {
            width: 72mm;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }

        .brand {
            font-weight: 700;
            font-size: 24px;
        }

        .title {
            font-size: 12px;
            margin-top: 2px;
        }

        .code {
            text-align: center;
            font-weight: 800;
            font-size: 40px;
            line-height: 1.1;
            margin: 8px 0;
        }

        .meta {
            text-align: center;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
            font-size: 11px;
        }

        .label {
            color: #000;
            font-size: 14px;
        }

        .value {
            text-align: right;
            max-width: 42mm;
            word-break: normal;
            font-size: 16px;
        }

        .section {
            border-top: 1px dashed #000;
            margin-top: 6px;
            padding-top: 6px;
        }

        .footer {
            text-align: center;
            border-top: 1px dashed #000;
            margin-top: 8px;
            padding-top: 6px;
            font-size: 10px;
        }

        .actions {
            margin: 10px 0;
            text-align: center;
        }

        .btn {
            display: inline-block;
            padding: 8px 10px;
            background: #2153A7;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 12px;
        }

        .muted {
            color: #333;
        }

        @media print {
            .actions {
                display: none;
            }

            .wrap {
                width: 72mm;
            }
        }
    </style>
    <script>
        // Evitar re-impresión al volver atrás (Navigation Timing L2)


        // Imprimir al cargar y volver luego
        window.addEventListener('load', function () {
            window.print();
        });

    </script>
</head>
<body>
<div class="wrap">
    <?php for ($i = 1; $i <= 5; $i++): ?>
        <div class="ticket">
            <div class="header">
                <div class="brand">www.Atankalama.com</div>
            </div>

            <div class="code">
                <img src='https://www.atankalama.com/custodia/img/smile.jpg' width='60px'>
                Wifi
                <img src='https://www.atankalama.com/custodia/img/smile.jpg' width='60px'>
            </div>

            <div class="section">
                <div class="row">Clave:</div>
                <div class="row" style="font-size: larger">Atan09876543210</div>
            </div>

            <div class="section">
                <div class="row">
                    <div class="label">Recepción Atankalama:</div>
                    <div class="value"> +569 44550791</div>
                </div>
            </div>

            <div class='section'>
                <div class='row'>
                    <div class='label'>Recepción Atankalama <br>INN:</div>
                    <div class='value'> +569 56786622</div>
                </div>
            </div>





            <div class="section">
                <div class='row' style="font-size: 16px"> facebook.com/atankalama/</div>
                <div class='row' style='font-size: 14px'> instagram.com/atankalama_hotel/</div>
            </div>


            <style>
                @page {
                    size: 80mm auto;
                    margin: 0;
                }

                .ticket {
                    width: 72mm;
                    padding: 4mm;
                    border-bottom: 1px dashed #000;
                    page-break-after: always;
                }

                /* Para que el último no deje hoja en blanco, puedes sobreescribir en el último si quieres */
            </style>
   

        </div>


    <?php endfor; ?>

    <div class="actions">
        <a class="btn" href="#" onclick="window.print();return false;">Imprimir</a>
        <a class="btn" href="<?=h($listar_url)?>" style="background:#4A5568">Volver</a>
    </div>
</div>
</body>
</html>
