<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Acceso — Chat Atankalama</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
  <style>
    body { background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); min-height: 100vh; }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">

<div style="width:100%;max-width:420px;padding:0 20px">

  <!-- Logo -->
  <div class="text-center mb-4">
    <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-3"
         style="width:72px;height:72px;background:rgba(59,130,246,.2);border:2px solid rgba(59,130,246,.4)">
      <i class="bi bi-chat-dots-fill text-primary" style="font-size:32px"></i>
    </div>
    <h1 class="text-white fw-bold mb-1" style="font-size:22px">Chat Interno</h1>
    <p class="mb-0" style="color:#64748b;font-size:13px">Hotel Atankalama</p>
  </div>

  <!-- Card -->
  <div class="card border-0 shadow-lg" style="border-radius:16px">
    <div class="card-body p-4">
      <h5 class="fw-semibold mb-1">Iniciar Sesión</h5>
      <p class="text-muted mb-4" style="font-size:13px">Ingresa tu correo para recibir el código de acceso.</p>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2" style="font-size:13px;border-radius:10px">
          <i class="bi bi-exclamation-triangle-fill me-1"></i><?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($msg)): ?>
        <div class="alert alert-success py-2" style="font-size:13px;border-radius:10px">
          <i class="bi bi-check-circle-fill me-1"></i><?= htmlspecialchars($msg) ?>
        </div>
      <?php endif; ?>

      <form method="post" action="<?= BASE_URL ?>/auth/requestOtp">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

        <div class="mb-3">
          <label class="form-label fw-medium" style="font-size:14px">Correo institucional</label>
          <div class="input-group">
            <span class="input-group-text bg-white">
              <i class="bi bi-envelope-fill text-primary"></i>
            </span>
            <input type="email" name="email" class="form-control form-control-lg"
                   placeholder="nombre@atankalama.com" required autofocus
                   style="border-left:0;font-size:15px">
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-semibold py-2"
                style="border-radius:10px;font-size:15px">
          Enviar código &nbsp;<i class="bi bi-arrow-right-circle-fill"></i>
        </button>
      </form>
    </div>
  </div>

  <p class="text-center mt-3" style="color:#475569;font-size:12px">
    Sistema de uso exclusivo para funcionarios del hotel.
  </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
