<?php
/**
 * ===================================================
 * Servicio: OtpService
 * Proyecto: Hotel Atankalama – Sistema de Contratos
 * PHP: 7.4 compatible
 * ===================================================
 *
 * Responsabilidad:
 * Gestiona la generación, envío y verificación de códigos
 * OTP de 6 dígitos para el login sin contraseña.
 */
class OtpService
{
    /**
     * Genera un código numérico de 6 dígitos de forma segura.
     *
     * Qué hace:
     * - Usa random_int() para garantizar aleatoriedad criptográfica
     * - Rellena con ceros a la izquierda si el número es menor a 100000
     *
     * @return string Código de 6 dígitos (ej: "048293")
     */
    public function generateCode()
    {
        return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
    // Fin de la función generateCode()

    /**
     * Genera el OTP, lo persiste en BD y lo envía por correo al usuario.
     *
     * Qué hace:
     * - Genera el código de 6 dígitos
     * - Calcula la expiración (10 minutos)
     * - Guarda el código en doc_users via UserModel::saveOtp()
     * - Envía el correo con el diseño institucional via MailService
     *
     * @param  array $user Datos del usuario (debe tener 'id', 'email', 'name')
     * @return bool  true si el correo se envió correctamente
     */
    public function sendOtp($user)
    {
        $code    = $this->generateCode();
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Persistir en BD
        $userModel = new UserModel();
        $userModel->saveOtp((int)$user['id'], $code, $expires);

        // Construir y enviar el correo
        $mailService = new MailService();
        $subject     = 'Tu código de acceso – ' . APP_NAME;
        $body        = $this->buildEmailBody($code);

        return $mailService->send($user['email'], $subject, $body);
    }
    // Fin de la función sendOtp()

    /**
     * Verifica el OTP ingresado, crea la sesión si es válido y limpia el código.
     *
     * Qué hace:
     * - Busca el usuario por email+OTP (con validación de expiración en la query)
     * - Si es válido: carga la sesión, carga permisos y limpia el OTP
     * - Si no es válido: retorna false sin revelar detalles
     *
     * @param  string $email Email ingresado en el formulario
     * @param  string $code  Código OTP de 6 dígitos ingresado por el usuario
     * @return bool   true si el login fue exitoso
     */
    public function verifyAndLogin($email, $code)
    {
        $userModel = new UserModel();
        $user      = $userModel->getUserByOtp($email, $code);

        if (!$user) return false;

        // Cargar sesión (mismo patrón que AuthService::login())
        $_SESSION['user_id']    = (int)$user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role_id']    = (int)$user['role_id'];
        $_SESSION['role']       = $user['role_name'] ?? 'user';

        // Cargar permisos del rol
        $permModel               = new PermissionModel();
        $_SESSION['permissions'] = $permModel->getPermissionsByRoleId((int)$user['role_id']);

        // Limpiar OTP y regenerar sesión por seguridad
        $userModel->clearOtp((int)$user['id']);
        session_regenerate_id(true);

        return true;
    }
    // Fin de la función verifyAndLogin()

    /**
     * Construye el HTML del correo de notificación OTP.
     *
     * @param  string $code Código OTP de 6 dígitos
     * @return string HTML del correo
     */
    private function buildEmailBody($code)
    {
        return "
        <div style=\"font-family: 'Segoe UI', Arial, sans-serif; max-width: 480px; margin: 0 auto; padding: 32px 24px; background: #f4f7fb;\">
            <div style=\"background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);\">
                <!-- Header -->
                <div style=\"background: linear-gradient(135deg, #1a3a5c 0%, #2c5f8a 100%); padding: 28px 32px; text-align: center;\">
                    <p style=\"margin: 0; color: rgba(255,255,255,0.85); font-size: 14px; letter-spacing: 1px; text-transform: uppercase;\">Sistema de Contratos</p>
                    <p style=\"margin: 4px 0 0; color: rgba(255,255,255,0.6); font-size: 12px;\">Hotel Atankalama</p>
                </div>
                <!-- Body -->
                <div style=\"padding: 36px 32px; text-align: center;\">
                    <p style=\"margin: 0 0 8px; color: #333; font-size: 16px;\">Hola,</p>
                    <p style=\"margin: 0 0 28px; color: #555; font-size: 14px; line-height: 1.6;\">
                        Tu código de acceso para el sistema <strong>Contratos</strong>
                    </p>
                    <!-- OTP Code -->
                    <div style=\"background: #f0f5ff; border: 2px dashed #2c5f8a; border-radius: 10px; padding: 20px 32px; display: inline-block; margin-bottom: 28px;\">
                        <span style=\"font-size: 40px; font-weight: 700; letter-spacing: 12px; color: #1a3a5c; font-family: 'Courier New', monospace;\">{$code}</span>
                    </div>
                    <p style=\"margin: 0 0 8px; color: #888; font-size: 13px;\">⏱ Este código expirará en <strong>10 minutos</strong>.</p>
                    <p style=\"margin: 0; color: #aaa; font-size: 12px;\">Si no solicitaste este código, puedes ignorar este correo.</p>
                </div>
                <!-- Footer -->
                <div style=\"background: #f8f9fa; padding: 16px 32px; text-align: center; border-top: 1px solid #eee;\">
                    <p style=\"margin: 0; color: #bbb; font-size: 11px;\">— Hotel Atankalama · Sistema de Contratos —</p>
                </div>
            </div>
        </div>";
    }
    // Fin de la función buildEmailBody()
}
