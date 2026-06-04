<?php
/**
 * TareasApiController — API REST para módulo de tareas
 * PHP 7.4–8.2 compatible
 */
class TareasApiController extends Controller
{
    // -------------------------------------------------------
    // GET /api/tareas — tareas según rol
    // -------------------------------------------------------
    public function index(): void
    {
        $payload = AuthMiddleware::api();
        $model   = new TareaModel();

        if (($payload['rol'] ?? '') === 'Administrador') {
            $tareas = $model->getAll([]);
        } else {
            $tareas = $model->getMisTagreas((int)$payload['uid']);
        }

        $this->json(['ok' => true, 'tareas' => $tareas]);
    }

    // -------------------------------------------------------
    // GET /api/tareas/ver/{id}
    // -------------------------------------------------------
    public function ver(string $id): void
    {
        $payload = AuthMiddleware::api();
        $tarea   = (new TareaModel())->getById((int)$id);

        if (!$tarea) {
            $this->json(['ok' => false, 'msg' => 'Tarea no encontrada.'], 404);
        }

        $this->json(['ok' => true, 'tarea' => $tarea]);
    }

    // -------------------------------------------------------
    // POST /api/tareas/crear
    // -------------------------------------------------------
    public function crear(): void
    {
        $payload = AuthMiddleware::api();
        $data    = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            $data = [];
        }
        $data['creado_por'] = (int)$payload['uid'];

        if (empty($data['titulo'])) {
            $this->json(['ok' => false, 'msg' => 'El campo título es obligatorio.'], 422);
        }

        $id = (new TareaModel())->create($data);
        $this->json(['ok' => true, 'id' => $id]);
    }

    // -------------------------------------------------------
    // POST /api/tareas/completar  (multipart/form-data)
    // tarea_id, foto_cierre (file), nota (text)
    // -------------------------------------------------------
    public function completar(): void
    {
        $payload = AuthMiddleware::api();
        $tareaId = (int)($_POST['tarea_id'] ?? 0);

        if ($tareaId === 0) {
            $this->json(['ok' => false, 'msg' => 'tarea_id es obligatorio.'], 422);
        }

        if (
            empty($_FILES['foto_cierre'])
            || !isset($_FILES['foto_cierre']['error'])
            || $_FILES['foto_cierre']['error'] !== UPLOAD_ERR_OK
        ) {
            $this->json(['ok' => false, 'msg' => 'La foto de cierre es obligatoria.'], 422);
        }

        $img  = new ImageService();
        $ruta = $img->saveAsWebp($_FILES['foto_cierre'], 'tareas/' . $tareaId);

        if (!$ruta) {
            $this->json(['ok' => false, 'msg' => 'No se pudo procesar la imagen.'], 500);
        }

        $ok = (new TareaModel())->completar(
            $tareaId,
            $ruta,
            trim($_POST['nota'] ?? ''),
            (int)$payload['uid']
        );

        $this->json(['ok' => $ok]);
    }

    // -------------------------------------------------------
    // POST /api/tareas/comentar  (application/json)
    // { tarea_id, comentario }
    // -------------------------------------------------------
    public function comentar(): void
    {
        $payload = AuthMiddleware::api();
        $data    = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            $data = [];
        }

        $tareaId   = (int)($data['tarea_id'] ?? 0);
        $comentario = trim($data['comentario'] ?? '');

        if ($tareaId === 0 || $comentario === '') {
            $this->json(['ok' => false, 'msg' => 'tarea_id y comentario son requeridos.'], 422);
        }

        (new TareaModel())->addComentario($tareaId, (int)$payload['uid'], $comentario);
        $this->json(['ok' => true]);
    }
}
