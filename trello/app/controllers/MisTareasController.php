<?php
class MisTareasController extends Controller
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
        if (!$uid) {
            $this->redirect('/tablero');
        }
        $this->usuario_id = $uid;
    }

    public function index(): void
    {
        $tareas      = $this->tarjetaModelo->asignadasAUsuario($this->usuario_id);
        $tableros_nav = $this->tableroModelo->todos();

        $hoy    = date('Y-m-d');
        $semana = date('Y-m-d', strtotime('+7 days'));

        foreach ($tareas as &$t) {
            $fv = $t['fecha_vencimiento'] ? substr($t['fecha_vencimiento'], 0, 10) : null;
            if (!$fv) {
                $t['urgencia'] = 'sin_fecha';
            } elseif ($fv < $hoy) {
                $t['urgencia'] = 'vencida';
            } elseif ($fv === $hoy) {
                $t['urgencia'] = 'hoy';
            } elseif ($fv <= $semana) {
                $t['urgencia'] = 'semana';
            } else {
                $t['urgencia'] = 'futura';
            }
        }
        unset($t);

        // Conteos por filtro
        $conteos = ['todas' => count($tareas), 'vencida' => 0, 'hoy' => 0, 'semana' => 0, 'sin_fecha' => 0, 'futura' => 0];
        foreach ($tareas as $t) {
            $conteos[$t['urgencia']]++;
        }

        // Agrupar por tablero para la vista
        $por_tablero = [];
        foreach ($tareas as $t) {
            $key = $t['tablero_id'];
            if (!isset($por_tablero[$key])) {
                $por_tablero[$key] = [
                    'nombre'      => $t['tablero_nombre'],
                    'fondo_color' => $t['fondo_color'],
                    'tarjetas'    => [],
                ];
            }
            $por_tablero[$key]['tarjetas'][] = $t;
        }

        $this->view('mistareas/index', compact('tareas', 'por_tablero', 'conteos', 'tableros_nav'));
    }
}
