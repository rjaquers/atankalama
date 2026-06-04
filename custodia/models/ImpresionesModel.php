<?php

/**
 * ImpresionesModel
 *
 * Responsabilidades por tabla:
 *
 *  colacion_voucher_impresiones — fuente de verdad del tótem.
 *    Escrita por: registrarImpresion()
 *    Leída por:   fueImpresoHoy()  (solo registros de hoy)
 *
 *  colacion_impresiones — auditoría enriquecida (IP, user-agent, flag copia).
 *    Escrita por: registrar()  (también llamada por registrarImpresion para auditoría)
 *    Leída por:   yaImpresoHoy()
 *
 *  colacion_impresion_log — tracking de impresiones a nivel lote/voucher (flujo admin).
 *    Gestionada por ColacionVoucher::registrarImpresionLote() y registrarImpresionVoucher().
 */
class ImpresionesModel
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Verifica si el RUT ya imprimió este servicio HOY (flujo admin / PersonaController).
     * Usa colacion_impresiones con filtro de fecha.
     */
    public function yaImpresoHoy(string $rut, int $servicio_id): bool
    {
        $sql = 'SELECT 1
                FROM `colacion_impresiones`
                WHERE `rut` = ? AND `servicio_id` = ?
                  AND DATE(fecha_impresion) = CURDATE()
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('si', $rut, $servicio_id);
        $stmt->execute();

        return (bool)$stmt->get_result()->fetch_row();
    }

    /**
     * Registra impresión en colacion_impresiones con IP y user-agent (flujo admin).
     * Si ya existe registro hoy, marca como copia.
     */
    public function registrar(string $rut, int $servicio_id, bool $copia = false): bool
    {
        $sql = 'INSERT INTO colacion_impresiones
                (rut, servicio_id, ip, user_agent, copia)
                VALUES (?, ?, ?, ?, ?)';

        $ip       = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua       = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $copia_val = $copia ? 1 : 0;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('sissi', $rut, $servicio_id, $ip, $ua, $copia_val);

        return $stmt->execute();
    }

    /**
     * Registra impresión del tótem en colacion_voucher_impresiones.
     * También escribe en colacion_impresiones para auditoría enriquecida.
     */
    public function registrarImpresion(string $rut, int $servicio_id): bool
    {
        // Antes de insertar, verificar si ya existe hoy (para marcar copia en auditoría)
        $esCopia = $this->fueImpresoHoy($rut, $servicio_id) !== null;

        // Fuente de verdad del tótem
        $sql = 'INSERT INTO colacion_voucher_impresiones (rut, servicio_id)
                VALUES (?, ?)';
        $stmt = $this->db->prepare($sql);
        if (! $stmt) {
            throw new RuntimeException('Error en prepare(): '.$this->db->error);
        }
        $stmt->bind_param('si', $rut, $servicio_id);
        $ok = $stmt->execute();
        $stmt->close();

        // Auditoría enriquecida con IP y user-agent
        $this->registrar($rut, $servicio_id, $esCopia);

        return $ok;
    }

    /**
     * Verifica si el tótem ya imprimió este servicio hoy para el RUT dado.
     * Devuelve la hora de impresión (formato H:i) o null si no hay registro.
     * Fuente: colacion_voucher_impresiones.
     */
    public function fueImpresoHoy(string $rut, int $servicio_id): ?string
    {
        $sql = 'SELECT TIME_FORMAT(fecha_impresion, "%H:%i") AS hora
                FROM colacion_voucher_impresiones
                WHERE rut = ?
                  AND servicio_id = ?
                  AND DATE(fecha_impresion) = CURDATE()
                ORDER BY fecha_impresion DESC
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('si', $rut, $servicio_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ? $row['hora'] : null;
    }

    /**
     * Reporte de impresiones para todos los registrados en un lote.
     * Devuelve estructura indexada por RUT:
     *   [ rut => [ rut, nombre, habitacion, servicios => [ servicio_id => [ nombre, total, primera, ultima ] ] ] ]
     */
    public function obtenerReporteDelLote(int $lote_id): array
    {
        $sql = '
            SELECT
                v.guest_rut,
                v.guest_nombre,
                v.guest_habitacion,
                st.id                        AS servicio_id,
                st.nombre                    AS servicio_nombre,
                COUNT(cvi.id)                AS total_impresiones,
                MIN(cvi.fecha_impresion)     AS primera_impresion,
                MAX(cvi.fecha_impresion)     AS ultima_impresion
            FROM colacion_voucher v
            JOIN (
                SELECT servicio_tipo_id AS id FROM colacion_lote WHERE id = ?
                UNION
                SELECT adicional_id     AS id FROM colacion_lote_adicional WHERE lote_id = ?
            ) srv ON 1=1
            JOIN colacion_servicio_tipo st ON st.id = srv.id
            LEFT JOIN colacion_voucher_impresiones cvi
                ON  cvi.rut        = v.guest_rut
                AND cvi.servicio_id = st.id
            WHERE v.lote_id        = ?
              AND v.guest_rut IS NOT NULL
              AND v.guest_rut     != \'\'
            GROUP BY v.guest_rut, v.guest_nombre, v.guest_habitacion, st.id, st.nombre
            ORDER BY v.guest_nombre ASC, st.nombre ASC
        ';

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log('ImpresionesModel::obtenerReporteDelLote prepare falló: '.$this->db->error);
            return [];
        }
        $stmt->bind_param('iii', $lote_id, $lote_id, $lote_id);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Transformar resultado plano en estructura anidada por RUT
        $reporte = [];
        foreach ($rows as $r) {
            $rut = $r['guest_rut'];
            if (!isset($reporte[$rut])) {
                $reporte[$rut] = [
                    'rut'        => $rut,
                    'nombre'     => $r['guest_nombre'],
                    'habitacion' => $r['guest_habitacion'],
                    'servicios'  => [],
                ];
            }
            $reporte[$rut]['servicios'][(int)$r['servicio_id']] = [
                'nombre'           => $r['servicio_nombre'],
                'total'            => (int)$r['total_impresiones'],
                'primera'          => $r['primera_impresion'],
                'ultima'           => $r['ultima_impresion'],
            ];
        }

        return $reporte;
    }

    /**
     * Detalle plano de cada impresión del tótem para los registrados en un lote.
     * Fuente: colacion_voucher_impresiones.
     */
    public function obtenerDetalleTotemDelLote(int $lote_id): array
    {
        $sql = '
            SELECT
                v.guest_rut                         AS rut,
                v.guest_nombre                      AS nombre,
                v.guest_habitacion                  AS habitacion,
                st.nombre                           AS servicio,
                cvi.fecha_impresion,
                ROW_NUMBER() OVER (
                    PARTITION BY cvi.rut, cvi.servicio_id
                    ORDER BY cvi.fecha_impresion
                )                                   AS nro_impresion
            FROM colacion_voucher_impresiones cvi
            INNER JOIN colacion_voucher v
                ON  v.guest_rut = cvi.rut
                AND v.lote_id   = ?
            INNER JOIN colacion_servicio_tipo st ON st.id = cvi.servicio_id
            ORDER BY cvi.fecha_impresion ASC
        ';

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log('ImpresionesModel::obtenerDetalleTotemDelLote prepare falló: '.$this->db->error);
            return [];
        }
        $stmt->bind_param('i', $lote_id);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    /**
     * Auditoría enriquecida (IP + user-agent + flag copia) para un lote.
     * Fuente: colacion_impresiones.
     */
    public function obtenerAuditoriaDelLote(int $lote_id): array
    {
        $sql = '
            SELECT
                v.guest_rut                         AS rut,
                v.guest_nombre                      AS nombre,
                v.guest_habitacion                  AS habitacion,
                st.nombre                           AS servicio,
                ci.fecha_impresion,
                ci.ip,
                ci.user_agent,
                CASE ci.copia WHEN 1 THEN "Reimpresión" ELSE "Original" END AS tipo
            FROM colacion_impresiones ci
            INNER JOIN colacion_voucher v
                ON  v.guest_rut = ci.rut
                AND v.lote_id   = ?
            INNER JOIN colacion_servicio_tipo st ON st.id = ci.servicio_id
            ORDER BY ci.fecha_impresion ASC
        ';

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log('ImpresionesModel::obtenerAuditoriaDelLote prepare falló: '.$this->db->error);
            return [];
        }
        $stmt->bind_param('i', $lote_id);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }
}
