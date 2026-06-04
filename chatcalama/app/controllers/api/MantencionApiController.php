<?php
/**
 * MantencionApiController — API REST para el módulo de mantención
 * PHP 7.4–8.2 compatible
 */
class MantencionApiController extends Controller
{
    /**
     * GET /api/mantencion
     * Lista todas las mantenciones con filtros opcionales (query string).
     */
    public function index(): void
    {
        $payload = AuthMiddleware::api();
        $rol     = $payload['rol']     ?? 'Operador';
        $areaId  = (int)($payload['area_id'] ?? 0);
        $userId  = (int)($payload['uid']     ?? 0);
        $model   = new MantencionModel();

        $filtros = [];
        foreach (['estado', 'tipo', 'prioridad'] as $key) {
            if (!empty($_GET[$key])) {
                $filtros[$key] = $_GET[$key];
            }
        }

        if ($rol === 'Administrador') {
            // Admin ve todo
            if (!empty($_GET['area_id'])) $filtros['area_id'] = $_GET['area_id'];
            $mantenciones = $model->getAll($filtros);
        } elseif ($areaId > 0 && $model->isMantencionArea($areaId)) {
            // Usuario del área Mantención ve todas las de su área
            $filtros['area_id'] = $areaId;
            $mantenciones = $model->getAll($filtros);
        } else {
            // Resto: solo las asignadas a él
            $mantenciones = $model->getMisMantencion($userId);
        }

        $this->json([
            'ok'    => true,
            'data'  => $mantenciones,
            'total' => count($mantenciones),
        ]);
    }

    /**
     * GET /api/mantencion/ver/ID
     * Detalle de una mantención con comentarios y archivos.
     */
    public function ver(string $id): void
    {
        $payload = AuthMiddleware::api();

        $model = new MantencionModel();
        $mant  = $model->getById((int)$id);

        if (!$mant) {
            $this->json(['ok' => false, 'msg' => 'Mantención no encontrada.'], 404);
        }

        $comentarios = $model->getComentarios((int)$id);
        $archivos    = $model->getArchivos((int)$id);

        $this->json([
            'ok'          => true,
            'data'        => $mant,
            'comentarios' => $comentarios,
            'archivos'    => $archivos,
        ]);
    }

    /**
     * POST /api/mantencion
     * Crea una nueva orden de mantención (JSON body).
     * Body: titulo, descripcion, ubicacion, tipo, area_id, asignado_a, prioridad, fecha_programada, costo_estimado
     */
    public function crear(): void
    {
        $payload = AuthMiddleware::api();

        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body) {
            $this->json(['ok' => false, 'msg' => 'Body JSON inválido.'], 400);
        }

        if (empty($body['titulo'])) {
            $this->json(['ok' => false, 'msg' => 'El campo título es obligatorio.'], 422);
        }

        $data = [
            'titulo'           => trim($body['titulo']           ?? ''),
            'descripcion'      => trim($body['descripcion']      ?? ''),
            'ubicacion'        => trim($body['ubicacion']        ?? ''),
            'tipo'             => $body['tipo']                  ?? 'correctiva',
            'area_id'          => !empty($body['area_id'])       ? (int)$body['area_id']    : null,
            'asignado_a'       => !empty($body['asignado_a'])    ? (int)$body['asignado_a'] : null,
            'prioridad'        => $body['prioridad']             ?? 'media',
            'fecha_programada' => $body['fecha_programada']      ?? null,
            'costo_estimado'   => !empty($body['costo_estimado']) ? (float)$body['costo_estimado'] : null,
            'creado_por'       => (int)($payload['uid']      ?? 0),
            'estado'           => 'pendiente',
        ];

        $id = (new MantencionModel())->create($data);

        if (!$id) {
            $this->json(['ok' => false, 'msg' => 'Error al crear la mantención.'], 500);
        }

        $mant = (new MantencionModel())->getById($id);

        $this->json(['ok' => true, 'msg' => 'Mantención creada.', 'data' => $mant], 201);
    }

    /**
     * POST /api/mantencion/completar  (multipart/form-data)
     * Completa una mantención. Requiere foto_cierre (file) y mantencion_id (field).
     * Campos opcionales: nota_cierre, costo_real
     */
    public function completar(): void
    {
        $payload = AuthMiddleware::api();

        $mantId = (int)($_POST['mantencion_id'] ?? 0);
        if (!$mantId) {
            $this->json(['ok' => false, 'msg' => 'Se requiere mantencion_id.'], 422);
        }

        $model = new MantencionModel();
        $mant  = $model->getById($mantId);

        if (!$mant) {
            $this->json(['ok' => false, 'msg' => 'Mantención no encontrada.'], 404);
        }

        if (
            empty($_FILES['foto_cierre']) ||
            ($_FILES['foto_cierre']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
        ) {
            $this->json(['ok' => false, 'msg' => 'La foto de cierre es obligatoria.'], 422);
        }

        $ruta = (new ImageService())->saveAsWebp(
            $_FILES['foto_cierre'],
            'mantencion/' . $mantId . '/cierre'
        );

        if ($ruta === false) {
            $this->json(['ok' => false, 'msg' => 'Error al procesar la imagen. Verifique el formato.'], 422);
        }

        $notaCierre = trim($_POST['nota_cierre'] ?? '');
        $costoReal  = (float)($_POST['costo_real'] ?? 0);
        $userId     = (int)($payload['uid'] ?? 0);

        $ok = $model->completar($mantId, $ruta, $notaCierre, $costoReal, $userId);

        if (!$ok) {
            $this->json(['ok' => false, 'msg' => 'No se pudo completar la mantención.'], 500);
        }

        $this->json([
            'ok'  => true,
            'msg' => 'Mantención completada correctamente.',
            'data'=> $model->getById($mantId),
        ]);
    }

    /**
     * POST /api/mantencion/comentar  (JSON body)
     * Añade un comentario a una mantención.
     * Body: mantencion_id, texto
     */
    public function comentar(): void
    {
        $payload = AuthMiddleware::api();

        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body) {
            $this->json(['ok' => false, 'msg' => 'Body JSON inválido.'], 400);
        }

        $mantId = (int)($body['mantencion_id'] ?? 0);
        $texto  = trim($body['texto'] ?? '');

        if (!$mantId) {
            $this->json(['ok' => false, 'msg' => 'Se requiere mantencion_id.'], 422);
        }
        if ($texto === '') {
            $this->json(['ok' => false, 'msg' => 'El comentario no puede estar vacío.'], 422);
        }

        $model = new MantencionModel();

        $mant = $model->getById($mantId);
        if (!$mant) {
            $this->json(['ok' => false, 'msg' => 'Mantención no encontrada.'], 404);
        }

        $userId      = (int)($payload['uid'] ?? 0);
        $comentId    = $model->addComentario($mantId, $userId, $texto);

        $this->json([
            'ok'          => true,
            'msg'         => 'Comentario añadido.',
            'comentario_id' => $comentId,
        ], 201);
    }
}
