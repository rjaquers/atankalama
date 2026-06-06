<?php
/**
 * AlimentacionModel - Atankalama Empresas
 * Gestiona registros de alimentación desde cat6852_atan.coci_comandas
 */
class AlimentacionModel extends Model
{
    /**
     * Obtiene el conteo de servicios en un rango de días o fechas específicas
     */
    public function countByCompany($company_id, $filter = 7, $start_date = null, $end_date = null)
    {
        $dateCondition = $this->getDateCondition($filter, $start_date, $end_date);
        
        $sql = "SELECT SUM(cantidad_personas) as total 
                FROM cat6852_atan.coci_comandas 
                WHERE company_id = ? AND $dateCondition";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$company_id]);
        $row = $stmt->fetch();
        
        return $row['total'] ?? 0;
    }

    /**
     * Obtiene listado detallado con filtros por periodo o fechas personalizadas
     */
    public function getLatestByCompany($company_id, $filter = 7, $limit = 10, $start_date = null, $end_date = null)
    {
        $dateCondition = $this->getDateCondition($filter, $start_date, $end_date);
        
        $sql = "SELECT c.*, v.rut, v.nombre as nombre_comensal, o.habitacion
                FROM cat6852_atan.coci_comandas c
                LEFT JOIN cat6852_atan.coci_voucher_clientes v ON c.id = v.comanda_id
                LEFT JOIN cat6852_atan.coci_ordenes o ON c.orden_id = o.id
                WHERE c.company_id = ? AND $dateCondition
                ORDER BY c.fecha DESC, c.hora_servicio DESC
                LIMIT $limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$company_id]);
        $rows = $stmt->fetchAll();

        // Aplicar máscara de RUT
        foreach ($rows as &$row) {
            if (!empty($row['rut'])) {
                $row['rut_masked'] = RutHelper::mask($row['rut']);
            }
        }
        
        return $rows;
    }

    /**
     * Obtiene el historial completo de una persona específica por su RUT
     */
    public function getHistoryByRut($company_id, $rut)
    {
        $sql = "SELECT c.*, v.rut, v.nombre as nombre_comensal, o.habitacion
                FROM cat6852_atan.coci_comandas c
                LEFT JOIN cat6852_atan.coci_voucher_clientes v ON c.id = v.comanda_id
                LEFT JOIN cat6852_atan.coci_ordenes o ON c.orden_id = o.id
                WHERE c.company_id = ? AND v.rut = ?
                ORDER BY c.fecha DESC, c.hora_servicio DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$company_id, $rut]);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            if (!empty($row['rut'])) {
                $row['rut_masked'] = RutHelper::mask($row['rut']);
            }
        }
        return $rows;
    }

    /**
     * Datos agrupados por día para gráfica de tendencia
     */
    public function getDailyStats($company_id, $filter = 7, $start_date = null, $end_date = null)
    {
        $dateCondition = $this->getDateCondition($filter, $start_date, $end_date);
        $sql = "SELECT fecha as label, SUM(cantidad_personas) as value 
                FROM cat6852_atan.coci_comandas 
                WHERE company_id = ? AND $dateCondition
                GROUP BY fecha ORDER BY fecha ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$company_id]);
        return $stmt->fetchAll();
    }

    /**
     * Distribución por tipo de servicio (Cena, Almuerzo, etc)
     */
    public function getDistributionStats($company_id, $filter = 7, $start_date = null, $end_date = null)
    {
        $dateCondition = $this->getDateCondition($filter, $start_date, $end_date);
        $sql = "SELECT tipo_servicio as label, COUNT(*) as value 
                FROM cat6852_atan.coci_comandas 
                WHERE company_id = ? AND $dateCondition
                GROUP BY tipo_servicio";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$company_id]);
        return $stmt->fetchAll();
    }

    private function getDateCondition($filter, $start_date = null, $end_date = null)
    {
        if ($filter === 'custom' && $start_date) {
            if ($end_date) {
                return "fecha BETWEEN '$start_date' AND '$end_date'";
            }
            return "fecha >= '$start_date'";
        }

        if ($filter == 'last_month') {
            return "MONTH(fecha) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) 
                    AND YEAR(fecha) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)";
        }
        
        $days = (int)$filter;
        return "fecha >= DATE_SUB(CURRENT_DATE, INTERVAL $days DAY)";
    }
}
