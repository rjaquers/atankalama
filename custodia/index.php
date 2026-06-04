<?php
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 * Este software es propiedad exclusiva de su autor.
 * Se concede un derecho de uso limitado al cliente. No se transfiere
 * la propiedad del código ni de la aplicación.
 *
 * @author  Rodrigo Jaque Escobar
 * @project Sistema de Custodia — Hotel Atankalama
 */
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__.'/logs/php_errors.log');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * BASE de la app (subcarpeta pública)
 * Para tu caso es fijo: /custodia
 */
define('BASE_URL', '/custodia');

/** Helpers básicos */
function url(string $path = '/'): string
{
    if ($path === '' || $path[0] !== '/') {
        $path = '/'.$path;
    }

    return rtrim(BASE_URL, '/').$path;
}

function redirect(string $path = '/'): void
{
    header('Location: '.url($path));
    exit;
}

/** Conexión (expone $mysqli y opcionalmente $db=$mysqli) */
require_once __DIR__.'/connections/conec6.php';

// ── Logout centralizado ──────────────────────────────────────────────────────
if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) !== false) {
    $_logoutBasePath = rtrim((string) parse_url(BASE_URL, PHP_URL_PATH), '/');
    $_logoutReqPath  = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $_logoutClean    = trim(substr($_logoutReqPath, strlen($_logoutBasePath)), '/');
    if ($_logoutClean === 'logout') {
        unset($_SESSION['cus_admin_email'], $_SESSION['cus_admin_expires']);
        header('Location: https://www.atankalama.com/login/index.php?route=auth/logout');
        exit;
    }
}

// ── AccesoBootstrap: auth centralizado Hotel Atankalama ─────────────────────
require_once $_SERVER['DOCUMENT_ROOT'] . '/shared/AccesoBootstrap.php';

$_acc_ruta_param = $_GET['route'] ?? null;

if ($_acc_ruta_param !== null) {
    // Rama query-param: viene de redirect del sistema de auth (auth/login, auth/verify, etc.)
    // auth routes son manejadas internamente por AccesoBootstrap y hacen exit.
    // Para rutas no-auth (ej: colaciones/lotes post-login), AccesoBootstrap retorna y
    // redirigimos a la URL path-based definitiva.
    AccesoBootstrap::arrancar('custodia', 'cus', $_acc_ruta_param, [$_acc_ruta_param], 'Sistema de Custodia');
    header('Location: ' . rtrim(BASE_URL, '/') . '/' . ltrim($_acc_ruta_param, '/'));
    exit;
} else {
    // Rama path-based: rutas normales de la app.
    // AccesoBootstrap gestiona la sesión; auth_require() en cada ruta protege el acceso.
    $_acc_ruta_path = ltrim(current_path(), '/');
    if ($_acc_ruta_path === '' || $_acc_ruta_path === 'index.php') {
        $_acc_ruta_path = 'colaciones/lotes';
    }
    AccesoBootstrap::arrancar('custodia', 'cus', $_acc_ruta_path, [$_acc_ruta_path], 'Sistema de Custodia');
}

/** Email del usuario autenticado (disponible para controladores y vistas) */
$email = AccesoBootstrap::email();

/** Auth guard local (verifica sesión activa; usa AccesoBootstrap internamente) */
require_once __DIR__.'/includes/auth.php';

/** Controladores principales */
require_once __DIR__.'/controllers/ColacionController.php';
$colacionController = new ColacionController();

require_once __DIR__.'/controllers/TicketController.php';
$ticketController = new TicketController();

require_once __DIR__.'/controllers/EmpresaController.php';
$empresaController = new EmpresaController();

require_once __DIR__.'/controllers/ExcelColacionController.php';
$excelCtl = new ExcelColacionController();

require_once __DIR__.'/controllers/PersonaController.php';
$personaController = new PersonaController();

require_once __DIR__.'/controllers/ColacionVoucherController.php';
$voucherController = new ColacionVoucherController();






/** Normalizador de path (remueve BASE_URL y /index.php) */
function current_path(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
    $uri = preg_replace('#/+#', '/', $uri);

    // 1) Remover BASE_URL (/custodia)
    $base = rtrim(BASE_URL, '/');
    if ($base !== '' && strpos($uri, $base) === 0) {
        $uri = substr($uri, strlen($base));
    }

    // 2) Remover /index.php o /index.php/
    $uri = preg_replace('#^/index\.php#i', '', $uri);

    // 3) Si queda vacío, asignar "/"
    if ($uri === '' || $uri === false) {
        $uri = '/';
    }

    return $uri;
}


/** Router simple */
class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler): void
    {
        $regex = "#^{$pattern}$#i";
        $this->routes[] = [$method, $regex, $handler];

    }

    public function dispatch(string $method, string $uriPath): void
    {
        foreach ($this->routes as [$m, $regex, $handler]) {
            if (strcasecmp($m, $method) !== 0) {
                continue;
            }
            if (preg_match($regex, $uriPath, $mats)) {
                array_shift($mats);
                call_user_func_array($handler, $mats);

                return;
            }
        }
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo "404 No encontrado: {$uriPath}";
    }
}

$router = new Router();

/** ===================== RUTAS ===================== **/

// HOME → lista de colaciones
$router->add('GET', '/', function () {
    redirect('/colaciones/lotes');
});




// --- Colaciones (lotes)
$router->add('GET', '/colaciones/lotes', function () use ($colacionController) {
    auth_require();
    $colacionController->index();
});
$router->add('GET', '/colaciones/lotes/crear', function () use ($colacionController) {
    auth_require();
    $colacionController->crear();
});
$router->add('POST', '/colaciones/lotes/guardar', function () use ($colacionController) {
    auth_require();
    $colacionController->guardar();
});
$router->add('GET', '/colaciones/lotes/imprimir/([0-9]+)', function ($id) use ($colacionController) {
    auth_require();
    $colacionController->imprimir((int)$id);
});
$router->add('GET', '/colaciones/lotes/reimprimir/([0-9]+)', function ($id) use ($colacionController) {
    auth_require();
    $colacionController->reimprimir((int)$id);
});
$router->add('GET', '/colaciones/lotes/ver/([0-9]+)', function ($id) use ($colacionController) {
    auth_require();
    if (method_exists($colacionController, 'ver')) {
        $colacionController->ver((int)$id);
    } else {
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Detalle pendiente de implementar. ID lote: '.(int)$id;
    }
});



// --- Tickets Custodia
$router->add('GET', '/tickets/custodia/crear', function () use ($ticketController) {
    $ticketController->crear();
});
$router->add('GET', '/tickets/custodia/listar', function () use ($ticketController) {
    $ticketController->listarVista();
});
$router->add('GET', '/tickets/custodia/imprimir/([0-9]+)', function ($id) use ($ticketController) {
    $ticketController->imprimirVista((int)$id);
});




// Compatibilidad
$router->add('GET', '/views/tickets/form.php', function () {
    redirect('/tickets/custodia/crear');
});
$router->add('GET', '/views/tickets/listar.php', function () {
    redirect('/tickets/custodia/listar');
});
$router->add('GET', '/views/tickets/imprimir.php', function () {
    if (isset($_GET['id'])) {
        redirect('/tickets/custodia/imprimir/'.(int)$_GET['id']);
    }
    http_response_code(400);
    echo 'Falta parámetro id';
});
$router->add('GET', '/tickets/custodia/form.php', function () {
    redirect('/tickets/custodia/crear');
});




// (si usas “nuevo/guardar”)
$router->add('GET', '/tickets/custodia/nuevo', function () use ($ticketController) {
    $ticketController->nuevo();
});
$router->add('POST', '/tickets/custodia/guardar', function () use ($ticketController) {
    $ticketController->guardar();
});



// personas



$router->add('POST', '/colaciones/persona/guardar', function () use ($personaController) {
    auth_require();
    $personaController->guardar();
});

$router->add('POST', '/colaciones/persona/eliminar', function () use ($personaController) {
    auth_require();
    $personaController->eliminar();
});

$router->add('POST', '/colaciones/lotes/actualizar-fechas', function () use ($colacionController) {
    auth_require();
    $colacionController->actualizarFechas();
});

$router->add('POST', '/colaciones/lotes/actualizar-servicios', function () use ($colacionController) {
    auth_require();
    $colacionController->actualizarServicios();
});



// 2026 Creación del voucher
// --- Editar Lote ---
$router->add('GET', '/colaciones/lotes/form/([0-9]+)', function ($id) use ($colacionController) {
    auth_require();
    if (method_exists($colacionController, 'form')) {
        $colacionController->form((int)$id);
    } else {
        // fallback: reutiliza crear()
        $colacionController->crear((int)$id);
    }
});







//5) RUTA DEL VOUCHER INDIVIDUAL
$router->add('GET', '/colaciones/voucher/imprimir', function() {
    require_once __DIR__.'/controllers/ColacionVoucherController.php';
    $ctl = new ColacionVoucherController();
    $ctl->imprimirIndividual();
});









// --- Empresas
$router->add('GET', '/empresas/nuevo', function () use ($empresaController) {
    $empresaController->nuevo();
});
$router->add('POST', '/empresas/guardar', function () use ($empresaController) {
    $empresaController->guardar();
});
$router->add('GET', '/empresas/listar', function () use ($empresaController) {
    $empresaController->listar();
});

$router->add('GET', '/empresas/editar/([0-9]+)', function ($id) use ($empresaController) {
    auth_require();
    $empresaController->editar((int)$id);
});

$router->add('POST', '/empresas/actualizar', function () use ($empresaController) {
    auth_require();
    $empresaController->actualizar();
});



// --- WiFi (según tu controlador)
$router->add('GET', '/wifi/imprimir', function () use ($empresaController) {
    $empresaController->wifiimprimir();
});

// ... TODAS las rutas previas ...

// QR scan
$router->add('GET', '/colaciones/vouchers/scan', function () use ($colacionController) {
    $colacionController->scanVoucher();
});



// =====================================
//   Buscar servicio por RUT
// =====================================
$router->add('GET', '/colaciones/buscar', function () use ($colacionController) {
    $colacionController->buscar(); // muestra formulario y resultado si viene el rut
});

$router->add('POST', '/colaciones/buscar', function () use ($colacionController) {
    $colacionController->buscar(); // por si el formulario usa POST
});




// ----- Excel (importar/preview) -----
require_once __DIR__.'/controllers/ExcelColacionController.php';
$excelCtl = new ExcelColacionController();

$router->add('GET',  '/colaciones/excel/import',  function () {
    auth_require();
    include __DIR__ . '/views/colaciones/excel_import_form.php';
});
$router->add('POST', '/colaciones/excel/import',  function () {
    auth_require();
    include __DIR__ . '/views/colaciones/excel_import_form.php';
});

$router->add('GET',  '/colaciones/excel/preview', function () {
    auth_require();
    include __DIR__ . '/views/colaciones/excel_preview.php';
});
$router->add('POST', '/colaciones/excel/preview', function () {
    auth_require();
    include __DIR__ . '/views/colaciones/excel_preview.php';
});



/* Exportar reporte de impresiones a Excel */
$router->add('GET', '/colaciones/personas/(\d+)/exportar',
    function ($id) use ($colacionController) {
        auth_require();
        $colacionController->exportarImpresiones((int)$id);
    }
);

/* Listar Personas*/
$router->add('GET', '/colaciones/personas/(\d+)',
    function ($id) use ($colacionController) {
        auth_require();
        $colacionController->personas($id);
    }
);






// index.php (router)
$router->add('GET', '/colaciones/lotes/crear-desde-excel', function () use ($colacionController) {
    auth_require();
    $colacionController->crearDesdeExcel();
});




//impresion

$router->add('GET', '/colaciones/voucher/imprimir-registrando', function () use ($voucherController) {
    auth_require();
    $voucherController->imprimirYRegistrar();
});




//Lector QR
$router->add('POST', '/colaciones/qr/validar', function () {
    if (ob_get_length()) {
        ob_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    $input = file_get_contents('php://input');
    if (! $input) {
        echo json_encode(['estado' => 'error', 'mensaje' => 'No se recibió contenido.']);
        return;
    }
    $req = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
                             'estado' => 'error',
                             'mensaje' => 'JSON inválido',
                             'detalle' => json_last_error_msg(),
                             'raw' => $input,
                         ]);
        return;
    }
    $qr = $req['qr'] ?? '';
    if (! $qr) {
        echo json_encode(['estado' => 'error', 'mensaje' => 'QR vacío.']);
        return;
    }
    $parsed = parse_url($qr);
    parse_str($parsed['query'] ?? '', $params);
    $run = $params['RUN'] ?? '';
    $type = $params['type'] ?? '';
    $serial = $params['serial'] ?? '';
    $mrz = $params['mrz'] ?? '';
    $name = isset($params['name']) ? urldecode($params['name']) : '';
    echo json_encode([
                         'estado' => 'ok',
                         'mensaje' => 'QR procesado correctamente.',
                         'datos' => [
                             'rut' => $run,
                             'nombre' => $name,
                             'tipo' => $type,
                             'serial' => $serial,
                             'mrz' => $mrz,
                         ],
                         'qr_original' => $qr,
                     ]);
});
// fin lector QR


// --- Servicios (colacion_adicional)
require_once __DIR__.'/controllers/ServicioController.php';
$servicioController = new ServicioController();

$router->add('GET',  '/servicios/listar', function () use ($servicioController) {
    $servicioController->listar();
});

$router->add('GET',  '/servicios/nuevo', function () use ($servicioController) {
    $servicioController->nuevo();
});

$router->add('POST', '/servicios/guardar', function () use ($servicioController) {
    $servicioController->guardar();
});

$router->add('GET',  '/servicios/editar/([0-9]+)', function ($id) use ($servicioController) {
    $servicioController->editar((int)$id);
});

$router->add('POST', '/servicios/actualizar', function () use ($servicioController) {
    $servicioController->actualizar();
});

$router->add('POST', '/servicios/eliminar', function () use ($servicioController) {
    $servicioController->eliminar();
});
$router->add('POST', '/servicios/activar', function () use ($servicioController) {
    $servicioController->activar();
});




/* --- despachar al final --- */
//$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', current_path());
// --- despachar ---
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', current_path());



