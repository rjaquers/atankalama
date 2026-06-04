<?php

require_once __DIR__ . '/../config/db.php';

class ImpresionLogModel
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance();
    }

    // ─────────────────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────────────────

    public function registrar(
        int     $comandaId,
        string  $emailUsuario,
        int     $cantidadNominales,
        int     $cantidadGenericos,
        ?string $ip        = null,
        ?int    $reservaId = null
    ): void {
        $stmt = $this->conn->prepare(
            "INSERT INTO coci_impresiones_log
                (comanda_id, reserva_id, email_usuario, cantidad_nominales, cantidad_genericos, ip_address)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$comandaId, $reservaId, $emailUsuario, $cantidadNominales, $cantidadGenericos, $ip]);
    }

    // ─────────────────────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────────────────────

    /** Historial de impresiones de una comanda, del más reciente al más antiguo. */
    public function obtenerPorComanda(int $comandaId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM coci_impresiones_log
             WHERE comanda_id = ?
             ORDER BY created_at DESC"
        );
        $stmt->execute([$comandaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Historial de impresiones de todos los días de una reserva. */
    public function obtenerPorReserva(int $reservaId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT il.*, c.fecha, c.tipo_servicio
             FROM coci_impresiones_log il
             INNER JOIN coci_comandas c ON c.id = il.comanda_id
             WHERE il.reserva_id = ?
             ORDER BY il.created_at DESC"
        );
        $stmt->execute([$reservaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Indica si una comanda ya fue impresa hoy. */
    public function fueImpresaHoy(int $comandaId): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM coci_impresiones_log
             WHERE comanda_id = ? AND DATE(created_at) = CURDATE()"
        );
        $stmt->execute([$comandaId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** Última vez que se imprimió una comanda (o null si nunca). */
    public function ultimaImpresion(int $comandaId): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM coci_impresiones_log
             WHERE comanda_id = ?
             ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute([$comandaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
