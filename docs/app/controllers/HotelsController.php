<?php
/**
 * Controller de Hoteles.
 *
 * Gestiona las acciones CRUD para los hoteles del grupo.
 * Permite configurar datos como dirección, RUT, representante legal, etc.
 *
 * @package App\Controllers
 */
class HotelsController extends Controller
{
    /**
     * Lista todos los hoteles.
     */
    public function index()
    {
        // PermissionMiddleware::check('hotels_view'); // TODO: Asegurar permisos en DB

        $model = new HotelModel();
        $hotels = $model->getAll(true); // Incluir inactivos para gestión completa
        
        $this->view('hotels/index', compact('hotels'));
    }

    /**
     * Formulario de creación.
     */
    public function create()
    {
        $hotel = [];
        $isEdit = false;
        $this->view('hotels/form', compact('hotel', 'isEdit'));
    }

    /**
     * Guarda un nuevo hotel.
     */
    public function store()
    {
        csrf_verify();
        
        $data = $this->collectFormData();

        if (empty($data['name']) || empty($data['code'])) {
            $_SESSION['flash_error'] = 'El nombre y el código son obligatorios';
            $this->redirect('/hotels/create');
            return;
        }

        $model = new HotelModel();
        if ($model->create($data)) {
            (new AuditModel())->add(AuthService::userId(), 'hoteles', 'crear', "Hotel creado: {$data['name']}");
            $_SESSION['flash_success'] = 'Hotel creado exitosamente';
            $this->redirect('/hotels');
        } else {
            $_SESSION['flash_error'] = 'Error al crear el hotel. Verifique que el código no esté duplicado.';
            $this->redirect('/hotels/create');
        }
    }

    /**
     * Formulario de edición.
     */
    public function edit($id)
    {
        $model = new HotelModel();
        $hotel = $model->getById((int)$id);

        if (!$hotel) {
            $_SESSION['flash_error'] = 'Hotel no encontrado';
            $this->redirect('/hotels');
            return;
        }

        $isEdit = true;
        $this->view('hotels/form', compact('hotel', 'isEdit'));
    }

    /**
     * Actualiza un hotel.
     */
    public function update($id)
    {
        csrf_verify();
        
        $model = new HotelModel();
        $hotel = $model->getById((int)$id);

        if (!$hotel) {
            $_SESSION['flash_error'] = 'Hotel no encontrado';
            $this->redirect('/hotels');
            return;
        }

        $data = $this->collectFormData();

        if (empty($data['name']) || empty($data['code'])) {
            $_SESSION['flash_error'] = 'El nombre y el código son obligatorios';
            $this->redirect('/hotels/edit/' . $id);
            return;
        }

        if ($model->update((int)$id, $data)) {
            (new AuditModel())->add(AuthService::userId(), 'hoteles', 'editar', "Hotel editado: {$data['name']} (ID: {$id})");
            $_SESSION['flash_success'] = 'Hotel actualizado exitosamente';
            $this->redirect('/hotels');
        } else {
            $_SESSION['flash_error'] = 'Error al actualizar el hotel';
            $this->redirect('/hotels/edit/' . $id);
        }
    }

    /**
     * Elimina (desactiva) un hotel.
     */
    public function delete($id)
    {
        csrf_verify();
        $model = new HotelModel();
        if ($model->delete((int)$id)) {
            (new AuditModel())->add(AuthService::userId(), 'hoteles', 'eliminar', "Hotel desactivado (ID: {$id})");
            $_SESSION['flash_success'] = 'Hotel desactivado exitosamente';
        } else {
            $_SESSION['flash_error'] = 'Error al eliminar el hotel';
        }
        $this->redirect('/hotels');
    }

    /**
     * Recopila datos del formulario.
     */
    private function collectFormData()
    {
        return [
            'name'                 => trim($_POST['name'] ?? ''),
            'code'                 => strtoupper(trim($_POST['code'] ?? '')),
            'rut'                  => trim($_POST['rut'] ?? ''),
            'address'              => trim($_POST['address'] ?? ''),
            'city'                 => trim($_POST['city'] ?? ''),
            'phone'                => trim($_POST['phone'] ?? ''),
            'email'                => trim($_POST['email'] ?? ''),
            'legal_representative' => trim($_POST['legal_representative'] ?? ''),
            'representative_rut'   => trim($_POST['representative_rut'] ?? ''),
        ];
    }
}
