<?php
/**
 * Controller de Plantillas de Contrato.
 *
 * Permite a los administradores editar el HTML base de los contratos
 * con variables dinámicas como {{nombre_empresa}}.
 *
 * @package App\Controllers
 */
class TemplatesController extends Controller
{
    /**
     * Lista las plantillas disponibles.
     */
    public function index()
    {
        PermissionMiddleware::check('templates_view');
        $model = new ContractTemplateModel();
        $templates = $model->getAll();
        $this->view('templates/index', compact('templates'));
    }

    /**
     * Muestra el formulario de creación.
     */
    public function create()
    {
        PermissionMiddleware::check('templates_manage');
        $this->view('templates/form');
    }

    /**
     * Guarda una nueva plantilla.
     */
    public function store()
    {
        PermissionMiddleware::check('templates_manage');
        csrf_verify();

        $data = [
            'name' => $_POST['name'],
            'contract_type' => $_POST['contract_type'],
            'body_html' => $_POST['body_html'],
            'header_text' => $_POST['header_text'] ?? null,
            'footer_text' => $_POST['footer_text'] ?? null
        ];

        $model = new ContractTemplateModel();
        if ($model->create($data, $_SESSION['user_id'])) {
            $this->redirect("/templates");
        }
        
        die("Error al guardar plantilla");
    }

    /**
     * Muestra el formulario de edición.
     */
    public function edit($id)
    {
        PermissionMiddleware::check('templates_manage');
        $model = new ContractTemplateModel();
        $template = $model->getById($id);
        if (!$template) die("Plantilla no encontrada");

        $this->view('templates/form', compact('template'));
    }

    /**
     * Actualiza una plantilla existente.
     */
    public function update($id)
    {
        PermissionMiddleware::check('templates_manage');
        csrf_verify();

        $data = [
            'name' => $_POST['name'],
            'contract_type' => $_POST['contract_type'],
            'body_html' => $_POST['body_html'],
            'header_text' => $_POST['header_text'] ?? null,
            'footer_text' => $_POST['footer_text'] ?? null
        ];

        $model = new ContractTemplateModel();
        if ($model->update($id, $data)) {
            $this->redirect("/templates");
        }
        
        die("Error al actualizar plantilla");
    }
}
