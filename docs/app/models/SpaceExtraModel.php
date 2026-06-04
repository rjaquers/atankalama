<?php
/**
 * Modelo de Extras Cobrables de Espacios.
 *
 * Gestiona el catálogo de extras (coffee break, datashow, etc.)
 * en la tabla doc_space_extras.
 *
 * @package App\Models
 */
class SpaceExtraModel extends Model
{
    /**
     * Obtiene todos los extras.
     *
     * @param  array $filters Filtros opcionales (active)
     * @return array Lista de extras
     */
    public function getAll($filters = [])
    {
        $where = [];
        $params = [];
        $types = "";

        if (isset($filters['active'])) {
            $where[] = "active = ?";
            $params[] = (int)$filters['active'];
            $types .= "i";
        }

        $whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";
        $sql = "SELECT * FROM doc_space_extras {$whereSql} ORDER BY name ASC";
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
    // Fin de la función getAll()

    /**
     * Busca un extra por su ID.
     *
     * @param  int $id ID del extra
     * @return array|null
     */
    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM doc_space_extras WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }
    // Fin de la función getById()

    /**
     * Crea un nuevo extra.
     *
     * @param  array $data Datos del extra
     * @return int|false ID creado o false
     */
    public function create($data)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO doc_space_extras(name, description, charge_type, unit_price)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("sssd",
            $data['name'],
            $data['description'],
            $data['charge_type'],
            $data['unit_price']
        );
        $stmt->execute();
        return $stmt->affected_rows > 0 ? $stmt->insert_id : false;
    }
    // Fin de la función create()

    /**
     * Actualiza un extra existente.
     *
     * @param  int   $id   ID del extra
     * @param  array $data Datos a actualizar
     * @return bool
     */
    public function update($id, $data)
    {
        $stmt = $this->conn->prepare("
            UPDATE doc_space_extras SET name = ?, description = ?, charge_type = ?, unit_price = ?, active = ?
            WHERE id = ?
        ");
        $stmt->bind_param("sssdii",
            $data['name'],
            $data['description'],
            $data['charge_type'],
            $data['unit_price'],
            $data['active'],
            $id
        );
        $stmt->execute();
        return $stmt->affected_rows >= 0;
    }
    // Fin de la función update()

    /**
     * Obtiene los extras activos (para selectores en formularios).
     *
     * @return array Lista de extras activos
     */
    public function getActive()
    {
        $stmt = $this->conn->prepare("SELECT * FROM doc_space_extras WHERE active = 1 ORDER BY name");
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
    // Fin de la función getActive()
}
