<?php

require_once __DIR__.'/../models/ColacionAdicional.php';

class ServicioController
{
    /**
     * Lista servicios/adicionales.
     *
     * @return void
     */
    public function listar(): void
    {
        $m = new ColacionAdicional();
        $items = $m->listar(true); // SOLO activos

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        include __DIR__.'/../views/servicios/listar.php';
    }
// Fin de la función listar()



    /**
     * Muestra formulario de creación.
     *
     * @return void
     */
    public function nuevo(): void
    {
        $item = ['id' => 0, 'nombre' => ''];
        $modo = 'nuevo';
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        include __DIR__.'/../views/servicios/form.php';
    }
    // Fin de la función nuevo()

    /**
     * Guarda un nuevo servicio/adicional.
     *
     * @return void
     */
    public function guardar(): void
    {
        try {

            $nombre = (string)($_POST['nombre'] ?? '');
            $tipo   = (int)($_POST['tipo'] ?? 2);
            $m = new ColacionAdicional();
            $m->crear($nombre);

            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Servicio creado.'];
            redirect('/servicios/listar');
        } catch (Throwable $e) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
            redirect('/servicios/nuevo');
        }
    }
    // Fin de la función guardar()

    /**
     * Muestra formulario de edición.
     *
     * @param int $id
     * @return void
     */
    public function editar(int $id): void
    {
        $m = new ColacionAdicional();
        $item = $m->obtener($id);

        if (! $item) {
            http_response_code(404);
            exit('Servicio no encontrado.');
        }

        $modo = 'editar';
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        include __DIR__.'/../views/servicios/form.php';
    }
    // Fin de la función editar()

    /**
     * Actualiza un servicio/adicional.
     *
     * @return void
     */
    public function actualizar(): void
    {
        try {

            $id     = (int)($_POST['id'] ?? 0);
            $nombre = (string)($_POST['nombre'] ?? '');
            $tipo   = (int)($_POST['tipo'] ?? 2);


            $m = new ColacionAdicional();
            $m->actualizar($id, $nombre, $tipo);

            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Servicio actualizado.'];
            redirect('/servicios/listar');
        } catch (Throwable $e) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
            redirect('/servicios/listar');
        }
    }
    // Fin de la función actualizar()

    /**
     * Elimina un servicio/adicional (si no está en uso).
     *
     * @return void
     */
    public function eliminar(): void
    {// solo desactiva
        try {
            $id = (int)($_POST['id'] ?? 0);

            $m = new ColacionAdicional();
            $m->desactivar($id);

            $_SESSION['flash'] = [
                'type' => 'success',
                'msg'  => 'Servicio desactivado.'
            ];
            redirect('/servicios/listar');

        } catch (Throwable $e) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg'  => $e->getMessage()
            ];
            redirect('/servicios/listar');
        }
    }
// Fin de la función eliminar()

    public function activar(): void
    {
        try {
            $id = (int)($_POST['id'] ?? 0);

            $m = new ColacionAdicional();
            $m->activar($id);

            $_SESSION['flash'] = [
                'type' => 'success',
                'msg'  => 'Servicio reactivado.'
            ];
            redirect('/servicios/listar');

        } catch (Throwable $e) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'msg'  => $e->getMessage()
            ];
            redirect('/servicios/listar');
        }
    }
// Fin de la función activar()

}
