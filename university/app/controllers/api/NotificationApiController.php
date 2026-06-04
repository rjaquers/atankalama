<!--
  = Proyecto: Starter Kit RKM =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com.                   =
  = Fecha: <?= date('Y') ?>                  =

-->
<?php
class NotificationApiController extends Controller
{
    public function latest()
    {
        AuthMiddleware::check();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $items = (new NotificationModel())->latestForUser($userId, 10);
        return $this->json(['data'=>$items]);
    }
}
