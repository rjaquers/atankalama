<?php

class AccesoAppController
{
    public function list()
    {
        $model = new AccesoApp();
        $apps  = $model->listar();
        include __DIR__ . '/../views/acceso/apps_list.php';
    }

    public function create()
    {
        include __DIR__ . '/../views/acceso/apps_form.php';
    }

    public function store()
    {
        $slug        = trim(strtolower(preg_replace('/[^a-z0-9_]/', '', strtolower($_POST['slug'] ?? ''))));
        $nombre      = trim($_POST['nombre']      ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $urlInicio   = trim($_POST['url_inicio']  ?? '');
        $urlAdmin    = trim($_POST['url_admin']   ?? '');

        $model = new AccesoApp();

        if (!$slug || !$nombre) {
            $_SESSION['flash_error'] = 'Slug y nombre son obligatorios.';
            header('Location: index.php?route=acceso/apps/create');
            exit;
        }

        if ($model->slugExiste($slug)) {
            $_SESSION['flash_error'] = "El slug '$slug' ya está en uso.";
            header('Location: index.php?route=acceso/apps/create');
            exit;
        }

        $sessionPrefix = trim($_POST['session_prefix'] ?? '');
        $icono         = trim($_POST['icono']          ?? '');
        $model->guardar($slug, $nombre, $descripcion, $urlInicio, $urlAdmin, $sessionPrefix, $icono);
        $_SESSION['flash_ok'] = 'Aplicación registrada correctamente.';
        header('Location: index.php?route=acceso/apps/list');
        exit;
    }

    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header('Location: index.php?route=acceso/apps/list'); exit; }

        $model = new AccesoApp();
        $app   = $model->buscar($id);
        if (!$app) { header('Location: index.php?route=acceso/apps/list'); exit; }

        include __DIR__ . '/../views/acceso/apps_edit.php';
    }

    public function update()
    {
        $id          = (int)($_POST['id'] ?? 0);
        $slug        = trim(strtolower(preg_replace('/[^a-z0-9_]/', '', strtolower($_POST['slug'] ?? ''))));
        $nombre      = trim($_POST['nombre']      ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $estado      = $_POST['estado'] ?? 'activo';
        $urlInicio   = trim($_POST['url_inicio']  ?? '');
        $urlAdmin    = trim($_POST['url_admin']   ?? '');

        $model = new AccesoApp();

        if (!$slug || !$nombre) {
            $_SESSION['flash_error'] = 'Slug y nombre son obligatorios.';
            header("Location: index.php?route=acceso/apps/edit&id=$id");
            exit;
        }

        if ($model->slugExiste($slug, $id)) {
            $_SESSION['flash_error'] = "El slug '$slug' ya está en uso por otra app.";
            header("Location: index.php?route=acceso/apps/edit&id=$id");
            exit;
        }

        $sessionPrefix = trim($_POST['session_prefix'] ?? '');
        $icono         = trim($_POST['icono']          ?? '');
        $model->actualizar($id, $slug, $nombre, $descripcion, $estado, $urlInicio, $urlAdmin, $sessionPrefix, $icono);
        $_SESSION['flash_ok'] = 'Aplicación actualizada correctamente.';
        header('Location: index.php?route=acceso/apps/list');
        exit;
    }
}
