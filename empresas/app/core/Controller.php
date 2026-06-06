<?php
/**
 * Controller Base - Atankalama Empresas
 * Reutiliza la lógica del Starter Kit RKM
 */
class Controller
{
    protected function view($path, $data = [])
    {
        extract($data);
        $viewFile = "../app/views/" . $path . ".php";
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            die("La vista {$path} no existe.");
        }
    }

    protected function json($data, $status = 200)
    {
        http_response_code($status);
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode($data);
        exit;
    }

    protected function redirect($path)
    {
        header("Location: " . BASE_URL . $path);
        exit;
    }
}
