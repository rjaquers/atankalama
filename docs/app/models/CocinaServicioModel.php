<?php
/**
 * Modelo de Servicios de Alimentación (cocina).
 *
 * Lee y actualiza registros en coci_comandas (BD cat6852_atan).
 * Se usa exclusivamente desde la ficha de empresa para mostrar
 * el historial de servicios y permitir marcar cobrado/pendiente.
 *
 * @package App\Models
 */
class CocinaServicioModel
{
    private PDO $conn;

    /**
     * Tipos de servicio disponibles para filtros y etiquetas.
     */
    const TIPOS = [
        'desayuno'          => 'Desayuno',
        'cena'              => 'Cena',
        'colacion'          => 'Colación',
        'colacion_especial' => 'Colación Especial',
    ];

    public function __construct()
    {
        $host   = defined('COCINA_DB_HOST') ? COCINA_DB_HOST : DB_HOST;
        $dbname = defined('COCINA_DB_NAME') ? COCINA_DB_NAME : 'cat6852_atan';
        $user   = defined('COCINA_DB_USER') ? COCINA_DB_USER : '';
        $pass   = defined('COCINA_DB_PASS') ? COCINA_DB_PASS : '';

        $this->conn = new PDO(
            "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    /**
     * Devuelve los servicios de alimentación de una empresa con filtros opcionales.
     *
     * @param  int   $companyId ID de la empresa en doc_companies
     * @param  array $filters   Filtros opcionales:
     *   - tipo_servicio (string) desayuno|cena|colacion|colacion_especial
     *   - cobrado       (string) '0' o '1'
     *   - fecha_desde   (string) Y-m-d
     *   - fecha_hasta   (string) Y-m-d
     *   - sin_contrato  (bool)   true = solo donde contract_id IS NULL
     * @return array
     */
    public function getByCompany(int $companyId, array $filters = []): array
    {
        $where  = ['company_id = :company_id'];
        $params = [':company_id' => $companyId];

        if (!empty($filters['tipo_servicio'])) {
            $where[]                   = 'tipo_servicio = :tipo_servicio';
            $params[':tipo_servicio']  = $filters['tipo_servicio'];
        }

        if (isset($filters['cobrado']) && $filters['cobrado'] !== '') {
            $where[]           = 'cobrado = :cobrado';
            $params[':cobrado'] = (int)$filters['cobrado'];
        }

        if (!empty($filters['fecha_desde'])) {
            $where[]               = 'fecha >= :fecha_desde';
            $params[':fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $where[]               = 'fecha <= :fecha_hasta';
            $params[':fecha_hasta'] = $filters['fecha_hasta'];
        }

        if (!empty($filters['sin_contrato'])) {
            $where[] = 'contract_id IS NULL';
        }

        $sql  = "SELECT * FROM coci_comandas WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY fecha DESC, hora_servicio DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Resumen estadístico de servicios para una empresa.
     *
     * @param  int $companyId
     * @return array total, cobrado, pendiente, total_personas
     */
    public function resumenByCompany(int $companyId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT
                COUNT(*)                    AS total,
                COALESCE(SUM(cobrado), 0)   AS cobrado,
                COALESCE(SUM(1 - cobrado), 0) AS pendiente,
                COALESCE(SUM(cantidad_personas), 0) AS total_personas,
                COALESCE(SUM(CASE WHEN contract_id IS NULL THEN 1 ELSE 0 END), 0) AS sin_contrato
             FROM coci_comandas
             WHERE company_id = ?"
        );
        $stmt->execute([$companyId]);
        return $stmt->fetch() ?: ['total' => 0, 'cobrado' => 0, 'pendiente' => 0, 'total_personas' => 0, 'sin_contrato' => 0];
    }

    /**
     * Alterna el estado cobrado de un servicio.
     * Actualiza cobrado_at según el nuevo estado.
     *
     * @param  int $id ID de coci_comandas
     * @return bool
     */
    public function toggleCobrado(int $id): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE coci_comandas
             SET cobrado    = 1 - cobrado,
                 cobrado_at = IF(cobrado = 0, NOW(), NULL)
             WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    /**
     * Devuelve totales de personas agrupados por hotel, fecha y tipo de servicio.
     * Usado para el reporte resumen por hotel (exportación Excel multi-hoja).
     *
     * @param  int    $companyId  ID de la empresa
     * @param  string $fechaDesde Y-m-d
     * @param  string $fechaHasta Y-m-d
     * @return array  Filas con: nombre_hotel, fecha, tipo_servicio, total
     */
    public function getResumenPorHotelFecha(int $companyId, string $fechaDesde, string $fechaHasta): array
    {
        $stmt = $this->conn->prepare(
            "SELECT nombre_hotel, fecha, tipo_servicio,
                    SUM(cantidad_personas) AS total
             FROM coci_comandas
             WHERE company_id = :company_id
               AND fecha BETWEEN :fecha_desde AND :fecha_hasta
             GROUP BY nombre_hotel, fecha, tipo_servicio
             ORDER BY nombre_hotel, fecha, tipo_servicio"
        );
        $stmt->execute([
            ':company_id'  => $companyId,
            ':fecha_desde' => $fechaDesde,
            ':fecha_hasta' => $fechaHasta,
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Devuelve el estado actual de cobrado para un servicio.
     *
     * @param  int $id
     * @return array|null
     */
    public function getEstadoCobrado(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, cobrado, cobrado_at FROM coci_comandas WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
