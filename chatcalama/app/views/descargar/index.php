<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Descargar App — Chat Atankalama</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap.min.css">
  <style>
    :root {
      --teal:  #128C7E;
      --green: #25D366;
    }
    body {
      min-height: 100vh;
      background: linear-gradient(150deg, #0f4c44 0%, #128C7E 50%, #1a9e8f 100%);
      display: flex; align-items: center; justify-content: center;
      font-family: 'Segoe UI', system-ui, sans-serif;
      padding: 24px 16px;
    }
    .card {
      background: #fff;
      border-radius: 24px;
      padding: 40px 36px;
      max-width: 440px;
      width: 100%;
      box-shadow: 0 20px 60px rgba(0,0,0,.25);
      text-align: center;
    }
    .app-icon {
      width: 80px; height: 80px;
      background: var(--teal);
      border-radius: 22px;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 20px;
      font-size: 38px;
    }
    h1 { font-size: 22px; font-weight: 800; color: #1e293b; margin-bottom: 4px; }
    .subtitle { color: #64748b; font-size: 14px; margin-bottom: 28px; }

    /* QR */
    #qr-wrap {
      background: #f8fafc;
      border: 2px solid #e2e8f0;
      border-radius: 16px;
      padding: 20px;
      display: inline-block;
      margin-bottom: 20px;
    }
    #qr-wrap canvas, #qr-wrap img { display: block; }

    /* Botón descarga */
    .btn-download {
      display: flex; align-items: center; justify-content: center; gap: 10px;
      background: var(--green);
      color: #fff;
      font-size: 16px; font-weight: 700;
      padding: 14px 28px;
      border-radius: 14px;
      text-decoration: none;
      transition: background .15s;
      margin-bottom: 12px;
    }
    .btn-download:hover { background: #1ebe5a; color: #fff; }
    .btn-download svg { flex-shrink: 0; }

    /* Steps */
    .steps { text-align: left; margin-top: 28px; border-top: 1px solid #e2e8f0; padding-top: 24px; }
    .steps h6 { font-weight: 700; color: #475569; font-size: 13px; text-transform: uppercase;
                letter-spacing: .04em; margin-bottom: 14px; }
    .step { display: flex; gap: 12px; align-items: flex-start; margin-bottom: 12px; }
    .step-num {
      flex-shrink: 0;
      width: 26px; height: 26px;
      background: var(--teal);
      color: #fff;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 12px; font-weight: 700;
    }
    .step p { margin: 0; font-size: 13px; color: #475569; line-height: 1.5; }
    .step strong { color: #1e293b; }

    /* iOS note */
    .ios-note {
      background: #fff7ed;
      border: 1px solid #fed7aa;
      border-radius: 10px;
      padding: 12px 14px;
      font-size: 12px;
      color: #92400e;
      margin-top: 16px;
      text-align: left;
    }
    .ios-note strong { color: #78350f; }

    /* Sin URL configurada */
    .no-apk {
      background: #fef2f2;
      border: 1px solid #fecaca;
      border-radius: 12px;
      padding: 16px;
      font-size: 13px;
      color: #991b1b;
    }
  </style>
</head>
<body>

<?php
// ── CONFIGURA AQUÍ LA URL DEL APK ───────────���────────────────────────────────
// Después de hacer el build con EAS, pega la URL del APK aquí.
// Ejemplo: 'https://expo.dev/artifacts/eas/xxxxxxxx.apk'
// O si subiste el APK al servidor: BASE_URL . '/app.apk'
$apkUrl = 'https://expo.dev/artifacts/eas/uJHeL2hzm6DmuB3z7P9mD7.apk';
// ───────────────────────────────────────────────��─────────────────────────────
?>

<div class="card">

  <div class="app-icon">💬</div>
  <h1>Chat Atankalama</h1>
  <p class="subtitle">App interna para el equipo del hotel</p>

  <?php if ($apkUrl): ?>

    <!-- QR Code -->
    <div id="qr-wrap">
      <div id="qr-code"></div>
    </div>
    <p style="font-size:12px;color:#94a3b8;margin-bottom:20px;">
      Escanea con la cámara del celular para descargar
    </p>

    <!-- Botón descarga directa -->
    <a href="<?= htmlspecialchars($apkUrl) ?>" class="btn-download">
      <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
      </svg>
      Descargar APK (Android)
    </a>

    <!-- Pasos de instalación -->
    <div class="steps">
      <h6>Cómo instalar</h6>
      <div class="step">
        <div class="step-num">1</div>
        <p>Escanea el QR o toca <strong>Descargar APK</strong> desde tu celular Android.</p>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <p>Cuando el archivo termine de bajar, toca la notificación de descarga o búscalo en tu carpeta de <strong>Descargas</strong>.</p>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <p>Si aparece el aviso <em>"Instalar apps de fuentes desconocidas"</em>, toca <strong>Configuración</strong> y activa el permiso para tu navegador.</p>
      </div>
      <div class="step">
        <div class="step-num">4</div>
        <p>Toca <strong>Instalar</strong> y listo. Inicia sesión con tu correo corporativo.</p>
      </div>
    </div>

    <div class="ios-note">
      <strong>iPhone (iOS):</strong> La instalación directa no está disponible en iOS.
      Usa la app desde el navegador en <a href="<?= BASE_URL ?>" style="color:#92400e;">esta dirección</a>
      y agrégala a la pantalla de inicio tocando
      <strong>Compartir → Agregar a pantalla de inicio</strong>.
    </div>

  <?php else: ?>

    <div class="no-apk">
      <strong>⚙️ App en construcción.</strong><br>
      El APK aún no está listo. Contacta al administrador del sistema.
    </div>

  <?php endif; ?>

</div>

<?php if ($apkUrl): ?>
<script>
// Genera el QR code con la URL del APK
(function () {
  var url = <?= json_encode($apkUrl) ?>;

  // Cargar librería QRCode.js desde CDN
  var s = document.createElement('script');
  s.src = 'https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js';
  s.onload = function () {
    QRCode.toCanvas(url, { width: 200, margin: 1, color: { dark: '#0f172a', light: '#f8fafc' } },
      function (err, canvas) {
        if (!err) document.getElementById('qr-code').appendChild(canvas);
      }
    );
  };
  document.head.appendChild(s);
})();
</script>
<?php endif; ?>

</body>
</html>
