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
        $url = $_GET['route'] ?? 'dashboard';
        $url = trim($url, "/");

        $parts = $url === '' ? ['dashboard'] : explode("/", $url);

        $segment = strtolower($parts[0] ?? 'dashboard');

        if ($segment === 'api') {
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

        $obj->$method(...$params);
    }
}
