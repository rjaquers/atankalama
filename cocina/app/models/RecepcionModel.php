<?php

require_once __DIR__ . '/../config/db.php';

class RecepcionModel
{
    private $conn;

    public function __construct()
    {
        // Obtener instancia PDO
        $this->conn = Database::getInstance();
    }

    public function obtenerOrdenes()
    {
        $stmt = $this->conn->prepare('SELECT * FROM coci_ordenes ORDER BY fecha_hora DESC');
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertarOrden(
        $habitacion, $lugar, $nombre_huesped, $cantidad_personas, $total, $hora_entrega,
        $tipo_solicitante = 'particular', $company_id = null, $contract_id = null,
        $nombre_contacto = null, $observaciones = null, $pagado = 0, $voucher = null
    ) {
        $sql = 'INSERT INTO coci_ordenes
                    (habitacion, lugar, nombre_huesped, cantidad_personas, total, fecha_hora,
                     tipo_solicitante, company_id, contract_id, nombre_contacto, email_respaldo, 
                     pagado, voucher, observaciones)
                VALUES
                    (:habitacion, :lugar, :nombre_huesped, :cantidad_personas, :total, :fecha_hora,
                     :tipo_solicitante, :company_id, :contract_id, :nombre_contacto, :email_respaldo,
                     :pagado, :voucher, :observaciones)';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':habitacion',       $habitacion);
        $stmt->bindParam(':lugar',            $lugar);
        $stmt->bindParam(':nombre_huesped',   $nombre_huesped);
        $stmt->bindParam(':cantidad_personas',$cantidad_personas, PDO::PARAM_INT);
        $stmt->bindParam(':total',            $total);
        $stmt->bindParam(':fecha_hora',       $hora_entrega);
        $stmt->bindParam(':tipo_solicitante', $tipo_solicitante);
        $stmt->bindParam(':company_id',       $company_id, PDO::PARAM_INT);
        $stmt->bindParam(':contract_id',      $contract_id, PDO::PARAM_INT);
        $stmt->bindParam(':nombre_contacto',  $nombre_contacto);
        $stmt->bindParam(':email_respaldo',   $observaciones); // Mantenemos por compatibilidad temporal
        $stmt->bindParam(':pagado',           $pagado, PDO::PARAM_INT);
        $stmt->bindParam(':voucher',          $voucher);
        $stmt->bindParam(':observaciones',    $observaciones);

        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function actualizarArchivoRespaldo($orden_id, $ruta)
    {
        $stmt = $this->conn->prepare(
            'UPDATE coci_ordenes SET archivo_respaldo = :ruta WHERE id = :id'
        );
        $stmt->bindParam(':ruta', $ruta);
        $stmt->bindParam(':id',   $orden_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function insertarDetalleOrden($orden_id, $producto, $precio, $cantidad)
    {
        $sql = 'INSERT INTO coci_detalles_orden (orden_id, producto, precio, cantidad) 
                VALUES (:orden_id, :producto, :precio, :cantidad)';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':orden_id', $orden_id, PDO::PARAM_INT);
        $stmt->bindParam(':producto', $producto);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);

        $stmt->execute();
    }

    public function obtenerOrdenPorId($id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM coci_ordenes WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerDetallesOrden($orden_id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM coci_detalles_orden WHERE orden_id = :orden_id');
        $stmt->bindParam(':orden_id', $orden_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
