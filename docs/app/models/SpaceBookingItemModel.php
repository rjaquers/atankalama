<?php
/**
 * Modelo de Ítems de Reserva.
 *
 * Gestiona el desglose de cada reserva (arriendo base, extras, descuento, recargo).
 *
 * @package App\Models
 */
class SpaceBookingItemModel extends Model
{
    /**
     * Obtiene todos los ítems de una reserva.
     *
     * @param  int $bookingId ID de la reserva
     * @return array
     */
    public function getByBookingId($bookingId)
    {
        $stmt = $this->conn->prepare("
            SELECT i.*, e.name AS extra_name
            FROM doc_space_booking_items i
            LEFT JOIN doc_space_extras e ON e.id = i.extra_id
            WHERE i.booking_id = ?
            ORDER BY i.item_type ASC, i.id ASC
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

    /**
     * Crea un ítem de reserva.
     *
     * @param  array $data Datos del ítem
     * @return int|false ID del ítem
     */
    public function create($data)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO doc_space_booking_items(booking_id, item_type, extra_id, description, quantity, unit, unit_price, subtotal)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isissdd",
            $data['booking_id'],
            $data['item_type'],
            $data['extra_id'],
            $data['description'],
            $data['quantity'],
            $data['unit'],
            $data['unit_price'],
            $data['subtotal']
        );
        $stmt->execute();
        return $stmt->affected_rows > 0 ? $stmt->insert_id : false;
    }
    // Fin de la función create()

    /**
     * Elimina un ítem de reserva.
     *
     * @param  int $id ID del ítem
     * @return bool
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM doc_space_booking_items WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
    // Fin de la función delete()

    /**
     * Elimina todos los ítems de una reserva.
     *
     * @param  int $bookingId ID de la reserva
     * @return bool
     */
    public function deleteByBookingId($bookingId)
    {
        $stmt = $this->conn->prepare("DELETE FROM doc_space_booking_items WHERE booking_id = ?");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        return true;
    }
    // Fin de la función deleteByBookingId()
}
