// ── Carousel de Comandas ──────────────────────────────────────────────────────
// Rota entre los 3 paneles cada 30 segundos.
// En cada rotación también refresca los datos desde el servidor.

(function () {
    const INTERVALO_SEG = 30;

    const titulos = [
        'Almuerzos / Colaciones',
        'Cenas',
        'Colaciones Especiales',
        'Desayunos Mañana',
    ];

    let panelActual   = 0;
    let totalPaneles  = 4;
    let progreso      = 0;         // 0–100
    let timerProgreso = null;
    let timerRotacion = null;

    // ── Mostrar panel por índice ─────────────────────────────
    function mostrarPanel(idx) {
        const panels = document.querySelectorAll('.comanda-panel');
        const dots   = document.querySelectorAll('.comanda-dot');
        const titulo  = document.getElementById('comandaTitulo');
        const counter = document.getElementById('panelCounter');

        panels.forEach((p, i) => {
            p.style.display = (i === idx) ? 'block' : 'none';
        });
        dots.forEach((d, i) => {
            d.style.background = (i === idx)
                ? 'var(--color-cta)'
                : 'var(--color-border)';
            d.style.width  = (i === idx) ? '14px' : '10px';
            d.style.height = (i === idx) ? '14px' : '10px';
        });
        if (titulo)  titulo.textContent  = titulos[idx] || 'Comandas';
        if (counter) counter.textContent = `${idx + 1} / ${totalPaneles}`;
        panelActual = idx;
    }

    // ── Barra de progreso ────────────────────────────────────
    function iniciarProgreso() {
        progreso = 0;
        const barra = document.getElementById('barraProgreso');
        if (barra) barra.style.width = '0%';

        clearInterval(timerProgreso);
        timerProgreso = setInterval(() => {
            progreso += (100 / INTERVALO_SEG);
            if (progreso > 100) progreso = 100;
            if (barra) barra.style.width = progreso + '%';
        }, 1000);
    }

    // ── Avanzar al siguiente panel + refrescar datos ─────────
    function avanzarPanel() {
        const siguiente = (panelActual + 1) % totalPaneles;

        // Refrescar HTML de comandas desde el servidor
        const url = 'index.php?page=cocina/index&_t=' + Date.now();
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                if (data && data.htmlComandas) {
                    const contenedor = document.getElementById('contenedorComandas');
                    if (contenedor) {
                        contenedor.innerHTML = data.htmlComandas;
                        // Re-asignar clicks a los dots después de re-render
                        asignarClicksDots();
                    }
                }
            })
            .catch(() => {})
            .finally(() => {
                mostrarPanel(siguiente);
                iniciarProgreso();
            });
    }

    // ── Navegación manual (dots + botones) ──────────────────
    function navegarA(idx) {
        clearTimeout(timerRotacion);
        mostrarPanel(idx);
        iniciarProgreso();
        programarSiguiente();
    }

    function asignarClicksDots() {
        document.querySelectorAll('.comanda-dot').forEach(dot => {
            dot.addEventListener('click', function () {
                navegarA(parseInt(this.dataset.panel));
            });
        });

        const btnAnt = document.getElementById('btnPanelAnterior');
        const btnSig = document.getElementById('btnPanelSiguiente');

        if (btnAnt) btnAnt.addEventListener('click', () => {
            navegarA((panelActual - 1 + totalPaneles) % totalPaneles);
        });
        if (btnSig) btnSig.addEventListener('click', () => {
            navegarA((panelActual + 1) % totalPaneles);
        });
    }

    // ── Programar la siguiente rotación ─────────────────────
    function programarSiguiente() {
        clearTimeout(timerRotacion);
        timerRotacion = setTimeout(() => {
            avanzarPanel();
            programarSiguiente();
        }, INTERVALO_SEG * 1000);
    }

    // ── Inicialización ───────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        const contenedor = document.getElementById('contenedorComandas');
        if (!contenedor) return; // no estamos en la página de cocina

        mostrarPanel(0);
        iniciarProgreso();
        asignarClicksDots();
        programarSiguiente();
    });

})();
