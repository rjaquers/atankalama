<?php
class PlanificadorController extends Controller
{
    private int $usuario_id;
    private TarjetaModel $tarjetaModelo;
    private TableroModel $tableroModelo;

    public function __construct()
    {
        global $email;
        $this->tarjetaModelo = new TarjetaModel();
        $this->tableroModelo = new TableroModel();
        $uid = $this->tableroModelo->usuarioId($email ?? '');
        if (!$uid) $this->redirect('/logout');
        $this->usuario_id = $uid;
    }

    public function index(): void
    {
        $tarjetas     = $this->tarjetaModelo->conFechaVencimiento($this->usuario_id);
        $tableros_nav = $this->tableroModelo->todos();
        $this->view('planificador/index', compact('tarjetas', 'tableros_nav'));
    }
}
