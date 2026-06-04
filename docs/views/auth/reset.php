<?php
// Vista de Cambio de Contraseña - Sistema de Contratos Atankalama
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cambiar Contraseña - Sistema de Contratos Atankalama</title>
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
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      overflow: hidden;
    }
    .login-header {
      background: linear-gradient(135deg, #1a3a5c 0%, #2c5f8a 100%);
      padding: 2rem;
      text-align: center;
      color: white;
    }
    .login-header h4 {
      margin: 0;
      font-weight: 600;
    }
    .login-header p {
      margin: 1rem 0 0;
      opacity: 0.8;
      font-size: 0.9rem;
    }
    .login-body {
      padding: 2rem;
    }
    .form-control:focus {
      border-color: #2c5f8a;
      box-shadow: 0 0 0 0.2rem rgba(44, 95, 138, 0.25);
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
      box-shadow: 0 6px 20px rgba(26, 58, 92, 0.4);
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
          <i class="fa-solid fa-lock-open fa-3x mb-2"></i>
          <h4>Nueva Contraseña</h4>
          <p>Ingresa tu nueva contraseña para acceder.</p>
        </div>

        <!-- Body -->
        <div class="login-body">
          <?php if(!empty($error)): ?>
            <div class="alert alert-danger alert-sm py-2">
              <i class="fa-solid fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <form method="post" action="<?= BASE_URL ?>/login/updateNewPassword">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="mb-3">
              <label class="form-label">
                <i class="fa-solid fa-lock text-muted"></i> Nueva Contraseña
              </label>
              <input type="password" name="password" class="form-control form-control-lg"
                     placeholder="••••••••"
                     required autofocus>
            </div>

            <div class="mb-4">
              <label class="form-label">
                <i class="fa-solid fa-lock text-muted"></i> Confirmar Contraseña
              </label>
              <input type="password" name="confirm_password" class="form-control form-control-lg"
                     placeholder="••••••••"
                     required>
            </div>

            <button type="submit" class="btn btn-login btn-primary w-100">
              <i class="fa-solid fa-save"></i> Cambiar Contraseña
            </button>
          </form>

          <div class="text-center mt-3">
            <a href="<?= BASE_URL ?>/login" class="text-decoration-none small text-muted">
                Cancelar y volver al login
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
