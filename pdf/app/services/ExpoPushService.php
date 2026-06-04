<?php
/**
 * ExpoPushService — envía notificaciones push via Expo Push API
 * Compatible con tokens ExponentPushToken[...] generados por expo-notifications.
 * PHP 7.4–8.2 compatible.
 */
class ExpoPushService
{
    private const EXPO_PUSH_URL = 'https://exp.host/--/api/v2/push/send';

    /**
     * Envía una notificación a uno o varios tokens.
     *
     * @param string|array $tokens  Token(s) Expo Push
     * @param string $title         Título de la notificación
     * @param string $body          Cuerpo del mensaje
     * @param array  $data          Datos adicionales (ej: ['conv_id' => 5])
     * @param int    $badge         Número del badge (0 = limpiar)
     */
    public function send(
        $tokens,
        string $title,
        string $body,
        array $data = [],
        int $badge = 1
    ): bool {
        if (!function_exists('curl_init')) {
            app_log('ExpoPushService: cURL no disponible');
            return false;
        }

        $tokens = is_array($tokens) ? $tokens : [$tokens];
        $tokens = array_values(array_filter($tokens, function ($t) {
            return is_string($t) && $t !== '';
        }));

        if (empty($tokens)) {
            return false;
        }

        // Construir mensajes (Expo acepta hasta 100 en una llamada)
        $messages = [];
        foreach ($tokens as $token) {
            $messages[] = [
                'to'       => $token,
                'title'    => $title,
                'body'     => $body,
                'sound'    => 'default',
                'badge'    => $badge,
                'data'     => $data,
                'priority' => 'high',
                'channelId' => 'mensajes',
            ];
        }

        $success = true;
        // Expo recomienda chunks de máximo 100
        foreach (array_chunk($messages, 100) as $chunk) {
            if (!$this->postToExpo($chunk)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Envía notificación a todos los participantes de una conversación,
     * excluyendo al remitente.
     */
    public function notifyConversation(
        int $convId,
        int $senderUserId,
        string $senderNombre,
        string $mensaje
    ): void {
        $tokens = (new ChatModel())->getPushTokensForConversacion($convId, $senderUserId);

        if (empty($tokens)) {
            return;
        }

        $bodyText = strlen($mensaje) > 100
            ? mb_substr($mensaje, 0, 97, 'UTF-8') . '…'
            : $mensaje;

        $this->send(
            $tokens,
            $senderNombre,
            $bodyText,
            ['conv_id' => $convId],
            1
        );
    }

    private function postToExpo(array $messages): bool
    {
        $ch = curl_init(self::EXPO_PUSH_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Accept-Encoding: gzip, deflate',
            ],
            CURLOPT_POSTFIELDS => json_encode($messages),
            CURLOPT_TIMEOUT    => 10,
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            app_log("ExpoPushService cURL error: $error");
            return false;
        }

        $result = json_decode($response, true);

        // Limpiar tokens inválidos/expirados reportados por Expo
        if (!empty($result['data'])) {
            $userModel = new ChatUserModel();
            foreach ($result['data'] as $i => $item) {
                if (($item['status'] ?? '') === 'error') {
                    $errorCode = $item['details']['error'] ?? '';
                    app_log('ExpoPush error: ' . json_encode($item));
                    if ($errorCode === 'DeviceNotRegistered' && isset($messages[$i]['to'])) {
                        $userModel->clearFcmToken($messages[$i]['to']);
                    }
                }
            }
        }

        return isset($result['data']);
    }
}
