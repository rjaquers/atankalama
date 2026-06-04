<?php
/**
 * RolModel — tabla chat_roles
 * PHP 7.4–8.2 compatible
 */
class RolModel extends Model
{
    public function getAll(): array
    {
        $res = $this->conn->query('SELECT * FROM chat_roles ORDER BY id ASC');
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM chat_roles WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? ($res->fetch_assoc() ?: null) : null;
    }
}
