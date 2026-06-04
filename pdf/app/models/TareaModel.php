<?php
/**
 * TareaModel — tabla chat_tareas
 * PHP 7.4–8.2 compatible
 */
class TareaModel extends Model
{
    // -------------------------------------------------------
    // Consulta base compartida
    // -------------------------------------------------------
    private function baseSelect(): string
    {
        return "
            SELECT t.*,
                   u.nombre  AS asignado_nombre,
                   a.nombre  AS area_nombre,
                   c.nombre  AS creador_nombre
            FROM chat_tareas t
            LEFT JOIN chat_usuarios u ON u.id = t.asignado_a
            LEFT JOIN chat_areas    a ON a.id = t.area_id
            LEFT JOIN chat_usuarios c ON c.id = t.creado_por
        ";
    }

    // -------------------------------------------------------
    // getAll
    // -------------------------------------------------------
    public function getAll(array $filtros = []): array
    {
        $where  = [];
        $types  = '';
        $values = [];

        if (!empty($filtros['area_id'])) {
            $where[]  = 't.area_id = ?';
            $types   .= 'i';
            $values[] = (int)$filtros['area_id'];
        }
        if (!empty($filtros['asignado_a'])) {
            $where[]  = 't.asignado_a = ?';
            $types   .= 'i';
            $values[] = (int)$filtros['asignado_a'];
        }
        if (!empty($filtros['estado'])) {
            $where[]  = 't.estado = ?';
            $types   .= 's';
            $values[] = $filtros['estado'];
        }
        if (!empty($filtros['prioridad'])) {
            $where[]  = 't.prioridad = ?';
            $types   .= 's';
            $values[] = $filtros['prioridad'];
        }

        $sql = $this->baseSelect();
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= " ORDER BY FIELD(t.estado,'urgente','en_proceso','pendiente','completada','cancelada'), t.fecha_limite ASC";

        if (empty($values)) {
            $res = $this->conn->query($sql);
            return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    // -------------------------------------------------------
    // getById — incluye archivos y comentarios
    // -------------------------------------------------------
    public function getById(int $id): ?array
    {
        $sql  = $this->baseSelect() . ' WHERE t.id = ? LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res  = $stmt->get_result();
        $tarea = $res ? $res->fetch_assoc() : null;
        if (!$tarea) {
            return null;
        }
        $tarea['archivos']     = $this->getArchivos($id);
        $tarea['comentarios']  = $this->getComentarios($id);
        return $tarea;
    }

    // -------------------------------------------------------
    // create
    // -------------------------------------------------------
    public function create(array $data): int
    {
        $titulo      = $data['titulo']      ?? '';
        $descripcion = $data['descripcion'] ?? '';
        $tipo        = in_array($data['tipo'] ?? '', ['abierta','dirigida'], true) ? $data['tipo'] : 'abierta';
        $area_id     = !empty($data['area_id'])    ? (int)$data['area_id']    : null;
        $asignado_a  = !empty($data['asignado_a']) ? (int)$data['asignado_a'] : null;
        $creado_por  = (int)($data['creado_por'] ?? 0);
        $prioridad   = $data['prioridad']   ?? 'media';
        $estado      = $data['estado']      ?? 'pendiente';
        $fecha_limite = !empty($data['fecha_limite']) ? $data['fecha_limite'] : null;

        $stmt = $this->conn->prepare("
            INSERT INTO chat_tareas
                (titulo, descripcion, tipo, area_id, asignado_a, creado_por, prioridad, estado, fecha_limite)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'sssiiisss',
            $titulo,
            $descripcion,
            $tipo,
            $area_id,
            $asignado_a,
            $creado_por,
            $prioridad,
            $estado,
            $fecha_limite
        );
        $stmt->execute();
        return (int)$this->conn->insert_id;
    }

    // -------------------------------------------------------
    // update — solo campos permitidos
    // -------------------------------------------------------
    public function update(int $id, array $data): bool
    {
        $allowed = ['titulo', 'descripcion', 'tipo', 'area_id', 'asignado_a', 'prioridad', 'estado', 'fecha_limite'];
        $intCols = ['area_id', 'asignado_a'];
        $fields  = [];
        $types   = '';
        $values  = [];

        foreach ($allowed as $col) {
            if (!array_key_exists($col, $data)) {
                continue;
            }
            $fields[] = "$col = ?";
            if (in_array($col, $intCols, true)) {
                $types   .= 'i';
                $values[] = !empty($data[$col]) ? (int)$data[$col] : null;
            } else {
                $types   .= 's';
                $values[] = $data[$col];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $types   .= 'i';
        $values[] = $id;

        $stmt = $this->conn->prepare(
            'UPDATE chat_tareas SET ' . implode(', ', $fields) . ' WHERE id = ?'
        );
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    // -------------------------------------------------------
    // completar — foto obligatoria
    // -------------------------------------------------------
    public function completar(int $id, string $fotoCierre, string $notaCierre, int $userId): bool
    {
        if ($fotoCierre === '') {
            return false;
        }

        $stmt = $this->conn->prepare("
            UPDATE chat_tareas
            SET estado = 'completada',
                foto_cierre      = ?,
                nota_cierre      = ?,
                fecha_completada = NOW()
            WHERE id = ?
              AND estado NOT IN ('completada', 'cancelada')
        ");
        $stmt->bind_param('ssi', $fotoCierre, $notaCierre, $id);
        return $stmt->execute();
    }

    // -------------------------------------------------------
    // cancelar
    // -------------------------------------------------------
    public function cancelar(int $id, int $userId): bool
    {
        $stmt = $this->conn->prepare("
            UPDATE chat_tareas SET estado = 'cancelada' WHERE id = ?
        ");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    // -------------------------------------------------------
    // addComentario
    // -------------------------------------------------------
    public function addComentario(int $tareaId, int $userId, string $texto): int
    {
        $stmt = $this->conn->prepare("
            INSERT INTO chat_tarea_comentarios (tarea_id, usuario_id, comentario)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param('iis', $tareaId, $userId, $texto);
        $stmt->execute();
        return (int)$this->conn->insert_id;
    }

    // -------------------------------------------------------
    // getComentarios
    // -------------------------------------------------------
    public function getComentarios(int $tareaId): array
    {
        $stmt = $this->conn->prepare("
            SELECT tc.*,
                   u.nombre     AS autor_nombre,
                   u.foto_perfil AS autor_foto
            FROM chat_tarea_comentarios tc
            JOIN chat_usuarios u ON u.id = tc.usuario_id
            WHERE tc.tarea_id = ?
            ORDER BY tc.created_at ASC
        ");
        $stmt->bind_param('i', $tareaId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    // -------------------------------------------------------
    // addArchivo
    // -------------------------------------------------------
    public function addArchivo(int $tareaId, int $userId, string $ruta): int
    {
        $stmt = $this->conn->prepare("
            INSERT INTO chat_tarea_archivos (tarea_id, subido_por, ruta)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param('iis', $tareaId, $userId, $ruta);
        $stmt->execute();
        return (int)$this->conn->insert_id;
    }

    // -------------------------------------------------------
    // getArchivos
    // -------------------------------------------------------
    public function getArchivos(int $tareaId): array
    {
        $stmt = $this->conn->prepare("
            SELECT ta.*, u.nombre AS subido_por_nombre
            FROM chat_tarea_archivos ta
            JOIN chat_usuarios u ON u.id = ta.subido_por
            WHERE ta.tarea_id = ?
            ORDER BY ta.created_at ASC
        ");
        $stmt->bind_param('i', $tareaId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    // -------------------------------------------------------
    // getMisTagreas
    // -------------------------------------------------------
    public function getMisTagreas(int $userId): array
    {
        $stmt = $this->conn->prepare("
            SELECT t.*,
                   u.nombre AS asignado_nombre,
                   a.nombre AS area_nombre,
                   c.nombre AS creador_nombre
            FROM chat_tareas t
            LEFT JOIN chat_usuarios u ON u.id = t.asignado_a
            LEFT JOIN chat_areas    a ON a.id = t.area_id
            LEFT JOIN chat_usuarios c ON c.id = t.creado_por
            WHERE t.asignado_a = ?
              AND t.estado NOT IN ('completada', 'cancelada')
            ORDER BY t.prioridad DESC
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    // -------------------------------------------------------
    // countByEstado
    // -------------------------------------------------------
    public function countByEstado(): array
    {
        $res = $this->conn->query("
            SELECT estado, COUNT(*) AS total
            FROM chat_tareas
            GROUP BY estado
        ");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}
