<?php
class EtiquetaController extends Controller
{
    private int $usuario_id;
    private EtiquetaModel $modelo;
    private TarjetaModel $tarjetaModelo;
    private TableroModel $tableroModelo;

    public function __construct()
    {
        global $email;
        $this->modelo        = new EtiquetaModel();
        $this->tarjetaModelo = new TarjetaModel();
        $this->tableroModelo = new TableroModel();
        $uid = $this->tableroModelo->usuarioId($email ?? '');
        if (!$uid) $this->json(['ok' => false, 'error' => 'No autenticado'], 401);
        $this->usuario_id = $uid;
    }

    // POST /etiqueta/toggle  { tarjeta_id, etiqueta_id }
    public function toggle(): void
    {
        $d           = $this->input();
        $tarjeta_id  = (int)($d['tarjeta_id']  ?? 0);
        $etiqueta_id = (int)($d['etiqueta_id'] ?? 0);
        if (!$tarjeta_id || !$etiqueta_id) $this->json(['ok' => false], 400);

        $tablero_id = $this->tarjetaModelo->tableroDeTarjeta($tarjeta_id);
        if (!$tablero_id || !$this->tableroModelo->puedeEditar($tablero_id, $this->usuario_id)) {
            $this->json(['ok' => false, 'error' => 'Sin permiso'], 403);
        }
        $action = $this->modelo->toggleTarjeta($tarjeta_id, $etiqueta_id);
        $this->json(['ok' => true, 'action' => $action]);
    }

    private function input(): array
    {
        $raw = file_get_contents('php://input');
        return $raw ? (json_decode($raw, true) ?? []) : $_POST;
    }
}
