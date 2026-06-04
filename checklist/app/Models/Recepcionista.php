<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Recepcionista extends Model
{
    /**
     * Obtiene la lista de todos los recepcionistas activos
     */
    public function allActive()
    {
        $stmt = $this->db->query("SELECT id, nombre, correo FROM nov_recepcionistas WHERE activo = 1 ORDER BY nombre ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
