<?php
class NotificationService
{
    public function send($channels, $title, $message, $toEmail = null, $userId = null)
    {
        $channels = is_array($channels) ? $channels : [$channels];

        $ok = true;

        if (in_array('internal', $channels, true)) {
            $ok = $this->sendInternal($title, $message, $userId) && $ok;
        }

        if (in_array('email', $channels, true) && $toEmail) {
            $ok = $this->sendEmail($toEmail, $title, $message) && $ok;
        }

        if (in_array('telegram', $channels, true)) {
            $ok = $this->sendTelegram($title, $message) && $ok;
        }

        return $ok;
    }

    private function sendEmail($to, $title, $message)
    {
        // Template básico
        $html = $this->renderEmail($title, $message);
        $mail = new MailService();
        $sent = $mail->send($to, $title, $html);

        // Auditoría interna opcional
        $model = new NotificationModel();
        $model->insert(null, $title, $message, 'email', $sent ? 'sent' : 'failed');

        return $sent;
    }

    private function sendInternal($title, $message, $userId)
    {
        $model = new NotificationModel();
        return $model->insert($userId, $title, $message, 'internal', 'sent');
    }

    private function sendTelegram($title, $message)
    {
        if (!TELEGRAM_ENABLED) return true;

        $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
        $payload = [
            'chat_id' => TELEGRAM_CHAT_ID,
            'text'    => $title . "\n" . $message
        ];

        $ctx = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($payload),
                'timeout' => 5
            ]
        ]);

        $res = @file_get_contents($url, false, $ctx);
        $ok = ($res !== false);

        $model = new NotificationModel();
        $model->insert(null, $title, $message, 'telegram', $ok ? 'sent' : 'failed');

        return $ok;
    }

    private function renderEmail($title, $message)
    {
        ob_start();
        $t = $title;
        $m = $message;
        include VIEW_PATH . "/emails/notification.php";
        return ob_get_clean();
    }
}
