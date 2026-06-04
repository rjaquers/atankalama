<?php
class TableroController extends Controller
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

    public function index(): void
    {
        $tableros = $this->modelo->todos();
        if (empty($tableros)) {
            $this->view('tablero/index', [
                'tableros_nav' => [],
                'tablero'      => null,
                'listas'       => [],
                'puede_editar' => false,
            ]);
            return;
        }
        $this->redirect('/tablero/ver?id=' . $tableros[0]['id']);
    }

    // GET /tablero/ver?id=ID o /tablero/ver/ID
    public function ver(string $id = ''): void
    {
        $id = (int)($id ?: ($_GET['id'] ?? 0));
        if (!$id) { $this->redirect('/tablero'); }

        // DEBUG
        app_log($this->modelo->debugEsquema('trell_comentarios'));

        $tablero = $this->modelo->porId($id);
        if (!$tablero) {
            $this->redirect('/tablero');
        }

        $listas = $this->modelo->listasConTarjetas((int)$id);

        // Inyectar referencias apuntando a este tablero en las listas correspondientes
        $refModelo = new ReferenciaModel();
        $refs = $refModelo->porTableroDestino((int)$id);
        $refs_por_lista = [];
        foreach ($refs as $r) {
            $refs_por_lista[$r['lista_destino_id']][] = $r;
        }
        foreach ($listas as &$lista) {
            $lista['referencias'] = $refs_por_lista[$lista['id']] ?? [];
        }

        $this->view('tablero/index', [
            'tablero'      => $tablero,
            'listas'       => $listas,
            'tableros_nav' => $this->modelo->todos(),
            'puede_editar' => $this->modelo->puedeEditar((int)$id, $this->usuario_id),
        ]);
    }

    // POST /tablero/fondo  { id, fondo_color, fondo_imagen }
    public function fondo(): void
    {
        $d            = $this->input();
        $id           = (int)($d['id']           ?? 0);
        $fondo_color  = trim($d['fondo_color']   ?? '');
        $fondo_imagen = trim($d['fondo_imagen']  ?? '');

        if (!$id) $this->json(['ok' => false, 'error' => 'ID requerido'], 400);
        if (!$this->modelo->puedeEditar($id, $this->usuario_id)) {
            $this->json(['ok' => false, 'error' => 'Sin permiso'], 403);
        }
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $fondo_color)) $fondo_color = '#1e3a5f';
        if ($fondo_imagen && !preg_match('/^https:\/\/images\.unsplash\.com\//', $fondo_imagen)) {
            $fondo_imagen = '';
        }

        $ok = $this->modelo->actualizarFondo($id, $fondo_color, $fondo_imagen);
        $this->json(['ok' => $ok]);
    }

    // GET /tablero/archivadas?id=ID
    public function archivadas(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) $this->json(['ok' => false, 'error' => 'Tablero ID requerido'], 400);

        if (!$this->modelo->puedeEditar($id, $this->usuario_id)) {
            $this->json(['ok' => false, 'error' => 'Sin acceso'], 403);
        }

        $tarjetaModelo = new TarjetaModel();
        $archivadas = $tarjetaModelo->archivadasPorTablero($id);
        $this->json(['ok' => true, 'archivadas' => $archivadas]);
    }

    private function input(): array
    {
        $raw = file_get_contents('php://input');
        return $raw ? (json_decode($raw, true) ?? []) : $_POST;
    }
}
