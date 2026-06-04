<?php

class ChkArea
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function listar(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM chk_areas ORDER BY nombre ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM chk_areas WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function nombreExiste(string $nombre, ?int $excluirId = null): bool
    {
        if ($excluirId) {
            $stmt = $this->pdo->prepare("SELECT id FROM chk_areas WHERE nombre = ? AND id != ?");
            $stmt->execute([$nombre, $excluirId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT id FROM chk_areas WHERE nombre = ?");
            $stmt->execute([$nombre]);
        }
        return (bool)$stmt->fetch();
    }

    public function guardar(string $nombre, string $descripcion): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO chk_areas (nombre, descripcion, estado)
            VALUES (?, ?, 'activo')
        ");
        $stmt->execute([$nombre, $descripcion ?: null]);
        return (int)$this->pdo->lastInsertId();
    }

    public function actualizar(int $id, string $nombre, string $descripcion, string $estado): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE chk_areas SET nombre = ?, descripcion = ?, estado = ? WHERE id = ?
        ");
        return $stmt->execute([$nombre, $descripcion ?: null, $estado, $id]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM chk_areas WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
