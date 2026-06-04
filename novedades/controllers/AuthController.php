<?php

class AuthController
{
    /**
     * GET: muestra el formulario para ingresar el email.
     */
    public function requestForm()
    {
        $redirect = $_GET['redirect'] ?? 'recepcionistas/list';
        $error    = $_SESSION['auth_error'] ?? null;
        unset($_SESSION['auth_error']);

        include __DIR__ . '/../views/auth/otp_email.php';
    }

    /**
     * POST: recibe el email, valida autorización y envía el OTP.
     */
    public function sendOtp()
    {
        $email    = trim($_POST['email'] ?? '');
        $redirect = $_POST['redirect'] ?? 'recepcionistas/list';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['auth_error'] = 'Ingresa un correo válido.';
            header('Location: index.php?route=auth/request&redirect=' . urlencode($redirect));
            exit;
        }

        $otp  = new OtpService();
        $user = $otp->buscarUsuarioAutorizado($email);

        if (!$user) {
            // No revelar si el email existe o no
            $_SESSION['auth_error'] = 'Correo no autorizado o sin acceso a esta sección.';
            header('Location: index.php?route=auth/request&redirect=' . urlencode($redirect));
            exit;
        }

        $ip        = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $enviado = $otp->generarYEnviar($email, $ip, $userAgent);

        if (!$enviado) {
            $_SESSION['auth_error'] = 'No se pudo enviar el correo. Intenta nuevamente.';
            header('Location: index.php?route=auth/request&redirect=' . urlencode($redirect));
            exit;
        }

        $_SESSION['otp_pending_email']    = $email;
        $_SESSION['otp_pending_redirect'] = $redirect;

        header('Location: index.php?route=auth/verify');
        exit;
    }

    /**
     * GET: muestra el formulario para ingresar el código OTP.
     */
    public function verifyForm()
    {
        if (empty($_SESSION['otp_pending_email'])) {
            header('Location: index.php?route=auth/request');
            exit;
        }

        $email = $_SESSION['otp_pending_email'];
        $error = $_SESSION['auth_error'] ?? null;
        unset($_SESSION['auth_error']);

        include __DIR__ . '/../views/auth/otp_code.php';
    }

    /**
     * POST: valida el código OTP ingresado.
     */
    public function verifyCode()
    {
        $email    = $_SESSION['otp_pending_email'] ?? '';
        $redirect = $_SESSION['otp_pending_redirect'] ?? 'recepcionistas/list';
        $code     = trim($_POST['code'] ?? '');

        if (!$email) {
            header('Location: index.php?route=auth/request');
            exit;
        }

        $otp   = new OtpService();
        $valido = $otp->verificarCodigo($email, $code);

        if (!$valido) {
            $_SESSION['auth_error'] = 'Código incorrecto o expirado. Inténtalo de nuevo.';
            header('Location: index.php?route=auth/verify');
            exit;
        }

        // Autenticación exitosa
        unset($_SESSION['otp_pending_email'], $_SESSION['otp_pending_redirect']);
        $_SESSION['nov_admin_email']   = $email;
        $_SESSION['nov_admin_expires'] = time() + (4 * 3600); // 4 horas

        header('Location: index.php?route=' . urlencode($redirect));
        exit;
    }

    /**
     * Cierra la sesión del administrador.
     */
    public function logout()
    {
        unset($_SESSION['nov_admin_email'], $_SESSION['nov_admin_expires']);
        header('Location: index.php?route=novedades/form');
        exit;
    }
}
