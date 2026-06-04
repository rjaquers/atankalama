<?php

require_once __DIR__ . '/../models/ReservaModel.php';
require_once __DIR__ . '/../models/ComandaModel.php';
require_once __DIR__ . '/../models/CambioLogModel.php';

class ReservaController
{
    // ─────────────────────────────────────────────────────────
    // CREAR RESERVA
    // ─────────────────────────────────────────────────────────

    public function crear(): void
    {
        $cm = new ComandaModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre    = trim($_POST['nombre'] ?? '');
            $companyId = $_POST['company_id'] ? (int)$_POST['company_id'] : null;
            $empNombre = trim($_POST['nombre_empresa'] ?? '') ?: null;
            $obs       = trim($_POST['observaciones'] ?? '') ?: null;
            $ids       = array_filter(array_map('intval', $_POST['comandas'] ?? []));

            if ($nombre === '' || empty($ids)) {
                $error = 'Debe ingresar un nombre y seleccionar al menos una comanda.';
                $comandasSinReserva = $cm->obtenerSinReserva();
                require_once __DIR__ . '/../views/reserva/crear.php';
                return;
            }

            // Calcular fechas desde/hasta según las comandas seleccionadas
            $fechas = [];
            foreach ($ids as $cid) {
                $c = $cm->obtenerPorId($cid);
                if ($c) $fechas[] = $c['fecha'];
            }
            sort($fechas);
            $fechaDesde = reset($fechas);
            $fechaHasta = end($fechas);

            $rm        = new ReservaModel();
            $reservaId = $rm->crear($nombre, $fechaDesde, $fechaHasta, $companyId, $empNombre, $obs);

            foreach ($ids as $cid) {
                $rm->vincularComanda($cid, $reservaId);
            }

            header("Location: index.php?page=reserva/ver/{$reservaId}&ok=creada");
            exit;
        }

        $comandasSinReserva = $cm->obtenerSinReserva();
        require_once __DIR__ . '/../views/reserva/crear.php';
    }

    // ─────────────────────────────────────────────────────────
    // VER RESERVA (resumen de todos los días)
    // ─────────────────────────────────────────────────────────

    public function ver(string $id = '0'): void
    {
        $reservaId = (int) $id;
        $rm        = new ReservaModel();
        $reserva   = $rm->obtenerPorId($reservaId);

        if (!$reserva) {
            echo '<div class="alert alert-danger m-4">Reserva no encontrada.</div>';
            return;
        }

        $reserva['comandas'] = $rm->obtenerComandasDeReserva($reservaId);
        $ok                  = $_GET['ok'] ?? null;

        require_once __DIR__ . '/../views/reserva/ver.php';
    }

    // ─────────────────────────────────────────────────────────
    // EDITAR CANTIDAD DE PERSONAS (POST, desde voucher/clientes)
    // ─────────────────────────────────────────────────────────

    public function editarCantidad(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=comanda/listado');
            exit;
        }

        $comandaId = (int)($_POST['comanda_id'] ?? 0);
        $nueva     = max(1, (int)($_POST['cantidad'] ?? 1));

        $cm      = new ComandaModel();
        $comanda = $cm->obtenerPorId($comandaId);

        if (!$comanda) {
            header('Location: index.php?page=comanda/listado');
            exit;
        }

        $anterior = (int) $comanda['cantidad_personas'];

        if ($anterior !== $nueva) {
            $cm->actualizarCantidad($comandaId, $nueva);

            $email = AccesoBootstrap::email() ?? 'sistema';
            (new CambioLogModel())->registrar(
                $comandaId,
                'coci_comandas',
                'cantidad_personas',
                (string) $anterior,
                (string) $nueva,
                $email,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $comanda['reserva_id'] ?? null
            );
        }

        header("Location: index.php?page=voucher/clientes/{$comandaId}&ok=cantidad");
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // LOG DE CAMBIOS DE UNA RESERVA
    // ─────────────────────────────────────────────────────────

    public function logCambios(string $id = '0'): void
    {
        $reservaId = (int) $id;
        $rm        = new ReservaModel();
        $reserva   = $rm->obtenerPorId($reservaId);

        if (!$reserva) {
            echo '<div class="alert alert-danger m-4">Reserva no encontrada.</div>';
            return;
        }

        $cambios = (new CambioLogModel())->obtenerPorReserva($reservaId);

        require_once __DIR__ . '/../views/reserva/log.php';
    }

    // ─────────────────────────────────────────────────────────
    // AGREGAR DÍA A UNA RESERVA EXISTENTE
    // ─────────────────────────────────────────────────────────

    public function agregarDia(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=comanda/listado');
            exit;
        }

        $reservaId    = (int)($_POST['reserva_id']       ?? 0);
        $origenId     = (int)($_POST['comanda_origen_id'] ?? 0);
        $fecha        = trim($_POST['fecha']              ?? '');
        $horaServicio = !empty($_POST['hora_servicio'])   ? $_POST['hora_servicio'] . ':00' : null;
        $cantidad     = max(1, (int)($_POST['cantidad_personas'] ?? 1));

        if ($reservaId <= 0 || $origenId <= 0 || $fecha === '') {
            header('Location: index.php?page=comanda/listado');
            exit;
        }

        $cm      = new ComandaModel();
        $rm      = new ReservaModel();
        $origen  = $cm->obtenerPorId($origenId);
        $reserva = $rm->obtenerPorId($reservaId);

        if (!$origen || !$reserva) {
            header('Location: index.php?page=comanda/listado');
            exit;
        }

        // Crear nueva comanda heredando datos de la comanda origen
        $nuevaId = $cm->insertar(
            $fecha,
            $origen['tipo_servicio'],
            $origen['nombre_hotel'],
            $origen['tipo_solicitante'],
            $origen['company_id']      ? (int)$origen['company_id']   : null,
            $origen['contract_id']     ? (int)$origen['contract_id']  : null,
            $origen['nombre_empresa']  ?? null,
            $origen['nombre_contacto'] ?? null,
            $cantidad,
            $horaServicio,
            $origen['observaciones']   ?? null,
            (int)($origen['es_para_llevar'] ?? 0),
            'programada',
            null,
            $reservaId
        );

        // Actualizar fecha_hasta de la reserva si la nueva fecha es posterior
        if ($fecha > $reserva['fecha_hasta']) {
            $rm->actualizar(
                $reservaId,
                $reserva['nombre'],
                $reserva['fecha_desde'],
                $fecha,
                $reserva['company_id']     ? (int)$reserva['company_id']   : null,
                $reserva['nombre_empresa'] ?? null,
                $reserva['observaciones']  ?? null
            );
        }

        header("Location: index.php?page=voucher/clientes/{$nuevaId}&ok=dia_agregado");
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // VINCULAR / DESVINCULAR COMANDA (POST)
    // ─────────────────────────────────────────────────────────

    public function vincular(): void
    {
        $reservaId = (int)($_POST['reserva_id'] ?? 0);
        $comandaId = (int)($_POST['comanda_id'] ?? 0);

        if ($reservaId > 0 && $comandaId > 0) {
            (new ReservaModel())->vincularComanda($comandaId, $reservaId);
        }

        header("Location: index.php?page=reserva/ver/{$reservaId}");
        exit;
    }

    public function desvincular(): void
    {
        $reservaId = (int)($_POST['reserva_id'] ?? 0);
        $comandaId = (int)($_POST['comanda_id'] ?? 0);

        if ($comandaId > 0) {
            (new ReservaModel())->desvincularComanda($comandaId);
        }

        header("Location: index.php?page=reserva/ver/{$reservaId}");
        exit;
    }
}
