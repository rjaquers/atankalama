<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Atankalama Empresas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { max-width: 450px; width: 100%; border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
        .login-header { background: #2c3e50; color: white; padding: 30px; text-align: center; }
        .btn-primary { background-color: #0056b3; border: none; padding: 12px; font-weight: bold; }
        .form-control { padding: 12px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="login-card card">
        <div class="login-header">
            <i class="fa-solid fa-hotel fa-3x mb-3"></i>
            <h4>Portal de Clientes</h4>
            <p class="mb-0 text-white-50">Ingrese sus credenciales para acceder</p>
        </div>
        <div class="card-body p-4">
            <?php if(isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>login/authenticate" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Correo Electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="ejemplo@empresa.com" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="********" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 shadow-sm">
                    <i class="fa-solid fa-right-to-bracket me-2"></i> INICIAR SESIÓN
                </button>
            </form>
        </div>
        <div class="card-footer bg-white border-0 text-center pb-4 text-muted small">
            © <?= date('Y') ?> Hotel Atankalama
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
