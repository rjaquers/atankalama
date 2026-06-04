<?php
/**
 * DashboardModel — estadísticas para el panel principal
 * PHP 7.4–8.2 compatible
 */
class DashboardModel extends Model
{
    public function getStats(): array
    {
        $stats = [
            'usuarios_activos' => $this->scalar("SELECT COUNT(*) FROM chat_usuarios WHERE estado = 1"),
            'mensajes_hoy'     => $this->scalar("SELECT COUNT(*) FROM chat_mensajes WHERE DATE(created_at) = CURDATE()"),
            'tareas'           => $this->taskStats(),
            'mantencion'       => $this->mantStats(),
        ];
        return $stats;
    }

    public function getStatsForUser(int $userId, int $areaId): array
    {
        $stats = $this->getStats();

        // Tareas asignadas al usuario
        $stmt = $this->conn->prepare("
            SELECT
                SUM(estado = 'pendiente')  AS pendientes,
                SUM(estado = 'en_proceso') AS en_proceso,
                SUM(estado = 'completada') AS completadas
            FROM chat_tareas
            WHERE asignado_a = ?
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stats['mis_tareas'] = $stmt->get_result()->fetch_assoc() ?? ['pendientes'=>0,'en_proceso'=>0,'completadas'=>0];

        // Mantención de su área
        if ($areaId) {
            $stmt2 = $this->conn->prepare("
                SELECT
                    SUM(estado = 'pendiente')  AS pendientes,
                    SUM(estado = 'en_proceso') AS en_proceso
                FROM chat_mantencion
                WHERE area_id = ? AND estado NOT IN ('completada','cancelada')
            ");
            $stmt2->bind_param('i', $areaId);
            $stmt2->execute();
            $stats['area_mantencion'] = $stmt2->get_result()->fetch_assoc() ?? ['pendientes'=>0,'en_proceso'=>0];
        }

        return $stats;
    }

    private function taskStats(): array
    {
        $res = $this->conn->query("
            SELECT
                SUM(estado = 'pendiente')  AS pendientes,
                SUM(estado = 'en_proceso') AS en_proceso,
                SUM(estado = 'completada') AS completadas
            FROM chat_tareas
        ");
        return $res ? ($res->fetch_assoc() ?? []) : ['pendientes'=>0,'en_proceso'=>0,'completadas'=>0];
    }

    private function mantStats(): array
    {
        $res = $this->conn->query("
            SELECT
                SUM(estado = 'pendiente')  AS pendientes,
                SUM(estado = 'en_proceso') AS en_proceso,
                SUM(estado = 'completada') AS completadas
            FROM chat_mantencion
        ");
        return $res ? ($res->fetch_assoc() ?? []) : ['pendientes'=>0,'en_proceso'=>0,'completadas'=>0];
    }

    private function scalar(string $sql): int
    {
        $res = $this->conn->query($sql);
        if (!$res) return 0;
        $row = $res->fetch_row();
        return $row ? (int)$row[0] : 0;
    }
}
