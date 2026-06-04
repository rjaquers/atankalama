<?php
/**
 * Vista: Conversación de chat
 * Bootstrap 5 + Bootstrap Icons — estilo burbuja
 */
require VIEW_PATH . '/layouts/header.php';

$userId      = (int)($_SESSION['user_id'] ?? 0);
$esGrupo     = in_array($conv['tipo'] ?? '', ['grupo', 'area'], true);
$fotoConv    = $conv['otro_foto'] ?? $conv['foto_grupo'] ?? '';
$letraAvatar = strtoupper(mb_substr($titulo, 0, 1, 'UTF-8'));

$avatarColors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16'];
$bgColor      = $avatarColors[$convId % count($avatarColors)];
?>

<!-- Estilos específicos del chat -->
<style>
  /* Compensar márgenes del layout */
  .chat-wrapper {
    display: flex;
    flex-direction: column;
    height: calc(100vh - var(--mobile-top-h) - var(--bottom-nav-h) - 16px);
    max-height: 800px;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 6px rgba(0,0,0,.08);
  }
  @media (min-width: 992px) {
    .chat-wrapper {
      height: calc(100vh - 32px);
      max-height: none;
    }
  }

  /* Header de la conversación */
  .chat-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: #fff;
    border-bottom: 1px solid #e2e8f0;
    flex-shrink: 0;
  }
  .chat-header-avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: <?= $bgColor ?>;
    color: #fff;
    font-weight: 700;
    font-size: 16px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
  }
  .chat-header-avatar img {
    width: 100%; height: 100%; object-fit: cover;
  }

  /* Área de mensajes */
  .chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px 12px;
    background: #f1f5f9;
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  /* Burbujas */
  .chat-bubble {
    max-width: 75%;
    padding: 8px 12px;
    border-radius: 16px;
    font-size: 14px;
    line-height: 1.45;
    word-break: break-word;
    position: relative;
  }
  .chat-bubble.sent {
    align-self: flex-end;
    background: #dcf8c6;
    border-bottom-right-radius: 4px;
    color: #1e293b;
  }
  .chat-bubble.received {
    align-self: flex-start;
    background: #fff;
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 2px rgba(0,0,0,.08);
    color: #1e293b;
  }
  .bubble-author {
    font-size: 11px;
    font-weight: 600;
    color: #3b82f6;
    margin-bottom: 2px;
    display: block;
  }
  .bubble-time {
    font-size: 10px;
    color: #94a3b8;
    display: block;
    text-align: right;
    margin-top: 3px;
  }
  .bubble-img {
    max-width: 220px;
    border-radius: 10px;
    display: block;
    cursor: pointer;
  }

  /* Separador de fecha */
  .chat-date-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 10px 0 6px;
  }
  .chat-date-divider span {
    background: rgba(225,245,254,0.92);
    color: #546e7a;
    font-size: 11.5px;
    font-weight: 600;
    padding: 3px 12px;
    border-radius: 10px;
    box-shadow: 0 1px 2px rgba(0,0,0,.08);
  }

  /* Input bar */
  .chat-input-bar {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    padding: 10px 12px;
    background: #fff;
    border-top: 1px solid #e2e8f0;
    flex-shrink: 0;
  }
  .chat-textarea {
    flex: 1;
    resize: none;
    border: 1px solid #e2e8f0;
    border-radius: 22px;
    padding: 10px 16px;
    font-size: 14px;
    line-height: 1.4;
    max-height: 120px;
    overflow-y: auto;
    outline: none;
    transition: border-color .15s;
  }
  .chat-textarea:focus {
    border-color: #3b82f6;
  }
  .btn-chat-action {
    width: 40px; height: 40px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: 18px;
    border: none;
    cursor: pointer;
    transition: background .12s;
  }
  .btn-chat-send {
    background: #25d366;
    color: #fff;
  }
  .btn-chat-send:hover { background: #1ebe5a; }
  .btn-chat-attach {
    background: #f1f5f9;
    color: #64748b;
  }
  .btn-chat-attach:hover { background: #e2e8f0; }

  /* Preview imagen seleccionada */
  #foto-preview-wrap {
    display: none;
    padding: 8px 12px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    position: relative;
  }
  #foto-preview-wrap img {
    height: 80px;
    border-radius: 8px;
    object-fit: cover;
  }
  #foto-preview-remove {
    position: absolute;
    top: 4px; left: 80px;
    background: #ef4444;
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 22px; height: 22px;
    font-size: 12px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
  }
</style>

<div class="chat-wrapper">

  <!-- ======================== HEADER ======================== -->
  <div class="chat-header">
    <a href="<?= BASE_URL ?>/chat"
       class="btn btn-sm btn-light d-flex align-items-center justify-content-center"
       style="width:34px;height:34px;border-radius:50%;padding:0;">
      <i class="bi bi-chevron-left"></i>
    </a>

    <div class="chat-header-avatar" style="border-radius:<?= ($conv['tipo'] ?? '') === 'individual' ? '50%' : '10px' ?>">
      <?php if ($fotoConv): ?>
        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($fotoConv) ?>" alt="<?= htmlspecialchars($titulo) ?>">
      <?php elseif (($conv['tipo'] ?? '') === 'sistema'): ?>
        <i class="bi bi-megaphone-fill" style="font-size:18px;font-weight:normal;"></i>
      <?php elseif (($conv['tipo'] ?? '') === 'area'): ?>
        <i class="bi bi-people-fill" style="font-size:18px;font-weight:normal;"></i>
      <?php elseif (($conv['tipo'] ?? '') === 'grupo'): ?>
        <i class="bi bi-chat-square-dots-fill" style="font-size:18px;font-weight:normal;"></i>
      <?php else: ?>
        <?= htmlspecialchars($letraAvatar) ?>
      <?php endif; ?>
    </div>

    <div class="flex-grow-1 overflow-hidden">
      <div class="fw-semibold text-truncate" style="font-size:15px;">
        <?= htmlspecialchars($titulo) ?>
      </div>
      <?php if (($conv['tipo'] ?? '') === 'sistema'): ?>
        <small class="text-muted" style="font-size:11px;"><i class="bi bi-megaphone me-1"></i>Chat General</small>
      <?php elseif (($conv['tipo'] ?? '') === 'area'): ?>
        <small class="text-muted" style="font-size:11px;"><i class="bi bi-people-fill me-1"></i>Chat de Área</small>
      <?php elseif (($conv['tipo'] ?? '') === 'grupo'): ?>
        <small class="text-muted" style="font-size:11px;"><i class="bi bi-chat-square-dots me-1"></i>Grupo</small>
      <?php endif; ?>
    </div>

    <!-- Botón archivar (oculto en chats de área y Chat General) -->
    <?php if (!in_array($conv['tipo'] ?? '', ['area', 'sistema'], true)): ?>
    <form method="POST" action="<?= BASE_URL ?>/chat/archivar/<?= (int)$convId ?>">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <button type="submit"
              class="btn btn-sm btn-light d-flex align-items-center justify-content-center"
              style="width:34px;height:34px;border-radius:50%;padding:0;"
              title="<?= $conv['archivada'] ? 'Desarchivar' : 'Archivar' ?>">
        <i class="bi bi-<?= $conv['archivada'] ? 'archive-fill' : 'archive' ?>"></i>
      </button>
    </form>
    <?php endif; ?>
  </div>

  <!-- ======================== MENSAJES ======================== -->
  <div class="chat-messages" id="chat-messages">
    <?php if (empty($mensajes)): ?>
      <div class="text-center text-muted py-4" style="font-size:13px;">
        <i class="bi bi-chat-square-dots d-block fs-2 mb-2 opacity-25"></i>
        No hay mensajes aún. ¡Sé el primero en escribir!
      </div>
    <?php endif; ?>

    <?php
    $fechaAnterior = null;
    $hoy   = date('Y-m-d');
    $ayer  = date('Y-m-d', strtotime('-1 day'));
    foreach ($mensajes as $msg):
      $esMio    = (int)$msg['usuario_id'] === $userId;
      $clase    = $esMio ? 'sent' : 'received';
      $ts       = strtotime($msg['created_at']);
      $fechaMsg = date('Y-m-d', $ts);
      $hora     = date('H:i', $ts);
      $contenido = preg_replace(
          '/(https?:\/\/[^\s<>"]+)/i',
          '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
          htmlspecialchars($msg['contenido'] ?? '')
      );
      $msgId = (int)$msg['id'];

      // Separador de fecha
      if ($fechaMsg !== $fechaAnterior):
        if ($fechaMsg === $hoy)        $labelFecha = 'Hoy';
        elseif ($fechaMsg === $ayer)   $labelFecha = 'Ayer';
        else                           $labelFecha = strftime('%e de %B de %Y', $ts) ?: date('d/m/Y', $ts);
        $fechaAnterior = $fechaMsg;
    ?>
        <div class="chat-date-divider">
          <span><?= htmlspecialchars($labelFecha) ?></span>
        </div>
    <?php endif; ?>

    <div class="chat-bubble <?= $clase ?>" id="msg-<?= $msgId ?>">
      <?php if (!$esMio && $esGrupo): ?>
        <span class="bubble-author"><?= htmlspecialchars($msg['autor_nombre'] ?? '') ?></span>
      <?php endif; ?>

      <?php if ($msg['tipo'] === 'imagen' && $msg['archivo_ruta']): ?>
        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($msg['archivo_ruta']) ?>"
             alt="Imagen"
             class="bubble-img"
             onclick="verImagen(this.src)">
        <?php if ($contenido !== ''): ?>
          <p class="mb-0 mt-1"><?= $contenido ?></p>
        <?php endif; ?>
      <?php else: ?>
        <?= nl2br($contenido) ?>
      <?php endif; ?>

      <span class="bubble-time"><?= $hora ?></span>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ======================== INPUT BAR ======================== -->
  <form id="chat-form" method="POST" action="<?= BASE_URL ?>/chat/enviar" enctype="multipart/form-data">
    <input type="hidden" name="csrf"    value="<?= csrf_token() ?>">
    <input type="hidden" name="conv_id" value="<?= (int)$convId ?>">

    <!-- Preview foto seleccionada -->
    <div id="foto-preview-wrap">
      <img src="" alt="preview" id="foto-preview-img">
      <button type="button" id="foto-preview-remove" title="Quitar foto">
        <i class="bi bi-x"></i>
      </button>
    </div>

    <div class="chat-input-bar">
      <!-- Adjuntar foto -->
      <label class="btn-chat-action btn-chat-attach mb-0" title="Adjuntar foto">
        <i class="bi bi-image"></i>
        <input type="file" name="foto" id="foto-input" accept="image/*" style="display:none;">
      </label>

      <!-- Texto -->
      <textarea class="chat-textarea"
                name="contenido"
                id="chat-texto"
                placeholder="Escribe un mensaje..."
                rows="1"
                autocomplete="off"></textarea>

      <!-- Enviar -->
      <button type="submit" class="btn-chat-action btn-chat-send" id="btn-enviar" title="Enviar">
        <i class="bi bi-send-fill"></i>
      </button>
    </div>
  </form>

</div>

<!-- Modal visor de imagen -->
<div class="modal fade" id="modalImagen" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-transparent border-0">
      <div class="modal-body text-center p-0">
        <img src="" id="modal-img-src" class="img-fluid rounded" alt="Imagen">
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var convId    = <?= (int)$convId ?>;
  var lastMsgId = <?= (int)$lastId ?>;
  var userId    = <?= (int)$_SESSION['user_id'] ?>;
  var baseUrl   = window._BASE_URL || '';
  var pollTimer = null;

  /* ---- Scroll al fondo ---- */
  function scrollFondo() {
    var box = document.getElementById('chat-messages');
    if (box) box.scrollTop = box.scrollHeight;
  }
  scrollFondo();

  /* ---- Crea burbuja HTML ---- */
  function crearBurbuja(msg) {
    var esMio  = parseInt(msg.usuario_id) === userId;
    var clase  = esMio ? 'sent' : 'received';
    var hora   = msg.created_at ? msg.created_at.substring(11, 16) : '';
    var div    = document.createElement('div');
    div.className = 'chat-bubble ' + clase;
    div.id        = 'msg-' + msg.id;

    var html = '';

    // Autor (grupos, mensajes recibidos)
    if (!esMio && <?= $esGrupo ? 'true' : 'false' ?>) {
      html += '<span class="bubble-author">' + escHtml(msg.autor_nombre || '') + '</span>';
    }

    if (msg.tipo === 'imagen' && msg.archivo_ruta) {
      html += '<img src="' + baseUrl + '/' + escHtml(msg.archivo_ruta) + '" alt="Imagen" class="bubble-img" onclick="verImagen(this.src)">';
      if (msg.contenido) {
        html += '<p class="mb-0 mt-1">' + escHtml(msg.contenido) + '</p>';
      }
    } else {
      html += linkify(escHtml(msg.contenido || '')).replace(/\n/g, '<br>');
    }

    html += '<span class="bubble-time">' + hora + '</span>';
    div.innerHTML = html;
    return div;
  }

  /* ---- Etiqueta de fecha legible ---- */
  function labelFecha(dateStr) {
    var hoy  = new Date(); hoy.setHours(0,0,0,0);
    var ayer = new Date(hoy); ayer.setDate(hoy.getDate() - 1);
    var d    = new Date(dateStr.replace(' ', 'T')); d.setHours(0,0,0,0);
    if (d.getTime() === hoy.getTime())  return 'Hoy';
    if (d.getTime() === ayer.getTime()) return 'Ayer';
    return d.toLocaleDateString('es-CL', { day: 'numeric', month: 'long', year: 'numeric' });
  }

  /* ---- Crea separador de fecha ---- */
  function crearSeparadorFecha(dateStr) {
    var wrap  = document.createElement('div');
    wrap.className = 'chat-date-divider';
    wrap.setAttribute('data-fecha', dateStr.slice(0, 10));
    var span  = document.createElement('span');
    span.textContent = labelFecha(dateStr);
    wrap.appendChild(span);
    return wrap;
  }

  /* ---- Devuelve la última fecha visible en el área de mensajes ---- */
  function ultimaFechaVisible(box) {
    var dividers = box.querySelectorAll('.chat-date-divider[data-fecha]');
    if (dividers.length === 0) return null;
    return dividers[dividers.length - 1].getAttribute('data-fecha');
  }

  /* ---- Convierte URLs en enlaces clicables (aplicar después de escHtml) ---- */
  function linkify(s) {
    return s.replace(/(https?:\/\/[^\s<>"]+)/gi, '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>');
  }

  /* ---- Escape HTML básico ---- */
  function escHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  /* ---- Sonido de alerta (Web Audio API, sin archivo externo) ---- */
  var audioCtx = null;
  // Desbloquear AudioContext con la primera interacción del usuario
  document.addEventListener('click', function unlockAudio() {
    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    document.removeEventListener('click', unlockAudio);
  }, { once: true });

  function playNotifSound() {
    try {
      if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
      if (audioCtx.state === 'suspended') audioCtx.resume();
      var osc  = audioCtx.createOscillator();
      var gain = audioCtx.createGain();
      osc.connect(gain);
      gain.connect(audioCtx.destination);
      osc.type      = 'sine';
      osc.frequency.setValueAtTime(880, audioCtx.currentTime);
      osc.frequency.exponentialRampToValueAtTime(440, audioCtx.currentTime + 0.12);
      gain.gain.setValueAtTime(0.35, audioCtx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.18);
      osc.start(audioCtx.currentTime);
      osc.stop(audioCtx.currentTime + 0.18);
    } catch (e) { /* silenciar si el navegador bloquea */ }
  }

  /* ---- Polling de mensajes nuevos ---- */
  function poll() {
    fetch(baseUrl + '/api/chat/poll?conv=' + convId + '&since=' + lastMsgId, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data.ok && data.mensajes && data.mensajes.length > 0) {
        var box     = document.getElementById('chat-messages');
        var vacío   = box.querySelector('.text-center.text-muted');
        if (vacío) vacío.remove();

        var hayMensajeAjeno = false;
        data.mensajes.forEach(function (msg) {
          if (!document.getElementById('msg-' + msg.id)) {
            var fechaMsg = msg.created_at ? msg.created_at.slice(0, 10) : '';
            if (fechaMsg && fechaMsg !== ultimaFechaVisible(box)) {
              box.appendChild(crearSeparadorFecha(msg.created_at));
            }
            box.appendChild(crearBurbuja(msg));
            lastMsgId = Math.max(lastMsgId, parseInt(msg.id));
            if (parseInt(msg.usuario_id) !== userId) hayMensajeAjeno = true;
          }
        });
        if (hayMensajeAjeno) playNotifSound();
        scrollFondo();
      }
    })
    .catch(function () { /* silenciar errores de red */ });
  }

  // Iniciar polling cada 5 segundos
  pollTimer = setInterval(poll, 5000);

  /* ---- Envío AJAX del formulario ---- */
  var form     = document.getElementById('chat-form');
  var enviando = false; // guard para evitar doble envío

  function ejecutarEnvio() {
    if (enviando) return;

    var texto     = document.getElementById('chat-texto').value.trim();
    var fotoFiles = document.getElementById('foto-input').files;

    if (texto === '' && fotoFiles.length === 0) return;

    enviando = true;
    var fd        = new FormData(form);
    var btnEnviar = document.getElementById('btn-enviar');
    btnEnviar.disabled = true;

    // — UI optimista: mostrar burbuja inmediatamente —
    var box     = document.getElementById('chat-messages');
    var vacio   = box.querySelector('.text-center.text-muted');
    if (vacio) vacio.remove();

    // Separador de fecha si el día cambió
    var ahora     = new Date();
    var fechaHoy  = ahora.toISOString().slice(0, 10);
    if (fechaHoy !== ultimaFechaVisible(box)) {
      box.appendChild(crearSeparadorFecha(ahora.toISOString().replace('T', ' ')));
    }

    var tempDiv = document.createElement('div');
    tempDiv.className = 'chat-bubble sent';
    tempDiv.id        = 'msg-temp';
    tempDiv.style.opacity = '0.6';

    var ahora = new Date();
    var hora  = ('0' + ahora.getHours()).slice(-2) + ':' + ('0' + ahora.getMinutes()).slice(-2);

    if (fotoFiles.length > 0) {
      // Mostrar preview de la imagen mientras sube
      var objUrl = URL.createObjectURL(fotoFiles[0]);
      tempDiv.innerHTML = '<img src="' + objUrl + '" alt="Imagen" class="bubble-img">'
                        + (texto ? '<p class="mb-0 mt-1">' + escHtml(texto) + '</p>' : '')
                        + '<span class="bubble-time">' + hora + '</span>';
    } else {
      tempDiv.innerHTML = linkify(escHtml(texto)).replace(/\n/g, '<br>')
                        + '<span class="bubble-time">' + hora + '</span>';
    }

    box.appendChild(tempDiv);
    scrollFondo();

    fetch(baseUrl + '/chat/enviar', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: fd
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data.ok) {
        document.getElementById('chat-texto').value = '';
        limpiarFoto();
        autoResize();
        // Confirmar burbuja: asignar id real y quitar opacidad
        var temp = document.getElementById('msg-temp');
        if (temp) {
          temp.id           = 'msg-' + data.msg_id;
          temp.style.opacity = '';
        }
        // Avanzar lastMsgId para que el poll no re-agregue este mensaje
        if (data.msg_id) {
          lastMsgId = Math.max(lastMsgId, parseInt(data.msg_id));
        }
      } else {
        // Revertir burbuja optimista si falló
        var temp = document.getElementById('msg-temp');
        if (temp) temp.remove();
        alert(data.msg || 'Error al enviar el mensaje.');
      }
    })
    .catch(function () {
      var temp = document.getElementById('msg-temp');
      if (temp) temp.remove();
      alert('Error de red. Intenta de nuevo.');
    })
    .finally(function () {
      btnEnviar.disabled = false;
      enviando = false;
    });
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    ejecutarEnvio();
  });

  /* ---- Auto-resize textarea ---- */
  var textarea = document.getElementById('chat-texto');
  function autoResize() {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
  }
  textarea.addEventListener('input', autoResize);

  // Enviar con Enter (sin Shift) — llama directamente la función, no dispara submit
  textarea.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      ejecutarEnvio();
    }
  });

  /* ---- Preview de foto ---- */
  var fotoInput   = document.getElementById('foto-input');
  var previewWrap = document.getElementById('foto-preview-wrap');
  var previewImg  = document.getElementById('foto-preview-img');
  var removeBtn   = document.getElementById('foto-preview-remove');

  fotoInput.addEventListener('change', function () {
    if (this.files && this.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
        previewImg.src     = e.target.result;
        previewWrap.style.display = 'block';
      };
      reader.readAsDataURL(this.files[0]);
    }
  });

  function limpiarFoto() {
    fotoInput.value       = '';
    previewImg.src        = '';
    previewWrap.style.display = 'none';
  }
  removeBtn.addEventListener('click', limpiarFoto);

  /* ---- Limpiar polling al salir de la página ---- */
  window.addEventListener('beforeunload', function () {
    clearInterval(pollTimer);
  });

})();

/* ---- Visor de imagen ---- */
function verImagen(src) {
  document.getElementById('modal-img-src').src = src;
  var modal = new bootstrap.Modal(document.getElementById('modalImagen'));
  modal.show();
}
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
