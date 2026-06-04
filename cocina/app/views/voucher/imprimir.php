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

    <style>
        /* Estilos base para visualización */
        body { background: #f5f5f5; font-family: 'Courier New', Courier, monospace; margin: 0; padding: 0; }
        
        .toolbar {
            background: #fff;
            padding: 12px 24px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            gap: 12px;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* Contenedor de vouchers */
        .vouchers-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            gap: 0; /* Sin espacio entre ellos para emular rollo térmico si se desea */
        }

        /* Estilo del Voucher Térmico (80mm) */
        .voucher-thermal {
            background: #fff;
            width: 80mm;
            padding: 5mm;
            box-sizing: border-box;
            border-bottom: 1px dashed #ccc; /* Separador visual */
            text-align: center;
            color: #000;
        }

        .logo-thermal {
            max-width: 60mm;
            height: auto;
            margin-bottom: 4mm;
            filter: grayscale(1) contrast(1.5);
        }

        .hotel-name   { font-size: 16pt; font-weight: bold; letter-spacing: 2pt; margin-bottom: 3mm; }
        .service-type { font-size: 14pt; font-weight: bold; text-transform: uppercase; border: 2pt solid #000; padding: 2mm 4mm; display: inline-block; margin: 3mm 0; }

        .voucher-info { margin: 4mm 0; line-height: 1.4; }
        .v-nombre  { font-size: 13pt; font-weight: bold; margin-bottom: 2mm; }
        .v-empresa { font-size: 18pt; font-weight: bold; margin-bottom: 3mm; line-height: 1.3; }
        .v-fecha   { font-size: 11pt; font-weight: bold; }
        .v-hora    { font-size: 11pt; }

        .v-obs { font-size: 9pt; border: 1pt dashed #000; padding: 2mm 3mm; margin: 3mm 0; text-align: left; line-height: 1.4; }

        .qr-container { margin: 5mm 0 2mm; }
        .qr-container canvas { width: 65mm !important; height: 65mm !important; }

        .v-codigo  { font-size: 8pt; color: #333; margin-top: 2mm; letter-spacing: 1pt; }
        .v-counter { font-size: 13pt; font-weight: bold; letter-spacing: 2pt; margin: 3mm 0; }
        .v-footer  { font-size: 9pt; border-top: 1pt solid #000; padding-top: 3mm; margin-top: 2mm; font-style: italic; line-height: 1.6; }

        /* ─── AJUSTES DE IMPRESIÓN ─────────────────────────────────────────── */
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .vouchers-container { padding: 0; }
            .voucher-thermal {
                border-bottom: 1px solid transparent;
                page-break-after: always;
                width: 80mm;
                margin: 0;
            }
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
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
    document.addEventListener('DOMContentLoaded', function () {
        const qrOpts = {
            width: 180,
            height: 180,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        };

        // Generar QR para nominales
        <?php foreach ($clientes as $c): 
            $url = BASE_URL . 'index.php?page=voucher/ver/' . $c['codigo'];
        ?>
        new QRCode(document.getElementById('qr-<?= $c['codigo'] ?>'), { ...qrOpts, text: '<?= addslashes($url) ?>' });
        <?php endforeach; ?>

        // Generar QR para genéricos
        <?php foreach ($genericos as $g): 
            $url = BASE_URL . 'index.php?page=voucher/ver/' . $g['codigo'];
        ?>
        new QRCode(document.getElementById('qr-<?= $g['codigo'] ?>'), { ...qrOpts, text: '<?= addslashes($url) ?>' });
        <?php endforeach; ?>

        // Función para marcar como impresos
        function marcarComoImpresos() {
            const formData = new FormData();
            formData.append('comanda_id', '<?= $comanda['id'] ?>');

            fetch('index.php?page=voucher/marcarImpresos', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Tracking de impresión actualizado:', data);
            })
            .catch(error => {
                console.error('Error al actualizar tracking:', error);
            });
        }

        // Evento imprimir
        document.getElementById('btnImprimir').addEventListener('click', function() {
            marcarComoImpresos();
            window.print();
        });

        // Toggle: solo vouchers sin imprimir
        let soloNuevos = false;
        document.getElementById('btnFiltroNuevos').addEventListener('click', function () {
            soloNuevos = !soloNuevos;
            this.classList.toggle('btn-outline-warning', !soloNuevos);
            this.classList.toggle('btn-warning',         soloNuevos);
            this.innerHTML = soloNuevos
                ? '<i class="bi bi-funnel-fill me-1"></i>Mostrando sin imprimir'
                : '<i class="bi bi-funnel me-1"></i>Solo sin imprimir';

            const todos = document.querySelectorAll('.voucher-thermal');
            let visibles = 0;
            todos.forEach(v => {
                const ocultar = soloNuevos && v.dataset.impreso === '1';
                v.style.display = ocultar ? 'none' : '';
                if (!ocultar) visibles++;
            });
            document.getElementById('nVisible').textContent = visibles;
        });
    });
    </script>

</body>
</html>
