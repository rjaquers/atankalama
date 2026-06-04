<?php
// controllers/EmpresaController.php
declare(strict_types=1);

require_once __DIR__ . '/../models/Empresa.php';

class EmpresaController
{
    private Empresa $empresa;

    public function __construct()
    {
        $this->empresa = new Empresa();
    }

    /** Formulario de alta */
    public function nuevo(array $flash = []): void
    {
        // $flash: ['error' => '...', 'form' => ['nombre' => '...', 'activo' => 1]]
        include __DIR__ . '/../views/empresas/nuevo.php';
    }

    /** POST: crear empresa */
    public function guardar(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            echo 'Método no permitido';
            return;
        }

        $data = [
            'business_name' => trim((string)($_POST['business_name'] ?? '')),
            'rut'           => trim((string)($_POST['rut']           ?? '')),
            'trade_name'    => trim((string)($_POST['trade_name']    ?? '')),
            'contact_name'  => trim((string)($_POST['contact_name']  ?? '')),
            'contact_email' => trim((string)($_POST['contact_email'] ?? '')),
            'contact_phone' => trim((string)($_POST['contact_phone'] ?? '')),
            'address'       => trim((string)($_POST['address']       ?? '')),
            'city'          => trim((string)($_POST['city']          ?? '')),
            'type'          => trim((string)($_POST['type']          ?? 'cliente')),
            'notes'         => trim((string)($_POST['notes']         ?? '')),
            'active'        => isset($_POST['active']) ? 1 : 0,
        ];

        if ($data['business_name'] === '') {
            $this->nuevo(['error' => 'La razón social es obligatoria.', 'form' => $data]);
            return;
        }

        try {
            $this->empresa->crear($data);
        } catch (Throwable $e) {
            $this->nuevo(['error' => $e->getMessage(), 'form' => $data]);
            return;
        }

        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        header('Location: ' . $base . '/empresas/listar?ok=1');
        exit;
    }

    /** Listado sencillo con filtros */
    public function listar(): void
    {
        $q      = isset($_GET['q'])      ? trim((string)$_GET['q']) : '';
        $activo = isset($_GET['activo']) ? trim((string)$_GET['activo']) : '';
        $page   = isset($_GET['page'])   ? max(1, (int)$_GET['page']) : 1;
        $sort   = isset($_GET['sort'])   ? trim((string)$_GET['sort']) : 'nombre';
        $order  = isset($_GET['order'])  ? trim((string)$_GET['order']) : 'ASC';

        $limit  = 20;
        $offset = ($page - 1) * $limit;

        $params = [
            'q'      => $q ?: null,
            'activo' => ($activo === '' ? null : (int)$activo),
            'sort'   => $sort,
            'order'  => $order,
            'limit'  => $limit,
            'offset' => $offset,
        ];

        $rows  = $this->empresa->listar($params);
        $total = $this->empresa->contar($params);
        $pages = (int)ceil(max(1, $total) / $limit);

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        include __DIR__ . '/../views/empresas/listar.php';
    }

    /** Formulario de edición */
    public function editar(int $id, array $flash = []): void
    {
        $row = $this->empresa->obtener($id);
        if (!$row) {
            http_response_code(404);
            echo 'Empresa no encontrada';
            return;
        }

        // Si no hay form data en flash, usamos el row de la DB
        if (!isset($flash['form'])) {
            $flash['form'] = $row;
        }

        include __DIR__ . '/../views/empresas/editar.php';
    }

    /** POST: actualizar empresa */
    public function actualizar(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            echo 'Método no permitido';
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'ID inválido';
            return;
        }

        $data = [
            'business_name' => trim((string)($_POST['business_name'] ?? '')),
            'rut'           => trim((string)($_POST['rut']           ?? '')),
            'trade_name'    => trim((string)($_POST['trade_name']    ?? '')),
            'contact_name'  => trim((string)($_POST['contact_name']  ?? '')),
            'contact_email' => trim((string)($_POST['contact_email'] ?? '')),
            'contact_phone' => trim((string)($_POST['contact_phone'] ?? '')),
            'address'       => trim((string)($_POST['address']       ?? '')),
            'city'          => trim((string)($_POST['city']          ?? '')),
            'type'          => trim((string)($_POST['type']          ?? 'cliente')),
            'notes'         => trim((string)($_POST['notes']         ?? '')),
            'active'        => isset($_POST['active']) ? 1 : 0,
        ];

        if ($data['business_name'] === '') {
            $this->editar($id, ['error' => 'La razón social es obligatoria.', 'form' => $data]);
            return;
        }

        try {
            $this->empresa->actualizar($id, $data);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Empresa actualizada correctamente.'];
        } catch (Throwable $e) {
            $this->editar($id, ['error' => $e->getMessage(), 'form' => $data]);
            return;
        }

        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        header('Location: ' . $base . '/empresas/listar');
        exit;
    }

    public function wifiimprimir(array $flash = []): void
    {

        include __DIR__ . '/../views/wifi/imprimir.php';
    }
}
