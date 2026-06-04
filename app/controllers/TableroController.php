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
        $this->redirect('/tablero/ver/' . $tableros[0]['id']);
    }

    public function ver(string $id): void
    {
        $tablero = $this->modelo->porId((int)$id);
        if (!$tablero) {
            $this->redirect('/tablero');
        }

        $this->view('tablero/index', [
            'tablero'      => $tablero,
            'listas'       => $this->modelo->listasConTarjetas((int)$id),
            'tableros_nav' => $this->modelo->todos(),
            'puede_editar' => $this->modelo->puedeEditar((int)$id, $this->usuario_id),
        ]);
    }
}
