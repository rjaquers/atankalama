<?php
/**
 * Modelo de Usuarios.
 *
 * Gestiona las operaciones sobre la tabla doc_users.
 * Incluye búsqueda por email con JOIN a roles para
 * obtener el nombre del rol directamente.
 *
 * @package App\Models
 */
class UserModel extends Model
{
    /**
     * Busca un usuario por su email, incluyendo el nombre del rol.
     *
     * Qué hace:
     * - Realiza JOIN con doc_roles para obtener role_name
     * - Retorna todos los campos del usuario + nombre del rol
     * - Retorna null si no encuentra el usuario
     *
     * @param  string $email Email del usuario a buscar
     * @return array|null Datos del usuario con role_name, o null si no existe
     */
    public function getByEmail($email)
    {
        $stmt = $this->conn->prepare("
            SELECT u.*, r.name AS role_name
            FROM doc_users u
            LEFT JOIN doc_roles r ON r.id = u.role_id
            WHERE u.email = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }
    // Fin de la función getByEmail()

    /**
     * Cuenta los usuarios activos en el sistema.
     *
     * @return int Total de usuarios con status = 1
     */
    public function countActive()
    {
        $res = $this->conn->query("SELECT COUNT(*) AS total FROM doc_users WHERE status = 1");
        $row = $res ? $res->fetch_assoc() : ['total' => 0];
        return (int)$row['total'];
    }
    // Fin de la función countActive()

    /**
     * Obtiene todos los usuarios con datos de rol.
     *
     * Qué hace:
     * - Realiza JOIN con doc_roles
     * - Retorna lista ordenada por nombre
     * - Solo usuarios activos (status = 1)
     *
     * @param  bool $includeInactive Si true, incluye usuarios inactivos
     * @return array Lista de usuarios con role_name
     */
    public function getAll($includeInactive = false)
    {
        $where = $includeInactive ? "" : "WHERE u.status = 1";
        $res = $this->conn->query("
            SELECT u.*, r.name AS role_name
            FROM doc_users u
            LEFT JOIN doc_roles r ON r.id = u.role_id
            {$where}
            ORDER BY u.name ASC
        ");
        $users = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $users[] = $row;
            }
        }
        return $users;
    }
    // Fin de la función getAll()

    /**
     * Busca un usuario por su ID.
     *
     * @param  int $id ID del usuario
     * @return array|null Datos del usuario o null
     */
    public function getById($id)
    {
        $stmt = $this->conn->prepare("
            SELECT u.*, r.name AS role_name
            FROM doc_users u
            LEFT JOIN doc_roles r ON r.id = u.role_id
            WHERE u.id = ?
            LIMIT 1
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }
    // Fin de la función getById()

    /**
     * Crea un nuevo usuario en el sistema.
     *
     * Qué hace:
     * - Hashea la contraseña con bcrypt
     * - Inserta el usuario en doc_users
     * - Retorna el ID del usuario creado
     *
     * @param  string $name     Nombre completo
     * @param  string $email    Email (único)
     * @param  string $password Contraseña en texto plano
     * @param  int    $roleId   ID del rol
     * @return int|false ID del usuario creado o false si falla
     */
    public function create($name, $email, $password, $roleId)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("
            INSERT INTO doc_users(name, email, password_hash, role_id, status)
            VALUES (?, ?, ?, ?, 1)
        ");
        $stmt->bind_param("sssi", $name, $email, $hash, $roleId);
        $stmt->execute();
        return $stmt->affected_rows > 0 ? $stmt->insert_id : false;
    }
    // Fin de la función create()

    /**
     * Actualiza los datos de un usuario.
     *
     * @param  int    $id     ID del usuario
     * @param  string $name   Nombre completo
     * @param  string $email  Email
     * @param  int    $roleId ID del rol
     * @return bool true si se actualizó
     */
    public function update($id, $name, $email, $roleId)
    {
        $stmt = $this->conn->prepare("
            UPDATE doc_users SET name = ?, email = ?, role_id = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssii", $name, $email, $roleId, $id);
        $stmt->execute();
        return $stmt->affected_rows >= 0;
    }
    // Fin de la función update()

    /**
     * Guarda un token de recuperación para un usuario.
     *
     * @param  string $email   Email del usuario
     * @param  string $token   Token de recuperación
     * @param  string $expires Fecha-hora de expiración
     * @return bool
     */
    public function setResetToken($email, $token, $expires)
    {
        $stmt = $this->conn->prepare("UPDATE doc_users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expires, $email);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    /**
     * Busca un usuario por su token de recuperación activo.
     *
     * @param  string $token Token a buscar
     * @return array|null Datos del usuario o null si no es válido/expirado
     */
    public function getUserByToken($token)
    {
        if (empty($token)) return null;

        $stmt = $this->conn->prepare("
            SELECT * FROM doc_users 
            WHERE reset_token = ? AND reset_expires > NOW() 
            LIMIT 1
        ");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    /**
     * Limpia los campos de recuperación de un usuario.
     * 
     * @param int $id ID del usuario
     * @return bool
     */
    public function clearResetToken($id)
    {
        $stmt = $this->conn->prepare("UPDATE doc_users SET reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    /**
     * Actualiza la contraseña de un usuario.
     *
     * @param  int    $id       ID del usuario
     * @param  string $password Nueva contraseña en texto plano
     * @return bool true si se actualizó
     */
    public function updatePassword($id, $password)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE doc_users SET password_hash = ? WHERE id = ?");
        $stmt->bind_param("si", $hash, $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
    // Fin de la función updatePassword()

    /**
     * Activa o desactiva un usuario (soft delete).
     *
     * @param  int $id     ID del usuario
     * @param  int $status 1 = activo, 0 = inactivo
     * @return bool true si se actualizó
     */
    public function setStatus($id, $status)
    {
        $stmt = $this->conn->prepare("UPDATE doc_users SET status = ? WHERE id = ?");
        $stmt->bind_param("ii", $status, $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
    // Fin de la función setStatus()

    // ===================================================
    // Métodos OTP — Login por código de 6 dígitos
    // ===================================================

    /**
     * Guarda el código OTP y su fecha de expiración para un usuario.
     *
     * @param  int    $userId  ID del usuario
     * @param  string $code    Código OTP de 6 dígitos
     * @param  string $expires Fecha-hora de expiración (Y-m-d H:i:s)
     * @return bool   true si se guardó correctamente
     */
    public function saveOtp($userId, $code, $expires)
    {
        $stmt = $this->conn->prepare(
            "UPDATE doc_users SET otp_code = ?, otp_expires = ? WHERE id = ?"
        );
        $stmt->bind_param("ssi", $code, $expires, $userId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
    // Fin de la función saveOtp()

    /**
     * Busca un usuario activo cuyo OTP coincide y no ha expirado.
     *
     * @param  string $email Email del usuario
     * @param  string $code  Código OTP ingresado por el usuario
     * @return array|null    Datos del usuario o null si el código no es válido
     */
    public function getUserByOtp($email, $code)
    {
        $stmt = $this->conn->prepare("
            SELECT u.*, r.name AS role_name
            FROM doc_users u
            LEFT JOIN doc_roles r ON r.id = u.role_id
            WHERE u.email = ?
              AND u.otp_code = ?
              AND u.otp_expires > NOW()
              AND u.status = 1
            LIMIT 1
        ");
        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }
    // Fin de la función getUserByOtp()

    /**
     * Limpia el OTP tras un login exitoso o expirado.
     *
     * @param  int  $userId ID del usuario
     * @return bool true si se limpió
     */
    public function clearOtp($userId)
    {
        $stmt = $this->conn->prepare(
            "UPDATE doc_users SET otp_code = NULL, otp_expires = NULL WHERE id = ?"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
    // Fin de la función clearOtp()
}
