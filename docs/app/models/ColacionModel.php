<?php
/**
 * Modelo de Colaciones y Vouchers.
 *
 * Lee registros de colacion_lote y colacion_voucher para asociar nombres
 * de huéspedes a los servicios de cocina.
 *
 * @package App\Models
 */
class ColacionModel extends Model
{
    /**
     * Intenta encontrar el ID de empresa en el sistema antiguo (tabla empresas)
     * basándose en el nombre de la empresa del sistema de contratos.
     *
     * @param string $name Nombre de la empresa
     * @return int|null ID en tabla empresas o null
     */
    public function findOldCompanyId($name)
    {
        $stmt = $this->conn->prepare("SELECT id FROM empresas WHERE nombre = ? OR nombre LIKE ? LIMIT 1");
        $likeName = "%" . $name . "%";
        $stmt->bind_param("ss", $name, $likeName);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row ? (int)$row['id'] : null;
    }

    /**
     * Obtiene todos los nombres de huéspedes para una empresa en un rango de fechas.
     *
     * @param  int    $companyId ID de la empresa (se probará directo y por nombre)
     * @param  string $companyName Nombre de la empresa (para fallback)
     * @param  string $desde
     * @param  string $hasta
     * @return array  Agrupado por [fecha][tipo_id] => [nombres]
     */
    public function getNamesBatch($companyId, $companyName, $desde = null, $hasta = null)
    {
        // Primero intentamos por ID directo
        $names = $this->fetchFromDb($companyId, $desde, $hasta);
        
        // Si no hay nada, intentamos buscar el ID antiguo por nombre
        if (empty($names)) {
            $oldId = $this->findOldCompanyId($companyName);
            if ($oldId && $oldId !== $companyId) {
                $names = $this->fetchFromDb($oldId, $desde, $hasta);
            }
        }
        
        return $names;
    }

    private function fetchFromDb($id, $desde, $hasta)
    {
        $where = ["l.empresa_id = ?"];
        $params = [$id];
        $types = "i";

        if ($desde) {
            $where[] = "l.fecha_servicio >= ?";
            $params[] = $desde;
            $types .= "s";
        }
        if ($hasta) {
            $where[] = "l.fecha_servicio <= ?";
            $params[] = $hasta;
            $types .= "s";
        }

        $sql = "
            SELECT l.fecha_servicio, l.servicio_tipo_id, v.guest_nombre
            FROM colacion_voucher v
            JOIN colacion_lote l ON l.id = v.lote_id
            WHERE " . implode(" AND ", $where) . "
              AND v.estado != 'anulado'
            ORDER BY l.fecha_servicio ASC, v.guest_nombre ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();

        $batch = [];
        while ($row = $res->fetch_assoc()) {
            $f = $row['fecha_servicio'];
            $t = $row['servicio_tipo_id'];
            if (!isset($batch[$f])) $batch[$f] = [];
            if (!isset($batch[$f][$t])) $batch[$f][$t] = [];
            if (!empty($row['guest_nombre'])) {
                $batch[$f][$t][] = $row['guest_nombre'];
            }
        }
        return $batch;
    }
}
