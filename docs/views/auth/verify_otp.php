<?php
// Vista de Login – Paso 2: Verificación del código OTP
// Sistema de Contratos Atankalama
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Verificar código – Sistema de Contratos Atankalama</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      background: linear-gradient(135deg, #1a3a5c 0%, #2c5f8a 50%, #3a7cb8 100%);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .login-card {
      background: rgba(255,255,255,0.97);
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      overflow: hidden;
    }
    .login-header {
      background: linear-gradient(135deg, #1a3a5c 0%, #2c5f8a 100%);
      padding: 2.2rem 2rem;
      text-align: center;
      color: white;
    }
    .login-header h4 { margin: 0; font-weight: 700; font-size: 1.15rem; }
    .login-header p  { margin: 0.4rem 0 0; opacity: 0.75; font-size: 0.85rem; }
    .login-body { padding: 2rem; }

    /* Inputs de dígitos OTP */
    .otp-inputs {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin: 1.5rem 0;
    }
    .otp-inputs input {
      width: 50px;
      height: 60px;
      text-align: center;
      font-size: 1.6rem;
      font-weight: 700;
      border: 2px solid #dde2ef;
      border-radius: 10px;
      color: #1a3a5c;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .otp-inputs input:focus {
      border-color: #2c5f8a;
      box-shadow: 0 0 0 3px rgba(44,95,138,0.2);
      outline: none;
    }
    .btn-verify {
      background: linear-gradient(135deg, #1a3a5c 0%, #2c5f8a 100%);
      border: none;
      padding: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.5px;
      border-radius: 8px;
      transition: all 0.3s ease;
    }
    .btn-verify:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(26,58,92,0.4);
    }
    .countdown-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #fff8e1;
      color: #856404;
      border-radius: 20px;
      padding: 4px 14px;
      font-size: 0.82rem;
      font-weight: 600;
    }
    .countdown-badge.expired {
      background: #fdecea;
      color: #a93226;
    }
    .email-display {
      background: #f0f5ff;
      border-radius: 8px;
      padding: 8px 14px;
      font-size: 0.85rem;
      color: #1a3a5c;
      font-weight: 600;
      text-align: center;
      margin-bottom: 0.5rem;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
      <div class="login-card">

        <!-- Header -->
        <div class="login-header">
          <i class="fa-solid fa-envelope-open-text fa-2x mb-2"></i>
          <h4>Revisa tu correo</h4>
          <p>Ingresa el código de 6 dígitos que enviamos</p>
        </div>

        <!-- Body -->
        <div class="login-body">

          <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small">
              <i class="fa-solid fa-exclamation-circle"></i>
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <!-- Email al que se envió -->
          <p class="text-muted small text-center mb-1">Código enviado a:</p>
          <div class="email-display">
            <i class="fa-solid fa-envelope"></i>
            <?= htmlspecialchars($email ?? '') ?>
          </div>

          <!-- Cuenta regresiva -->
          <div class="text-center mt-3 mb-1">
            <span class="countdown-badge" id="countdownBadge">
              <i class="fa-regular fa-clock"></i>
              Expira en <span id="countdown">10:00</span>
            </span>
          </div>

          <!-- Formulario OTP -->
          <form method="post" action="<?= BASE_URL ?>/login/authenticate" id="otpForm">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="otp_code" id="otpHidden">

            <div class="otp-inputs" id="otpInputs">
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" id="d1" class="otp-digit" autocomplete="off">
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" id="d2" class="otp-digit" autocomplete="off">
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" id="d3" class="otp-digit" autocomplete="off">
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" id="d4" class="otp-digit" autocomplete="off">
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" id="d5" class="otp-digit" autocomplete="off">
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" id="d6" class="otp-digit" autocomplete="off">
            </div>

            <button type="submit" class="btn btn-verify btn-primary w-100" id="btnVerify">
              <i class="fa-solid fa-right-to-bracket"></i> Verificar y acceder
            </button>
          </form>

          <!-- Reenviar y volver -->
          <div class="text-center mt-3 d-flex justify-content-between">
            <a href="<?= BASE_URL ?>/login" class="text-muted small text-decoration-none">
              <i class="fa-solid fa-arrow-left"></i> Cambiar correo
            </a>
            <a href="<?= BASE_URL ?>/login/resendOtp" class="text-decoration-none small text-primary" id="resendLink">
              <i class="fa-solid fa-rotate-right"></i> Reenviar código
            </a>
          </div>

          <div class="text-center mt-3">
            <small class="text-muted">
              <i class="fa-solid fa-shield-halved"></i> Acceso seguro · Código de un solo uso
            </small>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  // ─── Auto-focus y navegación entre dígitos ───────────────────────────────────
  var digits  = document.querySelectorAll('.otp-digit');
  var hidden  = document.getElementById('otpHidden');
  var form    = document.getElementById('otpForm');
  var btnVerify = document.getElementById('btnVerify');

  digits.forEach(function (input, idx) {
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Backspace' && !input.value && idx > 0) {
        digits[idx - 1].focus();
      }
    });

    input.addEventListener('input', function () {
      // Solo dígitos
      input.value = input.value.replace(/\D/g, '');
      if (input.value && idx < digits.length - 1) {
        digits[idx + 1].focus();
      }
      syncHidden();
    });

    // Pegar código completo
    input.addEventListener('paste', function (e) {
      e.preventDefault();
      var pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
      for (var i = 0; i < digits.length; i++) {
        digits[i].value = pasted[i] || '';
      }
      digits[Math.min(pasted.length, digits.length) - 1].focus();
      syncHidden();
    });
  });

  function syncHidden() {
    var code = '';
    digits.forEach(function (d) { code += d.value; });
    hidden.value = code;
    btnVerify.disabled = code.length < 6;
  }

  // Deshabilitar botón hasta que el código esté completo
  btnVerify.disabled = true;

  form.addEventListener('submit', function (e) {
    syncHidden();
    if (hidden.value.length < 6) {
      e.preventDefault();
    }
  });

  // Foco automático en el primer dígito
  digits[0].focus();

  // ─── Cuenta regresiva 10 minutos ─────────────────────────────────────────────
  var total   = 10 * 60; // segundos
  var badge   = document.getElementById('countdownBadge');
  var display = document.getElementById('countdown');

  var timer = setInterval(function () {
    total--;
    if (total <= 0) {
      clearInterval(timer);
      display.textContent = '00:00';
      badge.classList.add('expired');
      badge.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Código expirado';
      btnVerify.disabled = true;
      return;
    }
    var m = Math.floor(total / 60);
    var s = total % 60;
    display.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
  }, 1000);
})();
</script>
</body>
</html>
