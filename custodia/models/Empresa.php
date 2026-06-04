<?php
// models/Empresa.php
declare(strict_types=1);

class Empresa
{
    /** @var mysqli */
    private $db;

    public function __construct()
    {
        // Igual que Ticket.php: usa la conexión global
        global $mysqli;
        if (!$mysqli instanceof mysqli) {
            throw new RuntimeException('No hay conexión MySQLi disponible en $mysqli.');
        }
        $this->db = $mysqli;
    }

    public function obtener(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, rut, business_name, trade_name, contact_name, contact_email,
                    contact_phone, address, city, type, notes, active, created_at
             FROM doc_companies WHERE id = ?'
        );
        if (!$stmt) throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();
        return $row;
    }

    public function actualizar(int $id, array $data): void
    {
        $business_name = trim($data['business_name'] ?? '');
        if ($business_name === '') {
            throw new InvalidArgumentException('La razón social es obligatoria.');
        }

        // Verificar duplicado excluyendo el propio registro
        $stmt = $this->db->prepare(
            'SELECT 1 FROM doc_companies WHERE LOWER(business_name) = LOWER(?) AND id <> ? LIMIT 1'
        );
        if (!$stmt) throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        $stmt->bind_param('si', $business_name, $id);
        $stmt->execute();
        $dup = (bool)$stmt->get_result()->fetch_row();
        $stmt->close();
        if ($dup) throw new RuntimeException('Ya existe otra empresa con esa razón social.');

        $rut           = trim($data['rut']           ?? '') ?: null;
        $trade_name    = trim($data['trade_name']    ?? '') ?: null;
        $contact_name  = trim($data['contact_name']  ?? '') ?: null;
        $contact_email = trim($data['contact_email'] ?? '') ?: null;
        $contact_phone = trim($data['contact_phone'] ?? '') ?: null;
        $address       = trim($data['address']       ?? '') ?: null;
        $city          = trim($data['city']          ?? '') ?: null;
        $type          = in_array($data['type'] ?? '', ['cliente', 'proveedor'], true)
                            ? $data['type'] : 'cliente';
        $notes         = trim($data['notes'] ?? '') ?: null;
        $active        = isset($data['active']) ? 1 : 0;

        $sql = 'UPDATE doc_companies SET
                    business_name=?, rut=?, trade_name=?, contact_name=?, contact_email=?,
                    contact_phone=?, address=?, city=?, type=?, notes=?, active=?
                WHERE id=?';
        $stmt = $this->db->prepare($sql);
        if (!$stmt) throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);

        $stmt->bind_param(
            'ssssssssssi i',
            $business_name, $rut, $trade_name, $contact_name, $contact_email,
            $contact_phone, $address, $city, $type, $notes, $active, $id
        );
        if (!$stmt->execute()) {
            $err = $this->db->error;
            $stmt->close();
            throw new RuntimeException('DB_EXECUTE_FAILED: '.$err);
        }
        $stmt->close();
    }

    public function existePorNombre(string $nombre): bool
    {
        // Chequeo case-insensitive
        $stmt = $this->db->prepare('SELECT 1 FROM doc_companies WHERE LOWER(business_name) = LOWER(?) LIMIT 1');
        if (!$stmt) throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);

        $stmt->bind_param('s', $nombre);
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = (bool)$res->fetch_row();
        $stmt->close();
        return $exists;
    }

    /** Crea empresa con todos los campos y retorna ID */
    public function crear(array $data): int
    {
        $business_name = trim($data['business_name'] ?? '');
        if ($business_name === '') {
            throw new InvalidArgumentException('La razón social es obligatoria.');
        }

        if ($this->existePorNombre($business_name)) {
            throw new RuntimeException('Ya existe una empresa con esa razón social.');
        }

        $rut           = trim($data['rut']           ?? '') ?: null;
        $trade_name    = trim($data['trade_name']    ?? '') ?: null;
        $contact_name  = trim($data['contact_name']  ?? '') ?: null;
        $contact_email = trim($data['contact_email'] ?? '') ?: null;
        $contact_phone = trim($data['contact_phone'] ?? '') ?: null;
        $address       = trim($data['address']       ?? '') ?: null;
        $city          = trim($data['city']          ?? '') ?: null;
        $type          = in_array($data['type'] ?? '', ['cliente', 'proveedor'], true)
                            ? $data['type'] : 'cliente';
        $notes         = trim($data['notes'] ?? '') ?: null;
        $active        = isset($data['active']) ? 1 : 0;

        $sql = 'INSERT INTO doc_companies
                    (business_name, rut, trade_name, contact_name, contact_email,
                     contact_phone, address, city, type, notes, active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = $this->db->prepare($sql);
        if (!$stmt) throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);

        $stmt->bind_param(
            'ssssssssssi',
            $business_name, $rut, $trade_name, $contact_name, $contact_email,
            $contact_phone, $address, $city, $type, $notes, $active
        );

        if (!$stmt->execute()) {
            $err = $this->db->error;
            $stmt->close();
            throw new RuntimeException('DB_EXECUTE_FAILED: '.$err);
        }
        $id = (int)$this->db->insert_id;
        $stmt->close();
        return $id;
    }

    /** Listado simple con filtros opcionales */
    public function listar(array $params = []): array
    {
        $where = [];
        $t = '';
        $v = [];

        if (!empty($params['q'])) {
            $where[] = 'business_name LIKE CONCAT("%", ?, "%")';
            $t .= 's';
            $v[] = $params['q'];
        }
        if (isset($params['activo']) && $params['activo'] !== '') {
            $where[] = 'active = ?';
            $t .= 'i';
            $v[] = (int)$params['activo'];
        }

        $sql = 'SELECT id, business_name AS nombre, rut, trade_name AS fantasia, contact_name AS contacto, contact_email AS email, active AS activo FROM doc_companies';
        if ($where) $sql .= ' WHERE '.implode(' AND ', $where);

        // Ordenamiento dinámico
        $sort  = $params['sort'] ?? 'nombre';
        $order = strtoupper($params['order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
        $allowedSort = ['id', 'nombre', 'rut', 'fantasia', 'contacto', 'email', 'activo'];
        if (!in_array($sort, $allowedSort)) $sort = 'nombre';

        // Mapeo de alias si es necesario para el ORDER BY
        $sortField = $sort;
        if ($sort === 'nombre')   $sortField = 'business_name';
        if ($sort === 'fantasia') $sortField = 'trade_name';
        if ($sort === 'contacto') $sortField = 'contact_name';
        if ($sort === 'email')    $sortField = 'contact_email';
        if ($sort === 'activo')   $sortField = 'active';

        $sql .= " ORDER BY $sortField $order";

        $limit  = isset($params['limit'])  ? max(1, (int)$params['limit'])  : 20;
        $offset = isset($params['offset']) ? max(0, (int)$params['offset']) : 0;

        $sql .= ' LIMIT ? OFFSET ?';
        $t   .= 'ii';
        $v[]  = $limit;
        $v[]  = $offset;

        $stmt = $this->db->prepare($sql);
        if (!$stmt) throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);

        $stmt->bind_param($t, ...$v);
        $stmt->execute();
        $res  = $stmt->get_result();
        $rows = $res->fetch_all(MYSQLI_ASSOC) ?: [];
        $stmt->close();
        return $rows;
    }

    public function contar(array $params = []): int
    {
        $where = [];
        $t = '';
        $v = [];

        if (!empty($params['q'])) {
            $where[] = 'business_name LIKE CONCAT("%", ?, "%")';
            $t .= 's';
            $v[] = $params['q'];
        }
        if (isset($params['activo']) && $params['activo'] !== '') {
            $where[] = 'active = ?';
            $t .= 'i';
            $v[] = (int)$params['activo'];
        }

        $sql = 'SELECT COUNT(*) AS total FROM doc_companies';
        if ($where) $sql .= ' WHERE '.implode(' AND ', $where);

        $stmt = $this->db->prepare($sql);
        if (!$stmt) throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);

        if ($t) $stmt->bind_param($t, ...$v);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return (int)($row['total'] ?? 0);
    }
}
