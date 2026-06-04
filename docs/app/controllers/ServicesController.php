<?php
/**
 * Controller de Servicios.
 *
 * Gestiona el catálogo de servicios incluibles en contratos
 * (Alojamiento, Desayuno, Lavandería, etc.).
 * Solo admin puede crear/editar/eliminar servicios.
 *
 * @package App\Controllers
 */
class ServicesController extends Controller
{
    /**
     * Lista todos los servicios.
     * Permiso requerido: services_view
     */
    public function index()
    {
        PermissionMiddleware::check('services_view');

        $model = new ServiceModel();
        $services = $model->getAll(AuthService::isAdmin());

        $this->view('services/index', compact('services'));
    }
    // Fin de la función index()

    /**
     * Muestra formulario de creación.
     * Permiso requerido: services_manage
     */
    public function create()
    {
        PermissionMiddleware::check('services_manage');

        $service = [];
        $isEdit = false;
        $this->view('services/form', compact('service', 'isEdit'));
    }
    // Fin de la función create()

    /**
     * Guarda un nuevo servicio.
     * Permiso requerido: services_manage
     */
    public function store()
    {
        PermissionMiddleware::check('services_manage');
        csrf_verify();

        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'base_price'  => (float)($_POST['base_price'] ?? 0),
        ];

        if (empty($data['name'])) {
            $_SESSION['flash_error'] = 'El nombre del servicio es obligatorio';
            $this->redirect('/services/create');
            return;
        }

        $model = new ServiceModel();
        $id = $model->create($data);

        if ($id) {
            (new AuditModel())->add(AuthService::userId(), 'servicios', 'crear', "Servicio creado: {$data['name']}");
            $_SESSION['flash_success'] = 'Servicio creado exitosamente';
        } else {
            $_SESSION['flash_error'] = 'Error al crear el servicio';
        }

        $this->redirect('/services');
    }
    // Fin de la función store()

    /**
     * Muestra formulario de edición.
     * Permiso requerido: services_manage
     *
     * @param int $id ID del servicio
     */
    public function edit($id)
    {
        PermissionMiddleware::check('services_manage');

        $model = new ServiceModel();
        $service = $model->getById((int)$id);

        if (!$service) {
            $_SESSION['flash_error'] = 'Servicio no encontrado';
            $this->redirect('/services');
            return;
        }

        $isEdit = true;
        $this->view('services/form', compact('service', 'isEdit'));
    }
    // Fin de la función edit()

    /**
     * Actualiza un servicio.
     * Permiso requerido: services_manage
     *
     * @param int $id ID del servicio
     */
    public function update($id)
    {
        PermissionMiddleware::check('services_manage');
        csrf_verify();

        $model = new ServiceModel();
        $service = $model->getById((int)$id);

        if (!$service) {
            $_SESSION['flash_error'] = 'Servicio no encontrado';
            $this->redirect('/services');
            return;
        }

        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'base_price'  => (float)($_POST['base_price'] ?? 0),
        ];

        if (empty($data['name'])) {
            $_SESSION['flash_error'] = 'El nombre del servicio es obligatorio';
            $this->redirect('/services/edit/' . $id);
            return;
        }

        $model->update((int)$id, $data);
        (new AuditModel())->add(AuthService::userId(), 'servicios', 'editar', "Servicio editado: {$data['name']} (ID: {$id})");
        $_SESSION['flash_success'] = 'Servicio actualizado exitosamente';
        $this->redirect('/services');
    }
    // Fin de la función update()

    /**
     * Soft delete de un servicio.
     * Permiso requerido: services_manage
     *
     * @param int $id ID del servicio
     */
    public function delete($id)
    {
        PermissionMiddleware::check('services_manage');
        csrf_verify();

        $model = new ServiceModel();
        if ($model->delete((int)$id)) {
            (new AuditModel())->add(AuthService::userId(), 'servicios', 'eliminar', "Servicio eliminado (ID: {$id})");
            $_SESSION['flash_success'] = 'Servicio eliminado';
        } else {
            $_SESSION['flash_error'] = 'Error al eliminar el servicio';
        }

        $this->redirect('/services');
    }
    // Fin de la función delete()
}
