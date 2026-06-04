<?php
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 * Monitor de Cocina — página pública sin autenticación.
 * URL: /cocina/public/monitor.php
 */
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/models/CocinaModel.php';
require_once __DIR__ . '/../app/models/ComandaModel.php';

// ── Helpers de carga de datos ────────────────────────────────────────────────
function cargarOrdenes(): array
{
    $model   = new CocinaModel();
    $data    = $model->obtenerOrdenesPendientes();
    $ordenes = $data['ordenes'];
    $cantidad = $data['cantidad'];
    $detallesAgrupados = [];
    $ids = array_column($ordenes, 'id');
    if (!empty($ids)) {
        foreach ($model->obtenerDetallesPorOrdenes($ids) as $d) {
            $detallesAgrupados[$d['orden_id']][] = $d;
        }
    }
    return compact('ordenes', 'cantidad', 'detallesAgrupados');
}

function cargarComandas(): array
{
    $cm     = new ComandaModel();
    $hoy    = date('Y-m-d');
    $manana = date('Y-m-d', strtotime('+1 day'));
    return [
        'hoy'               => $hoy,
        'manana'            => $manana,
        'almuerzos'         => $cm->obtenerPorFechaYTipo($hoy,    'almuerzo'),
        'cenas'             => $cm->obtenerPorFechaYTipo($hoy,    'cena'),
        'colaciones'        => $cm->obtenerPorFechaYTipo($hoy,    'colacion'),
        'especiales'        => $cm->obtenerPorFechaYTipo($hoy,    'colacion_especial'),
        'desayunosManana'   => $cm->obtenerPorFechaYTipo($manana, 'desayuno'),
        'resumenAlmuerzos'  => $cm->resumenPorHotel($hoy,    'almuerzo'),
        'resumenCenas'      => $cm->resumenPorHotel($hoy,    'cena'),
        'resumenColaciones' => $cm->resumenPorHotel($hoy,    'colacion'),
        'resumenEspeciales' => $cm->resumenPorHotel($hoy,    'colacion_especial'),
        'resumenDesayunos'  => $cm->resumenPorHotel($manana, 'desayuno'),
        'totalAlmuerzos'    => $cm->totalPersonas($hoy,    'almuerzo'),
        'totalCenas'        => $cm->totalPersonas($hoy,    'cena'),
        'totalColaciones'   => $cm->totalPersonas($hoy,    'colacion'),
        'totalEspeciales'   => $cm->totalPersonas($hoy,    'colacion_especial'),
        'totalDesayunos'    => $cm->totalPersonas($manana, 'desayuno'),
    ];
}

$esAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// ── AJAX: cerrar orden ───────────────────────────────────────────────────────
if ($esAjax && isset($_GET['cerrar'])) {
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    $model = new CocinaModel();
    $ok    = $model->cerrarOrden((int) $_GET['cerrar']);
    echo json_encode(['status' => $ok ? 'success' : 'error']);
    exit;
}

// ── AJAX: polling de órdenes y comandas ──────────────────────────────────────
if ($esAjax) {
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    extract(cargarOrdenes());

    ob_start();
    include __DIR__ . '/../app/views/cocina/tabla_ordenes.php';
    $htmlOrdenes = ob_get_clean();

    extract(cargarComandas());

    ob_start();
    include __DIR__ . '/../app/views/cocina/tabla_comandas.php';
    $htmlComandas = ob_get_clean();

    echo json_encode([
        'status'      => 'success',
        'cantidad'    => $cantidad,
        'html'        => $htmlOrdenes,
        'htmlComandas' => $htmlComandas,
    ]);
    exit;
}

// ── Carga inicial ────────────────────────────────────────────────────────────
extract(cargarOrdenes());
extract(cargarComandas());
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monitor de Cocina - Atankalama</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 rx=%2220%22 fill=%22%230d6efd%22/><text y=%22.9em%22 font-size=%2280%22 x=%2250%%22 text-anchor=%22middle%22 font-family=%22serif%22 fill=%22white%22 font-weight=%22bold%22>C</text></svg>">
    <link href="<?= BASE_URL ?>public/static/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>public/static/css/pro-max.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="manifest" href="<?= BASE_URL ?>public/manifest.json">
    <meta name="theme-color" content="#0d6efd">
    <script>localStorage.removeItem('darkMode');</script>
    <style>
        .parpadeo { animation: parpadear 1s infinite; }
        @keyframes parpadear { 0%,100%{opacity:1} 50%{opacity:.3} }

        .monitor-urgent-pulse {
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0% { background-color: transparent; }
            50% { background-color: rgba(220, 38, 38, 0.08); }
            100% { background-color: transparent; }
        }

        #urgent-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(220, 38, 38, 0.85); z-index: 10000;
            display: flex; align-items: center; justify-content: center;
            backdrop-filter: blur(8px); transition: opacity 0.4s cubic-bezier(0.23, 1, 0.32, 1);
            opacity: 0; pointer-events: none;
        }

        .urgent-content {
            text-align: center; color: white; transform: scale(0.9);
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes icon-bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .urgent-icon { animation: icon-bounce 1s infinite var(--ease-out-back); }
    </style>
</head>
<body class="pro-body">

<div id="urgent-overlay">
    <div class="urgent-content">
        <i class="bi bi-exclamation-triangle-fill urgent-icon" style="font-size: 8rem; display: block; margin-bottom: 1rem;"></i>
        <h1 style="font-size: 5rem; font-weight: 900; letter-spacing: -0.05em; margin: 0; text-shadow: 0 4px 12px rgba(0,0,0,0.3);">¡PEDIDO URGENTE!</h1>
        <p style="font-size: 1.8rem; opacity: 0.9; margin-top: 1rem; font-weight: 500;">Revisar la cola de pedidos inmediatamente</p>
        <button class="btn btn-light mt-5 px-5 py-3 fw-bold fs-4" style="border-radius: 16px; transition: transform 0.2s ease;" onclick="closeUrgentAlert()">
            ENTENDIDO
        </button>
    </div>
</div>

<div class="container-fluid px-4 px-xl-5 my-4" style="padding-bottom:48px;">
    <div id="contenedor-general" class="row mb-5">

        <!-- Columna izquierda: Órdenes -->
        <div class="col-4 mb-0">
            <div class="d-flex align-items-center mb-3 pb-2 border-bottom" style="border-color:var(--color-border)!important;">
                <h4 class="mb-0 fw-bold">
                    <i class="bi bi-list-task me-2" style="color:var(--color-cta)"></i>Órdenes para Cocina
                </h4>
            </div>

            <div id="contenedor-ordenes" style="<?= $cantidad > 0 ? '' : 'display:none;' ?>">
                <div id="tbody-ordenes" class="d-flex flex-column gap-4">
                    <?php include __DIR__ . '/../app/views/cocina/tabla_ordenes.php'; ?>
                </div>
            </div>

            <div id="mensaje-sin-ordenes" class="text-center py-5 mt-4" style="<?= $cantidad == 0 ? '' : 'display:none;' ?>">
                <div class="pro-card p-5 d-inline-block w-100 border-0">
                    <h2 class="mb-3" style="color:#34d399;">
                        <i class="bi bi-check-circle-fill" style="font-size:3rem;"></i><br>¡Excelente Trabajo!
                    </h2>
                    <p class="fs-5 text-muted">No hay solicitudes pendientes en cocina.</p>
                    <p class="small text-muted">La cocina está al día. El sistema se actualizará automáticamente si ingresan nuevas órdenes.</p>
                </div>
            </div>
        </div>

        <!-- Columna derecha: Comandas -->
        <div class="col-8">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom" style="border-color:var(--color-border)!important;">
                <h4 class="mb-0 fw-bold">
                    <i class="bi bi-receipt me-2" style="color:var(--color-cta)"></i>
                    <span id="comandaTitulo">Comandas</span>
                </h4>
                <div class="d-flex align-items-center gap-2">
                    <button id="btnPanelAnterior" type="button" title="Panel anterior"
                        style="background:none;border:1px solid var(--color-border);border-radius:6px;width:28px;height:28px;cursor:pointer;display:flex;align-items:center;justify-content:center;padding:0;color:var(--color-primary);transition:all .2s;">
                        <i class="bi bi-chevron-left" style="font-size:.85rem;"></i>
                    </button>
                    <div class="d-flex gap-2 mx-1" id="panelIndicadores">
                        <span class="comanda-dot active" data-panel="0" title="Almuerzos / Colaciones"
                            style="width:10px;height:10px;border-radius:50%;background:var(--color-cta);cursor:pointer;transition:all .3s;"></span>
                        <span class="comanda-dot" data-panel="1" title="Cenas"
                            style="width:10px;height:10px;border-radius:50%;background:var(--color-border);cursor:pointer;transition:all .3s;"></span>
                        <span class="comanda-dot" data-panel="2" title="Colaciones Especiales"
                            style="width:10px;height:10px;border-radius:50%;background:var(--color-border);cursor:pointer;transition:all .3s;"></span>
                        <span class="comanda-dot" data-panel="3" title="Desayunos mañana"
                            style="width:10px;height:10px;border-radius:50%;background:var(--color-border);cursor:pointer;transition:all .3s;"></span>
                    </div>
                    <button id="btnPanelSiguiente" type="button" title="Panel siguiente"
                        style="background:none;border:1px solid var(--color-border);border-radius:6px;width:28px;height:28px;cursor:pointer;display:flex;align-items:center;justify-content:center;padding:0;color:var(--color-primary);transition:all .2s;">
                        <i class="bi bi-chevron-right" style="font-size:.85rem;"></i>
                    </button>
                    <div class="ms-1" style="width:60px;height:4px;background:var(--color-border);border-radius:2px;overflow:hidden;">
                        <div id="barraProgreso" style="height:100%;width:0%;background:var(--color-cta);transition:width 1s linear;"></div>
                    </div>
                    <small class="text-muted" id="panelCounter">1 / 4</small>
                </div>
            </div>

            <div id="contenedorComandas" style="min-height:400px;">
                <?php include __DIR__ . '/../app/views/cocina/tabla_comandas.php'; ?>
            </div>
        </div>

    </div>
</div>

<!-- Logo flotante (cuando no hay órdenes) -->
<div id="logoFlotante" style="position:absolute;z-index:10;display:<?= $cantidad == 0 ? 'block' : 'none' ?>;">
    <img src="<?= BASE_URL ?>public/static/img/logoAtankalama.png" alt="Logo Atankalama" style="width:200px;">
    <img src="<?= BASE_URL ?>public/static/img/cocinero.gif" alt="Cocinero" style="width:120px;">
</div>

<!-- Audios -->
<audio id="nuevoAudio"  src="<?= BASE_URL ?>public/static/audio/nueva_orden.mp3"  preload="auto"></audio>
<audio id="alertaAudio" src="<?= BASE_URL ?>public/static/audio/orden_lenta.mp3"  preload="auto"></audio>

<!-- Reloj fijo en el pie -->
<div id="barraReloj" style="position:fixed;bottom:0;left:0;right:0;z-index:1050;background:var(--bs-primary,#0d6efd);color:#fff;padding:6px 16px;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:600;letter-spacing:.03em;box-shadow:0 -2px 8px rgba(0,0,0,.18);">
    <i class="bi bi-clock me-2" style="opacity:.85;"></i>
    <span id="relojActual"><?= date('d-m-Y H:i:s') ?></span>
</div>

<script src="<?= BASE_URL ?>public/static/css/bootstrap.bundle.min.js"></script>

<script>
var MONITOR_URL = '<?= BASE_URL ?>public/monitor.php';

// ── Lógica de Alerta Urgente ──────────────────────────────────────────────
function triggerUrgentAlert() {
    const overlay = document.getElementById('urgent-overlay');
    const content = overlay.querySelector('.urgent-content');
    
    overlay.style.opacity = '1';
    overlay.style.pointerEvents = 'all';
    content.style.transform = 'scale(1)';
    
    document.body.classList.add('monitor-urgent-pulse');
    
    // Play audio
    const audio = document.getElementById('alertaAudio');
    if (audio) {
        audio.currentTime = 0;
        audio.play().catch(function() {});
    }
}

function closeUrgentAlert() {
    const overlay = document.getElementById('urgent-overlay');
    const content = overlay.querySelector('.urgent-content');
    
    overlay.style.opacity = '0';
    overlay.style.pointerEvents = 'none';
    content.style.transform = 'scale(0.9)';
    
    document.body.classList.remove('monitor-urgent-pulse');
}

// ── Reloj ──────────────────────────────────────────────────────────────────
setInterval(function () {
    var span = document.getElementById('relojActual');
    if (!span) return;
    var now = new Date();
    span.textContent =
        now.toLocaleDateString('es-CL', {day:'2-digit', month:'2-digit', year:'numeric'})
        + ' ' + now.toLocaleTimeString('es-CL');
}, 1000);

// ── AJAX helper (compatible IE11+) ─────────────────────────────────────────
function ajaxGet(url, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
    xhr.setRequestHeader('Pragma', 'no-cache');
    xhr.onreadystatechange = function () {
        if (xhr.readyState !== 4) return;
        if (xhr.status === 200) {
            try { callback(null, JSON.parse(xhr.responseText)); }
            catch (e) { callback(e, null); }
        } else {
            callback(new Error('HTTP ' + xhr.status), null);
        }
    };
    xhr.send();
}

// ── Polling de órdenes ─────────────────────────────────────────────────────
function fetchOrdenes() {
    ajaxGet(MONITOR_URL + '?_t=' + Date.now(), function (err, data) {
        if (err || !data || data.status !== 'success') return;
        var co    = document.getElementById('contenedor-ordenes');
        var tbody = document.getElementById('tbody-ordenes');
        var msj   = document.getElementById('mensaje-sin-ordenes');
        var logo  = document.getElementById('logoFlotante');
        if (data.cantidad > 0) {
            if (co)    co.style.display    = 'block';
            if (msj)   msj.style.display   = 'none';
            if (logo)  logo.style.display  = 'none';
            if (tbody) tbody.innerHTML     = data.html;
            revisarAlertas(true);
        } else {
            if (co)   co.style.display   = 'none';
            if (msj)  msj.style.display  = 'block';
            if (logo) logo.style.display = 'block';
        }
    });
}

// ── Cerrar orden ───────────────────────────────────────────────────────────
function cerrarOrden(id) {
    if (!confirm('¿Deseas cerrar esta orden de la cocina?')) return;
    ajaxGet(MONITOR_URL + '?cerrar=' + id, function (err, data) {
        if (err) { alert('Confirme la conexión a Internet.'); return; }
        if (data && data.status === 'success') {
            fetchOrdenes();
        } else {
            alert('Hubo un error al cerrar la orden. Por favor intenta recargar la página.');
        }
    });
}

// ── Alertas sonoras ────────────────────────────────────────────────────────
function revisarAlertas(reproducir) {
    var ordenes = document.querySelectorAll('div[id^="orden-"]');
    var tieneNuevas = false, tieneMasDe10Min = false, tieneVencida = false, tieneUrgente = false;
    ordenes.forEach(function (div) {
        var min = parseInt(div.dataset.minCreacion || '0');
        if (div.dataset.vencido === '1')  tieneVencida    = true;
        if (div.dataset.urgente === '1')  tieneUrgente    = true;
        
        if (min >= 10)               tieneMasDe10Min = true;
        else                         tieneNuevas     = true;
    });
    
    if (ordenes.length > 0 && reproducir) {
        if (tieneUrgente) { triggerUrgentAlert(); return; }
        if (tieneVencida) { playAlertaVencida(); return; }
        
        var audio = tieneMasDe10Min
            ? document.getElementById('alertaAudio')
            : document.getElementById('nuevoAudio');
        if (audio) audio.play().catch(function () {});
    }
}

function playAlertaVencida() {
    try {
        var AC = window.AudioContext || window.webkitAudioContext;
        if (!AC) throw new Error();
        var ctx = new AC();
        for (var i = 0; i < 3; i++) {
            (function (i) {
                var osc = ctx.createOscillator(), gain = ctx.createGain();
                osc.type = 'square';
                osc.frequency.setValueAtTime(800 + i * 100, ctx.currentTime + i * 0.25);
                gain.gain.setValueAtTime(0.3, ctx.currentTime + i * 0.25);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + i * 0.25 + 0.15);
                osc.connect(gain); gain.connect(ctx.destination);
                osc.start(ctx.currentTime + i * 0.25);
                osc.stop(ctx.currentTime + i * 0.25 + 0.25);
            })(i);
        }
    } catch (e) {
        var a = document.getElementById('alertaAudio');
        if (a) a.play().catch(function () {});
    }
}

// ── Logo flotante ──────────────────────────────────────────────────────────
var logoPosX = Math.random() * (window.innerWidth  - 120);
var logoPosY = Math.random() * (window.innerHeight - 120);
var logoVelX = 1.5, logoVelY = 1.2;

function moverLogo() {
    var logo = document.getElementById('logoFlotante');
    if (!logo || logo.style.display === 'none') return;
    logoPosX += logoVelX; logoPosY += logoVelY;
    if (logoPosX + 120 >= window.innerWidth  || logoPosX <= 0) logoVelX *= -1;
    if (logoPosY + 120 >= window.innerHeight || logoPosY <= 0) logoVelY *= -1;
    logo.style.left = logoPosX + 'px';
    logo.style.top  = logoPosY + 'px';
}

// ── Carousel de Comandas ───────────────────────────────────────────────────
(function () {
    var INTERVALO_SEG = 30;
    var titulos = ['Almuerzos / Colaciones', 'Cenas', 'Colaciones Especiales', 'Desayunos Mañana'];
    var panelActual = 0, totalPaneles = 4, progreso = 0;
    var timerProgreso = null, timerRotacion = null;

    function mostrarPanel(idx) {
        document.querySelectorAll('.comanda-panel').forEach(function (p, i) {
            p.style.display = (i === idx) ? 'block' : 'none';
        });
        document.querySelectorAll('.comanda-dot').forEach(function (d, i) {
            d.style.background = (i === idx) ? 'var(--color-cta)' : 'var(--color-border)';
            d.style.width = d.style.height = (i === idx) ? '14px' : '10px';
        });
        var t = document.getElementById('comandaTitulo');
        var c = document.getElementById('panelCounter');
        if (t) t.textContent = titulos[idx] || 'Comandas';
        if (c) c.textContent = (idx + 1) + ' / ' + totalPaneles;
        panelActual = idx;
    }

    function iniciarProgreso() {
        progreso = 0;
        var barra = document.getElementById('barraProgreso');
        if (barra) barra.style.width = '0%';
        clearInterval(timerProgreso);
        timerProgreso = setInterval(function () {
            progreso += 100 / INTERVALO_SEG;
            if (barra) barra.style.width = Math.min(progreso, 100) + '%';
        }, 1000);
    }

    function avanzarPanel() {
        var siguiente = (panelActual + 1) % totalPaneles;
        ajaxGet(MONITOR_URL + '?_t=' + Date.now(), function (err, data) {
            if (!err && data && data.htmlComandas) {
                var c = document.getElementById('contenedorComandas');
                if (c) { c.innerHTML = data.htmlComandas; asignarClicksDots(); }
            }
            mostrarPanel(siguiente);
            iniciarProgreso();
        });
    }

    function navegarA(idx) {
        clearTimeout(timerRotacion);
        mostrarPanel(idx);
        iniciarProgreso();
        programarSiguiente();
    }

    function asignarClicksDots() {
        document.querySelectorAll('.comanda-dot').forEach(function (dot) {
            dot.addEventListener('click', function () {
                navegarA(parseInt(this.dataset.panel));
            });
        });
        var btnAnt = document.getElementById('btnPanelAnterior');
        var btnSig = document.getElementById('btnPanelSiguiente');
        if (btnAnt) btnAnt.addEventListener('click', function () {
            navegarA((panelActual - 1 + totalPaneles) % totalPaneles);
        });
        if (btnSig) btnSig.addEventListener('click', function () {
            navegarA((panelActual + 1) % totalPaneles);
        });
    }

    function programarSiguiente() {
        clearTimeout(timerRotacion);
        timerRotacion = setTimeout(function () {
            avanzarPanel();
            programarSiguiente();
        }, INTERVALO_SEG * 1000);
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!document.getElementById('contenedorComandas')) return;
        mostrarPanel(0);
        iniciarProgreso();
        asignarClicksDots();
        programarSiguiente();
        revisarAlertas(false);
        setInterval(fetchOrdenes, 30000);
        setInterval(moverLogo, 20);
    });
})();
</script>

<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('<?= BASE_URL ?>public/sw.js')
            .catch(function () {});
    }
</script>

</body>
</html>
