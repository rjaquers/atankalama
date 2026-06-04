document.addEventListener('DOMContentLoaded', () => {
    // 1. Reloj en tiempo real
    setInterval(() => {
        const spanReloj = document.getElementById('relojActual');
        if (spanReloj) {
            const now = new Date();
            const timeString = now.toLocaleTimeString('es-CL');
            const dateString = now.toLocaleDateString('es-CL', { day: '2-digit', month: '2-digit', year: 'numeric' });
            spanReloj.innerText = `${dateString} ${timeString}`;
        }
    }, 1000);

    // 2. Polling AJAX de Órdenes (30 seg)
    setInterval(fetchOrdenes, 30000);

    // 4. Iniciar animación del Logo Flotante
    setInterval(moverLogo, 20);

    // Revisar alertas al inicio si hay datos
    revisarAlertas(false); // false para no tocar el audio en la primera carga a menos que sea muy crítico.
});

// -- LOGO FLOTANTE --
let logoPosX = Math.random() * (window.innerWidth - 120);
let logoPosY = Math.random() * (window.innerHeight - 120);
let logoVelX = 1.5;
let logoVelY = 1.2;

function moverLogo() {
    const logo = document.getElementById('logoFlotante');
    if (!logo || logo.style.display === 'none') return;

    logoPosX += logoVelX;
    logoPosY += logoVelY;

    if (logoPosX + 120 >= window.innerWidth || logoPosX <= 0) logoVelX *= -1;
    if (logoPosY + 120 >= window.innerHeight || logoPosY <= 0) logoVelY *= -1;

    logo.style.left = logoPosX + 'px';
    logo.style.top = logoPosY + 'px';
}

// -- AJAX PULL --
function ajaxGet(url, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
    xhr.setRequestHeader('Pragma', 'no-cache');
    xhr.onreadystatechange = function () {
        if (xhr.readyState !== 4) return;
        if (xhr.status === 200) {
            try {
                callback(null, JSON.parse(xhr.responseText));
            } catch (e) {
                callback(e, null);
            }
        } else {
            callback(new Error('HTTP ' + xhr.status), null);
        }
    };
    xhr.send();
}

function fetchOrdenes() {
    var url = '?page=cocina/index&_t=' + new Date().getTime();
    ajaxGet(url, function (err, data) {
        if (err) { console.error('Error cargando órdenes:', err); return; }
        if (!data || data.status !== 'success') return;

        var contenedorOrdenes = document.getElementById('contenedor-ordenes');
        var tbody = document.getElementById('tbody-ordenes');
        var msj = document.getElementById('mensaje-sin-ordenes');
        var logo = document.getElementById('logoFlotante');

        if (data.cantidad > 0) {
            if (contenedorOrdenes) contenedorOrdenes.style.display = 'block';
            if (msj) msj.style.display = 'none';
            if (logo) logo.style.display = 'none';
            if (tbody) tbody.innerHTML = data.html;
            revisarAlertas(true);
        } else {
            if (contenedorOrdenes) contenedorOrdenes.style.display = 'none';
            if (msj) msj.style.display = 'block';
            if (logo) logo.style.display = 'block';
        }
    });
}

// -- CERRAR ORDEN (AJAX) --
function cerrarOrden(id) {
    if (!confirm('¿Deseas cerrar esta orden de la cocina?')) return;

    ajaxGet('?page=cocina/cerrar/' + id, function (err, data) {
        if (err) {
            console.error('Error:', err);
            alert('Confirme la conexión a Internet.');
            return;
        }
        if (data && data.status === 'success') {
            fetchOrdenes();
        } else {
            alert('Hubo un error al cerrar la orden. Por favor intenta recargar la página.');
        }
    });
}

// -- ALERTA SONORA --
function revisarAlertas(reproducir = true) {
    const ordenes = document.querySelectorAll('div[id^="orden-"]');

    let tieneNuevas = false;
    let tieneMasDe10Min = false;
    let tieneVencida = false;

    ordenes.forEach(div => {
        const minCreacion = parseInt(div.dataset.minCreacion || '0');
        const vencido = div.dataset.vencido === '1';

        if (vencido) {
            tieneVencida = true;
        } else if (minCreacion >= 10) {
            tieneMasDe10Min = true;
        } else {
            tieneNuevas = true;
        }
    });

    if (ordenes.length > 0 && reproducir) {
        if (tieneVencida) {
            playAlertaVencida();
            return;
        }

        const audio = tieneMasDe10Min
            ? document.getElementById('alertaAudio')
            : document.getElementById('nuevoAudio');

        if (audio) {
            audio.volume = 1.0;
            audio.play().catch(err => console.warn('Autoplay bloqueado (requiere interacción):', err));
        }
    }
}

// -- SONIDO 3: ORDEN VENCIDA --
// Ya que antes existía un tercer sonido pero no se encontró un archivo .mp3 dedicado,
// utilizamos la Web Audio API para generar tonos agudos de alerta crítica.
function playAlertaVencida() {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) throw new Error("AudioContext no soportado");

        const ctx = new AudioContext();
        const duration = 0.15;
        const beepCount = 3; // Tres pitidos rápidos

        for (let i = 0; i < beepCount; i++) {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'square';
            osc.frequency.setValueAtTime(800 + (i * 100), ctx.currentTime + (i * 0.25));

            gain.gain.setValueAtTime(0.3, ctx.currentTime + (i * 0.25));
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + (i * 0.25) + duration);

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.start(ctx.currentTime + (i * 0.25));
            osc.stop(ctx.currentTime + (i * 0.25) + duration + 0.1);
        }
    } catch (e) {
        // Fallback en caso que el navegador no soporte Web Audio
        const fallbackAudio = document.getElementById('alertaAudio');
        if (fallbackAudio) fallbackAudio.play().catch(err => console.warn('Autoplay bloqueado:', err));
    }
}
