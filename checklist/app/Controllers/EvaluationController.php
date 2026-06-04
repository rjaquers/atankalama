<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Evaluation;
use App\Models\Checklist;
use App\Middleware\AuthMiddleware;
use App\Core\Logger;
use App\Services\PhotoUploadService;

class EvaluationController extends Controller
{
    public function index()
    {
        $checklistModel = new Checklist();
        $checklists = $checklistModel->all();
        $this->render('evaluaciones/index', ['checklists' => $checklists]);
    }

    public function execute()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/evaluaciones');
        }

        $checklistModel = new Checklist();
        $checklist = $checklistModel->find($id);

        if (!$checklist) {
            $this->redirect('/evaluaciones');
        }

        // Mapeo de área (y nombre de checklist) → perfiles de chk_usuarios
        $perfilesPorArea = [
            'cocina'       => ['Cocina', 'Jefatura de Cocina', 'Garzón'],
            'recepcion'    => ['Recepcionista', 'Jefatura de Recepción'],
            'recepción'    => ['Recepcionista', 'Jefatura de Recepción'],
            'housekeeping' => ['Housekeeping', 'Jefatura de HouseKeeping'],
            'habitaciones' => ['Housekeeping', 'Jefatura de HouseKeeping'],
            'mantencion'   => ['Mantención'],
            'mantención'   => ['Mantención'],
        ];

        $areaKey   = strtolower(trim($checklist['area'] ?? ''));
        $nombreKey = strtolower($checklist['nombre'] ?? '');

        // Buscar perfiles por área; si no hay coincidencia exacta intentar con el nombre del checklist
        $perfiles = $perfilesPorArea[$areaKey] ?? null;
        if (!$perfiles) {
            foreach ($perfilesPorArea as $keyword => $pfs) {
                if (stripos($nombreKey, $keyword) !== false) {
                    $perfiles = $pfs;
                    break;
                }
            }
        }
        // Compatibilidad con checklist id=1 de recepcionistas
        if (!$perfiles && $checklist['id'] == 1) {
            $perfiles = ['Recepcionista', 'Jefatura de Recepción'];
        }

        $usuariosArea = [];
        if ($perfiles) {
            $userModel = new \App\Models\User();
            $usuariosArea = $userModel->byPerfiles($perfiles);
        }

        $this->render('evaluaciones/execute', [
            'checklist'    => $checklist,
            'usuariosArea' => $usuariosArea,
        ]);
    }

    public function store()
    {
        $checklistId = $_POST['checklist_id'] ?? null;
        $tipo = $_POST['tipo_evaluado'] ?? 'persona';

        if ($tipo === 'espacio') {
            $nombre = $_POST['evaluado_hotel'] ?? '';
            $apellido = $_POST['evaluado_habitacion'] ?? '';
            $emailEvaluado = '';
        } else {
            $nombre = $_POST['evaluado_nombre'] ?? '';
            $apellido = $_POST['evaluado_apellido'] ?? '';
            $emailEvaluado = $_POST['evaluado_email'] ?? '';
            // Compatibilidad: si apellido es un email (caso recepcionista antiguo), usarlo
            if (empty($emailEvaluado) && filter_var($apellido, FILTER_VALIDATE_EMAIL)) {
                $emailEvaluado = $apellido;
            }
        }

        $respuestas = $_POST['respuestas'] ?? [];
        $email = \AccesoBootstrap::email();
        $fechaInicio = $_POST['fecha_inicio'] ?? null;
        $fechaFin    = $_POST['fecha_fin'] ?? null;

        if (!$checklistId || empty($nombre)) {
            return $this->json(['error' => 'Datos de evaluación incompletos'], 400);
        }

        $evalModel = new Evaluation();
        $evalId = $evalModel->create($checklistId, $nombre, $apellido, $email, $fechaInicio, $fechaFin);

        if ($evalId) {
            $photoService = new \App\Services\PhotoUploadService();
            $fotoQuestions = $_POST['foto_questions'] ?? [];
            $allPIds = array_unique(array_merge(array_keys($respuestas), $fotoQuestions));

            foreach ($allPIds as $pId) {
                $val = $respuestas[$pId] ?? '';
                $fotoPath = null;
                $fileKey = 'fotos_' . $pId;

                // Si es una pregunta que espera fotos y se enviaron archivos
                if (in_array($pId, $fotoQuestions) && isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'][0] !== UPLOAD_ERR_NO_FILE) {
                    try {
                        $paths = $photoService->upload($_FILES[$fileKey], (int) $evalId, (int) $pId);
                        if (!empty($paths)) {
                            $fotoPath = json_encode($paths, JSON_UNESCAPED_SLASHES);
                        }
                    } catch (\Exception $e) {
                        Logger::warning('EVAL', 'Error al subir foto: ' . $e->getMessage(), ['pregunta_id' => $pId], $email);
                    }
                }

                $evalModel->saveResponse($evalId, $pId, $val, $fotoPath);
            }

            // Enviar email de resultados al colaborador evaluado
            if (!empty($emailEvaluado) && filter_var($emailEvaluado, FILTER_VALIDATE_EMAIL)) {
                $reportService = new \App\Services\ReportService();
                $evalDetail = $reportService->getEvaluationDetail($evalId);
                if ($evalDetail) {
                    \App\Services\EmailService::sendEvaluacionColaborador($emailEvaluado, $evalDetail);
                }
            }

            Logger::info('EVAL', 'Evaluación completada', ['eval_id' => $evalId, 'checklist_id' => $checklistId], $email);
            return $this->json(['message' => 'Evaluación guardada con éxito', 'redirect' => BASE_URL . '/evaluaciones']);
        }

        return $this->json(['error' => 'Error al guardar la evaluación'], 500);
    }
}
