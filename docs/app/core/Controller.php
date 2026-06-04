<?php
class Controller
{
    protected function view($path, $data = [])
    {
        extract($data);
        require VIEW_PATH . "/" . $path . ".php";
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
