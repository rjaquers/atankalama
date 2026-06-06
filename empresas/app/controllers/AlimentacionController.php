<?php
/**
 * AlimentacionController - Atankalama Empresas
 */
class AlimentacionController extends Controller
{
    public function index()
    {
        $user = AuthService::user();
        $days = $_GET['days'] ?? 7;
        $start_date = $_GET['start_date'] ?? null;
        $end_date = $_GET['end_date'] ?? null;
        
        $model = new AlimentacionModel();
        // Obtenemos todos los registros para el periodo seleccionado (límite de 1000 por seguridad)
        $registros = $model->getLatestByCompany($user['company_id'], $days, 1000, $start_date, $end_date);
        
        $data = [
            'title' => 'Historial de Alimentación',
            'user' => $user,
            'days' => $days,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'registros' => $registros
        ];

        $this->view('alimentacion/index', $data);
    }

    public function persona($rutEncoded)
    {
        $user = AuthService::user();
        $rut = base64_decode($rutEncoded);
        
        $model = new AlimentacionModel();
        $registros = $model->getHistoryByRut($user['company_id'], $rut);
        
        if (empty($registros)) {
            header("Location: " . BASE_URL . "alimentacion?error=No se encontraron registros para este usuario");
            exit;
        }

        $nombre = $registros[0]['nombre_comensal'];

        $data = [
            'title' => "Historial: $nombre",
            'user' => $user,
            'nombre' => $nombre,
            'rut' => $registros[0]['rut_masked'],
            'registros' => $registros
        ];

        $this->view('alimentacion/persona', $data);
    }
}
