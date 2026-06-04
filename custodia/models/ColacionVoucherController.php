<?php

require_once __DIR__.'/../models/ImpresionesModel.php';

class ColacionVoucherController
{
    private ImpresionesModel $impresiones;

    public function __construct()
    {
        global $db;
        $this->impresiones = new ImpresionesModel($db);
    }

    /**
     * Registrar impresión desde AJAX (POST)
     */
    public function registrarImpresion()
    {
        $rut = trim($_POST['rut'] ?? '');
        $servicio_id = (int)($_POST['servicio_id'] ?? 0);

        if ($rut === '' || $servicio_id === 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos inválidos']);

            return;
        }

        // ¿Ya está impreso?
        $yaImpresa = $this->impresiones->yaImpresoHoy($rut, $servicio_id);

        // Registrar (si ya existe hoy, marcar como copia)
        $this->impresiones->registrar($rut, $servicio_id, $yaImpresa);

        echo json_encode([
                             'ok' => 1,
                             'mensaje' => $yaImpresa ? 'Copia registrada' : 'Impresión registrada'
                         ]);
    }
}
