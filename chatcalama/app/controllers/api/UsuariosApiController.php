<?php
/**
 * UsuariosApiController — API REST de usuarios
 * PHP 7.4–8.2 compatible
 */
class UsuariosApiController extends Controller
{
    public function perfil(): void
    {
        $payload = AuthMiddleware::api();
        $usuario = (new ChatUserModel())->getById((int)$payload['uid']);

        if ($usuario === null) {
            $this->json(['ok' => false, 'msg' => 'Usuario no encontrado.'], 404);
        }

        unset($usuario['password_hash'], $usuario['otp_code'], $usuario['otp_expires']);
        $this->json(['ok' => true, 'usuario' => $usuario]);
    }

    public function actualizarPerfil(): void
    {
        $payload = AuthMiddleware::api();
        $data    = json_decode(file_get_contents('php://input'), true) ?? [];
        (new ChatUserModel())->updatePerfil((int)$payload['uid'], $data);
        $this->json(['ok' => true]);
    }

    public function subirFoto(): void
    {
        $payload = AuthMiddleware::api();

        if (empty($_FILES['foto'])) {
            $this->json(['ok' => false, 'msg' => 'No se recibió foto.'], 400);
        }

        $ruta = (new ImageService())->saveAsWebp($_FILES['foto'], 'perfiles');

        if (!$ruta) {
            $this->json(['ok' => false, 'msg' => 'Error al procesar la imagen.'], 500);
        }

        (new ChatUserModel())->updateFoto((int)$payload['uid'], $ruta);
        $this->json(['ok' => true, 'ruta' => $ruta]);
    }

    public function listar(): void
    {
        $payload  = AuthMiddleware::api();
        $usuarios = (new ChatUserModel())->getAll(true);

        foreach ($usuarios as &$u) {
            unset($u['otp_code'], $u['otp_expires'], $u['fcm_token']);
        }
        unset($u);

        $this->json(['ok' => true, 'usuarios' => $usuarios]);
    }

    public function fcmToken(): void
    {
        $payload = AuthMiddleware::api();
        $data    = json_decode(file_get_contents('php://input'), true) ?? [];
        $token   = trim($data['fcm_token'] ?? '');

        if ($token !== '') {
            (new ChatUserModel())->updateFcmToken((int)$payload['uid'], $token);
        }

        $this->json(['ok' => true]);
    }
}
