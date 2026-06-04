<?php
/**
 * JwtService — genera y valida tokens JWT para la API móvil
 * Implementación propia sin dependencias externas. PHP 7.4–8.2 compatible.
 */
class JwtService
{
    /**
     * @param array $payload  Datos a incluir en el token
     * @param int   $expiresIn Segundos de expiración (0 = usar JWT_EXPIRES del .env)
     * @return string JWT token
     */
    public function generate(array $payload, int $expiresIn = 0): string
    {
        $header  = $this->b64u(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload['iat'] = time();
        $payload['exp'] = time() + ($expiresIn > 0 ? $expiresIn : JWT_EXPIRES);
        $body    = $this->b64u(json_encode($payload));
        $sig     = $this->b64u(hash_hmac('sha256', "$header.$body", JWT_SECRET, true));
        return "$header.$body.$sig";
    }

    /** @return array|null payload si válido, null si inválido/expirado */
    public function verify(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $body, $sig] = $parts;
        $expected = $this->b64u(hash_hmac('sha256', "$header.$body", JWT_SECRET, true));

        if (!hash_equals($expected, $sig)) return null;

        $payload = json_decode($this->b64uDecode($body), true);
        if (!is_array($payload) || !isset($payload['exp']) || $payload['exp'] < time()) return null;

        return $payload;
    }

    /** Extrae token del header Authorization: Bearer <token> */
    public function fromRequest(): ?array
    {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (strpos($auth, 'Bearer ') === 0) {
            return $this->verify(substr($auth, 7));
        }
        // Fallback para servidores que no pasan Authorization header
        $token = $_GET['token'] ?? '';
        return $token ? $this->verify($token) : null;
    }

    private function b64u(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function b64uDecode(string $data): string
    {
        $pad = (4 - strlen($data) % 4) % 4;
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', $pad));
    }
}
