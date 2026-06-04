<?php
/**
 * Modelo de Bitácora de Reservas de Espacios.
 *
 * Registra todas las acciones sobre reservas de espacios.
 *
 * @package App\Models
 */
class SpaceBookingHistoryModel extends Model
{
    /**
     * Registra una acción.
     *
     * @param  int    $bookingId   ID de la reserva
     * @param  int    $userId      ID del usuario
     * @param  string $action      Tipo de acción
     * @param  string $description Detalle
     * @return int|false
     */
    public function add($bookingId, $userId, $action, $description = null)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO doc_space_booking_history(booking_id, user_id, action, description)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiss", $bookingId, $userId, $action, $description);
        $stmt->execute();
        return $stmt->affected_rows > 0 ? $stmt->insert_id : false;
    }
    // Fin de la función add()

    /**
     * Obtiene el historial de una reserva.
     *
     * @param  int $bookingId ID de la reserva
     * @return array
     */
    public function getByBookingId($bookingId)
    {
        $stmt = $this->conn->prepare("
            SELECT h.*, u.name AS user_name
            FROM doc_space_booking_history h
            LEFT JOIN doc_users u ON u.id = h.user_id
            WHERE h.booking_id = ?
            ORDER BY h.created_at DESC
        ");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
    // Fin de la función getByBookingId()
}
