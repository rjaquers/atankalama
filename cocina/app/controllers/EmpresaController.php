<?php
require_once __DIR__ . '/../models/EmpresaModel.php';

class EmpresaController
{
    private $model;

    public function __construct()
    {
        $this->model = new EmpresaModel();
    }

    public function index()
    {
        $empresas = $this->model->listarTodasEmpresas();
        require_once __DIR__ . '/../views/empresa/index.php';
    }

    public function ver($id = null)
    {
        $id = intval($id ?: ($_GET['id'] ?? 0));
        if (!$id) {
            header('Location: index.php?page=empresa/index');
            exit;
        }

        $empresa   = $this->model->obtenerEmpresaCompleta($id);
        if (!$empresa) {
            header('Location: index.php?page=empresa/index');
            exit;
        }

        $proyectos = $this->model->listarProyectosPorEmpresa($id);
        require_once __DIR__ . '/../views/empresa/ver.php';
    }

    public function agregarProyecto()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=empresa/index');
            exit;
        }

        $company_id = intval($_POST['company_id'] ?? 0);
        $nombre     = trim($_POST['nombre'] ?? '');
        $redirect   = $_POST['redirect'] ?? null;

        if (!$company_id || $nombre === '') {
            $back = $redirect ?? ('empresa/ver/' . $company_id);
            header('Location: index.php?page=' . $back . '&error=datos');
            exit;
        }

        $this->model->crearProyecto($company_id, $nombre);

        if ($redirect) {
            header('Location: index.php?page=' . $redirect . '&success=proyecto');
        } else {
            header('Location: index.php?page=empresa/ver/' . $company_id . '&success=proyecto');
        }
        exit;
    }

    public function eliminarProyecto()
    {
        $id         = intval($_GET['id'] ?? 0);
        $company_id = intval($_GET['company_id'] ?? 0);

        if ($id) {
            $this->model->eliminarProyecto($id);
        }

        header('Location: index.php?page=empresa/ver/' . $company_id . '&success=eliminado');
        exit;
    }
}
