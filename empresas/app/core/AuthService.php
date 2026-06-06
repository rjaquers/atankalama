<?php
/**
 * AuthService - Atankalama Empresas
 * Gestiona la sesión del cliente
 */
class AuthService
{
    public static function login($email, $password)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT u.*, c.business_name 
                             FROM emp_users u 
                             JOIN doc_companies c ON u.company_id = c.id 
                             WHERE u.email = ? AND u.status = 1 
                             LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['emp_user_id'] = $user['id'];
            $_SESSION['emp_company_id'] = $user['company_id'];
            $_SESSION['emp_user_name'] = $user['name'];
            $_SESSION['emp_company_name'] = $user['business_name'];
            
            // Actualizar último login
            $stmt = $db->prepare("UPDATE emp_users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            return true;
        }
        return false;
    }

    public static function check()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['emp_user_id']);
    }

    public static function user()
    {
        return [
            'id' => $_SESSION['emp_user_id'] ?? null,
            'company_id' => $_SESSION['emp_company_id'] ?? null,
            'name' => $_SESSION['emp_user_name'] ?? null,
            'company_name' => $_SESSION['emp_company_name'] ?? null
        ];
    }

    public static function logout()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
    }
}
