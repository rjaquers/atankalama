<?php
/**
 * Vista: Nueva conversación — buscar usuario para iniciar chat
 * Bootstrap 5 + Bootstrap Icons
 */
require VIEW_PATH . '/layouts/header.php';
?>

<div class="d-flex align-items-center gap-2 mb-4">
  <a href="<?= BASE_URL ?>/chat" class="btn btn-sm btn-light">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h5 class="fw-bold mb-0">
    <i class="bi bi-person-plus-fill text-primary me-2"></i>
    <?= htmlspecialchars($title ?? 'Nueva conversación') ?>
  </h5>
</div>

<div class="card shadow-sm border-0" style="max-width:560px;margin:0 auto;">
  <div class="card-body p-4">

    <!-- Campo de búsqueda -->
    <div class="mb-3 position-relative">
      <label class="form-label fw-semibold">Buscar usuario</label>
      <div class="input-group">
        <span class="input-group-text bg-white border-end-0">
          <i class="bi bi-search text-muted"></i>
        </span>
        <input type="text"
               id="buscar-input"
               class="form-control border-start-0"
               placeholder="Nombre o correo electrónico..."
               autocomplete="off"
               autofocus>
      </div>
      <small class="text-muted">Escribe al menos 2 caracteres para buscar.</small>
    </div>

    <!-- Spinner de carga -->
    <div id="buscar-spinner" class="text-center py-3 d-none">
      <div class="spinner-border spinner-border-sm text-primary" role="status">
        <span class="visually-hidden">Buscando...</span>
      </div>
    </div>

    <!-- Resultados -->
    <div id="buscar-resultados"></div>

  </div>
</div>

<!-- Formulario oculto para iniciar la conversación -->
<form id="form-iniciar" method="POST" action="<?= BASE_URL ?>/chat/iniciar" style="display:none;">
  <input type="hidden" name="csrf"       value="<?= csrf_token() ?>">
  <input type="hidden" name="usuario_id" id="form-usuario-id" value="">
</form>

<style>
.resultado-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 14px;
  border-radius: 10px;
  cursor: pointer;
  transition: background .12s;
  text-decoration: none;
  color: inherit;
  margin-bottom: 4px;
  border: 1px solid #f1f5f9;
}
.resultado-item:hover {
  background: #f0f9ff;
  border-color: #bae6fd;
}
.resultado-avatar {
  width: 44px; height: 44px;
  border-radius: 50%;
  background: #3b82f6;
  color: #fff;
  font-weight: 700;
  font-size: 17px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  overflow: hidden;
}
.resultado-avatar img {
  width: 100%; height: 100%; object-fit: cover;
}
.resultado-nombre  { font-weight: 600; font-size: 14px; line-height: 1.2; }
.resultado-detalle { font-size: 12px; color: #64748b; }
.sin-resultados {
  text-align: center;
  color: #94a3b8;
  padding: 20px 0;
  font-size: 14px;
}
</style>

<script>
(function () {
  var input     = document.getElementById('buscar-input');
  var spinner   = document.getElementById('buscar-spinner');
  var resultados = document.getElementById('buscar-resultados');
  var formInic  = document.getElementById('form-iniciar');
  var formUid   = document.getElementById('form-usuario-id');
  var baseUrl   = window._BASE_URL || '';
  var timer     = null;

  var avatarColors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16'];

  function esc(s) {
    return String(s || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function inicial(nombre) {
    return nombre ? nombre.charAt(0).toUpperCase() : '?';
  }

  function colorFor(id) {
    return avatarColors[parseInt(id) % avatarColors.length];
  }

  function renderResultados(usuarios) {
    if (!usuarios || usuarios.length === 0) {
      resultados.innerHTML = '<div class="sin-resultados"><i class="bi bi-person-x d-block fs-3 mb-2 opacity-25"></i>No se encontraron usuarios.</div>';
      return;
    }

    var html = '';
    usuarios.forEach(function (u) {
      var color = colorFor(u.id);
      var avatarHtml = u.foto_perfil
        ? '<img src="' + baseUrl + '/' + esc(u.foto_perfil) + '" alt="">'
        : inicial(u.nombre);

      html += '<div class="resultado-item" onclick="iniciarChat(' + parseInt(u.id) + ')">';
      html += '  <div class="resultado-avatar" style="background:' + (u.foto_perfil ? '#e2e8f0' : color) + '">' + avatarHtml + '</div>';
      html += '  <div class="flex-grow-1 overflow-hidden">';
      html += '    <div class="resultado-nombre text-truncate">' + esc(u.nombre) + '</div>';
      html += '    <div class="resultado-detalle text-truncate">' + esc(u.email) + '</div>';
      html += '  </div>';
      html += '  <i class="bi bi-chevron-right text-muted"></i>';
      html += '</div>';
    });
    resultados.innerHTML = html;
  }

  function buscar(q) {
    if (q.length < 2) {
      resultados.innerHTML = '';
      return;
    }
    spinner.classList.remove('d-none');
    resultados.innerHTML = '';

    fetch(baseUrl + '/chat/buscarUsuario?q=' + encodeURIComponent(q), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      spinner.classList.add('d-none');
      if (data.ok) {
        renderResultados(data.usuarios);
      }
    })
    .catch(function () {
      spinner.classList.add('d-none');
      resultados.innerHTML = '<div class="sin-resultados text-danger">Error al buscar. Intenta de nuevo.</div>';
    });
  }

  input.addEventListener('input', function () {
    clearTimeout(timer);
    var q = this.value.trim();
    if (q.length < 2) {
      resultados.innerHTML = '';
      return;
    }
    timer = setTimeout(function () { buscar(q); }, 300);
  });

  // Función global para el onclick del resultado
  window.iniciarChat = function (uid) {
    formUid.value = uid;
    formInic.submit();
  };
})();
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
