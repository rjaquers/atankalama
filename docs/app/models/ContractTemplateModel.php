<?php
/**
 * Modelo de Plantillas de Contrato.
 *
 * Gestiona las plantillas HTML editables en doc_contract_templates.
 * Las plantillas contienen variables dinámicas ({{empresa_nombre}}, etc.)
 * que se reemplazan al generar el PDF del contrato.
 * Solo los administradores pueden crear/editar plantillas.
 *
 * @package App\Models
 */
class ContractTemplateModel extends Model
{
    /**
     * Obtiene todas las plantillas activas.
     *
     * @param  array $filters Filtros opcionales:
     *   - contract_type (string) 'arriendo', 'hospedaje', 'proveedor'
     * @return array Lista de plantillas
     */
    public function getAll($filters = [])
    {
        $where = ["t.active = 1"];
        $params = [];
        $types = "";

        if (!empty($filters['contract_type'])) {
            $where[] = "t.contract_type = ?";
            $params[] = $filters['contract_type'];
            $types .= "s";
        }

        $sql = "SELECT t.*, u.name AS created_by_name
                FROM doc_contract_templates t
                LEFT JOIN doc_users u ON u.id = t.created_by
                WHERE " . implode(" AND ", $where) . "
                ORDER BY t.name ASC";
        $stmt = $this->conn->prepare($sql);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

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
    // Fin de la función getAll()

    /**
     * Busca una plantilla por su ID.
     *
     * @param  int $id ID de la plantilla
     * @return array|null Datos de la plantilla o null
     */
    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM doc_contract_templates WHERE id = ? AND active = 1 LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }
    // Fin de la función getById()

    /**
     * Crea una nueva plantilla.
     *
     * @param  array $data Datos:
     *   - name          (string) Nombre de la plantilla
     *   - contract_type (string) Tipo de contrato
     *   - body_html     (string) Contenido HTML con variables
     *   - header_text   (string) Texto del encabezado
     *   - footer_text   (string) Texto del pie
     * @param  int $userId ID del usuario que crea
     * @return int|false ID de la plantilla creada o false
     */
    public function create($data, $userId)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO doc_contract_templates(name, contract_type, body_html, header_text, footer_text, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssssi",
            $data['name'],
            $data['contract_type'],
            $data['body_html'],
            $data['header_text'],
            $data['footer_text'],
            $userId
        );
        $stmt->execute();
        return $stmt->affected_rows > 0 ? $stmt->insert_id : false;
    }
    // Fin de la función create()

    /**
     * Actualiza una plantilla.
     *
     * @param  int   $id   ID de la plantilla
     * @param  array $data Datos a actualizar
     * @return bool  true si se actualizó
     */
    public function update($id, $data)
    {
        $stmt = $this->conn->prepare("
            UPDATE doc_contract_templates
            SET name = ?, contract_type = ?, body_html = ?, header_text = ?, footer_text = ?
            WHERE id = ? AND active = 1
        ");
        $stmt->bind_param("sssssi",
            $data['name'],
            $data['contract_type'],
            $data['body_html'],
            $data['header_text'],
            $data['footer_text'],
            $id
        );
        $stmt->execute();
        return $stmt->affected_rows >= 0;
    }
    // Fin de la función update()

    /**
     * Soft delete de una plantilla.
     *
     * @param  int  $id ID de la plantilla
     * @return bool true si se desactivó
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare("UPDATE doc_contract_templates SET active = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
    // Fin de la función delete()

    /**
     * Obtiene plantillas para un select/dropdown.
     *
     * @param  string|null $contractType Filtrar por tipo
     * @return array Lista con id y name
     */
    public function getForSelect($contractType = null)
    {
        $where = "WHERE active = 1";
        $params = [];
        $types = "";

        if ($contractType) {
            $where .= " AND contract_type = ?";
            $params[] = $contractType;
            $types .= "s";
        }

        $sql = "SELECT id, name, contract_type FROM doc_contract_templates {$where} ORDER BY name ASC";
        $stmt = $this->conn->prepare($sql);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

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
    // Fin de la función getForSelect()
}
