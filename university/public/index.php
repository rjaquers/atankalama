<?php ob_start();
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 * Este software es propiedad exclusiva de su autor.
 * Se concede un derecho de uso limitado al cliente. No se transfiere
 * la propiedad del código ni de la aplicación.
 *
 * @author  Rodrigo Jaque Escobar
 * @project Universidad Atankalama — Sistema de Capacitación
 */
require_once "../config/config.php";
require_once "../config/database.php";
require_once "../app/core/Autoload.php";
require_once "../app/helpers/csrf.php";

if (session_status() === PHP_SESSION_NONE) session_start();

// ── Sesión del hub central (portal) ──────────────────────────────────────────
// La autenticación la maneja https://www.atankalama.com/login
// No hay OTP propio; se confía en la sesión portal_email/portal_expires.

function univ_email(): ?string {
    $email = $_SESSION['portal_email']   ?? null;
    $exp   = $_SESSION['portal_expires'] ?? 0;
    if (!$email || time() > (int)$exp) return null;
    return $email;
}

// ── Logout centralizado ───────────────────────────────────────────────────────
if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) !== false) {
    $_logoutBasePath = rtrim((string) parse_url(BASE_URL, PHP_URL_PATH), '/');
    $_logoutReqPath  = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $_logoutClean    = trim(substr($_logoutReqPath, strlen($_logoutBasePath)), '/');
    if ($_logoutClean === 'logout') {
        header('Location: https://www.atankalama.com/login/index.php?route=auth/logout');
        exit;
    }
}

// ── Protección: redirigir al hub si no hay sesión ─────────────────────────────
$route         = trim($_GET['route'] ?? 'univ/index', '/');
$rutasPublicas = ['offline-sync/store'];

if (!in_array($route, $rutasPublicas) && !univ_email()) {
    $destino = 'https://www.atankalama.com/login/index.php?route=auth/login'
             . '&redirect=' . urlencode(BASE_URL . '/index.php?route=' . $route);
    ob_end_clean();
    header('Location: ' . $destino);
    exit;
}

// Registrar listeners de eventos
EventDispatcher::listen("offline_synced", function($data){
    app_log("EVENT offline_synced: " . json_encode($data, JSON_UNESCAPED_UNICODE));
});

$router = new Router();
$router->dispatch();
