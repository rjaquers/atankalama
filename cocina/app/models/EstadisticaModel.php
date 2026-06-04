<?php
require_once __DIR__ . '/../config/db.php';

class EstadisticaModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance(); // Usamos el singleton correctamente
    }

    public function obtenerEstadisticas($inicio, $fin, $periodo = 'day')
    {
        return [
            'por_dia'           => $this->ordenesPorFechas($inicio, $fin),
            'colores_dia'       => $this->coloresPorFechas($inicio, $fin),
            'recaudacion_dia'   => $this->recaudacionPorFechas($inicio, $fin),
            'sin_pago'          => $this->ordenesSinPago($inicio, $fin),
            'comandas_empresa'  => $this->comandasPorEmpresa($inicio, $fin, $periodo),
            'tipos_servicio'    => $this->comandasPorTipo($inicio, $fin),
            'resumen_empresas'  => $this->resumenTotalEmpresas($inicio, $fin)
        ];
    }

    public function comandasPorEmpresa($inicio, $fin, $periodo = 'day')
    {
        $dateFormat = match($periodo) {
            'week'  => 'YEARWEEK(fecha, 1)',
            'month' => 'DATE_FORMAT(fecha, "%Y-%m")',
            'year'  => 'YEAR(fecha)',
            default => 'DATE(fecha)'
        };

        // Intentamos obtener el nombre de la empresa de varias fuentes
        $sql = "SELECT 
                    $dateFormat as periodo,
                    COALESCE(NULLIF(nombre_empresa, ''), 'Particular') as empresa,
                    tipo_servicio,
                    SUM(cantidad_personas) as total_pax,
                    COUNT(*) as total_comandas
                FROM coci_comandas
                WHERE 1 ";
        
        $params = [];
        if ($inicio) { $sql .= "AND fecha >= ? "; $params[] = $inicio; }
        if ($fin)    { $sql .= "AND fecha <= ? "; $params[] = $fin; }
        
        $sql .= "GROUP BY periodo, empresa, tipo_servicio
                 ORDER BY periodo ASC, total_pax DESC";
                 
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function comandasPorTipo($inicio, $fin)
    {
        $sql = "SELECT tipo_servicio, SUM(cantidad_personas) as total 
                FROM coci_comandas 
                WHERE 1 ";
        $params = [];
        if ($inicio) { $sql .= "AND fecha >= ? "; $params[] = $inicio; }
        if ($fin)    { $sql .= "AND fecha <= ? "; $params[] = $fin; }
        $sql .= "GROUP BY tipo_servicio";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function resumenTotalEmpresas($inicio, $fin)
    {
        $sql = "SELECT 
                    COALESCE(nombre_empresa, 'Particular') as empresa,
                    SUM(cantidad_personas) as total_pax,
                    COUNT(*) as total_comandas
                FROM coci_comandas
                WHERE 1 ";
        $params = [];
        if ($inicio) { $sql .= "AND fecha >= ? "; $params[] = $inicio; }
        if ($fin)    { $sql .= "AND fecha <= ? "; $params[] = $fin; }
        $sql .= "GROUP BY empresa ORDER BY total_pax DESC LIMIT 10";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ordenesPorFechas($inicio, $fin)
    {
        $sql = 'SELECT DATE(`fecha_hora`) AS `fecha`, COUNT(*) AS `total` FROM `coci_ordenes` WHERE 1 ';
        $params = [];
        if ($inicio) { $sql .= 'AND fecha_hora >= ? '; $params[] = $inicio; }
        if ($fin)    { $sql .= 'AND fecha_hora <= ? '; $params[] = $fin; }
        $sql .= 'GROUP BY DATE(fecha_hora) ORDER BY fecha DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function coloresPorFechas($inicio, $fin)
    {
        $sql = "SELECT color_cierre, COUNT(*) AS total FROM coci_ordenes WHERE estado = 'cerrada' ";
        $params = [];
        if ($inicio) { $sql .= 'AND fecha_cierre >= ? '; $params[] = $inicio; }
        if ($fin)    { $sql .= 'AND fecha_cierre <= ? '; $params[] = $fin; }
        $sql .= 'GROUP BY color_cierre';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recaudacionPorFechas($inicio, $fin)
    {
        $sql = 'SELECT DATE(o.fecha_cierre) AS fecha, SUM(d.precio * d.cantidad) AS total
                FROM coci_detalles_orden d
                JOIN coci_ordenes o ON o.id = d.orden_id
                WHERE d.pagado = 1 ';
        $params = [];
        if ($inicio) { $sql .= 'AND o.fecha_cierre >= ? '; $params[] = $inicio; }
        if ($fin)    { $sql .= 'AND o.fecha_cierre <= ? '; $params[] = $fin; }
        $sql .= 'GROUP BY DATE(o.fecha_cierre) ORDER BY fecha DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ordenesSinPago($inicio = null, $fin = null)
    {
        $sql = 'SELECT o.id, o.habitacion, o.fecha_hora, o.total
                FROM coci_ordenes o
                JOIN coci_detalles_orden d ON o.id = d.orden_id
                WHERE d.pagado = 0 ';
        $params = [];
        if ($inicio) { $sql .= 'AND o.fecha_hora >= ? '; $params[] = $inicio; }
        if ($fin)    { $sql .= 'AND o.fecha_hora <= ? '; $params[] = $fin; }
        $sql .= 'GROUP BY o.id ORDER BY o.fecha_hora DESC, o.habitacion';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
