<?php
/**
 * HotelAppModel — gestiona acceso de usuarios a apps del hotel (tablas chk_*)
 * Enlace: chat_usuarios.email ↔ chk_usuarios.email
 */
class HotelAppModel extends Model
{
    /**
     * Devuelve todas las apps activas de chk_apps, marcando cuáles
     * tiene habilitadas el usuario (por email).
     */
    public function getAppsConAcceso(string $email): array
    {
        $stmt = $this->conn->prepare("
            SELECT a.id, a.slug, a.nombre,
                   (
                     SELECT COUNT(*) FROM chk_usuario_apps ua
                     JOIN chk_usuarios u ON u.id = ua.usuario_id
                     WHERE u.email = ? AND ua.app_id = a.id
                   ) AS tiene_acceso
            FROM chk_apps a
            WHERE a.estado = 'activo'
            ORDER BY a.nombre ASC
        ");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    /**
     * Sincroniza el acceso del usuario a las apps del hotel.
     * - Si el usuario no existe en chk_usuarios, lo crea.
     * - Inserta o elimina filas en chk_usuario_apps según $appIds.
     *
     * @param string   $email   Email del usuario (clave de enlace)
     * @param string   $nombre  Nombre del usuario (para crear en chk_usuarios si no existe)
     * @param string   $perfil  'Administrador' o 'Operador'
     * @param int[]    $appIds  IDs de apps que deben quedar habilitadas
     */
    public function sincronizarAcceso(string $email, string $nombre, string $perfil, array $appIds): void
    {
        // 1. Obtener o crear el usuario en chk_usuarios
        $chkId = $this->obtenerOCrearChkUsuario($email, $nombre, $perfil);
        if (!$chkId) return;

        // 2. Obtener IDs de apps válidas (activas)
        $idsValidos = $this->getAppIdsActivos();
        $appIds = array_filter($appIds, fn($id) => in_array((int)$id, $idsValidos, true));
        $appIds = array_map('intval', array_values($appIds));

        // 3. Borrar accesos que ya no aplican
        $delStmt = $this->conn->prepare("
            DELETE FROM chk_usuario_apps
            WHERE usuario_id = ? AND app_id NOT IN (
                SELECT id FROM chk_apps WHERE estado = 'activo'
            )
        ");
        $delStmt->bind_param('i', $chkId);
        $delStmt->execute();
        $delStmt->close();

        // Borrar todos los accesos actuales para re-sincronizar limpio
        $clearStmt = $this->conn->prepare("DELETE FROM chk_usuario_apps WHERE usuario_id = ?");
        $clearStmt->bind_param('i', $chkId);
        $clearStmt->execute();
        $clearStmt->close();

        // 4. Insertar los accesos habilitados
        if (empty($appIds)) return;

        $insStmt = $this->conn->prepare("
            INSERT IGNORE INTO chk_usuario_apps (usuario_id, app_id) VALUES (?, ?)
        ");
        foreach ($appIds as $appId) {
            $insStmt->bind_param('ii', $chkId, $appId);
            $insStmt->execute();
        }
        $insStmt->close();
    }

    /**
     * Devuelve el id en chk_usuarios del email dado.
     * Si no existe, lo crea con estado 'activo'.
     */
    private function obtenerOCrearChkUsuario(string $email, string $nombre, string $perfil): int
    {
        // Buscar
        $sel = $this->conn->prepare("SELECT id FROM chk_usuarios WHERE email = ? LIMIT 1");
        $sel->bind_param('s', $email);
        $sel->execute();
        $res = $sel->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $sel->close();

        if ($row) {
            // Actualizar perfil por si cambió el rol en chatCalama
            $upd = $this->conn->prepare("UPDATE chk_usuarios SET perfil = ?, nombre = ? WHERE id = ?");
            $upd->bind_param('ssi', $perfil, $nombre, $row['id']);
            $upd->execute();
            $upd->close();
            return (int)$row['id'];
        }

        // Crear
        $ins = $this->conn->prepare("
            INSERT INTO chk_usuarios (nombre, email, perfil, estado)
            VALUES (?, ?, ?, 'activo')
        ");
        $ins->bind_param('sss', $nombre, $email, $perfil);
        $ins->execute();
        $id = (int)$this->conn->insert_id;
        $ins->close();
        return $id;
    }

    /** Devuelve array de IDs de apps activas */
    private function getAppIdsActivos(): array
    {
        $res = $this->conn->query("SELECT id FROM chk_apps WHERE estado = 'activo'");
        if (!$res) return [];
        return array_column($res->fetch_all(MYSQLI_ASSOC), 'id');
    }
}
