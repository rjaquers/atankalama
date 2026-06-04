<?php
/**
 * MantencionController — módulo de mantención
 * PHP 7.4–8.2 compatible
 */
class MantencionController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::check();

        $rol    = $_SESSION['user_rol']     ?? 'Operador';
        $areaId = (int)($_SESSION['user_area_id'] ?? 0);
        $userId = (int)($_SESSION['user_id']      ?? 0);
        $model  = new MantencionModel();

        if ($rol === 'Administrador') {
            $mantenciones = $model->getAll($_GET);
        } elseif ($areaId > 0 && $model->isMantencionArea($areaId)) {
            $mantenciones = $model->getAll(['area_id' => $areaId]);
        } else {
            $mantenciones = $model->getMisMantencion($userId);
        }

        $areas = (new AreaModel())->getAll(true);
        $title = 'Mantención';

        $this->view('mantencion/index', compact('mantenciones', 'areas', 'title'));
    }

    public function ver(string $id): void
    {
        AuthMiddleware::check();

        $model = new MantencionModel();
        $mant  = $model->getById((int)$id);

        if (!$mant) {
            $this->redirect('/mantencion');
        }

        $comentarios = $model->getComentarios((int)$id);
        $archivos    = $model->getArchivos((int)$id);
        $title       = $mant['titulo'];

        $this->view('mantencion/ver', compact('mant', 'comentarios', 'archivos', 'title'));
    }

    public function crear(): void
    {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();

            $data = [
                'titulo'           => trim($_POST['titulo']           ?? ''),
                'descripcion'      => trim($_POST['descripcion']      ?? ''),
                'ubicacion'        => trim($_POST['ubicacion']        ?? ''),
                'tipo'             => $_POST['tipo']                  ?? 'correctiva',
                'area_id'          => !empty($_POST['area_id'])        ? $_POST['area_id'] : null,
                'asignado_a'       => !empty($_POST['asignado_a'])    ? $_POST['asignado_a'] : null,
                'prioridad'        => $_POST['prioridad']             ?? 'media',
                'fecha_programada' => !empty($_POST['fecha_programada']) ? $_POST['fecha_programada'] : null,
                'costo_estimado'   => $_POST['costo_estimado']        ?? null,
                'creado_por'       => $_SESSION['user_id'],
                'estado'           => 'pendiente',
            ];

            $id = (new MantencionModel())->create($data);

            // Notificar al asignado por chat si es distinto al creador
            if ($data['asignado_a'] && (int)$data['asignado_a'] !== (int)$_SESSION['user_id']) {
                $chatModel = new ChatModel();
                $convId    = $chatModel->getOrCreateConversacionIndividual(
                    (int)$_SESSION['user_id'],
                    (int)$data['asignado_a']
                );
                $link = BASE_URL . '/mantencion/ver/' . $id;
                $msg  = "Se te ha asignado la mantención: \"{$data['titulo']}\"\nRevísala aquí: {$link}";
                $chatModel->enviarMensaje($convId, (int)$_SESSION['user_id'], 'texto', $msg);
            }

            // Subir fotos iniciales si existen
            if (!empty($_FILES['fotos']['name'][0])) {
                $momento      = $_POST['momento_fotos'] ?? 'antes';
                $imageService = new ImageService();
                $model        = new MantencionModel();

                $count = count($_FILES['fotos']['name']);
                for ($i = 0; $i < $count; $i++) {
                    $file = [
                        'name'     => $_FILES['fotos']['name'][$i],
                        'tmp_name' => $_FILES['fotos']['tmp_name'][$i],
                        'error'    => $_FILES['fotos']['error'][$i],
                        'size'     => $_FILES['fotos']['size'][$i],
                    ];
                    $ruta = $imageService->saveAsWebp($file, 'mantencion/' . $id . '/' . $momento);
                    if ($ruta !== false) {
                        $model->addArchivo($id, $_SESSION['user_id'], $ruta, $momento);
                    }
                }
            }

            $this->redirect('/mantencion/ver/' . $id);
        }

        $areas    = (new AreaModel())->getAll(true);
        $usuarios = (new ChatUserModel())->getAll(true);
        $title    = 'Nueva Mantención';
        $mant     = null;

        $this->view('mantencion/form', compact('areas', 'usuarios', 'title', 'mant'));
    }

    public function editar(string $id): void
    {
        AuthMiddleware::check();

        $model = new MantencionModel();
        $mant  = $model->getById((int)$id);

        if (!$mant) {
            $this->redirect('/mantencion');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();

            $data = [
                'titulo'           => trim($_POST['titulo']           ?? ''),
                'descripcion'      => trim($_POST['descripcion']      ?? ''),
                'ubicacion'        => trim($_POST['ubicacion']        ?? ''),
                'tipo'             => $_POST['tipo']                  ?? 'correctiva',
                'area_id'          => !empty($_POST['area_id'])        ? $_POST['area_id'] : null,
                'asignado_a'       => !empty($_POST['asignado_a'])    ? $_POST['asignado_a'] : null,
                'prioridad'        => $_POST['prioridad']             ?? 'media',
                'fecha_programada' => !empty($_POST['fecha_programada']) ? $_POST['fecha_programada'] : null,
                'costo_estimado'   => $_POST['costo_estimado']        ?? null,
            ];

            $nuevoAsignado = $data['asignado_a'] ? (int)$data['asignado_a'] : null;
            $viejoAsignado = !empty($mant['asignado_a']) ? (int)$mant['asignado_a'] : null;

            $model->update((int)$id, $data);

            // Notificar si cambió el asignado y es distinto al editor
            if ($nuevoAsignado && $nuevoAsignado !== $viejoAsignado
                && $nuevoAsignado !== (int)$_SESSION['user_id']
            ) {
                $chatModel = new ChatModel();
                $convId    = $chatModel->getOrCreateConversacionIndividual(
                    (int)$_SESSION['user_id'],
                    $nuevoAsignado
                );
                $link = BASE_URL . '/mantencion/ver/' . $id;
                $msg  = "Se te ha asignado la mantención: \"{$data['titulo']}\"\nRevísala aquí: {$link}";
                $chatModel->enviarMensaje($convId, (int)$_SESSION['user_id'], 'texto', $msg);
            }

            $this->redirect('/mantencion/ver/' . $id);
        }

        $areas    = (new AreaModel())->getAll(true);
        $usuarios = (new ChatUserModel())->getAll(true);
        $title    = 'Editar: ' . $mant['titulo'];

        $this->view('mantencion/form', compact('mant', 'areas', 'usuarios', 'title'));
    }

    public function completar(string $id): void
    {
        AuthMiddleware::check();
        csrf_verify();

        $mantId = (int)$id;

        if (
            empty($_FILES['foto_cierre']) ||
            ($_FILES['foto_cierre']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
        ) {
            $_SESSION['flash_error'] = 'La foto de cierre es obligatoria para completar la mantención.';
            $this->redirect('/mantencion/ver/' . $id);
        }

        $ruta = (new ImageService())->saveAsWebp($_FILES['foto_cierre'], 'mantencion/' . $mantId . '/cierre');

        if ($ruta === false) {
            $_SESSION['flash_error'] = 'Error al subir la foto de cierre. Verifique el formato del archivo.';
            $this->redirect('/mantencion/ver/' . $id);
        }

        $costoReal  = (float)($_POST['costo_real']  ?? 0);
        $notaCierre = trim($_POST['nota_cierre'] ?? '');

        (new MantencionModel())->completar($mantId, $ruta, $notaCierre, $costoReal, $_SESSION['user_id']);

        $this->redirect('/mantencion/ver/' . $id);
    }

    public function cancelar(string $id): void
    {
        AuthMiddleware::check();
        csrf_verify();
        AuthMiddleware::jefe();

        (new MantencionModel())->cancelar((int)$id, $_SESSION['user_id']);

        $this->redirect('/mantencion/ver/' . $id);
    }

    public function comentar(string $id): void
    {
        AuthMiddleware::check();
        csrf_verify();

        $texto = trim($_POST['comentario'] ?? '');

        if ($texto !== '') {
            (new MantencionModel())->addComentario((int)$id, $_SESSION['user_id'], $texto);
        }

        $this->redirect('/mantencion/ver/' . $id);
    }

    public function subirFoto(string $id): void
    {
        AuthMiddleware::check();
        csrf_verify();

        $momento = $_POST['momento'] ?? 'durante';

        if (!empty($_FILES['foto']) && ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $ruta = (new ImageService())->saveAsWebp($_FILES['foto'], 'mantencion/' . $id . '/' . $momento);
            if ($ruta !== false) {
                (new MantencionModel())->addArchivo((int)$id, $_SESSION['user_id'], $ruta, $momento);
            }
        }

        $this->redirect('/mantencion/ver/' . $id);
    }
}
