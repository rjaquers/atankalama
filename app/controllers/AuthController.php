<!--
  = Proyecto: Starter Kit RKM =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com.                   =
  = Fecha: <?= date('Y') ?>                  =

-->
<?php
class AuthController extends Controller
{
    public function login()
    {
        $error = null;
        $this->view("auth/login", compact('error'));
    }

    public function authenticate()
    {
        csrf_verify();

        $email = trim($_POST['email'] ?? '');
        $pass  = (string)($_POST['password'] ?? '');

        $service = new AuthService();
        if ($service->login($email, $pass)) {
            $this->redirect("/dashboard");
        }

        $error = "Credenciales inválidas";
        $this->view("auth/login", compact('error'));
    }

    public function logout()
    {
        session_destroy();
        $this->redirect("/login");
    }
}
