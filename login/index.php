<?php
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 * Este software es propiedad exclusiva de su autor.
 * Se concede un derecho de uso limitado al cliente. No se transfiere
 * la propiedad del código ni de la aplicación.
 *
 * @author  Rodrigo Jaque Escobar
 * @project Portal de Acceso — Hotel Atankalama
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/shared/acceso_db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

define('PORTAL_SK',    'portal_email');
define('PORTAL_EXP',   'portal_expires');
define('PORTAL_HORAS', 4);

$route = $_GET['route'] ?? 'dashboard';

// ── Verificar sesión portal ───────────────────────────────
$pEmail = $_SESSION[PORTAL_SK]  ?? null;
$pExp   = $_SESSION[PORTAL_EXP] ?? 0;

if ($pEmail && time() > $pExp) {
    unset($_SESSION[PORTAL_SK], $_SESSION[PORTAL_EXP]);
    $pEmail = null;
}

if ($pEmail) {
    try {
        $s = acceso_pdo()->prepare(
            "SELECT forzar_logout FROM chk_usuarios WHERE email = ? LIMIT 1"
        );
        $s->execute([$pEmail]);
        $row = $s->fetch();
        if ($row && (int)$row['forzar_logout'] === 1) {
            unset($_SESSION[PORTAL_SK], $_SESSION[PORTAL_EXP]);
            acceso_pdo()->prepare(
                "UPDATE chk_usuarios SET sesion_expira_en = NULL WHERE email = ?"
            )->execute([$pEmail]);
            $pEmail = null;
        }
    } catch (\Throwable) {}
}

// ── Proteger rutas privadas ───────────────────────────────
$rutasAuth = [
    'auth/login', 'auth/send-otp', 'auth/verify', 'auth/verify-code',
    'auth/totp',  'auth/verify-totp', 'auth/totp-fallback', 'auth/logout',
    'terms',      'privacy',
];

if (!in_array($route, $rutasAuth) && !$pEmail) {
    $q = ($route !== 'dashboard') ? '&redirect=' . urlencode($route) : '';
    header('Location: index.php?route=auth/login' . $q);
    exit;
}

// ── Dispatch ──────────────────────────────────────────────
switch ($route) {
    case 'auth/login':         pLogin();                   break;
    case 'auth/send-otp':      pSendOtp();                 break;
    case 'auth/verify':        pVerify();                  break;
    case 'auth/verify-code':   pVerifyCode();              break;
    case 'auth/totp':           pTotp();            break;
    case 'auth/verify-totp':   pVerifyTotp();      break;
    case 'auth/totp-fallback': pTotpFallback();    break;
    case 'auth/logout':        pLogout();          break;
    case 'perfil/editar':      pPerfil($pEmail);           break;
    case 'perfil/actualizar':  pActualizarPerfil($pEmail); break;
    case 'terms':              pTerms();                   break;
    case 'privacy':            pPrivacy();                 break;
    case 'version':            pVersion();                 break;
    default:                   pDashboard($pEmail);        break;
}

// ═══════════════════════════════════════════════════════════
// AUTH — Formulario de email
// ═══════════════════════════════════════════════════════════

function pLogin(): void
{
    $error    = $_SESSION['_portal_err'] ?? null;
    $redirect = htmlspecialchars($_GET['redirect'] ?? '');
    $year     = date('Y');
    unset($_SESSION['_portal_err']);

    portHtmlIni('Acceso · Hotel Atankalama');
    ?>
    <style>
      :root {
        --hotel-dark: #1e3a5f;
        --hotel-gold: #c49b63;
        --hotel-gold-dark: #a68252;
        --hotel-bg: #f4f6f9;
      }
      body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--hotel-bg);
        color: #334155;
      }
      .bg-zoom {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: -1;
        overflow: hidden;
      }
      .bg-zoom-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: var(--hotel-dark);
        background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('https://www.atankalama.com/public/uploads/piscinaAtankalama.webp');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        animation: kenburns 40s infinite alternate ease-in-out;
        will-change: transform;
      }
      @keyframes kenburns {
        0% { transform: scale(1); }
        100% { transform: scale(1.2); }
      }
      .card-login {
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,.2);
        border: 1px solid rgba(255,255,255,.1);
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
      }
      .btn-hotel-gold {
        background-color: var(--hotel-gold);
        color: white;
        border: none;
        font-weight: 500;
        transition: background-color .2s;
      }
      .btn-hotel-gold:hover {
        background-color: var(--hotel-gold-dark);
        color: white;
      }
      .form-control:focus {
        border-color: var(--hotel-gold);
        box-shadow: 0 0 0 0.25rem rgba(196,155,99,.15);
      }
      .text-gold { color: var(--hotel-gold); }
    </style>
    <body class="d-flex align-items-center justify-content-center" style="min-height:100vh">
    <div class="bg-zoom"><div class="bg-zoom-image"></div></div>
    <div style="max-width:360px;width:100%;padding:16px;position:relative;z-index:1">

      <div class="text-center mb-4">
        <img src="https://www.atankalama.com/public/uploads/logoHotelAtankalama.webp"
             alt="Hotel Atankalama"
             style="max-width:160px;width:100%;height:auto;margin-bottom:8px;filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3))">
        <p class="text-white small mb-0 opacity-75" style="font-size:.75rem">Portal de acceso a los sistemas</p>
      </div>

      <div class="card card-login border-0 overflow-hidden">
        <div style="height: 3px; background-color: var(--hotel-gold)"></div>
        <div class="card-body p-4">
          <h6 class="fw-bold mb-3 text-dark text-center" style="font-size:.9rem">
            <i class="bi bi-shield-lock me-2 text-gold"></i>Iniciar Sesión
          </h6>
          <?php if ($error): ?>
            <div class="alert alert-danger border-0 py-2 small shadow-sm" style="font-size:.75rem">
              <i class="bi bi-exclamation-triangle me-1"></i>
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif ?>
          <form method="POST" action="index.php?route=auth/send-otp">
            <input type="hidden" name="redirect" value="<?= $redirect ?>">
            <div class="mb-3">
              <label class="form-label small fw-medium text-muted" style="font-size:.75rem">Correo electrónico</label>
              <input type="email" name="email" class="form-control border-light-subtle bg-light"
                     placeholder="tu@correo.com" required autofocus style="font-size: .85rem">
            </div>
            <button type="submit" class="btn btn-hotel-gold w-100 py-2 shadow-sm" style="font-size:.9rem">
              <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
              Continuar <i class="bi bi-arrow-right ms-1"></i>
            </button>
          </form>
        </div>
      </div>

      <div class="text-center text-white mt-4 opacity-50" style="font-size:.65rem">
        <p class="mb-1">&copy; <?= $year ?> Hotel Atankalama &mdash; Todos los derechos reservados.</p>
        <p class="mb-2">Desarrollado por Rodrigo Jaque Escobar</p>
        <div class="d-flex justify-content-center gap-2">
          <a href="index.php?route=terms" class="text-white text-decoration-none">Términos y Condiciones</a>
          <span>|</span>
          <a href="index.php?route=privacy" class="text-white text-decoration-none">Política de Privacidad</a>
        </div>
      </div>
    </div>
    </body></html>
    <?php
}

// ═══════════════════════════════════════════════════════════
// AUTH — Procesar envío OTP
// ═══════════════════════════════════════════════════════════

function pSendOtp(): void
{
    $email    = trim($_POST['email']    ?? '');
    $redirect = trim($_POST['redirect'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['_portal_err'] = 'Ingresa un correo válido.';
        header('Location: index.php?route=auth/login');
        exit;
    }

    $pdo  = acceso_pdo();
    $stmt = $pdo->prepare(
        "SELECT id FROM chk_usuarios WHERE email = ? AND estado = 'activo' LIMIT 1"
    );
    $stmt->execute([$email]);

    if (!$stmt->fetch()) {
        $_SESSION['_portal_err'] = 'Correo no autorizado o sin acceso.';
        header('Location: index.php?route=auth/login');
        exit;
    }

    // ¿TOTP activo? → redirigir sin enviar correo
    if (totpActivo($pdo, $email)) {
        $_SESSION['_portal_otp_email']    = $email;
        $_SESSION['_portal_otp_redirect'] = $redirect;
        header('Location: index.php?route=auth/totp');
        exit;
    }

    // ── Throttling: Anti-fuerza bruta ────────────────────────
    $checkThrottle = $pdo->prepare("
        SELECT COUNT(*) FROM chk_login_tokens 
        WHERE (email = ? OR ip_address = ?) 
          AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ");
    $checkThrottle->execute([$email, $_SERVER['REMOTE_ADDR'] ?? '']);
    if ((int)$checkThrottle->fetchColumn() >= 3) {
        $_SESSION['_portal_err'] = 'Demasiados intentos. Espera unos minutos antes de solicitar otro código.';
        header('Location: index.php?route=auth/login');
        exit;
    }

    // Invalidar tokens previos
    $pdo->prepare("UPDATE chk_login_tokens SET used = 1 WHERE email = ? AND used = 0")
        ->execute([$email]);

    // Generar código
    $code      = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    $pdo->prepare("
        INSERT INTO chk_login_tokens
              (email, token, expires_at, used, attempts, ip_address, user_agent)
        VALUES (?, ?, ?, 0, 0, ?, ?)
    ")->execute([
        $email, $code, $expiresAt,
        $_SERVER['REMOTE_ADDR']     ?? '',
        substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
    ]);

    if (!enviarOtp($email, $code)) {
        $_SESSION['_portal_err'] = 'No se pudo enviar el correo. Intenta nuevamente.';
        header('Location: index.php?route=auth/login');
        exit;
    }

    $_SESSION['_portal_otp_email']    = $email;
    $_SESSION['_portal_otp_redirect'] = $redirect;
    header('Location: index.php?route=auth/verify');
    exit;
}

// ═══════════════════════════════════════════════════════════
// AUTH — Formulario de código OTP
// ═══════════════════════════════════════════════════════════

function pVerify(): void
{
    if (empty($_SESSION['_portal_otp_email'])) {
        header('Location: index.php?route=auth/login');
        exit;
    }

    $email = htmlspecialchars($_SESSION['_portal_otp_email']);
    $error = $_SESSION['_portal_err'] ?? null;
    $year  = date('Y');
    unset($_SESSION['_portal_err']);

    portHtmlIni('Verificar código · Hotel Atankalama');
    ?>
    <style>
      :root {
        --hotel-dark: #1e3a5f;
        --hotel-gold: #c49b63;
        --hotel-gold-dark: #a68252;
        --hotel-bg: #f4f6f9;
      }
      body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--hotel-bg);
        color: #334155;
      }
      .bg-zoom {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: -1;
        overflow: hidden;
      }
      .bg-zoom-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: var(--hotel-dark);
        background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('https://www.atankalama.com/public/uploads/piscinaAtankalama.webp');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        animation: kenburns 40s infinite alternate ease-in-out;
        will-change: transform;
      }
      @keyframes kenburns {
        0% { transform: scale(1); }
        100% { transform: scale(1.2); }
      }
      .card-verify {
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,.2);
        border: 1px solid rgba(255,255,255,.1);
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
      }
      .btn-hotel-gold {
        background-color: var(--hotel-gold);
        color: white;
        border: none;
        font-weight: 500;
        transition: background-color .2s;
      }
      .btn-hotel-gold:hover {
        background-color: var(--hotel-gold-dark);
        color: white;
      }
      .form-control:focus {
        border-color: var(--hotel-gold);
        box-shadow: 0 0 0 0.25rem rgba(196,155,99,.15);
      }
      .text-gold { color: var(--hotel-gold); }
    </style>
    <body class="d-flex align-items-center justify-content-center" style="min-height:100vh">
    <div class="bg-zoom"><div class="bg-zoom-image"></div></div>
    <div style="max-width:360px;width:100%;padding:16px;position:relative;z-index:1">
      <div class="card card-verify border-0 overflow-hidden">
        <div style="height: 3px; background-color: var(--hotel-gold)"></div>
        <div class="card-body p-4">
          <div class="text-center mb-3">
            <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3" style="width: 60px; height: 60px">
              <i class="bi bi-envelope-check text-gold" style="font-size:1.8rem"></i>
            </div>
            <h6 class="fw-bold mb-1">Verifica tu código</h6>
            <p class="text-muted small">Enviado a <strong class="text-dark"><?= $email ?></strong></p>
          </div>
          <?php if ($error): ?>
            <div class="alert alert-danger border-0 py-2 small shadow-sm" style="font-size:.75rem">
              <i class="bi bi-exclamation-triangle me-1"></i>
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif ?>
          <form method="POST" action="index.php?route=auth/verify-code" id="frmOtp">
            <div class="mb-3">
              <input type="text" name="code" id="inputCodigo"
                     class="form-control text-center fw-bold border-light-subtle bg-light"
                     style="font-size:1.8rem;letter-spacing:.4rem"
                     maxlength="6" pattern="\d{6}" inputmode="numeric"
                     autocomplete="one-time-code" placeholder="000000"
                     required autofocus>
            </div>
            <button type="submit" class="btn btn-hotel-gold w-100 py-2 shadow-sm" style="font-size:.9rem">
              <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
              <i class="bi bi-check-circle me-1"></i> Verificar Código
            </button>
          </form>
          <div class="text-center mt-3">
            <a href="index.php?route=auth/login" class="text-muted small text-decoration-none">
              <i class="bi bi-arrow-left"></i> Usar otro correo
            </a>
          </div>
        </div>
      </div>
      <p class="text-center text-white small mt-3 opacity-50" style="font-size:.65rem">&copy; <?= $year ?> Hotel Atankalama</p>
    </div>
    <script>
      document.getElementById('inputCodigo').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
        if (this.value.length === 6) document.getElementById('frmOtp').requestSubmit();
      });
    </script>
    </body></html>
    <?php
}

// ═══════════════════════════════════════════════════════════
// AUTH — Verificar código OTP
// ═══════════════════════════════════════════════════════════

function pVerifyCode(): void
{
    $email    = $_SESSION['_portal_otp_email']    ?? '';
    $redirect = $_SESSION['_portal_otp_redirect'] ?? '';
    $code     = trim($_POST['code'] ?? '');

    if (!$email) {
        header('Location: index.php?route=auth/login');
        exit;
    }

    $pdo  = acceso_pdo();
    $stmt = $pdo->prepare("
        SELECT id, token, attempts
        FROM chk_login_tokens
        WHERE email = ? AND used = 0 AND expires_at > NOW() AND attempts < 5
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute([$email]);
    $token = $stmt->fetch();

    if (!$token) {
        $_SESSION['_portal_err'] = 'Código expirado o agotado. Solicita uno nuevo.';
        unset($_SESSION['_portal_otp_email'], $_SESSION['_portal_otp_redirect']);
        header('Location: index.php?route=auth/login');
        exit;
    }

    $pdo->prepare("UPDATE chk_login_tokens SET attempts = attempts + 1 WHERE id = ?")
        ->execute([$token['id']]);

    if (!hash_equals($token['token'], $code)) {
        $restantes = 5 - ((int)$token['attempts'] + 1);
        $_SESSION['_portal_err'] = "Código incorrecto. Intentos restantes: {$restantes}.";
        header('Location: index.php?route=auth/verify');
        exit;
    }

    $pdo->prepare("UPDATE chk_login_tokens SET used = 1 WHERE id = ?")->execute([$token['id']]);
    registrarLog($pdo, $email, 'portal');
    abrirSesiones($email);

    unset($_SESSION['_portal_otp_email'], $_SESSION['_portal_otp_redirect']);
    header('Location: index.php?route=' . urlencode($redirect ?: 'dashboard'));
    exit;
}

// ═══════════════════════════════════════════════════════════
// AUTH — Formulario TOTP (Google Authenticator)
// ═══════════════════════════════════════════════════════════

function pTotp(): void
{
    if (empty($_SESSION['_portal_otp_email'])) {
        header('Location: index.php?route=auth/login');
        exit;
    }

    $error = $_SESSION['_portal_err'] ?? null;
    $year  = date('Y');
    unset($_SESSION['_portal_err']);

    portHtmlIni('Autenticador · Hotel Atankalama');
    ?>
    <style>
      :root {
        --hotel-dark: #1e3a5f;
        --hotel-gold: #c49b63;
        --hotel-gold-dark: #a68252;
        --hotel-bg: #f4f6f9;
      }
      body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--hotel-bg);
        color: #334155;
      }
      .bg-zoom {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: -1;
        overflow: hidden;
      }
      .bg-zoom-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: var(--hotel-dark);
        background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('https://www.atankalama.com/public/uploads/piscinaAtankalama.webp');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        animation: kenburns 40s infinite alternate ease-in-out;
        will-change: transform;
      }
      @keyframes kenburns {
        0% { transform: scale(1); }
        100% { transform: scale(1.2); }
      }
      .card-verify {
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,.2);
        border: 1px solid rgba(255,255,255,.1);
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
      }
      .btn-hotel-gold {
        background-color: var(--hotel-gold);
        color: white;
        border: none;
        font-weight: 500;
        transition: background-color .2s;
      }
      .btn-hotel-gold:hover {
        background-color: var(--hotel-gold-dark);
        color: white;
      }
      .form-control:focus {
        border-color: var(--hotel-gold);
        box-shadow: 0 0 0 0.25rem rgba(196,155,99,.15);
      }
      .text-gold { color: var(--hotel-gold); }
    </style>
    <body class="d-flex align-items-center justify-content-center" style="min-height:100vh">
    <div class="bg-zoom"><div class="bg-zoom-image"></div></div>
    <div style="max-width:360px;width:100%;padding:16px;position:relative;z-index:1">
      <div class="card card-verify border-0 overflow-hidden">
        <div style="height: 3px; background-color: var(--hotel-gold)"></div>
        <div class="card-body p-4">
          <div class="text-center mb-3">
            <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3" style="width: 60px; height: 60px">
              <i class="bi bi-phone text-gold" style="font-size:1.8rem"></i>
            </div>
            <h6 class="fw-bold mb-1">Código autenticador</h6>
            <p class="text-muted small">Ingresa el código de <strong>Google Authenticator</strong></p>
          </div>
          <?php if ($error): ?>
            <div class="alert alert-danger border-0 py-2 small shadow-sm" style="font-size:.75rem">
              <i class="bi bi-exclamation-triangle me-1"></i>
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif ?>
          <form method="POST" action="index.php?route=auth/verify-totp" id="frmTotp">
            <div class="mb-3">
              <input type="text" name="code" id="inputTotp"
                     class="form-control text-center fw-bold border-light-subtle bg-light"
                     style="font-size:1.8rem;letter-spacing:.4rem"
                     maxlength="6" pattern="\d{6}" inputmode="numeric"
                     autocomplete="one-time-code" placeholder="000000"
                     required autofocus>
              <div class="form-text text-center opacity-75 small mt-2" style="font-size:.7rem">El código cambia cada 30 segundos</div>
            </div>
            <button type="submit" class="btn btn-hotel-gold w-100 py-2 shadow-sm" style="font-size:.9rem">
              <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
              <i class="bi bi-check-circle me-1"></i> Verificar
            </button>
          </form>
          <div class="text-center mt-3 d-flex flex-column gap-2">
            <a href="index.php?route=auth/totp-fallback" class="small text-gold text-decoration-none fw-medium"
               style="font-size:.75rem"
               onclick="return confirm('¿Prefieres recibir el código por correo?')">
              <i class="bi bi-envelope me-1"></i> Recibir código por correo
            </a>
            <a href="index.php?route=auth/login" class="text-muted small text-decoration-none opacity-75" style="font-size:.75rem">
              <i class="bi bi-arrow-left"></i> Usar otro correo
            </a>
          </div>
        </div>
      </div>
      <p class="text-center text-white small mt-3 opacity-50" style="font-size:.65rem">&copy; <?= $year ?> Hotel Atankalama</p>
    </div>
    <script>
      document.getElementById('inputTotp').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 6);
        if (this.value.length === 6) document.getElementById('frmTotp').requestSubmit();
      });
    </script>
    </body></html>
    <?php
}

// ═══════════════════════════════════════════════════════════
// AUTH — Fallback: enviar OTP por correo desde la pantalla TOTP
// ═══════════════════════════════════════════════════════════

function pTotpFallback(): void
{
    $email    = $_SESSION['_portal_otp_email']    ?? '';
    $redirect = $_SESSION['_portal_otp_redirect'] ?? '';

    if (!$email) {
        header('Location: index.php?route=auth/login');
        exit;
    }

    $pdo = acceso_pdo();

    // Invalidar tokens previos
    $pdo->prepare("UPDATE chk_login_tokens SET used = 1 WHERE email = ? AND used = 0")
        ->execute([$email]);

    // Generar y guardar código
    $code      = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    $pdo->prepare("
        INSERT INTO chk_login_tokens
              (email, token, expires_at, used, attempts, ip_address, user_agent)
        VALUES (?, ?, ?, 0, 0, ?, ?)
    ")->execute([
        $email, $code, $expiresAt,
        $_SERVER['REMOTE_ADDR']     ?? '',
        substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
    ]);

    if (!enviarOtp($email, $code)) {
        $_SESSION['_portal_err'] = 'No se pudo enviar el correo. Intenta nuevamente.';
        header('Location: index.php?route=auth/totp');
        exit;
    }

    // Redirigir al formulario OTP normal (la sesión _portal_otp_email ya está guardada)
    header('Location: index.php?route=auth/verify');
    exit;
}

// ═══════════════════════════════════════════════════════════
// AUTH — Verificar TOTP
// ═══════════════════════════════════════════════════════════

function pVerifyTotp(): void
{
    $email    = $_SESSION['_portal_otp_email']    ?? '';
    $redirect = $_SESSION['_portal_otp_redirect'] ?? '';
    $code     = preg_replace('/\D/', '', trim($_POST['code'] ?? ''));

    if (!$email) {
        header('Location: index.php?route=auth/login');
        exit;
    }

    $pdo  = acceso_pdo();
    $stmt = $pdo->prepare("SELECT totp_secret FROM chk_usuarios WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $secret = $stmt->fetchColumn();

    if (!$secret || strlen($code) !== 6) {
        $_SESSION['_portal_err'] = 'Código inválido. Intenta nuevamente.';
        header('Location: index.php?route=auth/totp');
        exit;
    }

    cargarVendor();

    try {
        $tfa = new \RobThree\Auth\TwoFactorAuth(
            new \RobThree\Auth\Providers\Qr\ImageChartsQRCodeProvider(),
            'Hotel Atankalama'
        );
        $ok = $tfa->verifyCode($secret, $code);
    } catch (\Throwable $e) {
        error_log('Portal::pVerifyTotp — ' . $e->getMessage());
        $_SESSION['_portal_err'] = 'Error al verificar. Intenta nuevamente.';
        header('Location: index.php?route=auth/totp');
        exit;
    }

    if (!$ok) {
        $_SESSION['_portal_err'] = 'Código incorrecto. Verifica la hora de tu teléfono.';
        header('Location: index.php?route=auth/totp');
        exit;
    }

    registrarLog($pdo, $email, 'portal');
    abrirSesiones($email);

    unset($_SESSION['_portal_otp_email'], $_SESSION['_portal_otp_redirect']);
    header('Location: index.php?route=' . urlencode($redirect ?: 'dashboard'));
    exit;
}

// ═══════════════════════════════════════════════════════════
// AUTH — Logout
// ═══════════════════════════════════════════════════════════

function pLogout(): void
{
    $email = $_SESSION[PORTAL_SK] ?? null;
    unset($_SESSION[PORTAL_SK], $_SESSION[PORTAL_EXP]);

    if ($email) {
        try {
            acceso_pdo()->prepare(
                "UPDATE chk_usuarios SET sesion_expira_en = NULL WHERE email = ?"
            )->execute([$email]);
        } catch (\Throwable) {}
    }

    header('Location: index.php?route=auth/login');
    exit;
}

// ═══════════════════════════════════════════════════════════
// DASHBOARD — Grilla de sistemas
// ═══════════════════════════════════════════════════════════

function pDashboard(string $email): void
{
    $pdo = acceso_pdo();

    $stmt = $pdo->prepare(
        "SELECT nombre, apellido FROM chk_usuarios WHERE email = ? LIMIT 1"
    );
    $stmt->execute([$email]);
    $usuario = $stmt->fetch() ?: ['nombre' => '', 'apellido' => ''];

    $stmt = $pdo->prepare("
        SELECT a.nombre, a.descripcion, a.url_inicio,
               COALESCE(a.icono, 'bi-grid-3x3-gap') AS icono,
               1 AS tiene_acceso
        FROM chk_apps a
        INNER JOIN chk_usuario_apps ua ON ua.app_id = a.id
        INNER JOIN chk_usuarios u      ON u.id = ua.usuario_id
        WHERE u.email = ? 
          AND u.estado = 'activo' 
          AND a.estado = 'activo'
        ORDER BY a.nombre ASC
    ");
    $stmt->execute([$email]);
    $apps = $stmt->fetchAll();

    $nombre = htmlspecialchars(trim($usuario['nombre'] . ' ' . $usuario['apellido']));
    $year   = date('Y');

    portHtmlIni('Portal · Hotel Atankalama');
    ?>
    <style>
      :root {
        --hotel-dark: #1e3a5f;
        --hotel-gold: #c49b63;
        --hotel-gold-dark: #a68252;
        --hotel-bg: #f4f6f9;
        --hotel-card-shadow: 0 4px 15px rgba(0,0,0,.05);
        --hotel-card-hover-shadow: 0 12px 30px rgba(30,58,95,.12);
      }
      body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--hotel-bg);
        color: #334155;
      }
      .navbar-hotel {
        background-color: var(--hotel-dark);
        border-bottom: 3px solid var(--hotel-gold);
      }
      .app-card {
        border-radius: 12px;
        transition: all .3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(0,0,0,.03) !important;
        box-shadow: var(--hotel-card-shadow);
        --app-color: var(--hotel-gold);
        --app-bg: #f8fafc;
      }
      .app-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--hotel-card-hover-shadow);
        border-color: var(--app-color) !important;
      }
      .btn-app {
        background-color: var(--app-color);
        color: white;
        border: none;
        font-weight: 600;
        transition: all .2s;
        font-size: .75rem;
      }
      .btn-app:hover {
        filter: brightness(0.9);
        color: white;
        transform: scale(1.02);
      }
      .icon-circle {
        width: 54px;
        height: 54px;
        background: var(--app-bg);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        transition: all .3s;
        color: var(--app-color);
      }
      .app-card:hover .icon-circle {
        transform: scale(1.1) rotate(5deg);
        border-radius: 12px;
      }
      .text-gold { color: var(--hotel-gold); }
    </style>

    <?php
    function getAppColorScheme(string $name): array {
        $nameLower = strtolower($name);
        $schemes = [
            'blue'    => ['p' => '#3b82f6', 'bg' => '#eff6ff'],
            'emerald' => ['p' => '#10b981', 'bg' => '#ecfdf5'],
            'violet'  => ['p' => '#8b5cf6', 'bg' => '#f5f3ff'],
            'amber'   => ['p' => '#f59e0b', 'bg' => '#fffbeb'],
            'rose'    => ['p' => '#f43f5e', 'bg' => '#fff1f2'],
            'cyan'    => ['p' => '#06b6d4', 'bg' => '#ecfeff'],
            'orange'  => ['p' => '#f97316', 'bg' => '#fff7ed'],
            'indigo'  => ['p' => '#6366f1', 'bg' => '#eef2ff'],
            'sky'     => ['p' => '#0ea5e9', 'bg' => '#f0f9ff'],
            'fuchsia' => ['p' => '#d946ef', 'bg' => '#fdf4ff'],
        ];

        if (str_contains($nameLower, 'temperatura')) return $schemes['orange'];
        if (str_contains($nameLower, 'incidencia')) return $schemes['rose'];
        if (str_contains($nameLower, 'trello') || str_contains($nameLower, 'proyectos')) return $schemes['cyan'];
        if (str_contains($nameLower, 'checklist')) return $schemes['emerald'];
        if (str_contains($nameLower, 'cocina'))    return $schemes['amber'];
        if (str_contains($nameLower, 'inventario')) return $schemes['blue'];
        if (str_contains($nameLower, 'novedades'))  return $schemes['violet'];
        if (str_contains($nameLower, 'reservas'))   return $schemes['indigo'];
        if (str_contains($nameLower, 'soporte') || str_contains($nameLower, 'sop')) return $schemes['orange'];
        if (str_contains($nameLower, 'wifi'))      return $schemes['sky'];
        if (str_contains($nameLower, 'validaci'))  return $schemes['fuchsia'];

        $keys = array_keys($schemes);
        $idx  = abs(crc32($name)) % count($keys);
        return $schemes[$keys[$idx]];
    }
    ?>

    <body class="bg-light">
    <?php if (!empty($_SESSION["admin_impersonator_email"])): ?>
      <div class="alert alert-dark border-0 rounded-0 mb-0 py-2 d-flex align-items-center justify-content-between px-3 px-md-4" style="background-color: #0f172a !important; color: white !important;">
        <div class="small">
          <i class="bi bi-eye-fill me-2"></i>
          Estás viendo el portal como <strong><?php echo htmlspecialchars($email); ?></strong>
        </div>
        <a href="/admin/index.php?route=acceso/usuarios/salir-ver-como" class="btn btn-sm btn-light py-0 fw-bold" style="font-size: .7rem">
          <i class="bi bi-box-arrow-left me-1"></i> Volver a Admin
        </a>
      </div>
    <?php endif; ?>

    <nav class="navbar navbar-dark navbar-hotel shadow-sm sticky-top py-2">
      <div class="container-fluid px-3 px-md-4">
        <div class="d-flex flex-column">
          <span class="navbar-brand fw-bold mb-0" style="font-size: 1.1rem; line-height: 1.2">
            <i class="bi bi-building me-2 text-gold"></i>Hotel Atankalama
          </span>
          <span class="text-white-50 d-none d-md-block" style="font-size: .75rem">
            Bienvenido, <?= explode(' ', $nombre)[0] ?> &mdash; Selecciona un sistema para comenzar
          </span>
        </div>
        
        <div class="d-flex align-items-center gap-2 gap-md-3">
          <div class="text-end d-none d-sm-block lh-sm me-2">
            <div class="text-white small fw-semibold" style="font-size: .8rem"><?= $nombre ?></div>
            <div class="text-white-50" style="font-size: .65rem"><?= htmlspecialchars($email) ?></div>
          </div>
          <a href="index.php?route=perfil/editar"
             class="btn btn-outline-light btn-sm border-0 p-1" title="Mi perfil">
            <i class="bi bi-person-circle" style="font-size: 1.1rem"></i>
          </a>
          <a href="index.php?route=auth/logout"
             class="btn btn-outline-danger btn-sm border-0 p-1" title="Cerrar sesión"
             onclick="return confirm('¿Cerrar sesión?')">
            <i class="bi bi-box-arrow-right" style="font-size: 1.1rem"></i>
          </a>
        </div>
      </div>
    </nav>

    <div class="container py-3 px-3 px-md-4">

      <?php if (empty($apps)): ?>
        <div class="alert alert-info border-0 shadow-sm">No hay sistemas registrados.</div>
      <?php else: ?>
        <div class="row g-3">
          <?php foreach ($apps as $app):
            $acceso  = (bool)$app['tiene_acceso'];
            $icono   = htmlspecialchars($app['icono']);
            $nombre  = htmlspecialchars($app["nombre"]); if ($nombre === "Sistema Trello") $nombre = "Tablero de Proyectos";
            $desc    = htmlspecialchars($app["descripcion"] ?? ""); if ($nombre === "Tablero de Proyectos") $desc = "Gestión de tareas por tableros tipo Kanban";
            $url     = $app['url_inicio'] ?? '';
            $scheme  = getAppColorScheme($nombre);
          ?>
            <div class="col-6 col-md-4 col-lg-3">
              <div class="card h-100 border-0 app-card<?= $acceso ? '' : ' opacity-50' ?>"
                   style="--app-color: <?= $scheme['p'] ?>; --app-bg: <?= $scheme['bg'] ?>;">
                <div class="card-body text-center p-3 d-flex flex-column">
                  <div class="icon-circle">
                    <i class="bi <?= $icono ?>" style="font-size:1.6rem"></i>
                  </div>
                  <h6 class="fw-bold mb-1 text-dark" style="font-size:.85rem"><?= $nombre ?></h6>
                  <?php if ($desc): ?>
                    <p class="text-muted mb-3 flex-grow-1" style="font-size:.75rem; line-height: 1.3"><?= $desc ?></p>
                  <?php else: ?>
                    <div class="flex-grow-1"></div>
                  <?php endif ?>

                  <?php if ($acceso && $url): ?>
                    <a href="<?= htmlspecialchars($url) ?>" class="btn btn-app py-1">
                      Ingresar <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                  <?php elseif ($acceso): ?>
                    <span class="btn btn-outline-secondary btn-sm disabled py-1" style="font-size:.75rem">
                      Sin URL
                    </span>
                  <?php else: ?>
                    <span class="btn btn-light btn-sm disabled py-1" style="font-size:.75rem">
                      <i class="bi bi-lock me-1"></i> Sin acceso
                    </span>
                  <?php endif ?>
                </div>
              </div>
            </div>
          <?php endforeach ?>
        </div>
      <?php endif ?>

    </div>

    <footer class="text-center text-muted py-5 mt-4 border-top bg-white" style="font-size:.75rem">
      <div class="container">
        &copy; <?= $year ?> Rodrigo Jaque Escobar &mdash; Todos los derechos reservados.<br>
        <span class="opacity-75">Se concede uso operacional de esta aplicación. El código fuente permanece como propiedad exclusiva del autor.</span><br>
        <div class="mt-2">
          <a href="index.php?route=terms" class="text-muted text-decoration-none opacity-75 mx-2">Términos y Condiciones</a>
          <span class="opacity-25">|</span>
          <a href="index.php?route=privacy" class="text-muted text-decoration-none opacity-75 mx-2">Política de Privacidad</a>
          <span class="opacity-25">|</span>
          <a href="index.php?route=version" class="text-muted text-decoration-none opacity-75 mx-2">
            <i class="bi bi-code-branch me-1"></i>v1.2
          </a>
        </div>
      </div>
    </footer>

    </body></html>
    <?php
}

// ═══════════════════════════════════════════════════════════
// PERFIL — Ver y editar
// ═══════════════════════════════════════════════════════════

function pPerfil(string $email): void
{
    $pdo  = acceso_pdo();
    $stmt = $pdo->prepare(
        "SELECT nombre, apellido, telefono FROM chk_usuarios WHERE email = ? LIMIT 1"
    );
    $stmt->execute([$email]);
    $u    = $stmt->fetch() ?: ['nombre' => '', 'apellido' => '', 'telefono' => ''];

    $ok   = $_SESSION['_portal_ok']  ?? null;
    $err  = $_SESSION['_portal_err'] ?? null;
    $year = date('Y');
    unset($_SESSION['_portal_ok'], $_SESSION['_portal_err']);

    portHtmlIni('Mi perfil · Hotel Atankalama');
    ?>
    <style>
      :root {
        --hotel-dark: #1e3a5f;
        --hotel-gold: #c49b63;
        --hotel-gold-dark: #a68252;
        --hotel-bg: #f4f6f9;
      }
      body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--hotel-bg);
        color: #334155;
      }
      .navbar-hotel {
        background-color: var(--hotel-dark);
        border-bottom: 3px solid var(--hotel-gold);
      }
      .card-profile {
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,.05);
        border: 1px solid rgba(0,0,0,.02);
      }
      .btn-hotel-gold {
        background-color: var(--hotel-gold);
        color: white;
        border: none;
        font-weight: 500;
        transition: background-color .2s;
      }
      .btn-hotel-gold:hover {
        background-color: var(--hotel-gold-dark);
        color: white;
      }
      .form-control:focus {
        border-color: var(--hotel-gold);
        box-shadow: 0 0 0 0.25rem rgba(196,155,99,.15);
      }
      .text-gold { color: var(--hotel-gold); }
    </style>
    <body class="bg-light">
    <?php if (!empty($_SESSION["admin_impersonator_email"])): ?>
      <div class="alert alert-dark border-0 rounded-0 mb-0 py-2 d-flex align-items-center justify-content-between px-3 px-md-4" style="background-color: #0f172a !important; color: white !important;">
        <div class="small">
          <i class="bi bi-eye-fill me-2"></i>
          Estás viendo el portal como <strong><?php echo htmlspecialchars($email); ?></strong>
        </div>
        <a href="/admin/index.php?route=acceso/usuarios/salir-ver-como" class="btn btn-sm btn-light py-0 fw-bold" style="font-size: .7rem">
          <i class="bi bi-box-arrow-left me-1"></i> Volver a Admin
        </a>
      </div>
    <?php endif; ?>
    <nav class="navbar navbar-dark navbar-hotel shadow-sm">
      <div class="container-fluid px-3 px-md-4">
        <a class="navbar-brand fw-bold" href="index.php?route=dashboard">
          <i class="bi bi-arrow-left me-2 text-gold"></i>Volver al portal
        </a>
      </div>
    </nav>

    <div class="container py-5 px-3" style="max-width:540px">
      <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-circle shadow-sm mb-3" style="width: 80px; height: 80px">
          <i class="bi bi-person-circle text-gold" style="font-size:2.5rem"></i>
        </div>
        <h4 class="fw-bold mb-1">Mi perfil</h4>
        <p class="text-muted small"><?= htmlspecialchars($email) ?></p>
      </div>

      <?php if ($ok): ?>
        <div class="alert alert-success border-0 shadow-sm py-2 small mb-4">
          <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($ok) ?>
        </div>
      <?php endif ?>
      <?php if ($err): ?>
        <div class="alert alert-danger border-0 shadow-sm py-2 small mb-4">
          <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($err) ?>
        </div>
      <?php endif ?>

      <div class="card card-profile border-0">
        <div class="card-body p-4 p-md-5">
          <form method="POST" action="index.php?route=perfil/actualizar">
            <div class="mb-3">
              <label class="form-label small fw-medium text-muted">Nombre</label>
              <input type="text" name="nombre" class="form-control border-light-subtle bg-light"
                     value="<?= htmlspecialchars($u['nombre']) ?>"
                     required maxlength="100">
            </div>
            <div class="mb-3">
              <label class="form-label small fw-medium text-muted">Apellido</label>
              <input type="text" name="apellido" class="form-control border-light-subtle bg-light"
                     value="<?= htmlspecialchars($u['apellido']) ?>"
                     required maxlength="100">
            </div>
            <div class="mb-3">
              <label class="form-label small fw-medium text-muted">Teléfono</label>
              <input type="tel" name="telefono" class="form-control border-light-subtle bg-light"
                     value="<?= htmlspecialchars($u['telefono'] ?? '') ?>"
                     maxlength="20" placeholder="+56 9 xxxxxxxx">
            </div>
            <div class="mb-4">
              <label class="form-label small fw-medium text-muted opacity-50">Correo electrónico</label>
              <input type="text" class="form-control bg-light text-muted border-light-subtle"
                     value="<?= htmlspecialchars($email) ?>" disabled>
              <div class="form-text small opacity-50">El correo no se puede modificar.</div>
            </div>
            <button type="submit" class="btn btn-hotel-gold w-100 btn-lg py-2 shadow-sm">
              <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
              <i class="bi bi-floppy me-1"></i> Guardar Cambios
            </button>
          </form>
        </div>
      </div>

      <p class="text-center text-muted small mt-5 opacity-50">&copy; <?= $year ?> Hotel Atankalama</p>
    </div>

    </body></html>
    <?php
}

function pActualizarPerfil(string $email): void
{
    $nombre   = trim($_POST['nombre']   ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    if (!$nombre || !$apellido) {
        $_SESSION['_portal_err'] = 'Nombre y apellido son obligatorios.';
        header('Location: index.php?route=perfil/editar');
        exit;
    }

    try {
        acceso_pdo()->prepare(
            "UPDATE chk_usuarios SET nombre = ?, apellido = ?, telefono = ? WHERE email = ?"
        )->execute([$nombre, $apellido, $telefono ?: null, $email]);

        $_SESSION['_portal_ok'] = 'Perfil actualizado correctamente.';
    } catch (\Throwable $e) {
        error_log('Portal::pActualizarPerfil — ' . $e->getMessage());
        $_SESSION['_portal_err'] = 'Error al guardar. Intenta nuevamente.';
    }

    header('Location: index.php?route=perfil/editar');
    exit;
}

// ═══════════════════════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════════════════════

/**
 * Abre sesión en el portal Y en todas las apps accesibles del usuario.
 * Funciona porque todas las apps comparten el mismo dominio → misma sesión PHP.
 */
function abrirSesiones(string $email): void
{
    // Seguridad: Regenerar ID de sesión tras login exitoso
    session_regenerate_id(true);

    $pdo    = acceso_pdo();
    $expira = time() + PORTAL_HORAS * 3600;

    // Sesión del portal
    $_SESSION[PORTAL_SK]  = $email;
    $_SESSION[PORTAL_EXP] = $expira;

    // Sesión de cada app con session_prefix configurado
    $stmt = $pdo->prepare("
        SELECT a.session_prefix
        FROM chk_apps a
        JOIN chk_usuario_apps ua ON ua.app_id   = a.id
        JOIN chk_usuarios u      ON u.id        = ua.usuario_id
        WHERE u.email             = ?
          AND u.estado            = 'activo'
          AND a.estado            = 'activo'
          AND a.session_prefix IS NOT NULL
          AND a.session_prefix    <> ''
    ");
    $stmt->execute([$email]);

    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $prefix) {
        $_SESSION["{$prefix}_admin_email"]   = $email;
        $_SESSION["{$prefix}_admin_expires"] = $expira;
    }

    // Actualizar BD
    $pdo->prepare("
        UPDATE chk_usuarios
        SET last_login = NOW(), forzar_logout = 0, sesion_expira_en = ?
        WHERE email = ?
    ")->execute([date('Y-m-d H:i:s', $expira), $email]);
}

function totpActivo(PDO $pdo, string $email): bool
{
    try {
        $stmt = $pdo->prepare(
            "SELECT totp_habilitado FROM chk_usuarios WHERE email = ? LIMIT 1"
        );
        $stmt->execute([$email]);
        return (bool)$stmt->fetchColumn();
    } catch (\Throwable) {
        return false;
    }
}

function registrarLog(PDO $pdo, string $email, string $appSlug): void
{
    try {
        $pdo->prepare("
            INSERT INTO chk_login_log (email, app_slug, ip_address, user_agent)
            VALUES (?, ?, ?, ?)
        ")->execute([
            $email, $appSlug,
            $_SERVER['REMOTE_ADDR']     ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        ]);
    } catch (\Throwable) {}
}

function enviarOtp(string $email, string $code): bool
{
    cargarVendor();

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
        $mail->Subject = 'Código de acceso · Hotel Atankalama';
        $mail->Body    = plantillaOtp($code);
        $mail->AltBody = "Tu código de acceso al portal es: {$code}\nVálido por 10 minutos.";
        $mail->send();
        return true;
    } catch (\Throwable $e) {
        error_log('Portal::enviarOtp — ' . $e->getMessage());
        return false;
    }
}

function plantillaOtp(string $code): string
{
    return <<<HTML
    <!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"></head>
    <body style="font-family:Arial,sans-serif;background:#f4f6f9;margin:0;padding:40px 20px">
      <div style="max-width:420px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,.08);border:1px solid #eee">
        <div style="background:#1e3a5f;padding:32px 24px;text-align:center;border-bottom:4px solid #c49b63">
          <h2 style="color:#fff;margin:0;font-size:22px;letter-spacing:1px">Hotel Atankalama</h2>
          <p style="color:#a8c4e0;margin:6px 0 0;font-size:13px;text-transform:uppercase;letter-spacing:2px opacity:0.8">Portal de Acceso</p>
        </div>
        <div style="padding:40px 24px;text-align:center">
          <p style="color:#64748b;font-size:15px;margin:0 0 24px">Usa el siguiente código para ingresar:</p>
          <div style="display:inline-block;background:#f8fafc;border:2px solid #e2e8f0;border-radius:12px;padding:20px 32px">
            <span style="font-size:38px;font-weight:bold;letter-spacing:8px;color:#1e3a5f">{$code}</span>
          </div>
          <p style="color:#94a3b8;font-size:13px;margin:24px 0 0">Válido por <strong>10 minutos</strong>. No compartas este código con nadie.</p>
        </div>
        <div style="background:#f8fafc;padding:16px;text-align:center;border-top:1px solid #f1f5f9">
          <p style="color:#cbd5e1;font-size:11px;margin:0">&copy; Hotel Atankalama &mdash; Todos los derechos reservados.</p>
        </div>
      </div>
    </body></html>
    HTML;
}

function cargarVendor(): void
{
    static $cargado = false;
    if ($cargado) return;

    $rutas = [
        $_SERVER['DOCUMENT_ROOT'] . '/novedades/vendor/autoload.php',
        dirname(__FILE__, 2)      . '/novedades/vendor/autoload.php',
    ];
    foreach ($rutas as $ruta) {
        if (file_exists($ruta)) {
            require_once $ruta;
            $cargado = true;
            return;
        }
    }
}

// ═══════════════════════════════════════════════════════════
// TÉRMINOS Y PRIVACIDAD
// ═══════════════════════════════════════════════════════════

function pTerms(): void
{
    $year = date('Y');
    portHtmlIni('Términos y Condiciones · Hotel Atankalama');
    ?>
    <style>
      :root { --hotel-dark:#1e3a5f; --hotel-gold:#c49b63; --hotel-bg:#f4f6f9; }
      body { font-family:'Poppins',sans-serif; background-color:var(--hotel-bg); color:#334155; }
      .navbar-hotel { background-color:var(--hotel-dark); border-bottom:3px solid var(--hotel-gold); }
      .text-gold { color:var(--hotel-gold); }
    </style>
    <body class="bg-light">
    <nav class="navbar navbar-dark navbar-hotel shadow-sm">
      <div class="container-fluid px-3 px-md-4">
        <a class="navbar-brand fw-bold" href="javascript:history.back()">
          <i class="bi bi-arrow-left me-2 text-gold"></i>Volver
        </a>
      </div>
    </nav>

    <div class="container py-5 px-3" style="max-width:800px">
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4 p-md-5">
          <h1 class="mb-4 text-center fw-bold">Términos y Condiciones de Uso</h1>
          <p class="text-muted text-center mb-5">Última actualización: 30 de mayo de 2026</p>

          <p>Bienvenido a <strong>Atankalama</strong>. Al acceder y utilizar nuestras aplicaciones y servicios alojados en el dominio <strong>www.atankalama.com</strong>, usted acepta cumplir y estar sujeto a los siguientes términos y condiciones de uso.</p>

          <hr class="my-5">

          <h3 class="fw-bold h5">1. Aceptación de los Términos</h3>
          <p>El uso de este sitio web y sus aplicaciones asociadas constituye la aceptación plena y sin reservas de estos Términos y Condiciones. Si no está de acuerdo con alguna parte de estos términos, no debe utilizar nuestros servicios.</p>

          <h3 class="mt-4 fw-bold h5">2. Propiedad Intelectual</h3>
          <p>Todo el contenido, código fuente, logotipos y materiales educativos son propiedad exclusiva de <strong>Rodrigo Jaque Escobar</strong>. Se concede una licencia limitada para uso operacional, sin transferencia de propiedad.</p>

          <div class="alert alert-warning mt-4 border-0 shadow-sm">
            <h3 class="alert-heading fw-bold h5"><i class="bi bi-exclamation-triangle me-2"></i>3. Limitación de Responsabilidad</h3>
            <p class="mb-0 small"><strong>3.1 Pérdida de Datos:</strong> Atankalama <strong>NO SE HACE RESPONSABLE</strong> por la pérdida, corrupción o eliminación de datos, información o contenidos introducidos por los usuarios en la plataforma. No garantizamos la integridad absoluta de la información ante fallos técnicos o errores de terceros. Es responsabilidad del usuario mantener respaldos externos.</p>
          </div>

          <h3 class="mt-4 fw-bold h5">4. Disponibilidad del Servicio</h3>
          <p>El servicio se proporciona "tal cual". No garantizamos que el servicio sea ininterrumpido o libre de errores.</p>

          <h3 class="mt-4 fw-bold h5">5. Sincronización Offline y PWA</h3>
          <p>La persistencia de datos en modo offline depende del navegador del usuario. No somos responsables si los datos almacenados localmente se pierden antes de sincronizarse con el servidor.</p>

          <h3 class="mt-4 fw-bold h5">6. Responsabilidades del Usuario</h3>
          <p>El usuario es responsable de la veracidad de su información y de la confidencialidad de sus credenciales de acceso.</p>

          <h3 class="mt-4 fw-bold h5">7. Modificaciones</h3>
          <p>Nos reservamos el derecho de modificar estos términos en cualquier momento.</p>

          <h3 class="mt-4 fw-bold h5">8. Ley Aplicable</h3>
          <p>Estos términos se rigen por las leyes de la República de Chile.</p>

          <div class="mt-5 text-center">
            <a href="javascript:history.back()" class="btn btn-primary px-4">Entendido</a>
          </div>
        </div>
      </div>
      <p class="text-center text-muted small mt-4 opacity-50">&copy; <?= $year ?> Rodrigo Jaque Escobar</p>
    </div>
    </body></html>
    <?php
}

function pPrivacy(): void
{
    $year = date('Y');
    portHtmlIni('Política de Privacidad · Hotel Atankalama');
    ?>
    <style>
      :root { --hotel-dark:#1e3a5f; --hotel-gold:#c49b63; --hotel-bg:#f4f6f9; }
      body { font-family:'Poppins',sans-serif; background-color:var(--hotel-bg); color:#334155; }
      .navbar-hotel { background-color:var(--hotel-dark); border-bottom:3px solid var(--hotel-gold); }
      .text-gold { color:var(--hotel-gold); }
    </style>
    <body class="bg-light">
    <nav class="navbar navbar-dark navbar-hotel shadow-sm">
      <div class="container-fluid px-3 px-md-4">
        <a class="navbar-brand fw-bold" href="javascript:history.back()">
          <i class="bi bi-arrow-left me-2 text-gold"></i>Volver
        </a>
      </div>
    </nav>

    <div class="container py-5 px-3" style="max-width:800px">
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4 p-md-5">
          <h1 class="mb-4 text-center fw-bold">Política de Privacidad</h1>
          <p class="text-muted text-center mb-5">Última actualización: 30 de mayo de 2026</p>

          <p>En <strong>Atankalama</strong> (en adelante, "nosotros" o "el Sitio"), nos tomamos muy en serio la privacidad de nuestros usuarios. Esta Política de Privacidad describe cómo recopilamos, utilizamos, almacenamos y protegemos la información personal en todas nuestras aplicaciones y servicios alojados en el dominio <strong>www.atankalama.com</strong>.</p>

          <hr class="my-5">

          <h3 class="fw-bold h5">1. Responsable del Tratamiento de Datos</h3>
          <p>El responsable del tratamiento de sus datos personales es <strong>Rodrigo Jaque Escobar</strong>, con domicilio en Chile y correo de contacto <strong>rjaquers@gmail.com</strong>.</p>

          <h3 class="mt-4 fw-bold h5">2. Información que Recopilamos</h3>
          <p>Dependiendo de la aplicación específica que utilice, podemos recopilar los siguientes tipos de datos:</p>
          <ul class="small">
            <li><strong>Datos de Identidad y Contacto:</strong> Nombre completo, dirección de correo electrónico, teléfono y credenciales de acceso.</li>
            <li><strong>Datos Operativos:</strong> Registros de actividad, inventarios, novedades, capacitaciones y cualquier información ingresada en las diversas herramientas de gestión.</li>
            <li><strong>Información Técnica y de Uso:</strong> Dirección IP, tipo de dispositivo, navegador, registros de auditoría y datos de navegación.</li>
            <li><strong>Datos de Sincronización Offline:</strong> Información almacenada temporalmente en el dispositivo del usuario para permitir el funcionamiento sin conexión.</li>
          </ul>

          <h3 class="mt-4 fw-bold h5">3. Finalidad del Tratamiento</h3>
          <ul class="small">
            <li>Proveer y gestionar el acceso a los sistemas internos del hotel.</li>
            <li>Personalizar la experiencia del usuario y realizar seguimiento de actividades operativas.</li>
            <li>Enviar notificaciones importantes relacionadas con el servicio.</li>
            <li>Garantizar la seguridad del sistema y prevenir actividades fraudulentas.</li>
          </ul>

          <h3 class="mt-4 fw-bold h5">4. Base Legal para el Tratamiento</h3>
          <p>El tratamiento de sus datos se realiza bajo el <strong>Consentimiento</strong> al utilizar nuestras aplicaciones y para la <strong>Ejecución de un Contrato</strong> (relación laboral o de servicios con el hotel).</p>

          <h3 class="mt-4 fw-bold h5">5. Almacenamiento y Protección de Datos</h3>
          <p>Sus datos se almacenan de forma segura. Implementamos medidas técnicas para proteger la información, incluyendo protocolos HTTPS y controles de acceso basados en roles.</p>

          <h3 class="mt-4 fw-bold h5">6. Derechos ARCO</h3>
          <p>De acuerdo con la legislación vigente (Ley N° 19.628 en Chile), usted tiene derecho a Acceso, Rectificación, Cancelación y Oposición. Contacto: <strong>rjaquers@gmail.com</strong>.</p>

          <h3 class="mt-4 fw-bold h5">7. Cambios en la Política</h3>
          <p>Nos reservamos el derecho de modificar esta política en cualquier momento.</p>

          <div class="mt-5 text-center">
            <a href="javascript:history.back()" class="btn btn-primary px-4">Entendido</a>
          </div>
        </div>
      </div>
      <p class="text-center text-muted small mt-4 opacity-50">&copy; <?= $year ?> Rodrigo Jaque Escobar</p>
    </div>
    </body></html>
    <?php
}

// ═══════════════════════════════════════════════════════════
// VERSIONES
// ═══════════════════════════════════════════════════════════

function pVersion(): void
{
    $year = date('Y');
    portHtmlIni('Versiones · Portal Atankalama');
    ?>
    <style>
      :root { --hotel-dark:#1e3a5f; --hotel-gold:#c49b63; --hotel-bg:#f4f6f9; }
      body { font-family:'Poppins',sans-serif; background-color:var(--hotel-bg); color:#334155; }
      .navbar-hotel { background-color:var(--hotel-dark); border-bottom:3px solid var(--hotel-gold); }
      .text-gold { color:var(--hotel-gold); }
    </style>
    <body class="bg-light">
    <nav class="navbar navbar-dark navbar-hotel shadow-sm">
      <div class="container-fluid px-3 px-md-4">
        <a class="navbar-brand fw-bold" href="index.php?route=dashboard">
          <i class="bi bi-arrow-left me-2 text-gold"></i>Volver al portal
        </a>
      </div>
    </nav>

    <div class="container py-5 px-3" style="max-width:760px">
      <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4 p-md-5">
          <h4 class="fw-bold mb-1">
            <i class="bi bi-code-branch me-2 text-gold"></i>Historial de versiones
          </h4>
          <p class="text-muted small mb-4">Portal de Acceso · Hotel Atankalama</p>
          <p class="text-muted small">Desarrollado por Rodrigo Jaque Escobar</p>
          <hr>

          <!-- 1.2 -->
          <div class="mb-4">
            <h5 class="fw-bold mb-1">Versión 1.2 <small class="text-muted fw-normal fs-6">— 30/05/2026</small></h5>
            <p class="text-muted small mb-2">Legal y Cumplimiento</p>
            <ul class="small text-secondary">
              <li><strong>Legal — Términos y Condiciones:</strong> Implementación de página global de términos y condiciones que abarca todos los sistemas de la plataforma Atankalama.</li>
              <li><strong>Legal — Política de Privacidad:</strong> Implementación de política de privacidad centralizada para el tratamiento de datos personales en el dominio atankalama.com.</li>
              <li><strong>UX — Accesibilidad legal:</strong> Integración de enlaces directos a los documentos legales en los pies de página del dashboard y la pantalla de inicio de sesión.</li>
            </ul>
          </div>

          <!-- 1.1 -->
          <div class="mb-4">
            <h5 class="fw-bold mb-1">Versión 1.1 <small class="text-muted fw-normal fs-6">— 30/05/2026</small></h5>
            <p class="text-muted small mb-2">Mejoras de UX, Rendimiento y Seguridad</p>
            <ul class="small text-secondary">
              <li><strong>UX — Feedback de carga:</strong> Implementación de spinners animados en todos los botones de acción para indicar procesos en curso y evitar múltiples envíos.</li>
              <li><strong>Rendimiento — Optimización de Assets:</strong> Migración de imágenes a formato WebP de alta eficiencia y configuración de pre-carga (preload) para reducir el tiempo de carga inicial.</li>
              <li><strong>Rendimiento — Fluidez visual:</strong> Optimización de la animación de fondo mediante aceleración por hardware (GPU) con la propiedad <code>will-change</code>.</li>
              <li><strong>Seguridad — Robustez de Sesiones:</strong> Implementación de regeneración de ID de sesión tras autenticación exitosa para prevenir ataques de fijación de sesión.</li>
              <li><strong>Seguridad — Cookies Seguras:</strong> Configuración estricta de cookies (HttpOnly, Secure, SameSite=Lax) para proteger contra ataques XSS y CSRF.</li>
              <li><strong>Seguridad — Anti-Fuerza Bruta:</strong> Implementación de limitación de tasa (throttling) en la solicitud de códigos OTP (máximo 3 intentos cada 5 minutos por IP/Email).</li>
            </ul>
          </div>

          <!-- 1.0 -->
          <div class="mb-4">
            <h5 class="fw-bold mb-1">Versión 1.0 <small class="text-muted fw-normal fs-6">— 28/05/2026</small></h5>
            <p class="text-muted small mb-2">Perfil: Todos los usuarios</p>
            <ul class="small text-secondary">
              <li><strong>Seguridad — Autenticación OTP:</strong> Login mediante código de 6 dígitos enviado por correo, con expiración de 10 minutos y bloqueo tras 5 intentos fallidos.</li>
              <li><strong>Seguridad — Autenticación TOTP:</strong> Soporte para Google Authenticator como segundo factor; el sistema detecta automáticamente si el usuario lo tiene activo.</li>
              <li><strong>Seguridad — Fallback a correo:</strong> Desde la pantalla del autenticador, el usuario puede solicitar recibir el código por correo si no tiene el teléfono disponible.</li>
              <li><strong>Funcionalidad — Dashboard centralizado:</strong> Grilla de accesos a los sistemas del hotel, filtrada según los permisos asignados a cada usuario.</li>
              <li><strong>Funcionalidad — SSO simplificado:</strong> Al hacer login, se abren automáticamente las sesiones en todas las apps accesibles del usuario (mismo dominio).</li>
              <li><strong>Funcionalidad — Edición de perfil:</strong> El usuario puede actualizar su nombre, apellido y teléfono desde el portal.</li>
              <li><strong>UX / Interfaz — Tarjetas con color dinámico:</strong> Cada sistema muestra un esquema de color único y animación de hover; fondo con efecto Ken Burns.</li>
            </ul>
          </div>

        </div>
      </div>
      <p class="text-center text-muted small mt-4 opacity-50">&copy; <?= $year ?> Rodrigo Jaque Escobar</p>
    </div>
    </body></html>
    <?php
}

function portHtmlIni(string $titulo): void
{
    echo '<!DOCTYPE html><html lang="es"><head>'
        . '<meta charset="UTF-8">'
        . '<meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>' . htmlspecialchars($titulo) . '</title>'
        . '<link rel="preconnect" href="https://fonts.googleapis.com">'
        . '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
        . '<link rel="preload" as="image" href="https://www.atankalama.com/public/uploads/piscinaAtankalama.webp">'
        . '<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">'
        . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">'
        . '<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">'
        . '<script>
            document.addEventListener("DOMContentLoaded", function() {
              document.addEventListener("submit", function(e) {
                const btn = e.target.querySelector(\'button[type="submit"]\') || e.target.querySelector(\'button:not([type="button"])\');
                if (btn && !btn.classList.contains("no-loader")) {
                  const spinner = btn.querySelector(".spinner-border");
                  if (spinner) {
                    btn.disabled = true;
                    spinner.classList.remove("d-none");
                    const icon = btn.querySelector(".bi");
                    if (icon) icon.classList.add("d-none");
                  }
                }
              });
            });
          </script>'
        . '</head>';
}
