<?php
/**
 * Router - Atankalama Empresas
 */
class Router
{
    public function dispatch()
    {
        $url = $_GET['url'] ?? 'dashboard';
        $url = rtrim($url, '/');
        $parts = explode('/', $url);

        $controllerName = ucfirst($parts[0]) . "Controller";
        
        // Protección de rutas
        if (!AuthService::check() && $controllerName !== 'LoginController') {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        $method = $parts[1] ?? "index";
        $params = array_slice($parts, 2);

        if (!class_exists($controllerName)) {
            http_response_code(404);
            die("Controlador no encontrado: " . $controllerName);
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $method)) {
            http_response_code(404);
            die("Método no encontrado: " . $method);
        }

        call_user_func_array([$controller, $method], $params);
    }
}
