/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 * Chatbot de bodega — cliente JavaScript.
 */
(function () {
    'use strict';

    // ─── Estado ────────────────────────────────────────────────────────────────
    let pendienteActual = null;
    let escuchando = false;
    let reconocimiento = null;

    const chatMessages = document.getElementById('chatMessages');
    const chatInput    = document.getElementById('chatInput');
    const typingRow    = document.getElementById('typingRow');
    const confirmCard  = document.getElementById('confirmCard');
    const confirmDet   = document.getElementById('confirmDetails');
    const btnVoz       = document.getElementById('btnVoz');
    const vozStatus    = document.getElementById('vozStatus');
    const btnEnviar    = document.getElementById('btnEnviar');

    // ─── Scroll al fondo ───────────────────────────────────────────────────────
    function scrollBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    scrollBottom();

    // ─── Agregar burbuja ───────────────────────────────────────────────────────
    function addBubble(role, html, raw) {
        const row = document.createElement('div');
        row.className = 'msg-row ' + (role === 'user' ? 'user' : 'asst');

        const bubble = document.createElement('div');
        bubble.className = 'msg-bubble ' + (role === 'user' ? 'user' : 'asst');

        if (role === 'user') {
            bubble.textContent = raw || html;
        } else {
            // Renderizar markdown del bot
            bubble.innerHTML = (typeof marked !== 'undefined')
                ? marked.parse(raw || html)
                : (raw || html).replace(/\n/g, '<br>');
        }

        row.appendChild(bubble);
        // Insertar antes del indicador de escritura
        chatMessages.insertBefore(row, typingRow);
        scrollBottom();
        return bubble;
    }

    function showTyping(show) {
        typingRow.style.display = show ? 'flex' : 'none';
        btnEnviar.disabled = show;
        chatInput.disabled = show;
        scrollBottom();
    }

    // ─── Enviar mensaje ────────────────────────────────────────────────────────
    window.enviarMensaje = function () {
        const texto = chatInput.value.trim();
        if (!texto) return;

        chatInput.value = '';
        addBubble('user', null, texto);
        ocultarConfirmacion();
        showTyping(true);

        fetch('index.php?page=chatbot&action=process', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json; charset=utf-8' },
            body:    JSON.stringify({ texto }),
        })
        .then(r => r.json())
        .then(data => {
            showTyping(false);
            if (data.status === 'error') {
                addBubble('asst', null, '⚠️ ' + (data.mensaje || 'Error al procesar el mensaje.'));
                return;
            }
            if (data.respuesta) {
                addBubble('asst', null, data.respuesta);
            }
            if (data.pendiente) {
                mostrarConfirmacion(data.pendiente);
            }
        })
        .catch(() => {
            showTyping(false);
            addBubble('asst', null, '⚠️ Error de conexión. Verifica tu red e intenta de nuevo.');
        });
    };

    // ─── Confirmación ──────────────────────────────────────────────────────────
    function mostrarConfirmacion(pendiente) {
        pendienteActual = pendiente;

        const op    = pendiente.operacion;
        const nom   = pendiente.producto_nombre || '';
        const cant  = pendiente.cantidad;
        const unit  = pendiente.unidad || '';
        const antes = pendiente.stock_antes !== undefined ? pendiente.stock_antes : pendiente.stock_actual;
        const desp  = pendiente.stock_despues;
        const adv   = pendiente.advertencia;

        let etiquetaOp = { ingreso: '📥 Ingreso', consumo: '📤 Consumo/Retiro', deshacer: '↩️ Deshacer' }[op] || op;

        let html = `<strong>${etiquetaOp}</strong>: ${cant} ${unit} de <em>${nom}</em><br>`;
        if (antes !== undefined && desp !== undefined) {
            html += `Stock: <strong>${antes}</strong> → <strong>${desp}</strong> ${unit}`;
        }
        if (adv) {
            html += `<br><span class="text-warning fw-semibold">⚠️ ${adv}</span>`;
        }
        if (pendiente.fecha_original) {
            html += `<br><small class="text-muted">Movimiento original: ${pendiente.fecha_original}</small>`;
        }

        confirmDet.innerHTML = html;
        confirmCard.classList.remove('d-none');
        scrollBottom();
    }

    function ocultarConfirmacion() {
        pendienteActual = null;
        confirmCard.classList.add('d-none');
        confirmDet.innerHTML = '';
    }

    window.confirmarOperacion = function () {
        if (!pendienteActual) return;

        const payload = { ...pendienteActual };
        ocultarConfirmacion();
        showTyping(true);

        fetch('index.php?page=chatbot&action=confirm', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json; charset=utf-8' },
            body:    JSON.stringify(payload),
        })
        .then(r => r.json())
        .then(data => {
            showTyping(false);
            const icono = data.status === 'done' ? '✅' : '❌';
            let msg = icono + ' ' + (data.mensaje || '');
            if (data.status === 'done' && data.stock_actual !== null && data.stock_actual !== undefined) {
                msg += `\nStock actual: **${data.stock_actual}** unidades.`;
            }
            addBubble('asst', null, msg);
        })
        .catch(() => {
            showTyping(false);
            addBubble('asst', null, '⚠️ Error al confirmar la operación.');
        });
    };

    window.cancelarOperacion = function () {
        ocultarConfirmacion();
        addBubble('asst', null, 'Operación cancelada po, no se realizó ningún cambio. ¿Necesitas algo más?');
    };

    // ─── Nueva conversación ────────────────────────────────────────────────────
    window.nuevaConversacion = function () {
        if (!confirm('¿Iniciar una nueva conversación? El historial actual se conserva en el registro.')) return;

        fetch('index.php?page=chatbot&action=reset', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json; charset=utf-8' },
            body:    JSON.stringify({}),
        }).then(() => location.reload());
    };

    // ─── Reconocimiento de voz ─────────────────────────────────────────────────
    const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (SpeechRec) {
        reconocimiento = new SpeechRec();
        reconocimiento.lang = 'es-CL';
        reconocimiento.continuous = false;
        reconocimiento.interimResults = false;

        reconocimiento.onstart = function () {
            escuchando = true;
            btnVoz.classList.replace('btn-outline-secondary', 'btn-danger');
            btnVoz.innerHTML = '<i class="fas fa-stop"></i>';
            vozStatus.textContent = '🎙️ Escuchando... hablá po';
        };

        reconocimiento.onresult = function (e) {
            const texto = e.results[0][0].transcript;
            chatInput.value = texto;
            chatInput.focus();
            vozStatus.textContent = '✓ Escuché: "' + texto + '" — presiona Enter para enviar.';
        };

        reconocimiento.onerror = function (e) {
            vozStatus.textContent = 'Error de micrófono: ' + e.error;
        };

        reconocimiento.onend = function () {
            escuchando = false;
            btnVoz.classList.replace('btn-danger', 'btn-outline-secondary');
            btnVoz.innerHTML = '<i class="fas fa-microphone"></i>';
        };
    } else {
        if (btnVoz) {
            btnVoz.disabled = true;
            btnVoz.title = 'Tu navegador no soporta reconocimiento de voz';
        }
    }

    window.toggleVoz = function () {
        if (!reconocimiento) return;
        if (escuchando) {
            reconocimiento.stop();
        } else {
            vozStatus.textContent = '';
            reconocimiento.start();
        }
    };

    // Tecla Escape cierra confirmación
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') ocultarConfirmacion();
    });

    // Chips de ejemplos rápidos → auto-envían el mensaje
    document.querySelectorAll('.ejemplo-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            const msg = chip.getAttribute('data-msg');
            if (!msg) return;
            chatInput.value = msg;
            enviarMensaje();
        });
    });
})();
