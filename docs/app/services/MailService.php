<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Servicio de Envío de Correos.
 *
 * Utiliza PHPMailer (vía Composer/Autoload) para el envío de notificaciones.
 * Lee la configuración desde las constantes definidas en .env vía config.php.
 *
 * @package App\Services
 */
class MailService
{
    /**
     * Envía un correo electrónico.
     * 
     * @param string $to      Email del destinatario
     * @param string $subject Asunto
     * @param string $body    Cuerpo del mensaje (HTML soportado)
     * @return bool           true si se envió correctamente
     */
    public function send($to, $subject, $body)
    {
        // Si no hay destinatario, abortar
        if (empty($to)) return false;

        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            // Si la constante MAIL_ENCRYPTION es nula o vacía, PHPMailer lo manejará
            if (defined('MAIL_ENCRYPTION') && !empty(MAIL_ENCRYPTION)) {
                $mail->SMTPSecure = MAIL_ENCRYPTION; 
            }
            $mail->Port       = (int)MAIL_PORT;
            $mail->CharSet    = 'UTF-8';

            // Emisor y Receptor
            $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            $mail->addAddress($to);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            $mail->send();
            return true;
        } catch (Exception $e) {
            app_log("Error al enviar correo a {$to}: " . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Envía una notificación de prueba.
     * 
     * @param string $to
     * @return bool
     */
    public function sendTest($to)
    {
        $subject = "Prueba de Configuración - Sistema de Contratos Atankalama";
        $body = "<h2>¡Sistema Operativo!</h2><p>Esta es una prueba de envío de correo exitosa.</p>";
        return $this->send($to, $subject, $body);
    }
}
