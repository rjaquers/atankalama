<?php
/**
 * Controlador: LogController
 * Función: visualizar los registros de cambios de productos
 * Autor: Rodrigo Jaque Escobar
 */

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Log.php';


class LogController
{
    protected PDO $conn;
    protected Log $log_model;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();

        $this->log_model = new Log($this->conn);
    }

    public function index(): void
    {
        /**
         * Lista logs del mes actual o del mes seleccionado
         */

        // Mes y año seleccionados (o actuales)
        $year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
        $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');

        // Validación básica
        if ($month < 1 || $month > 12) {
            $month = (int)date('n');
        }

        // Obtener logs filtrados
        $logs = $this->log_model->getProductLogsByMonth($year, $month);

        $page_title = 'Historial de Cambios';
        $view = __DIR__ . '/../views/logs/index.php';

        require __DIR__ . '/../views/layout/home.php';
    }



    //public function index(): void
    //{
    //    $logs = $this->log_model->getProductLogs();
    //
    //    $page_title = 'Historial de Cambios';
    //    $view = __DIR__ . '/../views/logs/index.php';
    //
    //    require __DIR__ . '/../views/layout/home.php';
    //}

    /**
     * Verifica que el usuario actual sea administrador
     */
    private function checkAdmin(): void
    {
        if (!isAdmin()) {
            $_SESSION['error'] = 'Acceso no autorizado.';
            redirect('dashboard');
            exit;
        }
    }

    ///**
    // * Lista los registros de cambios de productos
    // */
    //public function index(): void
    //{
    //    $logs = $this->log_model->getProductLogs();
    //
    //    $page_title = 'Historial de Cambios';
    //    $view = __DIR__ . '/../views/logs/index.php';
    //
    //    require __DIR__ . '/../views/layout/home.php';
    //}

    /**
     * Limpia todos los logs (solo admin)
     */
    public function clear(): void
    {
        $this->checkAdmin();

        $this->log_model->clearAll();
        $_SESSION['success'] = 'El registro fue limpiado correctamente.';

        redirect('logs');
    }
}
