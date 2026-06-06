<?php
/**
 * ServiciosController - Atankalama Empresas
 */
class ServiciosController extends Controller
{
    public function index()
    {
        $user = AuthService::user();
        $days = $_GET['days'] ?? 7;
        
        $model = new ServiciosModel();
        $servicios = $model->getAllByCompany($user['company_id'], $days, 1000);
        
        $data = [
            'title' => 'Historial de Servicios',
            'user' => $user,
            'days' => $days,
            'servicios' => $servicios
        ];

        $this->view('servicios/index', $data);
    }
}
