<?php
/**
 * Modelo de Hoteles.
 *
 * Gestiona las operaciones CRUD sobre la tabla doc_hotels.
 * Almacena los hoteles del grupo Atankalama (Atankalama, Atankalama Inn).
 *
 * @package App\Models
 */
class HotelModel extends Model
{
    /**
     * Obtiene todos los hoteles activos.
     *
     * @param  bool $includeInactive Si true, incluye hoteles inactivos
     * @return array Lista de hoteles
     */
    public function getAll($includeInactive = false)
    {
        $where = $includeInactive ? "" : "WHERE active = 1";
        $res = $this->conn->query("SELECT * FROM doc_hotels {$where} ORDER BY name ASC");
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
     * Busca un hotel por su ID.
     *
     * @param  int $id ID del hotel
     * @return array|null Datos del hotel o null
     */
    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM doc_hotels WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }
    // Fin de la función getById()

    /**
     * Obtiene los hoteles asociados a un contrato.
     *
     * Qué hace:
     * - Realiza JOIN con doc_contract_hotels
     * - Retorna solo los hoteles vinculados al contrato indicado
     *
     * @param  int   $contractId ID del contrato
     * @return array Lista de hoteles del contrato
     */
    public function getByContractId($contractId)
    {
        $stmt = $this->conn->prepare("
            SELECT h.*
            FROM doc_contract_hotels ch
            JOIN doc_hotels h ON h.id = ch.hotel_id
            WHERE ch.contract_id = ?
            ORDER BY h.name ASC
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
     * Crea un nuevo hotel.
     *
     * @param  array $data Datos: name, code, rut, address, city, phone, email, legal_representative, representative_rut
     * @return int|false ID del hotel creado o false
     */
    public function create($data)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO doc_hotels(name, code, rut, address, city, phone, email, legal_representative, representative_rut)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssssssss",
            $data['name'],
            $data['code'],
            $data['rut'],
            $data['address'],
            $data['city'],
            $data['phone'],
            $data['email'],
            $data['legal_representative'],
            $data['representative_rut']
        );
        $stmt->execute();
        return $stmt->affected_rows > 0 ? $stmt->insert_id : false;
    }
    // Fin de la función create()

    /**
     * Actualiza un hotel existente.
     *
     * @param  int   $id   ID del hotel
     * @param  array $data Datos a actualizar
     * @return bool  true si se actualizó
     */
    public function update($id, $data)
    {
        $stmt = $this->conn->prepare("
            UPDATE doc_hotels
            SET name = ?, code = ?, rut = ?, address = ?, city = ?, phone = ?, email = ?, legal_representative = ?, representative_rut = ?
            WHERE id = ?
        ");
        $stmt->bind_param("sssssssssi",
            $data['name'],
            $data['code'],
            $data['rut'],
            $data['address'],
            $data['city'],
            $data['phone'],
            $data['email'],
            $data['legal_representative'],
            $data['representative_rut'],
            $id
        );
        $stmt->execute();
        return $stmt->affected_rows >= 0;
    }
    // Fin de la función update()

    /**
     * Soft delete de un hotel.
     *
     * @param  int  $id ID del hotel
     * @return bool true si se desactivó
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare("UPDATE doc_hotels SET active = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
    // Fin de la función delete()
}
