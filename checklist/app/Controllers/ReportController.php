<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\ReportService;
use App\Middleware\AuthMiddleware;

class ReportController extends Controller
{
    public function index()
    {
        $service = new ReportService();
        $evaluaciones = $service->getAllEvaluations();
        $this->render('reportes/index', ['evaluaciones' => $evaluaciones]);
    }

    public function view()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/reportes');
        }

        $service = new ReportService();
        $report = $service->getEvaluationDetail($id);

        if (!$report) {
            $this->redirect('/reportes');
        }

        $this->render('reportes/view', ['report' => $report]);
    }

    public function logs()
    {
        $filters = [
            'startDate' => $_GET['startDate'] ?? '',
            'endDate' => $_GET['endDate'] ?? '',
            'user' => $_GET['user'] ?? '',
            'modulo' => $_GET['modulo'] ?? '',
            'nivel' => $_GET['nivel'] ?? ''
        ];

        $service = new ReportService();
        $logs = $service->getSystemActivity($filters);
        $modules = $service->getLogModules();

        $this->render('reportes/logs', [
            'logs' => $logs,
            'filters' => $filters,
            'modules' => $modules
        ]);
    }

    public function stats()
    {
        $filters = [
            'startDate' => $_GET['startDate'] ?? '',
            'endDate'   => $_GET['endDate'] ?? '',
            'area'      => $_GET['area'] ?? '',
            'persona'   => $_GET['persona'] ?? '',
            'hotel'     => $_GET['hotel'] ?? '',
            'tipo'      => $_GET['tipo'] ?? '',
        ];

        $service = new ReportService();

        if (isset($_GET['export']) && $_GET['export'] == '1') {
            $stats = $service->getGlobalStats($filters, 9999); // Todo el periodo sin límite de Top 10
            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=reporte_stats_" . date('Ymd_His') . ".xls");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo "<html><head><meta charset='utf-8'></head><body>";
            echo "<h2>Dashboard de Cumplimiento</h2>";
            
            // 1. Cumplimiento por Área
            echo "<h3>Cumplimiento por Área</h3>";
            echo "<table border='1'><tr><th>Área</th><th>Sí</th><th>No</th><th>Total Respuestas</th><th>% Cumplimiento</th></tr>";
            foreach ($stats['por_area'] as $area) {
                $pct = ($area['total_si'] + $area['total_no']) > 0 ? round(($area['total_si'] / ($area['total_si'] + $area['total_no'])) * 100, 1) : 0;
                echo "<tr><td>{$area['area']}</td><td>{$area['total_si']}</td><td>{$area['total_no']}</td><td>{$area['total_respuestas']}</td><td>{$pct}%</td></tr>";
            }
            echo "</table><br>";

            // 2. Tendencia Mensual
            echo "<h3>Tendencia Mensual</h3>";
            echo "<table border='1'><tr><th>Mes</th><th>Reportes (Aprox)</th><th>% Cumplimiento</th></tr>";
            foreach ($stats['por_mes'] as $mes) {
                $pct = ($mes['total_si'] + $mes['total_no']) > 0 ? round(($mes['total_si'] / ($mes['total_si'] + $mes['total_no'])) * 100, 1) : 0;
                $mesNombre = date('F Y', strtotime($mes['mes'] . '-01'));
                echo "<tr><td>{$mesNombre}</td><td>{$mes['total_evaluaciones']}</td><td>{$pct}%</td></tr>";
            }
            echo "</table><br>";
            
            // 3. Detalle de Evaluaciones (Agrupado por ID)
            echo "<h3>Detalle de Evaluaciones (Agrupado por ID)</h3>";
            echo "<table border='1'><tr><th>ID Evaluación</th><th>Fecha</th><th>Persona Evaluada</th><th>Checklist</th><th>SÍ</th><th>NO</th><th>Total Respuestas</th><th>% Cumplimiento</th></tr>";
            foreach ($stats['por_evaluacion'] as $eva) {
                $pct = ($eva['total_si'] + $eva['total_no']) > 0 ? round(($eva['total_si'] / ($eva['total_si'] + $eva['total_no'])) * 100, 1) : 0;
                $fecha = date('d/m/Y H:i', strtotime($eva['fecha_evaluacion']));
                echo "<tr>
                        <td>#{$eva['evaluacion_id']}</td>
                        <td>{$fecha}</td>
                        <td>" . htmlspecialchars($eva['evaluado_nombre'] . ' ' . $eva['evaluado_apellido']) . "</td>
                        <td>" . htmlspecialchars($eva['checklist_nombre']) . "</td>
                        <td>{$eva['total_si']}</td>
                        <td>{$eva['total_no']}</td>
                        <td>{$eva['total_respuestas']}</td>
                        <td>{$pct}%</td>
                      </tr>";
            }
            echo "</table><br>";

            // 4. Checklists Creados por Tipo
            echo "<h3>Checklists Creados por Tipo</h3>";
            echo "<table border='1'><tr><th>Tipo de Checklist</th><th>Total Creados</th></tr>";
            foreach ($stats['por_tipo'] as $tipo) {
                echo "<tr>
                        <td>" . htmlspecialchars($tipo['checklist_nombre']) . "</td>
                        <td>{$tipo['total_creados']}</td>
                      </tr>";
            }
            echo "</table>";

            echo "</body></html>";
            exit;
        }

        // Resumen mensual
        $summaryYear  = (int)($_GET['summary_year']  ?? date('Y'));
        $summaryMonth = (int)($_GET['summary_month'] ?? date('m'));
        $summaryMonth = max(1, min(12, $summaryMonth));
        $monthlySummary = $service->getMonthlySummary($summaryYear, $summaryMonth);

        // Si no es export, obtener datos normales con paginación
        $perPage = 20;
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $stats   = $service->getGlobalStats($filters, $perPage, $page);
        $totalPages = (int)ceil($stats['total_evaluaciones'] / $perPage);

        $areaModel = new \App\Models\Area();
        $areas = $areaModel->all(true);

        $this->render('reportes/stats', [
            'stats'          => $stats,
            'filters'        => $filters,
            'areas'          => $areas,
            'page'           => $page,
            'perPage'        => $perPage,
            'totalPages'     => $totalPages,
            'monthlySummary' => $monthlySummary,
            'summaryYear'    => $summaryYear,
            'summaryMonth'   => $summaryMonth,
        ]);
    }

    public function encuestas()
    {
        $filters = [
            'startDate'    => $_GET['startDate'] ?? '',
            'endDate'      => $_GET['endDate'] ?? '',
            'checklist_id' => $_GET['checklist_id'] ?? '',
        ];

        $service = new ReportService();
        $stats = $service->getSurveyStats($filters);
        $checklists = $service->getOpenChecklists();

        $this->render('reportes/encuestas', [
            'stats'      => $stats,
            'filters'    => $filters,
            'checklists' => $checklists,
            'active'     => 'encuestas',
        ]);
    }

    public function exportEncuestas()
    {
        $filters = [
            'startDate'    => $_GET['startDate'] ?? '',
            'endDate'      => $_GET['endDate'] ?? '',
            'checklist_id' => $_GET['checklist_id'] ?? '',
        ];

        $service = new ReportService();
        $data = $service->getSurveyExportData($filters);
        $preguntas = $data['preguntas'];
        $evaluaciones = $data['evaluaciones'];

        // Nombre del archivo
        $nombreChecklist = '';
        if (!empty($filters['checklist_id'])) {
            $checklists = $service->getOpenChecklists();
            foreach ($checklists as $cl) {
                if ($cl['id'] == $filters['checklist_id']) {
                    $nombreChecklist = '_' . preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $cl['nombre']));
                    break;
                }
            }
        }
        $filename = 'encuestas' . $nombreChecklist . '_' . date('Ymd_His') . '.xls';

        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo "<html><head><meta charset='utf-8'></head><body>";
        echo "<h2>Detalle de Respuestas — Encuestas Abiertas</h2>";
        if (!empty($filters['startDate']) || !empty($filters['endDate'])) {
            echo "<p>Período: " . ($filters['startDate'] ?: '—') . " al " . ($filters['endDate'] ?: '—') . "</p>";
        }
        echo "<br>";

        echo "<table border='1'>";

        // Cabecera
        echo "<tr style='background:#4361ee;color:#fff;font-weight:bold;'>";
        echo "<th>#</th>";
        echo "<th>Fecha</th>";
        if (empty($filters['checklist_id'])) {
            echo "<th>Encuesta</th>";
            echo "<th>Área</th>";
        }
        foreach ($preguntas as $p) {
            $col = htmlspecialchars($p['pregunta']);
            if ($p['grupo']) {
                $col = htmlspecialchars($p['grupo']) . " — " . $col;
            }
            echo "<th>$col</th>";
        }
        echo "</tr>";

        // Filas
        $num = 1;
        foreach ($evaluaciones as $ev) {
            echo "<tr>";
            echo "<td>" . $num++ . "</td>";
            echo "<td>" . date('d/m/Y H:i', strtotime($ev['fecha_evaluacion'])) . "</td>";
            if (empty($filters['checklist_id'])) {
                echo "<td>" . htmlspecialchars($ev['checklist_nombre']) . "</td>";
                echo "<td>" . htmlspecialchars($ev['area']) . "</td>";
            }
            foreach ($preguntas as $p) {
                $r = $ev['respuestas'][$p['id']] ?? null;
                if ($r === null) {
                    echo "<td>—</td>";
                    continue;
                }
                switch ($r['tipo']) {
                    case 'boolean':
                        $valor = ($r['boolean'] === null || $r['boolean'] === '') ? '—' : ($r['boolean'] ? 'Sí' : 'No');
                        break;
                    case 'numeric_scale':
                        $valor = ($r['numerica'] !== null) ? $r['numerica'] : '—';
                        break;
                    case 'text':
                        $valor = htmlspecialchars($r['texto'] ?? '—');
                        break;
                    case 'photo':
                        $valor = $r['texto'] ? 'Foto enviada' : '—';
                        break;
                    default:
                        $valor = htmlspecialchars($r['texto'] ?? $r['numerica'] ?? '—');
                }
                echo "<td>$valor</td>";
            }
            echo "</tr>";
        }

        if (empty($evaluaciones)) {
            $cols = 2 + count($preguntas) + (empty($filters['checklist_id']) ? 2 : 0);
            echo "<tr><td colspan='$cols' style='text-align:center;color:#888;'>Sin respuestas para los filtros seleccionados.</td></tr>";
        }

        echo "</table>";
        echo "</body></html>";
        exit;
    }

    public function delete($id)
    {
        $evaluationModel = new \App\Models\Evaluation();
        $reportService = new ReportService();
        $report = $reportService->getEvaluationDetail($id);

        if (!$report) {
            echo json_encode(['error' => 'Evaluación no encontrada.']);
            return;
        }

        if ($evaluationModel->delete($id)) {
            // Log de actividad
            $db = \App\Core\Database::getInstance();
            $db->prepare("INSERT INTO " . DB_PREFIX . "system_logs (user_email, modulo, accion, detalle) VALUES (?, ?, ?, ?)")
                ->execute([
                    $_SESSION['user']['email'] ?? 'Sistema',
                    'Reportes',
                    'Eliminar',
                    "Se ha eliminado lógicamente el reporte ID: " . $id
                ]);

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Error al eliminar la evaluación.']);
        }
    }
}
