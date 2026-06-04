<?php
class PermissionMiddleware
{
    public static function check($permission)
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }
        $role = $_SESSION['role'] ?? 'user';

        $model = new PermissionModel();
        if (!$model->roleHasPermission($role, $permission)) {
            http_response_code(403);
            die("Acceso no autorizado");
        }
    }
}
