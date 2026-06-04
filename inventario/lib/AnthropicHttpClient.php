<?php
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 *
 * Cliente HTTP para la Messages API de Anthropic.
 * Compatible con PHP 7.4+. No requiere SDK externo; usa cURL directamente.
 */

class AnthropicHttpClient
{
    private string $apiKey;
    private string $endpoint        = 'https://api.anthropic.com/v1/messages';
    private string $anthropicVersion = '2023-06-01';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Envía una solicitud a la Messages API.
     *
     * @param array $params  Conforme a Messages API: model, max_tokens, system, messages, tools
     * @return array Respuesta decodificada
     * @throws RuntimeException en error de red o HTTP
     */
    public function createMessage(array $params): array
    {
        $payload = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $ch = curl_init($this->endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: ' . $this->anthropicVersion,
            ],
            CURLOPT_TIMEOUT        => 60,
        ]);

        $body      = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new RuntimeException('Error de red al contactar Anthropic: ' . $curlError);
        }

        $data = json_decode($body, true);

        if ($httpCode !== 200) {
            $msg = $data['error']['message'] ?? "HTTP $httpCode";
            throw new RuntimeException('Error de API Anthropic: ' . $msg);
        }

        return $data;
    }
}
