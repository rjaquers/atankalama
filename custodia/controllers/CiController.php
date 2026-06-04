<?php

/*
-------------------------------------------------------
Resumen:
Controlador que recibe un QR desde JS, valida formato
y envía respuesta estándar JSON.
Variables requeridas: $_POST['qr']
-------------------------------------------------------
*/

class CiController
{
    public function validar()
    {
        header('Content-Type: application/json');

        // Leer JSON entrante
        $json = file_get_contents('php://input');
        $req = json_decode($json, true);

        $qr = $req['qr'] ?? '';

        if (! $qr) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'QR vacío']);

            return; // <-- Aquí termina la función
        }

        // ===============================================
        // Lógica de negocio:
        // Podrías guardar el QR, validar identidad,
        // o asociarlo a un registro de cliente.
        // ===============================================

        echo json_encode([
                             'estado' => 'ok',
                             'mensaje' => 'QR recibido',
                             'qr_original' => $qr
                         ]);
        // <-- Aquí termina la función
    }
}
