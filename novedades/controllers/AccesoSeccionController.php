<?php

class AccesoSeccionController
{
    public function list()
    {
        $model    = new AccesoSeccion();
        $appId    = !empty($_GET['app_id']) ? (int)$_GET['app_id'] : null;
        $secciones = $model->listar($appId);
        $apps     = $model->listarApps();
        $appSeleccionada = $appId;
        include __DIR__ . '/../views/acceso/secciones_list.php';
    }

    public function create()
    {
        $apps  = (new AccesoSeccion())->listarApps();
        $appId = (int)($_GET['app_id'] ?? 0);
        include __DIR__ . '/../views/acceso/secciones_form.php';
    }

    public function store()
    {
        $appId  = (int)($_POST['app_id'] ?? 0);
        $slug   = trim($_POST['slug']    ?? '');
        $nombre = trim($_POST['nombre']  ?? '');
        $tipo   = $_POST['tipo'] ?? 'restringida';

        $model = new AccesoSeccion();

        if (!$appId || !$slug || !$nombre) {
            $_SESSION['flash_error'] = 'App, slug y nombre son obligatorios.';
            header('Location: index.php?route=acceso/secciones/create');
            exit;
        }

        if ($model->slugExiste($appId, $slug)) {
            $_SESSION['flash_error'] = "La ruta '$slug' ya existe para esa app.";
            header('Location: index.php?route=acceso/secciones/create');
            exit;
        }

        $model->guardar($appId, $slug, $nombre, $tipo);
        $_SESSION['flash_ok'] = 'Sección registrada correctamente.';
        header("Location: index.php?route=acceso/secciones/list&app_id=$appId");
        exit;
    }

    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header('Location: index.php?route=acceso/secciones/list'); exit; }

        $model   = new AccesoSeccion();
        $seccion = $model->buscar($id);
        if (!$seccion) { header('Location: index.php?route=acceso/secciones/list'); exit; }

        include __DIR__ . '/../views/acceso/secciones_edit.php';
    }

    public function update()
    {
        $id     = (int)($_POST['id']     ?? 0);
        $slug   = trim($_POST['slug']    ?? '');
        $nombre = trim($_POST['nombre']  ?? '');
        $tipo   = $_POST['tipo']   ?? 'restringida';
        $estado = $_POST['estado'] ?? 'activo';

        $model   = new AccesoSeccion();
        $seccion = $model->buscar($id);

        if ($seccion && $model->slugExiste((int)$seccion['app_id'], $slug, $id)) {
            $_SESSION['flash_error'] = "La ruta '$slug' ya existe para esa app.";
            header("Location: index.php?route=acceso/secciones/edit&id=$id");
            exit;
        }

        $model->actualizar($id, $slug, $nombre, $tipo, $estado);
        $_SESSION['flash_ok'] = 'Sección actualizada correctamente.';
        header('Location: index.php?route=acceso/secciones/list');
        exit;
    }

    public function eliminar()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            (new AccesoSeccion())->eliminar($id);
            $_SESSION['flash_ok'] = 'Sección eliminada.';
        }
        header('Location: index.php?route=acceso/secciones/list');
        exit;
    }
}
