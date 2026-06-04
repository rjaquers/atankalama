<?php

class AccesoPerfilController
{
    public function list()
    {
        $model = new AccesoPerfil();
        $perfiles = $model->listar();
        include __DIR__ . '/../views/acceso_perfiles_list.php';
    }

    public function create()
    {
        include __DIR__ . '/../views/acceso_perfiles_form.php';
    }

    public function store()
    {
        $nombre = trim($_POST['nombre'] ?? '');

        if (!$nombre) {
            $_SESSION['flash_error'] = 'El nombre es obligatorio.';
            header('Location: index.php?route=acceso/perfiles/create');
            exit;
        }

        $model = new AccesoPerfil();
        if ($model->existeNombre($nombre)) {
            $_SESSION['flash_error'] = 'El nombre del perfil ya existe.';
            header('Location: index.php?route=acceso/perfiles/create');
            exit;
        }

        $model->guardar($nombre);
        $_SESSION['flash_ok'] = 'Perfil creado correctamente.';
        header('Location: index.php?route=acceso/perfiles/list');
        exit;
    }

    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header('Location: index.php?route=acceso/perfiles/list'); exit; }

        $model = new AccesoPerfil();
        $perfil = $model->buscar($id);
        if (!$perfil) { header('Location: index.php?route=acceso/perfiles/list'); exit; }

        include __DIR__ . '/../views/acceso_perfiles_edit.php';
    }

    public function update()
    {
        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');

        if (!$id || !$nombre) {
            $_SESSION['flash_error'] = 'Datos incompletos.';
            header("Location: index.php?route=acceso/perfiles/edit&id=$id");
            exit;
        }

        $model = new AccesoPerfil();
        if ($model->existeNombre($nombre, $id)) {
            $_SESSION['flash_error'] = 'El nombre del perfil ya existe.';
            header("Location: index.php?route=acceso/perfiles/edit&id=$id");
            exit;
        }

        $model->actualizar($id, $nombre);
        $_SESSION['flash_ok'] = 'Perfil actualizado correctamente.';
        header('Location: index.php?route=acceso/perfiles/list');
        exit;
    }

    public function eliminar()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            (new AccesoPerfil())->eliminar($id);
            $_SESSION['flash_ok'] = 'Perfil eliminado.';
        }
        header('Location: index.php?route=acceso/perfiles/list');
        exit;
    }
}
