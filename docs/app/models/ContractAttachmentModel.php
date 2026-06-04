<?php
/**
 * Modelo de Archivos Adjuntos.
 *
 * Gestiona los archivos adjuntos de contratos en doc_contract_attachments.
 * Soporta categorías (contrato_firmado, evidencia_cobro, comprobante_pago,
 * foto, email, otro) y vinculación opcional a pagos específicos.
 *
 * @package App\Models
 */
class ContractAttachmentModel extends Model
{
    /**
     * Obtiene todos los adjuntos de un contrato.
     *
     * @param  int         $contractId ID del contrato
     * @param  string|null $category   Filtrar por categoría
     * @return array Lista de adjuntos con nombre del usuario que subió
     */
    public function getByContractId($contractId, $category = null)
    {
        $where = ["a.contract_id = ?", "a.active = 1"];
        $params = [(int)$contractId];
        $types = "i";

        if ($category) {
            $where[] = "a.category = ?";
            $params[] = $category;
            $types .= "s";
        }

        $sql = "
            SELECT a.*, u.name AS uploaded_by_name
            FROM doc_contract_attachments a
            LEFT JOIN doc_users u ON u.id = a.uploaded_by
            WHERE " . implode(" AND ", $where) . "
            ORDER BY a.created_at DESC
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
     * Busca un adjunto por su ID.
     *
     * @param  int $id ID del adjunto
     * @return array|null Datos del adjunto o null
     */
    public function getById($id)
    {
        $stmt = $this->conn->prepare("
            SELECT a.*, u.name AS uploaded_by_name
            FROM doc_contract_attachments a
            LEFT JOIN doc_users u ON u.id = a.uploaded_by
            WHERE a.id = ? AND a.active = 1
            LIMIT 1
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }
    // Fin de la función getById()

    /**
     * Crea un registro de adjunto.
     *
     * @param  array $data Datos del archivo:
     *   - contract_id  (int)
     *   - filename     (string) Nombre en disco
     *   - original_name (string) Nombre original
     *   - mime_type    (string)
     *   - file_size    (int)
     *   - file_path    (string) Ruta relativa
     *   - category     (string)
     *   - description  (string|null)
     *   - payment_id   (int|null)
     * @param  int $userId ID del usuario que sube
     * @return int|false ID del adjunto creado o false
     */
    public function create($data, $userId)
    {
        $paymentId = !empty($data['payment_id']) ? (int)$data['payment_id'] : null;
        $description = $data['description'] ?? null;

        $stmt = $this->conn->prepare("
            INSERT INTO doc_contract_attachments(contract_id, filename, original_name,
                mime_type, file_size, file_path, category, description, payment_id, uploaded_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssisssii",
            $data['contract_id'],
            $data['filename'],
            $data['original_name'],
            $data['mime_type'],
            $data['file_size'],
            $data['file_path'],
            $data['category'],
            $description,
            $paymentId,
            $userId
        );
        $stmt->execute();
        return $stmt->affected_rows > 0 ? $stmt->insert_id : false;
    }
    // Fin de la función create()

    /**
     * Soft delete de un adjunto.
     *
     * @param  int  $id ID del adjunto
     * @return bool true si se desactivó
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare("UPDATE doc_contract_attachments SET active = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
    // Fin de la función delete()

    /**
     * Cuenta adjuntos de un contrato.
     *
     * @param  int $contractId ID del contrato
     * @return int Total de adjuntos activos
     */
    public function countByContract($contractId)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM doc_contract_attachments WHERE contract_id = ? AND active = 1");
        $stmt->bind_param("i", $contractId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : ['total' => 0];
        return (int)$row['total'];
    }
    // Fin de la función countByContract()

    /**
     * Obtiene adjuntos vinculados a un pago específico.
     *
     * @param  int   $paymentId ID del pago
     * @return array Lista de adjuntos del pago
     */
    public function getByPaymentId($paymentId)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM doc_contract_attachments
            WHERE payment_id = ? AND active = 1
            ORDER BY created_at DESC
        ");
        $stmt->bind_param("i", $paymentId);
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
    // Fin de la función getByPaymentId()
}
