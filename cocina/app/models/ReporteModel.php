<?php

require_once __DIR__ . '/../config/db.php';

class ReporteModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance(); // Instancia de PDO
    }

// Esta función permite filtrar por rango de fechas
    public function obtenerVentasPorRango($fecha_inicio = null, $fecha_fin = null)
    {
        $sql = 'SELECT 
                DATE(fecha_registro) AS fecha,
                COUNT(*) AS total_ordenes,
                SUM(total) AS total_ventas
            FROM coci_ordenes
            WHERE 1=1';

        $params = [];

        if ($fecha_inicio) {
            $sql .= ' AND fecha_registro >= :inicio';
            $params[':inicio'] = $fecha_inicio;
        }

        if ($fecha_fin) {
            $sql .= ' AND fecha_registro <= :fin';
            $params[':fin'] = $fecha_fin.' 23:59:59';
        }

        $sql .= ' GROUP BY DATE(fecha_registro) ORDER BY fecha DESC';

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerResumenUltimosDias($dias = 5)
    {
        file_put_contents(__DIR__.'/../tmp/debug.txt', '['.date('Y-m-d H:i:s')."] Entró Model Cocina\n", FILE_APPEND);

        $stmt = $this->conn->prepare(
            '
        SELECT DATE(fecha_registro) AS fecha, 
               COUNT(*) AS total_ordenes, 
               COALESCE(SUM(total), 0) AS total_ventas
        FROM coci_ordenes
        WHERE fecha_registro >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        GROUP BY DATE(fecha_registro)
        ORDER BY fecha DESC'
        );
        $stmt->execute([$dias]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Esta función devuelve los productos vendidos en una fecha específica
    public function obtenerProductosPorFecha($fecha)
    {
        $sql = 'SELECT D.producto, SUM(D.cantidad) as cantidad_total, SUM(D.precio * D.cantidad) as total
            FROM coci_detalles_orden D
            INNER JOIN coci_ordenes O ON O.id = D.orden_id
            WHERE DATE(O.fecha_registro) = :fecha
            GROUP BY D.producto
            ORDER BY total DESC';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':fecha', $fecha);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
