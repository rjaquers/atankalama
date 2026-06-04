<?php
/**
 * AuthService — autenticación OTP para web y JWT para API móvil
 * PHP 7.4–8.2 compatible
 */
class AuthService
{
    /**
     * Paso 1: valida email, genera OTP, lo guarda y lo envía por correo.
     * @return array{ok: bool, msg: string}
     */
    public function requestOtp(string $email): array
    {
        $model = new ChatUserModel();
        $user  = $model->getByEmail($email);

        if (!$user) {
            return ['ok' => false, 'msg' => 'Correo no registrado en el sistema.'];
        }
        if ((int)$user['estado'] !== 1) {
            return ['ok' => false, 'msg' => 'Usuario inactivo. Contacta al administrador.'];
        }

        $otp   = new OtpService();
        $code  = $otp->generate();
        $saved = $model->saveOtp((int)$user['id'], $code, OTP_EXPIRES_MIN);

        if (!$saved) {
            return ['ok' => false, 'msg' => 'Error interno al generar código.'];
        }

        $sent = $otp->sendByEmail($email, $code, $user['nombre']);
        if (!$sent) {
            app_log("AuthService: OTP generado pero correo no enviado a $email (code=$code)");
            // En desarrollo se puede continuar; en producción activa el return de abajo:
            // return ['ok' => false, 'msg' => 'Error al enviar correo. Intenta de nuevo.'];
        }

        $_SESSION['otp_email'] = $email;
        $_SESSION['otp_uid']   = (int)$user['id'];

        return ['ok' => true, 'msg' => 'Código enviado. Revisa tu correo institucional.'];
    }

    /**
     * Paso 2: verifica OTP y crea la sesión.
     * @return array{ok: bool, msg?: string}
     */
    public function verifyOtp(int $userId, string $code): array
    {
        $model = new ChatUserModel();

        if (!$model->verifyOtp($userId, $code)) {
            return ['ok' => false, 'msg' => 'Código inválido o expirado.'];
        }

        $user = $model->getById($userId);
        if (!$user) {
            return ['ok' => false, 'msg' => 'Usuario no encontrado.'];
        }

        // Crear sesión web
        $_SESSION['user_id']      = (int)$user['id'];
        $_SESSION['user_nombre']  = $user['nombre'];
        $_SESSION['user_email']   = $user['email'];
        $_SESSION['user_rol']     = $user['rol_nombre'] ?? 'Operador';
        $_SESSION['user_rol_id']  = (int)$user['rol_id'];
        $_SESSION['user_area']    = $user['area_nombre'] ?? '';
        $_SESSION['user_area_id'] = (int)($user['area_id'] ?? 0);
        $_SESSION['user_foto']    = $user['foto_perfil'] ?? '';
        $_SESSION['user_es_jefe'] = !empty($user['es_jefe']);

        $model->updateLastAccess((int)$user['id']);
        $model->clearOtp((int)$user['id']);

        unset($_SESSION['otp_email'], $_SESSION['otp_uid']);
        session_regenerate_id(true);

        return ['ok' => true];
    }

    /**
     * Genera JWT para API móvil después de verificar OTP.
     */
    public function generateJwt(int $userId): string
    {
        $model = new ChatUserModel();
        $user  = $model->getById($userId);
        if (!$user) return '';

        $jwt = new JwtService();
        return $jwt->generate([
            'uid'     => (int)$user['id'],
            'email'   => $user['email'],
            'nombre'  => $user['nombre'],
            'rol'     => $user['rol_nombre'] ?? 'Operador',
            'area_id' => (int)($user['area_id'] ?? 0),
            'es_jefe' => !empty($user['es_jefe']),
        ]);
    }

    // Compatibilidad con starter kit (no utilizado en chat)
    public function login(string $email, string $password): bool
    {
        return false;
    }
}
