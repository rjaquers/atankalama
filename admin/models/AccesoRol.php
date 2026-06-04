<?php

class AccesoRol
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
                SELECT r.*, a.nombre AS app_nombre, a.slug AS app_slug,
                       COUNT(DISTINCT rs.seccion_id) AS total_secciones,
                       COUNT(DISTINCT ur.usuario_id) AS total_usuarios
                FROM acc_roles r
                JOIN chk_apps a ON a.id = r.app_id
                LEFT JOIN acc_rol_secciones rs ON rs.rol_id = r.id
                LEFT JOIN acc_usuario_roles ur ON ur.rol_id = r.id
                WHERE r.app_id = ?
                GROUP BY r.id
                ORDER BY r.nombre ASC
            ");
            $stmt->execute([$appId]);
        } else {
            $stmt = $this->pdo->query("
                SELECT r.*, a.nombre AS app_nombre, a.slug AS app_slug,
                       COUNT(DISTINCT rs.seccion_id) AS total_secciones,
                       COUNT(DISTINCT ur.usuario_id) AS total_usuarios
                FROM acc_roles r
                JOIN chk_apps a ON a.id = r.app_id
                LEFT JOIN acc_rol_secciones rs ON rs.rol_id = r.id
                LEFT JOIN acc_usuario_roles ur ON ur.rol_id = r.id
                GROUP BY r.id
                ORDER BY a.nombre ASC, r.nombre ASC
            ");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(int $id): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT r.*, a.nombre AS app_nombre, a.slug AS app_slug
            FROM acc_roles r
            JOIN chk_apps a ON a.id = r.app_id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar(int $appId, string $nombre, string $descripcion): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO acc_roles (app_id, nombre, descripcion, estado)
            VALUES (?, ?, ?, 'activo')
        ");
        $stmt->execute([$appId, $nombre, $descripcion]);
        return (int)$this->pdo->lastInsertId();
    }

    public function actualizar(int $id, string $nombre, string $descripcion, string $estado): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE acc_roles SET nombre = ?, descripcion = ?, estado = ? WHERE id = ?
        ");
        return $stmt->execute([$nombre, $descripcion, $estado, $id]);
    }

    public function eliminar(int $id): bool
    {
        return $this->pdo->prepare("DELETE FROM acc_roles WHERE id = ?")->execute([$id]);
    }

    // ── Secciones del rol ─────────────────────────────────

    public function listarSeccionesConEstado(int $rolId, int $appId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT s.*,
                   (rs.rol_id IS NOT NULL) AS habilitada
            FROM acc_secciones s
            LEFT JOIN acc_rol_secciones rs ON rs.seccion_id = s.id AND rs.rol_id = ?
            WHERE s.app_id = ? AND s.estado = 'activo'
            ORDER BY s.tipo DESC, s.slug ASC
        ");
        $stmt->execute([$rolId, $appId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function sincronizarSecciones(int $rolId, array $seccionIds): void
    {
        $this->pdo->prepare("DELETE FROM acc_rol_secciones WHERE rol_id = ?")->execute([$rolId]);
        $stmt = $this->pdo->prepare("INSERT IGNORE INTO acc_rol_secciones (rol_id, seccion_id) VALUES (?, ?)");
        foreach ($seccionIds as $sid) {
            $stmt->execute([$rolId, (int)$sid]);
        }
    }

    public function listarApps(): array
    {
        $stmt = $this->pdo->query("
            SELECT id, slug, nombre FROM chk_apps WHERE estado = 'activo' ORDER BY nombre ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPorApp(int $appId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, nombre FROM acc_roles WHERE app_id = ? AND estado = 'activo' ORDER BY nombre ASC
        ");
        $stmt->execute([$appId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
