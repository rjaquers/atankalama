<?php
// views/tickets/form.php
declare(strict_types=1);
date_default_timezone_set('America/Santiago');

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

/** Conexión: usa SIEMPRE el mismo archivo */
require_once __DIR__ . '/../../connections/conec6.php';

/** Normaliza la variable de conexión a $conn */
if (!isset($conn) || !($conn instanceof mysqli)) {
    if (isset($mysqli) && $mysqli instanceof mysqli) {
        $conn = $mysqli;
    } elseif (isset($db) && $db instanceof mysqli) {
        $conn = $db;
    } else {
        http_response_code(500);
        exit('No hay conexión MySQLi disponible ($conn/$mysqli/$db). Revisa connections/conec6.php');
    }
}

/** Generar ID tipo BAG-##### (5 dígitos aleatorios) */
$secuencia = str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
$ticketId  = "BAG-{$secuencia}";

/** Guardar (vía GET, id_formulario=100) */
if (isset($_GET['id_formulario']) && (int) $_GET['id_formulario'] === 100) {
    // Datos
    $ticketId  = trim((string) ($_GET['ticketId'] ?? ''));
    $mode      = trim((string) ($_GET['mode'] ?? 'custodia'));
    $guestName = trim((string) ($_GET['guestName'] ?? ''));
    $itemType  = trim((string) ($_GET['itemType'] ?? ''));
    $location  = trim((string) ($_GET['location'] ?? ''));
    $notes     = trim((string) ($_GET['notes'] ?? ''));

    if ($ticketId === '') {
        http_response_code(400);
        exit('ticketId requerido');
    }
    if ($mode !== 'custodia' && $mode !== 'perdido') {
        $mode = 'custodia';
    }

    $status    = 'en_custodia';
    $createdAt = (new DateTime('now', new DateTimeZone('America/Santiago')))->format('Y-m-d H:i:s');

    $sql = 'INSERT INTO `tickets`
            (`public_code`, `mode`, `guest_name`, `item_type`, `location_label`, `notes`, `status`, `created_at`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        http_response_code(500);
        exit('Prepare failed: ' . mysqli_error($conn));
    }

    if (!mysqli_stmt_bind_param(
        $stmt,
        'ssssssss',
        $ticketId,   // public_code
        $mode,       // mode
        $guestName,  // guest_name
        $itemType,   // item_type
        $location,   // location_label
        $notes,      // notes
        $status,     // status
        $createdAt   // created_at
    )) {
        $err = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        http_response_code(500);
        exit("Bind failed: $err");
    }

    if (!mysqli_stmt_execute($stmt)) {
        $err = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        http_response_code(500);
        exit("Execute failed: $err");
    }

    mysqli_stmt_close($stmt);
    // (Puedes no cerrar $conn si la app sigue usando la conexión)
    // mysqli_close($conn);

    // Redirección limpia al listado (ruta del router)
    $qs = http_build_query([
                               'imprimir'  => 1,
                               'tid'       => $ticketId,
                               'ticketId'  => $ticketId,
                               'mode'      => $mode,
                               'guestName' => $guestName,
                               'itemType'  => $itemType,
                               'location'  => $location,
                               'notes'     => $notes,
                           ]);

    // Si tienes helper url() disponible por config.php:
    $baseList = function_exists('url') ? url('/tickets/custodia/listar') : '/custodia/tickets/custodia/listar';
    header('Location: ' . $baseList . '?' . $qs);
    exit;
}

/** Helpers de rutas para assets y action del form (funcionan con o sin config.php) */
$BASE_URL   = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '/custodia';
$formAction = function_exists('url') ? url('/tickets/custodia/crear') : ($BASE_URL . '/tickets/custodia/crear');
$limpiarUrl = $formAction; // limpiar = volver a la misma ruta
$logoUrl    = $BASE_URL . '/img/Logo-Atankalama.png';

include __DIR__ . '/../../includes/header.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($title) ? htmlspecialchars($title, ENT_QUOTES, 'UTF-8') : 'Ticket'; ?></title>
    <style>
        :root { --muted:#555; --accent:#0a66c2; }
        * { box-sizing: border-box; }
        body { font-family:-apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:0; padding:20px; background:#f7f7f7; color:#111; }
        .terms { font-size:12px; color:#333; line-height:1.35; }
        .signs { display:grid; grid-template-columns:1fr 1fr; gap:12px; align-items:end; }
        .sign { border-top:1px solid #aaa; padding-top:4px; text-align:center; font-size:12px; color:#444; }
        .page { background:#fff; margin:0 auto; max-width:780px; border:1px solid #ddd; border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,.08); overflow:hidden; }
        header { display:flex; justify-content:space-between; align-items:center; padding:12px 16px; border-bottom:1px solid #eee; background:#fafafa; }
        .controls a, .controls input[type=submit] { border:1px solid #ccc; padding:8px 12px; border-radius:8px; background:#fff; cursor:pointer; text-decoration:none; color:#111; }
        .controls .primary { border-color:var(--accent); background:var(--accent); color:#fff; }
        .wrapper { padding:12px 16px 16px; }
        .ticket { border:1px solid #ddd; border-radius:12px; padding:12px; }
        .dual { display:grid; grid-template-rows:1fr auto 1fr; gap:8px; }
        .meta { display:flex; justify-content:space-between; border:1px dashed #bbb; padding:8px 10px; border-radius:10px; background:#fcfcfc; }
        .num { font-weight:800; font-size:20px; letter-spacing:.5px; }
        label { font-size:12px; color:var(--muted); display:block; margin-bottom:4px; }
        input[type="text"], textarea, select { width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:14px; }
        textarea { resize:vertical; min-height:70px; }
        .grid { display:grid; gap:10px; }
        .grid.cols-2 { grid-template-columns:1fr 1fr; }
        .row { display:flex; gap:10px; align-items:center; color:var(--muted); }
        .badge { display:inline-block; font-size:12px; border:1px solid #bbb; border-radius:999px; padding:4px 8px; background:#f0f0f0; color:#333; }
        .big-number { margin-top:30px; font-size:120px; font-weight:900; text-align:center; letter-spacing:2px; line-height:1; }
        .only-print { display:none; }
        @media print {
            header, .no-print { display:none !important; }
            .page { border:none; box-shadow:none; border-radius:0; }
            .wrapper { padding:0; }
            .ticket { break-inside:avoid; }
            .only-print { display:block; }
        }
    </style>
</head>
<body>
<div class="page">
    <form accept-charset="utf-8"
          name="custodia"
          action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>"
          enctype="multipart/form-data"
          method="get">
        <header class="no-print">
            <div>
                <strong>
                    <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Logo" onerror="this.style.display='none'">
                    Ticket de Custodia / Objetos Perdidos
                </strong>
                <span id="status" style="color:#0a66c2; font-size:12px; margin-left:8px;"></span>
            </div>
            <div class="controls">
                <a href="<?= htmlspecialchars($limpiarUrl, ENT_QUOTES, 'UTF-8') ?>">Limpiar</a>
                <input type="submit" value="Guardar" name="Guardar" id="guardar" class="primary">
            </div>
        </header>

        <div class="wrapper">
            <div class="dual">
                <!-- Copia A -->
                <section class="ticket">
                    <div class="meta">
                        <div>
                            <div class="muted">N° Ticket A</div>
                            <div id="ticketNumberA">
                                <?php
                                if (isset($_GET['ticketId'])) {
                                    echo htmlspecialchars((string)$_GET['ticketId'], ENT_QUOTES, 'UTF-8');
                                } else {
                                    echo htmlspecialchars($ticketId, ENT_QUOTES, 'UTF-8');
                                }
                                ?>
                            </div>
                        </div>
                        <div class="right">
                            <div class="muted">Fecha y hora</div>
                            <div id="timestampA"><?= date('d-m-Y H:i') ?></div>
                        </div>
                    </div>

                    <div class="row" style="margin-top:10px;">
                        <span class="badge" id="modeBadge">Custodia</span>
                        <div>Seleccione si es "Objeto perdido" cuando corresponda.</div>
                    </div>

                    <div class="grid cols-2" style="margin-top:10px;">
                        <div>
                            <label>Nombre del huésped</label>
                            <input id="guestName" name="guestName" type="text" placeholder="Ej: Juan Pérez"
                                   value="<?= isset($_GET['guestName']) ? htmlspecialchars((string)$_GET['guestName'], ENT_QUOTES, 'UTF-8') : '' ?>">
                        </div>
                        <div>
                            <label>Tipo de objeto (opcional)</label>
                            <input id="itemType" name="itemType" type="text" placeholder="Ej: Maleta, Mochila, Bolsa…"
                                   value="<?= isset($_GET['itemType']) ? htmlspecialchars((string)$_GET['itemType'], ENT_QUOTES, 'UTF-8') : '' ?>">
                        </div>
                    </div>

                    <div class="grid cols-2">
                        <div>
                            <label>Posición / Ubicación (opcional)</label>
                            <input id="location" name="location" type="text" placeholder="Ej: Estante B-03"
                                   value="<?= isset($_GET['location']) ? htmlspecialchars((string)$_GET['location'], ENT_QUOTES, 'UTF-8') : '' ?>">
                        </div>
                        <div>
                            <label>Modo</label>
                            <select id="mode" name="mode">
                                <option value="custodia" <?= (isset($_GET['mode']) && $_GET['mode'] === 'perdido') ? '' : 'selected' ?>>Custodia temporal</option>
                                <option value="perdido"  <?= (isset($_GET['mode']) && $_GET['mode'] === 'perdido') ? 'selected' : '' ?>>Objeto perdido</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label>Observaciones (opcional)</label>
                        <textarea id="notes" name="notes" placeholder="Daños visibles, sellos, color, marca, cantidad de bultos, etc."><?=
                            isset($_GET['notes']) ? htmlspecialchars((string)$_GET['notes'], ENT_QUOTES, 'UTF-8') : ''
                            ?></textarea>
                    </div>

                    <div class="terms">
                        <strong>Condiciones:</strong> El hotel recibe en custodia el objeto descrito de forma temporal y sin inspección de su contenido.
                        La responsabilidad se limita a la guarda razonable del bien y no cubre dinero, joyas u objetos de alto valor no declarados.
                        El depósito es por un máximo de 24 horas salvo acuerdo escrito. La entrega se realiza contra presentación del ticket.
                    </div>

                    <div class="signs" style="margin-top:10px;">
                        <div>
                            <label>Nombre y firma del huésped / receptor</label>
                            <div class="sign">Nombre y firma</div>
                        </div>
                        <div>
                            <label>Nombre y firma del personal</label>
                            <div class="sign">Nombre y firma</div>
                        </div>
                    </div>
                </section>

                <!-- Copia B (Cliente) -->
                <section class="ticket">
                    <div class="meta">
                        <div>
                            <div class="muted">N° Ticket</div>
                            <div class="num" id="ticketNumberB"><?= htmlspecialchars($ticketId, ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <div class="right">
                            <div class="muted">Fecha y hora</div>
                            <div id="timestampB"><?= date('d-m-Y H:i') ?></div>
                        </div>
                    </div>

                    <div class="row" style="margin-top:10px;">
                        <span class="badge" id="modeBadgeB">Custodia</span>
                        <div>Copia para el cliente</div>
                    </div>

                    <div class="grid cols-2" style="margin-top:10px;">
                        <div>
                            <label>Nombre del huésped</label>
                            <div id="guestNameB">—</div>
                        </div>
                        <div>
                            <label>Tipo de objeto</label>
                            <div id="itemTypeB">—</div>
                        </div>
                    </div>

                    <div class="grid cols-2">
                        <div>
                            <label>Posición / Ubicación</label>
                            <div id="locationB">—</div>
                        </div>
                        <div>
                            <label>Modo</label>
                            <div id="modeB">Custodia temporal</div>
                        </div>
                    </div>

                    <div>
                        <label>Observaciones</label>
                        <div id="notesB">—</div>
                    </div>

                    <div class="terms">
                        <strong>Condiciones:</strong> El hotel recibe en custodia el objeto descrito de forma temporal y sin inspección de su contenido.
                        La responsabilidad se limita a la guarda razonable del bien y no cubre dinero, joyas u objetos de alto valor no declarados.
                        El depósito es por un máximo de 24 horas salvo acuerdo escrito. La entrega se realiza contra presentación del ticket.
                    </div>

                    <div class="big-number" id="bigNumber">
                        <?= htmlspecialchars(substr($ticketId, -5), ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </section>
            </div>
        </div>

        <input type="hidden" name="id_formulario" value="100">
        <input type="hidden" name="imprimir" value="1">
        <input type="hidden" name="ticketId" value="<?= htmlspecialchars($ticketId, ENT_QUOTES, 'UTF-8') ?>">
    </form>
</div>

<script>
    (function() {
        function mirror(srcId, dstId, transform) {
            var s = document.getElementById(srcId), d = document.getElementById(dstId);
            if (!s || !d) return;
            function u() { d.textContent = transform ? transform(s.value) : (s.value || '—'); }
            s.addEventListener('input', u); u();
        }

        function setModeBadge(val) {
            var a = document.getElementById('modeBadge');
            var b = document.getElementById('modeBadgeB');
            var mb = document.getElementById('modeB');
            if (a) a.textContent = (val === 'perdido') ? 'Objeto perdido' : 'Custodia';
            if (b) b.textContent = (val === 'perdido') ? 'Objeto perdido' : 'Custodia temporal';
            if (mb) mb.textContent = (val === 'perdido') ? 'Objeto perdido' : 'Custodia temporal';
        }

        function lastFive(code) {
            var part = code.slice(code.lastIndexOf('-') + 1);
            var digits = part.replace(/\D/g, '');
            return (digits || '0').padStart(5, '0').slice(-5);
        }

        window.addEventListener('DOMContentLoaded', function () {
            mirror('guestName', 'guestNameB');
            mirror('itemType', 'itemTypeB');
            mirror('location', 'locationB');
            mirror('notes', 'notesB');

            var mode = document.getElementById('mode');
            if (mode) {
                setModeBadge(mode.value);
                mode.addEventListener('change', function (e) {
                    setModeBadge(e.target.value);
                });
            }

            var tka = document.getElementById('ticketNumberA');
            var tkb = document.getElementById('ticketNumberB');
            var big = document.getElementById('bigNumber');
            if (tka && tkb && big) {
                var code = tka.textContent || tkb.textContent || '';
                big.textContent = lastFive(code);
            }

            // Habilitar impresión si viene imprimir=1 (por si lo necesitas)
            var params = new URLSearchParams(window.location.search);
            if (params.get('imprimir') === '1') {
                // aquí podrías habilitar un botón de imprimir si existiera
            }
        });
    })();
</script>
</body>
</html>
