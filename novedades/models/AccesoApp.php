<?php

class AccesoApp
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function listar(): array
    {
        $stmt = $this->pdo->query("
            SELECT a.*,
                   COUNT(DISTINCT ua.usuario_id) AS total_usuarios,
                   COUNT(DISTINCT ar.id)         AS total_roles,
                   COUNT(DISTINCT s.id)          AS total_secciones
            FROM chk_apps a
            LEFT JOIN chk_usuario_apps ua ON ua.app_id = a.id
            LEFT JOIN acc_roles ar         ON ar.app_id = a.id AND ar.estado = 'activo'
            LEFT JOIN acc_secciones s      ON s.app_id  = a.id AND s.estado  = 'activo'
            GROUP BY a.id
            ORDER BY a.nombre ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM chk_apps WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function slugExiste(string $slug, ?int $excluirId = null): bool
    {
        if ($excluirId) {
            $stmt = $this->pdo->prepare("SELECT id FROM chk_apps WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $excluirId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT id FROM chk_apps WHERE slug = ?");
            $stmt->execute([$slug]);
        }
        return (bool)$stmt->fetch();
    }

    public function guardar(string $slug, string $nombre, string $descripcion, string $urlInicio, string $urlAdmin, string $sessionPrefix = '', string $icono = ''): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO chk_apps (slug, session_prefix, nombre, descripcion, url_inicio, url_admin, icono, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'activo')
        ");
        $stmt->execute([$slug, $sessionPrefix ?: null, $nombre, $descripcion, $urlInicio ?: null, $urlAdmin ?: null, $icono ?: null]);
        return (int)$this->pdo->lastInsertId();
    }

    public function actualizar(int $id, string $slug, string $nombre, string $descripcion, string $estado, string $urlInicio, string $urlAdmin, string $sessionPrefix = '', string $icono = ''): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE chk_apps
            SET slug = ?, session_prefix = ?, nombre = ?, descripcion = ?,
                estado = ?, url_inicio = ?, url_admin = ?, icono = ?
            WHERE id = ?
        ");
        return $stmt->execute([$slug, $sessionPrefix ?: null, $nombre, $descripcion, $estado, $urlInicio ?: null, $urlAdmin ?: null, $icono ?: null, $id]);
    }
}
