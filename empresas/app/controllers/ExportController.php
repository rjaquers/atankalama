<?php
/**
 * ExportController - Atankalama Empresas
 * Gestiona la exportación de datos a XLSX nativo
 */

// Intentar cargar PhpSpreadsheet usando rutas relativas
// Probamos con custodia/vendor ya que es más completo que cocina/vendor
$vendorPath = dirname(dirname(dirname(dirname(__FILE__)))) . '/custodia/vendor/autoload.php';

if (file_exists($vendorPath)) {
    require_once $vendorPath;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportController extends Controller
{
    public function alimentacion($days = 7)
    {
        // Limpiar cualquier búfer de salida previo para evitar corrupción del archivo
        if (ob_get_level()) ob_end_clean();

        $user = AuthService::user();
        $start_date = $_GET['start_date'] ?? null;
        $end_date = $_GET['end_date'] ?? null;

        $model = new AlimentacionModel();
        $records = $model->getLatestByCompany($user['company_id'], $days, 5000, $start_date, $end_date);

        // Si PhpSpreadsheet no está disponible, fallamos con gracia
        if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            die("Error: La librería PhpSpreadsheet no se encuentra en la ruta: " . $vendorPath);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Alimentación');

        // Estilo para cabeceras
        $sheet->setCellValue('A1', 'Fecha');
        $sheet->setCellValue('B1', 'Servicio');
        $sheet->setCellValue('C1', 'Comensal');
        $sheet->setCellValue('D1', 'RUT (Protegido)');
        $sheet->setCellValue('E1', 'Cantidad');
        $sheet->setCellValue('F1', 'Estado');

        // Negrita para cabeceras
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        $row = 2;
        foreach ($records as $r) {
            $sheet->setCellValue('A' . $row, date('d/m/Y', strtotime($r['fecha'])));
            $sheet->setCellValue('B' . $row, ucfirst($r['tipo_servicio']));
            $sheet->setCellValue('C' . $row, $r['nombre_comensal'] ?? 'No especificado');
            $sheet->setCellValue('D' . $row, $r['rut_masked'] ?? '---');
            $sheet->setCellValue('E' . $row, $r['cantidad_personas']);
            $sheet->setCellValue('F' . $row, $r['cobrado'] ? 'Cobrado' : 'Pendiente');
            $row++;
        }

        // Autoajustar columnas
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "alimentacion_" . date('Ymd_His') . ".xlsx";

        // Cabeceras HTTP para XLSX
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Fecha en el pasado
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Pragma: public');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
