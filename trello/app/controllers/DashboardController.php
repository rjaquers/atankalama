<?php
class DashboardController extends Controller
{
    private int $usuario_id;
    private TableroModel $modelo;

    public function __construct()
    {
        global $email;
        $this->modelo = new TableroModel();
        $uid = $this->modelo->usuarioId($email ?? '');
        if (!$uid) {
            $this->redirect('/logout');
        }
        $this->usuario_id = $uid;
    }

    public function index()
    {
        $userModel = new UserModel();
        $stats = $this->modelo->estadisticasGlobales($this->usuario_id);
        $tableros_stats = $this->modelo->estadisticasPorTablero($this->usuario_id);
        
        $this->view("dashboard/index", [
            'totalUsers' => $userModel->countActive(),
            'notifications' => [],
            'stats' => $stats,
            'tableros_stats' => $tableros_stats,
            'tableros_nav' => $this->modelo->todos()
        ]);
    }
}
