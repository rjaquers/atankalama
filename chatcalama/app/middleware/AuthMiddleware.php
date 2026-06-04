<?php
/**
 * AuthMiddleware — protección de rutas web y API
 * La autenticación principal es manejada por AccesoBootstrap en index.php.
 * Este middleware solo verifica que los datos de chat_usuarios estén en sesión.
 */
class AuthMiddleware
{
    /** Verifica que el usuario tenga sesión activa (autenticado via hub) */
    public static function check(): void
    {
        if (empty($_SESSION['user_email'])) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
    }

    /** Solo administradores */
    public static function admin(): void
    {
        self::check();
        if (($_SESSION['user_rol'] ?? '') !== 'Administrador') {
            http_response_code(403);
            die('<h2>Acceso denegado</h2><p>Necesitas permisos de administrador.</p><a href="' . BASE_URL . '/dashboard">Volver</a>');
        }
    }

    /**
     * Retorna true si el perfil tiene nivel de jefatura o superior.
     * Agregar aquí nuevos perfiles de jefatura cuando se creen.
     */
    public static function esJefeOSuperior(string $perfil): bool
    {
        return in_array($perfil, [
            'Administrador',
            'Gerencia',
            'Jefatura de Cocina',
            'Jefatura de HouseKeeping',
            'Jefatura de Recepción',
        ], true);
    }

    /** Solo perfiles de jefatura o superiores */
    public static function jefe(): void
    {
        self::check();
        if (!self::esJefeOSuperior($_SESSION['user_rol'] ?? '')) {
            http_response_code(403);
            die('<h2>Acceso denegado</h2><p>Se requiere perfil de Jefatura o superior.</p><a href="' . BASE_URL . '/dashboard">Volver</a>');
        }
    }

    /**
     * Protege rutas API (retorna JSON 401 si token inválido).
     * @return array payload del JWT
     */
    public static function api(): array
    {
        $jwt     = new JwtService();
        $payload = $jwt->fromRequest();

        if (!$payload) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'msg' => 'Token inválido o expirado.']);
            exit;
        }

        return $payload;
    }

    /** API solo admin */
    public static function apiAdmin(): array
    {
        $payload = self::api();
        if (($payload['rol'] ?? '') !== 'Administrador') {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'msg' => 'Se requieren permisos de administrador.']);
            exit;
        }
        return $payload;
    }
}
