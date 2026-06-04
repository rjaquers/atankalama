<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher Orden #
        <?= htmlspecialchars($orden['id']) ?>
    </title>
    <style>
        /* Estilos específicos para ticketera 80mm */
        @page {
            margin: 0;
            size: 80mm auto;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            margin: 0;
            padding: 5mm;
            width: 80mm;
            box-sizing: border-box;
            color: #000;
            background: #fff;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .mb-1 {
            margin-bottom: 5px;
        }

        .mb-2 {
            margin-bottom: 10px;
        }

        .mb-3 {
            margin-bottom: 15px;
        }

        .mt-1 {
            margin-top: 5px;
        }

        .mt-2 {
            margin-top: 10px;
        }

        .mt-3 {
            margin-top: 15px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: left;
            padding: 2px 0;
            vertical-align: top;
        }

        .price-col {
            text-align: right;
            width: 60px;
        }

        .qty-col {
            width: 30px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 14px;
        }

        .action-buttons {
            margin-top: 30px;
            text-align: center;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            background: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            font-family: sans-serif;
            font-size: 14px;
            margin: 5px;
            cursor: pointer;
        }

        .btn-primary {
            background: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="text-center mb-2">
        <div class="title">TICKET DE COCINA</div>
        <div class="subtitle">Orden #
            <?= str_pad($orden['id'], 5, '0', STR_PAD_LEFT) ?>
        </div>
    </div>

    <div class="mb-2">
        <div><span class="font-bold">Fecha/Hora:</span>
            <?= date('d/m/Y H:i', strtotime($orden['fecha_hora'])) ?>
        </div>
        <div><span class="font-bold">Habitación:</span>
            <?= htmlspecialchars($orden['habitacion']) ?>
        </div>
        <div><span class="font-bold">Lugar entrega:</span>
            <?= htmlspecialchars($orden['lugar']) ?>
        </div>
        <?php if (($orden['tipo_solicitante'] ?? 'particular') === 'empresa' && $empresa): ?>
            <div><span class="font-bold">Empresa:</span>
                <?= htmlspecialchars($empresa['business_name']) ?>
            </div>
            <?php if ($contrato): ?>
                <div><span class="font-bold">Contrato:</span>
                    <?= htmlspecialchars($contrato['code']) ?>
                    (<?= htmlspecialchars($contrato['contract_type']) ?>)
                </div>
            <?php endif; ?>
            <?php if (!empty($orden['nombre_contacto'])): ?>
                <div><span class="font-bold">Contacto:</span>
                    <?= htmlspecialchars($orden['nombre_contacto']) ?>
                </div>
            <?php endif; ?>
        <?php elseif (!empty($orden['nombre_huesped'])): ?>
            <div><span class="font-bold">Huésped:</span>
                <?= htmlspecialchars($orden['nombre_huesped']) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($orden['email_respaldo'])): ?>
            <div><span class="font-bold">Obs:</span>
                <?= htmlspecialchars($orden['email_respaldo']) ?>
            </div>
        <?php endif; ?>

        <div><span class="font-bold">Personas:</span>
            <?= htmlspecialchars($orden['cantidad_personas']) ?>
        </div>
    </div>

    <div class="divider"></div>
    <div class="font-bold mb-1">DETALLE PRODUCTOS</div>
    <table>
        <?php foreach ($detalles as $detalle): ?>
            <tr>
                <td class="qty-col">
                    <?= htmlspecialchars($detalle['cantidad']) ?>x
                </td>
                <td>
                    <?= htmlspecialchars($detalle['producto']) ?>
                </td>
                <td class="price-col">$
                    <?= number_format($detalle['precio'], 0, ',', '.') ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="divider"></div>
    <table>
        <tr>
            <td class="font-bold" style="font-size: 16px;">TOTAL:</td>
            <td class="font-bold price-col" style="font-size: 16px;">$
                <?= number_format($orden['total'], 0, ',', '.') ?>
            </td>
        </tr>
    </table>
    <div class="divider"></div>

    <div class="text-center mt-3">
        *** GRACIAS ***
    </div>

    <div class="action-buttons no-print">
        <button class="btn btn-primary" onclick="window.print()">Imprimir de nuevo</button>
        <a href="index.php?page=recepcion/index&ok=1" class="btn">Volver a Recepción</a>
    </div>

    <script>
        // Imprimir automáticamente al cargar
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 500); // Pequeño retraso para asegurar renderizado
        });

        // Intentar redirigir después de imprimir (o cuando la ventana recibe foco de nuevo tras cerrar el diálogo)
        window.addEventListener('afterprint', function () {
            // Se puede descomentar si se desea redirección súper automática, pero a veces falla en algunos navegadores
            // window.location.href = 'index.php?page=recepcion/index&ok=1';
        });
    </script>
</body>

</html>