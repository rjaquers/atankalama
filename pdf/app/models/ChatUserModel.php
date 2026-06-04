<?php
/**
 * ChatUserModel — tabla chat_usuarios
 * PHP 7.4–8.2 compatible
 */
class ChatUserModel extends Model
{
    public function getByEmail(string $email): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT u.*, r.nombre AS rol_nombre, a.nombre AS area_nombre
            FROM chat_usuarios u
            LEFT JOIN chat_roles r ON r.id = u.rol_id
            LEFT JOIN chat_areas  a ON a.id = u.area_id
            WHERE u.email = ? LIMIT 1
        ");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? ($res->fetch_assoc() ?: null) : null;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT u.*, r.nombre AS rol_nombre, a.nombre AS area_nombre, a.color AS area_color
            FROM chat_usuarios u
            LEFT JOIN chat_roles r ON r.id = u.rol_id
            LEFT JOIN chat_areas  a ON a.id = u.area_id
            WHERE u.id = ? LIMIT 1
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? ($res->fetch_assoc() ?: null) : null;
    }

    public function getAll(bool $soloActivos = false): array
    {
        $where = $soloActivos ? 'WHERE u.estado = 1' : '';
        $res   = $this->conn->query("
            SELECT u.id, u.nombre, u.email, u.estado, u.es_jefe, u.foto_perfil,
                   u.ultimo_acceso, u.created_at,
                   r.nombre AS rol_nombre, a.nombre AS area_nombre
            FROM chat_usuarios u
            LEFT JOIN chat_roles r ON r.id = u.rol_id
            LEFT JOIN chat_areas  a ON a.id = u.area_id
            $where
            ORDER BY u.nombre ASC
        ");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getByArea(int $areaId): array
    {
        $stmt = $this->conn->prepare("
            SELECT u.id, u.nombre, u.email, u.foto_perfil, u.es_jefe, u.estado,
                   r.nombre AS rol_nombre
            FROM chat_usuarios u
            LEFT JOIN chat_roles r ON r.id = u.rol_id
            WHERE u.area_id = ? AND u.estado = 1
            ORDER BY u.es_jefe DESC, u.nombre ASC
        ");
        $stmt->bind_param('i', $areaId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function create(array $data): int
    {
        $hash   = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT); // password desactivado, acceso por OTP
        $esJefe = (int)($data['es_jefe'] ?? 0);
        $areaId = !empty($data['area_id']) ? (int)$data['area_id'] : null;
        $rolId  = (int)($data['rol_id'] ?? 3);

        $stmt = $this->conn->prepare("
            INSERT INTO chat_usuarios (nombre, email, password_hash, rol_id, area_id, es_jefe, estado)
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->bind_param('sssiii', $data['nombre'], $data['email'], $hash, $rolId, $areaId, $esJefe);
        $stmt->execute();
        $id = (int)$this->conn->insert_id;
        $stmt->close();
        return $id;
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['nombre', 'rol_id', 'area_id', 'es_jefe', 'estado'];
        $fields  = [];
        $types   = '';
        $values  = [];
        foreach ($allowed as $f) {
            if (!array_key_exists($f, $data)) continue;
            $fields[] = "$f = ?";
            $types   .= in_array($f, ['rol_id','area_id','es_jefe','estado'], true) ? 'i' : 's';
            $values[] = $data[$f];
        }
        if (empty($fields)) return false;
        $types   .= 'i';
        $values[] = $id;
        $stmt = $this->conn->prepare('UPDATE chat_usuarios SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    public function updatePerfil(int $id, array $data): bool
    {
        // Solo nombre y área (email no editable por el usuario)
        $stmt = $this->conn->prepare('UPDATE chat_usuarios SET nombre = ?, area_id = ? WHERE id = ?');
        $areaId = !empty($data['area_id']) ? (int)$data['area_id'] : null;
        $stmt->bind_param('sii', $data['nombre'], $areaId, $id);
        return $stmt->execute();
    }

    public function updateFoto(int $id, string $ruta): bool
    {
        $stmt = $this->conn->prepare('UPDATE chat_usuarios SET foto_perfil = ? WHERE id = ?');
        $stmt->bind_param('si', $ruta, $id);
        return $stmt->execute();
    }

    public function saveOtp(int $userId, string $code, int $minutes = 10): bool
    {
        $expires = date('Y-m-d H:i:s', time() + ($minutes * 60));
        $stmt    = $this->conn->prepare('UPDATE chat_usuarios SET otp_code = ?, otp_expires = ? WHERE id = ?');
        $stmt->bind_param('ssi', $code, $expires, $userId);
        return $stmt->execute();
    }

    public function verifyOtp(int $userId, string $code): bool
    {
        $stmt = $this->conn->prepare("
            SELECT id FROM chat_usuarios
            WHERE id = ? AND otp_code = ? AND otp_expires > NOW() AND estado = 1
            LIMIT 1
        ");
        $stmt->bind_param('is', $userId, $code);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res && $res->num_rows === 1;
    }

    public function clearOtp(int $userId): void
    {
        $stmt = $this->conn->prepare('UPDATE chat_usuarios SET otp_code = NULL, otp_expires = NULL WHERE id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
    }

    public function updateLastAccess(int $userId): void
    {
        $stmt = $this->conn->prepare('UPDATE chat_usuarios SET ultimo_acceso = NOW() WHERE id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
    }

    public function updateFcmToken(int $userId, string $token): void
    {
        $stmt = $this->conn->prepare('UPDATE chat_usuarios SET fcm_token = ? WHERE id = ?');
        $stmt->bind_param('si', $token, $userId);
        $stmt->execute();
    }

    /**
     * Elimina un token push inválido/expirado de la base de datos.
     */
    public function clearFcmToken(string $token): void
    {
        $stmt = $this->conn->prepare(
            "UPDATE chat_usuarios SET fcm_token = NULL WHERE fcm_token = ?"
        );
        $stmt->bind_param('s', $token);
        $stmt->execute();
    }

    public function countActive(): int
    {
        $res = $this->conn->query("SELECT COUNT(*) AS c FROM chat_usuarios WHERE estado = 1");
        return $res ? (int)$res->fetch_assoc()['c'] : 0;
    }

    public function getFcmTokensByArea(int $areaId): array
    {
        $stmt = $this->conn->prepare("
            SELECT fcm_token FROM chat_usuarios
            WHERE area_id = ? AND estado = 1 AND fcm_token IS NOT NULL AND fcm_token != ''
        ");
        $stmt->bind_param('i', $areaId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? array_column($res->fetch_all(MYSQLI_ASSOC), 'fcm_token') : [];
    }

    public function getAllFcmTokens(): array
    {
        $res = $this->conn->query("
            SELECT fcm_token FROM chat_usuarios
            WHERE estado = 1 AND fcm_token IS NOT NULL AND fcm_token != ''
        ");
        return $res ? array_column($res->fetch_all(MYSQLI_ASSOC), 'fcm_token') : [];
    }

    public function emailExists(string $email, int $excludeId = 0): bool
    {
        $stmt = $this->conn->prepare('SELECT id FROM chat_usuarios WHERE email = ? AND id != ? LIMIT 1');
        $stmt->bind_param('si', $email, $excludeId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res && $res->num_rows > 0;
    }
}
