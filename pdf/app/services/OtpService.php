<?php
/**
 * OtpService — genera, guarda y envía códigos OTP de 6 dígitos
 * PHP 7.4–8.2 compatible
 */
class OtpService
{
  public function generate(): string
  {
    return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
  }

  public function sendByEmail(string $email, string $code, string $nombre): bool
  {
    $subject = 'Tu codigo de acceso  Chat Atankalama';
    $html = $this->buildHtml($nombre, $code);
    $mailer = new MailService();
    return $mailer->send($email, $subject, $html);
  }

  private function buildHtml(string $nombre, string $code): string
  {
    $min = OTP_EXPIRES_MIN;
    $n = htmlspecialchars($nombre);
    $c = htmlspecialchars($code);
    return "
        <div style='font-family:Arial,sans-serif;max-width:500px;margin:0 auto;padding:20px'>
          <div style='background:#0f172a;padding:24px;border-radius:10px 10px 0 0;text-align:center'>
            <h2 style='color:#60a5fa;margin:0;font-size:22px'>&#x1F4AC; Chat Interno</h2>
            <p style='color:#94a3b8;margin:6px 0 0;font-size:13px'>Hotel Atankalama</p>
          </div>
          <div style='background:#f8fafc;padding:32px;border-radius:0 0 10px 10px;border:1px solid #e2e8f0'>
            <p style='color:#374151;margin:0 0 12px'>Hola <strong>{$n}</strong>,</p>
            <p style='color:#374151;margin:0 0 20px'>Tu c&oacute;digo de acceso al sistema de chat es:</p>
            <div style='background:#fff;border:2px dashed #3b82f6;border-radius:10px;padding:24px;text-align:center;margin:0 0 20px'>
              <span style='font-size:40px;font-weight:900;letter-spacing:14px;color:#1e3a5f;font-family:monospace'>{$c}</span>
            </div>
            <p style='color:#64748b;font-size:13px;margin:0 0 8px'>&#x23F1; Este c&oacute;digo expira en <strong>{$min} minutos</strong>.</p>
            <p style='color:#64748b;font-size:13px;margin:0'>Si no solicitaste este c&oacute;digo, ignora este mensaje. <br> Atentamente, Hotel Atankalama.
            <br><small>Nota: algunos acentos han sido eliminados para mejor legibilidad de texto.</small></p>
          </div>
        </div>";
  }
}
