<?php
/**
 * Modelo de Notas de Contrato.
 *
 * Gestiona las observaciones múltiples para contratos en doc_contract_notes.
 *
 * @package App\Models
 */
class ContractNoteModel extends Model
{
    /**
     * Obtiene todas las notas de un contrato.
     *
     * @param  int $contractId ID del contrato
     * @return array Lista de notas con el nombre del usuario
     */
    public function getByContractId($contractId)
    {
        $stmt = $this->conn->prepare("
            SELECT n.*, u.name AS user_name
            FROM doc_contract_notes n
            LEFT JOIN doc_users u ON u.id = n.user_id
            WHERE n.contract_id = ?
            ORDER BY n.created_at DESC
        ");
        $stmt->bind_param("i", $contractId);
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
     * Crea una nueva nota para un contrato.
     *
     * @param int    $contractId ID del contrato
     * @param int    $userId     ID del usuario que crea la nota
     * @param string $note       Contenido de la nota
     * @return int|false ID de la nota creada o false
     */
    public function create($contractId, $userId, $note)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO doc_contract_notes (contract_id, user_id, note)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iis", $contractId, $userId, $note);
        $stmt->execute();
        return $stmt->affected_rows > 0 ? $stmt->insert_id : false;
    }

    /**
     * Elimina una nota (borrado físico en este caso por ser notas/observaciones simples).
     *
     * @param  int $id ID de la nota
     * @return bool true si se eliminó
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM doc_contract_notes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
}
