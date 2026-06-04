<?php


class ColacionVoucherController
{
    private function db(): mysqli {
        global $mysqli, $db;
        if ($db instanceof mysqli) return $db;
        if ($mysqli instanceof mysqli) return $mysqli;
        throw new RuntimeException('Sin conexión MySQLi disponible');
    }


    public function imprimirIndividual()
    {
        $db = $this->db();
        $codigo = $_GET['codigo'] ?? null;

        if (! $codigo) {
            die('Código no proporcionado');
        }
        require_once __DIR__.'/../models/ColacionVoucher.php';
        $vM = new ColacionVoucher($db);
        require_once __DIR__.'/../models/ColacionLote.php';
        $loteM = new ColacionLote($db);


        $voucher = $vM->obtenerPorCodigoPublico($codigo);

        if (! $voucher) {
            die('Voucher no encontrado.');
        }

        // Obtener datos del lote asociado
        $lote = $loteM->obtenerPorId($voucher['lote_id']);


        $empresa = $lote['empresa'] ?? '';
        $servicio = $lote['servicio'] ?? '';
        $fechaDMY = date('d/m/Y', strtotime($lote['fecha_servicio']));

        include __DIR__.'/../views/colaciones/print_voucher_80mm.php';
    }

    public function imprimirYRegistrar()
    {
        $rut         = trim($_GET['r'] ?? '');
        $servicio_id = (int)($_GET['s'] ?? 0);
        $url         = $_GET['url'] ?? '';

        if (empty($rut) || $servicio_id === 0 || empty($url)) {
            http_response_code(400);
            exit('Error: Datos incompletos.');
        }

        // 2.2 — Evitar open redirect: solo se permiten rutas internas de vouchers
        $urlDecoded = urldecode($url);
        if (!str_starts_with($urlDecoded, '/custodia/colaciones/voucher/imprimir?codigo=')) {
            http_response_code(400);
            exit('Error: URL de destino no permitida.');
        }

        $db = $this->db();

        // 2.3 — Validar que el servicio_id exista en BD
        $stmt = $db->prepare('SELECT 1 FROM colacion_servicio_tipo WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $servicio_id);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            http_response_code(400);
            exit('Error: Servicio no válido.');
        }
        $stmt->close();

        // Cargar modelo
        require_once __DIR__ . '/../models/ImpresionesModel.php';
        $impModel = new ImpresionesModel($db);

        // Registrar impresión
        $impModel->registrarImpresion($rut, $servicio_id);

        // Redirigir al voucher real (lo imprime y se cierra)
        header('Location: ' . $urlDecoded);
        exit;
    }

}
