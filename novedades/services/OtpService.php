<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class OtpService
{
    private PDO $pdo;

    // Minutos de validez del código
    const EXPIRY_MINUTES = 10;
    // Máximo de intentos fallidos antes de invalidar
    const MAX_ATTEMPTS = 5;
    // Slug de la app protegida
    const APP_SLUG = 'novedades';

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Verifica que el email existe en chk_usuarios, está activo
     * y tiene acceso a la app 'novedades'.
     *
     * @param string $email
     * @return array|false  Fila del usuario o false
     */
    public function buscarUsuarioAutorizado(string $email)
    {
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.email, u.perfil
            FROM chk_usuarios u
            JOIN chk_usuario_apps ua ON ua.usuario_id = u.id
            JOIN chk_apps a          ON a.id = ua.app_id
            WHERE u.email   = ?
              AND u.estado  = 'activo'
              AND a.slug    = ?
              AND a.estado  = 'activo'
            LIMIT 1
        ");
        $stmt->execute([$email, self::APP_SLUG]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Genera un código OTP de 6 dígitos, lo guarda en chk_login_tokens
     * (invalida cualquier token previo no usado del mismo email)
     * y envía el correo al usuario.
     *
     * @param string $email
     * @param string $ip
     * @param string $userAgent
     * @return bool
     */
    public function generarYEnviar(string $email, string $ip = '', string $userAgent = ''): bool
    {
        // Invalidar tokens previos vigentes para este email
        $this->pdo->prepare("
            UPDATE chk_login_tokens
            SET used = 1
            WHERE email = ? AND used = 0
        ")->execute([$email]);

        // Generar código de 6 dígitos
        $code      = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::EXPIRY_MINUTES . ' minutes'));

        $stmt = $this->pdo->prepare("
            INSERT INTO chk_login_tokens (email, token, expires_at, used, attempts, ip_address, user_agent)
            VALUES (?, ?, ?, 0, 0, ?, ?)
        ");
        $stmt->execute([$email, $code, $expiresAt, $ip, $userAgent]);

        return $this->enviarEmail($email, $code);
    }

    /**
     * Valida el código ingresado por el usuario.
     *
     * @param string $email
     * @param string $code
     * @return bool
     */
    public function verificarCodigo(string $email, string $code): bool
    {
        // Buscar token vigente, no usado, sin exceder intentos
        $stmt = $this->pdo->prepare("
            SELECT id, attempts
            FROM chk_login_tokens
            WHERE email      = ?
              AND used        = 0
              AND expires_at  > NOW()
              AND attempts    < ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$email, self::MAX_ATTEMPTS]);
        $token = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$token) {
            return false;
        }

        // Incrementar intentos
        $this->pdo->prepare("UPDATE chk_login_tokens SET attempts = attempts + 1 WHERE id = ?")
                  ->execute([$token['id']]);

        // Comparar código (timing-safe)
        if (!hash_equals($code, $this->obtenerCodigoPorId($token['id']))) {
            return false;
        }

        // Marcar como usado
        $this->pdo->prepare("UPDATE chk_login_tokens SET used = 1 WHERE id = ?")
                  ->execute([$token['id']]);

        return true;
    }

    /**
     * Obtiene el token (código) por ID (evita re-query del código en texto plano).
     */
    private function obtenerCodigoPorId(int $id): string
    {
        $stmt = $this->pdo->prepare("SELECT token FROM chk_login_tokens WHERE id = ?");
        $stmt->execute([$id]);
        return (string)($stmt->fetchColumn() ?? '');
    }

    /**
     * Envía el correo con el código OTP usando PHPMailer.
     */
    private function enviarEmail(string $email, string $code): bool
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->Port       = SMTP_PORT;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Código de acceso · Hotel Atankalama';
            $mail->Body    = $this->plantillaEmail($code);
            $mail->AltBody = "Tu código de acceso es: $code\nVigente por " . self::EXPIRY_MINUTES . " minutos.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('OtpService::enviarEmail error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * HTML del correo OTP.
     */
    private function plantillaEmail(string $code): string
    {
        $minutos = self::EXPIRY_MINUTES;
        return <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head><meta charset="UTF-8"></head>
        <body style="font-family:Arial,sans-serif;background:#f4f4f4;margin:0;padding:20px;">
          <div style="max-width:420px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.1);">
            <div style="background:#1e3a5f;padding:24px;text-align:center;">
              <h2 style="color:#fff;margin:0;font-size:20px;">Hotel Atankalama</h2>
              <p style="color:#a8c4e0;margin:4px 0 0;font-size:13px;">Sistema de Novedades</p>
            </div>
            <div style="padding:32px 24px;text-align:center;">
              <p style="color:#555;font-size:15px;margin:0 0 24px;">Tu código de acceso es:</p>
              <div style="display:inline-block;background:#f0f4ff;border:2px dashed #1e3a5f;border-radius:8px;padding:16px 32px;">
                <span style="font-size:36px;font-weight:bold;letter-spacing:10px;color:#1e3a5f;">{$code}</span>
              </div>
              <p style="color:#888;font-size:13px;margin:24px 0 0;">Válido por <strong>{$minutos} minutos</strong>. No compartas este código.</p>
            </div>
            <div style="background:#f9f9f9;padding:12px;text-align:center;border-top:1px solid #eee;">
              <p style="color:#aaa;font-size:11px;margin:0;">Si no solicitaste este código, ignora este mensaje.</p>
            </div>
          </div>
        </body>
        </html>
        HTML;
    }
}
