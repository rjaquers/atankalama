<?php
/**
 * Modelo de Pagos de Reservas.
 *
 * Gestiona los cobros y abonos realizados a reservas de espacios
 * que NO están asociadas a un contrato (clientes externos).
 *
 * @package App\Models
 */
class BookingPaymentModel extends Model
{
    /**
     * Obtiene todos los abonos de una reserva.
     *
     * @param  int $bookingId ID de la reserva
     * @return array
     */
    public function getByBookingId($bookingId)
    {
        $stmt = $this->conn->prepare("
            SELECT p.*, u.name AS registered_by_name
            FROM doc_space_booking_payments p
            LEFT JOIN doc_users u ON u.id = p.registered_by
            WHERE p.booking_id = ? AND p.active = 1
            ORDER BY p.payment_date DESC
        ");
        $stmt->bind_param("i", $bookingId);
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

    /**
     * Registra un nuevo abono a una reserva.
     *
     * @param  array $data
     * @param  int   $userId
     * @return int|false
     */
    public function create($data, $userId)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO doc_space_booking_payments(
                booking_id, amount, payment_date, payment_method, 
                reference_number, receipt_path, notes, registered_by
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("idsssssi",
            $data['booking_id'],
            $data['amount'],
            $data['payment_date'],
            $data['payment_method'],
            $data['reference_number'],
            $data['receipt_path'],
            $data['notes'],
            $userId
        );
        $stmt->execute();
        return $stmt->affected_rows > 0 ? $stmt->insert_id : false;
    }

    /**
     * Suma total de abonos recibidos para una reserva.
     *
     * @param  int $bookingId
     * @return float
     */
    public function getTotalPaid($bookingId)
    {
        $stmt = $this->conn->prepare("
            SELECT COALESCE(SUM(amount), 0) AS total
            FROM doc_space_booking_payments
            WHERE booking_id = ? AND active = 1
        ");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : ['total' => 0];
        return (float)$row['total'];
    }

    /**
     * Anula un pago (soft delete).
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare("UPDATE doc_space_booking_payments SET active = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
}
