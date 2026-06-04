<?php $page_title = 'Iniciar Sesión'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- PWA Config -->
    <link rel='manifest' href='/inventario/manifest.json'>
    <meta name='theme-color' content='#0d6efd'>

    <link rel='icon' type='image/png' sizes='32x32' href='/inventario/assets/icons/icon-32.png'>
    <link rel='apple-touch-icon' sizes='192x192' href='/inventario/assets/icons/icon-192.png'>

    <title><?php echo $page_title . ' - ' . APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container { max-width: 400px; margin: auto; }
        .login-card {
            border: none; border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.95);
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; border-radius: 15px 15px 0 0;
            padding: 2rem; text-align: center;
        }
        .form-control { border-radius: 10px; padding: 12px 16px; }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25);
        }
        .btn-login {
            background: linear-gradient(135deg,#667eea 0%,#764ba2 100%);
            border: none; border-radius: 10px;
            padding: 12px; color: white; font-weight: 600;
            transition: all .3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
        }
        .input-group-text {
            background: transparent; border-left: none; cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="login-container">
        <div class="card login-card">
            <div class="login-header">
                <h1><img src="https://www.atankalama.cl/wp-content/uploads/2024/09/LOGO_CORP.png" width="250px"></h1>
                <h2><small>Atankalama Group</small></h2>
                <p class="mb-0"><i class='fas fa-hotel me-2'></i>Sistema de Gestión de Inventario</p>
            </div>
            <div class="card-body p-4">

                <?php
                if (isset($_SESSION['error'])) {
                    echo showAlert($_SESSION['error'], 'danger');
                    unset($_SESSION['error']);
                }
                ?>

                <form method="POST" action="index.php?page=login&action=login" id="loginForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="fas fa-user me-1"></i>Usuario
                        </label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Contraseña
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required>
                            <span class="input-group-text" id="togglePassword" title="Mostrar/Ocultar">
                                <i class="fas fa-eye-slash"></i>
                            </span>
                        </div>
                    </div>

                    <!-- 🔹 Recordar usuario -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">
                            Recordar usuario
                        </label>
                    </div>

                    <button type="submit" class="btn btn-login w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                    </button>

                    <div class="text-center mt-3">
                        <a href='index.php?page=login&action=recover' class='text-decoration-none text-muted'>
                            ¿Olvidaste tu contraseña?
                        </a>

                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    /**
     * 👁️ Mostrar/Ocultar contraseña
     */
    document.getElementById('togglePassword').addEventListener('click', function() {
        const input = document.getElementById('password');
        const icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye-slash','fa-eye');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye','fa-eye-slash');
        }
    });

    /**
     * 💾 Recordar usuario con localStorage
     * Guarda el nombre de usuario si el checkbox está activo.
     */
    document.addEventListener('DOMContentLoaded', function() {
        const usernameInput = document.getElementById('username');
        const rememberMe = document.getElementById('rememberMe');

        // Cargar valor guardado
        const savedUser = localStorage.getItem('rememberedUser');
        if (savedUser) {
            usernameInput.value = savedUser;
            rememberMe.checked = true;
        }

        // Guardar o borrar según selección
        document.getElementById('loginForm').addEventListener('submit', function() {
            if (rememberMe.checked) {
                localStorage.setItem('rememberedUser', usernameInput.value);
            } else {
                localStorage.removeItem('rememberedUser');
            }
        });
    });

    // /**
    //  * 🔐 Enlace “Olvidé mi contraseña”
    //  * Redirige a una página (aún no implementada) de recuperación.
    //  */
    // document.getElementById('forgotLink').addEventListener('click', function(e){
    //     e.preventDefault();
    //     alert('La recuperación de contraseña se implementará próximamente.\nContacte al administrador si necesita restablecer su acceso.');
    // });
</script>

</body>
</html>
