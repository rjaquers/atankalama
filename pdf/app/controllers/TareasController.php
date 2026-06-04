<?php
/**
 * TareasController — Módulo de Tareas (web)
 * PHP 7.4–8.2 compatible
 */
class TareasController extends Controller
{
    // -------------------------------------------------------
    // index — listado con filtros
    // -------------------------------------------------------
    public function index(): void
    {
        AuthMiddleware::check();

        $rol   = $_SESSION['user_rol'] ?? 'Operador';
        $model = new TareaModel();

        if ($rol === 'Administrador') {
            $filtros = [];
            if (!empty($_GET['area_id']))   $filtros['area_id']   = $_GET['area_id'];
            if (!empty($_GET['estado']))    $filtros['estado']    = $_GET['estado'];
            if (!empty($_GET['prioridad'])) $filtros['prioridad'] = $_GET['prioridad'];
            $tareas = $model->getAll($filtros);
        } else {
            $tareas = $model->getMisTagreas((int)$_SESSION['user_id']);
        }

        $areas = (new AreaModel())->getAll(true);
        $title = 'Tareas';

        $this->view('tareas/index', compact('tareas', 'areas', 'title'));
    }

    // -------------------------------------------------------
    // ver — detalle de una tarea
    // -------------------------------------------------------
    public function ver(string $id): void
    {
        AuthMiddleware::check();

        $model = new TareaModel();
        $tarea = $model->getById((int)$id);

        if (!$tarea) {
            $this->redirect('/tareas');
        }

        $comentarios = $model->getComentarios((int)$id);
        $archivos    = $model->getArchivos((int)$id);
        $title       = $tarea['titulo'];

        $this->view('tareas/ver', compact('tarea', 'comentarios', 'archivos', 'title'));
    }

    // -------------------------------------------------------
    // crear
    // -------------------------------------------------------
    public function crear(): void
    {
        AuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();

            $tipo      = in_array($_POST['tipo'] ?? '', ['abierta','dirigida'], true) ? $_POST['tipo'] : 'abierta';
            $area_id   = !empty($_POST['area_id'])    ? $_POST['area_id']    : null;
            $asignado_a = !empty($_POST['asignado_a']) ? $_POST['asignado_a'] : null;

            if ($tipo === 'abierta' && !$area_id) {
                $_SESSION['flash_error'] = 'Las tareas abiertas deben tener un área asignada.';
                $this->redirect('/tareas/crear');
            }
            if ($tipo === 'dirigida' && !$asignado_a) {
                $_SESSION['flash_error'] = 'Las tareas dirigidas deben tener una persona asignada.';
                $this->redirect('/tareas/crear');
            }

            $data = [
                'titulo'      => trim($_POST['titulo'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'tipo'        => $tipo,
                'area_id'     => $area_id,
                'asignado_a'  => $asignado_a,
                'creado_por'  => $_SESSION['user_id'],
                'prioridad'   => $_POST['prioridad'] ?? 'media',
                'estado'      => 'pendiente',
                'fecha_limite'=> !empty($_POST['fecha_limite']) ? $_POST['fecha_limite'] : null,
            ];

            $id = (new TareaModel())->create($data);

            // Notificar al asignado por chat si es distinto al creador
            if ($asignado_a && (int)$asignado_a !== (int)$_SESSION['user_id']) {
                $chatModel = new ChatModel();
                $convId    = $chatModel->getOrCreateConversacionIndividual(
                    (int)$_SESSION['user_id'],
                    (int)$asignado_a
                );
                $link = BASE_URL . '/tareas/ver/' . $id;
                $msg  = "Se te ha asignado la tarea: \"{$data['titulo']}\"\nRevísala aquí: {$link}";
                $chatModel->enviarMensaje($convId, (int)$_SESSION['user_id'], 'texto', $msg);
            }

            $this->redirect('/tareas/ver/' . $id);
        }

        $esJefeAdmin = in_array($_SESSION['user_rol'] ?? '', ['Administrador', 'Jefe de Área'], true);
        $areas    = (new AreaModel())->getAll(true);
        $usuarios = (new ChatUserModel())->getAll(true);
        $title    = 'Nueva Tarea';

        $this->view('tareas/form', compact('areas', 'usuarios', 'title', 'esJefeAdmin'));
    }

    // -------------------------------------------------------
    // editar
    // -------------------------------------------------------
    public function editar(string $id): void
    {
        AuthMiddleware::check();

        $model       = new TareaModel();
        $tarea       = $model->getById((int)$id);
        $esJefeAdmin = in_array($_SESSION['user_rol'] ?? '', ['Administrador', 'Jefe de Área'], true);

        if (!$tarea) {
            $this->redirect('/tareas');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $postData = $_POST;
            if (!$esJefeAdmin) {
                // Usuarios sin rol de jefe no pueden reasignar
                unset($postData['area_id'], $postData['asignado_a'], $postData['tipo']);
            }

            $nuevoAsignado = !empty($postData['asignado_a']) ? (int)$postData['asignado_a'] : null;
            $viejoAsignado = !empty($tarea['asignado_a'])    ? (int)$tarea['asignado_a']    : null;

            $model->update((int)$id, $postData);

            // Notificar si cambió el asignado y es distinto al editor
            if ($nuevoAsignado && $nuevoAsignado !== $viejoAsignado
                && $nuevoAsignado !== (int)$_SESSION['user_id']
            ) {
                $chatModel = new ChatModel();
                $convId    = $chatModel->getOrCreateConversacionIndividual(
                    (int)$_SESSION['user_id'],
                    $nuevoAsignado
                );
                $link  = BASE_URL . '/tareas/ver/' . $id;
                $titulo = $postData['titulo'] ?? $tarea['titulo'];
                $msg   = "Se te ha asignado la tarea: \"{$titulo}\"\nRevísala aquí: {$link}";
                $chatModel->enviarMensaje($convId, (int)$_SESSION['user_id'], 'texto', $msg);
            }

            $this->redirect('/tareas/ver/' . $id);
        }

        $areas    = (new AreaModel())->getAll(true);
        $usuarios = (new ChatUserModel())->getAll(true);
        $title    = 'Editar Tarea';

        $this->view('tareas/form', compact('tarea', 'areas', 'usuarios', 'title', 'esJefeAdmin'));
    }

    // -------------------------------------------------------
    // completar — foto de cierre obligatoria
    // -------------------------------------------------------
    public function completar(string $id): void
    {
        AuthMiddleware::check();
        csrf_verify();

        $tareaId = (int)$id;

        if (
            empty($_FILES['foto_cierre'])
            || !isset($_FILES['foto_cierre']['error'])
            || $_FILES['foto_cierre']['error'] !== UPLOAD_ERR_OK
        ) {
            $_SESSION['flash_error'] = 'La foto de cierre es obligatoria.';
            $this->redirect('/tareas/ver/' . $tareaId);
        }

        $img  = new ImageService();
        $ruta = $img->saveAsWebp($_FILES['foto_cierre'], 'tareas/' . $tareaId);

        if (!$ruta) {
            $_SESSION['flash_error'] = 'No se pudo guardar la foto de cierre. Verifica el formato e inténtalo de nuevo.';
            $this->redirect('/tareas/ver/' . $tareaId);
        }

        (new TareaModel())->completar(
            $tareaId,
            $ruta,
            trim($_POST['nota_cierre'] ?? ''),
            (int)$_SESSION['user_id']
        );

        $this->redirect('/tareas/ver/' . $tareaId);
    }

    // -------------------------------------------------------
    // cancelar — solo admin/jefe
    // -------------------------------------------------------
    public function cancelar(string $id): void
    {
        AuthMiddleware::check();
        csrf_verify();
        AuthMiddleware::jefe();

        (new TareaModel())->cancelar((int)$id, (int)$_SESSION['user_id']);
        $this->redirect('/tareas/ver/' . $id);
    }

    // -------------------------------------------------------
    // comentar
    // -------------------------------------------------------
    public function comentar(string $id): void
    {
        AuthMiddleware::check();
        csrf_verify();

        $texto = trim($_POST['comentario'] ?? '');
        if ($texto === '') {
            $this->redirect('/tareas/ver/' . $id);
        }

        (new TareaModel())->addComentario((int)$id, (int)$_SESSION['user_id'], $texto);
        $this->redirect('/tareas/ver/' . $id);
    }

    // -------------------------------------------------------
    // subirFoto — adjuntar foto adicional
    // -------------------------------------------------------
    public function subirFoto(string $id): void
    {
        AuthMiddleware::check();
        csrf_verify();

        $error = $_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE) {
            $_SESSION['flash_error'] = 'No se seleccionó ninguna foto.';
            $this->redirect('/tareas/ver/' . $id);
        }

        $errMsg = [
            UPLOAD_ERR_INI_SIZE  => 'La foto supera el límite permitido por el servidor (máx. ' . ini_get('upload_max_filesize') . ').',
            UPLOAD_ERR_FORM_SIZE => 'La foto supera el tamaño máximo del formulario.',
            UPLOAD_ERR_PARTIAL   => 'La foto se subió de forma incompleta. Intenta de nuevo.',
            UPLOAD_ERR_CANT_WRITE => 'Error interno: no se pudo escribir en disco.',
        ];
        if ($error !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = $errMsg[$error] ?? "Error al subir la foto (código $error).";
            $this->redirect('/tareas/ver/' . $id);
        }

        $ruta = (new ImageService())->saveAsWebp($_FILES['foto'], 'tareas/' . $id);

        if (!$ruta) {
            $_SESSION['flash_error'] = 'No se pudo procesar la imagen. Verifica el formato (JPG, PNG, WebP) e inténtalo de nuevo.';
            $this->redirect('/tareas/ver/' . $id);
        }

        (new TareaModel())->addArchivo((int)$id, (int)$_SESSION['user_id'], $ruta);
        $_SESSION['flash_success'] = 'Foto subida correctamente.';
        $this->redirect('/tareas/ver/' . $id);
    }

    // -------------------------------------------------------
    // misTareas
    // -------------------------------------------------------
    public function misTareas(): void
    {
        AuthMiddleware::check();

        $tareas = (new TareaModel())->getMisTagreas((int)$_SESSION['user_id']);
        $title  = 'Mis Tareas';

        $this->view('tareas/index', compact('tareas', 'title'));
    }
}
