<?php
/**
 * LoginController - Atankalama Empresas
 */
class LoginController extends Controller
{
    public function index()
    {
        if (AuthService::check()) {
            $this->redirect('dashboard');
        }
        $this->view('login/index');
    }

    public function authenticate()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (AuthService::login($email, $password)) {
            $this->redirect('dashboard');
        } else {
            $data = ['error' => 'Credenciales inválidas o cuenta desactivada'];
            $this->view('login/index', $data);
        }
    }

    public function logout()
    {
        AuthService::logout();
        $this->redirect('login');
    }
}
