<?php
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 * Este software es propiedad exclusiva de su autor.
 * Se concede un derecho de uso limitado al cliente. No se transfiere
 * la propiedad del código ni de la aplicación.
 *
 * @author  Rodrigo Jaque Escobar
 * @project chatCalama — Sistema de Chat y Gestión de Tareas Hotel Atankalama
 */
require_once "../config/config.php";

// CORS para la app móvil (rutas /api/*)
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
if (str_contains($requestUri, '/api/')) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

session_start();

$_routeActual = $_GET['route'] ?? 'dashboard';

// ── Logout centralizado ──────────────────────────────────────────────────────
if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) !== false) {
    $_logoutBasePath = rtrim((string) parse_url(BASE_URL, PHP_URL_PATH), '/');
    $_logoutReqPath  = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $_logoutClean    = trim(substr($_logoutReqPath, strlen($_logoutBasePath)), '/');
    if ($_logoutClean === 'logout') {
        session_destroy();
        header('Location: https://www.atankalama.com/login/index.php?route=auth/logout');
        exit;
    }
}
// ────────────────────────────────────────────────────────────────────────────

require_once "../config/database.php";
require_once "../app/core/Autoload.php";

// ── Autenticación — solo rutas no-API ────────────────────────────────────────
if (!str_starts_with($_routeActual, 'api/')) {

    // Las rutas auth/* no tienen uso en chatcalama: el login es el hub central.
    if (str_starts_with($_routeActual, 'auth/')) {
        header('Location: https://www.atankalama.com/login/index.php');
        exit;
    }

    // Sin sesión válida del hub → redirigir al hub central (nunca mostrar login propio)
    $_portalEmail = $_SESSION['portal_email']   ?? null;
    $_portalExp   = $_SESSION['portal_expires'] ?? 0;
    if (!$_portalEmail || $_portalExp <= time()) {
        header('Location: https://www.atankalama.com/login/index.php');
        exit;
    }

    // ── Verificar forzar_logout desde chk_usuarios ───────────────────────────
    require_once $_SERVER['DOCUMENT_ROOT'] . '/shared/acceso_db.php';
    try {
        $_stmtFl = acceso_pdo()->prepare(
            "SELECT forzar_logout FROM chk_usuarios WHERE email = ? LIMIT 1"
        );
        $_stmtFl->execute([$_portalEmail]);
        $_rowFl = $_stmtFl->fetch(PDO::FETCH_ASSOC);
        if ($_rowFl && (int)$_rowFl['forzar_logout'] === 1) {
            session_destroy();
            header('Location: https://www.atankalama.com/login/index.php?route=auth/logout');
            exit;
        }
    } catch (\Throwable) {}
    // ─────────────────────────────────────────────────────────────────────────

    // Sincronizar sesión: perfil desde chk_usuarios, datos de chat desde chat_usuarios
    if (empty($_SESSION['user_id'])) {
        // Rol y nombre desde chk_usuarios (fuente de verdad)
        $_stmtChk = acceso_pdo()->prepare(
            "SELECT nombre, apellido, perfil FROM chk_usuarios WHERE email = ? AND estado = 'activo' LIMIT 1"
        );
        $_stmtChk->execute([$_portalEmail]);
        $_chkUser = $_stmtChk->fetch(PDO::FETCH_ASSOC);

        // ID, área y foto desde chat_usuarios
        $_chatUserModel = new ChatUserModel();
        $_chatUser      = $_chatUserModel->getByEmail($_portalEmail);

        // La autenticidad viene del hub (chk_usuarios) — chat_usuarios provee datos opcionales
        if ($_chkUser) {
            $_SESSION['user_nombre']  = trim($_chkUser['nombre'] . ' ' . ($_chkUser['apellido'] ?? ''));
            $_SESSION['user_email']   = $_portalEmail;
            $_SESSION['user_rol']     = $_chkUser['perfil'] ?? 'Operador';

            if ($_chatUser) {
                $_SESSION['user_id']      = (int)$_chatUser['id'];
                $_SESSION['user_rol_id']  = (int)($_chatUser['rol_id'] ?? 3);
                $_SESSION['user_area']    = $_chatUser['area_nombre'] ?? '';
                $_SESSION['user_area_id'] = (int)($_chatUser['area_id'] ?? 0);
                $_SESSION['user_foto']    = $_chatUser['foto_perfil'] ?? '';
                $_SESSION['user_es_jefe'] = (bool)($_chatUser['es_jefe'] ?? false);
                $_chatUserModel->updateLastAccess((int)$_chatUser['id']);
            } else {
                // Sin registro en chat_usuarios aún — acceso básico sin participación en chat
                $_SESSION['user_id']      = 0;
                $_SESSION['user_rol_id']  = 3;
                $_SESSION['user_area']    = '';
                $_SESSION['user_area_id'] = 0;
                $_SESSION['user_foto']    = '';
                $_SESSION['user_es_jefe'] = false;
            }
        }
    }
}
// ────────────────────────────────────────────────────────────────────────────

EventDispatcher::listen("offline_synced", function($data){
    app_log("EVENT offline_synced: " . json_encode($data, JSON_UNESCAPED_UNICODE));
});

$router = new Router();
$router->dispatch();
