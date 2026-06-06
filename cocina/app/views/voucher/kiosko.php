<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosko Vouchers — Hotel Atankalama</title>
    <link href="<?= BASE_URL ?>public/static/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/static/voucher/kiosko.css">
</head>
<body>

    <!-- ═══════════════ RELOJ ═══════════════ -->
    <div id="reloj-kiosko">
        <div id="reloj-hora">00:00:00</div>
        <div id="reloj-fecha"></div>
    </div>

    <!-- ═══════════════ PANTALLA DE BÚSQUEDA ═══════════════ -->
    <div id="pantalla-busqueda">
        <img src="<?= BASE_URL ?>public/static/img/logoAtankalama.png"
             class="kiosko-logo" alt="Hotel Atankalama">

        <h1 class="kiosko-titulo">Imprima su Voucher</h1>
        <p class="kiosko-subtitulo">Ingrese su RUT para buscar sus servicios registrados</p>

        <div class="kiosko-card">
            <p class="kiosko-label">RUT (Sin puntos ni guión)</p>
            <div class="rut-container">
                <input type="text" id="rut-field"
                       placeholder="12345678"
                       autocomplete="off"
                       inputmode="numeric"
                       maxlength="8">
                <span class="rut-separator">-</span>
                <input type="text" id="dv-field"
                       placeholder="?"
                       readonly
                       tabindex="-1">
            </div>
            <div id="mensaje-error"></div>
            <div id="btn-enter-visual" onclick="manejarBoton()">
                <span id="btn-enter-texto">Presione Enter para buscar</span>
                <i class="bi bi-arrow-return-left" id="btn-enter-icono"></i>
            </div>
        </div>
    </div>

    <!-- ═══════════════ PANTALLA DE RESULTADOS ═══════════════ -->
    <div id="pantalla-resultados">
        <div class="res-header">
            <img src="<?= BASE_URL ?>public/static/img/logoAtankalama.png" alt="Atankalama">
            <span class="res-nombre" id="res-nombre-usuario"></span>
            <div id="aviso-retorno">
                <i class="bi bi-check-circle"></i>
                <span>Impreso · volviendo en <strong id="cuenta-regresiva">6</strong>s</span>
                <button onclick="cancelarRetorno()">Quedarme</button>
            </div>
            <button class="btn-volver" onclick="volverBusqueda()">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </button>
        </div>
        <div class="res-body" id="res-lista"></div>
        <div id="msg-agradecimiento">
            <i class="bi bi-heart-fill me-2" style="color:#e25c7a;"></i>
            Gracias por usar los servicios de alimentación de Hotel Atankalama
        </div>
    </div>

    <!-- ═══════════════ OVERLAY DE CARGA ═══════════════ -->
    <div id="overlay-carga">
        <div class="spinner-border"></div>
        <p>Buscando...</p>
    </div>

    <!-- ═══════════════ OVERLAY DE IMPRESIÓN ═══════════════ -->
    <div id="overlay-impresion">
        <i class="bi bi-printer printer-icon-anim"></i>
        <h2 class="printing-text">Imprimiendo su voucher</h2>
        <p class="printing-subtext">Por favor espere un momento...</p>
    </div>

    <!-- ═══════════════ BANNER REFRESCO ═══════════════ -->
    <div id="banner-refresco">
        <i class="bi bi-arrow-clockwise me-2"></i>Reiniciando en <strong id="cuenta-refresco">5</strong>s
    </div>

    <!-- ═══════════════ ÁREA DE IMPRESIÓN ═══════════════ -->
    <div id="area-impresion"></div>

    <script>const BASE_URL = '<?= BASE_URL ?>';</script>
    <script src="<?= BASE_URL ?>public/static/voucher/kiosko.js"></script>
</body>
</html>
