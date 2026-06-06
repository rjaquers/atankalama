<?php
require_once __DIR__ . '/controller/TemperaturaController.php';

$controller = new TemperaturaController();
$route = $_GET['route'] ?? 'form';

ob_start();

switch ($route) {
    case 'guardar':
        $controller->guardar();
        break;
    case 'listar':
        $controller->listar();
        break;
    case 'exportarPDF':
        $controller->exportarPDF();
        break;
    case 'eliminar':
        $controller->eliminar();
        break;
    default:
        $controller->form();
        break;
}

$vistaContent = ob_get_clean();

if (trim($vistaContent) === '') {
    // Diagnóstico: mostramos detalles
    $vistaContent = '<div class="alert alert-danger">
        <strong>Error 2:</strong> No se pudo cargar la vista ccc.<br>
        Ruta: <code>' . htmlspecialchars($route) . '</code><br>
        Archivo esperado: <code>views/temperatura_' . htmlspecialchars($route) . '.php</code><br>
        Verifica rutas y nombres en el servidor.
    </div>';
}

include __DIR__ . '/views/layout.php';
?>
