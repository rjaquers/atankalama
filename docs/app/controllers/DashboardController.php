<?php
/**
 * Controller del Dashboard.
 *
 * Muestra el panel principal con KPIs del sistema de contratos:
 * contratos vigentes, por vencer, pagos pendientes, huéspedes, etc.
 *
 * @package App\Controllers
 */
class DashboardController extends Controller
{
    /**
     * Muestra el dashboard principal.
     * Requiere autenticación.
     */
    public function index()
    {
        AuthMiddleware::handle();

        // ===============================
        // KPIs DE CONTRATOS
        // ===============================
        $contractModel = new ContractModel();
        $totalVigentes   = $contractModel->countByStatus('vigente');
        $totalBorradores = $contractModel->countByStatus('borrador');
        $totalVencidos   = $contractModel->countByStatus('vencido');
        $totalPorRenovar = $contractModel->countByStatus('por_renovar');

        // Contratos próximos a vencer (30 días)
        $expiring = $contractModel->getExpiringContracts(30);
        $totalExpiring = count($expiring);

        // ===============================
        // KPIs DE PAGOS
        // ===============================
        $paymentModel = new PaymentModel();
        $totalPagosPendientes = $paymentModel->countPending();
        $montoPendiente = $paymentModel->getTotalPendingAmount();

        // ===============================
        // KPIs DE EMPRESAS
        // ===============================
        $companyModel = new CompanyModel();
        $totalEmpresas = $companyModel->count();

        // ===============================
        // VENTAS BRUTAS Y RECAUDACIÓN
        // ===============================
        $totalVentaAnual = $paymentModel->getTotalChargedGlobal(date('Y'));
        $totalRecaudado  = $paymentModel->getTotalPaidGlobal(date('Y'));

        // ===============================
        // ACTIVIDAD RECIENTE
        // ===============================
        $historyModel = new ContractHistoryModel();
        $recentActivity = $historyModel->getRecent(10);

        // ===============================
        // DATOS PARA GRÁFICOS
        // ===============================
        $chartData = [
            'vigentes'    => $totalVigentes,
            'borradores'  => $totalBorradores,
            'por_renovar' => $totalPorRenovar,
            'vencidos'    => $totalVencidos,
        ];

        // Usuario actual
        $userName = AuthService::userName();
        $userRole = $_SESSION['role'] ?? '';

        $this->view('dashboard/index', compact(
            'totalVigentes', 'totalBorradores', 'totalVencidos',
            'totalPorRenovar', 'totalExpiring', 'expiring',
            'totalPagosPendientes', 'montoPendiente',
            'totalEmpresas', 'totalVentaAnual', 'totalRecaudado',
            'recentActivity', 'chartData',
            'userName', 'userRole'
        ));
    }
    // Fin de la función index()
}
