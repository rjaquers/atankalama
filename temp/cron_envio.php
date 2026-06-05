<?php
/**
 * CRON ENVÍO DE REGISTROS DE TEMPERATURAS (con columna Hotel)
 * ------------------------------------------------------------
 * Envía diariamente (por ejemplo, a las 08:00) un correo con los registros
 * del día anterior a la lista de destinatarios configurada.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/___conec6.php';
require_once __DIR__ . '/models/Temperatura.php';
require_once __DIR__ . '/vendor/autoload.php'; // PHPMailer

$model = new Temperatura($pdo);

// Fecha de ayer
$ayer = date('Y-m-d', strtotime('-1 day'));
$registros = $model->listarPorDia($ayer);

// Enlace web de registros
$link = "https://www.atankalama.com/temp/index.php?route=listar&fecha=$ayer";

// Construcción del HTML del correo
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registros de Temperaturas - ' . $ayer . '</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color:#f8f9fa;padding:20px;">
<div class="container" style="max-width:900px;margin:auto;">
  <h2 style="text-align:center;color:#0d6efd;">Registros de Temperaturas - ' . $ayer . '</h2>
  <p style="text-align:center;">
    Ver registros en línea: 
    <a href="' . $link . '" target="_blank">' . $link . '</a>
  </p>
  <table class="table table-bordered table-striped mt-3">
    <thead class="table-primary">
      <tr>
        <th>Nombre</th>
        <th>Hotel / Cocina</th>
        <th>Temperatura (°C)</th>
        <th>Fecha / Hora</th>
      </tr>
    </thead>
    <tbody>';

if (empty($registros)) {
  $html .= '<tr><td colspan="4" class="text-center text-muted">No se registraron temperaturas el día ' . $ayer . '.</td></tr>';
} else {
  foreach ($registros as $r) {
    $html .= '<tr>
          <td>' . htmlspecialchars($r['nombre']) . '</td>
          <td>' . htmlspecialchars($r['hotel']) . '</td>
          <td>' . htmlspecialchars($r['temperatura']) . '</td>
          <td>' . htmlspecialchars($r['fecha_hora']) . '</td>
        </tr>';
  }
}

$html .= '
    </tbody>
  </table>
  <p class="text-muted text-center">Este correo fue generado automáticamente por el Sistema de Temperaturas del Hotel Atankalama.</p>
</div>
</body>
</html>';

// Configuración de envío con PHPMailer
$mail = new PHPMailer(true);

try {
  $mail->isSMTP();
  $mail->Host = SMTP_HOST;
  $mail->SMTPAuth = true;
  $mail->Username = SMTP_USER;
  $mail->Password = SMTP_PASS;
  $mail->SMTPSecure = 'tls';
  $mail->Port = SMTP_PORT;

  $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);

  // Múltiples destinatarios
  $destinatarios = [
    'jorgeperez@atankalama.com',
    'pamelawaldron@atankalama.com',
    'rodrigojaque@atankalama.com',
    'diegoortiz@atankalama.com',
    'osvaldocampos@atankalama.com'
  ];

  foreach ($destinatarios as $email) {
    $mail->addAddress($email);
  }
  $mail->Subject = "Registros de Temperaturas - $ayer";
  $mail->isHTML(true);
  $mail->Body = $html;

  $mail->send();
  // Output success HTML with SweetAlert and Redirect
  echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Enviando Reporte...</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: "Outfit", sans-serif; background: #f8f9fa; }
        div:where(.swal2-container) div:where(.swal2-popup) { border-radius: 20px; font-family: "Outfit", sans-serif; padding: 2em; }
    </style>
</head>
<body>
<script>
    Swal.fire({
        icon: "success",
        title: "¡Reporte Enviado!",
        text: "El reporte de temperaturas de ayer ha sido enviado correctamente por correo.",
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        allowOutsideClick: false
    }).then(() => {
        window.location.href = "https://www.atankalama.com/temp/index.php?route=listar";
    });
</script>
</body>
</html>';

} catch (Exception $e) {
  // Output error HTML
  echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Error al Enviar</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
    Swal.fire({
        icon: "error",
        title: "Error de Envío",
        text: "Ocurrió un error al enviar el correo: ' . addslashes($mail->ErrorInfo) . '",
        confirmButtonText: "Volver",
        confirmButtonColor: "#dc3545"
    }).then(() => {
        window.location.href = "https://www.atankalama.com/temp/index.php?route=listar";
    });
</script>
</body>
</html>';
}
?>