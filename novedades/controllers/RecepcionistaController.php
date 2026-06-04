<?php

class RecepcionistaController
{
    /**
     * Lista todo el personal activo agrupado por área.
     */
    public function list()
    {
        $model          = new Recepcionista();
        $recepcionistas = $model->listar();
        include __DIR__ . '/../views/recepcionistas/list.php';
    }

    /**
     * Muestra el formulario de creación de personal.
     */
    public function create()
    {
        $areas = (new Recepcionista())->listarAreas();
        include __DIR__ . '/../views/recepcionistas/form.php';
    }

    /**
     * Guarda un nuevo miembro del personal.
     */
    public function store()
    {
        $nombre  = $_POST['nombre']  ?? '';
        $fono    = $_POST['fono']    ?? null;
        $correo  = $_POST['correo']  ?? null;
        $areaId  = !empty($_POST['area_id']) ? (int)$_POST['area_id'] : null;

        (new Recepcionista())->guardar($nombre, $fono, $correo, $areaId);

        header('Location: index.php?route=recepcionistas/list');
        exit;
    }

    /**
     * Muestra el formulario de edición de un miembro del personal.
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            die('ID no especificado');
        }

        $model          = new Recepcionista();
        $recepcionista  = $model->buscar((int)$id);
        $areas          = $model->listarAreas();

        include __DIR__ . '/../views/recepcionistas/edit.php';
    }

    /**
     * Actualiza los datos de un miembro del personal.
     */
    public function update()
    {
        $id      = (int)$_POST['id'];
        $nombre  = $_POST['nombre']  ?? '';
        $fono    = $_POST['fono']    ?? '';
        $correo  = $_POST['correo']  ?? null;
        $activo  = isset($_POST['activo']) ? 1 : 0;
        $areaId  = !empty($_POST['area_id']) ? (int)$_POST['area_id'] : null;

        (new Recepcionista())->actualizar($id, $nombre, $fono, $activo, $correo, $areaId);

        header('Location: index.php?route=recepcionistas/list');
        exit;
    }

    /**
     * Desactiva un miembro del personal (baja lógica).
     */
    public function desactivar()
    {
        $id = $_GET['id'] ?? null;
        if ($id) {
            (new Recepcionista())->desactivar((int)$id);
        }
        header('Location: index.php?route=recepcionistas/list');
        exit;
    }
}
