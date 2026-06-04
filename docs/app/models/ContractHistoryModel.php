<?php
/**
 * Modelo de Historial de Contratos.
 *
 * Registra todas las acciones realizadas sobre un contrato
 * en la tabla doc_contract_history (bitácora de cambios).
 * Cada acción queda asociada al usuario y contrato.
 *
 * @package App\Models
 */
class ContractHistoryModel extends Model
{
    /**
     * Registra una acción en el historial de un contrato.
     *
     * @param  int    $contractId ID del contrato
     * @param  int    $userId     ID del usuario que realiza la acción
     * @param  string $action     Tipo de acción (creado, editado, pago_registrado, etc.)
     * @param  string $description Detalle de la acción
     * @return int|false ID del registro o false
     */
    public function add($contractId, $userId, $action, $description = null)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO doc_contract_history(contract_id, user_id, action, description)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiss", $contractId, $userId, $action, $description);
        $stmt->execute();
        return $stmt->affected_rows > 0 ? $stmt->insert_id : false;
    }
    // Fin de la función add()

    /**
     * Obtiene el historial completo de un contrato.
     *
     * @param  int   $contractId ID del contrato
     * @param  int   $limit      Cantidad máxima de registros (0 = sin límite)
     * @return array Lista de acciones con nombre del usuario
     */
    public function getByContractId($contractId, $limit = 0)
    {
        $limitSql = $limit > 0 ? "LIMIT " . (int)$limit : "";
        $stmt = $this->conn->prepare("
            SELECT h.*, u.name AS user_name
            FROM doc_contract_history h
            LEFT JOIN doc_users u ON u.id = h.user_id
            WHERE h.contract_id = ?
            ORDER BY h.created_at DESC
            {$limitSql}
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
    // Fin de la función getByContractId()

    /**
     * Obtiene las últimas acciones del sistema (global).
     *
     * @param  int   $limit Cantidad máxima de registros
     * @return array Lista de acciones recientes con datos de contrato
     */
    public function getRecent($limit = 20)
    {
        $stmt = $this->conn->prepare("
            SELECT h.*, u.name AS user_name, c.code AS contract_code
            FROM doc_contract_history h
            LEFT JOIN doc_users u ON u.id = h.user_id
            LEFT JOIN doc_contracts c ON c.id = h.contract_id
            ORDER BY h.created_at DESC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
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
    // Fin de la función getRecent()
}
