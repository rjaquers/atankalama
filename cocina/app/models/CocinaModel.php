<?php

require_once __DIR__ . '/../config/db.php';

class CocinaModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance(); // Instancia de PDO
    }

    public function obtenerOrdenPorId(int $id): ?array
    {
        $sql = 'SELECT * FROM coci_ordenes WHERE id = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function obtenerDetallesPorOrdenes(array $ids): array
    {
        if (empty($ids)) return [];

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT * FROM coci_detalles_orden WHERE orden_id IN ($placeholders)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($ids);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    private function calcularColorCierre(DateTime $fechaEntrega, DateTime $ahora): string
    {
        $diffMinutos = round(($fechaEntrega->getTimestamp() - $ahora->getTimestamp()) / 60);

        if ($diffMinutos < 0) {
            return 'negro'; // vencido
        } elseif ($diffMinutos <= 5) {
            return 'rojo';
        } elseif ($diffMinutos <= 15) {
            return 'amarillo';
        } else {
            return 'verde';
        }
    }



    public function obtenerOrdenesPendientes()
    {
        $sql = "SELECT O.id, O.habitacion, O.lugar, O.fecha_hora, O.nombre_huesped,
                   O.cantidad_personas, O.estado, O.fecha_cierre, O.fecha_registro, O.fecha_actualizacion, O.observaciones,  
                   C.producto, C.cantidad
            FROM coci_ordenes AS O
            LEFT JOIN coci_detalles_orden AS C ON O.id = C.orden_id
            WHERE O.estado != 'Cerrada'
            GROUP BY O.id
            ORDER BY O.fecha_hora ASC";

        $stmt = $this->conn->query($sql);
        $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [
            'ordenes' => $ordenes,
            'cantidad' => count($ordenes)
        ];
    }



    public function cerrarOrden($orden_id)
    {
        $horaCierre = date('Y-m-d H:i:s');

        // 1. Obtener la hora programada de entrega de esta orden
        $stmt = $this->conn->prepare('SELECT fecha_hora FROM coci_ordenes WHERE id = ?');
        $stmt->execute([$orden_id]);
        $orden = $stmt->fetch(PDO::FETCH_ASSOC);

        if (! $orden) {
            return false; // no existe
        }

        $fechaEntrega = new DateTime($orden['fecha_hora']);
        $ahora = new DateTime(); // fecha actual del servidor

        // 2. Calcular color de cierre
        $diffMinutos = round(($fechaEntrega->getTimestamp() - $ahora->getTimestamp()) / 60);
        if ($diffMinutos < 0) {
            $color = 'negro';
        } elseif ($diffMinutos <= 5) {
            $color = 'rojo';
        } elseif ($diffMinutos <= 15) {
            $color = 'amarillo';
        } else {
            $color = 'verde';
        }

        // 3. Actualizar estado, hora de cierre y color
        $stmt = $this->conn->prepare(
            "UPDATE coci_ordenes 
        SET estado = 'cerrada', fecha_cierre = ?, color_cierre = ?
        WHERE id = ?"
        );

        return $stmt->execute([$horaCierre, $color, $orden_id]);
    }


    function traducirColorCierre(string $color): string
    {
        switch ($color) {
            case 'verde': return 'A tiempo';
            case 'amarillo': return 'Con retraso leve';
            case 'rojo': return 'Retraso crítico';
            case 'negro': return 'Vencido';
            default: return 'Desconocido';
        }
    }

    public function obtenerResumenDelDia($fecha)
    {
        $stmt = $this->conn->prepare(
            'SELECT DATE(fecha_registro) AS fecha, 
               COUNT(*) AS total_ordenes, 
               COALESCE(SUM(total), 0) AS total_ventas
        FROM coci_ordenes
        WHERE fecha_registro >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        GROUP BY DATE(fecha_registro)
        ORDER BY fecha DESC
    '
        );
        $stmt->execute([$fecha]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
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

}
