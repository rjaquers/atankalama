<?php
/**
 * AdminAuthController — OTP del hotel para acceso al panel de administración
 * Usa chk_usuarios / chk_login_tokens (tablas compartidas Hotel Atankalama)
 * Prefijo de sesión: 'chat'  →  $_SESSION['chat_admin_email'] / ['chat_admin_expires']
 */
class AdminAuthController extends Controller
{
    /** GET /adminAuth/requestForm — formulario de email */
    public function requestForm(): void
    {
        AuthMiddleware::check(); // debe estar logueado como usuario de chat

        $redirect = $_GET['redirect'] ?? '/usuarios';
        $error    = $_SESSION['hotel_auth_error'] ?? null;
        unset($_SESSION['hotel_auth_error']);

        $this->view('admin_auth/request', compact('redirect', 'error'));
    }

    /** POST /adminAuth/sendOtp — valida email en chk_* y envía OTP */
    public function sendOtp(): void
    {
        AuthMiddleware::check();
        csrf_verify();

        $email    = trim(strtolower($_POST['email'] ?? ''));
        $redirect = $_POST['redirect'] ?? '/usuarios';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['hotel_auth_error'] = 'Ingresa un correo electrónico válido.';
            $this->redirect('/adminAuth/requestForm?redirect=' . urlencode($redirect));
        }

        $otp  = new HotelOtpService();
        $user = $otp->buscarUsuarioAutorizado($email);

        if (!$user) {
            // No revelar si el email existe o no tiene acceso
            $_SESSION['hotel_auth_error'] = 'Correo no autorizado o sin acceso a esta sección.';
            $this->redirect('/adminAuth/requestForm?redirect=' . urlencode($redirect));
        }

        $enviado = $otp->generarYEnviar($email);

        if (!$enviado) {
            $_SESSION['hotel_auth_error'] = 'No se pudo enviar el correo. Intenta nuevamente.';
            $this->redirect('/adminAuth/requestForm?redirect=' . urlencode($redirect));
        }

        $_SESSION['hotel_otp_email']    = $email;
        $_SESSION['hotel_otp_redirect'] = $redirect;

        $this->redirect('/adminAuth/verifyForm');
    }

    /** GET /adminAuth/verifyForm — formulario de código OTP */
    public function verifyForm(): void
    {
        AuthMiddleware::check();

        if (empty($_SESSION['hotel_otp_email'])) {
            $this->redirect('/adminAuth/requestForm');
        }

        $email = $_SESSION['hotel_otp_email'];
        $error = $_SESSION['hotel_auth_error'] ?? null;
        unset($_SESSION['hotel_auth_error']);

        $this->view('admin_auth/verify', compact('email', 'error'));
    }

    /** POST /adminAuth/verifyCode — verifica código y establece sesión admin */
    public function verifyCode(): void
    {
        AuthMiddleware::check();
        csrf_verify();

        $email    = $_SESSION['hotel_otp_email']    ?? '';
        $redirect = $_SESSION['hotel_otp_redirect'] ?? '/usuarios';
        $code     = trim($_POST['code'] ?? '');

        if (!$email) {
            $this->redirect('/adminAuth/requestForm');
        }

        if (!preg_match('/^\d{6}$/', $code)) {
            $_SESSION['hotel_auth_error'] = 'El código debe ser de 6 dígitos numéricos.';
            $this->redirect('/adminAuth/verifyForm');
        }

        $otp   = new HotelOtpService();
        $valido = $otp->verificarCodigo($email, $code);

        if (!$valido) {
            $_SESSION['hotel_auth_error'] = 'Código incorrecto o expirado. Inténtalo de nuevo.';
            $this->redirect('/adminAuth/verifyForm');
        }

        // Autenticación exitosa — establecer sesión admin del hotel (4 horas)
        unset($_SESSION['hotel_otp_email'], $_SESSION['hotel_otp_redirect']);
        $_SESSION['chat_admin_email']   = $email;
        $_SESSION['chat_admin_expires'] = time() + (4 * 3600);

        $this->redirect($redirect);
    }

    /** GET /adminAuth/logout — cierra solo la sesión admin del hotel */
    public function logout(): void
    {
        unset($_SESSION['chat_admin_email'], $_SESSION['chat_admin_expires']);
        $this->redirect('/dashboard');
    }
}
