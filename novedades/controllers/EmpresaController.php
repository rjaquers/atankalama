<?php

class EmpresaController
{
    /**
     * Lista todas las empresas.
     */
    public function list()
    {
        $model = new Empresa();
        $empresas = $model->listar();
        include __DIR__.'/../views/empresas/list.php';
    }
    // Fin de la función list()

    /**
     * Muestra formulario de creación de empresas.
     */
    public function create()
    {
        include __DIR__.'/../views/empresas/form.php';
    }
    // Fin de la función create()

    /**
     * Guarda una nueva empresa.
     *
     * Qué hace:
     * - Captura $_POST
     * - Llama al modelo para guardar
     * - Redirige al listado
     */
    public function store()
    {
        (new Empresa())->guardar(
            $_POST['rut'] ?? null,
            $_POST['business_name'] ?? '',
            $_POST['trade_name'] ?? null,
            $_POST['contact_name'] ?? null,
            $_POST['contact_email'] ?? null,
            $_POST['contact_phone'] ?? null,
            $_POST['address'] ?? null,
            $_POST['city'] ?? null,
            $_POST['type'] ?? 'cliente',
            $_POST['notes'] ?? null
        );
        header('Location: index.php?route=empresas/list');
        exit;
    }
    // Fin de la función store()

    /**
     * Muestra el formulario de edición de una empresa.
     *
     * @param int $_GET['id'] ID de la empresa
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            die('ID no válido');
        }
        $empresa = (new Empresa())->buscar($id);
        include __DIR__.'/../views/empresas/edit.php';
    }
    // Fin de la función edit()

    /**
     * Actualiza los datos de una empresa.
     *
     * Qué hace:
     * - Captura $_POST
     * - Llama al modelo para actualizar
     * - Redirige al listado
     */
    public function update()
    {
        (new Empresa())->actualizar(
            $_POST['id'],
            $_POST['rut'] ?? null,
            $_POST['business_name'] ?? '',
            $_POST['trade_name'] ?? null,
            $_POST['contact_name'] ?? null,
            $_POST['contact_email'] ?? null,
            $_POST['contact_phone'] ?? null,
            $_POST['address'] ?? null,
            $_POST['city'] ?? null,
            $_POST['type'] ?? 'cliente',
            $_POST['notes'] ?? null,
            isset($_POST['active']) ? 1 : 0
        );
        header('Location: index.php?route=empresas/list');
        exit;
    }
    // Fin de la función update()

    /**
     * Elimina físicamente una empresa.
     *
     * @param int $_GET['id'] ID de la empresa
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if ($id) {
            (new Empresa())->eliminar($id);
        }
        header('Location: index.php?route=empresas/list');
        exit;
    }
    // Fin de la función delete()
}
