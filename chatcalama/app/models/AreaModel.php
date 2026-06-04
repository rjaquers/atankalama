<?php
/**
 * AreaModel — tabla chat_areas
 * PHP 7.4–8.2 compatible
 */
class AreaModel extends Model
{
    public function getAll(bool $soloActivas = false): array
    {
        $where = $soloActivas ? "WHERE estado = 'activo'" : '';
        $res   = $this->conn->query("SELECT * FROM chat_areas $where ORDER BY nombre ASC");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM chat_areas WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? ($res->fetch_assoc() ?: null) : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->conn->prepare("
            INSERT INTO chat_areas (nombre, descripcion, color, icono, estado)
            VALUES (?, ?, ?, ?, 'activo')
        ");
        $stmt->bind_param('ssss',
            $data['nombre'],
            $data['descripcion'] ?? '',
            $data['color'] ?? '#3B82F6',
            $data['icono'] ?? ''
        );
        $stmt->execute();
        return (int)$this->conn->insert_id;
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->conn->prepare("
            UPDATE chat_areas SET nombre = ?, descripcion = ?, color = ?, icono = ?, estado = ?
            WHERE id = ?
        ");
        $stmt->bind_param('sssssi',
            $data['nombre'],
            $data['descripcion'] ?? '',
            $data['color'] ?? '#3B82F6',
            $data['icono'] ?? '',
            $data['estado'] ?? 'activo',
            $id
        );
        return $stmt->execute();
    }

    public function toggleEstado(int $id): bool
    {
        $stmt = $this->conn->prepare("
            UPDATE chat_areas
            SET estado = IF(estado = 'activo', 'inactivo', 'activo')
            WHERE id = ?
        ");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
