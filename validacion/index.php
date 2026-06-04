<?php
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 * Este software es propiedad exclusiva de su autor.
 * Se concede un derecho de uso limitado al cliente. No se transfiere
 * la propiedad del código ni de la aplicación.
 *
 * @author  Rodrigo Jaque Escobar
 * @project Sistema de Validación de Correo — Hotel Atankalama
 */

session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/shared/acceso_db.php';

// ── Constantes ────────────────────────────────────────────────────────────────
const DOMINIO_VALIDO   = '@atankalama.com';
const OTP_MINUTOS      = 10;
const OTP_MAX_INTENTOS = 5;
const TOTP_ISSUER      = 'Hotel Atankalama';

define('VENDOR_PATH', $_SERVER['DOCUMENT_ROOT'] . '/novedades/vendor/autoload.php');

// ── Helpers ───────────────────────────────────────────────────────────────────

function validar_dominio(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL)
        && str_ends_with(strtolower($email), DOMINIO_VALIDO);
}

function buscar_usuario(string $email): array|false
{
    $stmt = acceso_pdo()->prepare("
        SELECT id, nombre, apellido, validado
        FROM chk_usuarios
        WHERE email = ? AND estado = 'activo'
        LIMIT 1
    ");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function generar_y_enviar_otp(string $email): bool
{
    $pdo = acceso_pdo();

    // Invalidar tokens previos vigentes
    $pdo->prepare("UPDATE chk_login_tokens SET used = 1 WHERE email = ? AND used = 0")
        ->execute([$email]);

    $codigo    = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expira    = date('Y-m-d H:i:s', strtotime('+' . OTP_MINUTOS . ' minutes'));
    $ip        = $_SERVER['REMOTE_ADDR']     ?? '';
    $ua        = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $pdo->prepare("
        INSERT INTO chk_login_tokens (email, token, expires_at, used, attempts, ip_address, user_agent)
        VALUES (?, ?, ?, 0, 0, ?, ?)
    ")->execute([$email, $codigo, $expira, $ip, $ua]);

    return enviar_email_otp($email, $codigo);
}

function verificar_otp(string $email, string $codigo): array
{
    // Retorna: ['ok' => bool, 'intentos_restantes' => int, 'expirado' => bool]
    $pdo  = acceso_pdo();
    $stmt = $pdo->prepare("
        SELECT id, token, attempts
        FROM chk_login_tokens
        WHERE email     = ?
          AND used       = 0
          AND expires_at > NOW()
          AND attempts   < ?
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute([$email, OTP_MAX_INTENTOS]);
    $token = $stmt->fetch();

    if (!$token) {
        return ['ok' => false, 'intentos_restantes' => 0, 'expirado' => true];
    }

    // Incrementar intentos
    $pdo->prepare("UPDATE chk_login_tokens SET attempts = attempts + 1 WHERE id = ?")
        ->execute([$token['id']]);

    if (!hash_equals($token['token'], $codigo)) {
        $restantes = OTP_MAX_INTENTOS - ((int) $token['attempts'] + 1);
        return ['ok' => false, 'intentos_restantes' => max(0, $restantes), 'expirado' => false];
    }

    // Correcto → marcar usado
    $pdo->prepare("UPDATE chk_login_tokens SET used = 1 WHERE id = ?")
        ->execute([$token['id']]);

    return ['ok' => true, 'intentos_restantes' => OTP_MAX_INTENTOS, 'expirado' => false];
}

function marcar_validado(string $email): void
{
    acceso_pdo()->prepare("UPDATE chk_usuarios SET validado = 1 WHERE email = ?")
        ->execute([$email]);
}

// ── Helpers TOTP ──────────────────────────────────────────────────────────────

function tfa(): \RobThree\Auth\TwoFactorAuth
{
    if (!file_exists(VENDOR_PATH)) {
        throw new \RuntimeException('vendor/autoload.php no encontrado');
    }
    require_once VENDOR_PATH;
    static $instancia = null;
    if ($instancia === null) {
        $instancia = new \RobThree\Auth\TwoFactorAuth(
            new \RobThree\Auth\Providers\Qr\ImageChartsQRCodeProvider(),
            TOTP_ISSUER
        );
    }
    return $instancia;
}

function obtener_totp_info(string $email): array
{
    try {
        $stmt = acceso_pdo()->prepare(
            "SELECT totp_habilitado, totp_secret FROM chk_usuarios WHERE email = ? LIMIT 1"
        );
        $stmt->execute([$email]);
        $fila = $stmt->fetch();
        return [
            'habilitado' => (bool) ($fila['totp_habilitado'] ?? false),
            'secret'     => $fila['totp_secret'] ?? null,
        ];
    } catch (\Throwable) {
        // Columnas aún no existen (migración pendiente)
        return ['habilitado' => false, 'secret' => null];
    }
}

function guardar_totp(string $email, string $secret): void
{
    acceso_pdo()->prepare(
        "UPDATE chk_usuarios SET totp_secret = ?, totp_habilitado = 1 WHERE email = ?"
    )->execute([$secret, $email]);
}

function deshabilitar_totp(string $email): void
{
    try {
        acceso_pdo()->prepare(
            "UPDATE chk_usuarios SET totp_secret = NULL, totp_habilitado = 0 WHERE email = ?"
        )->execute([$email]);
    } catch (\Throwable) {}
}

function enviar_email_otp(string $email, string $codigo): bool
{
    if (!file_exists(VENDOR_PATH)) {
        error_log('validacion/index.php: vendor/autoload.php no encontrado en ' . VENDOR_PATH);
        return false;
    }
    require_once VENDOR_PATH;

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = ACCESO_SMTP_HOST;
        $mail->Port       = ACCESO_SMTP_PORT;
        $mail->SMTPAuth   = true;
        $mail->Username   = ACCESO_SMTP_USER;
        $mail->Password   = ACCESO_SMTP_PASS;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom(ACCESO_SMTP_FROM, ACCESO_SMTP_NAME);
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Verificación de correo · Hotel Atankalama';
        $mail->Body    = plantilla_email($codigo);
        $mail->AltBody = "Tu código de verificación es: {$codigo}\nVálido por " . OTP_MINUTOS . " minutos.\nNo compartas este código.";
        $mail->send();
        return true;
    } catch (\Throwable $e) {
        error_log('validacion/index.php enviarEmail: ' . $e->getMessage());
        return false;
    }
}

function plantilla_email(string $codigo): string
{
    $minutos = OTP_MINUTOS;
    return <<<HTML
    <!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"></head>
    <body style="font-family:Arial,sans-serif;background:#f4f4f4;margin:0;padding:20px;">
      <div style="max-width:440px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.12);">
        <div style="background:#1e3a5f;padding:24px;text-align:center;">
          <h2 style="color:#fff;margin:0;font-size:20px;">Hotel Atankalama</h2>
          <p style="color:#a8c4e0;margin:6px 0 0;font-size:13px;">Verificación de correo electrónico</p>
        </div>
        <div style="padding:32px 24px;text-align:center;">
          <p style="color:#333;font-size:15px;margin:0 0 8px;">Recibimos una solicitud de verificación para tu correo.</p>
          <p style="color:#555;font-size:14px;margin:0 0 28px;">Tu código de verificación es:</p>
          <div style="display:inline-block;background:#eef2ff;border:2px dashed #1e3a5f;border-radius:10px;padding:18px 36px;">
            <span style="font-size:40px;font-weight:bold;letter-spacing:12px;color:#1e3a5f;">{$codigo}</span>
          </div>
          <p style="color:#888;font-size:13px;margin:24px 0 0;">
            Válido por <strong>{$minutos} minutos</strong>.<br>
            No compartas este código con nadie.
          </p>
        </div>
        <div style="background:#f9f9f9;padding:14px 24px;text-align:center;border-top:1px solid #eee;">
          <p style="color:#aaa;font-size:11px;margin:0;">
            Si no solicitaste esta verificación, puedes ignorar este mensaje.<br>
            Hotel Atankalama &mdash; sistema@atankalama.com
          </p>
        </div>
      </div>
    </body></html>
    HTML;
}

// ── Controlador principal ─────────────────────────────────────────────────────

$paso    = $_SESSION['val_paso']   ?? 1;
$email   = $_SESSION['val_email']  ?? '';
$nombre  = $_SESSION['val_nombre'] ?? '';
$error   = null;
$info    = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── POST paso 1: recibir email ────────────────────────────────────────────
    if (isset($_POST['email'])) {
        $emailPost = strtolower(trim($_POST['email']));

        if (!validar_dominio($emailPost)) {
            $error = 'Solo se aceptan correos terminados en <strong>' . DOMINIO_VALIDO . '</strong>.';
            $paso  = 1;

        } else {
            $usuario = buscar_usuario($emailPost);

            if (!$usuario) {
                $error = 'Correo no encontrado o inactivo. Verifica que sea correcto.';
                $paso  = 1;

            } elseif ((int) $usuario['validado'] === 1) {
                $_SESSION['val_paso']      = 3;
                $_SESSION['val_email']     = $emailPost;
                $_SESSION['val_nombre']    = trim($usuario['nombre'] . ' ' . $usuario['apellido']);
                $_SESSION['val_ya_estaba'] = true;
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;

            } else {
                if (generar_y_enviar_otp($emailPost)) {
                    $_SESSION['val_paso']   = 2;
                    $_SESSION['val_email']  = $emailPost;
                    $_SESSION['val_nombre'] = trim($usuario['nombre'] . ' ' . $usuario['apellido']);
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    $error = 'No se pudo enviar el correo. Intenta nuevamente en unos segundos.';
                    $paso  = 1;
                }
            }
        }
    }

    // ── POST paso 2: verificar código OTP ────────────────────────────────────
    elseif (isset($_POST['codigo'])) {
        if (!$email) {
            unset($_SESSION['val_paso'], $_SESSION['val_email'], $_SESSION['val_nombre']);
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }

        $codigoPost = trim($_POST['codigo']);
        $resultado  = verificar_otp($email, $codigoPost);

        if ($resultado['ok']) {
            marcar_validado($email);
            $_SESSION['val_paso']      = 3;
            $_SESSION['val_ya_estaba'] = false;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;

        } elseif ($resultado['expirado']) {
            $error = 'El código expiró o se agotaron los intentos. Solicita uno nuevo.';
            unset($_SESSION['val_paso'], $_SESSION['val_email'], $_SESSION['val_nombre']);
            $paso  = 1;
            $email = '';

        } else {
            $restantes = $resultado['intentos_restantes'];
            if ($restantes > 0) {
                $error = "Código incorrecto. Te quedan <strong>{$restantes}</strong> " . ($restantes === 1 ? 'intento' : 'intentos') . '.';
            } else {
                $error = 'Demasiados intentos fallidos. Solicita un nuevo código.';
                unset($_SESSION['val_paso'], $_SESSION['val_email'], $_SESSION['val_nombre']);
                $paso  = 1;
                $email = '';
            }
        }
    }

    // ── POST reenviar código ──────────────────────────────────────────────────
    elseif (isset($_POST['reenviar']) && $email) {
        if (generar_y_enviar_otp($email)) {
            $info = 'Código reenviado. Revisa tu correo (puede tardar unos segundos).';
        } else {
            $error = 'No se pudo reenviar. Intenta nuevamente.';
        }
        $paso = 2;
    }

    // ── POST iniciar configuración TOTP ──────────────────────────────────────
    elseif (isset($_POST['iniciar_totp']) && $email && $paso === 3) {
        try {
            $secret = tfa()->createSecret();
            $_SESSION['val_totp_secret'] = $secret;
            $_SESSION['val_paso']        = 4;
        } catch (\Throwable $e) {
            $error = 'No se pudo iniciar la configuración. Intenta nuevamente.';
            error_log('validacion TOTP iniciar: ' . $e->getMessage());
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // ── POST confirmar código TOTP ────────────────────────────────────────────
    elseif (isset($_POST['confirmar_totp']) && $email && $paso === 4) {
        $secret  = $_SESSION['val_totp_secret'] ?? '';
        $codigoT = preg_replace('/\D/', '', trim($_POST['totp_code'] ?? ''));

        if (!$secret || strlen($codigoT) !== 6) {
            $_SESSION['_val_error'] = 'Código inválido. Ingresa exactamente 6 dígitos.';
        } else {
            try {
                $ok = tfa()->verifyCode($secret, $codigoT);
                if ($ok) {
                    guardar_totp($email, $secret);
                    unset($_SESSION['val_totp_secret'], $_SESSION['_val_error']);
                    $_SESSION['val_paso'] = 5;
                } else {
                    $_SESSION['_val_error'] = 'Código incorrecto. Verifica que la hora de tu teléfono sea correcta e intenta nuevamente.';
                }
            } catch (\Throwable $e) {
                $_SESSION['_val_error'] = 'Error al verificar. Intenta nuevamente.';
                error_log('validacion TOTP confirmar: ' . $e->getMessage());
            }
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // ── POST cancelar configuración TOTP (volver al paso 3) ──────────────────
    elseif (isset($_POST['cancelar_totp'])) {
        unset($_SESSION['val_totp_secret']);
        $_SESSION['val_paso'] = 3;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // ── POST reiniciar (nueva validación) ─────────────────────────────────────
    elseif (isset($_POST['reiniciar'])) {
        session_destroy();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Sincronizar desde sesión
$paso   = $_SESSION['val_paso']   ?? $paso;
$email  = $_SESSION['val_email']  ?? $email;
$nombre = $_SESSION['val_nombre'] ?? $nombre;

// Leer y limpiar error de sesión (para patrón PRG del paso 4)
if ($error === null && !empty($_SESSION['_val_error'])) {
    $error = $_SESSION['_val_error'];
    unset($_SESSION['_val_error']);
}

// Leer estado TOTP actual del usuario (solo cuando es relevante)
$totp_info = ($paso === 3 || $paso === 5) && $email ? obtener_totp_info($email) : null;

// URI para el QR (generada en paso 4 con el secreto temporal)
$totp_uri  = '';
$totp_secret_display = '';
if ($paso === 4 && !empty($_SESSION['val_totp_secret']) && $email) {
    try {
        require_once VENDOR_PATH;
        $totp_uri            = tfa()->getQRText("{$email}", $_SESSION['val_totp_secret']);
        $totp_secret_display = $_SESSION['val_totp_secret'];
    } catch (\Throwable) {}
}

// ── Vista ─────────────────────────────────────────────────────────────────────

$emailSeguro  = htmlspecialchars($email);
$nombreSeguro = htmlspecialchars($nombre);
$dominioHint  = DOMINIO_VALIDO;

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Verificación de correo · Hotel Atankalama</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background: #f0f4f8; }
    .card { border-radius: 14px; }
    .card-header-custom {
      background: #1e3a5f;
      border-radius: 14px 14px 0 0;
      padding: 28px 24px 20px;
    }
    .step-indicator {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0;
      margin-bottom: 28px;
    }
    .step-dot {
      width: 32px; height: 32px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 13px; font-weight: 700;
      border: 2px solid #dee2e6;
      background: #fff; color: #adb5bd;
      position: relative; z-index: 1;
    }
    .step-dot.activo  { border-color: #0d6efd; background: #0d6efd; color: #fff; }
    .step-dot.listo   { border-color: #198754; background: #198754; color: #fff; }
    .step-line {
      height: 2px; width: 60px;
      background: #dee2e6;
    }
    .step-line.listo { background: #198754; }
    .codigo-input {
      font-size: 2.2rem;
      font-weight: 700;
      letter-spacing: .6rem;
      text-align: center;
      border: 2px solid #dee2e6;
      border-radius: 10px;
      padding: 14px;
      transition: border-color .2s;
    }
    .codigo-input:focus { border-color: #0d6efd; box-shadow: 0 0 0 .2rem rgba(13,110,253,.15); }
    .success-icon { font-size: 4rem; color: #198754; }
    .already-icon { font-size: 4rem; color: #0d6efd; }
    .totp-secret-box {
      font-family: monospace;
      font-size: 1rem;
      letter-spacing: .12em;
      background: #f8f9fa;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      padding: 10px 14px;
      word-break: break-all;
      cursor: pointer;
    }
    #qrcode canvas, #qrcode img { display: block; margin: 0 auto; }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center py-4" style="min-height:100vh">

<div style="max-width:460px; width:100%; padding:0 16px">

  <div class="card shadow border-0 overflow-hidden">

    <!-- Encabezado fijo -->
    <div class="card-header-custom text-center text-white">
      <h5 class="fw-bold mb-1" style="font-size:1.1rem">
        <i class="bi bi-envelope-check me-2"></i>Verificación de correo
      </h5>
      <p class="mb-0 opacity-75" style="font-size:13px">Hotel Atankalama &mdash; Sistema interno</p>
    </div>

    <div class="card-body p-4 p-md-5">

      <?php if ($paso === 5): ?>
      <!-- ═══════════════════════════════════════════════════
           PASO 5 — TOTP vinculado exitosamente
      ════════════════════════════════════════════════════ -->
      <div class="text-center">
        <div class="mb-3">
          <i class="bi bi-shield-check" style="font-size:4rem;color:#198754"></i>
        </div>
        <h5 class="fw-bold text-success mb-2">¡Autenticador vinculado!</h5>
        <p class="text-muted mb-1" style="font-size:14px">
          A partir de ahora puedes usar Google Authenticator para ingresar a los sistemas del hotel.
        </p>
        <p class="text-muted" style="font-size:13px">
          Cuando ingreses a una app, en lugar de esperar un correo, abre la app y usa el código que aparece ahí.
        </p>
        <div class="alert alert-success border-0 py-2 mt-3" style="font-size:13px; background:#f0faf4">
          <i class="bi bi-info-circle me-1"></i>
          Si alguna vez pierdes acceso al autenticador, puedes volver a esta página y reconectar.
        </div>
        <hr class="my-4">
        <a href="https://www.atankalama.com/login/" class="btn btn-primary w-100 mb-2">
          <i class="bi bi-box-arrow-in-right me-1"></i>Ingresar al portal
        </a>
        <form method="POST" class="text-center">
          <button type="submit" name="reiniciar" value="1"
                  class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Verificar otro correo
          </button>
        </form>
      </div>

      <?php elseif ($paso === 4): ?>
      <!-- ═══════════════════════════════════════════════════
           PASO 4 — Escanear QR y confirmar TOTP
      ════════════════════════════════════════════════════ -->
      <div class="text-center mb-3">
        <i class="bi bi-qr-code" style="font-size:2rem;color:#0d6efd"></i>
        <h6 class="fw-bold mt-2 mb-0">Vincula tu autenticador</h6>
        <p class="text-muted" style="font-size:13px">Escanea el QR con Google Authenticator (o Authy)</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger py-2 small">
          <i class="bi bi-exclamation-triangle me-1"></i><?= $error ?>
        </div>
      <?php endif; ?>

      <!-- QR Code -->
      <div class="text-center mb-3">
        <div id="qrcode" class="mb-2"></div>
        <p class="text-muted mb-1" style="font-size:11px">¿No puedes escanear? Ingresa este código manualmente:</p>
        <div class="totp-secret-box text-center mx-auto" style="max-width:280px"
             id="secretBox" title="Click para copiar">
          <?= htmlspecialchars($totp_secret_display) ?>
        </div>
        <p class="text-muted mt-1" style="font-size:10px" id="copiadoMsg"></p>
      </div>

      <hr class="my-3">

      <!-- Instrucciones -->
      <div class="alert alert-light border py-2 mb-3" style="font-size:12px">
        <ol class="mb-0 ps-3">
          <li>Abre <strong>Google Authenticator</strong> en tu teléfono</li>
          <li>Toca <strong>"+"</strong> y elige <em>Escanear código QR</em></li>
          <li>Apunta la cámara al código de arriba</li>
          <li>Ingresa el código de 6 dígitos que aparece abajo</li>
        </ol>
      </div>

      <!-- Formulario de confirmación -->
      <form method="POST" id="frmTotp" autocomplete="off">
        <input type="hidden" name="confirmar_totp" value="1">
        <div class="mb-3">
          <label class="form-label fw-semibold text-center d-block" style="font-size:14px">
            Código de confirmación
          </label>
          <input type="text" name="totp_code" id="inputTotp"
                 class="form-control codigo-input"
                 maxlength="6" pattern="\d{6}" inputmode="numeric"
                 placeholder="000000"
                 required autofocus>
          <div class="form-text text-center mt-1" style="font-size:11px">
            El código cambia cada 30 segundos
          </div>
        </div>
        <button type="submit" id="btnConfirmar" class="btn btn-success w-100 py-2">
          <i class="bi bi-shield-check me-1"></i>Confirmar vinculación
        </button>
      </form>

      <div class="text-center mt-3">
        <form method="POST">
          <button type="submit" name="cancelar_totp" value="1"
                  class="btn btn-link btn-sm text-muted text-decoration-none p-0">
            <i class="bi bi-arrow-left me-1"></i>Cancelar y volver
          </button>
        </form>
      </div>

      <?php elseif ($paso === 3): ?>
      <!-- ═══════════════════════════════════════════════════
           PASO 3 — ÉXITO de validación de correo
      ════════════════════════════════════════════════════ -->

      <?php $yaEstaba = isset($_SESSION['val_ya_estaba']) && $_SESSION['val_ya_estaba']; ?>

      <div class="text-center">
        <div class="mb-3">
          <?php if ($yaEstaba): ?>
            <i class="bi bi-patch-check-fill already-icon"></i>
          <?php else: ?>
            <i class="bi bi-check-circle-fill success-icon"></i>
          <?php endif; ?>
        </div>

        <?php if ($yaEstaba): ?>
          <h5 class="fw-bold text-primary mb-1">¡Ya estás verificado!</h5>
          <p class="text-muted mb-1" style="font-size:14px">
            <?= $nombreSeguro ? "Hola <strong>{$nombreSeguro}</strong>, tu" : 'Tu' ?> correo
            <strong><?= $emailSeguro ?></strong> ya fue verificado anteriormente.
          </p>
        <?php else: ?>
          <h5 class="fw-bold text-success mb-1">¡Verificación exitosa!</h5>
          <p class="text-muted mb-1" style="font-size:14px">
            <?= $nombreSeguro ? "¡Hola <strong>{$nombreSeguro}</strong>!" : '' ?>
            Tu correo <strong><?= $emailSeguro ?></strong> quedó verificado correctamente.
          </p>
          <p class="text-muted" style="font-size:13px">
            A partir de ahora recibirás notificaciones de los sistemas internos del hotel.
          </p>
        <?php endif; ?>
      </div>

      <!-- ── Panel TOTP ───────────────────────────────────── -->
      <hr class="my-4">

      <?php if ($totp_info && $totp_info['habilitado']): ?>
        <!-- Ya tiene TOTP activado -->
        <div class="d-flex align-items-start gap-3 mb-3">
          <i class="bi bi-shield-check text-success mt-1" style="font-size:1.5rem;flex-shrink:0"></i>
          <div>
            <p class="fw-semibold mb-1" style="font-size:14px">Autenticador vinculado</p>
            <p class="text-muted mb-0" style="font-size:12px">
              Ya tienes Google Authenticator configurado. Si cambiaste de teléfono o quieres reconectar, puedes volver a vincularlo.
            </p>
          </div>
        </div>
        <form method="POST">
          <button type="submit" name="iniciar_totp" value="1"
                  class="btn btn-outline-primary btn-sm w-100">
            <i class="bi bi-arrow-repeat me-1"></i>Reconectar autenticador
          </button>
        </form>

      <?php else: ?>
        <!-- No tiene TOTP — ofrecer activación -->
        <div class="d-flex align-items-start gap-3 mb-3">
          <i class="bi bi-shield-plus text-primary mt-1" style="font-size:1.5rem;flex-shrink:0"></i>
          <div>
            <p class="fw-semibold mb-1" style="font-size:14px">¿Quieres más seguridad?</p>
            <p class="text-muted mb-0" style="font-size:12px">
              Vincula Google Authenticator y la próxima vez que ingreses a una app del hotel
              usarás un código de tu teléfono en lugar de esperar un correo.
            </p>
          </div>
        </div>
        <form method="POST">
          <button type="submit" name="iniciar_totp" value="1"
                  class="btn btn-primary btn-sm w-100 mb-2">
            <i class="bi bi-phone me-1"></i>Vincular Google Authenticator
          </button>
        </form>
        <p class="text-center text-muted mb-0" style="font-size:11px">
          Es opcional. Puedes hacerlo en cualquier momento volviendo a esta página.
        </p>
      <?php endif; ?>

      <hr class="my-3">

      <a href="https://www.atankalama.com/login/" class="btn btn-primary w-100 mb-2">
        <i class="bi bi-box-arrow-in-right me-1"></i>Ingresar al portal
      </a>
      <form method="POST" class="text-center">
        <button type="submit" name="reiniciar" value="1"
                class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-arrow-counterclockwise me-1"></i>Verificar otro correo
        </button>
      </form>

      <?php elseif ($paso === 2): ?>
      <!-- ═══════════════════════════════════════════════════
           PASO 2 — Ingresar código OTP
      ════════════════════════════════════════════════════ -->

        <!-- Indicador de pasos -->
        <div class="step-indicator">
          <div class="step-dot listo"><i class="bi bi-check-lg" style="font-size:14px"></i></div>
          <div class="step-line listo"></div>
          <div class="step-dot activo">2</div>
        </div>

        <div class="text-center mb-4">
          <p class="fw-semibold mb-1" style="font-size:15px">Revisa tu correo</p>
          <p class="text-muted" style="font-size:13px">
            Enviamos un código a <strong><?= $emailSeguro ?></strong>
            <?= $nombre ? '(' . $nombreSeguro . ')' : '' ?><br>
            El código es válido por <strong><?= OTP_MINUTOS ?> minutos</strong>.
          </p>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-danger py-2 small">
            <i class="bi bi-exclamation-triangle me-1"></i><?= $error ?>
          </div>
        <?php endif; ?>

        <?php if ($info): ?>
          <div class="alert alert-info py-2 small">
            <i class="bi bi-info-circle me-1"></i><?= htmlspecialchars($info) ?>
          </div>
        <?php endif; ?>

        <!-- Formulario código -->
        <form method="POST" id="frmCodigo" autocomplete="off">
          <div class="mb-4">
            <input type="text" name="codigo" id="inputCodigo"
                   class="form-control codigo-input"
                   maxlength="6" pattern="\d{6}" inputmode="numeric"
                   autocomplete="one-time-code"
                   placeholder="000000"
                   required autofocus>
            <div class="form-text text-center mt-2">Ingresa el código de 6 dígitos</div>
          </div>
          <button type="submit" class="btn btn-primary w-100 py-2">
            <i class="bi bi-check-circle me-1"></i>Verificar código
          </button>
        </form>

        <!-- Reenviar y volver -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <form method="POST">
            <button type="submit" name="reenviar" value="1"
                    class="btn btn-link btn-sm p-0 text-decoration-none text-muted">
              <i class="bi bi-arrow-repeat me-1"></i>Reenviar código
            </button>
          </form>
          <form method="POST">
            <button type="submit" name="reiniciar" value="1"
                    class="btn btn-link btn-sm p-0 text-decoration-none text-muted">
              <i class="bi bi-arrow-left me-1"></i>Cambiar correo
            </button>
          </form>
        </div>

      <?php else: ?>
      <!-- ═══════════════════════════════════════════════════
           PASO 1 — Ingresar email
      ════════════════════════════════════════════════════ -->

        <!-- Indicador de pasos -->
        <div class="step-indicator">
          <div class="step-dot activo">1</div>
          <div class="step-line"></div>
          <div class="step-dot">2</div>
        </div>

        <div class="text-center mb-4">
          <p class="fw-semibold mb-1" style="font-size:15px">Ingresa tu correo del hotel</p>
          <p class="text-muted" style="font-size:13px">
            Recibirás un código de 6 dígitos para verificar que tu correo
            <strong><?= $dominioHint ?></strong> funciona correctamente.
          </p>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-danger py-2 small">
            <i class="bi bi-exclamation-triangle me-1"></i><?= $error ?>
          </div>
        <?php endif; ?>

        <form method="POST" id="frmEmail" autocomplete="off">
          <div class="mb-3">
            <label for="inputEmail" class="form-label fw-semibold">
              Correo electrónico
            </label>
            <div class="input-group">
              <span class="input-group-text bg-white">
                <i class="bi bi-envelope text-muted"></i>
              </span>
              <input type="email" name="email" id="inputEmail"
                     class="form-control"
                     placeholder="tunombre<?= $dominioHint ?>"
                     pattern="^[^@]+@atankalama\.com$"
                     title="Solo correos terminados en <?= $dominioHint ?>"
                     required autofocus>
            </div>
            <div class="form-text">
              Solo se aceptan correos terminados en <strong><?= $dominioHint ?></strong>
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2" id="btnEnviar">
            <i class="bi bi-send me-1"></i>Enviar código de verificación
          </button>
        </form>

      <?php endif; ?>

    </div><!-- /card-body -->
  </div><!-- /card -->

  <p class="text-center text-muted mt-3" style="font-size:11px">
    &copy; <?= date('Y') ?> Rodrigo Jaque Escobar &mdash; Todos los derechos reservados.<br>
    Se concede uso operacional de esta aplicación. El código fuente y la aplicación
    permanecen como propiedad exclusiva del autor.
  </p>

</div><!-- /container -->

<?php if ($paso === 4 && $totp_uri): ?>
<!-- QR Code JS (solo en paso 4) -->
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
  new QRCode(document.getElementById('qrcode'), {
    text: <?= json_encode($totp_uri) ?>,
    width: 200,
    height: 200,
    correctLevel: QRCode.CorrectLevel.M
  });

  // Solo filtrar a dígitos (sin auto-submit — el usuario confirma con el botón)
  var inputTotp = document.getElementById('inputTotp');
  if (inputTotp) {
    inputTotp.addEventListener('input', function () {
      this.value = this.value.replace(/\D/g, '').slice(0, 6);
    });
  }

  // Mostrar spinner al confirmar (no deshabilitamos el botón para no perder el POST)
  var frmTotp = document.getElementById('frmTotp');
  if (frmTotp) {
    frmTotp.addEventListener('submit', function () {
      var btn = document.getElementById('btnConfirmar');
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Verificando...';
      btn.classList.add('disabled');  // visual, no deshabilita el input
    });
  }

  // Copiar secreto al portapapeles
  var secretBox = document.getElementById('secretBox');
  if (secretBox) {
    secretBox.addEventListener('click', function () {
      var texto = this.innerText.trim();
      navigator.clipboard.writeText(texto).then(function () {
        document.getElementById('copiadoMsg').textContent = '✓ Copiado al portapapeles';
        setTimeout(function () {
          document.getElementById('copiadoMsg').textContent = '';
        }, 2000);
      });
    });
  }
</script>
<?php endif; ?>

<script>
// Auto-submit al completar 6 dígitos (paso 2)
const inputCodigo = document.getElementById('inputCodigo');
if (inputCodigo) {
  inputCodigo.addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '').slice(0, 6);
    if (this.value.length === 6) {
      document.getElementById('frmCodigo').submit();
    }
  });
}

// Deshabilitar botón al enviar (evitar doble clic)
const frmEmail = document.getElementById('frmEmail');
if (frmEmail) {
  frmEmail.addEventListener('submit', function () {
    const btn = document.getElementById('btnEnviar');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enviando...';
  });
}
</script>

</body>
</html>
