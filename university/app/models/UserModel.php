<!--
  = Proyecto: Starter Kit RKM =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com.                   =
  = Fecha: <?= date('Y') ?>                  =

-->
<?php
class UserModel extends Model
{
    public function getByEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    public function countActive()
    {
        $res = $this->conn->query("SELECT COUNT(*) AS total FROM users WHERE status=1");
        $row = $res ? $res->fetch_assoc() : ['total'=>0];
        return (int)$row['total'];
    }
}
