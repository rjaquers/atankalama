<?php
require_once __DIR__ . '/../models/ProductoModel.php';

class ProductoController
{
    private $model;

    public function __construct()
    {
        $this->model = new ProductoModel();
    }

    public function index()
    {
        $productos = $this->model->obtenerTodos();
        require_once __DIR__ . '/../views/producto/index.php';
    }

    public function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre']);
            $precio = floatval($_POST['precio']);
            $activo = isset($_POST['activo']) ? 1 : 0;

            if ($this->model->crear($nombre, $precio, $activo)) {
                header('Location: index.php?page=producto/index&success=creado');
            } else {
                header('Location: index.php?page=producto/index&error=creacion');
            }
            exit;
        }
        require_once __DIR__ . '/../views/producto/crear.php';
    }

    public function editar()
    {
        if (!isset($_GET['id'])) {
            header('Location: index.php?page=producto/index');
            exit;
        }

        $id = intval($_GET['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre']);
            $precio = floatval($_POST['precio']);
            $activo = isset($_POST['activo']) ? 1 : 0;

            if ($this->model->actualizar($id, $nombre, $precio, $activo)) {
                header('Location: index.php?page=producto/index&success=actualizado');
            } else {
                header('Location: index.php?page=producto/index&error=actualizacion');
            }
            exit;
        }

        $producto = $this->model->obtenerPorId($id);
        if (!$producto) {
            header('Location: index.php?page=producto/index');
            exit;
        }

        require_once __DIR__ . '/../views/producto/editar.php';
    }

    public function eliminar()
    {
        if (!isset($_GET['id'])) {
            header('Location: index.php?page=producto/index');
            exit;
        }

        $id = intval($_GET['id']);
        if ($this->model->eliminar($id)) {
            header('Location: index.php?page=producto/index&success=eliminado');
        } else {
            header('Location: index.php?page=producto/index&error=eliminacion');
        }
        exit;
    }
}
