<?php

class DocProjectController
{
    private function resolverEmpresa(int $companyId): array
    {
        if (!$companyId) {
            $_SESSION['flash_error'] = 'Empresa no especificada.';
            header('Location: index.php?route=doc_companies/list');
            exit;
        }
        $model   = new DocProject();
        $empresa = $model->empresa($companyId);
        if (!$empresa) {
            $_SESSION['flash_error'] = 'Empresa no encontrada.';
            header('Location: index.php?route=doc_companies/list');
            exit;
        }
        return $empresa;
    }

    private function urlLista(int $companyId): string
    {
        return "index.php?route=doc_companies/proyectos&company={$companyId}";
    }

    public function list(): void
    {
        $companyId = (int) ($_GET['company'] ?? 0);
        $empresa   = $this->resolverEmpresa($companyId);

        $model     = new DocProject();
        $proyectos = $model->listarPorEmpresa($companyId);

        include __DIR__ . '/../views/doc_projects_list.php';
    }

    public function create(): void
    {
        $companyId = (int) ($_GET['company'] ?? 0);
        $empresa   = $this->resolverEmpresa($companyId);
        $proyecto  = null;

        include __DIR__ . '/../views/doc_projects_form.php';
    }

    public function store(): void
    {
        $companyId = (int) ($_POST['company_id'] ?? 0);
        $empresa   = $this->resolverEmpresa($companyId);

        $name = trim($_POST['name'] ?? '');

        if (empty($name)) {
            $_SESSION['flash_error'] = 'El nombre del proyecto es obligatorio.';
            header("Location: index.php?route=doc_companies/proyectos/create&company={$companyId}");
            exit;
        }

        $model = new DocProject();

        if ($model->nombreExiste($name, $companyId)) {
            $_SESSION['flash_error'] = 'Ya existe un proyecto con ese nombre para esta empresa.';
            header("Location: index.php?route=doc_companies/proyectos/create&company={$companyId}");
            exit;
        }

        $model->crear($companyId, $name);
        $_SESSION['flash_ok'] = "Proyecto <strong>" . htmlspecialchars($name) . "</strong> creado correctamente.";
        header("Location: " . $this->urlLista($companyId));
        exit;
    }

    public function edit(): void
    {
        $id        = (int) ($_GET['id']      ?? 0);
        $companyId = (int) ($_GET['company'] ?? 0);
        $empresa   = $this->resolverEmpresa($companyId);

        $model    = new DocProject();
        $proyecto = $model->buscar($id);

        if (!$proyecto || (int) $proyecto['company_id'] !== $companyId) {
            $_SESSION['flash_error'] = 'Proyecto no encontrado.';
            header("Location: " . $this->urlLista($companyId));
            exit;
        }

        include __DIR__ . '/../views/doc_projects_form.php';
    }

    public function update(): void
    {
        $id        = (int) ($_POST['id']         ?? 0);
        $companyId = (int) ($_POST['company_id'] ?? 0);
        $empresa   = $this->resolverEmpresa($companyId);

        $name   = trim($_POST['name'] ?? '');
        $active = isset($_POST['active']) ? 1 : 0;

        if (empty($name)) {
            $_SESSION['flash_error'] = 'El nombre del proyecto es obligatorio.';
            header("Location: index.php?route=doc_companies/proyectos/edit&id={$id}&company={$companyId}");
            exit;
        }

        $model    = new DocProject();
        $proyecto = $model->buscar($id);

        if (!$proyecto || (int) $proyecto['company_id'] !== $companyId) {
            $_SESSION['flash_error'] = 'Proyecto no encontrado.';
            header("Location: " . $this->urlLista($companyId));
            exit;
        }

        if ($model->nombreExiste($name, $companyId, $id)) {
            $_SESSION['flash_error'] = 'Ya existe un proyecto con ese nombre para esta empresa.';
            header("Location: index.php?route=doc_companies/proyectos/edit&id={$id}&company={$companyId}");
            exit;
        }

        $model->actualizar($id, $name, $active);
        $_SESSION['flash_ok'] = 'Proyecto actualizado correctamente.';
        header("Location: " . $this->urlLista($companyId));
        exit;
    }

    public function delete(): void
    {
        $id        = (int) ($_POST['id']         ?? 0);
        $companyId = (int) ($_POST['company_id'] ?? 0);
        $empresa   = $this->resolverEmpresa($companyId);

        $model    = new DocProject();
        $proyecto = $model->buscar($id);

        if (!$proyecto || (int) $proyecto['company_id'] !== $companyId) {
            $_SESSION['flash_error'] = 'Proyecto no encontrado.';
            header("Location: " . $this->urlLista($companyId));
            exit;
        }

        $model->eliminar($id);
        $_SESSION['flash_ok'] = 'Proyecto eliminado.';
        header("Location: " . $this->urlLista($companyId));
        exit;
    }
}
