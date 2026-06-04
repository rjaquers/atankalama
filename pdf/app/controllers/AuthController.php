<?php
/**
 * AuthController — login via OTP del hotel (chk_usuarios)
 * PHP 7.4–8.2 compatible
 */
class AuthController extends Controller
{
    /** GET /login — formulario de correo */
    public function login(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('/chat');
        }
        $error = $_SESSION['flash_error'] ?? null;
        $msg   = $_SESSION['flash_msg']   ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_msg']);
        $this->view('auth/login', compact('error', 'msg'));
    }

    /** POST /auth/requestOtp — valida email en chk_usuarios y envía OTP */
    public function requestOtp(): void
    {
        csrf_verify();

        $email = trim(strtolower($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Ingresa un correo electrónico válido.';
            $this->redirect('/login');
        }

        $otp  = new HotelOtpService();
        $user = $otp->buscarUsuarioAutorizado($email);

        if (!$user) {
            // Mismo mensaje para email no registrado o sin acceso (no revelar qué falla)
            $_SESSION['flash_error'] = 'Correo no autorizado o sin acceso a esta plataforma.';
            $this->redirect('/login');
        }

        $enviado = $otp->generarYEnviar($email);

        if (!$enviado) {
            $_SESSION['flash_error'] = 'No se pudo enviar el código. Intenta nuevamente.';
            $this->redirect('/login');
        }

        $_SESSION['otp_email'] = $email;
        $_SESSION['flash_msg'] = 'Código enviado a ' . $email . '. Revisa tu correo.';
        $this->redirect('/auth/otp');
    }

    /** GET /auth/otp — formulario de código */
    public function otp(): void
    {
        if (empty($_SESSION['otp_email'])) {
            $this->redirect('/login');
        }
        $error = $_SESSION['flash_error'] ?? null;
        $msg   = $_SESSION['flash_msg']   ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_msg']);
        $email = $_SESSION['otp_email'];
        $this->view('auth/otp', compact('error', 'msg', 'email'));
    }

    /** POST /auth/verifyOtp — verifica código y establece sesión */
    public function verifyOtp(): void
    {
        csrf_verify();

        $email = $_SESSION['otp_email'] ?? '';
        if (!$email) {
            $this->redirect('/login');
        }

        $code = trim($_POST['otp'] ?? '');
        if (!preg_match('/^\d{6}$/', $code)) {
            $_SESSION['flash_error'] = 'El código debe ser de 6 dígitos numéricos.';
            $this->redirect('/auth/otp');
        }

        $otp = new HotelOtpService();
        if (!$otp->verificarCodigo($email, $code)) {
            $_SESSION['flash_error'] = 'Código incorrecto o expirado. Inténtalo de nuevo.';
            $this->redirect('/auth/otp');
        }

        // OTP válido — cargar datos desde chat_usuarios
        $userModel = new ChatUserModel();
        $user      = $userModel->getByEmail($email);

        if (!$user || !$user['estado']) {
            $_SESSION['flash_error'] = 'Tu cuenta no tiene acceso a esta plataforma.';
            unset($_SESSION['otp_email']);
            $this->redirect('/login');
        }

        unset($_SESSION['otp_email']);

        // Establecer sesión completa
        $_SESSION['user_id']      = (int)$user['id'];
        $_SESSION['user_nombre']  = $user['nombre'];
        $_SESSION['user_email']   = $user['email'];
        $_SESSION['user_rol']     = $user['rol_nombre'] ?? 'Operador';
        $_SESSION['user_rol_id']  = (int)($user['rol_id'] ?? 3);
        $_SESSION['user_area']    = $user['area_nombre'] ?? '';
        $_SESSION['user_area_id'] = (int)($user['area_id'] ?? 0);
        $_SESSION['user_foto']    = $user['foto_perfil'] ?? '';
        $_SESSION['user_es_jefe'] = (bool)($user['es_jefe'] ?? false);

        $userModel->updateLastAccess((int)$user['id']);

        $this->redirect('/chat');
    }

    /** GET /auth/logout */
    public function logout(): void
    {
        session_destroy();
        $this->redirect('/login');
    }

    // Compatibilidad con rutas antiguas
    public function authenticate(): void
    {
        $this->redirect('/login');
    }
}
