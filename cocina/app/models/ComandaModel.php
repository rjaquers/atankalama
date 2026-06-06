<?php

require_once __DIR__ . '/../config/db.php';

class ComandaModel
{
    private PDO $conn;
    private PDO $tickets;

    public function __construct()
    {
        $this->conn    = Database::getInstance();
        $this->tickets = TicketsDatabase::getInstance();
        $this->ensureColumns();
    }

    /** Agrega columnas nuevas a coci_comandas si no existen (migración automática). */
    private function ensureColumns(): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        $db   = $this->conn->query('SELECT DATABASE()')->fetchColumn();
        $cols = $this->conn->query(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'coci_comandas'"
        )->fetchAll(PDO::FETCH_COLUMN);

        $missing = [
            'reserva_id' => 'INT NULL DEFAULT NULL',
            'project_id' => 'INT NULL DEFAULT NULL',
        ];

        foreach ($missing as $col => $def) {
            if (!in_array($col, $cols, true)) {
                $this->conn->exec("ALTER TABLE coci_comandas ADD COLUMN {$col} {$def}");
            }
        }
    }

    // ─────────────────────────────────────────────────────────
    // LECTURA — Dashboard
    // ─────────────────────────────────────────────────────────

    /** Comandas de un tipo para una fecha concreta (para el dashboard). */
    public function obtenerPorFechaYTipo(string $fecha, string $tipo): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM coci_comandas
             WHERE fecha = ? AND tipo_servicio = ?
             ORDER BY hora_servicio ASC, id ASC"
        );
        $stmt->execute([$fecha, $tipo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Totales agrupados por nombre_hotel para una fecha y tipo. */
    public function resumenPorHotel(string $fecha, string $tipo): array
    {
        $stmt = $this->conn->prepare(
            "SELECT nombre_hotel,
                    SUM(cantidad_personas) AS total_personas
             FROM coci_comandas
             WHERE fecha = ? AND tipo_servicio = ?
             GROUP BY nombre_hotel
             ORDER BY nombre_hotel ASC"
        );
        $stmt->execute([$fecha, $tipo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Total general de personas para una fecha y tipo. */
    public function totalPersonas(string $fecha, string $tipo): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COALESCE(SUM(cantidad_personas), 0)
             FROM coci_comandas
             WHERE fecha = ? AND tipo_servicio = ?"
        );
        $stmt->execute([$fecha, $tipo]);
        return (int) $stmt->fetchColumn();
    }

    // ─────────────────────────────────────────────────────────
    // LECTURA — Gestión
    // ─────────────────────────────────────────────────────────

    /** Lista todas las comandas de una fecha (para pantalla de gestión). */
    public function obtenerPorFecha(string $fecha): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM coci_comandas
             WHERE fecha = ?
             ORDER BY tipo_servicio, hora_servicio ASC"
        );
        $stmt->execute([$fecha]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Obtiene una comanda por ID. */
    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM coci_comandas WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Fechas futuras con desayunos registrados para una empresa.
     * Útil para mostrar al recepcionista qué días ya están cubiertos.
     */
    public function fechasDesayunoPorEmpresa(int $companyId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT fecha, cantidad_personas, observaciones, es_para_llevar
             FROM coci_comandas
             WHERE company_id = ? AND tipo_servicio = 'desayuno'
               AND fecha >= CURDATE()
             ORDER BY fecha ASC"
        );
        $stmt->execute([$companyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─────────────────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────────────────

    /**
     * Inserta una comanda para una sola fecha.
     * Retorna el ID insertado.
     */
    public function insertar(
        string  $fecha,
        string  $tipoServicio,
        string  $nombreHotel,
        string  $tipoSolicitante,
        ?int    $companyId,
        ?int    $contractId,
        ?string $nombreEmpresa,
        ?string $nombreContacto,
        int     $cantidadPersonas,
        ?string $horaServicio,
        ?string $observaciones,
        int     $esParaLlevar  = 0,
        string  $origen        = 'programada',
        ?int    $ordenId       = null,
        ?int    $reservaId     = null,
        ?int    $projectId     = null
    ): int {
        $stmt = $this->conn->prepare(
            "INSERT INTO coci_comandas
               (fecha, tipo_servicio, nombre_hotel, tipo_solicitante,
                company_id, contract_id, nombre_empresa, nombre_contacto,
                cantidad_personas, hora_servicio, observaciones,
                es_para_llevar, origen, orden_id, reserva_id, project_id)
             VALUES
               (?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $fecha, $tipoServicio, $nombreHotel, $tipoSolicitante,
            $companyId, $contractId, $nombreEmpresa, $nombreContacto,
            $cantidadPersonas, $horaServicio, $observaciones,
            $esParaLlevar, $origen, $ordenId, $reservaId, $projectId
        ]);
        return (int) $this->conn->lastInsertId();
    }

    /**
     * Inserta la misma comanda para un rango de fechas.
     * Retorna array de IDs insertados.
     * Omite fechas que ya tengan comanda del mismo tipo para la misma empresa/contacto.
     */
    public function insertarRango(
        array   $fechas,
        string  $tipoServicio,
        string  $nombreHotel,
        string  $tipoSolicitante,
        ?int    $companyId,
        ?int    $contractId,
        ?string $nombreEmpresa,
        ?string $nombreContacto,
        int     $cantidadPersonas,
        ?string $horaServicio,
        ?string $observaciones,
        int     $esParaLlevar = 0,
        ?int    $reservaId    = null,
        ?int    $projectId    = null
    ): array {
        $ids = [];
        foreach ($fechas as $fecha) {
            $ids[] = $this->insertar(
                $fecha, $tipoServicio, $nombreHotel, $tipoSolicitante,
                $companyId, $contractId, $nombreEmpresa, $nombreContacto,
                $cantidadPersonas, $horaServicio, $observaciones, $esParaLlevar,
                'programada', null, $reservaId, $projectId
            );
        }
        return $ids;
    }

    /** Actualiza una comanda existente. */
    public function actualizar(
        int     $id,
        int     $cantidadPersonas,
        ?string $horaServicio,
        ?string $observaciones,
        int     $esParaLlevar,
        string  $nombreHotel,
        ?string $nombreEmpresa,
        ?string $nombreContacto
    ): void {
        $stmt = $this->conn->prepare(
            "UPDATE coci_comandas
             SET cantidad_personas = ?,
                 hora_servicio     = ?,
                 observaciones     = ?,
                 es_para_llevar    = ?,
                 nombre_hotel      = ?,
                 nombre_empresa    = ?,
                 nombre_contacto   = ?
             WHERE id = ?"
        );
        $stmt->execute([
            $cantidadPersonas, $horaServicio, $observaciones,
            $esParaLlevar, $nombreHotel, $nombreEmpresa, $nombreContacto,
            $id,
        ]);
    }

    /** Actualiza solo la cantidad de personas de una comanda. */
    public function actualizarCantidad(int $id, int $cantidad): void
    {
        $this->conn->prepare(
            "UPDATE coci_comandas SET cantidad_personas = ? WHERE id = ?"
        )->execute([$cantidad, $id]);
    }

    /**
     * Devuelve comandas que aún no pertenecen a ninguna reserva.
     * Útil para el formulario de crear reserva.
     */
    public function obtenerSinReserva(): array
    {
        return $this->conn->query(
            "SELECT * FROM coci_comandas
             WHERE reserva_id IS NULL
             ORDER BY fecha DESC, hora_servicio ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Elimina una comanda por ID. */
    public function eliminar(int $id): void
    {
        $this->conn->prepare("DELETE FROM coci_comandas WHERE id = ?")
            ->execute([$id]);
    }

    // ─────────────────────────────────────────────────────────
    // VOUCHER CLIENTES
    // ─────────────────────────────────────────────────────────

    /** Lista los clientes con voucher de una comanda. */
    public function obtenerClientes(int $comandaId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM coci_voucher_clientes
             WHERE comanda_id = ? ORDER BY id ASC"
        );
        $stmt->execute([$comandaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Inserta un cliente para una comanda. */
    public function insertarCliente(int $comandaId, ?string $rut, string $nombre, ?string $empresa): int
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO coci_voucher_clientes (comanda_id, rut, nombre, empresa)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$comandaId, $rut, $nombre, $empresa]);
        return (int) $this->conn->lastInsertId();
    }

    /** Elimina todos los clientes de una comanda (para re-importar Excel). */
    public function eliminarClientes(int $comandaId): void
    {
        $this->conn->prepare(
            "DELETE FROM coci_voucher_clientes WHERE comanda_id = ?"
        )->execute([$comandaId]);
    }

    // ─────────────────────────────────────────────────────────
    // HELPERS — BD de empresas (tickets)
    // ─────────────────────────────────────────────────────────

    /** Obtiene el nombre de una empresa por company_id. */
    public function nombreEmpresa(int $companyId): string
    {
        $stmt = $this->tickets->prepare(
            "SELECT business_name FROM doc_companies WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$companyId]);
        return (string) ($stmt->fetchColumn() ?: '');
    }

    /**
     * Registra un archivo de respaldo asociado a una comanda
     */
    public function registrarRespaldo(int $comandaId, string $ruta, ?string $tipo): bool
    {
        $sql = 'INSERT INTO coci_comanda_respaldos (comanda_id, ruta_archivo, tipo_archivo) 
                VALUES (?, ?, ?)';
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$comandaId, $ruta, $tipo]);
    }

    /**
     * Obtiene los respaldos de una comanda
     */
    public function obtenerRespaldos(int $comandaId): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM coci_comanda_respaldos WHERE comanda_id = ?');
        $stmt->execute([$comandaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna un mapa [comanda_id => ['total' => N, 'impresos' => M]] para un conjunto de IDs.
     * Suma vouchers nominales (coci_voucher_clientes) y genéricos (coci_vouchers_genericos).
     */
    public function contarVouchersPorIds(array $ids): array
    {
        if (empty($ids)) return [];

        $ph   = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->conn->prepare(
            "SELECT comanda_id,
                    COUNT(*)      AS total,
                    SUM(impreso)  AS impresos
             FROM (
                 SELECT comanda_id, impreso FROM coci_voucher_clientes   WHERE comanda_id IN ({$ph})
                 UNION ALL
                 SELECT comanda_id, impreso FROM coci_vouchers_genericos WHERE comanda_id IN ({$ph})
             ) t
             GROUP BY comanda_id"
        );
        $stmt->execute(array_merge(array_values($ids), array_values($ids)));

        $mapa = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $mapa[(int)$row['comanda_id']] = [
                'total'    => (int)$row['total'],
                'impresos' => (int)$row['impresos'],
            ];
        }
        return $mapa;
    }
}
