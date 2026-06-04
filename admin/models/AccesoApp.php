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

    public function listarUsuarios(int $appId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.nombre, u.apellido, u.email, u.perfil, u.estado,
                   (SELECT ar.nombre 
                    FROM acc_roles ar 
                    JOIN acc_usuario_roles aur ON aur.rol_id = ar.id 
                    WHERE aur.usuario_id = u.id AND ar.app_id = ? 
                    LIMIT 1) AS rol_nombre
            FROM chk_usuarios u
            JOIN chk_usuario_apps ua ON ua.usuario_id = u.id
            WHERE ua.app_id = ?
            ORDER BY u.apellido ASC, u.nombre ASC
        ");
        $stmt->execute([$appId, $appId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function quitarUsuarios(int $appId, array $usuarioIds): void
    {
        if (empty($usuarioIds)) return;

        $placeholders = implode(',', array_fill(0, count($usuarioIds), '?'));

        // Eliminar de chk_usuario_apps
        $stmt1 = $this->pdo->prepare("
            DELETE FROM chk_usuario_apps
            WHERE app_id = ? AND usuario_id IN ($placeholders)
        ");
        $stmt1->execute(array_merge([$appId], $usuarioIds));

        // Eliminar de acc_usuario_roles para esta app
        $stmt2 = $this->pdo->prepare("
            DELETE ur FROM acc_usuario_roles ur
            JOIN acc_roles ar ON ar.id = ur.rol_id
            WHERE ar.app_id = ? AND ur.usuario_id IN ($placeholders)
        ");
        $stmt2->execute(array_merge([$appId], $usuarioIds));
    }
}
