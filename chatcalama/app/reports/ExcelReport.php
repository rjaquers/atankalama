<?php
class ExcelReport
{
    public static function export($data, $filename = "reporte.xlsx")
    {
        // Placeholder: integrar PhpSpreadsheet cuando lo uses (no incluido por defecto).
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode([
            'message' => 'ExcelReport placeholder. Integra PhpSpreadsheet en /vendor y reemplaza este método.',
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
