<?php
/**
 * Proyecto: Starter Kit RKM
 * Autor: Rodrigo Jaque Escobar
 * Contacto: rjaquers@gmail.com
 */
class PermissionModel extends Model
{
    public function roleHasPermission($roleName, $permissionName)
    {
        $stmt = $this->conn->prepare("
            SELECT 1
            FROM roles r
            JOIN role_permissions rp ON rp.role_id = r.id
            JOIN permissions p ON p.id = rp.permission_id
            WHERE r.name = ? AND p.name = ?
            LIMIT 1
        ");
        $stmt->bind_param("ss", $roleName, $permissionName);
        $stmt->execute();
        $res = $stmt->get_result();
        return ($res && $res->num_rows > 0);
    }
}
