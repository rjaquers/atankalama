<?php
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 * Este software es propiedad exclusiva de su autor.
 * Se concede un derecho de uso limitado al cliente. No se transfiere
 * la propiedad del código ni de la aplicación.
 *
 * @author  Rodrigo Jaque Escobar
 * @project Sistema de Checklists
 */
ob_start();

require_once __DIR__ . '/../config/config.php';

// Autoloader Simple
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// ── Logout centralizado ──────────────────────────────────────────────────────
// Limpiar sesión local y redirigir al logout del hub central
if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) !== false) {
    $_logoutBasePath = rtrim((string) parse_url(BASE_URL, PHP_URL_PATH), '/');
    $_logoutReqPath  = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $_logoutClean    = trim(substr($_logoutReqPath, strlen($_logoutBasePath)), '/');
    if ($_logoutClean === 'logout') {
        if (session_status() === PHP_SESSION_NONE) session_start();
        unset($_SESSION['chk_admin_email'], $_SESSION['chk_admin_expires']);
        header('Location: https://www.atankalama.com/login/index.php?route=auth/logout');
        exit;
    }
}

// ── AccesoBootstrap — sistema centralizado de autenticación OTP ─────────────
require_once $_SERVER['DOCUMENT_ROOT'] . '/shared/AccesoBootstrap.php';

// Extraer ruta limpia del path (igual que el router interno)
$_accesoBasePath = rtrim((string) parse_url(BASE_URL, PHP_URL_PATH), '/');
$_accesoReqPath  = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$_accesoClean    = ltrim(substr($_accesoReqPath, strlen($_accesoBasePath)), '/');
$_accesoClean    = (string) preg_replace('#^index\.php/?#', '', $_accesoClean);
// Normalizar segmentos numéricos dinámicos: checklists/editar/5 → checklists/editar
$_accesoClean    = (string) preg_replace('#(/\d+)+(/|$)#', '$2', $_accesoClean);
$_accesoClean    = trim($_accesoClean, '/');

// ?route= tiene prioridad (redireccionamientos internos de AccesoBootstrap)
$_accesoRoute = trim($_GET['route'] ?? '');
$_accesoRuta  = $_accesoRoute !== '' ? $_accesoRoute : ($_accesoClean ?: 'dashboard');

// Rutas completamente públicas (sin autenticación requerida)
$_accesoEsPublica = (bool) preg_match('#^(encuesta|api/encuesta)#', $_accesoRuta);

if (!$_accesoEsPublica) {
    AccesoBootstrap::arrancar('checklist', 'chk', $_accesoRuta, [], 'Sistema de Checklists');

    // Si AccesoBootstrap llega aquí con ?route=, redirigir a la URL de ruta limpia
    // (ocurre tras login exitoso o logout)
    if (isset($_GET['route'])) {
        if ($_accesoRoute !== '' && !str_starts_with($_accesoRoute, 'api/')) {
            header('Location: ' . BASE_URL . '/' . ltrim($_accesoRoute, '/'));
        } else {
            header('Location: ' . BASE_URL . '/dashboard');
        }
        exit;
    }
}

$router = new App\Core\Router();

// Definir Rutas

// Rutas Protegidas
$router->add('GET', '/', 'DashboardController', 'index', 'AuthMiddleware');
$router->add('GET', '/dashboard', 'DashboardController', 'index', 'AuthMiddleware');
$router->add('GET', '/checklists', 'ChecklistController', 'index', 'AuthMiddleware');
$router->add('GET', '/checklists/nuevo', 'ChecklistController', 'create', 'AuthMiddleware');
$router->add('GET', '/checklists/editar/:id', 'ChecklistController', 'edit', 'AuthMiddleware');
$router->add('POST', '/api/checklists/guardar', 'ChecklistController', 'store', 'AuthMiddleware');
$router->add('POST', '/api/checklists/actualizar/:id', 'ChecklistController', 'update', 'AuthMiddleware');
$router->add('POST', '/api/checklists/eliminar/:id', 'ChecklistController', 'delete', 'AuthMiddleware');
$router->add('GET', '/evaluaciones', 'EvaluationController', 'index', 'AuthMiddleware');
$router->add('GET', '/evaluaciones/ejecutar', 'EvaluationController', 'execute', 'AuthMiddleware');
$router->add('POST', '/api/evaluaciones/guardar', 'EvaluationController', 'store', 'AuthMiddleware');
$router->add('GET', '/reportes', 'ReportController', 'index', 'AuthMiddleware');
$router->add('GET', '/reportes/stats', 'ReportController', 'stats', 'AuthMiddleware');
$router->add('GET', '/reportes/ver', 'ReportController', 'view', 'AuthMiddleware');
$router->add('POST', '/api/reportes/eliminar/:id', 'ReportController', 'delete', 'AuthMiddleware');
$router->add('GET', '/reportes/logs', 'ReportController', 'logs', 'AuthMiddleware');
$router->add('GET', '/reportes/encuestas', 'ReportController', 'encuestas', 'AuthMiddleware');
$router->add('GET', '/reportes/encuestas/exportar', 'ReportController', 'exportEncuestas', 'AuthMiddleware');
$router->add('GET', '/usuarios', 'UserController', 'index', 'AuthMiddleware');
$router->add('POST', '/usuarios/guardar', 'UserController', 'store', 'AuthMiddleware');
$router->add('POST', '/usuarios/actualizar', 'UserController', 'update', 'AuthMiddleware');
$router->add('POST', '/usuarios/eliminar', 'UserController', 'delete', 'AuthMiddleware');
$router->add('GET', '/areas', 'AreaController', 'index', 'AuthMiddleware');
$router->add('POST', '/areas/guardar', 'AreaController', 'store', 'AuthMiddleware');
$router->add('POST', '/areas/eliminar', 'AreaController', 'delete', 'AuthMiddleware');

// Rutas Públicas (sin autenticación)
$router->add('GET', '/encuesta/:token', 'SurveyController', 'show');
$router->add('POST', '/api/encuesta/:token/guardar', 'SurveyController', 'store');

// Despachar
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$router->dispatch($method, $uri);
