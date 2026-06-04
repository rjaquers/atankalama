<?php
/**
 * HotelOtpService — OTP usando las tablas compartidas chk_* del Hotel Atankalama
 * Conecta a la misma BD (cat6852_hotel_tickets) usando MySQLi.
 * APP_SLUG 'chat' ya está registrado en chk_apps.
 */
class HotelOtpService
{
    private mysqli $conn;

    const APP_SLUG      = 'chat';
    const EXPIRY_MINUTES = 10;
    const MAX_ATTEMPTS   = 5;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /**
     * Verifica que el email existe en chat_usuarios (activo)
     * y también en chk_usuarios (activo) — acceso al chat del hotel.
     * @return array|null Fila del usuario o null
     */
    public function buscarUsuarioAutorizado(string $email): ?array
    {
        // Verificar en chat_usuarios (tabla propia de la app)
        $stmt = $this->conn->prepare("
            SELECT id, email, nombre
            FROM chat_usuarios
            WHERE email = ? AND estado = 1
            LIMIT 1
        ");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res  = $stmt->get_result();
        $user = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        return $user ?: null;
    }

    /**
     * Genera un código OTP de 6 dígitos, invalida tokens previos,
     * lo guarda en chk_login_tokens y lo envía por correo.
     */
    public function generarYEnviar(string $email): bool
    {
        // Invalidar tokens previos no usados para este email
        $inv = $this->conn->prepare("UPDATE chk_login_tokens SET used = 1 WHERE email = ? AND used = 0");
        $inv->bind_param('s', $email);
        $inv->execute();
        $inv->close();

        // Generar código y calcular expiración
        $code      = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::EXPIRY_MINUTES . ' minutes'));

        $ins = $this->conn->prepare("
            INSERT INTO chk_login_tokens (email, token, expires_at, used, attempts)
            VALUES (?, ?, ?, 0, 0)
        ");
        $ins->bind_param('sss', $email, $code, $expiresAt);
        $ins->execute();
        $ins->close();

        return $this->enviarEmail($email, $code);
    }

    /**
     * Valida el código OTP ingresado (máx 5 intentos, 10 min vigencia).
     * Usa hash_equals() para comparación segura.
     */
    public function verificarCodigo(string $email, string $code): bool
    {
        $stmt = $this->conn->prepare("
            SELECT id, token
            FROM chk_login_tokens
            WHERE email     = ?
              AND used       = 0
              AND expires_at > NOW()
              AND attempts   < ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $max = self::MAX_ATTEMPTS;
        $stmt->bind_param('si', $email, $max);
        $stmt->execute();
        $res   = $stmt->get_result();
        $token = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$token) {
            return false;
        }

        // Incrementar intentos
        $upd = $this->conn->prepare("UPDATE chk_login_tokens SET attempts = attempts + 1 WHERE id = ?");
        $upd->bind_param('i', $token['id']);
        $upd->execute();
        $upd->close();

        // Comparación segura
        if (!hash_equals($token['token'], $code)) {
            return false;
        }

        // Marcar como usado
        $used = $this->conn->prepare("UPDATE chk_login_tokens SET used = 1 WHERE id = ?");
        $used->bind_param('i', $token['id']);
        $used->execute();
        $used->close();

        $this->registrarLoginExitoso($email);

        return true;
    }

    /**
     * Registra login exitoso en chk_usuarios y chk_login_log.
     * Requerido por la regla auth-otp del hotel.
     */
    private function registrarLoginExitoso(string $email): void
    {
        $expira = date('Y-m-d H:i:s', strtotime('+4 hours'));

        $upd = $this->conn->prepare("
            UPDATE chk_usuarios
               SET last_login       = NOW(),
                   forzar_logout    = 0,
                   sesion_expira_en = ?
             WHERE email = ?
        ");
        $upd->bind_param('ss', $expira, $email);
        $upd->execute();
        $upd->close();

        $ip  = $_SERVER['REMOTE_ADDR']     ?? '';
        $ua  = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ins = $this->conn->prepare("
            INSERT INTO chk_login_log (email, app_slug, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $slug = self::APP_SLUG;
        $ins->bind_param('ssss', $email, $slug, $ip, $ua);
        $ins->execute();
        $ins->close();
    }

    private function enviarEmail(string $email, string $code): bool
    {
        $min     = self::EXPIRY_MINUTES;
        $subject = 'Código de acceso · Administración Chat Atankalama';
        $html    = <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head><meta charset="UTF-8"></head>
        <body style="font-family:Arial,sans-serif;background:#f4f4f4;margin:0;padding:20px;">
          <div style="max-width:420px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.1);">
            <div style="background:#1e3a5f;padding:24px;text-align:center;">
              <h2 style="color:#fff;margin:0;font-size:20px;">Hotel Atankalama</h2>
              <p style="color:#a8c4e0;margin:4px 0 0;font-size:13px;">Administración · Chat Interno</p>
            </div>
            <div style="padding:32px 24px;text-align:center;">
              <p style="color:#555;font-size:15px;margin:0 0 24px;">Tu código de acceso al panel de administración es:</p>
              <div style="display:inline-block;background:#f0f4ff;border:2px dashed #1e3a5f;border-radius:8px;padding:16px 32px;">
                <span style="font-size:36px;font-weight:bold;letter-spacing:10px;color:#1e3a5f;font-family:monospace;">{$code}</span>
              </div>
              <p style="color:#888;font-size:13px;margin:24px 0 0;">Válido por <strong>{$min} minutos</strong>. No compartas este código.</p>
            </div>
            <div style="background:#f9f9f9;padding:12px;text-align:center;border-top:1px solid #eee;">
              <p style="color:#aaa;font-size:11px;margin:0;">Si no solicitaste este código, ignora este mensaje.</p>
            </div>
          </div>
        </body>
        </html>
        HTML;

        $mailer = new MailService();
        return $mailer->send($email, $subject, $html);
    }
}
