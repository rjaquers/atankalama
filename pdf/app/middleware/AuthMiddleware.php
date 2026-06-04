<?php
/**
 * AuthMiddleware — protección de rutas web y API
 * PHP 7.4–8.2 compatible
 */
class AuthMiddleware
{
    /** Protege rutas web (redirige a login si no hay sesión o forzar_logout activo) */
    public static function check(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Verificar forzar_logout en chk_usuarios (control centralizado del hotel)
        $email = $_SESSION['user_email'] ?? null;
        if ($email) {
            $db   = new Database();
            $conn = $db->connect();
            $stmt = $conn->prepare("SELECT forzar_logout FROM chk_usuarios WHERE email = ? LIMIT 1");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            $stmt->close();

            if ($row && (int)$row['forzar_logout'] === 1) {
                session_destroy();
                header('Location: ' . BASE_URL . '/login?msg=sesion_cerrada');
                exit;
            }
        }
    }

    /** Solo administradores. Devuelve 403 o redirige. */
    public static function admin(): void
    {
        self::check();
        if (($_SESSION['user_rol'] ?? '') !== 'Administrador') {
            http_response_code(403);
            die('<h2>Acceso denegado</h2><p>Necesitas permisos de administrador.</p><a href="' . BASE_URL . '/dashboard">Volver</a>');
        }
    }

    /**
     * Panel de administración — requiere sesión activa, rol Administrador
     * Y doble factor OTP del hotel ($_SESSION['chat_admin_email']).
     * Si no hay sesión admin vigente, redirige a adminAuth/requestForm.
     */
    public static function hotelAdmin(): void
    {
        self::admin(); // verifica user_id y rol Administrador

        $adminEmail   = $_SESSION['chat_admin_email']   ?? null;
        $adminExpires = $_SESSION['chat_admin_expires'] ?? 0;

        if (!$adminEmail || time() > $adminExpires) {
            unset($_SESSION['chat_admin_email'], $_SESSION['chat_admin_expires']);
            $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '/usuarios');
            header('Location: ' . BASE_URL . '/adminAuth/requestForm?redirect=' . $redirect);
            exit;
        }
    }

    /** Solo Administrador o Jefe de Área */
    public static function jefe(): void
    {
        self::check();
        $rol = $_SESSION['user_rol'] ?? '';
        if (!in_array($rol, ['Administrador', 'Jefe de Área'], true)) {
            http_response_code(403);
            die('<h2>Acceso denegado</h2><p>Se requiere rol de Jefe de Área o superior.</p><a href="' . BASE_URL . '/dashboard">Volver</a>');
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
