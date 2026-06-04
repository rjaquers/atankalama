<!--
  = Proyecto: Starter Kit RKM =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com.                   =
  = Fecha: <?= date('Y') ?>                  =

-->
<?php
class Router
{
    public function dispatch()
    {
        // Rutas limpias: /login, /authenticate, /dashboard, etc.
        $url = $_GET['route'] ?? 'dashboard';
        $url = trim($url, "/");

        $parts = $url === '' ? ['dashboard'] : explode("/", $url);

        // Normaliza kebab-case / snake_case a StudlyCase: univ-admin → UnivAdmin
        $controllerBase = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $parts[0])));
        $controller = $controllerBase . "Controller";
        $method     = $parts[1] ?? "index";
        $param1     = $parts[2] ?? null;
        $param2     = $parts[3] ?? null;

        if (!class_exists($controller)) {
            http_response_code(404);
            die("Controller no encontrado");
        }

        $obj = new $controller();

        if (!method_exists($obj, $method)) {
            http_response_code(404);
            die("Método inválido");
        }

        // Soporta hasta 2 params sin complicar
        if ($param2 !== null) {
            $obj->$method($param1, $param2);
        } elseif ($param1 !== null) {
            $obj->$method($param1);
        } else {
            $obj->$method();
        }
    }
}
