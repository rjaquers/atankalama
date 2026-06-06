let _vouchersActuales = [];
let _timerRetorno   = null;
let _timerCuenta    = null;
let _timerNoFound   = null;

// ── Formato RUT en tiempo real ──────────────────────────────────
const rutField       = document.getElementById('rut-field');
const dvField        = document.getElementById('dv-field');
const btnEnterVisual = document.getElementById('btn-enter-visual');

rutField.addEventListener('input', function () {
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

    const noPrinted = vouchers.map((v, i) => ({v, i})).filter(({v}) => v.veces_impreso === 0);
    if (noPrinted.length === 1) {
        setTimeout(() => imprimirVoucher(noPrinted[0].i, 'kiosko-qr-' + noPrinted[0].i), 600);
    } else if (noPrinted.length === 0) {
        document.getElementById('aviso-retorno').querySelector('span').innerHTML =
            'Ya impreso · volviendo en <strong id="cuenta-regresiva">5</strong>s';
        iniciarRetorno(5);
    }
}

// ── Helper: genera QR como data URL ─────────────────────
function generarQrDataUrl(text, size) {
    return new Promise(function (resolve) {
        var div = document.createElement('div');
        div.style.position = 'absolute';
        div.style.left = '-9999px';
        document.body.appendChild(div);
        new QRCode(div, { text: text, width: size, height: size, correctLevel: QRCode.CorrectLevel.H });
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

    const ahora = new Date();
    const diasSemana   = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    const mesesNombres = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    const fechaImpresion = diasSemana[ahora.getDay()] + ' ' + ahora.getDate() + ' de ' + mesesNombres[ahora.getMonth()] + ' de ' + ahora.getFullYear();
    const horaImpresion  = ahora.getHours().toString().padStart(2,'0') + ':' + ahora.getMinutes().toString().padStart(2,'0');

    document.getElementById('overlay-impresion').style.display = 'flex';

    let qrImg = '';
    try {
        qrImg = await generarQrDataUrl(v.url_voucher, 246);
    } catch (e) {
        console.warn('Error generando QR:', e);
    }

    const empresaRut = v.empresa || '';

    const labelDuplicado = v.veces_impreso > 0
        ? `<div style="font-size: 14pt; font-weight: bold; border: 2pt solid #000; margin: 3mm 0; padding: 2mm; text-align:center;">*** DUPLICADO (#${v.veces_impreso + 1}) ***</div>`
        : '';

    document.getElementById('area-impresion').innerHTML = `
        <div class="voucher-thermal">
            <img src="${logoUrl}" class="logo-thermal" alt="Logo">
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

    function ocultarOverlay() {
        document.getElementById('overlay-impresion').style.display = 'none';
        window.removeEventListener('afterprint', ocultarOverlay);
        clearTimeout(_fallbackOverlay);
    }
    window.addEventListener('afterprint', ocultarOverlay);
    var _fallbackOverlay = setTimeout(ocultarOverlay, 6000);

    window.print();

    fetch('index.php?page=voucher/registrarImpresion', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'codigo=' + encodeURIComponent(v.codigo),
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'ok') {
            _vouchersActuales[idx].veces_impreso++;
        }
    })
    .catch(err => console.warn('Error registrando impresión:', err));

    iniciarRetorno();
}

// ── Cuenta regresiva tras imprimir ────────────────────────
function iniciarRetorno(segs = 6) {
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

document.addEventListener('keydown',    resetearRefresco);
document.addEventListener('touchstart', resetearRefresco);

// Reset automático tras 3 minutos de inactividad
let inactividadTimer;
function resetInactividad() {
    clearTimeout(inactividadTimer);
    inactividadTimer = setTimeout(() => {
        if (document.getElementById('pantalla-resultados').style.display !== 'none') {
            volverBusqueda();
        }
    }, 180000);
}
document.addEventListener('mousemove',  resetInactividad);
document.addEventListener('touchstart', resetInactividad);
document.addEventListener('keydown',    resetInactividad);
resetInactividad();
