<?php
/**
 * DashboardController - Atankalama Empresas
 */
class DashboardController extends Controller
{
    public function index()
    {
        $user = AuthService::user();
        $days = $_GET['days'] ?? 7;

        $alModel = new AlimentacionModel();
        $srvModel = new ServiciosModel();

        $totalAlimentacion = $alModel->countByCompany($user['company_id'], $days);
        $totalServicios = $srvModel->countByCompany($user['company_id'], $days);
        $latestRecords = $alModel->getLatestByCompany($user['company_id'], $days, 10);
        
        // Datos para gráficas
        $dailyData = $alModel->getDailyStats($user['company_id'], $days);
        $distData = $alModel->getDistributionStats($user['company_id'], $days);

        $data = [
            'title' => 'Panel de Control',
            'user' => $user,
            'days' => $days,
            'totalAlimentacion' => $totalAlimentacion,
            'totalServicios' => $totalServicios,
            'latestRecords' => $latestRecords,
            'chartDaily' => json_encode($dailyData),
            'chartDist' => json_encode($distData)
        ];

        $this->view('dashboard/index', $data);
    }
}
