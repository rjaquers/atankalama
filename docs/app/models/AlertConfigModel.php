<?php
/**
 * Modelo de Configuración de Alertas.
 *
 * Gestiona la configuración de alertas de vencimiento
 * en la tabla doc_alert_config.
 * Define con cuántos días de anticipación se envían
 * las notificaciones por email.
 *
 * @package App\Models
 */
class AlertConfigModel extends Model
{
    /**
     * Obtiene todas las configuraciones de alertas activas.
     *
     * @return array Lista de alertas ordenadas por días (descendente)
     */
    public function getAll()
    {
        $res = $this->conn->query("
            SELECT * FROM doc_alert_config
            WHERE active = 1
            ORDER BY days_before DESC
        ");
        $rows = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }
    // Fin de la función getAll()

    /**
     * Busca una configuración por su ID.
     *
     * @param  int $id ID de la configuración
     * @return array|null Datos de la configuración o null
     */
    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM doc_alert_config WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }
    // Fin de la función getById()

    /**
     * Crea una nueva configuración de alerta.
     *
     * @param  array $data Datos:
     *   - days_before     (int) Días antes del vencimiento
     *   - email_enabled   (int) 1 = sí, 0 = no
     *   - email_recipients (string|null) Emails adicionales
     * @return int|false ID creado o false
     */
    public function create($data)
    {
        $recipients = $data['email_recipients'] ?? null;
        $stmt = $this->conn->prepare("
            INSERT INTO doc_alert_config(days_before, email_enabled, email_recipients)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iis", $data['days_before'], $data['email_enabled'], $recipients);
        $stmt->execute();
        return $stmt->affected_rows > 0 ? $stmt->insert_id : false;
    }
    // Fin de la función create()

    /**
     * Actualiza una configuración de alerta.
     *
     * @param  int   $id   ID de la configuración
     * @param  array $data Datos a actualizar
     * @return bool  true si se actualizó
     */
    public function update($id, $data)
    {
        $recipients = $data['email_recipients'] ?? null;
        $stmt = $this->conn->prepare("
            UPDATE doc_alert_config
            SET days_before = ?, email_enabled = ?, email_recipients = ?
            WHERE id = ?
        ");
        $stmt->bind_param("iisi", $data['days_before'], $data['email_enabled'], $recipients, $id);
        $stmt->execute();
        return $stmt->affected_rows >= 0;
    }
    // Fin de la función update()

    /**
     * Desactiva una configuración de alerta.
     *
     * @param  int  $id ID de la configuración
     * @return bool true si se desactivó
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare("UPDATE doc_alert_config SET active = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
    // Fin de la función delete()
}
