<?php

class AccesoUsuario
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function listar(): array
    {
        $stmt = $this->pdo->query("
            SELECT u.*,
                   COUNT(DISTINCT ua.app_id) AS total_apps
            FROM chk_usuarios u
            LEFT JOIN chk_usuario_apps ua ON ua.usuario_id = u.id
            GROUP BY u.id
            ORDER BY u.estado ASC, u.apellido ASC, u.nombre ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM chk_usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar(string $email, string $nombre, string $apellido, string $perfil, string $telefono = '', string $rut = ''): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO chk_usuarios (email, nombre, apellido, perfil, telefono, rut, estado)
            VALUES (?, ?, ?, ?, ?, ?, 'activo')
        ");
        $stmt->execute([$email, $nombre, $apellido, $perfil, $telefono ?: null, $rut ?: null]);
        return (int)$this->pdo->lastInsertId();
    }

    public function actualizar(int $id, string $email, string $nombre, string $apellido, string $perfil, string $estado, string $telefono = '', string $rut = ''): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE chk_usuarios
            SET email = ?, nombre = ?, apellido = ?, perfil = ?, estado = ?, telefono = ?, rut = ?
            WHERE id = ?
        ");
        return $stmt->execute([$email, $nombre, $apellido, $perfil, $estado, $telefono ?: null, $rut ?: null, $id]);
    }

    public function emailExiste(string $email, ?int $excluirId = null): bool
    {
        if ($excluirId) {
            $stmt = $this->pdo->prepare("SELECT id FROM chk_usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, $excluirId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT id FROM chk_usuarios WHERE email = ?");
            $stmt->execute([$email]);
        }
        return (bool)$stmt->fetch();
    }

    // ── Apps asignadas al usuario ──────────────────────────

    public function listarAppsUsuario(int $usuarioId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT a.id, a.slug, a.nombre, a.estado,
                   (ua.app_id IS NOT NULL)  AS tiene_acceso,
                   user_rol.rol_id,
                   ar.nombre               AS rol_nombre
            FROM chk_apps a
            LEFT JOIN chk_usuario_apps ua ON ua.app_id = a.id AND ua.usuario_id = ?
            LEFT JOIN (
                SELECT ar2.app_id, ur.rol_id
                FROM acc_usuario_roles ur
                JOIN acc_roles ar2 ON ar2.id = ur.rol_id
                WHERE ur.usuario_id = ?
            ) user_rol ON user_rol.app_id = a.id
            LEFT JOIN acc_roles ar ON ar.id = user_rol.rol_id
            WHERE a.estado = 'activo'
            ORDER BY a.nombre ASC
        ");
        $stmt->execute([$usuarioId, $usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function resetValidado(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE chk_usuarios SET validado = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function forzarLogout(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE chk_usuarios SET forzar_logout = 1, sesion_expira_en = NULL WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    public function toggleRecibeNovedades(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE chk_usuarios SET recibe_novedades = 1 - recibe_novedades WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    public function listarDestinatariosNovedades(): array
    {
        $stmt = $this->pdo->query(
            "SELECT email, CONCAT(nombre, ' ', apellido) AS nombre_completo
             FROM chk_usuarios
             WHERE recibe_novedades = 1 AND estado = 'activo'"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarLog(?string $email = null, int $limite = 500): array
    {
        if ($email) {
            $stmt = $this->pdo->prepare("
                SELECT l.*, u.nombre, u.apellido
                FROM chk_login_log l
                LEFT JOIN chk_usuarios u
                       ON u.email COLLATE utf8mb4_unicode_ci = l.email COLLATE utf8mb4_unicode_ci
                WHERE l.email = ?
                ORDER BY l.created_at DESC
                LIMIT {$limite}
            ");
            $stmt->execute([$email]);
        } else {
            $stmt = $this->pdo->prepare("
                SELECT l.*, u.nombre, u.apellido
                FROM chk_login_log l
                LEFT JOIN chk_usuarios u
                       ON u.email COLLATE utf8mb4_unicode_ci = l.email COLLATE utf8mb4_unicode_ci
                ORDER BY l.created_at DESC
                LIMIT {$limite}
            ");
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function sincronizarAppsYRoles(int $usuarioId, array $appIds, array $roles): void
    {
        // Eliminar accesos anteriores
        $this->pdo->prepare("DELETE FROM chk_usuario_apps WHERE usuario_id = ?")->execute([$usuarioId]);
        $this->pdo->prepare("
            DELETE ur FROM acc_usuario_roles ur
            JOIN acc_roles ar ON ar.id = ur.rol_id
            WHERE ur.usuario_id = ?
        ")->execute([$usuarioId]);

        // Insertar apps seleccionadas y roles
        $stmtApp = $this->pdo->prepare("
            INSERT IGNORE INTO chk_usuario_apps (usuario_id, app_id) VALUES (?, ?)
        ");
        $stmtRol = $this->pdo->prepare("
            INSERT IGNORE INTO acc_usuario_roles (usuario_id, rol_id) VALUES (?, ?)
        ");

        foreach ($appIds as $appId) {
            $appId = (int)$appId;
            $stmtApp->execute([$usuarioId, $appId]);

            if (!empty($roles[$appId])) {
                $stmtRol->execute([$usuarioId, (int)$roles[$appId]]);
            }
        }
    }
}
