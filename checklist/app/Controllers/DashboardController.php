<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Checklist;

class DashboardController extends Controller
{
    public function index()
    {
        $db = \App\Core\Database::getInstance();

        // KPIs Básicos
        $totalChecklists = $db->query("SELECT COUNT(*) FROM " . DB_PREFIX . "checklists WHERE estado = 'activo'")->fetchColumn();
        $totalEvaluaciones = $db->query("SELECT COUNT(*) FROM " . DB_PREFIX . "evaluaciones WHERE activo = 1")->fetchColumn();

        // Actividad Reciente
        $recientes = $db->query("
            SELECT e.*, c.nombre as checklist_nombre 
            FROM " . DB_PREFIX . "evaluaciones e
            JOIN " . DB_PREFIX . "checklists c ON e.checklist_id = c.id
            WHERE e.activo = 1
            ORDER BY e.fecha_evaluacion DESC
            LIMIT 5
        ")->fetchAll();

        $this->render('dashboard/index', [
            'stats' => [
                'total_checklists' => $totalChecklists,
                'total_evaluaciones' => $totalEvaluaciones,
                'recientes' => $recientes
            ]
        ]);
    }
}
