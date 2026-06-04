<?php
/**
 * PerfilController — perfil de usuario (todos los autenticados)
 * PHP 7.4–8.2 compatible
 */
class PerfilController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::check();

        $usuario = (new ChatUserModel())->getById((int)$_SESSION['user_id']);
        $areas   = (new AreaModel())->getAll(true);

        $error = $_SESSION['flash_error'] ?? null;
        $msg   = $_SESSION['flash_msg']   ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_msg']);

        $title = 'Mi Perfil';
        $this->view('perfil/index', compact('usuario', 'areas', 'error', 'msg', 'title'));
    }

    public function actualizar(): void
    {
        AuthMiddleware::check();
        csrf_verify();

        $nombre = trim($_POST['nombre'] ?? '');
        $areaId = !empty($_POST['area_id']) ? (int)$_POST['area_id'] : null;

        if ($nombre === '') {
            $_SESSION['flash_error'] = 'El nombre es requerido.';
            $this->redirect('/perfil');
        }

        (new ChatUserModel())->updatePerfil((int)$_SESSION['user_id'], [
            'nombre'  => $nombre,
            'area_id' => $areaId,
        ]);

        $_SESSION['user_nombre']  = $nombre;
        $_SESSION['user_area_id'] = $areaId;

        if ($areaId) {
            $area = (new AreaModel())->getById($areaId);
            $_SESSION['user_area'] = $area ? $area['nombre'] : '';
        } else {
            $_SESSION['user_area'] = '';
        }

        $_SESSION['flash_msg'] = 'Perfil actualizado correctamente.';
        $this->redirect('/perfil');
    }

    public function subirFoto(): void
    {
        AuthMiddleware::check();
        csrf_verify();

        if (empty($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            $this->redirect('/perfil');
        }

        $ruta = (new ImageService())->saveAsWebp($_FILES['foto'], 'perfiles');

        if (!$ruta) {
            $_SESSION['flash_error'] = 'Error al procesar la imagen.';
            $this->redirect('/perfil');
        }

        (new ChatUserModel())->updateFoto((int)$_SESSION['user_id'], $ruta);
        $_SESSION['user_foto'] = $ruta;
        $_SESSION['flash_msg'] = 'Foto actualizada correctamente.';
        $this->redirect('/perfil');
    }
}
