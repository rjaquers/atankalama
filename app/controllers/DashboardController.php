<!--
  = Proyecto: Starter Kit RKM =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com.                   =
  = Fecha: <?= date('Y') ?>                  =

-->
<?php
class DashboardController extends Controller
{
    public function index()
    {
        AuthMiddleware::check();

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $notifications = (new NotificationModel())->latestForUser($userId, 8);
        $totalUsers = (new UserModel())->countActive();

        $this->view("dashboard/index", compact('notifications', 'totalUsers'));
    }
}
