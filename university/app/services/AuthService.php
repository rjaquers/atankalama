<!--
  = Proyecto: Starter Kit RKM =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com.                   =
  = Fecha: <?= date('Y') ?>                  =

-->
<?php
class AuthService
{
    public function login($email, $password)
    {
        $model = new UserModel();
        $user = $model->getByEmail($email);

        if (!$user) return false;
        if ((int)$user['status'] !== 1) return false;

        if (!password_verify($password, $user['password_hash'])) return false;

        $_SESSION['user_id']   = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role']      = $user['role'];

        session_regenerate_id(true);
        return true;
    }
}
