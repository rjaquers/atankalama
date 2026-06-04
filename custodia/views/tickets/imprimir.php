<?php
// views/tickets/imprimir.php
declare(strict_types=1);

function h(?string $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

// Si viene por el router, el controlador debe haber definido:
// - $ticket (array)
// - $listar_url (string, ej. '/custodia/tickets/custodia/listar')
// - $incremented = true (ya sumó impresión)
if (!isset($ticket) || !is_array($ticket)) {
    // Modo legacy: cargamos conexión/modelo y resolvemos por ?id=
    require_once __DIR__ . '/../../connections/conec6.php';
    require_once __DIR__ . '/../../models/Ticket.php';

    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $ticketModel = new Ticket();

    $ticket = $ticketModel->obtenerPorId($id);
    if (!is_array($ticket)) {
        http_response_code(404);
        echo 'Ticket no encontrado.';
        exit;
    }

    // Incremento de impresión en modo legacy
    $ticketModel->incrementarImpresion($id);
    $incremented = true;

    // URL por defecto para volver al listado (legacy)
    $listar_url = 'listar.php';
}

// Variables útiles
$public_code = $ticket['public_code'] ?? '';
$guest_name = $ticket['guest_name'] ?? '';
$item_type = $ticket['item_type'] ?? '';
$location = $ticket['location_label'] ?? '';
$mode = $ticket['mode'] ?? '';
$status = $ticket['status'] ?? '';
$notes = $ticket['notes'] ?? '';
$created_at = $ticket['created_at'] ?? '';
$retrieved_at = $ticket['retrieved_at'] ?? '';
$print_count = (int) ($ticket['print_count'] ?? 0) + (!empty($incremented) ? 1 : 0);
$empresa = 'SISCustodia';

$fecha_hoy = (new DateTime('now', new DateTimeZone('America/Santiago')))->format('Y-m-d H:i');

// Si tienes BASE_URL definida en un config, úsala para rutas absolutas de assets:
$BASE_URL = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
$logo_url = $BASE_URL . '/img/Logo-Atankalama.png';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Imprimir Ticket <?= h($public_code) ?></title>
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
            font-size: 14px;
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
            font-size: 11px;
            margin: 6px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
            font-size: 11px;
        }

        .label {
            color: #000;
        }

        .value {
            text-align: right;
            max-width: 42mm;
            word-break: break-word;
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
        (function () {
            var nav = (performance.getEntriesByType && performance.getEntriesByType('navigation')[0]) || null;
            if (nav && nav.type === 'back_forward') {
                window.location.replace('<?= h($listar_url) ?>');
            }
        })();

        // Imprimir al cargar y volver luego
        window.addEventListener('load', function () {
            window.print();
        });
        window.addEventListener('afterprint', function () {
            setTimeout(function () { window.location.replace('<?= h($listar_url) ?>'); }, 150);
        });
    </script>
</head>

<body>
    <div class="wrap">
        <?php for ($i = 1; $i <= 2; $i++): ?>
            <center><img src="<?= h($logo_url) ?>" alt="Atankalama" style="max-width:60mm"></center>
            <div class="ticket">
                <div class="header">
                    <div class="brand"><?= h($empresa) ?></div>
                    <div class="title">Comprobante de Custodia</div>
                    <div class="meta"><?= h($fecha_hoy) ?></div>
                </div>

                <div class="code"><?= h($public_code) ?></div>

                <div class="section">
                    <div class="row">
                        <div class="label">ID</div>
                        <div class="value">#<?= (int) $ticket['id'] ?></div>
                    </div>
                    <div class="row">
                        <div class="label">Modo</div>
                        <div class="value"><?= h($mode) ?></div>
                    </div>
                    <div class="row">
                        <div class="label">Estado</div>
                        <div class="value"><?= h($status) ?></div>
                    </div>

                    <div class="row">
                        <div class="label">Huésped</div>
                        <div class="value"><?= h($guest_name ?: 'sin datos') ?></div>
                    </div>
                    <div class="row">
                        <div class="label">Artículo</div>
                        <div class="value"><?= h($item_type ?: 'sin datos') ?></div>
                    </div>
                    <div class="row">
                        <div class="label">Ubicación</div>
                        <div class="value"><?= h($location ?: 'sin datos') ?></div>
                    </div>
                    <div class="row">
                        <div class="label">Notas</div>
                        <div class="value"><?= h($notes ?: 'sin datos') ?></div>
                    </div>

                    <div class="row">
                        <div class="label">Creado</div>
                        <div class="value"><?= h($created_at) ?></div>
                    </div>
                    <div class="row">
                        <div class="label">Retiro</div>
                        <div class="value"><?= h($retrieved_at ?: '—') ?></div>
                    </div>
                    <div class="row">
                        <div class="label">Impresiones</div>
                        <div class="value"><?= (int) $print_count ?></div>
                    </div>
                </div>

                <?php if ($i === 1): ?>
                    <div class="footer">
                        <?php if ($mode === 'custodia') { ?>
                            <p>
                                Presente este comprobante para retirar su artículo.<br>
                                El hotel recibe en custodia el objeto descrito de forma temporal y sin inspección de su
                                contenido. La responsabilidad se limita a la guarda razonable del bien y no cubre dinero,
                                joyas u objetos de alto valor no declarados. El depósito es por un máximo de
                                12 horas salvo acuerdo escrito. La entrega se realiza contra presentación del ticket.
                            </p>
                        <?php } else { ?>
                            <p>
                                Este objeto ha sido declarado encontrado o perdido por un huésped. la fecha limite para
                                ser retirado es
                                <?= empty($created_at) ? '—' : h((new DateTime($created_at))->modify('+90 days')->format('Y-m-d H:i')) ?>
                            </p>
                        <?php } ?>
                    </div>
                <?php endif; ?>

                <?php if ($i === 2): ?>
                    <div class="footer">
                        <?php if ($mode !== 'custodia') { ?>
                            <p>
                                Si este objeto ha sido declarado perdido o encontrado por una persona. La fecha limite para
                                ser retirado es
                                <?= empty($created_at) ? '—' : h((new DateTime($created_at))->modify('+90 days')->format('Y-m-d H:i')) ?>
                            </p>
                        <?php } ?>
                    </div>
                <?php endif; ?>

                <div class="muted" style="text-align:center; padding-top: 10px;">Gracias por su preferencia</div>
                <div class="muted" style="text-align:center;"><?= $i === 1 ? '(Cliente)' : '(Maleta)' ?></div>
            </div> <!-- End .ticket -->

            <?php if ($i === 1): ?>
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
                <div class="separator" style="text-align:center;">-------------------------------</div>
            <?php endif; ?>

        <?php endfor; ?>

        <div class="actions">
            <a class="btn" href="#" onclick="window.print();return false;">Imprimir</a>
            <a class="btn" href="<?= h($listar_url) ?>" style="background:#4A5568">Volver</a>
        </div>
    </div>
</body>

</html>