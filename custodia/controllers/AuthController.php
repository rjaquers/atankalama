<?php
declare(strict_types=1);

class AuthController
{
    /** GET /auth/login — muestra formulario de correo */
    public function loginForm(): void
    {
        $error = $_SESSION['auth_error'] ?? null;
        unset($_SESSION['auth_error']);
        include __DIR__ . '/../views/auth/login.php';
    }

    /** POST /auth/login — valida correo en nov_recepcionistas y envía OTP */
    public function loginPost(): void
    {
        $email = strtolower(trim($_POST['email'] ?? ''));

        if ($email === '') {
            $_SESSION['auth_error'] = 'Ingresa tu correo electrónico.';
            redirect('/auth/login');
        }

        $db   = db();
        $stmt = $db->prepare(
            'SELECT id, nombre, correo FROM nov_recepcionistas
             WHERE LOWER(correo) = ? AND activo = 1 LIMIT 1'
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Respuesta genérica: no revelar si el correo existe o no
        if (!$row) {
            $_SESSION['auth_error'] = 'Si el correo es válido, recibirás un código en breve.';
            redirect('/auth/login');
        }

        $recepId = (int)$row['id'];
        $nombre  = $row['nombre'];
        $correo  = $row['correo'];

        $_SESSION['auth_step']     = 'otp_pending';
        $_SESSION['auth_recep_id'] = $recepId;
        $_SESSION['auth_email']    = $correo;
        $_SESSION['auth_nombre']   = $nombre;

        auth_send_otp($recepId, $correo, $nombre);

        redirect('/auth/otp');
    }

    /** GET /auth/otp — muestra formulario de código */
    public function otpForm(): void
    {
        if (($_SESSION['auth_step'] ?? '') !== 'otp_pending') {
            redirect('/auth/login');
        }
        $error = $_SESSION['auth_error'] ?? null;
        unset($_SESSION['auth_error']);
        include __DIR__ . '/../views/auth/otp.php';
    }

    /** POST /auth/otp — valida el código de 6 dígitos */
    public function otpPost(): void
    {
        if (($_SESSION['auth_step'] ?? '') !== 'otp_pending') {
            redirect('/auth/login');
        }

        $recepId = (int)($_SESSION['auth_recep_id'] ?? 0);
        $code    = trim($_POST['code'] ?? '');

        if ($recepId === 0 || $code === '') {
            redirect('/auth/otp');
        }

        $db = db();

        // Incrementar intento antes de validar (previene race condition)
        $db->query(
            "UPDATE coc_otp
             SET attempts = attempts + 1
             WHERE recep_id = {$recepId}
               AND used = 0
               AND expires_at > NOW()
             ORDER BY id DESC LIMIT 1"
        );

        $stmt = $db->prepare(
            'SELECT id, attempts FROM coc_otp
             WHERE recep_id = ? AND code = ? AND used = 0
               AND expires_at > NOW()
             ORDER BY id DESC LIMIT 1'
        );
        $stmt->bind_param('is', $recepId, $code);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            $_SESSION['auth_error'] = 'Código incorrecto o expirado. Intenta de nuevo.';
            redirect('/auth/otp');
        }

        $otpId    = (int)$row['id'];
        $attempts = (int)$row['attempts'];

        if ($attempts > 5) {
            // Demasiados intentos: invalidar código y forzar re-login
            $db->query("UPDATE coc_otp SET used=1 WHERE id={$otpId}");
            auth_logout();
            $_SESSION['auth_error'] = 'Demasiados intentos. Solicita un nuevo código.';
            redirect('/auth/login');
        }

        // Código válido: marcar como usado
        $db->query("UPDATE coc_otp SET used=1 WHERE id={$otpId}");

        // Regenerar ID de sesión (previene session fixation)
        session_regenerate_id(true);

        $_SESSION['auth_step'] = 'authenticated';
        unset($_SESSION['auth_email']); // ya no se necesita

        $intended = $_SESSION['auth_intended'] ?? '/colaciones/lotes';
        unset($_SESSION['auth_intended']);

        redirect($intended);
    }

    /** GET /auth/otp/reenviar — reenvía el código OTP */
    public function otpReenviar(): void
    {
        if (($_SESSION['auth_step'] ?? '') === 'otp_pending') {
            $recepId = (int)($_SESSION['auth_recep_id'] ?? 0);
            $email   = $_SESSION['auth_email']  ?? '';
            $nombre  = $_SESSION['auth_nombre'] ?? '';
            if ($recepId > 0 && $email !== '') {
                auth_send_otp($recepId, $email, $nombre);
            }
        }
        redirect('/auth/otp');
    }

    /** GET /auth/logout */
    public function logout(): void
    {
        auth_logout();
        redirect('/auth/login');
    }
}
