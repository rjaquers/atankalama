<?php
require_once __DIR__ . '/../models/CocinaModel.php';
require_once __DIR__ . '/../models/ComandaModel.php';
require_once __DIR__ . '/../models/DesayunoMasivoModel.php';
require_once __DIR__ . '/../libraries/Mailer.php';

class CocinaController
{


    public function index()
    {
        $model = new CocinaModel();
        $data = $model->obtenerOrdenesPendientes();

        $ordenes = $data['ordenes'];
        $cantidad = $data['cantidad'];

        // Obtener todos los detalles asociados
        $ids = array_column($ordenes, 'id');
        $detallesAgrupados = [];
        if (!empty($ids)) {
            $detallesPlano = $model->obtenerDetallesPorOrdenes($ids);
            // Agrupar los detalles por orden_id
            foreach ($detallesPlano as $d) {
                $detallesAgrupados[$d['orden_id']][] = $d;
            }
        }

        // Cargar datos de comandas
        [
            $hoy, $manana,
            $almuerzos, $cenas, $colaciones, $especiales, $desayunosManana,
            $resumenAlmuerzos, $resumenCenas, $resumenColaciones, $resumenEspeciales, $resumenDesayunos,
            $totalAlmuerzos, $totalCenas, $totalColaciones, $totalEspeciales, $totalDesayunos,
            $masivoAtan, $masivoInn, $totalMasivoAtan, $totalMasivoInn,
        ] = $this->cargarDatosComandas();

        // Si es una petición AJAX, devolver JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            echo json_encode([
                'status'         => 'success',
                'cantidad'       => $cantidad,
                'ordenes'        => $ordenes,
                'detalles'       => $detallesAgrupados,
                'html'           => $this->renderOrdenesTable($ordenes, $detallesAgrupados),
                'htmlComandas'   => $this->renderComandas(
                    $hoy, $manana,
                    $almuerzos, $cenas, $colaciones, $especiales, $desayunosManana,
                    $resumenAlmuerzos, $resumenCenas, $resumenColaciones, $resumenEspeciales, $resumenDesayunos,
                    $totalAlmuerzos, $totalCenas, $totalColaciones, $totalEspeciales, $totalDesayunos,
                    $masivoAtan, $masivoInn, $totalMasivoAtan, $totalMasivoInn
                ),
            ]);
            return;
        }

        require_once __DIR__ . '/../views/cocina/index.php';
    }

    /** Helper: renderiza órdenes para AJAX */
    private function renderOrdenesTable($ordenes, $detallesAgrupados): string
    {
        ob_start();
        include __DIR__ . '/../views/cocina/tabla_ordenes.php';
        return ob_get_clean();
    }

    /** Helper: carga todos los datos de comandas del día */
    private function cargarDatosComandas(): array
    {
        $cm     = new ComandaModel();
        $dm     = new DesayunoMasivoModel();
        $hoy    = date('Y-m-d');
        $manana = date('Y-m-d', strtotime('+1 day'));

        $almuerzos       = $cm->obtenerPorFechaYTipo($hoy,    'almuerzo');
        $cenas           = $cm->obtenerPorFechaYTipo($hoy,    'cena');
        $colaciones      = $cm->obtenerPorFechaYTipo($hoy,    'colacion');
        $especiales      = $cm->obtenerPorFechaYTipo($hoy,    'colacion_especial');
        $desayunosManana = $cm->obtenerPorFechaYTipo($manana, 'desayuno');

        $resumenAlmuerzos  = $cm->resumenPorHotel($hoy,    'almuerzo');
        $resumenCenas      = $cm->resumenPorHotel($hoy,    'cena');
        $resumenColaciones = $cm->resumenPorHotel($hoy,    'colacion');
        $resumenEspeciales = $cm->resumenPorHotel($hoy,    'colacion_especial');
        $resumenDesayunos  = $cm->resumenPorHotel($manana, 'desayuno');

        $totalAlmuerzos  = $cm->totalPersonas($hoy,    'almuerzo');
        $totalCenas      = $cm->totalPersonas($hoy,    'cena');
        $totalColaciones = $cm->totalPersonas($hoy,    'colacion');
        $totalEspeciales = $cm->totalPersonas($hoy,    'colacion_especial');
        $totalDesayunos  = $cm->totalPersonas($manana, 'desayuno');

        // Desayunos masivos de mañana (nueva tabla)
        $masivoAtan      = $dm->obtenerPorFechaHotel($manana, 'Atankalama');
        $masivoInn       = $dm->obtenerPorFechaHotel($manana, 'Atankalama Inn');
        $totalesMasivo   = $dm->totalesPorHotel($manana);
        $totalMasivoAtan = $totalesMasivo['Atankalama']     ?? 0;
        $totalMasivoInn  = $totalesMasivo['Atankalama Inn'] ?? 0;

        return [
            $hoy, $manana,
            $almuerzos, $cenas, $colaciones, $especiales, $desayunosManana,
            $resumenAlmuerzos, $resumenCenas, $resumenColaciones, $resumenEspeciales, $resumenDesayunos,
            $totalAlmuerzos, $totalCenas, $totalColaciones, $totalEspeciales, $totalDesayunos,
            $masivoAtan, $masivoInn, $totalMasivoAtan, $totalMasivoInn,
        ];
    }

    /** Helper: renderiza comandas para AJAX */
    private function renderComandas(
        string $hoy, string $manana,
        array $almuerzos, array $cenas, array $colaciones, array $especiales, array $desayunosManana,
        array $resumenAlmuerzos, array $resumenCenas, array $resumenColaciones, array $resumenEspeciales, array $resumenDesayunos,
        int $totalAlmuerzos, int $totalCenas, int $totalColaciones, int $totalEspeciales, int $totalDesayunos,
        array $masivoAtan = [], array $masivoInn = [], int $totalMasivoAtan = 0, int $totalMasivoInn = 0
    ): string {
        ob_start();
        include __DIR__ . '/../views/cocina/tabla_comandas.php';
        return ob_get_clean();
    }


    public function cerrar($id)
    {
        $model = new CocinaModel();
        $success = $model->cerrarOrden($id);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            echo json_encode(['status' => $success ? 'success' : 'error']);
            return;
        }

        header('Location: index.php?page=cocina');
        exit;
    }



    public function enviarReporteDiario()
    {
        $fechaHoy = date('Y-m-d');
        $archivoControl = __DIR__ . '/../tmp/reporte_enviado.txt';
        $logFile = __DIR__ . '/../tmp/log_reporte.txt';
        $logMsg = '[' . date('Y-m-d H:i:s') . '] ';
        $isCli = php_sapi_name() === 'cli' || defined('STDIN');

        // Solo se ejecuta desde CLI (cron); rechazar peticiones web
        if (!$isCli) {
            http_response_code(403);
            return;
        }

        // Validar si ya se envió hoy
        if (file_exists($archivoControl)) {
            $fechaUltimoEnvio = trim(file_get_contents($archivoControl));
            if ($fechaUltimoEnvio === $fechaHoy) {
                file_put_contents(__DIR__ . '/../tmp/debug.txt', '[' . date('Y-m-d H:i:s') . "] Reporte ya enviado anteriormente\n", FILE_APPEND);
                $this->responderEnvio("Reporte ya fue enviado hoy.", $isCli);
                return;
            }
        }

        $model = new CocinaModel();
        $datos = $model->obtenerResumenUltimosDias(5);

        ob_start();
        include __DIR__ . '/../views/reporte/index.php';
        $contenido = ob_get_clean();

        $destinatarios = [
            'gabrielacarrasco@atankalama.com',
            'jorgeperez@atankalama.com',
            'rjaquers@gmail.com'
        ];

        $asunto = "Reporte Cocina " . date('d-m-Y');
        $resultado = Mailer::enviar($asunto, $contenido, $destinatarios);
        $logMsg .= 'PHPMailer envío ' . ($resultado ? '✅ OK' : '❌ ERROR') . '. ';

        file_put_contents($archivoControl, $fechaHoy);
        file_put_contents($logFile, $logMsg . PHP_EOL, FILE_APPEND);
        $this->responderEnvio("Reporte enviado correctamente.", $isCli);
    }

    private function responderEnvio($mensaje, $isCli)
    {
        if ($isCli) {
            echo $mensaje . "\n";
        } elseif (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['mensaje' => $mensaje]);
        } else {
            echo "<script>alert('$mensaje'); window.location.href = 'index.php?page=cocina/verReporte';</script>";
        }
    }


    public function log()
    //Crear vista para leer log: ?page=cocina/log
    {
        $logPath = __DIR__ . '/../tmp/log_reporte.txt';
        if (!file_exists($logPath)) {
            echo 'No hay registros aún.';

            return;
        }

        $lineas = file($logPath);
        include __DIR__ . '/../views/cocina/log.php';
    }

    public function verReporte()
    {
        $model = new CocinaModel();
        $datos = $model->obtenerResumenUltimosDias(5);

        include __DIR__ . '/../views/reporte/ver.php';
    }


} //fin controller
