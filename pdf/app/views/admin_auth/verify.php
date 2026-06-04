<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Código Admin — Chat Atankalama</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    body { background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); min-height: 100vh; }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">

<div style="width:100%;max-width:420px;padding:0 20px">

  <div class="text-center mb-4">
    <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-3"
         style="width:72px;height:72px;background:rgba(59,130,246,.2);border:2px solid rgba(59,130,246,.4)">
      <i class="bi bi-key-fill text-primary" style="font-size:32px"></i>
    </div>
    <h1 class="text-white fw-bold mb-1" style="font-size:22px">Ingresa el código</h1>
    <p class="mb-0" style="color:#64748b;font-size:13px">
      Enviamos un código de 6 dígitos a<br>
      <span class="text-white"><?= htmlspecialchars($email) ?></span>
    </p>
  </div>

  <div class="card border-0 shadow-lg" style="border-radius:16px">
    <div class="card-body p-4">
      <h5 class="fw-semibold mb-1">Verificación de identidad</h5>
      <p class="text-muted mb-4" style="font-size:13px">El código expira en 10 minutos. Máximo 5 intentos.</p>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2" style="font-size:13px;border-radius:10px">
          <i class="bi bi-exclamation-triangle-fill me-1"></i><?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="post" action="<?= BASE_URL ?>/adminAuth/verifyCode">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

        <div class="mb-4">
          <label class="form-label fw-medium" style="font-size:14px">Código de 6 dígitos</label>
          <input type="text" id="code" name="code"
                 class="form-control form-control-lg text-center fw-bold"
                 maxlength="6" pattern="\d{6}"
                 placeholder="_ _ _ _ _ _"
                 autocomplete="one-time-code"
                 inputmode="numeric"
                 autofocus required
                 style="font-size:28px;letter-spacing:10px;border-radius:10px">
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-semibold py-2"
                style="border-radius:10px;font-size:15px">
          <i class="bi bi-unlock-fill"></i> Verificar código
        </button>
      </form>

      <div class="text-center mt-3">
        <a href="<?= BASE_URL ?>/adminAuth/requestForm" class="text-muted" style="font-size:13px">
          <i class="bi bi-arrow-left"></i> Volver / usar otro correo
        </a>
      </div>
    </div>
  </div>

  <p class="text-center mt-3" style="color:#475569;font-size:12px">
    Solo administradores autorizados tienen acceso a esta sección.
  </p>
</div>

<script>
const codeInput = document.getElementById('code');

// Solo dígitos
codeInput.addEventListener('input', function () {
  this.value = this.value.replace(/\D/g, '').slice(0, 6);
});

// Auto-submit al completar 6 dígitos
codeInput.addEventListener('input', function () {
  if (this.value.length === 6) this.closest('form').submit();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
