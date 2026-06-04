<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Checklist;
use App\Models\Evaluation;
use App\Core\Logger;
use App\Services\PhotoUploadService;

class SurveyController extends Controller
{
    /**
     * Muestra la encuesta pública identificada por token.
     * No requiere autenticación.
     *
     * @param string $token Token único del checklist abierto.
     */
    public function show($token)
    {
        $model = new Checklist();
        $checklist = $model->findByToken($token);

        if (!$checklist) {
            $this->render('encuesta/error', [], 'public');
            return;
        }

        $this->render('encuesta/show', ['checklist' => $checklist], 'public');
    }

    /**
     * Guarda las respuestas de una encuesta pública.
     * No requiere autenticación. Al finalizar redirige al sitio web del hotel.
     *
     * @param string $token Token único del checklist abierto.
     */
    public function store($token)
    {
        try {
            $model = new Checklist();
            $checklist = $model->findByToken($token);

            if (!$checklist) {
                return $this->json(['error' => 'Encuesta no disponible'], 404);
            }

            $respuestas = $_POST['respuestas'] ?? [];
            $evalModel = new Evaluation();

            $ahora = date('Y-m-d H:i:s');
            $evalId = $evalModel->create($checklist['id'], 'Encuesta Publica', '', null, $ahora, $ahora);

            if ($evalId) {
                $photoService = new PhotoUploadService();
                $fotoQuestions = $_POST['foto_questions'] ?? [];
                if (!is_array($fotoQuestions)) {
                    $fotoQuestions = [$fotoQuestions];
                }
                $allPIds = array_unique(array_merge(array_keys($respuestas), $fotoQuestions));

                foreach ($allPIds as $pId) {
                    $val = $respuestas[$pId] ?? '';
                    $fotoPath = null;
                    $fileKey = 'fotos_' . $pId;

                    if (in_array($pId, $fotoQuestions) && isset($_FILES[$fileKey]) && !empty($_FILES[$fileKey]['name'][0])) {
                        try {
                            $paths = $photoService->upload($_FILES[$fileKey], (int) $evalId, (int) $pId);
                            if (!empty($paths)) {
                                $fotoPath = json_encode($paths, JSON_UNESCAPED_SLASHES);
                            }
                        } catch (\Exception $e) {
                            Logger::warning('ENCUESTA', 'Error al subir foto: ' . $e->getMessage(), ['pregunta_id' => $pId], 'publico');
                        }
                    }

                    $evalModel->saveResponse($evalId, $pId, $val, $fotoPath);
                }

                Logger::info('ENCUESTA', 'Encuesta publica completada', ['eval_id' => $evalId, 'checklist_id' => $checklist['id']], 'publico');
                return $this->json(['redirect' => 'https://www.atankalama.cl/hotel-boutique/']);
            }

            return $this->json(['error' => 'Error al guardar la encuesta'], 500);

        } catch (\Exception $e) {
            Logger::error('ENCUESTA', 'Excepcion en store: ' . $e->getMessage(), ['token' => substr($token, 0, 8) . '...'], 'publico');
            return $this->json(['error' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }
}
