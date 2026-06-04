<?php
declare(strict_types=1);
require_once __DIR__ . '/../models/PersonaModel.php';
require_once __DIR__ . '/../models/ImpresionesModel.php';

class PersonaController
{
    private $model;

    public function __construct()
    {
        global $db; // mysqli desde conec6.php
        $this->model = new PersonaModel($db);
    }

    public function guardar()
    {
        $id      = (int)($_POST['id'] ?? 0);
        $lote_id = (int)($_POST['lote_id'] ?? 0);

        $datos = [
            'guest_rut'        => trim($_POST['guest_rut']),
            'guest_nombre'     => trim($_POST['guest_nombre']),
            'guest_habitacion' => isset($_POST['guest_habitacion']) && $_POST['guest_habitacion'] !== ''
                ? trim($_POST['guest_habitacion'])
                : null,
        ];

        if ($id > 0) {
            // EDITAR
            $ok = $this->model->actualizar($id, $datos);
        } else {
            // CREAR
            $datos['lote_id'] = $lote_id;
            $ok = $this->model->crear($datos);
        }

        echo json_encode(['ok' => $ok ? 1 : 0]);
    }

    public function eliminar()
    {
        $id = (int)($_POST['id'] ?? 0);

        $ok = false;
        if ($id > 0) {
            $ok = $this->model->eliminar($id);
        }

        echo json_encode(['ok' => $ok ? 1 : 0]);
    }


    public function registrarImpresion()
    {
        $rut = $_POST['rut'] ?? '';
        $servicio_id = (int)($_POST['servicio_id'] ?? 0);

        if ($rut && $servicio_id) {
            require_once __DIR__ . '/../models/ImpresionesModel.php';
            $model = new ImpresionesModel($GLOBALS['db']);
            $model->registrar($rut, $servicio_id);
        }

        echo json_encode(['ok' => 1]);
    }

}
