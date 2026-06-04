<?php
require_once __DIR__ . '/../models/EstadisticaModel.php';
require_once __DIR__  . '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EstadisticaController
{

    public function index()
    {
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
        $fecha_fin    = $_GET['fecha_fin']    ?? date('Y-m-d');
        $periodo      = $_GET['periodo']      ?? 'day';

        $model = new EstadisticaModel();
        $datos = $model->obtenerEstadisticas($fecha_inicio, $fecha_fin, $periodo);

        require ROOT_PATH . '/views/estadistica/index.php';
    }

        public function exportar_excel()
    {
        $fecha_inicio = $_GET['fecha_inicio'] ?? null;
        $fecha_fin = $_GET['fecha_fin'] ?? null;

        $model = new EstadisticaModel(Database::getInstance());
        $datos = $model->ordenesSinPago(); // puedes agregar filtros si deseas

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Órdenes Sin Pago');

        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Habitación');
        $sheet->setCellValue('C1', 'Fecha');
        $sheet->setCellValue('D1', 'Total');

        $row = 2;
        foreach ($datos as $orden) {
            $sheet->setCellValue("A$row", $orden['id']);
            $sheet->setCellValue("B$row", $orden['habitacion']);
            $sheet->setCellValue("C$row", $orden['fecha_hora']);
            $sheet->setCellValue("D$row", $orden['total']);
            $row++;
        }

        $nombreArchivo = 'ordenes_sin_pago_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"$nombreArchivo\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
