<?php
/**
 * Modelo de Empresas.
 *
 * Gestiona las operaciones CRUD sobre la tabla doc_companies.
 * Almacena empresas clientes (arriendo/hospedaje) y proveedores.
 * Soporta soft delete y filtros por tipo y estado.
 *
 * @package App\Models
 */
class CompanyModel extends Model
{
    /**
     * Obtiene todas las empresas activas con filtros opcionales.
     *
     * Qué hace:
     * - Filtra por active = 1 (soft delete)
     * - Permite filtrar por tipo (cliente/proveedor)
     * - Permite búsqueda por nombre o RUT
     * - Ordena por razón social
     *
     * @param  array $filters Filtros opcionales:
     *   - type    (string) 'cliente' o 'proveedor'
     *   - search  (string) Busca en business_name, trade_name, rut
     * @return array Lista de empresas
     */
    public function getAll($filters = [])
    {
        $where = ["c.active = 1"];
        $params = [];
        $types = "";

        // ----------------------------
        // Filtro por tipo
        // ----------------------------
        if (!empty($filters['type'])) {
            $where[] = "c.type = ?";
            $params[] = $filters['type'];
            $types .= "s";
        }

        // ----------------------------
        // Búsqueda por texto
        // ----------------------------
        if (!empty($filters['search'])) {
            $where[] = "(c.business_name LIKE ? OR c.trade_name LIKE ? OR c.rut LIKE ?)";
            $search = "%" . $filters['search'] . "%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "sss";
        }

        $sql = "SELECT c.* FROM doc_companies c WHERE " . implode(" AND ", $where) . " ORDER BY c.business_name ASC";
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
     * Busca una empresa por su ID.
     *
     * @param  int $id ID de la empresa
     * @return array|null Datos de la empresa o null
     */
    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM doc_companies WHERE id = ? AND active = 1 LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }
    // Fin de la función getById()

    /**
     * Crea una nueva empresa.
     *
     * @param  array $data Datos del formulario:
     *   - rut           (string) RUT o identificador fiscal
     *   - business_name (string) Razón social
     *   - trade_name    (string) Nombre de fantasía
     *   - contact_name  (string) Nombre del contacto
     *   - contact_email (string) Email del contacto
     *   - contact_phone (string) Teléfono del contacto
     *   - address       (string) Dirección
     *   - city          (string) Ciudad
     *   - type          (string) 'cliente' o 'proveedor'
     *   - notes         (string) Notas adicionales
     * @param  int $userId ID del usuario que crea
     * @return int|false ID de la empresa creada o false
     */
    public function create($data, $userId = null)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO doc_companies(rut, business_name, trade_name, contact_name,
                contact_email, contact_phone, address, city, type, notes, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssssssssi",
            $data['rut'],
            $data['business_name'],
            $data['trade_name'],
            $data['contact_name'],
            $data['contact_email'],
            $data['contact_phone'],
            $data['address'],
            $data['city'],
            $data['type'],
            $data['notes'],
            $userId
        );
        $stmt->execute();
        return $stmt->affected_rows > 0 ? $stmt->insert_id : false;
    }
    // Fin de la función create()

    /**
     * Actualiza una empresa existente.
     *
     * @param  int   $id   ID de la empresa
     * @param  array $data Datos a actualizar (mismas claves que create)
     * @return bool  true si se actualizó
     */
    public function update($id, $data)
    {
        $stmt = $this->conn->prepare("
            UPDATE doc_companies
            SET rut = ?, business_name = ?, trade_name = ?, contact_name = ?,
                contact_email = ?, contact_phone = ?, address = ?, city = ?,
                type = ?, notes = ?
            WHERE id = ? AND active = 1
        ");
        $stmt->bind_param("ssssssssssi",
            $data['rut'],
            $data['business_name'],
            $data['trade_name'],
            $data['contact_name'],
            $data['contact_email'],
            $data['contact_phone'],
            $data['address'],
            $data['city'],
            $data['type'],
            $data['notes'],
            $id
        );
        $stmt->execute();
        return $stmt->affected_rows >= 0;
    }
    // Fin de la función update()

    /**
     * Soft delete de una empresa.
     *
     * @param  int  $id ID de la empresa
     * @return bool true si se desactivó
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare("UPDATE doc_companies SET active = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
    // Fin de la función delete()

    /**
     * Cuenta empresas activas, opcionalmente filtradas por tipo.
     *
     * @param  string|null $type 'cliente', 'proveedor' o null para todas
     * @return int Total de empresas
     */
    public function count($type = null)
    {
        if ($type) {
            $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM doc_companies WHERE active = 1 AND type = ?");
            $stmt->bind_param("s", $type);
            $stmt->execute();
            $res = $stmt->get_result();
        } else {
            $res = $this->conn->query("SELECT COUNT(*) AS total FROM doc_companies WHERE active = 1");
        }
        $row = $res ? $res->fetch_assoc() : ['total' => 0];
        return (int)$row['total'];
    }
    // Fin de la función count()

    /**
     * Obtiene empresas para select/dropdown (id + nombre).
     *
     * @param  string|null $type Filtrar por tipo
     * @return array Lista con id y business_name
     */
    public function getForSelect($type = null)
    {
        $where = "WHERE active = 1";
        $params = [];
        $types = "";

        if ($type) {
            $where .= " AND type = ?";
            $params[] = $type;
            $types .= "s";
        }

        $sql = "SELECT id, business_name, trade_name, rut FROM doc_companies {$where} ORDER BY business_name ASC";
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
