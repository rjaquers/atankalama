<?php
/**
 * Modelo de Bloqueos de Espacios.
 *
 * Gestiona los bloqueos por mantención, limpieza u otros
 * en la tabla doc_space_blocks.
 *
 * @package App\Models
 */
class SpaceBlockModel extends Model
{
    /**
     * Obtiene bloqueos por rango de fechas (para calendario).
     *
     * @param  string $start Fecha inicio
     * @param  string $end   Fecha fin
     * @return array
     */
    public function getByDateRange($start, $end)
    {
        $stmt = $this->conn->prepare("
            SELECT bl.*, s.name AS space_name
            FROM doc_space_blocks bl
            JOIN doc_spaces s ON s.id = bl.space_id
            WHERE bl.start_datetime < ? AND bl.end_datetime > ?
            ORDER BY bl.start_datetime ASC
        ");
        $endDate = $end . ' 23:59:59';
        $startDate = $start . ' 00:00:00';
        $stmt->bind_param("ss", $endDate, $startDate);
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
     * Obtiene bloqueos por espacio.
     *
     * @param  int $spaceId ID del espacio
     * @return array
     */
    public function getBySpaceId($spaceId)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM doc_space_blocks WHERE space_id = ? ORDER BY start_datetime DESC
        ");
        $stmt->bind_param("i", $spaceId);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
    // Fin de la función getBySpaceId()

    /**
     * Crea un bloqueo.
     *
     * @param  array $data   Datos del bloqueo
     * @param  int   $userId ID del usuario
     * @return int|false
     */
    public function create($data, $userId)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO doc_space_blocks(space_id, start_datetime, end_datetime, reason, block_type, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issssi",
            $data['space_id'],
            $data['start_datetime'],
            $data['end_datetime'],
            $data['reason'],
            $data['block_type'],
            $userId
        );
        $stmt->execute();
        return $stmt->affected_rows > 0 ? $stmt->insert_id : false;
    }
    // Fin de la función create()

    /**
     * Elimina un bloqueo.
     *
     * @param  int $id ID del bloqueo
     * @return bool
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM doc_space_blocks WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
    // Fin de la función delete()
}
