<?php
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 * Este software es propiedad exclusiva de su autor.
 * Se concede un derecho de uso limitado al cliente. No se transfiere
 * la propiedad del código ni de la aplicación.
 *
 * @author  Rodrigo Jaque Escobar
 * @project Sistema de Precios - Hotel Atankalama
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/shared/AccesoBootstrap.php';

// Garantizar que ?page= siempre esté en la URL para que AccesoBootstrap
// detecte correctamente el parámetro del router (usa isset($_GET['page'])).
// Sin esto, genera loginUrl con ?route=auth/login → loop infinito.
if (!isset($_GET['page'])) {
    header('Location: index.php?page=precios/lista');
    exit;
}

$page = $_GET['page'];

// Todas las rutas requieren autenticación (sin rutas públicas)
AccesoBootstrap::arrancar('precios', 'pre', $page, [], 'Sistema de Precios');

$email = AccesoBootstrap::email();

require_once __DIR__ . '/../models/PrecioModel.php';
$modelo = new PrecioModel();

// ── Router ───────────────────────────────────────────────────────────────────
$partes  = explode('/', $page, 2);
$seccion = $partes[0] ?? 'precios';
$accion  = $partes[1] ?? 'lista';

switch ("{$seccion}/{$accion}") {

    // ── Grilla de precios ────────────────────────────────────────────────────
    case 'precios/lista':
        require __DIR__ . '/../views/admin/precios/lista.php';
        break;

    case 'precios/guardar':
        // AJAX: POST JSON → actualiza precio → devuelve JSON
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
            exit;
        }
        $body    = json_decode(file_get_contents('php://input'), true);
        $tipoId  = (int)($body['tipo_id']      ?? 0);
        $catId   = (int)($body['categoria_id'] ?? 0);
        $precio  = trim($body['precio']        ?? '');
        if (!$tipoId || !$catId || $precio === '') {
            echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
            exit;
        }
        try {
            $modelo->actualizarPrecio($tipoId, $catId, $precio);
            echo json_encode(['ok' => true, 'precio' => $precio]);
        } catch (Throwable $e) {
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
        exit;

    // ── Categorías ───────────────────────────────────────────────────────────
    case 'categorias/lista':
        require __DIR__ . '/../views/admin/categorias/lista.php';
        break;

    case 'categorias/guardar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id     = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $nombre = trim($_POST['nombre'] ?? '');
            $orden  = (int)($_POST['orden']  ?? 0);
            if ($nombre !== '') {
                $modelo->guardarCategoria($nombre, $orden, $id);
                $mensajeExito = $id ? 'Categoría actualizada.' : 'Categoría creada.';
            } else {
                $mensajeError = 'El nombre no puede estar vacío.';
            }
        }
        require __DIR__ . '/../views/admin/categorias/lista.php';
        break;

    case 'categorias/toggle':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id     = (int)($_POST['id']     ?? 0);
            $activo = (int)($_POST['activo'] ?? 0);
            if ($id) {
                $modelo->toggleCategoria($id, $activo);
            }
        }
        header('Location: index.php?page=categorias/lista');
        exit;

    // ── Tipos de habitación ──────────────────────────────────────────────────
    case 'tipos/lista':
        require __DIR__ . '/../views/admin/tipos/lista.php';
        break;

    case 'tipos/guardar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id     = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $nombre = trim($_POST['nombre'] ?? '');
            $orden  = (int)($_POST['orden']  ?? 0);
            if ($nombre !== '') {
                $modelo->guardarTipo($nombre, $orden, $id);
                $mensajeExito = $id ? 'Tipo actualizado.' : 'Tipo creado.';
            } else {
                $mensajeError = 'El nombre no puede estar vacío.';
            }
        }
        require __DIR__ . '/../views/admin/tipos/lista.php';
        break;

    case 'tipos/toggle':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id     = (int)($_POST['id']     ?? 0);
            $activo = (int)($_POST['activo'] ?? 0);
            if ($id) {
                $modelo->toggleTipo($id, $activo);
            }
        }
        header('Location: index.php?page=tipos/lista');
        exit;

    default:
        http_response_code(404);
        $contenido    = '<div class="alert alert-warning">Página no encontrada.</div>';
        $tituloPagina = '404';
        require __DIR__ . '/../views/admin/layout.php';
}
