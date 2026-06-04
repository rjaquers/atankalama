<?php
/**
 * MailService
 * - Compatible PHP 7.4–8.2
 * - Soporta SMTP (recomendado) y fallback a mail()
 * - Si deseas PHPMailer:
 *   1) Descarga PHPMailer y colócalo en /vendor/phpmailer/
 *   2) Descomenta los require_once de abajo
 */
class MailService
{
    public function send($to, $subject, $html)
    {
        if (MAIL_DRIVER === 'smtp') {
            return $this->sendSmtp($to, $subject, $html);
        }
        return $this->sendNative($to, $subject, $html);
    }

    private function sendSmtp($to, $subject, $html)
    {
        // PHPMailer (opcional, recomendado)
        $phpMailerBase = APP_ROOT . "/vendor/phpmailer/src/";
        $classFile = $phpMailerBase . "PHPMailer.php";

        if (file_exists($classFile)) {

            require_once $phpMailerBase . "PHPMailer.php";
            require_once $phpMailerBase . "SMTP.php";
            require_once $phpMailerBase . "Exception.php";

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = MAIL_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = MAIL_USERNAME;
                $mail->Password = MAIL_PASSWORD;

                if (MAIL_ENCRYPTION) {
                    $mail->SMTPSecure = MAIL_ENCRYPTION;
                }

                $mail->Port = (int)MAIL_PORT;

                $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
                $mail->addAddress($to);

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $html;

                $mail->send();
                return true;

            } catch (\Exception $e) {
                app_log("MAIL SMTP FAIL: " . $e->getMessage());
                return false;
            }
        }

        // Fallback si PHPMailer no está instalado
        app_log("MAIL WARNING: PHPMailer no encontrado en /vendor/phpmailer/. Usando mail() fallback.");
        return $this->sendNative($to, $subject, $html);
    }

    private function sendNative($to, $subject, $html)
    {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_EMAIL . ">\r\n";
        return @mail($to, $subject, $html, $headers);
    }
}
