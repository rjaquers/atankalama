<?php

require_once 'models/User.php';

class AuthController {
    private $user_model;
    
    public function __construct() {
        $this->user_model = new User();
    }
    
    public function showLogin() {
        if (isLoggedIn()) {
            redirect('dashboard');
        }
        include 'views/auth/login.php';
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = sanitize($_POST['username']);
            $password = $_POST['password'];
            
            $user = $this->user_model->login($username, $password);
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                if ($this->isMobileDevice()) {
                    redirect('voice_stock');
                } else {
                    redirect('dashboard');
                }
//                redirect('dashboard');
            } else {
                $_SESSION['error'] = 'Usuario o contraseña incorrectos';
                redirect('login');
            }
        }
    }

    /**
     * Detecta si el usuario está usando un dispositivo móvil.
     *
     * @return bool Devuelve true si parece ser móvil, false si es escritorio.
     */
    private function isMobileDevice(): bool
    {
        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

        return preg_match('/android|iphone|ipad|ipod|blackberry|windows phone|mobile/i', $userAgent) === 1;
    } // Fin función isMobileDevice


    public function logout() {
        session_destroy();
        redirect('login');
    }

    /**
     * Recuperar contraseña - envía enlace de restablecimiento por correo.
     */
    public function recover()
    {
       // require_once 'Models/User.php';
        $userModel = new User();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);

            // Verificar si existe el usuario
            $user = $userModel->getUserByEmail($email);
            if (! $user) {
                $_SESSION['error'] = 'No se encontró ningún usuario con ese correo.';
                redirect('login&action=recover');

                return;
            }

            // Generar token único
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Guardar token en la base (requiere campo "reset_token" y "reset_expire")
            $userModel->setResetToken($user['id'], $token, $expiry);

            // Enviar correo con PHPMailer
            require 'vendor/autoload.php';
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            require 'vendor/autoload.php';
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'mail.atankalama.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'sistema@atankalama.com';
                $mail->Password = 'nmgPw-a=^uhS';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                // 🔧 AÑADIR ESTAS DOS LÍNEAS
                $mail->CharSet = 'UTF-8';
                $mail->Encoding = 'base64';

                $mail->setFrom('sistema@atankalama.com', 'Atankalama Inventario');
                $mail->addAddress($email, $user['full_name']);

                $mail->isHTML(true);
                $mail->Subject = 'Recuperación de Contraseña - Atankalama Inventario';

                $resetLink = BASE_URL.'index.php?page=login&action=reset&token='.$token;

                $mail->Body = "
                    <h3>Hola {$user['full_name']},</h3>
                    <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para continuar:</p>
                    <p><a href='{$resetLink}' style='background:#667eea;color:white;padding:10px 20px;border-radius:5px;text-decoration:none;'>Restablecer contraseña</a></p>
                    <p>Este enlace expirará en 1 hora.</p>
                    <br><small>Atentamente,<br>Equipo Atankalama</small>
                ";

                $mail->send();
                $_SESSION['success'] = 'Se ha enviado un enlace de recuperación a tu correo.';
            } catch (Exception $e) {
                $_SESSION['error'] = 'Error al enviar correo: '.$mail->ErrorInfo;
                authLog("Error SMTP al enviar correo a $email: " . $mail->ErrorInfo, 'ERROR');

            }


            //registra intentos fallidos de recuperación
            if (!$user) {
                authLog("Recuperación fallida: correo no encontrado ($email)", 'WARNING');
                $_SESSION['error'] = 'No se encontró ningún usuario con ese correo.';
                redirect('login&action=recover');
                return;
            }

            authLog("Solicitud de recuperación enviada a $email", 'INFO');



            redirect('login&action=recover');
        } else {
            require 'views/auth/recover.php';
        }
    }


    /**
     * Restablece la contraseña del usuario (paso 2)
     */
    public function reset()
    {
       // require_once 'Models/User.php';
        $userModel = new User();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $token = $_GET['token'] ?? '';
            $user = $userModel->getUserByToken($token);

            if (!$user) {
                $_SESSION['error'] = 'El enlace no es válido o ya ha sido usado.';
                redirect('login');
                return;
            }

            if (strtotime($user['reset_expire']) < time()) {
                $_SESSION['error'] = 'El enlace ha expirado. Solicite uno nuevo.';
                redirect('login&action=recover');
                return;
            }

            require 'views/auth/reset.php';

        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_GET['token'] ?? '';
            $password = trim($_POST['password']);
            $confirm = trim($_POST['confirm']);

            $user = $userModel->getUserByToken($token);
            if (!$user) {
                $_SESSION['error'] = 'El enlace no es válido.';
                redirect('login');
                return;
            }

            if ($password !== $confirm) {
                $_SESSION['error'] = 'Las contraseñas no coinciden.';
                redirect('login&action=reset&token=' . $token);
                return;
            }

            // Actualizar contraseña
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $userModel->updatePassword($user['id'], $hash);

            $_SESSION['success'] = 'Contraseña actualizada correctamente. Ahora puedes iniciar sesión.';
            redirect('login');
        }

        if (!$user) {
            authLog("Intento de reset con token inválido: $token", 'WARNING');
            $_SESSION['error'] = 'El enlace no es válido.';
            redirect('login');
            return;
        }

        if ($password !== $confirm) {
            authLog("Contraseñas no coinciden durante reset para usuario ID {$user['id']}", 'WARNING');
            $_SESSION['error'] = 'Las contraseñas no coinciden.';
            redirect('login&action=reset&token=' . $token);
            return;
        }

// Después de actualizar contraseña
        authLog("Contraseña restablecida correctamente para usuario ID {$user['id']}", 'INFO');



    }


}

?>