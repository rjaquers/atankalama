<?php
namespace App\Core;

class Router
{
    protected $routes = [];

    public function add($method, $path, $controller, $action, $middleware = null)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action,
            'middleware' => $middleware
        ];
    }

    public function dispatch($method, $uri)
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        // Ajustar URI para instalaciones en subcarpetas
        $basePath = parse_url(BASE_URL, PHP_URL_PATH);
        if ($basePath) {
            $basePath = rtrim($basePath, '/');
            if ($basePath !== '' && strpos($uri, $basePath) === 0) {
                $uri = substr($uri, strlen($basePath));
            }
        }

        // Asegurar que la URI comience con / y no termine con / (a menos que sea la raíz)
        if ($uri === '' || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }

        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = substr($uri, 0, -1);
        }

        foreach ($this->routes as $route) {
            // Convertir /ruta/:id a un patrón regex
            $pattern = preg_replace('/:[a-zA-Z0-9]+/', '([a-zA-Z0-9]+)', $route['path']);
            $pattern = "#^" . $pattern . "$#";

            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Eliminar la coincidencia completa

                // Ejecutar Middleware si existe
                if ($route['middleware']) {
                    $middlewareClass = "App\\Middleware\\" . $route['middleware'];
                    if (class_exists($middlewareClass)) {
                        $middleware = new $middlewareClass();
                        $middleware->handle();
                    }
                }

                $controllerClass = "App\\Controllers\\" . $route['controller'];
                $action = $route['action'];

                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    if (method_exists($controller, $action)) {
                        return call_user_func_array([$controller, $action], $matches);
                    }
                }
            }
        }

        http_response_code(404);
        echo "404 - Página no encontrada";

        // Debug para el usuario en caso de error 404 persistente
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            echo "<hr><small>Debug Info: Method=$method, URI=$uri, BasePath=" . parse_url(BASE_URL, PHP_URL_PATH) . "</small>";
        }
    }
}
