<?php
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 * Este software es propiedad exclusiva de su autor.
 * Se concede un derecho de uso limitado al cliente. No se transfiere
 * la propiedad del código ni de la aplicación.
 *
 * @author  Rodrigo Jaque Escobar
 * @project Sistema de Administración — Hotel Atankalama
 */

require_once __DIR__.'/../config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/shared/AccesoBootstrap.php';

session_start();

spl_autoload_register(function ($class) {
    foreach (['../controllers/', '../models/'] as $path) {
        $file = __DIR__.'/'.$path.$class.'.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

$route = $_GET['route'] ?? 'acceso/usuarios/list';

AccesoBootstrap::arrancar('admin', 'adm', $route, ['version'], 'Administración');

$adm_email = AccesoBootstrap::email();

switch($route) {

    // -------------------------------------------------------
    // Usuarios
    // -------------------------------------------------------
    case 'acceso/usuarios/list':
        (new AccesoUsuarioController())->list();
        break;
    case 'acceso/usuarios/create':
        (new AccesoUsuarioController())->create();
        break;
    case 'acceso/usuarios/store':
        (new AccesoUsuarioController())->store();
        break;
    case 'acceso/usuarios/edit':
        (new AccesoUsuarioController())->edit();
        break;
    case 'acceso/usuarios/update':
        (new AccesoUsuarioController())->update();
        break;
    case 'acceso/usuarios/permisos':
        (new AccesoUsuarioController())->permisos();
        break;
    case 'acceso/usuarios/permisos/save':
        (new AccesoUsuarioController())->guardarPermisos();
        break;
    case 'acceso/usuarios/cerrar-sesion':
        (new AccesoUsuarioController())->cerrarSesion();
        break;
    case 'acceso/usuarios/reset-validado':
        (new AccesoUsuarioController())->resetValidado();
        break;
    case 'acceso/usuarios/log':
        (new AccesoUsuarioController())->log();
        break;
    case 'acceso/usuarios/ver-como':
        (new AccesoUsuarioController())->verComo();
        break;
    case 'acceso/usuarios/salir-ver-como':
        (new AccesoUsuarioController())->salirVerComo();
        break;
    case 'acceso/usuarios/toggle-novedades':
        (new AccesoUsuarioController())->toggleNovedades();
        break;
    case 'acceso/log/archivo':
        (new AccesoUsuarioController())->logArchivo();
        break;

    // -------------------------------------------------------
    // Aplicaciones
    // -------------------------------------------------------
    case 'acceso/apps/list':
        (new AccesoAppController())->list();
        break;
    case 'acceso/apps/create':
        (new AccesoAppController())->create();
        break;
    case 'acceso/apps/store':
        (new AccesoAppController())->store();
        break;
    case 'acceso/apps/edit':
        (new AccesoAppController())->edit();
        break;
    case 'acceso/apps/update':
        (new AccesoAppController())->update();
        break;
    case 'acceso/apps/usuarios':
        (new AccesoAppController())->usuarios();
        break;
    case 'acceso/apps/usuarios/quitar':
        (new AccesoAppController())->quitarUsuarios();
        break;

    // -------------------------------------------------------
    // Roles
    // -------------------------------------------------------
    case 'acceso/roles/list':
        (new AccesoRolController())->list();
        break;
    case 'acceso/roles/create':
        (new AccesoRolController())->create();
        break;
    case 'acceso/roles/store':
        (new AccesoRolController())->store();
        break;
    case 'acceso/roles/edit':
        (new AccesoRolController())->edit();
        break;
    case 'acceso/roles/update':
        (new AccesoRolController())->update();
        break;
    case 'acceso/roles/eliminar':
        (new AccesoRolController())->eliminar();
        break;
    case 'acceso/roles/secciones':
        (new AccesoRolController())->secciones();
        break;
    case 'acceso/roles/secciones/save':
        (new AccesoRolController())->guardarSecciones();
        break;

    // -------------------------------------------------------
    // Secciones
    // -------------------------------------------------------
    case 'acceso/secciones/list':
        (new AccesoSeccionController())->list();
        break;
    case 'acceso/secciones/create':
        (new AccesoSeccionController())->create();
        break;
    case 'acceso/secciones/store':
        (new AccesoSeccionController())->store();
        break;
    case 'acceso/secciones/edit':
        (new AccesoSeccionController())->edit();
        break;
    case 'acceso/secciones/update':
        (new AccesoSeccionController())->update();
        break;
    case 'acceso/secciones/eliminar':
        (new AccesoSeccionController())->eliminar();
        break;

    // -------------------------------------------------------
    // Perfiles
    // -------------------------------------------------------
    case 'acceso/perfiles/list':
        (new AccesoPerfilController())->list();
        break;
    case 'acceso/perfiles/create':
        (new AccesoPerfilController())->create();
        break;
    case 'acceso/perfiles/store':
        (new AccesoPerfilController())->store();
        break;
    case 'acceso/perfiles/edit':
        (new AccesoPerfilController())->edit();
        break;
    case 'acceso/perfiles/update':
        (new AccesoPerfilController())->update();
        break;
    case 'acceso/perfiles/eliminar':
        (new AccesoPerfilController())->eliminar();
        break;

    // -------------------------------------------------------
    // Usuarios de Empresas (acceso portal empresas)
    // -------------------------------------------------------
    case 'emp/usuarios/list':
        (new EmpUsuarioController())->list();
        break;
    case 'emp/usuarios/create':
        (new EmpUsuarioController())->create();
        break;
    case 'emp/usuarios/store':
        (new EmpUsuarioController())->store();
        break;
    case 'emp/usuarios/edit':
        (new EmpUsuarioController())->edit();
        break;
    case 'emp/usuarios/update':
        (new EmpUsuarioController())->update();
        break;
    case 'emp/usuarios/delete':
        (new EmpUsuarioController())->delete();
        break;
    case 'emp/usuarios/reset-password':
        (new EmpUsuarioController())->resetPassword();
        break;

    // -------------------------------------------------------
    // Proyectos de Empresas
    // -------------------------------------------------------
    case 'doc_companies/proyectos':
        (new DocProjectController())->list();
        break;
    case 'doc_companies/proyectos/create':
        (new DocProjectController())->create();
        break;
    case 'doc_companies/proyectos/store':
        (new DocProjectController())->store();
        break;
    case 'doc_companies/proyectos/edit':
        (new DocProjectController())->edit();
        break;
    case 'doc_companies/proyectos/update':
        (new DocProjectController())->update();
        break;
    case 'doc_companies/proyectos/delete':
        (new DocProjectController())->delete();
        break;

    // -------------------------------------------------------
    // Empresas
    // -------------------------------------------------------
    case 'doc_companies/list':
        (new DocCompanyController())->list();
        break;
    case 'doc_companies/create':
        (new DocCompanyController())->create();
        break;
    case 'doc_companies/store':
        (new DocCompanyController())->store();
        break;
    case 'doc_companies/edit':
        (new DocCompanyController())->edit();
        break;
    case 'doc_companies/update':
        (new DocCompanyController())->update();
        break;
    case 'doc_companies/delete':
        (new DocCompanyController())->delete();
        break;

    // -------------------------------------------------------
    // Áreas
    // -------------------------------------------------------
    case 'chk/areas/list':
        (new ChkAreaController())->list();
        break;
    case 'chk/areas/create':
        (new ChkAreaController())->create();
        break;
    case 'chk/areas/store':
        (new ChkAreaController())->store();
        break;
    case 'chk/areas/edit':
        (new ChkAreaController())->edit();
        break;
    case 'chk/areas/update':
        (new ChkAreaController())->update();
        break;
    case 'chk/areas/eliminar':
        (new ChkAreaController())->eliminar();
        break;

    // -------------------------------------------------------
    // Versiones
    // -------------------------------------------------------
    case 'version':
        include __DIR__ . '/../views/version.php';
        break;

    default:
        header('Location: index.php?route=acceso/usuarios/list');
        exit;
}
