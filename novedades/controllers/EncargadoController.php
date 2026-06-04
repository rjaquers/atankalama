<?php

class EncargadoController
{
    /**
     * Lista los encargados, permitiendo filtrar por empresa.
     *
     * @param int|null $_GET['empresa_id'] Filtro opcional por empresa
     */
    public function list()
    {
        $model = new Encargado();

        $empresaId = null;

        if (isset($_GET['empresa_id'])) {
            $empresaId = filter_input(INPUT_GET, 'empresa_id', FILTER_VALIDATE_INT);
        }

        if ($empresaId) {
            $encargados = $model->listarPorEmpresa($empresaId);

            // Opcional: traer nombre empresa para mostrar contexto
            $empresaModel = new Empresa();
            $empresa = $empresaModel->buscar($empresaId);
        } else {
            $encargados = $model->listar();
            $empresa = null;
        }

        include __DIR__.'/../views/encargados/list.php';
    }
    // Fin de la función list()

    /**
     * Muestra formulario de creación de encargados.
     */
    public function create()
    {
        $empresas = (new Empresa())->listar();
        include __DIR__.'/../views/encargados/form.php';
    }
    // Fin de la función create()

    /**
     * Guarda un nuevo encargado.
     *
     * Qué hace:
     * - Captura $_POST (empresa_id, nombre, fono, correo, fechas)
     * - Llama al modelo para guardar
     * - Redirige al listado
     */
    public function store()
    {
        (new Encargado())->guardar(
            $_POST['empresa_id'],
            $_POST['nombre'],
            $_POST['telefono'],
            $_POST['correo'],
            $_POST['periodo_desde'],
            $_POST['periodo_hasta']
        );
        header('Location: index.php?route=encargados/list');
        exit;
    }
    // Fin de la función store()

    /**
     * Muestra el formulario de edición para un encargado.
     *
     * @param int $_GET['id'] ID del encargado
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            die('ID no válido');
        }
        $encargado = (new Encargado())->buscar($id);
        $empresas = (new Empresa())->listar();
        include __DIR__.'/../views/encargados/edit.php';
    }
    // Fin de la función edit()

    /**
     * Actualiza los datos de un encargado.
     *
     * Qué hace:
     * - Captura $_POST (id, empresa_id, nombre, fono, correo, fechas, activo)
     * - Llama al modelo para actualizar
     * - Redirige al listado
     */
    public function update()
    {
        (new Encargado())->actualizar(
            $_POST['id'],
            $_POST['empresa_id'],
            $_POST['nombre'],
            $_POST['telefono'],
            $_POST['correo'],
            $_POST['periodo_desde'],
            $_POST['periodo_hasta'],
            isset($_POST['activo']) ? 1 : 0
        );
        header('Location: index.php?route=encargados/list');
        exit;
    }
    // Fin de la función update()

    /**
     * Elimina físicamente un encargado.
     *
     * @param int $_GET['id'] ID del encargado
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if ($id) {
            (new Encargado())->eliminar((int)$id);
        }
        header('Location: index.php?route=encargados/list');
        exit;
    }
    // Fin de la función delete()
}
