<?php

class AccesoRolController
{
    public function list()
    {
        $model  = new AccesoRol();
        $appId  = !empty($_GET['app_id']) ? (int)$_GET['app_id'] : null;
        $roles  = $model->listar($appId);
        $apps   = $model->listarApps();
        $appSeleccionada = $appId;
        include __DIR__ . '/../views/acceso/roles_list.php';
    }

    public function create()
    {
        $apps    = (new AccesoRol())->listarApps();
        $appId   = (int)($_GET['app_id'] ?? 0);
        include __DIR__ . '/../views/acceso/roles_form.php';
    }

    public function store()
    {
        $appId       = (int)($_POST['app_id']      ?? 0);
        $nombre      = trim($_POST['nombre']        ?? '');
        $descripcion = trim($_POST['descripcion']   ?? '');

        if (!$appId || !$nombre) {
            $_SESSION['flash_error'] = 'App y nombre son obligatorios.';
            header('Location: index.php?route=acceso/roles/create');
            exit;
        }

        (new AccesoRol())->guardar($appId, $nombre, $descripcion);
        $_SESSION['flash_ok'] = 'Rol creado correctamente.';
        header("Location: index.php?route=acceso/roles/list&app_id=$appId");
        exit;
    }

    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header('Location: index.php?route=acceso/roles/list'); exit; }

        $model = new AccesoRol();
        $rol   = $model->buscar($id);
        if (!$rol) { header('Location: index.php?route=acceso/roles/list'); exit; }

        include __DIR__ . '/../views/acceso/roles_edit.php';
    }

    public function update()
    {
        $id          = (int)($_POST['id']           ?? 0);
        $nombre      = trim($_POST['nombre']         ?? '');
        $descripcion = trim($_POST['descripcion']    ?? '');
        $estado      = $_POST['estado'] ?? 'activo';

        (new AccesoRol())->actualizar($id, $nombre, $descripcion, $estado);
        $_SESSION['flash_ok'] = 'Rol actualizado correctamente.';
        header('Location: index.php?route=acceso/roles/list');
        exit;
    }

    public function eliminar()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            (new AccesoRol())->eliminar($id);
            $_SESSION['flash_ok'] = 'Rol eliminado.';
        }
        header('Location: index.php?route=acceso/roles/list');
        exit;
    }

    public function secciones()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header('Location: index.php?route=acceso/roles/list'); exit; }

        $model    = new AccesoRol();
        $rol      = $model->buscar($id);
        if (!$rol) { header('Location: index.php?route=acceso/roles/list'); exit; }

        $secciones = $model->listarSeccionesConEstado($id, (int)$rol['app_id']);
        include __DIR__ . '/../views/acceso/roles_secciones.php';
    }

    public function guardarSecciones()
    {
        $rolId      = (int)($_POST['rol_id']     ?? 0);
        $seccionIds = $_POST['secciones'] ?? [];

        if (!$rolId) { header('Location: index.php?route=acceso/roles/list'); exit; }

        (new AccesoRol())->sincronizarSecciones($rolId, $seccionIds);
        $_SESSION['flash_ok'] = 'Secciones del rol actualizadas.';
        header("Location: index.php?route=acceso/roles/secciones&id=$rolId");
        exit;
    }
}
