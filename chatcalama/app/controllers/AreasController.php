<?php
/**
 * AreasController — gestión de áreas (solo Administrador)
 * PHP 7.4–8.2 compatible
 */
class AreasController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::admin();

        $areas = (new AreaModel())->getAll();

        // Contar usuarios por área
        $db   = new Database();
        $conn = $db->connect();
        foreach ($areas as &$area) {
            $stmt = $conn->prepare('SELECT COUNT(*) AS c FROM chat_usuarios WHERE area_id = ?');
            $stmt->bind_param('i', $area['id']);
            $stmt->execute();
            $res              = $stmt->get_result();
            $row              = $res ? $res->fetch_assoc() : null;
            $area['usuarios'] = $row ? (int)$row['c'] : 0;
        }
        unset($area);

        $title = 'Áreas';
        $this->view('areas/index', compact('areas', 'title'));
    }

    public function crear(): void
    {
        AuthMiddleware::admin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            (new AreaModel())->create($_POST);
            $this->redirect('/areas');
        }

        $title = 'Nueva Área';
        $this->view('areas/form', compact('title'));
    }

    public function editar(string $id): void
    {
        AuthMiddleware::admin();

        $area = (new AreaModel())->getById((int)$id);
        if ($area === null) {
            $this->redirect('/areas');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            (new AreaModel())->update((int)$id, $_POST);
            $this->redirect('/areas');
        }

        $title = 'Editar Área';
        $this->view('areas/form', compact('area', 'title'));
    }

    public function toggle(string $id): void
    {
        AuthMiddleware::admin();
        csrf_verify();
        (new AreaModel())->toggleEstado((int)$id);
        $this->redirect('/areas');
    }
}
