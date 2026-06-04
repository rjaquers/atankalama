<?php
/**
 * Modelo de Reservas de Espacios.
 *
 * Gestiona las operaciones CRUD sobre doc_space_bookings.
 * Incluye validación de traslapes y consultas por rango de fechas.
 *
 * @package App\Models
 */
class SpaceBookingModel extends Model
{
    /**
     * Obtiene todas las reservas con filtros opcionales.
     *
     * @param  array $filters Filtros opcionales
     * @return array Lista de reservas
     */
    public function getAll($filters = [])
    {
        $where = ["b.active = 1"];
        $params = [];
        $types = "";

        if (!empty($filters['booking_status'])) {
            $where[] = "b.booking_status = ?";
            $params[] = $filters['booking_status'];
            $types .= "s";
        }
        if (!empty($filters['charge_status'])) {
            $where[] = "b.charge_status = ?";
            $params[] = $filters['charge_status'];
            $types .= "s";
        }
        if (!empty($filters['space_id'])) {
            $where[] = "b.space_id = ?";
            $params[] = (int)$filters['space_id'];
            $types .= "i";
        }
        if (!empty($filters['company_id'])) {
            $where[] = "b.company_id = ?";
            $params[] = (int)$filters['company_id'];
            $types .= "i";
        }
        if (!empty($filters['date_from'])) {
            $where[] = "b.start_datetime >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
            $types .= "s";
        }
        if (!empty($filters['date_to'])) {
            $where[] = "b.end_datetime <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
            $types .= "s";
        }

        $sql = "
            SELECT b.*, s.name AS space_name, s.space_type, s.code AS space_code,
                   c.business_name AS company_name, ct.code AS contract_code,
                   u.name AS created_by_name
            FROM doc_space_bookings b
            LEFT JOIN doc_spaces s ON s.id = b.space_id
            LEFT JOIN doc_companies c ON c.id = b.company_id
            LEFT JOIN doc_contracts ct ON ct.id = b.contract_id
            LEFT JOIN doc_users u ON u.id = b.created_by
            WHERE " . implode(" AND ", $where) . "
            ORDER BY b.start_datetime DESC
        ";
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
    // Fin de la función getAll()

    /**
     * Busca una reserva por su ID.
     *
     * @param  int $id ID de la reserva
     * @return array|null
     */
    public function getById($id)
    {
        $stmt = $this->conn->prepare("
            SELECT b.*, s.name AS space_name, s.space_type, s.code AS space_code, s.main_image,
                   s.capacity AS space_capacity, s.restrictions AS space_restrictions,
                   c.business_name AS company_name, c.rut AS company_rut,
                   ct.code AS contract_code,
                   u.name AS created_by_name, u2.name AS cancelled_by_name
            FROM doc_space_bookings b
            LEFT JOIN doc_spaces s ON s.id = b.space_id
            LEFT JOIN doc_companies c ON c.id = b.company_id
            LEFT JOIN doc_contracts ct ON ct.id = b.contract_id
            LEFT JOIN doc_users u ON u.id = b.created_by
            LEFT JOIN doc_users u2 ON u2.id = b.cancelled_by
            WHERE b.id = ? AND b.active = 1
            LIMIT 1
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }
    // Fin de la función getById()

    /**
     * Crea una nueva reserva.
     *
     * @param  array $data   Datos de la reserva
     * @param  int   $userId ID del usuario que crea
     * @return int|false ID de la reserva creada o false
     */
    public function create($data, $userId)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO doc_space_bookings(
                folio, space_id, company_id, contract_id, client_name,
                booking_mode, start_datetime, end_datetime,
                qty_hours, qty_days, qty_months,
                base_price, discount, surcharge, total_price,
                is_free, free_reason, booking_status, notes_client, notes_internal,
                origin, created_by
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("siiissssdiiddddisssssi",
            $data['folio'],
            $data['space_id'],
            $data['company_id'],
            $data['contract_id'],
            $data['client_name'],
            $data['booking_mode'],
            $data['start_datetime'],
            $data['end_datetime'],
            $data['qty_hours'],
            $data['qty_days'],
            $data['qty_months'],
            $data['base_price'],
            $data['discount'],
            $data['surcharge'],
            $data['total_price'],
            $data['is_free'],
            $data['free_reason'],
            $data['booking_status'],
            $data['notes_client'],
            $data['notes_internal'],
            $data['origin'],
            $userId
        );
        $stmt->execute();
        return $stmt->affected_rows > 0 ? $stmt->insert_id : false;
    }
    // Fin de la función create()

    /**
     * Actualiza una reserva existente.
     *
     * @param  int   $id     ID de la reserva
     * @param  array $data   Datos a actualizar
     * @param  int   $userId ID del usuario
     * @return bool
     */
    public function update($id, $data, $userId)
    {
        $stmt = $this->conn->prepare("
            UPDATE doc_space_bookings SET
                space_id = ?, company_id = ?, contract_id = ?, client_name = ?,
                booking_mode = ?, start_datetime = ?, end_datetime = ?,
                qty_hours = ?, qty_days = ?, qty_months = ?,
                base_price = ?, discount = ?, surcharge = ?, total_price = ?,
                is_free = ?, free_reason = ?, booking_status = ?,
                notes_client = ?, notes_internal = ?, updated_by = ?
            WHERE id = ?
        ");
        $stmt->bind_param("iiissssdiiddddissssii",
            $data['space_id'],
            $data['company_id'],
            $data['contract_id'],
            $data['client_name'],
            $data['booking_mode'],
            $data['start_datetime'],
            $data['end_datetime'],
            $data['qty_hours'],
            $data['qty_days'],
            $data['qty_months'],
            $data['base_price'],
            $data['discount'],
            $data['surcharge'],
            $data['total_price'],
            $data['is_free'],
            $data['free_reason'],
            $data['booking_status'],
            $data['notes_client'],
            $data['notes_internal'],
            $userId,
            $id
        );
        $stmt->execute();
        return $stmt->affected_rows >= 0;
    }
    // Fin de la función update()

    /**
     * Actualiza solo el total_price de una reserva.
     *
     * @param  int   $id         ID de la reserva
     * @param  float $totalPrice Nuevo total
     * @return bool
     */
    public function updateTotalPrice($id, $totalPrice)
    {
        $stmt = $this->conn->prepare("UPDATE doc_space_bookings SET total_price = ? WHERE id = ?");
        $stmt->bind_param("di", $totalPrice, $id);
        $stmt->execute();
        return $stmt->affected_rows >= 0;
    }
    // Fin de la función updateTotalPrice()

    /**
     * Verifica si hay traslape de fechas para un espacio.
     *
     * Qué hace:
     * - Busca reservas activas que se crucen en el rango de fechas
     * - Excluye opcionalmente una reserva (para edición)
     *
     * @param  int         $spaceId  ID del espacio
     * @param  string      $start    Fecha/hora inicio
     * @param  string      $end      Fecha/hora fin
     * @param  int|null    $excludeId ID de reserva a excluir
     * @return array|null  Reserva en conflicto o null si no hay traslape
     */
    public function checkOverlap($spaceId, $start, $end, $excludeId = null)
    {
        $excludeSql = $excludeId ? "AND b.id != ?" : "";
        $stmt = $this->conn->prepare("
            SELECT b.id, b.folio, b.start_datetime, b.end_datetime, b.booking_status,
                   c.business_name AS company_name
            FROM doc_space_bookings b
            LEFT JOIN doc_companies c ON c.id = b.company_id
            WHERE b.space_id = ?
              AND b.active = 1
              AND b.booking_status IN ('confirmada','en_uso','borrador')
              AND b.start_datetime < ?
              AND b.end_datetime > ?
              {$excludeSql}
            LIMIT 1
        ");
        if ($excludeId) {
            $stmt->bind_param("issi", $spaceId, $end, $start, $excludeId);
        } else {
            $stmt->bind_param("iss", $spaceId, $end, $start);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }
    // Fin de la función checkOverlap()

    /**
     * Verifica si hay bloqueos para un espacio en un rango.
     *
     * @param  int    $spaceId ID del espacio
     * @param  string $start   Inicio
     * @param  string $end     Fin
     * @return array|null Bloqueo existente o null
     */
    public function checkBlockOverlap($spaceId, $start, $end)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM doc_space_blocks
            WHERE space_id = ?
              AND start_datetime < ?
              AND end_datetime > ?
            LIMIT 1
        ");
        $stmt->bind_param("iss", $spaceId, $end, $start);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }
    // Fin de la función checkBlockOverlap()

    /**
     * Obtiene reservas por rango de fechas para el calendario.
     *
     * @param  string      $start   Fecha inicio
     * @param  string      $end     Fecha fin
     * @param  int|null    $spaceId Filtrar por espacio
     * @return array
     */
    public function getByDateRange($start, $end, $spaceId = null)
    {
        $where = [
            "b.active = 1",
            "b.booking_status NOT IN ('cancelada')",
            "b.start_datetime < ?",
            "b.end_datetime > ?"
        ];
        $params = [$end . ' 23:59:59', $start . ' 00:00:00'];
        $types = "ss";

        if ($spaceId) {
            $where[] = "b.space_id = ?";
            $params[] = (int)$spaceId;
            $types .= "i";
        }

        $sql = "
            SELECT b.*, s.name AS space_name, s.space_type, s.code AS space_code, s.calendar_color,
                   c.business_name AS company_name
            FROM doc_space_bookings b
            LEFT JOIN doc_spaces s ON s.id = b.space_id
            LEFT JOIN doc_companies c ON c.id = b.company_id
            WHERE " . implode(" AND ", $where) . "
            ORDER BY b.start_datetime ASC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
    // Fin de la función getByDateRange()

    /**
     * Genera un folio único para una reserva.
     *
     * @return string Folio generado (ej: RES-20260323-001)
     */
    public function generateFolio()
    {
        $date = date('Ymd');
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM doc_space_bookings WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        $res = $stmt->get_result();
        $count = $res->fetch_assoc()['total'] + 1;
        return 'RES-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
    // Fin de la función generateFolio()

    /**
     * Cambia el estado de una reserva.
     *
     * @param  int    $id     ID de la reserva
     * @param  string $status Nuevo estado
     * @param  int    $userId ID del usuario
     * @return bool
     */
    public function changeStatus($id, $status, $userId)
    {
        $stmt = $this->conn->prepare("UPDATE doc_space_bookings SET booking_status = ?, updated_by = ? WHERE id = ?");
        $stmt->bind_param("sii", $status, $userId, $id);
        $stmt->execute();
        $ok = $stmt->affected_rows >= 0;
        
        if ($ok && $status === 'confirmada') {
            $this->syncFinance($id, $userId);
        }
        
        return $ok;
    }
    // Fin de la función changeStatus()

    /**
     * Sincroniza el cargo financiero de la reserva.
     * Si tiene contrato, genera un cargo pendiente en el contrato.
     * Si no tiene contrato, genera un cargo directo de reserva.
     */
    public function syncFinance($id, $userId)
    {
        $booking = $this->getById($id);
        if (!$booking) return false;

        // 1. Si está asociada a un contrato, generar/actualizar movimiento de cargo en el contrato
        if (!empty($booking['contract_id'])) {
            $paymentModel = new PaymentModel();
            
            // Verificar si ya existe cargo para esta reserva
            $stmt = $this->conn->prepare("SELECT id, amount FROM doc_contract_payments WHERE booking_id = ? AND active = 1 LIMIT 1");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();

            if ($existing) {
                // Actualizar monto si cambió
                if ((float)$existing['amount'] !== (float)$booking['total_price']) {
                    $stmtUpd = $this->conn->prepare("UPDATE doc_contract_payments SET amount = ?, contract_id = ? WHERE id = ?");
                    $stmtUpd->bind_param("dii", $booking['total_price'], $booking['contract_id'], $existing['id']);
                    $stmtUpd->execute();
                }
                return true;
            }

            $paymentData = [
                'contract_id'     => $booking['contract_id'],
                'booking_id'      => $id,
                'amount'          => $booking['total_price'],
                'payment_date'    => date('Y-m-d'),
                'payment_method'  => 'transferencia',
                'period_type'     => 'otro',
                'period_start'    => date('Y-m-d', strtotime($booking['start_datetime'])),
                'period_end'      => date('Y-m-d', strtotime($booking['end_datetime'])),
                'status'          => 'pendiente',
                'notes'           => "Cargo automático por reserva: " . $booking['folio'] . " (" . $booking['space_name'] . ")"
            ];
            $paymentModel->create($paymentData, $userId);
        } else {
            // 2. Si no tiene contrato, registrar/actualizar en doc_space_booking_charges
            $stmt = $this->conn->prepare("SELECT id, amount FROM doc_space_booking_charges WHERE booking_id = ? LIMIT 1");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();

            if ($existing) {
                // Actualizar monto si cambió
                if ((float)$existing['amount'] !== (float)$booking['total_price']) {
                    $desc = "Arriendo de " . $booking['space_name'] . " (" . $booking['folio'] . ")";
                    $stmtUpd = $this->conn->prepare("UPDATE doc_space_booking_charges SET amount = ?, description = ? WHERE id = ?");
                    $stmtUpd->bind_param("dsi", $booking['total_price'], $desc, $existing['id']);
                    $stmtUpd->execute();
                }
            } else {
                $desc = "Arriendo de " . $booking['space_name'] . " (" . $booking['folio'] . ")";
                $stmt = $this->conn->prepare("INSERT INTO doc_space_booking_charges (booking_id, description, amount, created_by) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isdi", $id, $desc, $booking['total_price'], $userId);
                $stmt->execute();
            }
        }
        return true;
    }
    // Fin de la función syncFinance()

    /**
     * Cancela una reserva con motivo.
     *
     * @param  int    $id     ID de la reserva
     * @param  string $reason Motivo de cancelación
     * @param  int    $userId ID del usuario
     * @return bool
     */
    public function cancel($id, $reason, $userId)
    {
        $stmt = $this->conn->prepare("
            UPDATE doc_space_bookings SET
                booking_status = 'cancelada', cancel_reason = ?,
                cancelled_by = ?, cancelled_at = NOW(), updated_by = ?
            WHERE id = ?
        ");
        $stmt->bind_param("siii", $reason, $userId, $userId, $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
    // Fin de la función cancel()
}
