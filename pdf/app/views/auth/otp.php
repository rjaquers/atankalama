<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Verificar código — Chat Atankalama</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
  <style>
    body { background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); min-height: 100vh; }
    .otp-input {
      font-size: 36px !important;
      letter-spacing: 14px !important;
      font-weight: 900 !important;
      text-align: center;
      font-family: monospace;
    }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">

<div style="width:100%;max-width:420px;padding:0 20px">

  <!-- Logo -->
  <div class="text-center mb-4">
    <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-3"
         style="width:72px;height:72px;background:rgba(16,185,129,.15);border:2px solid rgba(16,185,129,.4)">
      <i class="bi bi-shield-check text-success" style="font-size:32px"></i>
    </div>
    <h1 class="text-white fw-bold mb-1" style="font-size:22px">Verificar código</h1>
    <?php if (!empty($email)): ?>
    <p class="mb-0" style="color:#64748b;font-size:13px">
      Código enviado a <strong class="text-white"><?= htmlspecialchars($email) ?></strong>
    </p>
    <?php endif; ?>
  </div>

  <!-- Card -->
  <div class="card border-0 shadow-lg" style="border-radius:16px">
    <div class="card-body p-4">

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2 mb-3" style="font-size:13px;border-radius:10px">
          <i class="bi bi-exclamation-triangle-fill me-1"></i><?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <p class="text-muted mb-4" style="font-size:13px">
        <i class="bi bi-clock me-1"></i>
        Ingresa el código de 6 dígitos. Expira en <strong><?= OTP_EXPIRES_MIN ?> minutos</strong>.
      </p>

      <form method="post" action="<?= BASE_URL ?>/auth/verifyOtp" id="otp-form">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

        <div class="mb-4">
          <input type="text" name="otp" id="otp-input"
                 class="form-control form-control-lg otp-input"
                 maxlength="6" pattern="\d{6}"
                 placeholder="——————"
                 required autofocus
                 inputmode="numeric"
                 autocomplete="one-time-code">
        </div>

        <button type="submit" class="btn btn-success w-100 fw-semibold py-2"
                style="border-radius:10px;font-size:15px">
          <i class="bi bi-unlock-fill me-1"></i> Ingresar al sistema
        </button>
      </form>

      <hr class="my-3">

      <div class="text-center">
        <a href="<?= BASE_URL ?>/login" class="text-muted" style="font-size:13px">
          <i class="bi bi-arrow-left me-1"></i> Volver a ingresar correo
        </a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Auto-submit cuando se ingresan 6 dígitos
  document.getElementById('otp-input').addEventListener('input', function () {
    if (/^\d{6}$/.test(this.value)) {
      document.getElementById('otp-form').submit();
    }
  });
</script>
</body>
</html>
