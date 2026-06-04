<?php
/**
 * @deprecated Este controlador ya no se usa.
 * La autenticación la gestiona AccesoBootstrap (/shared/AccesoBootstrap.php).
 * Las rutas /login, /logout, etc. fueron eliminadas del router.
 */
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Token;
use App\Models\User;
use App\Core\Logger;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (isset($_SESSION['user_email'])) {
            $this->redirect('/dashboard');
        }
        $this->render('login', [], 'none');
    }

    public function requestOTP()
    {
        $email = $_POST['email'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with($email, ALLOWED_DOMAIN)) {
            Logger::security('AUTH', 'Intento de login con correo no válido', ['email' => $email]);
            return $this->json(['error' => 'Correo corporativo no válido'], 400);
        }

        // Verificar si el usuario existe
        $userModel = new User();
        $user = $userModel->findByEmail($email);
        if (!$user) {
            Logger::security('AUTH', 'Intento de login con correo inexistente', ['email' => $email]);
            return $this->json(['error' => 'El correo no está registrado en el sistema'], 403);
        }

        $otp = sprintf("%06d", mt_rand(0, 999999));
        $tokenModel = new Token();

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        if ($tokenModel->create($email, $otp, $ip, $ua)) {
            \App\Services\EmailService::sendOTP($email, $otp);
            Logger::info('AUTH', 'OTP generado y enviado', ['email' => $email]);

            return $this->json([
                'message' => 'Código de acceso enviado a su correo corporativo'
            ]);
        }

        return $this->json(['error' => 'Error al generar el código'], 500);
    }

    public function verifyOTP()
    {
        $email = $_POST['email'] ?? '';
        $otp = $_POST['otp'] ?? '';

        $tokenModel = new Token();
        $token = $tokenModel->findValid($email, $otp);

        if (!$token) {
            Logger::security('AUTH', 'OTP inválido o expirado', ['email' => $email]);
            return $this->json(['error' => 'Código inválido o expirado'], 401);
        }

        // Actualizar último login
        $userModel = new \App\Models\User();
        $user = $userModel->findByEmail($email);
        $userModel->updateLastLogin($email);

        $tokenModel->markAsUsed($token['id']);

        session_regenerate_id(true);
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = $user['perfil'] ?? 'Operador';

        Logger::info('AUTH', 'Sesión iniciada', ['email' => $email, 'role' => $_SESSION['user_role']]);
        return $this->json(['message' => 'Login exitoso', 'redirect' => '/dashboard']);
    }

    public function logout()
    {
        $email = $_SESSION['user_email'] ?? 'unknown';
        Logger::info('AUTH', 'Sesión cerrada', ['email' => $email]);
        session_destroy();
        $this->redirect('/login');
    }
}
