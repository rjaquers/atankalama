<?php
namespace App\Core;

class Controller
{
    protected function render($view, $data = [], $layout = 'main')
    {
        extract($data);

        $viewPath = __DIR__ . "/../views/$view.php";

        if (!file_exists($viewPath)) {
            die("Error: La vista $view no existe.");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        $layoutPath = __DIR__ . "/../views/layouts/$layout.php";
        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    protected function redirect($url)
    {
        $target = (str_starts_with($url, 'http')) ? $url : BASE_URL . $url;
        header("Location: " . $target);
        exit;
    }

    protected function json($data, $status = 200)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
