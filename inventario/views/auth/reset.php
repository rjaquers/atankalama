<!--
  ===================================================
  = Proyecto: Hotel Atankalama - Sistema de Cocina  =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com.                   =
  = Fecha: <?= date('Y') ?>                         =
  ===================================================
-->
<?php $page_title = 'Restablecer Contraseña'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            background: rgba(255,255,255,0.95);
        }
        .header {
            background: linear-gradient(135deg,#667eea 0%,#764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 1.5rem;
            text-align: center;
        }
        .btn-gradient {
            background: linear-gradient(135deg,#667eea 0%,#764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card mx-auto" style="max-width:420px;">
        <div class="header">
            <h4><i class="fas fa-lock me-2"></i>Restablecer Contraseña</h4>
        </div>
        <div class="card-body p-4">

            <?php
            if (isset($_SESSION['error'])) {
                echo showAlert($_SESSION['error'], 'danger');
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo showAlert($_SESSION['success'], 'success');
                unset($_SESSION['success']);
            }
            ?>

            <form method="POST" action="index.php?page=login&action=reset&token=<?php echo htmlspecialchars($_GET['token']); ?>">
                <div class="mb-3">
                    <label for="password" class="form-label"><i class="fas fa-key me-1"></i>Nueva contraseña</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" minlength="6" required>
                        <span class="input-group-text" id="togglePassword">
                            <i class="fas fa-eye-slash"></i>
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="confirm" class="form-label"><i class="fas fa-check me-1"></i>Confirmar contraseña</label>
                    <input type="password" class="form-control" id="confirm" name="confirm" minlength="6" required>
                </div>

                <button type="submit" class="btn btn-gradient w-100">
                    <i class="fas fa-save me-2"></i>Actualizar Contraseña
                </button>

                <div class="text-center mt-3">
                    <a href="index.php?page=login" class="text-decoration-none text-muted">
                        <i class="fas fa-arrow-left me-1"></i>Volver al login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    /* Mostrar/Ocultar contraseña */
    document.getElementById('togglePassword').addEventListener('click', function() {
        const input = document.getElementById('password');
        const icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        }
    });

    /* Validar coincidencia antes de enviar */
    document.querySelector('form').addEventListener('submit', function(e) {
        const pass = document.getElementById('password').value;
        const confirm = document.getElementById('confirm').value;
        if (pass !== confirm) {
            e.preventDefault();
            alert('Las contraseñas no coinciden.');
        }
    });
</script>

</body>
</html>
