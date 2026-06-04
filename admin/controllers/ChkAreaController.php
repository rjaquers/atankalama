<?php

class ChkAreaController
{
    public function list()
    {
        $model = new ChkArea();
        $areas = $model->listar();
        include __DIR__ . '/../views/chk_areas_list.php';
    }

    public function create()
    {
        include __DIR__ . '/../views/chk_areas_form.php';
    }

    public function store()
    {
        $nombre      = trim($_POST['nombre']      ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (!$nombre) {
            $_SESSION['flash_error'] = 'El nombre es obligatorio.';
            header('Location: index.php?route=chk/areas/create');
            exit;
        }

        $model = new ChkArea();

        if ($model->nombreExiste($nombre)) {
            $_SESSION['flash_error'] = "El nombre '$nombre' ya está en uso.";
            header('Location: index.php?route=chk/areas/create');
            exit;
        }

        $model->guardar($nombre, $descripcion);
        $_SESSION['flash_ok'] = 'Área registrada correctamente.';
        header('Location: index.php?route=chk/areas/list');
        exit;
    }

    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header('Location: index.php?route=chk/areas/list'); exit; }

        $model = new ChkArea();
        $area  = $model->buscar($id);
        if (!$area) { header('Location: index.php?route=chk/areas/list'); exit; }

        include __DIR__ . '/../views/chk_areas_edit.php';
    }

    public function update()
    {
        $id          = (int)($_POST['id']          ?? 0);
        $nombre      = trim($_POST['nombre']        ?? '');
        $descripcion = trim($_POST['descripcion']   ?? '');
        $estado      = $_POST['estado'] ?? 'activo';

        if (!$id || !$nombre) {
            $_SESSION['flash_error'] = 'El nombre es obligatorio.';
            header("Location: index.php?route=chk/areas/edit&id=$id");
            exit;
        }

        $model = new ChkArea();

        if ($model->nombreExiste($nombre, $id)) {
            $_SESSION['flash_error'] = "El nombre '$nombre' ya está en uso por otra área.";
            header("Location: index.php?route=chk/areas/edit&id=$id");
            exit;
        }

        $model->actualizar($id, $nombre, $descripcion, $estado);
        $_SESSION['flash_ok'] = 'Área actualizada correctamente.';
        header('Location: index.php?route=chk/areas/list');
        exit;
    }

    public function eliminar()
    {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { header('Location: index.php?route=chk/areas/list'); exit; }

        $model = new ChkArea();
        $model->eliminar($id);
        $_SESSION['flash_ok'] = 'Área eliminada correctamente.';
        header('Location: index.php?route=chk/areas/list');
        exit;
    }
}
