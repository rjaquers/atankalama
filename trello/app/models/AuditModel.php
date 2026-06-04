<?php
class AuditModel extends Model
{
    public function add($userId, $module, $action, $description)
    {
        $ip = client_ip();
        $stmt = $this->conn->prepare("
            INSERT INTO audit_logs(user_id, module, action, description, ip_address)
            VALUES(?,?,?,?,?)
        ");
        $stmt->bind_param("issss", $userId, $module, $action, $description, $ip);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
}
