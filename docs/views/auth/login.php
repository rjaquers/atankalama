<?php
// Vista de Login – Paso 1: Ingreso de Email
// Sistema de Contratos Atankalama
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Acceso – Sistema de Contratos Atankalama</title>
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
      backdrop-filter: blur(10px);
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
    .form-control:focus {
      border-color: #2c5f8a;
      box-shadow: 0 0 0 0.2rem rgba(44,95,138,0.2);
    }
    .btn-login {
      background: linear-gradient(135deg, #1a3a5c 0%, #2c5f8a 100%);
      border: none;
      padding: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.5px;
      border-radius: 8px;
      transition: all 0.3s ease;
    }
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(26,58,92,0.4);
    }
    .otp-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #e8f0fe;
      color: #1a3a5c;
      border-radius: 20px;
      padding: 4px 12px;
      font-size: 0.78rem;
      font-weight: 600;
      margin-bottom: 1.5rem;
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
          <i class="fa-solid fa-file-contract fa-2x mb-2"></i>
          <h4>Sistema de Contratos</h4>
          <p>Hotel Atankalama</p>
        </div>

        <!-- Body -->
        <div class="login-body">

          <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger py-2 small">
              <i class="fa-solid fa-exclamation-circle"></i>
              <?= htmlspecialchars($_SESSION['flash_error']) ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
          <?php endif; ?>

          <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small">
              <i class="fa-solid fa-exclamation-circle"></i>
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($success)): ?>
            <div class="alert alert-success py-2 small">
              <i class="fa-solid fa-check-circle"></i>
              <?= htmlspecialchars($success) ?>
            </div>
          <?php endif; ?>

          <div class="text-center">
            <div class="otp-badge">
              <i class="fa-solid fa-envelope-open-text"></i>
              Acceso por código al correo
            </div>
          </div>

          <p class="text-muted small text-center mb-3">
            Ingresa tu correo registrado y recibirás un código de acceso de 6 dígitos.
          </p>

          <form method="post" action="<?= BASE_URL ?>/login/sendOtp">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

            <div class="mb-4">
              <label class="form-label fw-semibold">
                <i class="fa-solid fa-envelope text-muted"></i> Correo electrónico
              </label>
              <input type="email" name="email" id="email"
                     class="form-control form-control-lg"
                     placeholder="usuario@atankalama.com"
                     required autofocus autocomplete="email">
            </div>

            <button type="submit" class="btn btn-login btn-primary w-100">
              <i class="fa-solid fa-paper-plane"></i> Enviar código de acceso
            </button>
          </form>

          <div class="text-center mt-3">
            <small class="text-muted">
              <i class="fa-solid fa-shield-halved"></i> Acceso restringido · Solo usuarios registrados
            </small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
