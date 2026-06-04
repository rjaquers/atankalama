<?php
/**
 * Proyecto: Starter Kit RKM
 * Autor: Rodrigo Jaque Escobar
 * Contacto: rjaquers@gmail.com
 */
class NotificationApiController extends Controller
{
    public function latest(): void
    {
        $payload = AuthMiddleware::api();
        $userId  = (int)($payload['uid'] ?? 0);
        $items   = (new NotificationModel())->latestForUser($userId, 10);
        $this->json(['ok' => true, 'data' => $items]);
    }
}
