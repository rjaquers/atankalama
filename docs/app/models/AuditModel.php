<?php
/**
 * Modelo de Auditoría.
 *
 * Registra acciones de los usuarios en la tabla audit_logs
 * para trazabilidad y seguridad del sistema.
 *
 * @package App\Models
 */
class AuditModel extends Model
{
    /**
     * Registra una acción en el log de auditoría.
     *
     * Qué hace:
     * - Obtiene la IP del cliente
     * - Inserta un registro con usuario, módulo, acción y descripción
     *
     * @param  int    $userId     ID del usuario que realizó la acción
     * @param  string $module     Módulo donde ocurrió (ej: 'contratos', 'empresas')
     * @param  string $action     Acción realizada (ej: 'crear', 'editar', 'eliminar')
     * @param  string $description Descripción detallada de la acción
     * @return bool   true si se registró correctamente
     *
     * Variables usadas:
     * - client_ip() (helper network.php)
     */
    public function add($userId, $module, $action, $description)
    {
        $ip = client_ip();
        $stmt = $this->conn->prepare("
            INSERT INTO audit_logs(user_id, module, action, description, ip_address)
            VALUES(?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issss", $userId, $module, $action, $description, $ip);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
    // Fin de la función add()
}
