<?php
/**
 * Modelo de Pagos.
 *
 * Gestiona los pagos parciales de contratos en doc_contract_payments.
 * Soporta pagos semanales, mensuales, quincenales y otros períodos.
 * Incluye cálculo de saldo pendiente y cuenta corriente.
 *
 * @package App\Models
 */
class PaymentModel extends Model
{
    /**
     * Obtiene todos los pagos de un contrato.
     *
     * @param  int    $contractId ID del contrato
     * @param  string|null $status Filtrar por estado
     * @return array  Lista de pagos con nombre del registrador
     */
    public function getByContractId($contractId, $status = null)
    {
        $where = ["p.contract_id = ?", "p.active = 1"];
        $params = [(int)$contractId];
        $types = "i";

        if ($status) {
            $where[] = "p.status = ?";
            $params[] = $status;
            $types .= "s";
        }

        $sql = "
            SELECT p.*, u.name AS registered_by_name,
                   sb.folio AS booking_folio
            FROM doc_contract_payments p
            LEFT JOIN doc_users u ON u.id = p.registered_by
            LEFT JOIN doc_space_bookings sb ON sb.id = p.booking_id
            WHERE " . implode(" AND ", $where) . "
            ORDER BY p.payment_date DESC
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
     * Busca un pago por su ID.
     *
     * @param  int $id ID del pago
     * @return array|null Datos del pago o null
     */
    public function getById($id)
    {
        $stmt = $this->conn->prepare("
            SELECT p.*, u.name AS registered_by_name
            FROM doc_contract_payments p
            LEFT JOIN doc_users u ON u.id = p.registered_by
            WHERE p.id = ? AND p.active = 1
            LIMIT 1
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }
    // Fin de la función getById()

    /**
     * Registra un nuevo pago parcial.
     *
     * @param  array $data Datos del pago:
     *   - contract_id     (int)
     *   - amount          (float)
     *   - payment_date    (string) Y-m-d
     *   - payment_method  (string)
     *   - reference_number (string|null)
     *   - period_type     (string)
     *   - period_start    (string|null) Y-m-d
     *   - period_end      (string|null) Y-m-d
     *   - status          (string)
     *   - notes           (string|null)
     * @param  int $userId ID del usuario que registra
     * @return int|false ID del pago creado o false
     */
    public function create($data, $userId)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO doc_contract_payments(contract_id, booking_id, amount, payment_date,
                payment_method, reference_number, period_type, period_start,
                period_end, status, notes, registered_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $refNumber = $data['reference_number'] ?? null;
        $periodStart = !empty($data['period_start']) ? $data['period_start'] : null;
        $periodEnd = !empty($data['period_end']) ? $data['period_end'] : null;
        $notes = $data['notes'] ?? null;

        $bookingId = !empty($data['booking_id']) ? (int)$data['booking_id'] : null;

        $stmt->bind_param("iidssssssssi",
            $data['contract_id'],
            $bookingId,
            $data['amount'],
            $data['payment_date'],
            $data['payment_method'],
            $refNumber,
            $data['period_type'],
            $periodStart,
            $periodEnd,
            $data['status'],
            $notes,
            $userId
        );
        $stmt->execute();
        return $stmt->affected_rows > 0 ? $stmt->insert_id : false;
    }
    // Fin de la función create()

    /**
     * Actualiza un pago existente.
     *
     * @param  int   $id   ID del pago
     * @param  array $data Datos a actualizar
     * @return bool  true si se actualizó
     */
    public function update($id, $data)
    {
        $refNumber = $data['reference_number'] ?? null;
        $periodStart = !empty($data['period_start']) ? $data['period_start'] : null;
        $periodEnd = !empty($data['period_end']) ? $data['period_end'] : null;
        $notes = $data['notes'] ?? null;

        $stmt = $this->conn->prepare("
            UPDATE doc_contract_payments
            SET amount = ?, payment_date = ?, payment_method = ?,
                reference_number = ?, period_type = ?, period_start = ?,
                period_end = ?, status = ?, notes = ?, booking_id = ?
            WHERE id = ? AND active = 1
        ");
        $bookingId = !empty($data['booking_id']) ? (int)$data['booking_id'] : null;

        $stmt->bind_param("dssssssssii",
            $data['amount'],
            $data['payment_date'],
            $data['payment_method'],
            $refNumber,
            $data['period_type'],
            $periodStart,
            $periodEnd,
            $data['status'],
            $notes,
            $bookingId,
            $id
        );
        $stmt->execute();
        return $stmt->affected_rows >= 0;
    }
    // Fin de la función update()

    /**
     * Anula un pago (soft delete + cambio de estado).
     *
     * @param  int  $id ID del pago
     * @return bool true si se anuló
     */
    public function void($id)
    {
        $stmt = $this->conn->prepare("
            UPDATE doc_contract_payments SET status = 'anulado' WHERE id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
    // Fin de la función void()

    /**
     * Calcula el total pagado de un contrato.
     *
     * Solo suma pagos con status = 'pagado' o 'parcial' y active = 1.
     *
     * @param  int   $contractId ID del contrato
     * @return float Total pagado
     */
    public function getTotalPaid($contractId)
    {
        $stmt = $this->conn->prepare("
            SELECT COALESCE(SUM(amount), 0) AS total
            FROM doc_contract_payments
            WHERE contract_id = ? AND active = 1 AND status IN ('pagado', 'parcial')
        ");
        $stmt->bind_param("i", $contractId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : ['total' => 0];
        return (float)$row['total'];
    }
    // Fin de la función getTotalPaid()

    /**
     * Suma de todos los cargos generados en un contrato (pagados o no).
     *
     * @param  int   $contractId ID del contrato
     * @return float Total cargado (deuda total histórica)
     */
    public function getTotalCharged($contractId)
    {
        $stmt = $this->conn->prepare("
            SELECT COALESCE(SUM(amount), 0) AS total
            FROM doc_contract_payments
            WHERE contract_id = ? AND active = 1 AND status = 'pendiente'
        ");
        $stmt->bind_param("i", $contractId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : ['total' => 0];
        return (float)$row['total'];
    }

    /**
     * Cuenta pagos pendientes en todo el sistema.
     *
     * @return int Total de pagos con status = 'pendiente'
     */
    public function countPending()
    {
        $res = $this->conn->query("
            SELECT COUNT(*) AS total FROM doc_contract_payments
            WHERE active = 1 AND status = 'pendiente'
        ");
        $row = $res ? $res->fetch_assoc() : ['total' => 0];
        return (int)$row['total'];
    }
    // Fin de la función countPending()

    /**
     * Obtiene pagos pendientes o vencidos de todos los contratos.
     *
     * @return array Lista de pagos con datos de contrato y empresa
     */
    public function getPendingPayments()
    {
        $res = $this->conn->query("
            SELECT p.*, c.code AS contract_code, co.business_name
            FROM doc_contract_payments p
            JOIN doc_contracts c ON c.id = p.contract_id
            JOIN doc_companies co ON co.id = c.company_id
            WHERE p.active = 1 AND p.status = 'pendiente'
            ORDER BY p.period_end ASC
        ");
        $rows = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }
    // Fin de la función getPendingPayments()

    /**
     * Suma total de pagos pendientes en el sistema.
     *
     * @return float Monto total pendiente
     */
    public function getTotalPendingAmount()
    {
        $res = $this->conn->query("
            SELECT COALESCE(SUM(amount), 0) AS total
            FROM doc_contract_payments
            WHERE active = 1 AND status = 'pendiente'
        ");
        $row = $res ? $res->fetch_assoc() : ['total' => 0];
        return (float)$row['total'];
    }
    // Fin de la función getTotalPendingAmount()

    /**
     * Suma total de cargos generados en el sistema para un año específico.
     * Representa la "Venta Bruta" o facturación total.
     *
     * @param  int|null $year Año
     * @return float Total cargado (pendiente)
     */
    public function getTotalChargedGlobal($year = null)
    {
        $where = ["active = 1", "status = 'pendiente'"];
        $params = [];
        $types = "";

        if ($year) {
            $where[] = "YEAR(payment_date) = ?";
            $params[] = (int)$year;
            $types .= "i";
        }

        $sql = "SELECT COALESCE(SUM(amount), 0) AS total
                FROM doc_contract_payments
                WHERE " . implode(" AND ", $where);
        
        $stmt = $this->conn->prepare($sql);
        if ($year) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : ['total' => 0];
        return (float)$row['total'];
    }

    /**
     * Suma total de lo RECIBIDO/COBRADO (dinero en caja) en el año.
     *
     * @param  int|null $year Año
     * @return float Total pagado
     */
    public function getTotalPaidGlobal($year = null)
    {
        $where = ["active = 1", "status IN ('pagado', 'parcial')"];
        $params = [];
        $types = "";

        if ($year) {
            $where[] = "YEAR(payment_date) = ?";
            $params[] = (int)$year;
            $types .= "i";
        }

        $sql = "SELECT COALESCE(SUM(amount), 0) AS total
                FROM doc_contract_payments
                WHERE " . implode(" AND ", $where);
        
        $stmt = $this->conn->prepare($sql);
        if ($year) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : ['total' => 0];
        return (float)$row['total'];
    }
}
