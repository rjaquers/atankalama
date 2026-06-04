<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class OrdenModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function crearOrden(
        string $habitacion,
        string $lugar,
        ?string $nombreHuesped,
        int $cantidadPersonas,
        array $detalles // array de ['producto', 'precio', 'cantidad']
    ): int {
        $total = 0;
        foreach ($detalles as $d) {
            $total += $d['precio'] * $d['cantidad'];
        }

        $stmt = $this->db->prepare(
            'INSERT INTO coci_ordenes (habitacion, lugar, nombre_huesped, cantidad_personas, total)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$habitacion, $lugar, $nombreHuesped, $cantidadPersonas, $total]);
        $ordenId = (int)$this->db->lastInsertId();

        $stmtDet = $this->db->prepare(
            'INSERT INTO coci_detalles_orden (orden_id, producto, precio, cantidad) 
             VALUES (?, ?, ?, ?)'
        );
        foreach ($detalles as $d) {
            $stmtDet->execute([$ordenId, $d['producto'], $d['precio'], $d['cantidad']]);
        }

        return $ordenId;
    }
}
