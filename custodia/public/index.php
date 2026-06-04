<?php
declare(strict_types=1);

use App\Core\Router;

// Punto de entrada nuevo basado en Router PSR-4 y config centralizada.

// 1) Ajustar BASE_URL explícitamente (tu app cuelga de /custodia)
define('BASE_URL', '/custodia');

// 2) Autoload de Composer (App\*)
require __DIR__ . '/../vendor/autoload.php';

// 3) Config general (helpers, seguridad, etc.)
require __DIR__ . '/../connections/config.php';

// 4) Conexión a BD (define db(), $mysqli, $db, etc.)
require __DIR__ . '/../connections/conec6.php';

// 5) Controladores actuales (sin namespaces, por compatibilidad)
require __DIR__ . '/../controllers/ColacionController.php';
require __DIR__ . '/../controllers/TicketController.php';
require __DIR__ . '/../controllers/EmpresaController.php';
require __DIR__ . '/../controllers/ExcelColacionController.php';
require __DIR__ . '/../controllers/PersonaController.php';
require __DIR__ . '/../controllers/ColacionVoucherController.php';
require __DIR__ . '/../controllers/ServicioController.php';

// Instancias
$colacionController  = new ColacionController();
$ticketController    = new TicketController();
$empresaController   = new EmpresaController();
$excelCtl            = new ExcelColacionController();
$personaController   = new PersonaController();
$voucherController   = new ColacionVoucherController();
$servicioController  = new ServicioController();

// 6) Router nuevo
$router = new Router(BASE_URL);

// ===================== RUTAS =====================

// HOME → lista de colaciones
$router->get('/', function () use ($router) {
    $router->redirect('/colaciones/lotes');
});

// --- Colaciones (lotes)
$router->get('/colaciones/lotes', function () use ($colacionController) {
    $colacionController->index();
});
$router->get('/colaciones/lotes/crear', function () use ($colacionController) {
    $colacionController->crear();
});
$router->post('/colaciones/lotes/guardar', function () use ($colacionController) {
    $colacionController->guardar();
});
$router->get('/colaciones/lotes/imprimir/([0-9]+)', function ($id) use ($colacionController) {
    $colacionController->imprimir((int)$id);
});
$router->get('/colaciones/lotes/reimprimir/([0-9]+)', function ($id) use ($colacionController) {
    $colacionController->reimprimir((int)$id);
});
$router->get('/colaciones/lotes/ver/([0-9]+)', function ($id) use ($colacionController) {
    if (method_exists($colacionController, 'ver')) {
        $colacionController->ver((int)$id);
    } else {
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Detalle pendiente de implementar. ID lote: ' . (int)$id;
    }
});

// --- Tickets Custodia
$router->get('/tickets/custodia/crear', function () use ($ticketController) {
    $ticketController->crear();
});
$router->get('/tickets/custodia/listar', function () use ($ticketController) {
    $ticketController->listarVista();
});
$router->get('/tickets/custodia/imprimir/([0-9]+)', function ($id) use ($ticketController) {
    $ticketController->imprimirVista((int)$id);
});

// Compatibilidad rutas antiguas
$router->get('/views/tickets/form.php', function () use ($router) {
    $router->redirect('/tickets/custodia/crear');
});
$router->get('/views/tickets/listar.php', function () use ($router) {
    $router->redirect('/tickets/custodia/listar');
});
$router->get('/views/tickets/imprimir.php', function () use ($router) {
    if (isset($_GET['id'])) {
        $router->redirect('/tickets/custodia/imprimir/' . (int)$_GET['id']);
        return;
    }
    http_response_code(400);
    echo 'Falta parámetro id';
});
$router->get('/tickets/custodia/form.php', function () use ($router) {
    $router->redirect('/tickets/custodia/crear');
});

// (si usas “nuevo/guardar”)
$router->get('/tickets/custodia/nuevo', function () use ($ticketController) {
    $ticketController->nuevo();
});
$router->post('/tickets/custodia/guardar', function () use ($ticketController) {
    $ticketController->guardar();
});

// Personas
$router->post('/colaciones/persona/guardar', function () use ($personaController) {
    $personaController->guardar();
});
$router->post('/colaciones/persona/eliminar', function () use ($personaController) {
    $personaController->eliminar();
});
$router->post('/colaciones/lotes/actualizar-fechas', function () use ($colacionController) {
    $colacionController->actualizarFechas();
});

// 2026 Creación del voucher - Editar Lote
$router->get('/colaciones/lotes/form/([0-9]+)', function ($id) use ($colacionController) {
    if (method_exists($colacionController, 'form')) {
        $colacionController->form((int)$id);
    } else {
        $colacionController->crear((int)$id);
    }
});

// Voucher individual
$router->get('/colaciones/voucher/imprimir', function () {
    require_once __DIR__ . '/../controllers/ColacionVoucherController.php';
    $ctl = new ColacionVoucherController();
    $ctl->imprimirIndividual();
});

// --- Empresas
$router->get('/empresas/nuevo', function () use ($empresaController) {
    $empresaController->nuevo();
});
$router->post('/empresas/guardar', function () use ($empresaController) {
    $empresaController->guardar();
});
$router->get('/empresas/listar', function () use ($empresaController) {
    $empresaController->listar();
});
$router->get('/empresas/editar/([0-9]+)', function ($id) use ($empresaController) {
    $empresaController->editar((int)$id);
});
$router->post('/empresas/actualizar', function () use ($empresaController) {
    $empresaController->actualizar();
});

// --- WiFi
$router->get('/wifi/imprimir', function () use ($empresaController) {
    $empresaController->wifiimprimir();
});

// QR scan
$router->get('/colaciones/vouchers/scan', function () use ($colacionController) {
    $colacionController->scanVoucher();
});

// Buscar servicio por RUT
$router->get('/colaciones/buscar', function () use ($colacionController) {
    $colacionController->buscar();
});
$router->post('/colaciones/buscar', function () use ($colacionController) {
    $colacionController->buscar();
});

// Excel (importar/preview)
$router->get('/colaciones/excel/import', function () {
    include __DIR__ . '/../views/colaciones/excel_import_form.php';
});
$router->post('/colaciones/excel/import', function () {
    include __DIR__ . '/../views/colaciones/excel_import_form.php';
});
$router->get('/colaciones/excel/preview', function () {
    include __DIR__ . '/../views/colaciones/excel_preview.php';
});
$router->post('/colaciones/excel/preview', function () {
    include __DIR__ . '/../views/colaciones/excel_preview.php';
});

// Listar Personas
$router->get('/colaciones/personas/(\d+)', function ($id) use ($colacionController) {
    $colacionController->personas($id);
});

// Crear lote desde Excel
$router->get('/colaciones/lotes/crear-desde-excel', function () use ($colacionController) {
    $colacionController->crearDesdeExcel();
});

// Impresión: voucher imprimir-registrando
$router->get('/colaciones/voucher/imprimir-registrando', function () use ($voucherController) {
    $voucherController->imprimirYRegistrar();
});

// Lector QR (JSON)
$router->post('/colaciones/qr/validar', function () {
    if (ob_get_length()) {
        ob_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    $input = file_get_contents('php://input');
    if (!$input) {
        echo json_encode(['estado' => 'error', 'mensaje' => 'No se recibió contenido.']);
        return;
    }
    $req = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'estado'  => 'error',
            'mensaje' => 'JSON inválido',
            'detalle' => json_last_error_msg(),
            'raw'     => $input,
        ]);
        return;
    }
    $qr = $req['qr'] ?? '';
    if (!$qr) {
        echo json_encode(['estado' => 'error', 'mensaje' => 'QR vacío.']);
        return;
    }
    $parsed = parse_url($qr);
    parse_str($parsed['query'] ?? '', $params);
    $run    = $params['RUN'] ?? '';
    $type   = $params['type'] ?? '';
    $serial = $params['serial'] ?? '';
    $mrz    = $params['mrz'] ?? '';
    $name   = isset($params['name']) ? urldecode($params['name']) : '';
    echo json_encode([
        'estado' => 'ok',
        'mensaje' => 'QR procesado correctamente.',
        'datos' => [
            'rut'    => $run,
            'nombre' => $name,
            'tipo'   => $type,
            'serial' => $serial,
            'mrz'    => $mrz,
        ],
        'qr_original' => $qr,
    ]);
});

// --- Servicios (colacion_adicional)
$router->get('/servicios/listar', function () use ($servicioController) {
    $servicioController->listar();
});
$router->get('/servicios/nuevo', function () use ($servicioController) {
    $servicioController->nuevo();
});
$router->post('/servicios/guardar', function () use ($servicioController) {
    $servicioController->guardar();
});
$router->get('/servicios/editar/([0-9]+)', function ($id) use ($servicioController) {
    $servicioController->editar((int)$id);
});
$router->post('/servicios/actualizar', function () use ($servicioController) {
    $servicioController->actualizar();
});
$router->post('/servicios/eliminar', function () use ($servicioController) {
    $servicioController->eliminar();
});
$router->post('/servicios/activar', function () use ($servicioController) {
    $servicioController->activar();
});

// Despachar
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');

