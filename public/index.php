<?php
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 * Este software es propiedad exclusiva de su autor.
 * Se concede un derecho de uso limitado al cliente. No se transfiere
 * la propiedad del código ni de la aplicación.
 *
 * @author  Rodrigo Jaque Escobar
 * @project Sistema Trello — Hotel Atankalama
 */

session_start();

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../app/core/Autoload.php';

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
AccesoBootstrap::arrancar('trello', 'trl', $ruta, []);

$email = AccesoBootstrap::email();

// ── Eventos ──────────────────────────────────────────────────────────────────
EventDispatcher::listen('offline_synced', function ($data) {
    app_log('EVENT offline_synced: ' . json_encode($data, JSON_UNESCAPED_UNICODE));
});

// ── Dispatch ─────────────────────────────────────────────────────────────────
$router = new Router();
$router->dispatch();
