<?php

// models/Ticket.php
declare(strict_types=1);

class Ticket
{
    /** @var mysqli */
    private $db;

    public function __construct()
    {
        global $mysqli; // viene de conec6.php
        $this->db = $mysqli;
    }

    /**
     * Lista tickets con filtros simples.
     * Parámetros soportados: q (texto), status, mode, from (Y-m-d), to (Y-m-d), limit, offset
     * Retorna: array<int, array<string, mixed>>
     */
    public function listar(array $params = []): array
    {
        $where = [];
        $bindT = '';
        $bindV = [];

        // Búsqueda rápida por public_code o guest_name
        if (!empty($params['q'])) {
            $where[] = "(public_code LIKE CONCAT('%', ?, '%') OR guest_name LIKE CONCAT('%', ?, '%'))";
            $bindT .= 'ss';
            $bindV[] = $params['q'];
            $bindV[] = $params['q'];
        }

        if (!empty($params['status'])) {
            $where[] = 'status = ?';
            $bindT .= 's';
            $bindV[] = $params['status'];
        }

        if (!empty($params['mode'])) {
            $where[] = 'mode = ?';
            $bindT .= 's';
            $bindV[] = $params['mode'];
        }

        if (!empty($params['from'])) {
            $where[] = "created_at >= CONCAT(?, ' 00:00:00')";
            $bindT .= 's';
            $bindV[] = $params['from'];
        }

        if (!empty($params['to'])) {
            $where[] = "created_at <= CONCAT(?, ' 23:59:59')";
            $bindT .= 's';
            $bindV[] = $params['to'];
        }

        $sql = 'SELECT 
                    `id`, `public_code`, `mode`, `guest_name`, `item_type`, `location_label`, `notes`,
                    `status`, `created_at`, `retrieved_at`, `print_count`
                FROM `tickets`';

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY created_at DESC';

        $limit = isset($params['limit']) ? max(1, (int) $params['limit']) : 50;
        $offset = isset($params['offset']) ? max(0, (int) $params['offset']) : 0;

        $sql .= ' LIMIT ? OFFSET ?';
        $bindT .= 'ii';
        $bindV[] = $limit;
        $bindV[] = $offset;

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('DB_PREPARE_FAILED: ' . $this->db->error);
        }

        $stmt->bind_param($bindT, ...$bindV);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res->fetch_all(MYSQLI_ASSOC) ?: [];

        $stmt->close();

        return $data;
    }

    /**
     * Cuenta total para la paginación con los mismos filtros que listar()
     */
    public function contar(array $params = []): int
    {
        $where = [];
        $bindT = '';
        $bindV = [];

        if (!empty($params['q'])) {
            $where[] = "(public_code LIKE CONCAT('%', ?, '%') OR guest_name LIKE CONCAT('%', ?, '%'))";
            $bindT .= 'ss';
            $bindV[] = $params['q'];
            $bindV[] = $params['q'];
        }
        if (!empty($params['status'])) {
            $where[] = 'status = ?';
            $bindT .= 's';
            $bindV[] = $params['status'];
        }
        if (!empty($params['mode'])) {
            $where[] = 'mode = ?';
            $bindT .= 's';
            $bindV[] = $params['mode'];
        }
        if (!empty($params['from'])) {
            $where[] = "created_at >= CONCAT(?, ' 00:00:00')";
            $bindT .= 's';
            $bindV[] = $params['from'];
        }
        if (!empty($params['to'])) {
            $where[] = "created_at <= CONCAT(?, ' 23:59:59')";
            $bindT .= 's';
            $bindV[] = $params['to'];
        }

        $sql = 'SELECT COUNT(*) AS total FROM tickets';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('DB_PREPARE_FAILED: ' . $this->db->error);
        }

        if ($bindT) {
            $stmt->bind_param($bindT, ...$bindV);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        return (int) ($row['total'] ?? 0);
    }

    /**
     * Obtener por ID (para futuros usos)
     * Retorna array o 'sin datos'
     */
    public function obtenerPorId(int $id)
    {
        $stmt = $this->db->prepare('SELECT * FROM tickets WHERE id = ?');
        if (!$stmt) {
            throw new RuntimeException('DB_PREPARE_FAILED: ' . $this->db->error);
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res->fetch_assoc();
        $stmt->close();

        return $data ?: 'sin datos';
    }

    public function incrementarImpresion(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE tickets SET print_count = print_count + 1 WHERE id = ?');
        if (!$stmt) {
            throw new RuntimeException('DB_PREPARE_FAILED: ' . $this->db->error);
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }

    // models/Ticket.php (agrega dentro de la clase Ticket)

    private function generarPublicCode(): string
    {
        // Ej: BAG-12345 (5 dígitos)
        return 'BAG-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    /**
     * Crea un ticket y retorna el ID insertado
     * Campos obligatorios: mode
     * Campos opcionales: guest_name, item_type, location_label, notes
     */
    public function crear(array $data): int
    {
        $mode = ($data['mode'] ?? 'custodia') === 'perdido' ? 'perdido' : 'custodia';
        $guest_name = $data['guest_name'] ?? null;
        $item_type = $data['item_type'] ?? null;
        $location_label = $data['location_label'] ?? null;
        $notes = $data['notes'] ?? null;

        $status = 'en_custodia';
        $created_at = (new DateTime('now', new DateTimeZone('America/Santiago')))->format('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

        // Generar código único con reintentos por si choca el UNIQUE
        $public_code = null;
        $maxTries = 5;
        for ($i = 0; $i < $maxTries; $i++) {
            $public_code = $this->generarPublicCode();

            $sql = 'INSERT INTO tickets
            (public_code, mode, guest_name, item_type, location_label, notes, status, created_at, created_by_ip, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, INET6_ATON(?), ?)';

            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new RuntimeException('DB_PREPARE_FAILED: ' . $this->db->error);
            }

            // Tipos: 10 strings
            $stmt->bind_param(
                'ssssssssss',
                $public_code,
                $mode,
                $guest_name,
                $item_type,
                $location_label,
                $notes,
                $status,
                $created_at,
                $ip,
                $ua
            );

            if (!$stmt->execute()) {
                $errno = $this->db->errno;
                $error = $this->db->error;
                $stmt->close();

                // 1062 = duplicate entry (por UNIQUE en public_code)
                if ($errno === 1062 && $i < $maxTries - 1) {
                    // reintentar con otro código
                    continue;
                }
                throw new RuntimeException("DB_EXECUTE_FAILED ($errno): $error");
            }

            $insertId = (int) $this->db->insert_id;
            $stmt->close();

            return $insertId;
        }

        throw new RuntimeException('No fue posible generar un public_code único tras varios intentos.');
    }
}
