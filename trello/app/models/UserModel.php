<?php
class UserModel extends Model
{
    public function getByEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM chk_usuarios WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    public function countActive()
    {
        // En este sistema, los usuarios activos tienen estado = 'activo'
        $res = $this->conn->query("SELECT COUNT(*) AS total FROM chk_usuarios WHERE estado='activo'");
        $row = $res ? $res->fetch_assoc() : ['total'=>0];
        return (int)$row['total'];
    }
}
