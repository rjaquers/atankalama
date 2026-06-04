<?php
/**
 * Modelo de Registro Semanal de Huéspedes.
 *
 * Gestiona el registro semanal de huéspedes hospedados por contrato
 * y por hotel en la tabla doc_contract_weekly_guests.
 * Usado para contratos con pricing_mode = 'por_persona'.
 * Calcula automáticamente el monto según el tier aplicable.
 *
 * @package App\Models
 */
class WeeklyGuestModel extends Model
{
    /**
     * Obtiene los registros semanales de un contrato.
     *
     * @param  int         $contractId ID del contrato
     * @param  int|null    $year       Filtrar por año
     * @param  int|null    $hotelId    Filtrar por hotel
     * @return array Lista de registros con nombre de hotel y tier
     */
    public function getByContractId($contractId, $year = null, $hotelId = null)
    {
        $where = ["wg.contract_id = ?"];
        $params = [(int)$contractId];
        $types = "i";

        if ($year) {
            $where[] = "wg.year = ?";
            $params[] = (int)$year;
            $types .= "i";
        }

        if ($hotelId) {
            $where[] = "wg.hotel_id = ?";
            $params[] = (int)$hotelId;
            $types .= "i";
        }

        $sql = "
            SELECT wg.*, h.name AS hotel_name, h.code AS hotel_code,
                   t.min_guests AS tier_min, t.max_guests AS tier_max,
                   t.price_per_person AS tier_price,
                   u.name AS registered_by_name
            FROM doc_contract_weekly_guests wg
            JOIN doc_hotels h ON h.id = wg.hotel_id
            LEFT JOIN doc_contract_tiers t ON t.id = wg.tier_applied
            LEFT JOIN doc_users u ON u.id = wg.registered_by
            WHERE " . implode(" AND ", $where) . "
            ORDER BY wg.year DESC, wg.week_number DESC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }
    // Fin de la función getByContractId()

    /**
     * Registra huéspedes de una semana.
     *
     * Qué hace:
     * - Inserta o actualiza (UPSERT) el registro semanal
     * - Usa la constraint UNIQUE (contract_id, hotel_id, year, week_number)
     *
     * @param  array $data Datos:
     *   - contract_id   (int)
     *   - hotel_id      (int)
     *   - year          (int)
     *   - week_number   (int)
     *   - guest_count   (int)
     *   - amount_charged (float) Calculado por el service
     *   - tier_applied  (int|null)
     *   - notes         (string|null)
     * @param  int $userId ID del usuario que registra
     * @return int|false ID del registro o false
     */
    public function register($data, $userId)
    {
        $tierApplied = !empty($data['tier_applied']) ? (int)$data['tier_applied'] : null;
        $notes = $data['notes'] ?? null;

        $stmt = $this->conn->prepare("
            INSERT INTO doc_contract_weekly_guests(contract_id, hotel_id, year, week_number,
                guest_count, amount_charged, tier_applied, notes, registered_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                guest_count = VALUES(guest_count),
                amount_charged = VALUES(amount_charged),
                tier_applied = VALUES(tier_applied),
                notes = VALUES(notes),
                registered_by = VALUES(registered_by)
        ");
        $stmt->bind_param("iiiiidisi",
            $data['contract_id'],
            $data['hotel_id'],
            $data['year'],
            $data['week_number'],
            $data['guest_count'],
            $data['amount_charged'],
            $tierApplied,
            $notes,
            $userId
        );
        $stmt->execute();
        return $stmt->affected_rows > 0 ? $stmt->insert_id : false;
    }
    // Fin de la función register()

    /**
     * Obtiene el total de huéspedes de un contrato en un período.
     *
     * @param  int      $contractId ID del contrato
     * @param  int|null $year       Año (null = todos)
     * @return array    Totales: guest_count, amount_charged
     */
    public function getTotals($contractId, $year = null)
    {
        $where = ["contract_id = ?"];
        $params = [(int)$contractId];
        $types = "i";

        if ($year) {
            $where[] = "year = ?";
            $params[] = (int)$year;
            $types .= "i";
        }

        $sql = "SELECT COALESCE(SUM(guest_count), 0) AS total_guests,
                       COALESCE(SUM(amount_charged), 0) AS total_amount
                FROM doc_contract_weekly_guests
                WHERE " . implode(" AND ", $where);
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : ['total_guests' => 0, 'total_amount' => 0];
    }
    // Fin de la función getTotals()

    /**
     * Obtiene el total global de huéspedes hospedados (para dashboard).
     *
     * @param  int|null $year   Año
     * @param  int|null $month  Mes (se convierte a semanas)
     * @return int Total de huéspedes
     */
    public function getTotalGuestsGlobal($year = null, $month = null)
    {
        $where = ["1=1"];
        $params = [];
        $types = "";

        if ($year) {
            $where[] = "year = ?";
            $params[] = (int)$year;
            $types .= "i";
        }

        $sql = "SELECT COALESCE(SUM(guest_count), 0) AS total
                FROM doc_contract_weekly_guests
                WHERE " . implode(" AND ", $where);
        
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            app_log("Error al preparar consulta global de huéspedes: " . $this->conn->error);
            return 0;
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : ['total' => 0];
        return (int)($row['total'] ?? 0);
    }
    // Fin de la función getTotalGuestsGlobal()
}
