<?php
class AuthMiddleware
{
    public static function handle()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }
    }

    // Alias para compatibilidad
    public static function check()
    {
        self::handle();
    }
}
