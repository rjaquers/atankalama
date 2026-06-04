<?php
/**
 * Modelo de Servicios.
 *
 * Gestiona el catálogo de servicios incluibles en contratos
 * (Alojamiento, Desayuno, Lavandería, etc.) en la tabla doc_services.
 *
 * @package App\Models
 */
class ServiceModel extends Model
{
    /**
     * Obtiene todos los servicios activos.
     *
     * @param  bool $includeInactive Si true, incluye servicios inactivos
     * @return array Lista de servicios
     */
    public function getAll($includeInactive = false)
    {
        $where = $includeInactive ? "" : "WHERE active = 1";
        $res = $this->conn->query("SELECT * FROM doc_services {$where} ORDER BY name ASC");
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
     * Busca un servicio por su ID.
     *
     * @param  int $id ID del servicio
     * @return array|null Datos del servicio o null
     */
    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM doc_services WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }
    // Fin de la función getById()

    /**
     * Obtiene los servicios incluidos en un contrato.
     *
     * Qué hace:
     * - JOIN con doc_contract_services
     * - Retorna los servicios con sus notas específicas del contrato
     *
     * @param  int   $contractId ID del contrato
     * @return array Lista de servicios con campo 'contract_notes'
     */
    public function getByContractId($contractId)
    {
        $stmt = $this->conn->prepare("
            SELECT s.*, cs.notes AS contract_notes, cs.unit_price, cs.currency, cs.billing_type
            FROM doc_contract_services cs
            JOIN doc_services s ON s.id = cs.service_id
            WHERE cs.contract_id = ?
            ORDER BY s.name ASC
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
     * Crea un nuevo servicio.
     *
     * @param  array $data Datos: name, description
     * @return int|false ID del servicio creado o false
     */
    public function create($data)
    {
        $stmt = $this->conn->prepare("INSERT INTO doc_services(name, description, base_price) VALUES (?, ?, ?)");
        $basePrice = (float)($data['base_price'] ?? 0);
        $stmt->bind_param("ssd", $data['name'], $data['description'], $basePrice);
        $stmt->execute();
        return $stmt->affected_rows > 0 ? $stmt->insert_id : false;
    }

    public function update($id, $data)
    {
        $stmt = $this->conn->prepare("UPDATE doc_services SET name = ?, description = ?, base_price = ? WHERE id = ?");
        $basePrice = (float)($data['base_price'] ?? 0);
        $stmt->bind_param("ssdi", $data['name'], $data['description'], $basePrice, $id);
        $stmt->execute();
        return $stmt->affected_rows >= 0;
    }
    // Fin de la función update()

    /**
     * Soft delete de un servicio.
     *
     * @param  int  $id ID del servicio
     * @return bool true si se desactivó
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare("UPDATE doc_services SET active = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
    // Fin de la función delete()
}
