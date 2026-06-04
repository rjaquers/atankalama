<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Area;
use App\Core\Logger;

class AreaController extends Controller
{
    public function index()
    {
        $model = new Area();
        $areas = $model->all();
        $this->render('areas/index', [
            'areas' => $areas,
            'active' => 'areas'
        ]);
    }

    public function store()
    {
        $id = $_POST['id'] ?? null;
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $estado = $_POST['estado'] ?? 'activo';

        if (empty($nombre)) {
            return $this->json(['error' => 'El nombre es obligatorio'], 400);
        }

        $model = new Area();
        if ($id) {
            $success = $model->update($id, $nombre, $descripcion, $estado);
            $msg = 'Área actualizada';
        } else {
            $success = $model->create($nombre, $descripcion);
            $msg = 'Área creada';
        }

        if ($success) {
            Logger::info('AREA_MGMT', $msg, ['nombre' => $nombre], \AccesoBootstrap::email());
            return $this->json(['message' => $msg]);
        }

        return $this->json(['error' => 'Error al procesar el área'], 500);
    }

    public function delete()
    {
        $id = $_POST['id'] ?? null;
        if (!$id)
            return $this->json(['error' => 'ID no válido'], 400);

        $model = new Area();
        if ($model->delete($id)) {
            Logger::warning('AREA_MGMT', 'Área eliminada', ['id' => $id], \AccesoBootstrap::email());
            return $this->json(['message' => 'Área eliminada']);
        }

        return $this->json(['error' => 'Error al eliminar'], 500);
    }
}
