<?php
/**
 * Controller de Reportes.
 *
 * Genera resúmenes ejecutivos y exportaciones de datos en formato
 * Excel/CSV y PDF para directores y cobranzas.
 *
 * @package App\Controllers
 */
class ReportsController extends Controller
{
    /**
     * Muestra la página principal de reportes con filtros.
     * Permiso requerido: reports_view
     */
    public function index()
    {
        PermissionMiddleware::check('reports_view');

        $contractModel = new ContractModel();
        $paymentModel = new PaymentModel();
        $companyModel = new CompanyModel();

        // Datos para mini-reportes en tabla
        $contracts = $contractModel->getAll();
        $companies = $companyModel->getAll();
        
        // Totales de pago global
        $totalPaid = 0;
        $totalPending = $paymentModel->getTotalPendingAmount();

        $this->view('reports/index', compact(
            'contracts', 'companies', 'totalPaid', 'totalPending'
        ));
    }

    /**
     * Exporta los contratos actuales a CSV.
     */
    public function export_contracts()
    {
        PermissionMiddleware::check('reports_export');
        
        $contracts = (new ContractModel())->getAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=reporte_contratos_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Código', 'Empresa', 'Tipo', 'Estado', 'Monto Base', 'F. Inicio', 'F. Término']);

        foreach ($contracts as $c) {
            fputcsv($output, [
                $c['code'],
                $c['business_name'],
                $c['contract_type'],
                $c['status'],
                $c['base_amount'],
                $c['start_date'],
                $c['end_date'] ?? 'Indefinido'
            ]);
        }
        fclose($output);
        exit;
    }
}
