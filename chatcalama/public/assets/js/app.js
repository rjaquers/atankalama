/**
 * Chat Interno — Hotel Atankalama
 * app.js — Scripts globales
 */

// ====================================================
// Toast notifications
// ====================================================
function showToast(message, type, duration) {
  type     = type     || 'success';
  duration = duration || 4000;
  var container = document.getElementById('toast-container');
  if (!container) return;
  var icons = {
    success: 'check-circle-fill',
    danger:  'exclamation-triangle-fill',
    warning: 'exclamation-triangle',
    info:    'info-circle-fill'
  };
  var id  = 'toast-' + Date.now();
  var div = document.createElement('div');
  div.id        = id;
  div.className = 'toast align-items-center text-bg-' + type + ' border-0 show';
  div.setAttribute('role', 'alert');
  div.innerHTML =
    '<div class="d-flex">' +
      '<div class="toast-body">' +
        '<i class="bi bi-' + (icons[type] || 'info-circle') + ' me-1"></i>' + message +
      '</div>' +
      '<button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest(\'.toast\').remove()"></button>' +
    '</div>';
  container.appendChild(div);
  if (duration > 0) {
    setTimeout(function () { if (div.parentNode) div.remove(); }, duration);
  }
}

// ====================================================
// Preview de imágenes antes de subir
// ====================================================
function initPhotoPreview(inputSelector, previewSelector) {
  var input   = document.querySelector(inputSelector);
  var preview = document.querySelector(previewSelector);
  if (!input || !preview) return;
  input.addEventListener('change', function () {
    preview.innerHTML = '';
    Array.prototype.forEach.call(this.files, function (file) {
      if (!file.type.startsWith('image/')) return;
      var reader = new FileReader();
      reader.onload = function (e) {
        var img = document.createElement('img');
        img.src = e.target.result;
        img.className = 'rounded';
        img.style.cssText = 'width:90px;height:90px;object-fit:cover;border:2px solid #e2e8f0';
        preview.appendChild(img);
      };
      reader.readAsDataURL(file);
    });
  });
}

// ====================================================
// Marcar nav activo según URL
// ====================================================
function setActiveNav() {
  var path = window.location.pathname;
  document.querySelectorAll('.sidebar-link, .bottom-nav-item').forEach(function (el) {
    var href = el.getAttribute('href') || '';
    var parts = href.split('/').filter(Boolean);
    var seg   = parts[parts.length - 1];
    if (seg && path.indexOf('/' + seg) !== -1) {
      el.classList.add('active');
    }
  });
}

// ====================================================
// Auto-submit OTP al ingresar 6 dígitos
// ====================================================
function initOtpAutoSubmit() {
  var input = document.querySelector('input[name="otp"]');
  if (!input) return;
  input.addEventListener('input', function () {
    if (/^\d{6}$/.test(this.value)) {
      this.form.submit();
    }
  });
}

// ====================================================
// Chat polling
// ====================================================
var chatPoller  = null;
var lastMsgId   = 0;

function startChatPolling(conversacionId, callback, interval) {
  interval = interval || 5000;
  stopChatPolling();
  chatPoller = setInterval(function () {
    fetch(window._BASE_URL + '/api/chat/poll?conv=' + conversacionId + '&since=' + lastMsgId)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.ok && data.mensajes && data.mensajes.length) {
          callback(data.mensajes);
        }
      })
      .catch(function () {});
  }, interval);
}

function stopChatPolling() {
  if (chatPoller) { clearInterval(chatPoller); chatPoller = null; }
}

function scrollToBottom(el) {
  if (el) el.scrollTop = el.scrollHeight;
}

function escapeHtml(s) {
  return String(s).replace(/[&<>"']/g, function (m) {
    return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m];
  });
}

// ====================================================
// Init
// ====================================================
document.addEventListener('DOMContentLoaded', function () {
  setActiveNav();
  initOtpAutoSubmit();
  initPhotoPreview('#foto-input', '#foto-preview');
  initPhotoPreview('#fotos-input', '#fotos-preview');
});
