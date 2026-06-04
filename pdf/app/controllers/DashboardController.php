<?php
/**
 * DashboardController — panel principal
 * PHP 7.4–8.2 compatible
 */
class DashboardController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::check();

        $model = new DashboardModel();
        $stats = $model->getStatsForUser(
            (int)$_SESSION['user_id'],
            (int)($_SESSION['user_area_id'] ?? 0)
        );

        $title = 'Inicio';
        $this->view('dashboard/index', compact('stats', 'title'));
    }
}
