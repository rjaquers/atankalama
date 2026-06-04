<?php
/**
 * AccesoBootstrap — Clave de integración del sistema de acceso centralizado
 *
 * Activa en cualquier app el sistema completo: sesión OTP + permisos granulares.
 * No requiere AuthController propio en la app destino.
 *
 * ── Integración mínima (2 líneas en el index.php de la app) ──────────────
 *
 *   require_once $_SERVER['DOCUMENT_ROOT'] . '/shared/AccesoBootstrap.php';
 *   AccesoBootstrap::arrancar('mi_app', 'mia', $rutaActual, $rutasPublicas);
 *
 * ── Parámetros de arrancar() ─────────────────────────────────────────────
 *   $appSlug      → slug de la app en chk_apps  (ej: 'cocina')
 *   $prefix       → prefijo de sesión único      (ej: 'coc')
 *   $ruta         → ruta actual del router       (ej: $uri o $route)
 *   $rutasPublicas → array de rutas sin login    (ej: ['recepcion/index'])
 *   $appNombre    → nombre legible para el login (opcional, se consulta en BD si no se pasa)
 *
 * ── Rutas que gestiona internamente (no agregarlas al router de la app) ──
 *   auth/login        → formulario de email
 *   auth/send-otp     → procesa envío de OTP
 *   auth/verify       → formulario de código
 *   auth/verify-code  → procesa verificación
 *   auth/logout       → cierra sesión
 *
 * ── Después de arrancar(), obtener el email autenticado ──────────────────
 *   $email = AccesoBootstrap::email();  // null si no hay sesión
 *
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 */

require_once __DIR__ . '/acceso_db.php';
require_once __DIR__ . '/AccesoService.php';
require_once __DIR__ . '/AccesoLog.php';

class AccesoBootstrap
{
    private static array   $cfg    = [];
    private static ?string $pParam = null;   // parámetro GET del router ('route' o 'page')

    // ────────────────────────────────────────────────────────────────────────
    // PUNTO DE ENTRADA
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Activar el sistema en la app. Llamar antes del dispatch.
     *
     * - Si la ruta es de auth  → la maneja y hace exit.
     * - Si la ruta es pública  → retorna sin verificar.
     * - Si la ruta es privada  → verifica permisos; redirige o 403 si falla.
     *
     * @param string   $appSlug       Slug de la app (chk_apps.slug)
     * @param string   $prefix        Prefijo de sesión único por app
     * @param string   $ruta          Ruta actual ($_GET['route'] o $_GET['page'])
     * @param string[] $rutasPublicas Rutas accesibles sin login
     * @param string   $appNombre     Nombre para la pantalla de login (opcional)
     */
    public static function arrancar(
        string $appSlug,
        string $prefix,
        string $ruta,
        array  $rutasPublicas = [],
        string $appNombre     = ''
    ): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Detectar parámetro del router
        self::$pParam = isset($_GET['page']) ? 'page' : 'route';

        self::$cfg = [
            'app'      => $appSlug,
            'prefix'   => $prefix,
            'ruta'     => $ruta,
            'publicas' => $rutasPublicas,
            'nombre'   => $appNombre ?: self::nombreApp($appSlug),
            'emailKey' => "{$prefix}_admin_email",
            'expKey'   => "{$prefix}_admin_expires",
            'loginUrl' => 'index.php?' . self::$pParam . '=auth/login',
        ];

        // Limpiar sesión expirada
        $email   = $_SESSION[self::$cfg['emailKey']] ?? null;
        $expires = $_SESSION[self::$cfg['expKey']]   ?? 0;
        if ($email && time() > $expires) {
            unset($_SESSION[self::$cfg['emailKey']], $_SESSION[self::$cfg['expKey']]);
            $email = null;
        }

        // Verificar forzar_logout y sincronizar sesion_expira_en con la sesión PHP activa
        if ($email) {
            try {
                $pdo  = acceso_pdo();
                $stmt = $pdo->prepare(
                    "SELECT forzar_logout, sesion_expira_en FROM chk_usuarios WHERE email = ? LIMIT 1"
                );
                $stmt->execute([$email]);
                $fila = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($fila && (int)$fila['forzar_logout'] === 1) {
                    // Administrador forzó el cierre
                    unset($_SESSION[self::$cfg['emailKey']], $_SESSION[self::$cfg['expKey']]);
                    $pdo->prepare(
                        "UPDATE chk_usuarios SET sesion_expira_en = NULL WHERE email = ?"
                    )->execute([$email]);
                    $email = null;
                } elseif ($fila && $expires > time()) {
                    // Sesión PHP activa → sincronizar expiry en BD si está desactualizado o nulo
                    $expiraDb = $fila['sesion_expira_en']
                        ? strtotime($fila['sesion_expira_en'])
                        : 0;
                    if (!$expiraDb || abs($expiraDb - $expires) > 60) {
                        $pdo->prepare(
                            "UPDATE chk_usuarios SET sesion_expira_en = ? WHERE email = ?"
                        )->execute([date('Y-m-d H:i:s', $expires), $email]);
                    }
                }
            } catch (\Throwable) {
                // Si las columnas aún no existen, ignorar
            }
        }

        // Rutas de auth → manejar internamente y salir
        $rutasAuth = [
            'auth/login', 'auth/send-otp', 'auth/sendOtp',
            'auth/verify', 'auth/verify-code', 'auth/verifyCode',
            'auth/totp', 'auth/verify-totp', 'auth/enviar-otp-totp',
            'auth/logout',
        ];
        if (in_array($ruta, $rutasAuth)) {
            self::despacharAuth($ruta, $email);
            exit;
        }

        // Ruta pública → continuar sin verificar
        if (in_array($ruta, $rutasPublicas)) {
            return;
        }

        // Ruta protegida → verificar sesión + permisos
        AccesoService::requerir(
            $appSlug,
            $email,
            $ruta,
            self::$cfg['loginUrl'] . '&redirect=' . urlencode($ruta)
        );
    }

    /** Email del usuario autenticado actualmente. Null si no hay sesión. */
    public static function email(): ?string
    {
        $key = self::$cfg['emailKey'] ?? null;
        return $key ? ($_SESSION[$key] ?? null) : null;
    }

    /** Guardar sesión desde el AuthController de la app (si usa uno propio). */
    public static function abrirSesion(string $prefix, string $email, int $horas = 4): void
    {
        $_SESSION["{$prefix}_admin_email"]   = $email;
        $_SESSION["{$prefix}_admin_expires"] = time() + ($horas * 3600);
        acceso_pdo()->prepare("UPDATE chk_usuarios SET last_login = NOW() WHERE email = ?")
            ->execute([$email]);
    }

    /** Cerrar sesión y redirigir. */
    public static function cerrarSesion(string $prefix, string $redirigir = '/'): void
    {
        unset($_SESSION["{$prefix}_admin_email"], $_SESSION["{$prefix}_admin_expires"]);
        header('Location: ' . $redirigir);
        exit;
    }

    // ────────────────────────────────────────────────────────────────────────
    // DESPACHO INTERNO DE RUTAS AUTH
    // ────────────────────────────────────────────────────────────────────────

    private static function despacharAuth(string $ruta, ?string $emailActual): void
    {
        // Normalizar aliases
        $ruta = strtr($ruta, ['auth/sendOtp' => 'auth/send-otp', 'auth/verifyCode' => 'auth/verify-code']);

        match ($ruta) {
            'auth/login'        => self::paginaEmail(),
            'auth/send-otp'     => self::procesarEnvio(),
            'auth/verify'       => self::paginaCodigo(),
            'auth/verify-code'  => self::procesarCodigo(),
            'auth/totp'             => self::paginaTotp(),
            'auth/verify-totp'      => self::procesarTotp(),
            'auth/enviar-otp-totp'  => self::procesarEnvioDesdeTotp(),
            'auth/logout'       => self::procesarLogout(),
            default             => null,
        };
    }

    // ── 1. Formulario de email ────────────────────────────

    private static function paginaEmail(): void
    {
        $error    = $_SESSION['_acc_error'] ?? null;
        $redirect = htmlspecialchars($_GET['redirect'] ?? '');
        unset($_SESSION['_acc_error']);
        $nombre = self::$cfg['nombre'];
        $param  = self::$pParam;

        echo <<<HTML
        <!DOCTYPE html><html lang="es"><head>
        <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Acceso · {$nombre}</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        </head>
        <body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">
        <div style="max-width:420px;width:100%;padding:16px">
          <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
              <div class="text-center mb-4">
                <i class="bi bi-shield-lock" style="font-size:2.5rem;color:#0d6efd"></i>
                <h5 class="fw-bold mt-2 mb-0">Acceso restringido</h5>
                <p class="text-muted small">{$nombre} · Hotel Atankalama</p>
              </div>
        HTML;

        if ($error) {
            echo '<div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle me-1"></i>'
                . htmlspecialchars($error) . '</div>';
        }

        echo <<<HTML
              <form method="POST" action="index.php?{$param}=auth/send-otp">
                <input type="hidden" name="redirect" value="{$redirect}">
                <div class="mb-3">
                  <label class="form-label fw-semibold">Correo electrónico</label>
                  <input type="email" name="email" class="form-control"
                         placeholder="tu@email.com" required autofocus>
                  <div class="form-text">Recibirás un código de 6 dígitos.</div>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                  <i class="bi bi-send me-1"></i> Enviar código
                </button>
              </form>
            </div>
          </div>
          <p class="text-center text-muted small mt-3">
            &copy; <?= date('Y') ?> Rodrigo Jaque Escobar
          </p>
        </div>
        </body></html>
        HTML;
    }

    // ── 2. Procesar envío OTP ─────────────────────────────

    private static function procesarEnvio(): void
    {
        $email    = trim($_POST['email']    ?? '');
        $redirect = trim($_POST['redirect'] ?? '');
        $loginUrl = self::$cfg['loginUrl'];
        $param    = self::$pParam;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['_acc_error'] = 'Ingresa un correo válido.';
            header("Location: {$loginUrl}&redirect=" . urlencode($redirect));
            exit;
        }

        $pdo  = acceso_pdo();
        $user = self::buscarUsuario($pdo, $email, self::$cfg['app']);

        if (!$user) {
            $_SESSION['_acc_error'] = 'Correo no autorizado o sin acceso a esta sección.';
            header("Location: {$loginUrl}&redirect=" . urlencode($redirect));
            exit;
        }

        // ¿Usuario tiene TOTP activo? → bifurcar sin enviar correo
        if (self::totpActivo($pdo, $email)) {
            $_SESSION['_acc_otp_email']    = $email;
            $_SESSION['_acc_otp_redirect'] = $redirect;
            header("Location: index.php?{$param}=auth/totp");
            exit;
        }

        // Invalidar tokens previos
        $pdo->prepare("UPDATE chk_login_tokens SET used = 1 WHERE email = ? AND used = 0")
            ->execute([$email]);

        // Generar código
        $code      = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $ip        = $_SERVER['REMOTE_ADDR']     ?? '';
        $ua        = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $pdo->prepare("
            INSERT INTO chk_login_tokens (email, token, expires_at, used, attempts, ip_address, user_agent)
            VALUES (?, ?, ?, 0, 0, ?, ?)
        ")->execute([$email, $code, $expiresAt, $ip, $ua]);

        if (!self::enviarEmail($email, $code, self::$cfg['nombre'])) {
            $_SESSION['_acc_error'] = 'No se pudo enviar el correo. Intenta nuevamente.';
            header("Location: {$loginUrl}&redirect=" . urlencode($redirect));
            exit;
        }

        $_SESSION['_acc_otp_email']    = $email;
        $_SESSION['_acc_otp_redirect'] = $redirect;

        header("Location: index.php?{$param}=auth/verify");
        exit;
    }

    // ── 3. Formulario de código ───────────────────────────

    private static function paginaCodigo(): void
    {
        if (empty($_SESSION['_acc_otp_email'])) {
            header('Location: ' . self::$cfg['loginUrl']);
            exit;
        }

        $email  = htmlspecialchars($_SESSION['_acc_otp_email']);
        $error  = $_SESSION['_acc_error'] ?? null;
        $nombre = self::$cfg['nombre'];
        $param  = self::$pParam;
        unset($_SESSION['_acc_error']);

        echo <<<HTML
        <!DOCTYPE html><html lang="es"><head>
        <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Verificar código · {$nombre}</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        </head>
        <body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">
        <div style="max-width:420px;width:100%;padding:16px">
          <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
              <div class="text-center mb-4">
                <i class="bi bi-envelope-check" style="font-size:2.5rem;color:#0d6efd"></i>
                <h5 class="fw-bold mt-2 mb-0">Ingresa tu código</h5>
                <p class="text-muted small">Enviado a <strong>{$email}</strong></p>
              </div>
        HTML;

        if ($error) {
            echo '<div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle me-1"></i>'
                . htmlspecialchars($error) . '</div>';
        }

        echo <<<HTML
              <form method="POST" action="index.php?{$param}=auth/verify-code" id="frmCodigo">
                <div class="mb-4">
                  <input type="text" name="code" id="inputCodigo"
                         class="form-control text-center fw-bold"
                         style="font-size:2rem;letter-spacing:.5rem"
                         maxlength="6" pattern="\d{6}" inputmode="numeric"
                         autocomplete="one-time-code" placeholder="000000"
                         required autofocus>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                  <i class="bi bi-check-circle me-1"></i> Verificar
                </button>
              </form>
              <div class="text-center mt-3">
                <a href="index.php?{$param}=auth/login" class="text-muted small">
                  <i class="bi bi-arrow-left"></i> Ingresar otro correo
                </a>
              </div>
            </div>
          </div>
          <p class="text-center text-muted small mt-3">
            &copy; <?= date('Y') ?> Rodrigo Jaque Escobar
          </p>
        </div>
        <script>
          document.getElementById('inputCodigo').addEventListener('input', function () {
            var clean = this.value.replace(/\D/g,'');
            this.value = clean;
            if (clean.length === 6) document.getElementById('frmCodigo').submit();
          });
        </script>
        </body></html>
        HTML;
    }

    // ── 4. Procesar verificación de código ────────────────

    private static function procesarCodigo(): void
    {
        $email    = $_SESSION['_acc_otp_email']    ?? '';
        $redirect = $_SESSION['_acc_otp_redirect'] ?? '';
        $code     = trim($_POST['code'] ?? '');
        $param    = self::$pParam;

        if (!$email) {
            header('Location: ' . self::$cfg['loginUrl']);
            exit;
        }

        $pdo  = acceso_pdo();
        $stmt = $pdo->prepare("
            SELECT id, token, attempts
            FROM chk_login_tokens
            WHERE email     = ?
              AND used       = 0
              AND expires_at > NOW()
              AND attempts   < 5
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$email]);
        $token = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$token) {
            $_SESSION['_acc_error'] = 'Código expirado o agotado. Solicita uno nuevo.';
            unset($_SESSION['_acc_otp_email'], $_SESSION['_acc_otp_redirect']);
            header('Location: ' . self::$cfg['loginUrl']);
            exit;
        }

        // Incrementar intentos
        $pdo->prepare("UPDATE chk_login_tokens SET attempts = attempts + 1 WHERE id = ?")
            ->execute([$token['id']]);

        if (!hash_equals($token['token'], $code)) {
            $restantes = 5 - ((int)$token['attempts'] + 1);
            $_SESSION['_acc_error'] = "Código incorrecto. Intentos restantes: {$restantes}.";
            header("Location: index.php?{$param}=auth/verify");
            exit;
        }

        // Marcar usado y registrar sesión activa
        $pdo->prepare("UPDATE chk_login_tokens SET used = 1 WHERE id = ?")->execute([$token['id']]);
        $pdo->prepare("
            UPDATE chk_usuarios
            SET last_login = NOW(), forzar_logout = 0,
                sesion_expira_en = ?
            WHERE email = ?
        ")->execute([date('Y-m-d H:i:s', time() + 4 * 3600), $email]);

        // Log de ingreso en BD
        try {
            $pdo->prepare("
                INSERT INTO chk_login_log (email, app_slug, ip_address, user_agent)
                VALUES (?, ?, ?, ?)
            ")->execute([
                $email,
                self::$cfg['app'],
                $_SERVER['REMOTE_ADDR']     ?? null,
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            ]);
        } catch (\Throwable) {
            // Tabla aún no creada — ignorar
        }

        // Log de ingreso en archivo centralizado
        AccesoLog::registrar(self::$cfg['app'], self::$cfg['nombre'], $email);

        // Abrir sesión
        unset($_SESSION['_acc_otp_email'], $_SESSION['_acc_otp_redirect']);
        $eKey = self::$cfg['emailKey'];
        $xKey = self::$cfg['expKey'];
        $_SESSION[$eKey] = $email;
        $_SESSION[$xKey] = time() + (4 * 3600);

        $destino = $redirect ?: (self::$cfg['publicas'][0] ?? '');
        header("Location: index.php?{$param}=" . urlencode($destino));
        exit;
    }

    // ── 5. Formulario TOTP ───────────────────────────────

    private static function paginaTotp(): void
    {
        if (empty($_SESSION['_acc_otp_email'])) {
            header('Location: ' . self::$cfg['loginUrl']);
            exit;
        }

        $error  = $_SESSION['_acc_error'] ?? null;
        $nombre = self::$cfg['nombre'];
        $param  = self::$pParam;
        unset($_SESSION['_acc_error']);

        echo <<<HTML
        <!DOCTYPE html><html lang="es"><head>
        <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Verificar código · {$nombre}</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        </head>
        <body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">
        <div style="max-width:420px;width:100%;padding:16px">
          <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
              <div class="text-center mb-4">
                <i class="bi bi-phone" style="font-size:2.5rem;color:#0d6efd"></i>
                <h5 class="fw-bold mt-2 mb-0">Código de autenticador</h5>
                <p class="text-muted small">{$nombre} · Hotel Atankalama</p>
              </div>
        HTML;

        if ($error) {
            echo '<div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle me-1"></i>'
                . htmlspecialchars($error) . '</div>';
        }

        echo <<<HTML
              <p class="text-muted small text-center mb-3">
                Abre <strong>Google Authenticator</strong> e ingresa el código de 6 dígitos que aparece para <em>Hotel Atankalama</em>.
              </p>
              <form method="POST" action="index.php?{$param}=auth/verify-totp" id="frmTotp">
                <div class="mb-4">
                  <input type="text" name="code" id="inputTotp"
                         class="form-control text-center fw-bold"
                         style="font-size:2rem;letter-spacing:.5rem"
                         maxlength="6" pattern="\d{6}" inputmode="numeric"
                         autocomplete="one-time-code" placeholder="000000"
                         required autofocus>
                  <div class="form-text text-center">El código cambia cada 30 segundos</div>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                  <i class="bi bi-check-circle me-1"></i> Verificar
                </button>
              </form>
              <div class="text-center mt-3">
                <a href="index.php?{$param}=auth/enviar-otp-totp" class="btn btn-outline-secondary btn-sm w-100 mb-2">
                  <i class="bi bi-envelope me-1"></i> Recibir código por correo
                </a>
                <a href="index.php?{$param}=auth/login" class="text-muted small">
                  <i class="bi bi-arrow-left"></i> Ingresar otro correo
                </a>
              </div>
            </div>
          </div>
          <p class="text-center text-muted small mt-3">
            &copy; <?= date('Y') ?> Rodrigo Jaque Escobar
          </p>
        </div>
        <script>
          document.getElementById('inputTotp').addEventListener('input', function () {
            this.value = this.value.replace(/\D/g,'').slice(0,6);
            if (this.value.length === 6) document.getElementById('frmTotp').submit();
          });
        </script>
        </body></html>
        HTML;
    }

    // ── 6. Procesar código TOTP ───────────────────────────

    private static function procesarTotp(): void
    {
        $email    = $_SESSION['_acc_otp_email']    ?? '';
        $redirect = $_SESSION['_acc_otp_redirect'] ?? '';
        $code     = preg_replace('/\D/', '', trim($_POST['code'] ?? ''));
        $param    = self::$pParam;

        if (!$email) {
            header('Location: ' . self::$cfg['loginUrl']);
            exit;
        }

        // Obtener secreto del usuario
        $pdo    = acceso_pdo();
        $stmt   = $pdo->prepare("SELECT totp_secret FROM chk_usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $secret = $stmt->fetchColumn();

        if (!$secret || strlen($code) !== 6) {
            $_SESSION['_acc_error'] = 'Código inválido. Intenta nuevamente.';
            header("Location: index.php?{$param}=auth/totp");
            exit;
        }

        // Cargar librería TOTP
        $vendorPaths = [
            $_SERVER['DOCUMENT_ROOT'] . '/novedades/vendor/autoload.php',
            dirname($_SERVER['SCRIPT_FILENAME'], 3) . '/vendor/autoload.php',
            dirname($_SERVER['SCRIPT_FILENAME'], 2) . '/vendor/autoload.php',
        ];
        foreach ($vendorPaths as $path) {
            if (file_exists($path)) { require_once $path; break; }
        }

        try {
            $tfa = new \RobThree\Auth\TwoFactorAuth(
                new \RobThree\Auth\Providers\Qr\ImageChartsQRCodeProvider(),
                'Hotel Atankalama'
            );
            $ok = $tfa->verifyCode($secret, $code);
        } catch (\Throwable $e) {
            error_log('AccesoBootstrap::procesarTotp — ' . $e->getMessage());
            $_SESSION['_acc_error'] = 'Error al verificar. Intenta nuevamente.';
            header("Location: index.php?{$param}=auth/totp");
            exit;
        }

        if (!$ok) {
            $_SESSION['_acc_error'] = 'Código incorrecto. Verifica que la hora de tu teléfono sea correcta.';
            header("Location: index.php?{$param}=auth/totp");
            exit;
        }

        // Código válido → abrir sesión (mismo flujo que procesarCodigo)
        $pdo->prepare("
            UPDATE chk_usuarios
            SET last_login = NOW(), forzar_logout = 0, sesion_expira_en = ?
            WHERE email = ?
        ")->execute([date('Y-m-d H:i:s', time() + 4 * 3600), $email]);

        try {
            $pdo->prepare("
                INSERT INTO chk_login_log (email, app_slug, ip_address, user_agent)
                VALUES (?, ?, ?, ?)
            ")->execute([
                $email,
                self::$cfg['app'],
                $_SERVER['REMOTE_ADDR']     ?? null,
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            ]);
        } catch (\Throwable) {}

        // Log de ingreso en archivo centralizado
        AccesoLog::registrar(self::$cfg['app'], self::$cfg['nombre'], $email);

        unset($_SESSION['_acc_otp_email'], $_SESSION['_acc_otp_redirect']);
        $_SESSION[self::$cfg['emailKey']] = $email;
        $_SESSION[self::$cfg['expKey']]   = time() + (4 * 3600);

        $destino = $redirect ?: (self::$cfg['publicas'][0] ?? '');
        header("Location: index.php?{$param}=" . urlencode($destino));
        exit;
    }

    // ── 7. Enviar OTP por correo desde pantalla TOTP ─────

    private static function procesarEnvioDesdeTotp(): void
    {
        $email  = $_SESSION['_acc_otp_email']    ?? '';
        $redirect = $_SESSION['_acc_otp_redirect'] ?? '';
        $param  = self::$pParam;

        if (!$email) {
            header('Location: ' . self::$cfg['loginUrl']);
            exit;
        }

        $pdo = acceso_pdo();

        // Invalidar tokens previos
        $pdo->prepare("UPDATE chk_login_tokens SET used = 1 WHERE email = ? AND used = 0")
            ->execute([$email]);

        // Generar código
        $code      = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $ip        = $_SERVER['REMOTE_ADDR']     ?? '';
        $ua        = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $pdo->prepare("
            INSERT INTO chk_login_tokens (email, token, expires_at, used, attempts, ip_address, user_agent)
            VALUES (?, ?, ?, 0, 0, ?, ?)
        ")->execute([$email, $code, $expiresAt, $ip, $ua]);

        if (!self::enviarEmail($email, $code, self::$cfg['nombre'])) {
            $_SESSION['_acc_error'] = 'No se pudo enviar el correo. Intenta nuevamente.';
            header("Location: index.php?{$param}=auth/totp");
            exit;
        }

        // La sesión _acc_otp_email y _acc_otp_redirect ya están activas
        header("Location: index.php?{$param}=auth/verify");
        exit;
    }

    // ── 8. Logout ─────────────────────────────────────────

    private static function procesarLogout(): void
    {
        $eKey  = self::$cfg['emailKey'];
        $xKey  = self::$cfg['expKey'];
        $email = $_SESSION[$eKey] ?? null;
        unset($_SESSION[$eKey], $_SESSION[$xKey]);

        if ($email) {
            try {
                acceso_pdo()->prepare(
                    "UPDATE chk_usuarios SET sesion_expira_en = NULL WHERE email = ?"
                )->execute([$email]);
            } catch (\Throwable) {}
        }

        // Redirigir a primera ruta pública o raíz
        $destino = self::$cfg['publicas'][0] ?? '';
        header('Location: index.php?' . self::$pParam . '=' . urlencode($destino));
        exit;
    }

    // ────────────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ────────────────────────────────────────────────────────────────────────

    private static function totpActivo(PDO $pdo, string $email): bool
    {
        try {
            $stmt = $pdo->prepare(
                "SELECT totp_habilitado FROM chk_usuarios WHERE email = ? LIMIT 1"
            );
            $stmt->execute([$email]);
            return (bool) $stmt->fetchColumn();
        } catch (\Throwable) {
            return false; // columnas aún no migradas → flujo email normal
        }
    }

    private static function buscarUsuario(PDO $pdo, string $email, string $appSlug): array|false
    {
        $stmt = $pdo->prepare("
            SELECT u.id, u.email
            FROM chk_usuarios u
            JOIN chk_usuario_apps ua ON ua.usuario_id = u.id
            JOIN chk_apps a          ON a.id = ua.app_id
            WHERE u.email = ? AND u.estado = 'activo'
              AND a.slug  = ? AND a.estado = 'activo'
            LIMIT 1
        ");
        $stmt->execute([$email, $appSlug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private static function nombreApp(string $slug): string
    {
        try {
            $stmt = acceso_pdo()->prepare("SELECT nombre FROM chk_apps WHERE slug = ? LIMIT 1");
            $stmt->execute([$slug]);
            return $stmt->fetchColumn() ?: $slug;
        } catch (\Throwable) {
            return $slug;
        }
    }

    private static function enviarEmail(string $email, string $code, string $appNombre): bool
    {
        // PHPMailer: buscar vendor en la app actual o en novedades como fallback
        $vendorPaths = [
            $_SERVER['DOCUMENT_ROOT'] . '/novedades/vendor/autoload.php',
            dirname($_SERVER['SCRIPT_FILENAME'], 3) . '/vendor/autoload.php',
            dirname($_SERVER['SCRIPT_FILENAME'], 2) . '/vendor/autoload.php',
        ];
        $loaded = false;
        foreach ($vendorPaths as $path) {
            if (file_exists($path)) {
                require_once $path;
                $loaded = true;
                break;
            }
        }
        if (!$loaded) {
            error_log('AccesoBootstrap: no se encontró vendor/autoload.php');
            return false;
        }

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
            $mail->Subject = "Código de acceso · {$appNombre}";
            $mail->Body    = self::plantillaEmail($code, $appNombre);
            $mail->AltBody = "Tu código de acceso es: {$code}\nVálido por 10 minutos.";
            $mail->send();
            return true;
        } catch (\Throwable $e) {
            error_log('AccesoBootstrap::enviarEmail — ' . $e->getMessage());
            return false;
        }
    }

    private static function plantillaEmail(string $code, string $appNombre): string
    {
        return <<<HTML
        <!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"></head>
        <body style="font-family:Arial,sans-serif;background:#f4f4f4;margin:0;padding:20px;">
          <div style="max-width:420px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.1);">
            <div style="background:#1e3a5f;padding:24px;text-align:center;">
              <h2 style="color:#fff;margin:0;font-size:20px;">Hotel Atankalama</h2>
              <p style="color:#a8c4e0;margin:4px 0 0;font-size:13px;">{$appNombre}</p>
            </div>
            <div style="padding:32px 24px;text-align:center;">
              <p style="color:#555;font-size:15px;margin:0 0 24px;">Tu código de acceso es:</p>
              <div style="display:inline-block;background:#f0f4ff;border:2px dashed #1e3a5f;border-radius:8px;padding:16px 32px;">
                <span style="font-size:36px;font-weight:bold;letter-spacing:10px;color:#1e3a5f;">{$code}</span>
              </div>
              <p style="color:#888;font-size:13px;margin:24px 0 0;">Válido por <strong>10 minutos</strong>. No compartas este código.</p>
            </div>
            <div style="background:#f9f9f9;padding:12px;text-align:center;border-top:1px solid #eee;">
              <p style="color:#aaa;font-size:11px;margin:0;">Si no solicitaste este código, ignora este mensaje.</p>
            </div>
          </div>
        </body></html>
        HTML;
    }
}
