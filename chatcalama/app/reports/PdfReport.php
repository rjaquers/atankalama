<?php
class PdfReport
{
    public static function generate($html, $filename = "reporte.pdf")
    {
        // Placeholder: integrar Dompdf cuando lo uses (no incluido por defecto).
        header("Content-Type: text/html; charset=utf-8");
        echo "<h3>PdfReport placeholder</h3>";
        echo "<p>Integra Dompdf en /vendor y reemplaza este método.</p>";
        echo $html;
        exit;
    }
}
