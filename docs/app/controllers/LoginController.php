<?php
/**
 * Controller de Login.
 *
 * Gestiona el acceso al sistema mediante OTP de 6 dígitos enviado por correo.
 * Flujo en 2 pasos:
 *   Paso 1 (/login)       → El usuario ingresa su email.
 *   Paso 2 (/login/verify) → El usuario ingresa el código OTP recibido.
 *
 * @package App\Controllers
 */
class LoginController extends Controller
{
    /**
     * Muestra el formulario de Paso 1: ingreso de email.
     * Ruta: /login o /login/index
     */
    public function index()
    {
        $error   = null;
        $success = $_GET['success'] ?? null;
        $this->view("auth/login", compact('error', 'success'));
    }

    /**
     * Procesa el email del Paso 1.
     * Si el usuario existe y está activo, genera y envía el OTP.
     * Ruta: /login/sendOtp
     */
    public function sendOtp()
    {
        csrf_verify();

        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Por favor ingresa un correo electrónico válido.";
            $this->view("auth/login", compact('error'));
            return;
        }

        $userModel = new UserModel();
        $user      = $userModel->getByEmail($email);

        // Si el usuario existe y está activo: generar y enviar OTP
        if ($user && (int)$user['status'] === 1) {
            $otpService = new OtpService();
            $otpService->sendOtp($user);
        }

        // Almacenar el email en sesión para el Paso 2
        $_SESSION['otp_email'] = $email;

        // Siempre redirigir al Paso 2 (no revelar si el email existe)
        $this->redirect("/login/verify");
    }
    // Fin de la función sendOtp()

    /**
     * Muestra el formulario de Paso 2: ingreso del código OTP.
     * Ruta: /login/verify
     */
    public function verify()
    {
        // Si no hay email en sesión, volver al Paso 1
        if (empty($_SESSION['otp_email'])) {
            $this->redirect("/login");
            return;
        }

        $email = $_SESSION['otp_email'];
        $error = null;
        $this->view("auth/verify_otp", compact('email', 'error'));
    }
    // Fin de la función verify()

    /**
     * Procesa el código OTP del Paso 2 y autentica al usuario.
     * Ruta: /login/authenticate
     */
    public function authenticate()
    {
        csrf_verify();

        $email = $_SESSION['otp_email'] ?? '';
        $code  = trim($_POST['otp_code'] ?? '');

        if (empty($email) || empty($code)) {
            $this->redirect("/login");
            return;
        }

        $otpService = new OtpService();
        if ($otpService->verifyAndLogin($email, $code)) {
            unset($_SESSION['otp_email']);
            $this->redirect("/dashboard");
        }

        $error = "Código inválido o expirado. Inténtalo de nuevo.";
        $this->view("auth/verify_otp", compact('email', 'error'));
    }
    // Fin de la función authenticate()

    /**
     * Reenvía un nuevo OTP al correo almacenado en sesión.
     * Ruta: /login/resendOtp
     */
    public function resendOtp()
    {
        $email = $_SESSION['otp_email'] ?? '';

        if (!empty($email)) {
            $userModel = new UserModel();
            $user      = $userModel->getByEmail($email);

            if ($user && (int)$user['status'] === 1) {
                $otpService = new OtpService();
                $otpService->sendOtp($user);
            }
        }

        $this->redirect("/login/verify");
    }
    // Fin de la función resendOtp()

    /**
     * Muestra el formulario para solicitar recuperación de contraseña.
     * Ruta: /login/forgot
     */
    public function forgot()
    {
        $this->view("auth/forgot");
    }

    /**
     * Procesa la solicitud de recuperación.
     * Genera un token y envía el correo si el usuario existe.
     * Ruta: /login/sendResetLink
     */
    public function sendResetLink()
    {
        csrf_verify();
        $email = trim($_POST['email'] ?? '');

        if (!empty($email)) {
            $userModel = new UserModel();
            $user      = $userModel->getByEmail($email);

            if ($user && (int)$user['status'] === 1) {
                $token   = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $userModel->setResetToken($email, $token, $expires);

                $mailService = new MailService();
                $resetLink   = BASE_URL . "/login/reset/" . $token;
                $subject     = "Recuperación de Contraseña - " . APP_NAME;
                $body        = "
                    <h2>Recuperación de Contraseña</h2>
                    <p>Has solicitado restablecer tu contraseña para el sistema " . APP_NAME . ".</p>
                    <p>Haz clic en el siguiente enlace para continuar:</p>
                    <p><a href='{$resetLink}'>{$resetLink}</a></p>
                    <p>Este enlace expirará en 1 hora.</p>
                    <p>Si no solicitaste esto, puedes ignorar este correo.</p>
                ";

                $mailService->send($email, $subject, $body);
            }
        }

        $success = "Si la cuenta existe, recibirá un correo con instrucciones en breve.";
        $this->view("auth/forgot", compact('success'));
    }

    /**
     * Muestra el formulario para cambiar la contraseña usando el token.
     * Ruta: /login/reset/{token}
     *
     * @param string $token
     */
    public function reset($token = null)
    {
        if (!$token) {
            $this->redirect("/login");
        }

        $userModel = new UserModel();
        $user      = $userModel->getUserByToken($token);

        if (!$user) {
            $error = "El enlace no es válido o ha expirado.";
            $this->view("auth/login", compact('error'));
            return;
        }

        $this->view("auth/reset", compact('token'));
    }

    /**
     * Procesa la actualización de la nueva contraseña.
     * Ruta: /login/updateNewPassword
     */
    public function updateNewPassword()
    {
        csrf_verify();
        $token   = $_POST['token'] ?? '';
        $pass    = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($pass) || $pass !== $confirm) {
            $error = "Las contraseñas no coinciden o están vacías.";
            $this->view("auth/reset", compact('token', 'error'));
            return;
        }

        $userModel = new UserModel();
        $user      = $userModel->getUserByToken($token);

        if (!$user) {
            $error = "El enlace no es válido o ha expirado.";
            $this->view("auth/login", compact('error'));
            return;
        }

        $userModel->updatePassword($user['id'], $pass);
        $userModel->clearResetToken($user['id']);

        $success = "Contraseña actualizada con éxito. Ya puedes iniciar sesión.";
        $this->redirect("/login?success=" . urlencode($success));
    }

    /**
     * Cierra la sesión activa.
     * Ruta: /login/logout
     */
    public function logout()
    {
        session_destroy();
        $this->redirect("/login");
    }
}
