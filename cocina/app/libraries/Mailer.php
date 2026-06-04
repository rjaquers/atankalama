<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';

class Mailer
{
    public static function enviar($asunto, $contenidoHtml, $destinatarios = [])
    {
        $mail = new PHPMailer(true);

        try {
            // Configuración SMTP desde config.php
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = SMTP_PORT;

            $mail->setFrom(SMTP_FROM, SMTP_NAME);

            foreach ($destinatarios as $correo) {
                $mail->addAddress($correo);
            }

            $mail->addCC('osvaldocampos@atankalama.com');
            $mail->addCC('pamelawaldron@atankalama.com');
            $mail->addCC('rodrigojaque@atankalama.com');

            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $contenidoHtml;

            $mail->send();
            return true;
        } catch (Exception $e) {
            $mensajeError = 'Error PHPMailer: '.$mail->ErrorInfo;
            error_log($mensajeError); // registra en error_log del servidor

            // También guarda en log propio
            file_put_contents(__DIR__.'/../tmp/log_reporte.txt', '['.date('Y-m-d H:i:s')."] $mensajeError".PHP_EOL, FILE_APPEND);

            return false;
        }
    }
}
