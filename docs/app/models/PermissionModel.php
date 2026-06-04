<?php
/**
 * Modelo de Permisos.
 *
 * Gestiona la verificación de permisos por rol.
 * Consulta la relación doc_roles → doc_role_permissions → doc_permissions
 * para determinar qué acciones puede realizar cada usuario.
 *
 * @package App\Models
 */
class PermissionModel extends Model
{
    /**
     * Verifica si un rol tiene un permiso específico.
     *
     * Qué hace:
     * - Realiza JOIN entre roles, role_permissions y permissions
     * - Busca por nombre del rol y nombre del permiso
     * - Retorna true si existe la relación
     *
     * @param  string $roleName       Nombre del rol (ej: 'admin', 'vendedor')
     * @param  string $permissionName Nombre del permiso (ej: 'contracts_view')
     * @return bool   true si el rol tiene el permiso
     */
    public function roleHasPermission($roleName, $permissionName)
    {
        $stmt = $this->conn->prepare("
            SELECT 1
            FROM doc_roles r
            JOIN doc_role_permissions rp ON rp.role_id = r.id
            JOIN doc_permissions p ON p.id = rp.permission_id
            WHERE r.name = ? AND p.name = ?
            LIMIT 1
        ");
        $stmt->bind_param("ss", $roleName, $permissionName);
        $stmt->execute();
        $res = $stmt->get_result();
        return ($res && $res->num_rows > 0);
    }
    // Fin de la función roleHasPermission()

    /**
     * Obtiene todos los nombres de permisos de un rol por su ID.
     *
     * Qué hace:
     * - Consulta los permisos asociados a un role_id
     * - Retorna un array plano con los nombres de permisos
     *
     * @param  int   $roleId ID del rol
     * @return array Lista de nombres de permisos (ej: ['contracts_view', 'contracts_create'])
     */
    public function getPermissionsByRoleId($roleId)
    {
        $stmt = $this->conn->prepare("
            SELECT p.name
            FROM doc_role_permissions rp
            JOIN doc_permissions p ON p.id = rp.permission_id
            WHERE rp.role_id = ?
        ");
        $stmt->bind_param("i", $roleId);
        $stmt->execute();
        $res = $stmt->get_result();

        $permissions = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $permissions[] = $row['name'];
            }
        }
        return $permissions;
    }
    // Fin de la función getPermissionsByRoleId()

    /**
     * Obtiene todos los permisos disponibles en el sistema.
     *
     * @return array Lista de permisos con id y name
     */
    public function getAll()
    {
        $res = $this->conn->query("SELECT * FROM doc_permissions ORDER BY name ASC");
        $perms = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $perms[] = $row;
            }
        }
        return $perms;
    }
    // Fin de la función getAll()

    /**
     * Obtiene todos los roles disponibles.
     *
     * @return array Lista de roles con id y name
     */
    public function getRoles()
    {
        $res = $this->conn->query("SELECT * FROM doc_roles ORDER BY id ASC");
        $roles = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $roles[] = $row;
            }
        }
        return $roles;
    }
    // Fin de la función getRoles()
}
