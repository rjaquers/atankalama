<?php
/**
 * ===================================================
 * Servicio: AuthService
 * Proyecto: Hotel Atankalama – Sistema de Contratos
 * PHP: 7.4 compatible
 * ===================================================
 *
 * Responsabilidad:
 * Gestiona la autenticación de usuarios del sistema.
 * Verifica credenciales, inicia sesión con datos de rol
 * y nombre del rol (admin, vendedor, cobranzas, recepcion).
 */
class AuthService
{
    /**
     * Intenta autenticar un usuario con email y contraseña.
     *
     * Qué hace:
     * - Busca al usuario por email en la base de datos
     * - Verifica que el usuario esté activo (status = 1)
     * - Compara la contraseña con el hash almacenado
     * - Si es válido, carga los datos de sesión incluyendo el rol
     * - Regenera el ID de sesión por seguridad
     *
     * @param  string $email    Email del usuario
     * @param  string $password Contraseña en texto plano
     * @return bool   true si se autenticó correctamente, false si falló
     *
     * Variables usadas:
     * - UserModel::getByEmail()
     * - PermissionModel::getPermissionsByRoleId()
     */
    public function login($email, $password)
    {
        // ----------------------------
        // Buscar usuario por email
        // ----------------------------
        $model = new UserModel();
        $user = $model->getByEmail($email);

        if (!$user) return false;
        if ((int)$user['status'] !== 1) return false;

        // ----------------------------
        // Verificar contraseña
        // ----------------------------
        if (!password_verify($password, $user['password_hash'])) return false;

        // ----------------------------
        // Cargar datos de sesión
        // ----------------------------
        $_SESSION['user_id']   = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role_id']   = (int)$user['role_id'];
        $_SESSION['role']      = $user['role_name'] ?? 'user';

        // ----------------------------
        // Cargar permisos del rol
        // ----------------------------
        $permModel = new PermissionModel();
        $_SESSION['permissions'] = $permModel->getPermissionsByRoleId((int)$user['role_id']);

        // ----------------------------
        // Regenerar sesión por seguridad
        // ----------------------------
        session_regenerate_id(true);
        return true;
    }
    // Fin de la función login()

    /**
     * Verifica si el usuario en sesión tiene un permiso específico.
     *
     * @param  string $permission Nombre del permiso (ej: 'contracts_view')
     * @return bool   true si tiene el permiso
     */
    public static function hasPermission($permission)
    {
        $permissions = $_SESSION['permissions'] ?? [];
        return in_array($permission, $permissions);
    }
    // Fin de la función hasPermission()

    /**
     * Verifica si el usuario es administrador.
     *
     * @return bool true si el rol es 'admin'
     */
    public static function isAdmin()
    {
        return ($_SESSION['role'] ?? '') === 'admin';
    }
    // Fin de la función isAdmin()

    /**
     * Obtiene el ID del usuario en sesión.
     *
     * @return int ID del usuario, 0 si no hay sesión
     */
    public static function userId()
    {
        return (int)($_SESSION['user_id'] ?? 0);
    }
    // Fin de la función userId()

    /**
     * Obtiene el nombre del usuario en sesión.
     *
     * @return string Nombre del usuario
     */
    public static function userName()
    {
        return $_SESSION['user_name'] ?? 'Usuario';
    }
    // Fin de la función userName()
}
