<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/LocationModel.php';

class LocationController
{
    /** @var PDO */
    protected PDO $db;

    /** @var LocationModel */
    protected LocationModel $model;

    /**
     * Constructor
     * Obtiene conexión PDO desde Database
     */
    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
        $this->model = new LocationModel($this->db);
    }

    public function index(): void
    {
        $locations = $this->model->getAll();
        require __DIR__ . '/../views/locations/index.php';
    }

    public function create(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->create(
                sanitize($_POST['name']),
                sanitize($_POST['description']),
                sanitize($_POST['zone'])
            );
            redirect('locations');
        }

        require __DIR__ . '/../views/locations/create.php';
    }

    public function edit(int $id): void
    {
        requireAdmin();

        $location = $this->model->getById($id);
        if (!$location) {
            redirect('locations');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->update(
                $id,
                sanitize($_POST['name']),
                sanitize($_POST['description']),
                sanitize($_POST['zone']),
                (int)$_POST['active']
            );
            redirect('locations');
        }

        require __DIR__ . '/../views/locations/edit.php';
    }

    public function delete(int $id): void
    {
        requireAdmin();

        if ($this->model->getProductCount($id) === 0) {
            $this->model->delete($id);
        }

        redirect('locations');
    }
}
