<?php
/**
 * AuthApiController — API REST de autenticación para la app móvil
 * Usa OTP por email (6 dígitos, 10 min) y devuelve JWT de 1 hora.
 * PHP 7.4–8.2 compatible
 */
class AuthApiController extends Controller
{
    /**
     * POST /api/auth/solicitar
     * Body JSON: { "email": "usuario@hotel.cl" }
     * Genera y envía OTP al correo. No requiere token.
     */
    public function solicitar(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $email = strtolower(trim($input['email'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['ok' => false, 'msg' => 'Correo inválido.'], 400);
        }

        $userModel = new ChatUserModel();
        $user      = $userModel->getByEmail($email);

        if (!$user || !(int)$user['estado']) {
            $this->json(['ok' => false, 'msg' => 'No existe una cuenta activa con ese correo.'], 404);
        }

        $otp = (new OtpService())->generate();
        $userModel->saveOtp((int)$user['id'], $otp);
        (new OtpService())->sendByEmail($email, $otp, $user['nombre']);

        $this->json(['ok' => true, 'msg' => 'Código enviado al correo.']);
    }

    /**
     * POST /api/auth/verificar
     * Body JSON: { "email": "usuario@hotel.cl", "code": "123456" }
     * Verifica OTP y devuelve JWT (1 hora) + datos del usuario.
     */
    public function verificar(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $email = strtolower(trim($input['email'] ?? ''));
        $code  = trim($input['code']  ?? '');

        if (!$email || !$code) {
            $this->json(['ok' => false, 'msg' => 'Correo y código son requeridos.'], 400);
        }

        $userModel = new ChatUserModel();
        $user      = $userModel->getByEmail($email);

        if (!$user || !(int)$user['estado']) {
            $this->json(['ok' => false, 'msg' => 'Usuario no encontrado.'], 404);
        }

        // Verificar código y expiración
        if (!$userModel->verifyOtp((int)$user['id'], $code)) {
            $this->json(['ok' => false, 'msg' => 'Código incorrecto o expirado.'], 401);
        }

        // Limpiar OTP y registrar acceso
        $userModel->clearOtp((int)$user['id']);
        $userModel->updateLastAccess((int)$user['id']);

        // Generar JWT con expiración de 1 hora para la app móvil
        $token = (new JwtService())->generate([
            'uid'     => (int)$user['id'],
            'email'   => $user['email'],
            'nombre'  => $user['nombre'],
            'rol'     => $user['rol_nombre'] ?? 'Operador',
            'area_id' => $user['area_id'] ? (int)$user['area_id'] : null,
            'es_jefe' => (bool)$user['es_jefe'],
        ], 3600);

        $this->json([
            'ok'    => true,
            'token' => $token,
            'user'  => [
                'id'      => (int)$user['id'],
                'nombre'  => $user['nombre'],
                'email'   => $user['email'],
                'rol'     => $user['rol_nombre'] ?? 'Operador',
                'area_id' => $user['area_id'] ? (int)$user['area_id'] : null,
                'es_jefe' => (bool)$user['es_jefe'],
                'foto'    => $user['foto_perfil'] ?: null,
            ],
        ]);
    }
}
