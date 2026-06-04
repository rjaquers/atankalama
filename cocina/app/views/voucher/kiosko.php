<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosko Vouchers — Hotel Atankalama</title>
    <link href="<?= BASE_URL ?>public/static/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        :root { --color-accent: #0d6efd; }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html {
            height: 100%;
            background: #0a0e1a;
        }

        body {
            height: 100%;
            overflow: hidden;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: transparent;
            color: #fff;
        }

        /* ─── RELOJ ──────────────────────────────────────────── */
        #reloj-kiosko {
            position: fixed;
            top: 18px;
            left: 24px;
            z-index: 100;
            font-family: Impact, 'Arial Narrow', Arial, sans-serif;
            color: #ffe600;
            line-height: 1.1;
            text-shadow: 0 2px 8px rgba(0,0,0,.7);
            pointer-events: none;
        }
        #reloj-hora  { font-size: 2.6rem; letter-spacing: .04em; }
        #reloj-fecha { font-size: 1.1rem;  letter-spacing: .06em; opacity: .85; }

        /* ─── FONDO CON ZOOM LENTO ───────────────────────────── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url('https://www.atankalama.com/public/uploads/piscinaAtankalama.webp');
            background-size: cover;
            background-position: center;
            animation: zoom-lento 30s ease-in-out infinite alternate;
            z-index: -1;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(10, 14, 26, 0.72);
            z-index: -1;
        }

        @keyframes zoom-lento {
            from { transform: scale(1);    }
            to   { transform: scale(1.12); }
        }

        /* ─── PANTALLA PRINCIPAL ─────────────────────────────── */
        #pantalla-busqueda {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            padding: 30px;
            text-align: center;
        }

        .kiosko-logo {
            height: 105px;
            margin-bottom: 24px;
            filter: brightness(0) invert(1);
            opacity: .9;
        }

        .kiosko-titulo {
            font-size: 2.4rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .kiosko-subtitulo {
            font-size: 1.1rem;
            color: rgba(255,255,255,.6);
            margin-bottom: 36px;
        }

        .kiosko-card {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 24px;
            padding: 105px 120px;
            width: 100%;
            max-width: 1170px;
            backdrop-filter: blur(10px);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .kiosko-label {
            font-size: 1.25rem;
            color: rgba(255,255,255,.7);
            margin-bottom: 30px;
        }

        #rut-field {
            background: rgba(255,255,255,.1);
            border: 2px solid rgba(255,255,255,.25);
            border-radius: 14px;
            color: #fff;
            font-size: 3.6rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-align: center;
            padding: 27px 30px;
            flex: 1;
            outline: none;
            transition: border-color .2s;
            min-width: 0;
        }

        .rut-container {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            max-width: 720px;
            margin-bottom: 20px;
        }

        .rut-separator {
            font-size: 2rem;
            font-weight: 700;
            color: rgba(255,255,255,.3);
            line-height: 1;
        }

        #dv-field {
            background: rgba(255,255,255,.05);
            border: 2px solid rgba(255,255,255,.15);
            border-radius: 14px;
            color: var(--color-accent);
            font-size: 3.6rem;
            font-weight: 700;
            text-align: center;
            padding: 27px 0;
            width: 120px;
            outline: none;
            cursor: default;
        }

        #btn-enter-visual {
            background: rgba(255,255,255,.05);
            color: rgba(255,255,255,.2);
            border: 2px solid rgba(255,255,255,.1);
            border-radius: 12px;
            font-size: 2.1rem;
            font-weight: 700;
            padding: 24px 0;
            width: 100%;
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all .3s ease;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        #btn-enter-visual.active {
            background: var(--color-accent);
            color: #fff;
            border-color: var(--color-accent);
            box-shadow: 0 0 20px rgba(13, 110, 253, 0.4);
            cursor: pointer;
        }

        #btn-enter-visual.limpiar {
            background: rgba(220, 53, 69, 0.15);
            color: #f87171;
            border-color: rgba(220, 53, 69, 0.5);
            box-shadow: none;
            cursor: pointer;
        }

        #btn-enter-visual i { font-size: 1.6rem; }

        #mensaje-error {
            color: #f87171;
            font-size: 1.5rem;
            margin-top: 15px;
            min-height: 24px;
        }

        /* ─── PANTALLA RESULTADOS ────────────────────────────── */
        #pantalla-resultados {
            display: none;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            width: 100%;
        }

        .res-header {
            background: rgba(255,255,255,.06);
            border-bottom: 1px solid rgba(255,255,255,.1);
            padding: 14px 30px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-shrink: 0;
        }

        .res-header img { height: 36px; filter: brightness(0) invert(1); opacity:.85; }

        .res-header .res-nombre {
            flex: 1;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .btn-volver {
            background: transparent;
            border: 1px solid rgba(255,255,255,.3);
            color: #fff;
            border-radius: 8px;
            padding: 8px 20px;
            font-size: .95rem;
            cursor: pointer;
            transition: background .2s;
        }
        .btn-volver:hover { background: rgba(255,255,255,.1); }

        #aviso-retorno {
            display: none;
            align-items: center;
            gap: 10px;
            background: rgba(25,135,84,.25);
            border: 1px solid rgba(25,135,84,.5);
            border-radius: 8px;
            padding: 6px 14px;
            font-size: .88rem;
            color: #a3f0c8;
        }
        #aviso-retorno button {
            background: transparent;
            border: 1px solid rgba(163,240,200,.4);
            color: #a3f0c8;
            border-radius: 6px;
            padding: 3px 10px;
            font-size: .8rem;
            cursor: pointer;
        }

        .res-body {
            flex: 1;
            overflow-y: auto;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 28px;
        }

        /* ─── TARJETA DE VOUCHER ─────────────────────────────── */
        .voucher-resultado {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 40px;
            padding: 36px 48px;
            width: 100%;
            max-width: 900px;
        }

        .vr-info { flex: 1; }

        .vr-tipo {
            display: inline-block;
            border-radius: 8px;
            padding: 6px 20px;
            font-size: 1.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 12px;
        }

        .vr-fecha { font-size: 2rem; font-weight: 700; color: #fff; }
        .vr-hora  { font-size: 1.75rem; color: rgba(255,255,255,.6); margin-top: 6px; }
        .vr-hotel { font-size: 1.6rem; color: rgba(255,255,255,.5); margin-top: 8px; }

        .vr-qr { text-align: center; min-width: 170px; }
        .vr-qr canvas, .vr-qr img { width: 160px !important; height: 160px !important; border-radius: 10px; display: block; }
        .vr-codigo { font-size: 1.1rem; color: rgba(255,255,255,.35); margin-top: 6px; font-family: monospace; }

        .btn-imprimir-voucher {
            background: #198754;
            color: #fff;
            border: none;
            border-radius: 14px;
            padding: 24px 40px;
            font-size: 1.9rem;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: background .2s;
        }
        .btn-imprimir-voucher:hover { background: #157347; }

        /* ─── AGRADECIMIENTO ─────────────────────────────────── */
        #msg-agradecimiento {
            text-align: center;
            color: rgba(255,255,255,.55);
            font-size: 1.5rem;
            font-style: italic;
            padding: 10px 0 20px;
        }

        /* ─── OVERLAY DE CARGA ───────────────────────────────── */
        #overlay-carga {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(10,14,26,.7);
            z-index: 999;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 16px;
        }
        #overlay-carga .spinner-border { width: 3rem; height: 3rem; color: var(--color-accent); }
        #overlay-carga p { color: rgba(255,255,255,.7); font-size: 1rem; }

        /* ─── OVERLAY DE IMPRESIÓN ───────────────────────────── */
        #overlay-impresion {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(10,14,26,.9);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 20px;
            text-align: center;
            backdrop-filter: blur(15px);
        }

        .printer-icon-anim {
            font-size: 5rem;
            color: var(--color-accent);
            animation: pulse-printer 1.5s infinite ease-in-out;
        }

        @keyframes pulse-printer {
            0% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); opacity: 0.8; }
        }

        .printing-text {
            font-size: 1.8rem;
            font-weight: 700;
            color: #fff;
            margin: 0;
        }

        .printing-subtext {
            color: rgba(255,255,255,.5);
            font-size: 1.1rem;
        }

        /* ─── IMPRESIÓN ──────────────────────────────────────── */
        #area-impresion { display: none; }

        @media print {
            html, body { overflow: visible; height: auto; background: #fff; }
            #pantalla-busqueda, #pantalla-resultados, #overlay-carga,
            #overlay-impresion { display: none !important; }
            #area-impresion { display: block !important; }
            .voucher-thermal { border-bottom: none; margin: 0; }
            @page { size: 80mm auto; margin: 0; }
        }

        /* ─── VOUCHER TÉRMICO (idéntico a imprimir_uno.php) ──────── */
        .voucher-thermal {
            background: #fff;
            width: 80mm;
            padding: 5mm;
            box-sizing: border-box;
            text-align: center;
            color: #000;
            font-family: 'Courier New', Courier, monospace;
        }
        .logo-thermal { max-width: 60mm; height: auto; margin-bottom: 4mm; filter: grayscale(1) contrast(1.5); }
        .hotel-name   { font-size: 16pt; font-weight: bold; letter-spacing: 2pt; margin-bottom: 3mm; }
        .service-type { font-size: 14pt; font-weight: bold; text-transform: uppercase; border: 2pt solid #000; padding: 2mm 4mm; display: inline-block; margin: 3mm 0; }
        .voucher-info { margin: 4mm 0; line-height: 1.4; }
        .v-nombre     { font-size: 13pt; font-weight: bold; margin-bottom: 2mm; }
        .v-empresa    { font-size: 18pt; font-weight: bold; margin-bottom: 3mm; line-height: 1.3; }
        .v-fecha      { font-size: 13pt; font-weight: 900; }
        .v-hora       { font-size: 11pt; }
        .v-hotel-t    { font-size: 12pt; font-weight: bold; margin-bottom: 2mm; }
        .v-obs        { font-size: 11pt; font-weight: bold; border: 1pt dashed #000; padding: 2mm 3mm; margin: 3mm 0; text-align: left; line-height: 1.4; }
        .qr-container { margin: 5mm 0 2mm; }
        .qr-container img { width: 65mm !important; height: 65mm !important; }
        .v-codigo     { font-size: 8pt; color: #333; margin-top: 2mm; letter-spacing: 1pt; }
        .v-footer     { font-size: 9pt; border-top: 1pt solid #000; padding-top: 3mm; margin-top: 2mm; font-style: italic; line-height: 1.6; }
        .v-impresion  { font-size: 8pt; color: #444; margin-top: 3mm; padding-top: 2mm; border-top: 1pt dashed #ccc; }

        /* ─── BANNER REFRESCO AUTOMÁTICO ────────────────────── */
        #banner-refresco {
            display: none;
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 193, 7, 0.97);
            color: #000;
            padding: 14px 36px;
            border-radius: 40px;
            font-size: 14pt;
            font-weight: bold;
            z-index: 9999;
            box-shadow: 0 4px 24px rgba(0,0,0,0.5);
            white-space: nowrap;
            animation: fadeInBanner 0.3s ease;
        }
        @keyframes fadeInBanner {
            from { opacity: 0; transform: translateX(-50%) translateY(10px); }
            to   { opacity: 1; transform: translateX(-50%) translateY(0);    }
        }
    </style>
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
            <p class="kiosko-label">RUT (Sin puntos ni guion)</p>
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
                <span>Impreso · volviendo en <strong id="cuenta-regresiva">10</strong>s</span>
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

    <script>
    const BASE_URL = '<?= BASE_URL ?>';
    let _vouchersActuales = [];
    let _timerRetorno   = null;
    let _timerCuenta    = null;
    let _timerNoFound   = null;

    // ── Formato RUT en tiempo real (Cuerpo + DV calculado en campo aparte) ──────
    const rutField       = document.getElementById('rut-field');
    const dvField        = document.getElementById('dv-field');
    const btnEnterVisual = document.getElementById('btn-enter-visual');

    rutField.addEventListener('input', function () {
        // Solo permitir números
        this.value = this.value.replace(/[^0-9]/g, '');
        
        const v = this.value;
        const msgEl = document.getElementById('mensaje-error');
        
        if (v.length >= 7) {
            dvField.value = calcularDV(v);
            btnEnterVisual.classList.add('active');
            msgEl.textContent = '';
        } else {
            dvField.value = '';
            btnEnterVisual.classList.remove('active');
        }
    });

    rutField.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            if (rutField.value.length >= 7) {
                buscarRut();
            } else {
                document.getElementById('mensaje-error').textContent = 'Ingrese un RUT válido.';
            }
        }
    });

    /**
     * Calcula el dígito verificador de un RUT (Módulo 11)
     */
    function calcularDV(cuerpo) {
        let suma = 0;
        let multiplo = 2;
        
        for (let i = cuerpo.length - 1; i >= 0; i--) {
            suma += parseInt(cuerpo.charAt(i)) * multiplo;
            multiplo = (multiplo < 7) ? multiplo + 1 : 2;
        }
        
        const dvEsperado = 11 - (suma % 11);
        if (dvEsperado === 11) return '0';
        if (dvEsperado === 10) return 'K';
        return dvEsperado.toString();
    }

    // ── Control del botón principal ──────────────────────────
    function manejarBoton() {
        if (btnEnterVisual.classList.contains('limpiar')) {
            limpiarBusqueda();
        } else if (btnEnterVisual.classList.contains('active')) {
            buscarRut();
        }
    }

    function mostrarBotonLimpiar() {
        btnEnterVisual.classList.remove('active');
        btnEnterVisual.classList.add('limpiar');
        document.getElementById('btn-enter-texto').textContent = 'Limpiar / Intentar de nuevo';
        document.getElementById('btn-enter-icono').className = 'bi bi-arrow-counterclockwise';
    }

    function resetearBoton() {
        btnEnterVisual.classList.remove('limpiar', 'active');
        document.getElementById('btn-enter-texto').textContent = 'Presione Enter para buscar';
        document.getElementById('btn-enter-icono').className = 'bi bi-arrow-return-left';
    }

    function limpiarBusqueda() {
        if (_timerNoFound) { clearInterval(_timerNoFound); _timerNoFound = null; }
        rutField.value  = '';
        dvField.value   = '';
        document.getElementById('mensaje-error').textContent = '';
        resetearBoton();
        rutField.focus();
    }

    // ── Búsqueda ──────────────────────────────────────────────
    function buscarRut() {
        const cuerpo = rutField.value.trim();
        const dv     = dvField.value.trim();
        const msgEl  = document.getElementById('mensaje-error');
        msgEl.textContent = '';

        if (!cuerpo || cuerpo.length < 7 || !dv) {
            msgEl.textContent = 'Ingrese un RUT válido (7 u 8 dígitos).';
            return;
        }

        const fullRut = cuerpo + '-' + dv;

        document.getElementById('overlay-carga').style.display = 'flex';

        fetch('index.php?page=voucher/buscar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'rut=' + encodeURIComponent(fullRut),
        })
        .then(r => {
            if (!r.ok) {
                return r.text().then(t => {
                    console.error('HTTP ' + r.status + ':', t);
                    throw new Error('HTTP ' + r.status);
                });
            }
            return r.text().then(t => {
                try {
                    return JSON.parse(t);
                } catch (e) {
                    console.error('Respuesta no-JSON del servidor:', t);
                    throw new Error('Respuesta inválida del servidor');
                }
            });
        })
        .then(data => {
            document.getElementById('overlay-carga').style.display = 'none';

            if (data.status === 'error' || data.status === 'not_found') {
                msgEl.innerHTML = data.mensaje + ' <span style="color:#aaa;font-size:0.85em;">— volviendo en <strong id="cuenta-nofound">7</strong>s</span>';
                mostrarBotonLimpiar();
                if (_timerNoFound) clearInterval(_timerNoFound);
                let segsNoFound = 7;
                _timerNoFound = setInterval(() => {
                    segsNoFound--;
                    const el = document.getElementById('cuenta-nofound');
                    if (el) el.textContent = segsNoFound;
                    if (segsNoFound <= 0) {
                        clearInterval(_timerNoFound);
                        _timerNoFound = null;
                        limpiarBusqueda();
                    }
                }, 1000);
                return;
            }

            try {
                mostrarResultados(data.vouchers);
            } catch (e) {
                console.error('Error al mostrar resultados:', e);
                msgEl.textContent = 'Error al mostrar los resultados. Por favor recargue la página.';
            }
        })
        .catch((err) => {
            console.error('Error en búsqueda:', err);
            document.getElementById('overlay-carga').style.display = 'none';
            msgEl.textContent = 'Error de conexión. Intente nuevamente.';
            mostrarBotonLimpiar();
        });
    }

    // ── Mostrar resultados ────────────────────────────────────
    function mostrarResultados(vouchers) {
        _vouchersActuales = vouchers;
        const lista  = document.getElementById('res-lista');
        const nombre = vouchers[0]?.nombre || 'Usuario';

        document.getElementById('res-nombre-usuario').textContent = nombre;
        lista.innerHTML = '';

        const colores = {
            primary: '#0d6efd', success: '#198754',
            warning: '#ffc107', info: '#0dcaf0', secondary: '#6c757d'
        };

        vouchers.forEach((v, i) => {
            const div    = document.createElement('div');
            div.className = 'voucher-resultado';
            const bgTipo  = colores[v.color] || '#6c757d';
            const canvasId = 'kiosko-qr-' + i;
            
            // Lógica de impresión restringida
            let controlImpresion = '';
            if (v.veces_impreso > 0) {
                controlImpresion = `
                    <div class="text-danger fw-bold d-flex flex-column align-items-center text-center" style="font-size: 1.7rem; min-width: 180px;">
                        <i class="bi bi-exclamation-triangle-fill mb-2" style="font-size: 2.8rem;"></i>
                        <span>YA IMPRESO</span>
                        <small class="text-white-50 mt-2" style="font-size: 1.2rem; line-height: 1.3; font-weight: normal;">
                            Si lo extravió, solicite ayuda en recepción.
                        </small>
                    </div>`;
            } else {
                controlImpresion = `
                    <button class="btn-imprimir-voucher" onclick="imprimirVoucher(${i}, '${canvasId}')">
                        <i class="bi bi-printer me-1"></i>Imprimir
                    </button>`;
            }

            div.innerHTML = `
                <div class="vr-info">
                    <span class="vr-tipo" style="background:${bgTipo}">${v.etiqueta}</span>
                    <div class="vr-fecha">${v.fecha_texto}</div>
                    <div class="vr-hora"><i class="bi bi-clock" style="font-size:.75rem;"></i> ${v.hora}</div>
                    <div class="vr-hotel">${v.hotel}</div>
                    ${v.empresa ? `<div class="vr-hotel">${v.empresa}</div>` : ''}
                </div>
                <div class="vr-qr">
                    <div id="${canvasId}"></div>
                    <div class="vr-codigo">${v.codigo}</div>
                </div>
                ${controlImpresion}
            `;
            lista.appendChild(div);

            // Generar QR con qrcodejs (compatible con Edge/Firefox/Chrome)
            if (typeof QRCode !== 'undefined') {
                const wrapEl = document.getElementById(canvasId);
                if (wrapEl) {
                    try {
                        new QRCode(wrapEl, {
                            text: v.url_voucher,
                            width: 80,
                            height: 80,
                            correctLevel: QRCode.CorrectLevel.H
                        });
                    } catch (e) {
                        console.warn('QR error:', e);
                    }
                }
            }
        });

        document.getElementById('pantalla-busqueda').style.display   = 'none';
        document.getElementById('pantalla-resultados').style.display = 'flex';

        // Auto-impresión: si hay exactamente 1 voucher no impreso, imprimir sin esperar clic
        const noPrinted = vouchers.map((v, i) => ({v, i})).filter(({v}) => v.veces_impreso === 0);
        if (noPrinted.length === 1) {
            setTimeout(() => imprimirVoucher(noPrinted[0].i, 'kiosko-qr-' + noPrinted[0].i), 600);
        } else if (noPrinted.length === 0) {
            // Todos ya impresos — volver al formulario en 5 s
            document.getElementById('aviso-retorno').querySelector('span').innerHTML =
                'Ya impreso · volviendo en <strong id="cuenta-regresiva">5</strong>s';
            iniciarRetorno(5);
        }
    }

    // ── Helper: genera QR como data URL usando qrcodejs ─────
    function generarQrDataUrl(text, size) {
        return new Promise(function (resolve) {
            var div = document.createElement('div');
            div.style.position = 'absolute';
            div.style.left = '-9999px';
            document.body.appendChild(div);
            new QRCode(div, { text: text, width: size, height: size, correctLevel: QRCode.CorrectLevel.H });
            // qrcodejs copia canvas→img via setTimeout interno; esperamos 200ms
            setTimeout(function () {
                var img    = div.querySelector('img');
                var canvas = div.querySelector('canvas');
                var src = '';
                if (img && img.src && img.src.startsWith('data:')) {
                    src = img.src;
                } else if (canvas) {
                    src = canvas.toDataURL();
                }
                document.body.removeChild(div);
                resolve(src);
            }, 200);
        });
    }

    // ── Imprimir voucher individual ───────────────────────────
    async function imprimirVoucher(idx, canvasId) {
        const v       = _vouchersActuales[idx];
        const logoUrl = BASE_URL + 'public/static/img/logoAtankalama.png';

        // Fecha y hora de impresión
        const ahora = new Date();
        const diasSemana   = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
        const mesesNombres = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        const fechaImpresion = diasSemana[ahora.getDay()] + ' ' + ahora.getDate() + ' de ' + mesesNombres[ahora.getMonth()] + ' de ' + ahora.getFullYear();
        const horaImpresion  = ahora.getHours().toString().padStart(2,'0') + ':' + ahora.getMinutes().toString().padStart(2,'0');

        // Mostrar overlay de impresión
        document.getElementById('overlay-impresion').style.display = 'flex';

        // Generar QR con qrcodejs (misma librería que imprimir_uno.php, funciona en Edge)
        let qrImg = '';
        try {
            qrImg = await generarQrDataUrl(v.url_voucher, 246);
        } catch (e) {
            console.warn('Error generando QR:', e);
        }

        const empresaRut = v.empresa || '';

        // Etiqueta de duplicado
        const labelDuplicado = v.veces_impreso > 0
            ? `<div style="font-size: 14pt; font-weight: bold; border: 2pt solid #000; margin: 3mm 0; padding: 2mm; text-align:center;">*** DUPLICADO (#${v.veces_impreso + 1}) ***</div>`
            : '';

        document.getElementById('area-impresion').innerHTML = `
            <div class="voucher-thermal">
                <img src="${logoUrl}" class="logo-thermal" alt="Logo">
                <div class="hotel-name">HOTEL ATANKALAMA</div>
                ${labelDuplicado}
                <div class="service-type">${v.etiqueta}</div>
                <div class="voucher-info">
                    <div class="v-nombre">${v.nombre.toUpperCase()}</div>
                    ${v.hotel ? `<div class="v-hotel-t">${v.hotel.toUpperCase()}</div>` : ''}
                    ${empresaRut ? `<div class="v-empresa">${empresaRut}</div>` : ''}
                    <div class="v-fecha">${v.fecha_texto.toUpperCase()}</div>
                    ${v.hora !== '—' ? `<div class="v-hora">HORA: ${v.hora}</div>` : ''}
                </div>
                ${v.observaciones ? `<div class="v-obs"><strong>OBS:</strong> ${v.observaciones}</div>` : ''}
                <div class="qr-container">
                    ${qrImg ? `<img src="${qrImg}" alt="QR">` : ''}
                    <div class="v-codigo">${v.codigo}</div>
                </div>
                <div class="v-footer">
                    Voucher Personalizado · No Transferible<br>
                    Válido solo para la fecha indicada.
                </div>
                <div class="v-impresion">Impreso el ${fechaImpresion} a las ${horaImpresion}</div>
            </div>
        `;

        // Ocultar overlay cuando el diálogo de impresión se cierra (compatible Edge/Firefox/Chrome)
        function ocultarOverlay() {
            document.getElementById('overlay-impresion').style.display = 'none';
            window.removeEventListener('afterprint', ocultarOverlay);
            clearTimeout(_fallbackOverlay);
        }
        window.addEventListener('afterprint', ocultarOverlay);
        // Fallback: si afterprint no dispara (navegador antiguo), ocultar a los 10s
        var _fallbackOverlay = setTimeout(ocultarOverlay, 10000);

        window.print();

        // Registrar impresión en BD
        fetch('index.php?page=voucher/registrarImpresion', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'codigo=' + encodeURIComponent(v.codigo),
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'ok') {
                // Actualizar contador localmente
                _vouchersActuales[idx].veces_impreso++;
                // Para refrescar la UI sin salir, podríamos llamar a mostrarResultados(_vouchersActuales)
                // pero por ahora el usuario suele salir tras imprimir.
            }
        })
        .catch(err => console.warn('Error registrando impresión:', err));

        // Iniciar cuenta regresiva de 10 s para volver al formulario
        iniciarRetorno();
    }

    // ── Cuenta regresiva tras imprimir ────────────────────────
    function iniciarRetorno(segs = 10) {
        cancelarRetorno();

        const aviso   = document.getElementById('aviso-retorno');
        const cuenta  = document.getElementById('cuenta-regresiva');
        let segundos  = segs;

        aviso.style.display = 'flex';
        cuenta.textContent  = segundos;

        _timerCuenta = setInterval(() => {
            segundos--;
            cuenta.textContent = segundos;
            if (segundos <= 0) clearInterval(_timerCuenta);
        }, 1000);

        _timerRetorno = setTimeout(volverBusqueda, segs * 1000);
    }

    function cancelarRetorno() {
        clearTimeout(_timerRetorno);
        clearInterval(_timerCuenta);
        _timerRetorno = null;
        _timerCuenta  = null;
        const aviso = document.getElementById('aviso-retorno');
        if (aviso) aviso.style.display = 'none';
    }

    // ── Volver a búsqueda ─────────────────────────────────────
    function volverBusqueda() {
        cancelarRetorno();
        document.getElementById('pantalla-resultados').style.display = 'none';
        document.getElementById('pantalla-busqueda').style.display   = 'flex';
        document.getElementById('rut-field').value   = '';
        document.getElementById('dv-field').value    = '';
        document.getElementById('mensaje-error').textContent = '';
        resetearBoton();
        document.getElementById('rut-field').focus();
    }

    // Auto-focus al cargar
    document.addEventListener('DOMContentLoaded', () => rutField.focus());

    // ── Reloj en tiempo real ──────────────────────────────────
    (function tickReloj() {
        const dias  = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
        const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                       'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        function actualizar() {
            const n = new Date();
            const h = n.getHours().toString().padStart(2,'0');
            const m = n.getMinutes().toString().padStart(2,'0');
            const s = n.getSeconds().toString().padStart(2,'0');
            document.getElementById('reloj-hora').textContent  = h + ':' + m + ':' + s;
            document.getElementById('reloj-fecha').textContent =
                dias[n.getDay()] + ' ' + n.getDate() + ' ' + meses[n.getMonth()] + ' ' + n.getFullYear();
        }
        actualizar();
        setInterval(actualizar, 1000);
    })();

    // ── Refresco automático cada 90 segundos ──────────────────
    let _refrescoRestante = 90;
    const _bannerRefresco = document.getElementById('banner-refresco');
    const _cuentaRefresco = document.getElementById('cuenta-refresco');

    function resetearRefresco() {
        _refrescoRestante = 90;
        _bannerRefresco.style.display = 'none';
    }

    setInterval(() => {
        _refrescoRestante--;
        if (_refrescoRestante <= 5 && _refrescoRestante > 0) {
            _cuentaRefresco.textContent = _refrescoRestante;
            _bannerRefresco.style.display = 'block';
        }
        if (_refrescoRestante <= 0) {
            location.reload();
        }
    }, 1000);

    // Cualquier interacción del usuario reinicia el contador
    document.addEventListener('keydown',    resetearRefresco);
    document.addEventListener('touchstart', resetearRefresco);

    // Reset automático tras 3 minutos de inactividad (kiosko mode)
    let inactividadTimer;
    function resetInactividad() {
        clearTimeout(inactividadTimer);
        inactividadTimer = setTimeout(() => {
            if (document.getElementById('pantalla-resultados').style.display !== 'none') {
                volverBusqueda();
            }
        }, 180000); // 3 minutos
    }
    document.addEventListener('mousemove',  resetInactividad);
    document.addEventListener('touchstart', resetInactividad);
    document.addEventListener('keydown',    resetInactividad);
    resetInactividad();
    </script>
</body>
</html>
