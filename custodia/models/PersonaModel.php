<?php

class PersonaModel
{
    /** @var mysqli */
    private $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Obtener personas de un lote
     */
    public function obtenerPorLote(int $lote_id): array
    {
        $sql = 'SELECT *
                FROM `colacion_voucher`
                WHERE `lote_id` = ?
                ORDER BY `guest_nombre` ASC';

        $stmt = $this->db->prepare($sql);
        if (! $stmt) {
            error_log('MySQLi prepare error: '.$this->db->error);

            return [];
        }

        $stmt->bind_param('i', $lote_id);
        $stmt->execute();

        $res = $stmt->get_result();

        return $res->fetch_all(MYSQLI_ASSOC) ?: [];
    }

    /**
     * Obtener una persona por ID
     */
    public function obtener(int $id): ?array
    {
        $sql = 'SELECT *
                FROM colacion_voucher
                WHERE id = ?
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        if (! $stmt) {
            return null;
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();

        $res = $stmt->get_result();

        return $res->fetch_assoc() ?: null;
    }

    /**
     * Crear persona
     */
    public function crear(array $datos): bool
    {
        $sql = 'INSERT INTO colacion_voucher
                (lote_id, guest_rut, guest_nombre, guest_habitacion)
                VALUES (?, ?, ?, ?)';

        $stmt = $this->db->prepare($sql);
        if (! $stmt) {
            return false;
        }

        $stmt->bind_param(
            'isss',
            $datos['lote_id'],
            $datos['guest_rut'],
            $datos['guest_nombre'],
            $datos['guest_habitacion']
        );

        return $stmt->execute();
    }

    /**
     * Actualizar persona
     */
    public function actualizar(int $id, array $datos): bool
    {
        $sql = 'UPDATE colacion_voucher
                SET guest_rut = ?,
                    guest_nombre = ?,
                    guest_habitacion = ?
                WHERE id = ?';

        // =======================
        //  MODO DEBUG OPCIONAL
        // =======================
        if (isset($_GET['debug']) && $_GET['debug'] == 1) {
            $sqlFinal = sprintf(
                "UPDATE colacion_voucher SET guest_rut='%s', guest_nombre='%s', guest_habitacion='%s' WHERE id=%d",
                $this->db->real_escape_string($datos['guest_rut']),
                $this->db->real_escape_string($datos['guest_nombre']),
                $this->db->real_escape_string($datos['guest_habitacion']),
                $id
            );

            echo "<pre style='background:#222;color:#0f0;padding:20px;border:2px solid #0f0'>
SQL DEBUG MODE (NO EJECUTADO)
$sqlFinal
</pre>";
            exit;
        }

        $stmt = $this->db->prepare($sql);
        if (! $stmt) {
            error_log('MySQLi UPDATE error: '.$this->db->error);

            return false;
        }

        $stmt->bind_param(
            'sssi',
            $datos['guest_rut'],
            $datos['guest_nombre'],
            $datos['guest_habitacion'],
            $id
        );

        return $stmt->execute();
    }

    /**
     * Eliminar persona
     */
    public function eliminar(int $id): bool
    {
        $sql = 'DELETE FROM `colacion_voucher` WHERE `id` = ?';

        $stmt = $this->db->prepare($sql);
        if (! $stmt) {
            return false;
        }

        $stmt->bind_param('i', $id);

        return $stmt->execute();
    }
}
