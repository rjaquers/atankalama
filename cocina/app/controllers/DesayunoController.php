<?php

require_once __DIR__ . '/../models/DesayunoMasivoModel.php';
require_once __DIR__ . '/../models/EmpresaModel.php';

class DesayunoController
{
    private const HOTELES = ['Atankalama', 'Atankalama Inn'];

    public function tablero(): void
    {
        $empresaModel = new EmpresaModel();
        $model        = new DesayunoMasivoModel();

        $fecha = $_GET['fecha'] ?? date('Y-m-d', strtotime('+1 day'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $fecha = date('Y-m-d', strtotime('+1 day'));
        }
        $hotel = in_array($_GET['hotel'] ?? '', self::HOTELES)
                 ? $_GET['hotel'] : 'Atankalama';

        $empresas  = $empresaModel->listarEmpresasActivas();
        $registros = $model->obtenerPorFechaHotel($fecha, $hotel);

        // Precargar proyectos para las empresas ya registradas (evita AJAX en carga)
        $proyectosPorEmpresa = [];
        foreach ($registros as $r) {
            $cid = (int)$r['company_id'];
            if ($cid && !isset($proyectosPorEmpresa[$cid])) {
                $proyectosPorEmpresa[$cid] = $empresaModel->listarProyectosPorEmpresa($cid);
            }
        }

        require_once __DIR__ . '/../views/desayuno/tablero.php';
    }

    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=desayuno/tablero');
            exit;
        }

        $fecha = trim($_POST['fecha'] ?? '');
        $hotel = in_array($_POST['nombre_hotel'] ?? '', self::HOTELES)
                 ? $_POST['nombre_hotel'] : 'Atankalama';

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            header('Location: index.php?page=desayuno/tablero&error=fecha_invalida');
            exit;
        }

        $usuario      = AccesoBootstrap::email() ?: 'sistema';
        $filas        = (array)($_POST['filas'] ?? []);
        $model        = new DesayunoMasivoModel();
        $empresaModel = new EmpresaModel();

        $idsGuardados = [];

        foreach ($filas as $fila) {
            $companyId = intval($fila['company_id'] ?? 0);
            $cantidad  = max(1, intval($fila['cantidad'] ?? 1));
            if (!$companyId) continue;

            $empresa = $empresaModel->obtenerEmpresa($companyId);
            if (!$empresa) continue;

            $projectId      = intval($fila['project_id'] ?? 0) ?: null;
            $observaciones  = trim($fila['observaciones'] ?? '') ?: null;
            $nombreEmpresa  = $empresa['business_name'];
            $nombreProyecto = null;

            if ($projectId) {
                foreach ($empresaModel->listarProyectosPorEmpresa($companyId) as $p) {
                    if ((int)$p['id'] === $projectId) {
                        $nombreProyecto = $p['name'];
                        break;
                    }
                }
            }

            $model->upsert($fecha, $hotel, $companyId, $projectId,
                           $nombreEmpresa, $nombreProyecto, $cantidad, $observaciones, $usuario);
            $idsGuardados[] = $companyId;
        }

        $model->eliminarPorCompanyIds($fecha, $hotel, $idsGuardados, $usuario);

        $total = count($idsGuardados);
        header("Location: index.php?page=desayuno/tablero"
             . "&fecha=" . urlencode($fecha)
             . "&hotel=" . urlencode($hotel)
             . "&ok={$total}");
        exit;
    }

    public function proyectosAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $companyId = intval($_GET['company_id'] ?? 0);
        if (!$companyId) { echo json_encode([]); exit; }
        echo json_encode((new EmpresaModel())->listarProyectosPorEmpresa($companyId));
        exit;
    }
}
