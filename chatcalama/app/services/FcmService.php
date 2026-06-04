<?php
/**
 * FcmService — envía notificaciones push via Firebase Cloud Messaging
 * PHP 7.4–8.2 compatible.
 * Activar en .env: FCM_ENABLED=true y FCM_SERVER_KEY=<tu clave>
 */
class FcmService
{
    private const FCM_URL = 'https://fcm.googleapis.com/fcm/send';

    /**
     * Envía notificación a un token específico.
     */
    public function send(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        if (!FCM_ENABLED || empty(FCM_SERVER_KEY) || empty($fcmToken)) {
            return false;
        }

        return $this->post([
            'to'           => $fcmToken,
            'notification' => $this->buildNotification($title, $body),
            'data'         => $data,
            'priority'     => 'high',
        ]);
    }

    /**
     * Envía notificación a múltiples tokens (hasta 1000 por llamada FCM).
     * Retorna cantidad de envíos exitosos.
     */
    public function sendMultiple(array $tokens, string $title, string $body, array $data = []): int
    {
        if (!FCM_ENABLED || empty(FCM_SERVER_KEY)) return 0;

        $tokens  = array_values(array_filter($tokens));
        if (empty($tokens)) return 0;

        $success = 0;
        foreach (array_chunk($tokens, 1000) as $chunk) {
            $ok = $this->post([
                'registration_ids' => $chunk,
                'notification'     => $this->buildNotification($title, $body),
                'data'             => $data,
                'priority'         => 'high',
            ]);
            if ($ok) $success += count($chunk);
        }
        return $success;
    }

    /**
     * Envía notificación a un área completa (por sus tokens almacenados en DB).
     */
    public function sendToArea(int $areaId, string $title, string $body, array $data = []): int
    {
        $model  = new ChatUserModel();
        $tokens = $model->getFcmTokensByArea($areaId);
        return $this->sendMultiple($tokens, $title, $body, $data);
    }

    private function buildNotification(string $title, string $body): array
    {
        return [
            'title' => $title,
            'body'  => $body,
            'sound' => 'default',
            'badge' => '1',
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];
    }

    private function post(array $payload): bool
    {
        if (!function_exists('curl_init')) {
            app_log('FcmService: cURL no disponible');
            return false;
        }

        $ch = curl_init(self::FCM_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: key=' . FCM_SERVER_KEY,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT    => 10,
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            app_log("FcmService cURL error: $error");
            return false;
        }

        $result = json_decode($response, true);
        if (isset($result['failure']) && (int)$result['failure'] > 0) {
            app_log("FcmService send failure: $response");
        }

        return isset($result['success']) && (int)$result['success'] > 0;
    }
}
