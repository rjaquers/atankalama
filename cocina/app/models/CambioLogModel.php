<?php

require_once __DIR__ . '/../config/db.php';

class CambioLogModel
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance();
    }

    // ─────────────────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────────────────

    /**
     * Registra un cambio en el log de auditoría.
     *
     * @param string $tabla   Tabla afectada: 'coci_comandas', 'coci_voucher_clientes', 'coci_vouchers_genericos'
     * @param string $campo   Campo o acción: 'cantidad_personas', 'voucher_agregado', 'voucher_eliminado', etc.
     */
    public function registrar(
        int     $comandaId,
        string  $tabla,
        string  $campo,
        ?string $valorAnterior,
        ?string $valorNuevo,
        string  $emailUsuario,
        ?string $ip        = null,
        ?int    $reservaId = null
    ): void {
        $stmt = $this->conn->prepare(
            "INSERT INTO coci_cambios_log
                (comanda_id, reserva_id, tabla, campo, valor_anterior, valor_nuevo, email_usuario, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $comandaId, $reservaId, $tabla, $campo,
            $valorAnterior, $valorNuevo, $emailUsuario, $ip,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────────────────────

    /** Historial completo de una comanda, del más reciente al más antiguo. */
    public function obtenerPorComanda(int $comandaId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM coci_cambios_log
             WHERE comanda_id = ?
             ORDER BY created_at DESC"
        );
        $stmt->execute([$comandaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Historial completo de todos los días de una reserva. */
    public function obtenerPorReserva(int $reservaId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT cl.*, c.fecha, c.tipo_servicio
             FROM coci_cambios_log cl
             INNER JOIN coci_comandas c ON c.id = cl.comanda_id
             WHERE cl.reserva_id = ?
             ORDER BY cl.created_at DESC"
        );
        $stmt->execute([$reservaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─────────────────────────────────────────────────────────
    // ETIQUETAS LEGIBLES (para las vistas)
    // ─────────────────────────────────────────────────────────

    /** Convierte el campo técnico en texto legible para mostrar en la vista. */
    public static function etiquetaCampo(string $campo): string
    {
        return match ($campo) {
            'cantidad_personas'          => 'Cantidad de personas',
            'hora_servicio'              => 'Hora de servicio',
            'observaciones'              => 'Observaciones',
            'es_para_llevar'             => 'Para llevar',
            'nombre_hotel'               => 'Hotel',
            'nombre_empresa'             => 'Empresa',
            'nombre_contacto'            => 'Contacto',
            'voucher_agregado'           => 'Voucher nominal agregado',
            'voucher_editado'            => 'Voucher nominal editado',
            'voucher_eliminado'          => 'Voucher nominal eliminado',
            'vouchers_importados'        => 'Vouchers importados desde Excel',
            'vouchers_genericos'         => 'Vouchers genéricos generados',
            default                      => $campo,
        };
    }
}
