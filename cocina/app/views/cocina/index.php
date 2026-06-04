<!DOCTYPE html>
<html lang='es'>

<head>
    <?php 
    $pageTitle = "Monitor de Cocina";
    include(ROOT_PATH . '../public/static/templates/head.php'); 
    ?>
    <!-- Manifest PWA etc can be moved here if needed or kept in head.php, but they want identical structure -->
    <!-- Let's keep the specific PWA and audio stuff for cocina, but use the same overall layout structure -->
    <link rel="manifest" href="<?= BASE_URL ?>public/manifest.json">
    <meta name="theme-color" content="#0d6efd">
</head>

<body class='pro-body'>
    <div class='container-fluid px-4 px-xl-5 my-4' style='padding-bottom: 48px;'>
        <!-- Encabezado general -->
        <div class="row align-items-center mb-4">

            <div class="col-auto d-flex align-items-center">


            </div>
        </div>

        <div id="contenedor-general" class="row mb-5">
            <!-- Columna Izquierda: Fichas de órdenes (Reducido a 5/12) -->
            <div class="col-5 mb-0">
                <div class="d-flex align-items-center mb-3 pb-2 border-bottom"
                    style="border-color: var(--color-border) !important;">
                    <h4 class="mb-0 fw-bold"><i class="bi bi-list-task me-2" style="color: var(--color-cta)"></i>Órdenes
                        para
                        Cocina</h4>
                </div>

                <div id="contenedor-ordenes" style="<?= $cantidad > 0 ? '' : 'display:none;' ?>">
                    <div id="tbody-ordenes" class="d-flex flex-column gap-4">
                        <?php include __DIR__ . '/tabla_ordenes.php'; ?>
                    </div>
                </div>

                <!-- Mensaje cuando no hay órdenes -->
                <div id="mensaje-sin-ordenes" class="text-center py-5 mt-4"
                    style="<?= $cantidad == 0 ? '' : 'display:none;' ?>">
                    <div class="pro-card p-5 d-inline-block w-100 border-0">
                        <h2 class="mb-3" style="color: #34d399;"><i class="bi bi-check-circle-fill"
                                style="font-size: 3rem;"></i><br>¡Excelente Trabajo!</h2>
                        <p class="fs-5 text-muted">No hay solicitudes pendientes en cocina.</p>
                        <p class="small text-muted">La cocina está al día. El sistema se actualizará automáticamente
                            si ingresan nuevas órdenes.</p>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Comandas (Ampliado a 7/12) -->
            <div class="col-7">
                <!-- Cabecera con indicadores de panel -->
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom"
                    style="border-color: var(--color-border) !important;">
                    <h4 class="mb-0 fw-bold">
                        <i class="bi bi-receipt me-2" style="color: var(--color-cta)"></i>
                        <span id="comandaTitulo">Comandas</span>
                    </h4>
                    <div class="d-flex align-items-center gap-2">
                        <!-- Botón anterior -->
                        <button id="btnPanelAnterior" type="button"
                            style="background:none;border:1px solid var(--color-border);border-radius:6px;width:28px;height:28px;cursor:pointer;display:flex;align-items:center;justify-content:center;padding:0;color:var(--color-primary);transition:all .2s;"
                            title="Panel anterior">
                            <i class="bi bi-chevron-left" style="font-size:.85rem;"></i>
                        </button>
                        <!-- Indicadores de panel -->
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
                        <!-- Botón siguiente -->
                        <button id="btnPanelSiguiente" type="button"
                            style="background:none;border:1px solid var(--color-border);border-radius:6px;width:28px;height:28px;cursor:pointer;display:flex;align-items:center;justify-content:center;padding:0;color:var(--color-primary);transition:all .2s;"
                            title="Panel siguiente">
                            <i class="bi bi-chevron-right" style="font-size:.85rem;"></i>
                        </button>
                        <!-- Barra de progreso del ciclo -->
                        <div class="ms-1" style="width:60px;height:4px;background:var(--color-border);border-radius:2px;overflow:hidden;">
                            <div id="barraProgreso" style="height:100%;width:0%;background:var(--color-cta);transition:width 1s linear;"></div>
                        </div>
                        <small class="text-muted" id="panelCounter">1 / 4</small>
                    </div>
                </div>

                <!-- Contenedor de paneles -->
                <div id="contenedorComandas" style="min-height:400px;">
                    <?php include __DIR__ . '/tabla_comandas.php'; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Logo animado flotante -->
    <div id="logoFlotante" style="position:absolute; z-index:10; display:<?= $cantidad == 0 ? 'block' : 'none' ?>;">
        <img src="<?= BASE_URL ?>public/static/img/logoAtankalama.png" alt="Logo Atankalama" style="width:200px;">
        <img src="<?= BASE_URL ?>public/static/img/cocinero.gif" alt="Cocinero" style="width:120px;">
    </div>

    <!-- Audios -->
    <audio id="nuevoAudio" src="<?= BASE_URL ?>public/static/audio/nueva_orden.mp3" preload="auto"></audio>
    <audio id="alertaAudio" src="<?= BASE_URL ?>public/static/audio/orden_lenta.mp3" preload="auto"></audio>

    <!-- Barra de reloj fija en el pie -->
    <div id="barraReloj" style="
        position: fixed;
        bottom: 0; left: 0; right: 0;
        z-index: 1050;
        background: var(--bs-primary, #0d6efd);
        color: #fff;
        padding: 6px 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        box-shadow: 0 -2px 8px rgba(0,0,0,0.18);
    ">
        <i class="bi bi-clock me-2" style="opacity:.85;"></i>
        <span id="relojActual"><?= date('d-m-Y H:i:s') ?></span>
    </div>

    <?php include(ROOT_PATH . '../public/static/templates/footer.php'); ?>

    <!-- App JSON Scripts -->
    <script src="<?= BASE_URL ?>public/static/js/app.js"></script>
    <script src="<?= BASE_URL ?>public/static/js/comandas.js"></script>

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('<?= BASE_URL ?>public/sw.js')
                .then(() => console.log('✅ Service Worker registrado correctamente'))
                .catch(err => console.error('❌ Error al registrar Service Worker:', err));
        }
    </script>

    <style>
        .parpadeo {
            animation: parpadear 1s infinite;
        }

        @keyframes parpadear {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
        }
    </style>
</body>

</html>