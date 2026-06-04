<?php
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 * Este software es propiedad exclusiva de su autor.
 * Se concede un derecho de uso limitado al cliente. No se transfiere
 * la propiedad del código ni de la aplicación.
 *
 * @author  Rodrigo Jaque Escobar
 * @project Sistema de Novedades — Hotel Atankalama
 */

require_once __DIR__.'/../config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/shared/AccesoBootstrap.php';

session_start();

spl_autoload_register(function ($class) {
    foreach (['../controllers/', '../models/', '../services/'] as $path) {
        $file = __DIR__.'/'.$path.$class.'.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

$route = $_GET['route'] ?? 'novedades/form';

// -------------------------------------------------------
// Sistema de acceso centralizado
// Rutas completamente públicas (sin login requerido).
// Todo lo demás requiere sesión + permiso en acc_secciones.
// -------------------------------------------------------
AccesoBootstrap::arrancar('novedades', 'nov', $route, [
    'novedades/form',
    'novedades/store',
    'novedades/list',
    'novedades/agregarAdjunto',
    'novedades/export',
    'novedades/seguimiento',
    'novedades/seguimiento/cerrar',
    'novedades/comentario/agregar',
    'dashboard',
    'dashboard/index',
    'version',
], 'Sistema de Novedades');

// Email del usuario autenticado (disponible para controladores/vistas)
$nov_email = AccesoBootstrap::email();

switch($route) {

    // -------------------------------------------------------
    // Dashboard
    // -------------------------------------------------------
    case 'dashboard':
    case 'dashboard/index':
        (new DashboardController())->index();
        break;

    // -------------------------------------------------------
    // Novedades
    // -------------------------------------------------------
    case 'novedades/form':
        (new NovedadController())->form();
        break;
    case 'novedades/store':
        (new NovedadController())->store();
        break;
    case 'novedades/list':
        (new NovedadController())->list();
        break;
    case 'novedades/agregarAdjunto':
        (new NovedadController())->agregarAdjunto();
        break;
    case 'novedades/export':
        (new NovedadController())->export();
        break;
    case 'novedades/seguimiento':
        (new NovedadController())->seguimiento();
        break;
    case 'novedades/comentario/agregar':
        (new NovedadController())->agregarComentario();
        break;
    case 'novedades/seguimiento/cerrar':
        (new NovedadController())->cerrarSeguimiento();
        break;

    // -------------------------------------------------------
    // Personal  [PROTEGIDO vía AccesoBootstrap]
    // -------------------------------------------------------
    case 'recepcionistas/list':
        (new RecepcionistaController())->list();
        break;
    case 'recepcionistas/create':
        (new RecepcionistaController())->create();
        break;
    case 'recepcionistas/store':
        (new RecepcionistaController())->store();
        break;
    case 'recepcionistas/edit':
        (new RecepcionistaController())->edit();
        break;
    case 'recepcionistas/update':
        (new RecepcionistaController())->update();
        break;
    case 'recepcionistas/desactivar':
        (new RecepcionistaController())->desactivar();
        break;

    // -------------------------------------------------------
    // Empresas  [PROTEGIDO vía AccesoBootstrap]
    // -------------------------------------------------------
    case 'empresas/list':
        (new EmpresaController())->list();
        break;
    case 'empresas/create':
        (new EmpresaController())->create();
        break;
    case 'empresas/store':
        (new EmpresaController())->store();
        break;
    case 'empresas/edit':
        (new EmpresaController())->edit();
        break;
    case 'empresas/update':
        (new EmpresaController())->update();
        break;
    case 'empresas/delete':
        (new EmpresaController())->delete();
        break;

    // -------------------------------------------------------
    // Encargados  [PROTEGIDO vía AccesoBootstrap]
    // -------------------------------------------------------
    case 'encargados/list':
        (new EncargadoController())->list();
        break;
    case 'encargados/create':
        (new EncargadoController())->create();
        break;
    case 'encargados/store':
        (new EncargadoController())->store();
        break;
    case 'encargados/edit':
        (new EncargadoController())->edit();
        break;
    case 'encargados/update':
        (new EncargadoController())->update();
        break;
    case 'encargados/delete':
        (new EncargadoController())->delete();
        break;

    // -------------------------------------------------------
    // Control de Acceso — movido a /admin/
    // -------------------------------------------------------
    case 'acceso/usuarios/list':
    case 'acceso/usuarios/create':
    case 'acceso/usuarios/store':
    case 'acceso/usuarios/edit':
    case 'acceso/usuarios/update':
    case 'acceso/usuarios/permisos':
    case 'acceso/usuarios/permisos/save':
    case 'acceso/usuarios/cerrar-sesion':
    case 'acceso/usuarios/reset-validado':
    case 'acceso/usuarios/log':
    case 'acceso/log/archivo':
    case 'acceso/apps/list':
    case 'acceso/apps/create':
    case 'acceso/apps/store':
    case 'acceso/apps/edit':
    case 'acceso/apps/update':
    case 'acceso/roles/list':
    case 'acceso/roles/create':
    case 'acceso/roles/store':
    case 'acceso/roles/edit':
    case 'acceso/roles/update':
    case 'acceso/roles/eliminar':
    case 'acceso/roles/secciones':
    case 'acceso/roles/secciones/save':
    case 'acceso/secciones/list':
    case 'acceso/secciones/create':
    case 'acceso/secciones/store':
    case 'acceso/secciones/edit':
    case 'acceso/secciones/update':
    case 'acceso/secciones/eliminar':
        header('Location: https://www.atankalama.com/admin/public/index.php?' . $_SERVER['QUERY_STRING']);
        exit;

    // -------------------------------------------------------
    // Versiones
    // -------------------------------------------------------
    case 'version':
        (new VersionController())->index();
        break;

    default:
        echo "<h3 style='color:red'>Ruta no encontrada: " . htmlspecialchars($route) . "</h3>";
}
