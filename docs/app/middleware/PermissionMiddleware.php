<?php
/**
 * Middleware de Permisos.
 *
 * Verifica que el usuario autenticado tenga el permiso
 * requerido para acceder a una acción específica.
 * Primero verifica autenticación, luego verifica el permiso
 * desde los permisos cargados en sesión.
 *
 * @package App\Middleware
 */
class PermissionMiddleware
{
    /**
     * Verifica que el usuario tenga un permiso específico.
     *
     * Qué hace:
     * - Si no hay sesión, redirige al login
     * - Busca el permiso en el array de permisos de sesión
     * - Si no tiene el permiso, retorna 403
     *
     * @param  string $permission Nombre del permiso requerido (ej: 'contracts_view')
     * @return void
     */
    public static function check($permission)
    {
        // ----------------------------
        // Verificar autenticación
        // ----------------------------
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        // ----------------------------
        // Admin tiene acceso total
        // ----------------------------
        if (($_SESSION['role'] ?? '') === 'admin') {
            return;
        }

        // ----------------------------
        // Verificar permiso en sesión
        // ----------------------------
        $permissions = $_SESSION['permissions'] ?? [];
        if (!in_array($permission, $permissions)) {
            http_response_code(403);
            die("Acceso no autorizado");
        }
    }
    // Fin de la función check()

    /**
     * Verifica múltiples permisos (al menos uno debe cumplirse).
     *
     * @param  array $permissions Lista de permisos, basta que tenga uno
     * @return void
     */
    public static function checkAny(array $permissions)
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        if (($_SESSION['role'] ?? '') === 'admin') {
            return;
        }

        $userPerms = $_SESSION['permissions'] ?? [];
        foreach ($permissions as $perm) {
            if (in_array($perm, $userPerms)) {
                return;
            }
        }

        http_response_code(403);
        die("Acceso no autorizado");
    }
    // Fin de la función checkAny()
}
