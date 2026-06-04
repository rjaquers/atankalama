<?php
require_once __DIR__ . '/../models/ReporteModel.php';
require_once __DIR__.'/../libraries/Mailer.php';

// Este modelo obtiene el total de órdenes y ventas por fecha en los últimos 5 días
class ReporteController
{
// Función principal para mostrar el reporte
    public function ver()
    {
        $model = new ReporteModel();

        $fecha_inicio = $_GET['fecha_inicio'] ?? null;
        $fecha_fin = $_GET['fecha_fin'] ?? null;

        $datos = $model->obtenerVentasPorRango($fecha_inicio, $fecha_fin);

        require ROOT_PATH.'views/reporte/ver.php';
    }

    // AJAX: Devuelve productos vendidos por fecha (detalle)
    public function detalles()
    {
        $fecha = $_GET['fecha'] ?? null;

        if (! $fecha) {
            echo 'Fecha inválida';

            return;
        }

        $model = new ReporteModel();
        $productos = $model->obtenerProductosPorFecha($fecha);

        require ROOT_PATH.'views/reporte/ajax_detalles.php';
    }

    // Exporta el reporte a Excel
    public function excel()
    {
        $model = new ReporteModel();

        $fecha_inicio = $_GET['fecha_inicio'] ?? null;
        $fecha_fin = $_GET['fecha_fin'] ?? null;

        $datos = $model->obtenerVentasPorRango($fecha_inicio, $fecha_fin);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=ventas.xls');

        echo "<table border='1'>";
        echo '<tr><th>Fecha</th><th>Total de Órdenes</th><th>Total Ventas</th></tr>';

        foreach ($datos as $fila) {
            echo '<tr>';
            echo '<td>'.date('d-m-Y', strtotime($fila['fecha'])).'</td>';
            echo '<td>'.$fila['total_ordenes'].'</td>';
            echo '<td>'.number_format($fila['total_ventas'], 0, ',', '.').'</td>';
            echo '</tr>';
        }

        echo '</table>';
    }

    // Exporta a Excel el detalle de productos vendidos en una fecha específica, incluye total general al final
    public function detalleExcel()
    {
        $fecha = $_GET['fecha'] ?? null;

        if (! $fecha) {
            die('Fecha no válida.');
        }

        $model = new ReporteModel();
        $productos = $model->obtenerProductosPorFecha($fecha);

        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment; filename=productos_$fecha.xls");

        echo "<table border='1'>";
        echo '<tr><th>Producto</th><th>Cantidad</th><th>Total</th></tr>';

        $totalGeneral = 0;
        $totalCantidad = 0;

        foreach ($productos as $item) {
            echo '<tr>';
            echo '<td>'.htmlspecialchars($item['producto']).'</td>';
            echo '<td>'.$item['cantidad_total'].'</td>';
            echo '<td>$'.number_format($item['total'], 0, ',', '.').'</td>';
            echo '</tr>';
            $totalGeneral += $item['total'];
            $totalCantidad += $item['cantidad_total'];
        }

        // Fila de total general
        echo "<tr style='font-weight:bold; background:#e6ffe6;'>";
        echo '<td>Total General</td>';
        echo "<td>$totalCantidad</td>";
        echo '<td>$'.number_format($totalGeneral, 0, ',', '.').'</td>';
        echo '</tr>';

        echo '</table>';
    }
// Aquí termina la función detalleExcel



} // Aquí termina la clase ReporteController
