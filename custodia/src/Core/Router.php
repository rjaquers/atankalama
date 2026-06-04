<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Router simple pero robusto.
 *
 * - Soporta métodos GET/POST (y otros vía add()).
 * - Usa patrones tipo regex sobre la ruta normalizada sin BASE_URL.
 * - Se integra bien con el helper global current_path() si lo prefieres.
 */
class Router
{
    private string $baseUrl;

    /**
     * @var array<int, array{method:string, pattern:string, handler:callable}>
     */
    private array $routes = [];

    public function __construct(string $baseUrl = '/')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function add(string $method, string $pattern, callable $handler): void
    {
        $regex = '#^' . $pattern . '$#i';

        $this->routes[] = [
            'method'  => strtoupper($method),
            'pattern' => $regex,
            'handler' => $handler,
        ];
    }

    /**
     * Despacha la petición actual.
     *
     * @param string      $method
     * @param string|null $uri
     */
    public function dispatch(string $method, ?string $uri = null): void
    {
        $method = strtoupper($method);
        $uri    = $uri ?? ($_SERVER['REQUEST_URI'] ?? '/');

        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $path = $this->normalizePath($path);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                array_shift($matches);
                \call_user_func_array($route['handler'], $matches);
                return;
            }
        }

        if (!headers_sent()) {
            http_response_code(404);
        }
        echo '404 – Ruta no encontrada (' . htmlspecialchars($path, ENT_QUOTES, 'UTF-8') . ')';
    }

    /**
     * Construye URL relativa a la base.
     */
    public function url(string $path = '/'): string
    {
        if ($path === '' || $path[0] !== '/') {
            $path = '/' . $path;
        }

        return $this->baseUrl . $path;
    }

    /**
     * Redirección simple.
     */
    public function redirect(string $path = '/', int $status = 302): void
    {
        if (!headers_sent()) {
            header('Location: ' . $this->url($path), true, $status);
        }
        exit;
    }

    private function normalizePath(string $uri): string
    {
        $uri = str_replace('\\', '/', $uri);
        $uri = (string)preg_replace('#/+#', '/', $uri);

        // Remover BASE_URL si viene al inicio
        if ($this->baseUrl && strpos($uri, $this->baseUrl) === 0) {
            $uri = substr($uri, strlen($this->baseUrl)) ?: '/';
        }

        if ($uri === '') {
            $uri = '/';
        }

        return $uri;
    }
}

