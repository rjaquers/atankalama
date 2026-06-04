<?php
/**
 * Proyecto: Starter Kit RKM
 * Autor: Rodrigo Jaque Escobar
 * Contacto: rjaquers@gmail.com
 */
class Router
{
    public function dispatch()
    {
        // Rutas limpias: /login, /authenticate, /dashboard, etc.
        $url = $_GET['url'] ?? 'dashboard';
        $url = trim($url, "/");

        $parts = $url === '' ? ['dashboard'] : explode("/", $url);

        // Mapeo de rutas comunes a AuthController (antiguo LoginController)
        $route = strtolower($parts[0] ?? 'dashboard');
        if ($route === 'login') {
            $controller = "AuthController";
            $method     = "login";
            $params     = array_slice($parts, 1);
        } elseif ($route === 'authenticate') {
            $controller = "AuthController";
            $method     = "authenticate";
            $params     = array_slice($parts, 1);
        } elseif ($route === 'api') {
            // Detectar controladores API: /api/recurso/metodo/...params
            $controller = ucfirst($parts[1] ?? 'auth') . "ApiController";
            $method     = $parts[2] ?? "index";
            $params     = array_slice($parts, 3);
        } else {
            $controller = ucfirst($parts[0]) . "Controller";
            $method     = $parts[1] ?? "index";
            $params     = array_slice($parts, 2);
        }

        if (!class_exists($controller)) {
            http_response_code(404);
            die("Controller no encontrado: " . htmlspecialchars($controller));
        }

        $obj = new $controller();

        if (!method_exists($obj, $method)) {
            http_response_code(404);
            die("Método inválido: " . htmlspecialchars($method));
        }

        // Soporta N parámetros (para API REST: /api/chat/mensajes/123/45)
        $obj->$method(...$params);
    }
}
