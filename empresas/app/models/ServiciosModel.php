<?php
/**
 * ServiciosModel - Atankalama Empresas
 * Gestiona registros de servicios desde cat6852_atan.coci_ordenes
 */
class ServiciosModel extends Model
{
    public function countByCompany($company_id, $filter = 7)
    {
        $dateCondition = $this->getDateCondition($filter);
        
        $sql = "SELECT COUNT(*) as total 
                FROM cat6852_atan.coci_ordenes 
                WHERE company_id = ? AND $dateCondition";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$company_id]);
        $row = $stmt->fetch();
        
        return $row['total'] ?? 0;
    }

    public function getAllByCompany($company_id, $filter = 7, $limit = 1000)
    {
        $dateCondition = $this->getDateCondition($filter);
        
        $sql = "SELECT *
                FROM cat6852_atan.coci_ordenes
                WHERE company_id = ? AND $dateCondition
                ORDER BY fecha_hora DESC
                LIMIT $limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$company_id]);
        return $stmt->fetchAll();
    }

    private function getDateCondition($filter)
    {
        if ($filter == 'last_month') {
            return "MONTH(fecha_registro) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) 
                    AND YEAR(fecha_registro) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)";
        }
        
        $days = (int)$filter;
        return "fecha_registro >= DATE_SUB(CURRENT_DATE, INTERVAL $days DAY)";
    }
}
