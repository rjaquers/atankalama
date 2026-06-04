<?php
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 * Este software es propiedad exclusiva de su autor.
 * Se concede un derecho de uso limitado al cliente. No se transfiere
 * la propiedad del código ni de la aplicación.
 *
 * @author  Rodrigo Jaque Escobar
 * @project Tableros de proyectos Kanban — Hotel Atankalama
 */

ob_start();

// ── Security Headers ─────────────────────────────────────────────────────────
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net; font-src 'self' cdn.jsdelivr.net data:; img-src 'self' data:; connect-src 'self'; frame-ancestors 'none';");
header_remove("X-Powered-By");

error_reporting(E_ALL);
ini_set('display_errors', 1);

error_log("[TRELLO] Petición recibida: " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));

require_once '../config/config.php';    // ini_set de sesión antes de session_start
require_once '../config/database.php';
require_once '../app/core/Autoload.php';
// ...
session_start();

require_once '../app/helpers/logger.php';
app_log("RUTA RECIBIDA: " . ($_GET['route'] ?? $_GET['url'] ?? 'vacia'));

// ── Logout centralizado ──────────────────────────────────────────────────────
// Captura tanto ?route= (AccesoBootstrap) como ?url= (rewrite de Apache)
$ruta = trim(($_GET['route'] ?? '') ?: ($_GET['url'] ?? ''), '/') ?: 'tablero';

// 'login' es alias de la ruta interna de AccesoBootstrap
if ($ruta === 'login') $ruta = 'auth/login';

if ($ruta === 'logout') {
    unset($_SESSION['trl_admin_email'], $_SESSION['trl_admin_expires']);
    header('Location: https://www.atankalama.com/login/index.php?route=auth/logout');
    exit;
}

// ── Autenticación OTP compartida del hotel ───────────────────────────────────

require_once $_SERVER['DOCUMENT_ROOT'] . '/shared/AccesoBootstrap.php';

$GLOBALS['ruta'] = $ruta;
// recordatorio/ejecutar es llamado por el cron del servidor (sin sesión),
// se protege por token secreto dentro del propio controlador.
AccesoBootstrap::arrancar('trello', 'trl', $ruta, [
    'recordatorio/ejecutar',
]);

$email = AccesoBootstrap::email();

// ── Eventos ──────────────────────────────────────────────────────────────────
EventDispatcher::listen('offline_synced', function ($data) {
    app_log('EVENT offline_synced: ' . json_encode($data, JSON_UNESCAPED_UNICODE));
});

// ── Dispatch ─────────────────────────────────────────────────────────────────
$router = new Router();
$router->dispatch();
