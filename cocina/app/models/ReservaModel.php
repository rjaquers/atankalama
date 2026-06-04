<?php

require_once __DIR__ . '/../config/db.php';

class ReservaModel
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance();
    }

    // ─────────────────────────────────────────────────────────
    // CRUD — coci_reservas
    // ─────────────────────────────────────────────────────────

    public function crear(
        string  $nombre,
        string  $fechaDesde,
        string  $fechaHasta,
        ?int    $companyId      = null,
        ?string $nombreEmpresa  = null,
        ?string $observaciones  = null
    ): int {
        $stmt = $this->conn->prepare(
            "INSERT INTO coci_reservas
                (nombre, fecha_desde, fecha_hasta, company_id, nombre_empresa, observaciones)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$nombre, $fechaDesde, $fechaHasta, $companyId, $nombreEmpresa, $observaciones]);
        return (int) $this->conn->lastInsertId();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM coci_reservas WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function actualizar(
        int     $id,
        string  $nombre,
        string  $fechaDesde,
        string  $fechaHasta,
        ?int    $companyId,
        ?string $nombreEmpresa,
        ?string $observaciones
    ): void {
        $stmt = $this->conn->prepare(
            "UPDATE coci_reservas
             SET nombre = ?, fecha_desde = ?, fecha_hasta = ?,
                 company_id = ?, nombre_empresa = ?, observaciones = ?
             WHERE id = ?"
        );
        $stmt->execute([$nombre, $fechaDesde, $fechaHasta, $companyId, $nombreEmpresa, $observaciones, $id]);
    }

    public function eliminar(int $id): void
    {
        // Desvincular comandas antes de eliminar
        $this->conn->prepare(
            "UPDATE coci_comandas SET reserva_id = NULL WHERE reserva_id = ?"
        )->execute([$id]);

        $this->conn->prepare(
            "DELETE FROM coci_reservas WHERE id = ?"
        )->execute([$id]);
    }

    /** Lista todas las reservas ordenadas por fecha de inicio descendente. */
    public function listar(): array
    {
        return $this->conn->query(
            "SELECT * FROM coci_reservas ORDER BY fecha_desde DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─────────────────────────────────────────────────────────
    // COMANDAS — vínculo
    // ─────────────────────────────────────────────────────────

    /** Asigna una comanda a esta reserva. */
    public function vincularComanda(int $comandaId, int $reservaId): void
    {
        $this->conn->prepare(
            "UPDATE coci_comandas SET reserva_id = ? WHERE id = ?"
        )->execute([$reservaId, $comandaId]);
    }

    /** Quita una comanda de cualquier reserva. */
    public function desvincularComanda(int $comandaId): void
    {
        $this->conn->prepare(
            "UPDATE coci_comandas SET reserva_id = NULL WHERE id = ?"
        )->execute([$comandaId]);
    }

    /**
     * Devuelve todas las comandas de una reserva, ordenadas por fecha.
     * Incluye el conteo de vouchers nominales y genéricos ya generados.
     */
    public function obtenerComandasDeReserva(int $reservaId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT c.*,
                    COALESCE(vn.total, 0) AS total_nominales,
                    COALESCE(vg.total, 0) AS total_genericos,
                    COALESCE(il.ultima_impresion, NULL) AS ultima_impresion
             FROM coci_comandas c
             LEFT JOIN (
                 SELECT comanda_id, COUNT(*) AS total
                 FROM coci_voucher_clientes
                 GROUP BY comanda_id
             ) vn ON vn.comanda_id = c.id
             LEFT JOIN (
                 SELECT comanda_id, COUNT(*) AS total
                 FROM coci_vouchers_genericos
                 GROUP BY comanda_id
             ) vg ON vg.comanda_id = c.id
             LEFT JOIN (
                 SELECT comanda_id, MAX(created_at) AS ultima_impresion
                 FROM coci_impresiones_log
                 GROUP BY comanda_id
             ) il ON il.comanda_id = c.id
             WHERE c.reserva_id = ?
             ORDER BY c.fecha ASC, c.hora_servicio ASC"
        );
        $stmt->execute([$reservaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Dado un comanda_id, devuelve la reserva a la que pertenece (con sus comandas).
     * Retorna null si la comanda no tiene reserva.
     */
    public function obtenerPorComanda(int $comandaId): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT r.* FROM coci_reservas r
             INNER JOIN coci_comandas c ON c.reserva_id = r.id
             WHERE c.id = ? LIMIT 1"
        );
        $stmt->execute([$comandaId]);
        $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$reserva) {
            return null;
        }
        $reserva['comandas'] = $this->obtenerComandasDeReserva((int) $reserva['id']);
        return $reserva;
    }
}
