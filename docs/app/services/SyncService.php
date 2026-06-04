<?php
class SyncService
{
    /**
     * Procesa un payload offline genérico.
     * En un proyecto real, enrutarías por "module" / "action".
     */
    public function handle($payload, $userId)
    {
        // Dedupe por uuid_offline (para evitar duplicados)
        $uuid = $payload['uuid_offline'] ?? null;
        $module = $payload['module'] ?? 'offline';
        $action = $payload['action'] ?? 'sync';
        $data   = $payload['data'] ?? [];

        // Auditoría (ejemplo)
        $audit = new AuditModel();
        $audit->add($userId, $module, $action, json_encode(['uuid'=>$uuid,'data'=>$data], JSON_UNESCAPED_UNICODE));

        // Dispara evento global (para enganchar notificaciones, etc.)
        EventDispatcher::dispatch("offline_synced", [
            'user_id' => $userId,
            'uuid'    => $uuid,
            'module'  => $module,
            'action'  => $action,
            'data'    => $data
        ]);

        return true;
    }
}
