<?php
/**
 * MantencionModel — tabla chat_mantencion y relacionadas
 * PHP 7.4–8.2 compatible
 */
class MantencionModel extends Model
{
    public function getAll(array $filtros = []): array
    {
        $where  = [];
        $types  = '';
        $values = [];

        if (!empty($filtros['area_id'])) {
            $where[]  = 'm.area_id = ?';
            $types   .= 'i';
            $values[] = (int)$filtros['area_id'];
        }
        if (!empty($filtros['estado'])) {
            $where[]  = 'm.estado = ?';
            $types   .= 's';
            $values[] = $filtros['estado'];
        }
        if (!empty($filtros['tipo'])) {
            $where[]  = 'm.tipo = ?';
            $types   .= 's';
            $values[] = $filtros['tipo'];
        }
        if (!empty($filtros['prioridad'])) {
            $where[]  = 'm.prioridad = ?';
            $types   .= 's';
            $values[] = $filtros['prioridad'];
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT m.*,
                   u.nombre  AS asignado_nombre,
                   a.nombre  AS area_nombre,
                   c.nombre  AS creador_nombre
            FROM   chat_mantencion m
            LEFT JOIN chat_usuarios u ON u.id = m.asignado_a
            LEFT JOIN chat_areas    a ON a.id = m.area_id
            LEFT JOIN chat_usuarios c ON c.id = m.creado_por
            $whereStr
            ORDER BY FIELD(m.prioridad,'urgente','alta','media','baja'), m.created_at DESC
        ";

        if ($types === '') {
            $res = $this->conn->query($sql);
            return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Solo las mantenciones asignadas a un usuario específico.
     */
    public function getMisMantencion(int $userId): array
    {
        $stmt = $this->conn->prepare("
            SELECT m.*,
                   u.nombre  AS asignado_nombre,
                   a.nombre  AS area_nombre,
                   c.nombre  AS creador_nombre
            FROM   chat_mantencion m
            LEFT JOIN chat_usuarios u ON u.id = m.asignado_a
            LEFT JOIN chat_areas    a ON a.id = m.area_id
            LEFT JOIN chat_usuarios c ON c.id = m.creado_por
            WHERE  m.asignado_a = ?
            ORDER BY FIELD(m.prioridad,'urgente','alta','media','baja'), m.created_at DESC
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Verifica si un area_id corresponde al área de Mantención.
     */
    public function isMantencionArea(int $areaId): bool
    {
        if ($areaId <= 0) return false;
        $stmt = $this->conn->prepare("
            SELECT id FROM chat_areas WHERE id = ? AND LOWER(nombre) LIKE '%mantencion%' LIMIT 1
        ");
        $stmt->bind_param('i', $areaId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res && $res->num_rows > 0;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT m.*,
                   u.nombre  AS asignado_nombre,
                   a.nombre  AS area_nombre,
                   c.nombre  AS creador_nombre
            FROM   chat_mantencion m
            LEFT JOIN chat_usuarios u ON u.id = m.asignado_a
            LEFT JOIN chat_areas    a ON a.id = m.area_id
            LEFT JOIN chat_usuarios c ON c.id = m.creado_por
            WHERE  m.id = ?
            LIMIT  1
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? ($res->fetch_assoc() ?: null) : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->conn->prepare("
            INSERT INTO chat_mantencion
                (titulo, descripcion, ubicacion, tipo, area_id, asignado_a,
                 creado_por, prioridad, estado, fecha_programada, costo_estimado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $titulo          = $data['titulo']          ?? '';
        $descripcion     = $data['descripcion']     ?? '';
        $ubicacion       = $data['ubicacion']       ?? '';
        $tipo            = $data['tipo']            ?? 'correctiva';
        $areaId          = !empty($data['area_id'])    ? (int)$data['area_id']    : null;
        $asignadoA       = !empty($data['asignado_a']) ? (int)$data['asignado_a'] : null;
        $creadoPor       = (int)($data['creado_por']   ?? 0);
        $prioridad       = $data['prioridad']       ?? 'media';
        $estado          = $data['estado']          ?? 'pendiente';
        $fechaProgramada = !empty($data['fecha_programada']) ? $data['fecha_programada'] : null;
        $costoEstimado   = !empty($data['costo_estimado'])   ? (float)$data['costo_estimado'] : null;

        $stmt->bind_param(
            'ssssiissssd',
            $titulo,
            $descripcion,
            $ubicacion,
            $tipo,
            $areaId,
            $asignadoA,
            $creadoPor,
            $prioridad,
            $estado,
            $fechaProgramada,
            $costoEstimado
        );
        $stmt->execute();
        return (int)$this->conn->insert_id;
    }

    public function update(int $id, array $data): bool
    {
        $allowed = [
            'titulo'          => 's',
            'descripcion'     => 's',
            'ubicacion'       => 's',
            'tipo'            => 's',
            'area_id'         => 'i',
            'asignado_a'      => 'i',
            'prioridad'       => 's',
            'estado'          => 's',
            'fecha_programada'=> 's',
            'costo_estimado'  => 'd',
        ];

        $fields  = [];
        $types   = '';
        $values  = [];

        foreach ($allowed as $field => $type) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            $fields[] = "$field = ?";
            $types   .= $type;
            $values[] = $data[$field];
        }

        if (empty($fields)) {
            return false;
        }

        $types   .= 'i';
        $values[] = $id;

        $stmt = $this->conn->prepare(
            'UPDATE chat_mantencion SET ' . implode(', ', $fields) . ' WHERE id = ?'
        );
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    public function completar(int $id, string $fotoCierre, string $notaCierre, float $costoReal = 0, int $userId = 0): bool
    {
        if ($fotoCierre === '') {
            return false;
        }

        $stmt = $this->conn->prepare("
            UPDATE chat_mantencion
            SET    estado          = 'completada',
                   foto_cierre     = ?,
                   nota_cierre     = ?,
                   costo_real      = ?,
                   fecha_completada = NOW()
            WHERE  id = ?
        ");
        $stmt->bind_param('ssdi', $fotoCierre, $notaCierre, $costoReal, $id);
        return $stmt->execute();
    }

    public function cancelar(int $id, int $userId): bool
    {
        $stmt = $this->conn->prepare("
            UPDATE chat_mantencion SET estado = 'cancelada' WHERE id = ?
        ");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function addComentario(int $id, int $userId, string $texto): int
    {
        $stmt = $this->conn->prepare("
            INSERT INTO chat_mantencion_comentarios (mantencion_id, usuario_id, comentario)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param('iis', $id, $userId, $texto);
        $stmt->execute();
        return (int)$this->conn->insert_id;
    }

    public function getComentarios(int $id): array
    {
        $stmt = $this->conn->prepare("
            SELECT mc.*,
                   u.nombre      AS autor_nombre,
                   u.foto_perfil AS autor_foto
            FROM   chat_mantencion_comentarios mc
            JOIN   chat_usuarios u ON u.id = mc.usuario_id
            WHERE  mc.mantencion_id = ?
            ORDER  BY mc.created_at ASC
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function addArchivo(int $id, int $userId, string $ruta, string $momento = 'durante'): int
    {
        $nombreOrig = '';
        $stmt = $this->conn->prepare("
            INSERT INTO chat_mantencion_archivos (mantencion_id, ruta, nombre_orig, momento, subido_por)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('isssi', $id, $ruta, $nombreOrig, $momento, $userId);
        $stmt->execute();
        return (int)$this->conn->insert_id;
    }

    public function getArchivos(int $id): array
    {
        $stmt = $this->conn->prepare("
            SELECT ma.*, u.nombre AS subido_nombre
            FROM   chat_mantencion_archivos ma
            JOIN   chat_usuarios u ON u.id = ma.subido_por
            WHERE  ma.mantencion_id = ?
            ORDER  BY ma.created_at ASC
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function countByEstado(): array
    {
        $res = $this->conn->query("
            SELECT estado, COUNT(*) AS total
            FROM   chat_mantencion
            GROUP  BY estado
        ");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function countByTipo(): array
    {
        $res = $this->conn->query("
            SELECT tipo, COUNT(*) AS total
            FROM   chat_mantencion
            GROUP  BY tipo
        ");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}
