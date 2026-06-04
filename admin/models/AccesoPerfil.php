<?php

class AccesoPerfil
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function listar(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM acc_perfiles ORDER BY nombre ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM acc_perfiles WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar(string $nombre): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO acc_perfiles (nombre) VALUES (?)");
        $stmt->execute([$nombre]);
        return (int)$this->pdo->lastInsertId();
    }

    public function actualizar(int $id, string $nombre): bool
    {
        $stmt = $this->pdo->prepare("UPDATE acc_perfiles SET nombre = ? WHERE id = ?");
        return $stmt->execute([$nombre, $id]);
    }

    public function eliminar(int $id): bool
    {
        // Verificar si hay usuarios usando este perfil antes de eliminar?
        // Por ahora, eliminación directa. 
        $stmt = $this->pdo->prepare("DELETE FROM acc_perfiles WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function existeNombre(string $nombre, ?int $excluirId = null): bool
    {
        if ($excluirId) {
            $stmt = $this->pdo->prepare("SELECT id FROM acc_perfiles WHERE nombre = ? AND id != ?");
            $stmt->execute([$nombre, $excluirId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT id FROM acc_perfiles WHERE nombre = ?");
            $stmt->execute([$nombre]);
        }
        return (bool)$stmt->fetch();
    }
}
