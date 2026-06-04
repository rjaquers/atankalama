<?php
/**
 * Modelo de Espacios Arrendables.
 *
 * Gestiona las operaciones CRUD sobre la tabla doc_spaces.
 * Soporta filtros por tipo, estado y hotel.
 *
 * @package App\Models
 */
class SpaceModel extends Model
{
    /**
     * Obtiene todos los espacios con filtros opcionales.
     *
     * @param  array $filters Filtros opcionales (space_type, active, hotel_id)
     * @return array Lista de espacios
     */
    public function getAll($filters = [])
    {
        $where = [];
        $params = [];
        $types = "";

        if (isset($filters['active'])) {
            $where[] = "s.active = ?";
            $params[] = (int)$filters['active'];
            $types .= "i";
        }

        if (!empty($filters['space_type'])) {
            $where[] = "s.space_type = ?";
            $params[] = $filters['space_type'];
            $types .= "s";
        }

        if (!empty($filters['hotel_id'])) {
            $where[] = "s.hotel_id = ?";
            $params[] = (int)$filters['hotel_id'];
            $types .= "i";
        }

        $whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";
        $sql = "
            SELECT s.*, h.name AS hotel_name, u.name AS created_by_name
            FROM doc_spaces s
            LEFT JOIN doc_hotels h ON h.id = s.hotel_id
            LEFT JOIN doc_users u ON u.id = s.created_by
            {$whereSql}
            ORDER BY s.name ASC
        ";
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            // Obtener foto principal (placeholder si no hay)
            $row['main_image_url'] = !empty($row['main_image']) ? BASE_URL . $row['main_image'] : BASE_URL . '/public/assets/img/space-placeholder.webp';
            $rows[] = $row;
        }
        return $rows;
    }
    // Fin de la función getAll()

    /**
     * Busca un espacio por su ID.
     *
     * @param  int $id ID del espacio
     * @return array|null Datos del espacio o null
     */
    public function getById($id)
    {
        $stmt = $this->conn->prepare("
            SELECT s.*, h.name AS hotel_name, u.name AS created_by_name
            FROM doc_spaces s
            LEFT JOIN doc_hotels h ON h.id = s.hotel_id
            LEFT JOIN doc_users u ON u.id = s.created_by
            WHERE s.id = ?
            LIMIT 1
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }
    // Fin de la función getById()

    /**
     * Crea un nuevo espacio.
     *
     * @param  array $data Datos del espacio
     * @param  int   $userId ID del usuario que crea
     * @return int|false ID del espacio creado o false
     */
    public function create($data, $userId)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO doc_spaces(code, name, space_type, description, capacity, location,
                allows_hourly, allows_daily, allows_monthly,
                base_price_hour, base_price_day, base_price_month,
                included_equipment, restrictions, main_image, calendar_color, hotel_id, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $mainImage = $data['main_image'] ?? null;
        $calendarColor = $data['calendar_color'] ?? '#198754';

        $stmt->bind_param("ssssisiiidddssssii",
            $data['code'],
            $data['name'],
            $data['space_type'],
            $data['description'],
            $data['capacity'],
            $data['location'],
            $data['allows_hourly'],
            $data['allows_daily'],
            $data['allows_monthly'],
            $data['base_price_hour'],
            $data['base_price_day'],
            $data['base_price_month'],
            $data['included_equipment'],
            $data['restrictions'],
            $mainImage,
            $calendarColor,
            $data['hotel_id'],
            $userId
        );
        $stmt->execute();
        return $stmt->affected_rows > 0 ? $stmt->insert_id : false;
    }
    // Fin de la función create()

    /**
     * Actualiza un espacio existente.
     *
     * @param  int   $id   ID del espacio
     * @param  array $data Datos a actualizar
     * @param  int   $userId ID del usuario que actualiza
     * @return bool true si se actualizó
     */
    public function update($id, $data, $userId)
    {
        $stmt = $this->conn->prepare("
            UPDATE doc_spaces SET
                code = ?, name = ?, space_type = ?, description = ?,
                capacity = ?, location = ?,
                allows_hourly = ?, allows_daily = ?, allows_monthly = ?,
                base_price_hour = ?, base_price_day = ?, base_price_month = ?,
                included_equipment = ?, restrictions = ?,
                main_image = ?, calendar_color = ?, hotel_id = ?, active = ?, updated_by = ?
            WHERE id = ?
        ");
        $mainImage = $data['main_image'] ?? null;
        $calendarColor = $data['calendar_color'] ?? '#198754';

        $stmt->bind_param("ssssisiiidddssssiiii",
            $data['code'],
            $data['name'],
            $data['space_type'],
            $data['description'],
            $data['capacity'],
            $data['location'],
            $data['allows_hourly'],
            $data['allows_daily'],
            $data['allows_monthly'],
            $data['base_price_hour'],
            $data['base_price_day'],
            $data['base_price_month'],
            $data['included_equipment'],
            $data['restrictions'],
            $mainImage,
            $calendarColor,
            $data['hotel_id'],
            $data['active'],
            $userId,
            $id
        );
        $stmt->execute();
        return $stmt->affected_rows >= 0;
    }
    // Fin de la función update()

    /**
     * Soft delete de un espacio.
     *
     * @param  int  $id ID del espacio
     * @return bool true si se desactivó
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare("UPDATE doc_spaces SET active = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
    // Fin de la función delete()

    /**
     * Obtiene los espacios activos (para selectores).
     *
     * @return array Lista de espacios activos
     */
    public function getActive()
    {
        $stmt = $this->conn->prepare("SELECT id, code, name, space_type, capacity FROM doc_spaces WHERE active = 1 ORDER BY name");
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
    // Fin de la función getActive()

    /**
     * Genera un código único para un nuevo espacio.
     *
     * @param  string $type Tipo de espacio
     * @return string Código generado (ej: SAL-001)
     */
    public function generateCode($type)
    {
        $prefixes = [
            'salon' => 'SAL', 'sauna' => 'SAU', 'quincho' => 'QUI',
            'oficina' => 'OFI', 'terraza' => 'TER', 'otro' => 'ESP'
        ];
        $prefix = $prefixes[$type] ?? 'ESP';
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM doc_spaces WHERE space_type = ?");
        $stmt->bind_param("s", $type);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['total'] + 1;

        // Buscar el siguiente número que no esté ocupado
        $check = $this->conn->prepare("SELECT id FROM doc_spaces WHERE code = ? LIMIT 1");
        do {
            $code = $prefix . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
            $check->bind_param("s", $code);
            $check->execute();
            $exists = $check->get_result()->num_rows > 0;
            if ($exists) $count++;
        } while ($exists);

        return $code;
    }
    // Fin de la función generateCode()

    /**
     * Obtiene todas las fotos adicionales de un espacio.
     *
     * @param  int $spaceId ID del espacio
     * @return array
     */
    public function getPhotos($spaceId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM doc_space_photos WHERE space_id = ? ORDER BY sort_order, id");
        if (!$stmt) return []; // Silencioso si la tabla aún no existe

        $stmt->bind_param("i", $spaceId);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $row['url'] = BASE_URL . $row['file_path'];
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Agrega una foto a la galería de un espacio.
     *
     * @param int    $spaceId
     * @param string $path
     * @param string $originalName
     * @return bool
     */
    public function addPhoto($spaceId, $path, $originalName = null)
    {
        $stmt = $this->conn->prepare("INSERT INTO doc_space_photos(space_id, file_path, original_name) VALUES (?, ?, ?)");
        if (!$stmt) return false;

        $stmt->bind_param("iss", $spaceId, $path, $originalName);
        return $stmt->execute();
    }

    /**
     * Elimina una foto específica.
     *
     * @param int $photoId
     * @return bool
     */
    public function deletePhoto($photoId)
    {
        $stmt = $this->conn->prepare("DELETE FROM doc_space_photos WHERE id = ?");
        if (!$stmt) return false;

        $stmt->bind_param("i", $photoId);
        return $stmt->execute();
    }
}
