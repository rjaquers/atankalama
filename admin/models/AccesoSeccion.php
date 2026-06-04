<?php

class AccesoSeccion
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function listar(?int $appId = null): array
    {
        if ($appId) {
            $stmt = $this->pdo->prepare("
                SELECT s.*, a.nombre AS app_nombre, a.slug AS app_slug
                FROM acc_secciones s
                JOIN chk_apps a ON a.id = s.app_id
                WHERE s.app_id = ?
                ORDER BY s.tipo DESC, s.slug ASC
            ");
            $stmt->execute([$appId]);
        } else {
            $stmt = $this->pdo->query("
                SELECT s.*, a.nombre AS app_nombre, a.slug AS app_slug
                FROM acc_secciones s
                JOIN chk_apps a ON a.id = s.app_id
                ORDER BY a.nombre ASC, s.tipo DESC, s.slug ASC
            ");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(int $id): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT s.*, a.nombre AS app_nombre
            FROM acc_secciones s
            JOIN chk_apps a ON a.id = s.app_id
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function slugExiste(int $appId, string $slug, ?int $excluirId = null): bool
    {
        if ($excluirId) {
            $stmt = $this->pdo->prepare("SELECT id FROM acc_secciones WHERE app_id = ? AND slug = ? AND id != ?");
            $stmt->execute([$appId, $slug, $excluirId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT id FROM acc_secciones WHERE app_id = ? AND slug = ?");
            $stmt->execute([$appId, $slug]);
        }
        return (bool)$stmt->fetch();
    }

    public function guardar(int $appId, string $slug, string $nombre, string $tipo): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO acc_secciones (app_id, slug, nombre, tipo, estado, origen)
            VALUES (?, ?, ?, ?, 'activo', 'manual')
        ");
        $stmt->execute([$appId, $slug, $nombre, $tipo]);
        return (int)$this->pdo->lastInsertId();
    }

    public function actualizar(int $id, string $slug, string $nombre, string $tipo, string $estado): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE acc_secciones SET slug = ?, nombre = ?, tipo = ?, estado = ? WHERE id = ?
        ");
        return $stmt->execute([$slug, $nombre, $tipo, $estado, $id]);
    }

    public function eliminar(int $id): bool
    {
        return $this->pdo->prepare("DELETE FROM acc_secciones WHERE id = ?")->execute([$id]);
    }

    public function listarApps(): array
    {
        $stmt = $this->pdo->query("
            SELECT id, slug, nombre FROM chk_apps WHERE estado = 'activo' ORDER BY nombre ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
