<?php
class OfflineSyncController extends Controller
{
    public function store()
    {
        AuthMiddleware::check();

        // JSON body
        $raw = file_get_contents("php://input");
        $payload = json_decode($raw, true);

        if (!is_array($payload)) {
            return $this->json(['status'=>'error','message'=>'JSON inválido'], 400);
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);

        $sync = new SyncService();
        $ok = $sync->handle($payload, $userId);

        return $this->json(['status' => $ok ? 'ok' : 'error']);
    }
}
