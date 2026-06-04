<?php
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 * Vista: Asistente de Bodega (Chatbot con Claude)
 */
?>

<style>
.chat-wrapper   { max-width: 820px; margin: 0 auto; }
.chat-messages  {
    height: 58vh;
    overflow-y: auto;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
    scroll-behavior: smooth;
}
.msg-row        { display: flex; margin-bottom: 0.6rem; }
.msg-row.user   { justify-content: flex-end; }
.msg-row.asst   { justify-content: flex-start; }
.msg-bubble     {
    max-width: 76%;
    padding: 0.55rem 0.9rem;
    border-radius: 1rem;
    word-break: break-word;
    line-height: 1.45;
    font-size: 0.95rem;
}
.msg-bubble.user { background: #0d6efd; color: #fff; border-bottom-right-radius: 0.25rem; }
.msg-bubble.asst { background: #fff; border: 1px solid #dee2e6; border-bottom-left-radius: 0.25rem; }
.msg-bubble p:last-child { margin-bottom: 0; }
.msg-bubble ul, .msg-bubble ol { padding-left: 1.2rem; }
.typing { display: none; }
.typing-dot {
    display: inline-block;
    width: 8px; height: 8px;
    background: #6c757d;
    border-radius: 50%;
    margin: 0 1px;
    animation: blink 1.2s infinite;
}
.typing-dot:nth-child(2) { animation-delay: 0.2s; }
.typing-dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes blink { 0%,80%,100%{opacity:.25} 40%{opacity:1} }
.confirm-card   { border-left: 4px solid #ffc107; }
.stock-badge    { font-size: 0.85em; }
#vozStatus      { min-height: 1.2em; }
.ejemplos-wrap  { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.6rem; }
.ejemplo-chip   {
    cursor: pointer;
    font-size: 0.82rem;
    padding: 0.25rem 0.65rem;
    border-radius: 999px;
    border: 1px solid #dee2e6;
    background: #fff;
    color: #495057;
    transition: background .15s, color .15s;
    white-space: nowrap;
}
.ejemplo-chip:hover { background: #0d6efd; color: #fff; border-color: #0d6efd; }
</style>

<div class="chat-wrapper mt-3 mb-4">

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">
            <i class="fas fa-robot me-2 text-primary"></i>Asistente de Bodega
        </h5>
        <button class="btn btn-sm btn-outline-secondary" onclick="nuevaConversacion()">
            <i class="fas fa-plus me-1"></i> Nueva conversación
        </button>
    </div>

    <!-- Mensajes -->
    <div class="chat-messages" id="chatMessages">

        <?php if (empty($mensajes)): ?>
        <div class="msg-row asst">
            <div class="msg-bubble asst">
                ¡Hola po! Soy el asistente de bodega 👋<br>
                Puedo ayudarte a:
                <ul class="mt-1 mb-0">
                    <li>Consultar stock de cualquier producto</li>
                    <li>Registrar ingresos y consumos</li>
                    <li>Ver alertas de bajo stock</li>
                    <li>Deshacer el último movimiento (30 min)</li>
                </ul>
                ¿En qué te ayudo?
            </div>
        </div>
        <?php else: ?>
            <?php foreach ($mensajes as $m): ?>
            <div class="msg-row <?= $m['role'] === 'user' ? 'user' : 'asst' ?>">
                <div class="msg-bubble <?= $m['role'] === 'user' ? 'user' : 'asst' ?>">
                    <?= nl2br(htmlspecialchars($m['text'])) ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Indicador de escritura -->
        <div class="msg-row asst typing" id="typingRow">
            <div class="msg-bubble asst" style="padding:.45rem .8rem">
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
            </div>
        </div>

    </div><!-- /chat-messages -->

    <!-- Tarjeta de confirmación -->
    <div id="confirmCard" class="card confirm-card bg-warning bg-opacity-10 mt-3 d-none">
        <div class="card-body py-2 px-3">
            <p class="fw-semibold mb-1">
                <i class="fas fa-exclamation-triangle text-warning me-1"></i>Confirmar operación
            </p>
            <div id="confirmDetails" class="small mb-2"></div>
            <div class="d-flex gap-2">
                <button class="btn btn-success btn-sm px-3" onclick="confirmarOperacion()">
                    <i class="fas fa-check me-1"></i> Confirmar
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="cancelarOperacion()">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Input -->
    <div class="mt-3">
        <div class="input-group">
            <input type="text" id="chatInput" class="form-control"
                   placeholder="Ej: llegaron 24 jabones / ¿cuánto aceite hay?"
                   autocomplete="off"
                   onkeydown="if(event.key==='Enter' && !event.shiftKey){ event.preventDefault(); enviarMensaje(); }">
            <button class="btn btn-outline-secondary" id="btnVoz" onclick="toggleVoz()" title="Micrófono">
                <i class="fas fa-microphone"></i>
            </button>
            <button class="btn btn-primary" onclick="enviarMensaje()" id="btnEnviar">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
        <small class="text-muted mt-1 d-block" id="vozStatus"></small>

        <!-- Ejemplos rápidos -->
        <div class="ejemplos-wrap" id="ejemplosWrap">
            <span class="text-muted small me-1" style="line-height:2">Ejemplos:</span>

            <span class="ejemplo-chip" data-msg="¿Qué productos tienen bajo stock?">
                📉 Ver bajo stock
            </span>
            <span class="ejemplo-chip" data-msg="¿Cuánto stock hay de aceite?">
                🔍 Consultar stock
            </span>
            <span class="ejemplo-chip" data-msg="Llegaron 24 unidades de papel higiénico">
                📥 Registrar ingreso
            </span>
            <span class="ejemplo-chip" data-msg="Saqué 5 jabones para limpieza de habitaciones">
                📤 Registrar consumo
            </span>
            <span class="ejemplo-chip" data-msg="¿Cuál fue el último movimiento del papel higiénico?">
                📋 Ver historial
            </span>
            <span class="ejemplo-chip" data-msg="Deshaz el último movimiento del aceite">
                ↩️ Deshacer
            </span>
        </div>
    </div>

</div><!-- /chat-wrapper -->

<!-- marked.js para renderizar markdown del bot -->
<script src="https://cdn.jsdelivr.net/npm/marked@9/marked.min.js"></script>
<script>
    marked.use({ breaks: true, gfm: true });
</script>
<script src="<?= rtrim(BASE_URL, '/') ?>/public/js/chatbot.js"></script>
