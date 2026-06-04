<?php
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 * Este software es propiedad exclusiva de su autor.
 * Se concede un derecho de uso limitado al cliente. No se transfiere
 * la propiedad del código ni de la aplicación.
 *
 * @author  Rodrigo Jaque Escobar
 * @project Sistema de Cocina - Hotel Atankalama
 */
require_once __DIR__ . '/../app/config/config.php';

// ── Logout centralizado ──────────────────────────────────────────────────────
// Limpiar sesión local y redirigir al logout del hub central
if (($_GET['page'] ?? '') === 'logout') {
    if (session_status() === PHP_SESSION_NONE) session_start();
    unset($_SESSION['coc_admin_email'], $_SESSION['coc_admin_expires']);
    header('Location: https://www.atankalama.com/login/index.php?route=auth/logout');
    exit;
}

$uri = $_GET['page'] ?? 'recepcion/particular';

$params = explode('/', $uri);
$uriBase = ($params[0] ?? '') . '/' . ($params[1] ?? 'index');

// cocina/index es completamente pública — AccesoBootstrap no se carga para esta ruta
if ($uriBase !== 'cocina/index') {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/shared/AccesoBootstrap.php';
    AccesoBootstrap::arrancar('cocina', 'coc', $uriBase, [
        'cocina/cerrar',              // Display público de cocina
        'recepcion/contratosEmpresa', // AJAX: carga contratos por empresa
        'voucher/kiosko',             // Pantalla kiosko (sin auth)
        'voucher/buscar',             // AJAX búsqueda por RUT (kiosko)
        'voucher/registrarImpresion', // AJAX registrar impresión (kiosko)
        'voucher/ver',                // Ver voucher por QR (público)
    ]);
    $email = AccesoBootstrap::email();
} else {
    $email = null;
}

$controllerName = ucfirst($params[0]) . 'Controller';
$method = $params[1] ?? 'index';
$arg = $params[2] ?? null;

$controllerPath = __DIR__ . '/../app/controllers/' . $controllerName . '.php';

if (file_exists($controllerPath)) {
    require_once $controllerPath;
    if (class_exists($controllerName)) {
        $controller = new $controllerName();
        if (method_exists($controller, $method)) {
            $arg ? $controller->$method($arg) : $controller->$method();
        } else {
            echo "Método '$method' no encontrado en $controllerName.";
        }
    } else {
        echo "Controlador '$controllerName' no existe.";
    }
} else {
    echo "Archivo de controlador '$controllerPath' no encontrado.";
}
?>