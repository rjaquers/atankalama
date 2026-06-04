/**
 * ===================================================
 * Proyecto: Hotel Atankalama - Sistema de Cocina
 * Autor: Rodrigo Jaque Escobar
 * Módulo: Operación por Voz
 * Descripción:
 * - Botón define acción (ingreso/retiro)
 * - Voz genera propuesta
 * - Usuario confirma manualmente
 * - Maneja sugerencias obligatorias
 * ===================================================
 */

(function () {

    const btnIngreso = document.getElementById("btnIngreso");
    const btnRetiro = document.getElementById("btnRetiro");
    const txtVoz = document.getElementById("txtVoz");

    const panelResultado = document.getElementById("panelResultado");
    const resultadoInfo = document.getElementById("resultadoInfo");
    const btnConfirmar = document.getElementById("btnConfirmar");
    const btnCancelar = document.getElementById("btnCancelar");

    const panelCrear = document.getElementById("panelCrear");
    const crearInfo = document.getElementById("crearInfo");
    const btnCrearProducto = document.getElementById("btnCrearProducto");
    const btnCancelarCrear = document.getElementById("btnCancelarCrear");

    const panelMsg = document.getElementById("panelMsg");
    const msgBox = document.getElementById("msgBox");

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    let recognition = null;
    let currentAction = null;
    let lastProposal = null;

    /* =======================================================
   BUSCADOR MODAL PRODUCTOS
======================================================= */

    const inputBuscar = document.getElementById("buscarProducto");
    const tablaProductos = document.getElementById("tablaProductos");

    async function cargarProductos(query = "") {

        const res = await fetch(
            "index.php?page=voice_stock&action=list_products&q=" +
            encodeURIComponent(query)
        );

        const data = await res.json();

        if (data.status !== "ok") return;

        tablaProductos.innerHTML = "";

        if (!data.productos.length) {
            tablaProductos.innerHTML =
                "<tr><td colspan='3'>Sin resultados</td></tr>";
            return;
        }

        data.productos.forEach(p => {

            const row = document.createElement("tr");

            row.innerHTML = `
            <td>${p.id}</td>
            <td>${p.name}</td>
            <td><strong>${p.quantity}</strong></td>
        `;

            tablaProductos.appendChild(row);
        });
    }

    if (inputBuscar) {

        inputBuscar.addEventListener("keyup", function () {

            const val = this.value.trim();

            if (val.length >= 3) {
                cargarProductos(val);
            } else if (val.length === 0) {
                cargarProductos("");
            }
        });

        const modal = document.getElementById("modalProductos");

        modal.addEventListener("shown.bs.modal", function () {
            cargarProductos("");
        });
    }


    /* =======================================================
       UTILIDADES UI
    ======================================================== */

    function showMsg(type, text) {
        panelMsg.style.display = "block";
        msgBox.className = "alert alert-" + type;
        msgBox.textContent = text;
    }

    function hideMsg() {
        panelMsg.style.display = "none";
    }

    function resetPanels() {
        panelResultado.style.display = "none";
        panelCrear.style.display = "none";
        lastProposal = null;
    }

    function speak(text) {
        if (!window.speechSynthesis) return;
        const u = new SpeechSynthesisUtterance(text);
        u.lang = "es-CL";
        window.speechSynthesis.cancel();
        window.speechSynthesis.speak(u);
    }

    function ensureRecognition() {
        if (!SpeechRecognition) {
            showMsg("danger", "Tu navegador no soporta dictado por voz.");
            return false;
        }
        if (!recognition) {
            recognition = new SpeechRecognition();
            recognition.lang = "es-CL";
            recognition.continuous = false;
            recognition.interimResults = false;
        }
        return true;
    }

    async function postJson(url, data) {
        const res = await fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/json;charset=utf-8" },
            body: JSON.stringify(data),
        });
        return res.json();
    }

    /* =======================================================
       DETECCIÓN VISUAL VERBO
    ======================================================== */

    function detectarAccionVisual(texto) {

        const lower = texto.toLowerCase();

        const ingresoPalabras = ["agrego", "agregué", "compre", "compré"];
        const retiroPalabras = ["retire", "retiré", "saque", "saqué"];

        txtVoz.classList.remove("detect-success", "detect-danger");

        const indicador = document.getElementById("indicadorAccion");
        indicador.innerHTML = "";

        if (ingresoPalabras.some(v => lower.includes(v))) {

            txtVoz.classList.add("detect-success");

            indicador.innerHTML = `
                <span class="badge bg-success badge-accion">
                    🟢 Ingreso detectado
                </span>
            `;

            return "ingreso";
        }

        if (retiroPalabras.some(v => lower.includes(v))) {

            txtVoz.classList.add("detect-danger");

            indicador.innerHTML = `
                <span class="badge bg-danger badge-accion">
                    🔴 Retiro detectado
                </span>
            `;

            return "retiro";
        }

        return null;
    }

    /* =======================================================
       PROCESAMIENTO PRINCIPAL
    ======================================================== */

    async function processText(texto) {

        hideMsg();
        resetPanels();

        const accionDetectada = detectarAccionVisual(texto);

        if (accionDetectada && accionDetectada !== currentAction) {
            speak("La frase no coincide con el modo seleccionado.");
        }

        const resp = await postJson("index.php?page=voice_stock&action=process", {
            texto: texto,
            accion: currentAction,
        });

        /* -------- ERROR -------- */

        if (resp.status === "error") {
            showMsg("danger", resp.mensaje || "Error");
            speak(resp.mensaje || "Error");
            return;
        }

        /* -------- SUGERENCIAS -------- */

        if (resp.status === "suggestions") {

            panelResultado.style.display = "block";
            resultadoInfo.innerHTML = `
                <strong>No hay coincidencia exacta.</strong><br>
                Selecciona el producto correcto:
            `;

            const contenedor = document.createElement("div");
            contenedor.className = "mt-2";

            resp.sugerencias.forEach(p => {

                const btn = document.createElement("button");
                btn.className = "btn btn-outline-secondary btn-sm m-1";
                btn.textContent = `${p.name} (Stock: ${p.quantity})`;

                btn.onclick = function () {

                    lastProposal = {
                        accion: resp.accion,
                        cantidad: resp.cantidad,
                        texto_original: resp.texto_original,
                        verbo_detectado: resp.verbo_detectado,
                        producto: p
                    };

                    resultadoInfo.innerHTML = `
                        ${resp.accion === "ingreso" ? "Ingreso" : "Retiro"}: ${resp.cantidad}<br>
                        Producto seleccionado: <strong>${p.name}</strong><br>
                        Stock actual: ${p.quantity}
                    `;

                    btnConfirmar.style.display = "inline-block";
                };

                contenedor.appendChild(btn);
            });

            resultadoInfo.appendChild(contenedor);
            btnConfirmar.style.display = "none";

            speak("Selecciona el producto correcto.");
            return;
        }

        /* -------- NO ENCONTRADO -------- */

        if (resp.status === "not_found") {
            panelCrear.style.display = "block";
            crearInfo.textContent = resp.mensaje || "No encontrado.";
            const name = encodeURIComponent(resp.producto_query || "");
            btnCrearProducto.href = "index.php?page=products&action=create&prefill_name=" + name;
            speak("No se encontró el producto. Puedes crearlo.");
            return;
        }

        /* -------- OK -------- */

        lastProposal = resp;
        panelResultado.style.display = "block";

        const p = resp.producto;

        let msg = `${resp.accion === "ingreso" ? "Ingreso" : "Retiro"}: ${resp.cantidad}
Producto: ${p.name}
Stock actual: ${p.quantity}`;

        if (resp.warning) msg += `\n⚠ ${resp.warning}`;

        resultadoInfo.textContent = msg;

        speak(`${resp.accion === "ingreso" ? "Ingreso" : "Retiro"} detectado.`);
    }

    /* =======================================================
       CONFIRMAR OPERACIÓN
    ======================================================== */

    async function confirmOperation() {

        if (!lastProposal) return;

        const resp = await postJson("index.php?page=voice_stock&action=confirm", {
            producto_id: lastProposal.producto.id,
            cantidad: lastProposal.cantidad,
            accion: lastProposal.accion,
            texto_original: lastProposal.texto_original,
            verbo_detectado: lastProposal.verbo_detectado,
        });

        if (resp.status === "error") {
            showMsg("danger", resp.mensaje || "Error");
            speak(resp.mensaje || "Error");
            return;
        }

        resetPanels();
        txtVoz.value = "";

        const doneMsg =
            `${resp.accion === "ingreso" ? "✅ Ingreso aplicado" : "✅ Retiro aplicado"}: ${resp.cantidad}
${resp.producto.name}
Stock: ${resp.stock_antes} → ${resp.stock_despues}`;

        showMsg("success", doneMsg);
        speak("Operación aplicada correctamente.");
    }

    /* =======================================================
       VOZ
    ======================================================== */

    function startListening(action) {

        currentAction = action;
        resetPanels();
        hideMsg();

        if (!ensureRecognition()) return;

        recognition.onresult = function (event) {
            const text = event.results[0][0].transcript || "";
            txtVoz.value = text;
            processText(text);
        };

        recognition.onerror = function () {
            showMsg("danger", "Error con micrófono.");
            speak("Error con micrófono.");
        };

        recognition.start();
        speak(action === "ingreso" ? "Modo ingreso." : "Modo retiro.");
    }

    /* =======================================================
       EVENTOS
    ======================================================== */

    btnIngreso.addEventListener("click", () => startListening("ingreso"));
    btnRetiro.addEventListener("click", () => startListening("retiro"));
    btnConfirmar.addEventListener("click", confirmOperation);

    btnCancelar.addEventListener("click", () => {
        resetPanels();
        showMsg("secondary", "Operación cancelada.");
    });

    btnCancelarCrear.addEventListener("click", () => {
        resetPanels();
        showMsg("secondary", "Cancelado.");
    });

    /* =======================================================
       MODAL AYUDA PRIMERA VEZ
    ======================================================== */

    document.addEventListener("DOMContentLoaded", function () {
        const yaVisto = localStorage.getItem("voiceHelpShown");
        if (!yaVisto) {
            const modal = new bootstrap.Modal(document.getElementById("modalAyudaVoz"));
            modal.show();
            localStorage.setItem("voiceHelpShown", "1");
        }
    });

})();
