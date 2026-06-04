<?php
/**
 * Controller de Espacios Arrendables.
 *
 * Gestiona el CRUD de espacios del hotel y el catálogo de extras.
 * Requiere permisos: spaces_view, spaces_manage.
 *
 * @package App\Controllers
 */
class SpacesController extends Controller
{
    /**
     * Lista todos los espacios.
     * Permiso requerido: spaces_view
     */
    public function index()
    {
        PermissionMiddleware::check('spaces_view');

        $filters = [];
        if (!empty($_GET['space_type'])) {
            $filters['space_type'] = $_GET['space_type'];
        }
        if (isset($_GET['active']) && $_GET['active'] !== '') {
            $filters['active'] = (int)$_GET['active'];
        }

        $spaces = (new SpaceModel())->getAll($filters);
        $this->view('spaces/index', compact('spaces', 'filters'));
    }
    // Fin de la función index()

    /**
     * Muestra formulario de creación de espacio.
     * Permiso requerido: spaces_manage
     */
    public function create()
    {
        PermissionMiddleware::check('spaces_manage');

        $hotels = (new HotelModel())->getAll();
        $space = null;
        $this->view('spaces/form', compact('hotels', 'space'));
    }
    // Fin de la función create()

    /**
     * Guarda un nuevo espacio.
     * Permiso requerido: spaces_manage
     */
    public function store()
    {
        PermissionMiddleware::check('spaces_manage');
        csrf_verify();

        $data = $this->collectFormData();

        if (empty($data['name'])) {
            $_SESSION['flash_error'] = 'El nombre del espacio es obligatorio';
            $this->redirect('/spaces/create');
            return;
        }

        // Auto-generar código si no se ingresó
        if (empty($data['code'])) {
            $data['code'] = (new SpaceModel())->generateCode($data['space_type']);
        }

        $model = new SpaceModel();
        
        // --- Procesar Imagen Principal ---
        if (!empty($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            $uploader = new SpaceUploadService();
            $path = $uploader->upload($_FILES['main_image'], 0, true);
            if ($path) $data['main_image'] = $path;
        }

        $id = $model->create($data, AuthService::userId());

        if ($id) {
            // Re-subir/Renombrar si fuera necesario, o simplemente procesar la galería ahora
            if (!empty($_FILES['gallery_photos'])) {
                $uploader = new SpaceUploadService();
                $photos = $_FILES['gallery_photos'];
                for ($i = 0; $i < count($photos['name']); $i++) {
                    $photoFile = [
                        'name'     => $photos['name'][$i],
                        'type'     => $photos['type'][$i],
                        'tmp_name' => $photos['tmp_name'][$i],
                        'error'    => $photos['error'][$i],
                        'size'     => $photos['size'][$i]
                    ];
                    $path = $uploader->upload($photoFile, $id, false);
                    if ($path) {
                        $model->addPhoto($id, $path, $photos['name'][$i]);
                    }
                }
            }

            (new AuditModel())->add(AuthService::userId(), 'espacios', 'crear', "Espacio creado: {$data['name']} ({$data['code']})");
            $_SESSION['flash_success'] = 'Espacio creado exitosamente';
            $this->redirect('/spaces/show/' . $id);
        } else {
            $_SESSION['flash_error'] = 'Error al crear el espacio';
            $this->redirect('/spaces/create');
        }
    }
    // Fin de la función store()

    /**
     * Muestra detalle de un espacio.
     * Permiso requerido: spaces_view
     */
    public function show($id)
    {
        PermissionMiddleware::check('spaces_view');

        $space = (new SpaceModel())->getById((int)$id);
        if (!$space) {
            $_SESSION['flash_error'] = 'Espacio no encontrado';
            $this->redirect('/spaces');
            return;
        }

        // Reservas recientes del espacio
        $bookingModel = new SpaceBookingModel();
        $bookings = $bookingModel->getAll(['space_id' => (int)$id]);

        $photos = (new SpaceModel())->getPhotos((int)$id);

        $this->view('spaces/show', compact('space', 'bookings', 'photos'));
    }
    // Fin de la función show()

    /**
     * Muestra formulario de edición de espacio.
     * Permiso requerido: spaces_manage
     */
    public function edit($id)
    {
        PermissionMiddleware::check('spaces_manage');

        $model = new SpaceModel();
        $space = $model->getById((int)$id);
        if (!$space) {
            $_SESSION['flash_error'] = 'Espacio no encontrado';
            $this->redirect('/spaces');
            return;
        }

        $hotels = (new HotelModel())->getAll();
        $photos = $model->getPhotos((int)$id);

        $this->view('spaces/form', compact('space', 'hotels', 'photos'));
    }
    // Fin de la función edit()

    /**
     * Actualiza un espacio existente.
     * Permiso requerido: spaces_manage
     */
    public function update($id)
    {
        PermissionMiddleware::check('spaces_manage');
        csrf_verify();

        $data = $this->collectFormData();
        $data['active'] = (int)($_POST['active'] ?? 1);

        if (empty($data['name'])) {
            $_SESSION['flash_error'] = 'El nombre del espacio es obligatorio';
            $this->redirect('/spaces/edit/' . $id);
            return;
        }

        $model = new SpaceModel();
        $current = $model->getById((int)$id);

        // --- Procesar Imagen Principal ---
        if (!empty($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            $uploader = new SpaceUploadService();
            $path = $uploader->upload($_FILES['main_image'], $id, true);
            if ($path) {
                // Borrar anterior si existía
                if (!empty($current['main_image'])) $uploader->deleteFile($current['main_image']);
                $data['main_image'] = $path;
            }
        } else {
            // Mantener imagen anterior
            $data['main_image'] = $current['main_image'] ?? null;
        }

        if ($model->update((int)$id, $data, AuthService::userId())) {
            
            // --- Procesar Galería ---
            if (!empty($_FILES['gallery_photos']) && !empty($_FILES['gallery_photos']['name'][0])) {
                $uploader = new SpaceUploadService();
                $photos = $_FILES['gallery_photos'];
                for ($i = 0; $i < count($photos['name']); $i++) {
                    $photoFile = [
                        'name'     => $photos['name'][$i],
                        'type'     => $photos['type'][$i],
                        'tmp_name' => $photos['tmp_name'][$i],
                        'error'    => $photos['error'][$i],
                        'size'     => $photos['size'][$i]
                    ];
                    $path = $uploader->upload($photoFile, $id, false);
                    if ($path) {
                        $model->addPhoto($id, $path, $photos['name'][$i]);
                    }
                }
            }

            (new AuditModel())->add(AuthService::userId(), 'espacios', 'editar', "Espacio editado ID: {$id}");
            $_SESSION['flash_success'] = 'Espacio actualizado';
            $this->redirect('/spaces/show/' . $id);
        } else {
            $_SESSION['flash_error'] = 'Error al actualizar';
            $this->redirect('/spaces/edit/' . $id);
        }
    }
    // Fin de la función update()

    /**
     * Soft-delete de un espacio.
     * Permiso requerido: spaces_manage
     */
    public function delete($id)
    {
        PermissionMiddleware::check('spaces_manage');
        csrf_verify();

        if ((new SpaceModel())->delete((int)$id)) {
            (new AuditModel())->add(AuthService::userId(), 'espacios', 'inactivar', "Espacio inactivado ID: {$id}");
            $_SESSION['flash_success'] = 'Espacio inactivado';
        } else {
            $_SESSION['flash_error'] = 'Error al inactivar espacio';
        }
        $this->redirect('/spaces');
    }
    // Fin de la función delete()

    /**
     * Elimina una foto de la galería vía AJAX.
     * 
     * @param int $id ID de la foto (doc_space_photos)
     */
    public function deletePhoto($id)
    {
        PermissionMiddleware::check('spaces_manage');

        $model = new SpaceModel();
        // Tendríamos que buscar el path para borrarlo físicamente
        $conn = (new Database())->connect();
        $stmt = $conn->prepare("SELECT file_path FROM doc_space_photos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $photo = $res->fetch_assoc();

        if ($photo) {
            $uploader = new SpaceUploadService();
            $uploader->deleteFile($photo['file_path']);
            if ($model->deletePhoto((int)$id)) {
                $this->json(['status' => true, 'message' => 'Foto eliminada']);
                return;
            }
        }

        $this->json(['status' => false, 'message' => 'Error al eliminar foto'], 500);
    }
    // Fin de la función deletePhoto()

    /**
     * Elimina la imagen principal de un espacio vía AJAX.
     * 
     * @param int $id ID del espacio
     */
    public function removeMainImage($id)
    {
        PermissionMiddleware::check('spaces_manage');

        $model = new SpaceModel();
        $space = $model->getById((int)$id);

        if ($space && !empty($space['main_image'])) {
            $uploader = new SpaceUploadService();
            $uploader->deleteFile($space['main_image']);

            // Actualizar DB
            $conn = (new Database())->connect();
            $stmt = $conn->prepare("UPDATE doc_spaces SET main_image = NULL WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            $this->json(['status' => true, 'message' => 'Imagen eliminada']);
            return;
        }

        $this->json(['status' => false, 'message' => 'No hay imagen para eliminar'], 400);
    }
    // Fin de la función removeMainImage()

    // ===================================
    // EXTRAS
    // ===================================

    /**
     * Lista los extras cobrables.
     * Permiso requerido: spaces_manage
     */
    public function extras()
    {
        PermissionMiddleware::check('spaces_manage');
        $extras = (new SpaceExtraModel())->getAll();
        $this->view('spaces/extras', compact('extras'));
    }
    // Fin de la función extras()

    /**
     * Guarda un extra nuevo.
     * Permiso requerido: spaces_manage
     */
    public function storeExtra()
    {
        PermissionMiddleware::check('spaces_manage');
        csrf_verify();

        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'charge_type' => $_POST['charge_type'] ?? 'fijo',
            'unit_price'  => (float)($_POST['unit_price'] ?? 0),
        ];

        if (empty($data['name'])) {
            $_SESSION['flash_error'] = 'El nombre del extra es obligatorio';
            $this->redirect('/spaces/extras');
            return;
        }

        if ((new SpaceExtraModel())->create($data)) {
            (new AuditModel())->add(AuthService::userId(), 'espacios', 'crear_extra', "Extra creado: {$data['name']}");
            $_SESSION['flash_success'] = 'Extra creado exitosamente';
        } else {
            $_SESSION['flash_error'] = 'Error al crear el extra';
        }
        $this->redirect('/spaces/extras');
    }
    // Fin de la función storeExtra()

    /**
     * Actualiza un extra existente.
     * Permiso requerido: spaces_manage
     */
    public function updateExtra($id)
    {
        PermissionMiddleware::check('spaces_manage');
        csrf_verify();

        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'charge_type' => $_POST['charge_type'] ?? 'fijo',
            'unit_price'  => (float)($_POST['unit_price'] ?? 0),
            'active'      => (int)($_POST['active'] ?? 1),
        ];

        if ((new SpaceExtraModel())->update((int)$id, $data)) {
            $_SESSION['flash_success'] = 'Extra actualizado';
        } else {
            $_SESSION['flash_error'] = 'Error al actualizar extra';
        }
        $this->redirect('/spaces/extras');
    }
    // Fin de la función updateExtra()

    // ===================================
    // HELPERS
    // ===================================

    /**
     * Recopila datos del formulario de espacio.
     *
     * @return array Datos del formulario
     */
    private function collectFormData()
    {
        return [
            'code'               => trim($_POST['code'] ?? ''),
            'name'               => trim($_POST['name'] ?? ''),
            'space_type'         => $_POST['space_type'] ?? 'salon',
            'description'        => trim($_POST['description'] ?? ''),
            'capacity'           => !empty($_POST['capacity']) ? (int)$_POST['capacity'] : null,
            'location'           => trim($_POST['location'] ?? ''),
            'allows_hourly'      => (int)($_POST['allows_hourly'] ?? 0),
            'allows_daily'       => (int)($_POST['allows_daily'] ?? 0),
            'allows_monthly'     => (int)($_POST['allows_monthly'] ?? 0),
            'base_price_hour'    => !empty($_POST['base_price_hour']) ? (float)$_POST['base_price_hour'] : null,
            'base_price_day'     => !empty($_POST['base_price_day']) ? (float)$_POST['base_price_day'] : null,
            'base_price_month'   => !empty($_POST['base_price_month']) ? (float)$_POST['base_price_month'] : null,
            'included_equipment' => trim($_POST['included_equipment'] ?? ''),
            'restrictions'       => trim($_POST['restrictions'] ?? ''),
            'hotel_id'           => !empty($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : null,
            'calendar_color'     => $_POST['calendar_color'] ?? '#198754',
        ];
    }
    // Fin de la función collectFormData()
}
