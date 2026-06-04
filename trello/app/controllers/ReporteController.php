<?php
class ReporteController extends Controller
{
    private int $usuario_id;
    private TableroModel $modelo;

    public function __construct()
    {
        global $email;
        $this->modelo = new TableroModel();
        $uid = $this->modelo->usuarioId($email ?? '');
        if (!$uid) {
            http_response_code(401);
            die('No autenticado');
        }
        $this->usuario_id = $uid;
    }

    // GET /reporte/excel?id=ID
    public function excel(string $id = ''): void
    {
        $id      = (int)($id ?: ($_GET['id'] ?? 0));
        $tablero = $this->obtenerTablero($id);
        $listas  = $this->modelo->listasConTarjetas($id);

        // Añadir referencias (ya está en el controlador de tablero, replicamos la lógica)
        $refModelo   = new ReferenciaModel();
        $refs        = $refModelo->porTableroDestino($id);
        $refs_por_lista = [];
        foreach ($refs as $r) {
            $refs_por_lista[$r['lista_destino_id']][] = $r;
        }
        foreach ($listas as &$lista) {
            $lista['referencias'] = $refs_por_lista[$lista['id']] ?? [];
        }
        unset($lista);

        ExcelReport::exportTablero($tablero, $listas);
    }

    // GET /reporte/pdf?id=ID  (o ?id=ID&autoprint=1 para imprimir directo)
    public function pdf(string $id = ''): void
    {
        $id      = (int)($id ?: ($_GET['id'] ?? 0));
        $tablero = $this->obtenerTablero($id);
        $listas  = $this->modelo->listasConTarjetas($id);

        $refModelo   = new ReferenciaModel();
        $refs        = $refModelo->porTableroDestino($id);
        $refs_por_lista = [];
        foreach ($refs as $r) {
            $refs_por_lista[$r['lista_destino_id']][] = $r;
        }
        foreach ($listas as &$lista) {
            $lista['referencias'] = $refs_por_lista[$lista['id']] ?? [];
        }
        unset($lista);

        // La vista es un HTML standalone (no usa el layout kanban)
        $data = compact('tablero', 'listas');
        extract($data);
        require VIEW_PATH . '/reporte/pdf.php';
        exit;
    }

    private function obtenerTablero(int $id): array
    {
        if (!$id) {
            http_response_code(400);
            die('ID de tablero requerido');
        }
        $tablero = $this->modelo->porId($id);
        if (!$tablero) {
            http_response_code(404);
            die('Tablero no encontrado');
        }
        return $tablero;
    }
}
