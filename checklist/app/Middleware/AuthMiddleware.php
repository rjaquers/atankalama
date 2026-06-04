<?php
namespace App\Middleware;

class AuthMiddleware
{
    public function handle()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!\AccesoBootstrap::email()) {
            header('Location: index.php?route=auth/login');
            exit();
        }
    }

    public static function check()
    {
        if (!\AccesoBootstrap::email()) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }
    }

    public static function redirectIfGuest()
    {
        if (!\AccesoBootstrap::email()) {
            header('Location: index.php?route=auth/login');
            exit();
        }
    }

    public static function isAdmin(): bool
    {
        $email = \AccesoBootstrap::email();
        if (!$email) {
            return false;
        }

        try {
            $stmt = acceso_pdo()->prepare(
                "SELECT perfil FROM chk_usuarios WHERE email = ? LIMIT 1"
            );
            $stmt->execute([$email]);
            return $stmt->fetchColumn() === 'Administrador';
        } catch (\Throwable) {
            return false;
        }
    }
}
