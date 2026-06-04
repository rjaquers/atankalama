<?php

class DocCompanyController
{
    private DocCompany $model;

    public function __construct()
    {
        $this->model = new DocCompany();
    }

    public function list()
    {
        $companies = $this->model->listar();
        include __DIR__ . '/../views/doc_companies_list.php';
    }

    public function create()
    {
        $company = null; // Para que el form sea compartido (create/edit)
        include __DIR__ . '/../views/doc_companies_form.php';
    }

    public function store()
    {
        $emailActual = AccesoBootstrap::email();
        $userId      = $this->model->getUsuarioIdPorEmail($emailActual);

        $data = [
            'rut'            => trim($_POST['rut'] ?? ''),
            'business_name'  => trim($_POST['business_name'] ?? ''),
            'trade_name'     => trim($_POST['trade_name'] ?? ''),
            'contact_name'   => trim($_POST['contact_name'] ?? ''),
            'contact_email'  => trim($_POST['contact_email'] ?? ''),
            'contact_phone'  => trim($_POST['contact_phone'] ?? ''),
            'address'        => trim($_POST['address'] ?? ''),
            'city'           => trim($_POST['city'] ?? ''),
            'type'           => $_POST['type'] ?? 'cliente',
            'notes'          => trim($_POST['notes'] ?? ''),
            'active'         => isset($_POST['active']) ? 1 : 0,
            'created_by'     => $userId
        ];

        if (empty($data['business_name'])) {
            $_SESSION['flash_error'] = 'La Razón Social es obligatoria.';
            header('Location: index.php?route=doc_companies/create');
            exit;
        }

        $this->model->guardar($data);
        $_SESSION['flash_ok'] = 'Empresa creada correctamente.';
        header('Location: index.php?route=doc_companies/list');
        exit;
    }

    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: index.php?route=doc_companies/list');
            exit;
        }

        $company = $this->model->buscar($id);
        if (!$company) {
            header('Location: index.php?route=doc_companies/list');
            exit;
        }

        include __DIR__ . '/../views/doc_companies_form.php';
    }

    public function update()
    {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            header('Location: index.php?route=doc_companies/list');
            exit;
        }

        $data = [
            'rut'            => trim($_POST['rut'] ?? ''),
            'business_name'  => trim($_POST['business_name'] ?? ''),
            'trade_name'     => trim($_POST['trade_name'] ?? ''),
            'contact_name'   => trim($_POST['contact_name'] ?? ''),
            'contact_email'  => trim($_POST['contact_email'] ?? ''),
            'contact_phone'  => trim($_POST['contact_phone'] ?? ''),
            'address'        => trim($_POST['address'] ?? ''),
            'city'           => trim($_POST['city'] ?? ''),
            'type'           => $_POST['type'] ?? 'cliente',
            'notes'          => trim($_POST['notes'] ?? ''),
            'active'         => isset($_POST['active']) ? 1 : 0
        ];

        if (empty($data['business_name'])) {
            $_SESSION['flash_error'] = 'La Razón Social es obligatoria.';
            header("Location: index.php?route=doc_companies/edit&id=$id");
            exit;
        }

        $this->model->actualizar($id, $data);
        $_SESSION['flash_ok'] = 'Empresa actualizada correctamente.';
        header('Location: index.php?route=doc_companies/list');
        exit;
    }

    public function delete()
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $this->model->eliminar($id);
            $_SESSION['flash_ok'] = 'Empresa eliminada correctamente.';
        }
        header('Location: index.php?route=doc_companies/list');
        exit;
    }
}
