<?php $page_title = 'Recuperar Contraseña'; ?>
<!DOCTYPE html>
<html lang="es">
<!--
  ===================================================
  = Proyecto: Hotel Atankalama - Sistema de Cocina  =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com.                   =
  = Fecha: <?= date('Y') ?>                  =
  ===================================================
-->
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
            <h4><i class="fas fa-key me-2"></i>Recuperar Contraseña</h4>
        </div>
        <div class="card-body p-4">
            <p class="text-muted">
                Ingresa tu correo electrónico registrado y te enviaremos un enlace para restablecer tu contraseña.
            </p>

            <?php
            if (isset($_SESSION['success'])) {
                echo showAlert($_SESSION['success'], 'success');
                unset($_SESSION['success']);
            } elseif (isset($_SESSION['error'])) {
                echo showAlert($_SESSION['error'], 'danger');
                unset($_SESSION['error']);
            }
            ?>

            <form method="POST" action="index.php?page=login&action=recover">
                <div class="mb-3">
                    <label for="email" class="form-label"><i class="fas fa-envelope me-1"></i>Correo electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <button type="submit" class="btn btn-gradient w-100">
                    <i class="fas fa-paper-plane me-2"></i>Enviar enlace
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
</body>
</html>
