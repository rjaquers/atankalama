<?php
/**
 * UsuariosController — gestión de usuarios (solo Administrador)
 * PHP 7.4–8.2 compatible
 */
class UsuariosController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::hotelAdmin();
        $usuarios = (new ChatUserModel())->getAll();
        $title    = 'Usuarios';
        $this->view('usuarios/index', compact('usuarios', 'title'));
    }

    public function crear(): void
    {
        AuthMiddleware::hotelAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();

            $email = trim($_POST['email'] ?? '');
            if ((new ChatUserModel())->emailExists($email)) {
                $error = 'Ya existe un usuario con ese correo electrónico.';
                $areas = (new AreaModel())->getAll(true);
                $roles = (new RolModel())->getAll();
                $title = 'Nuevo Usuario';
                $this->view('usuarios/form', compact('areas', 'roles', 'title', 'error'));
                return;
            }

            (new ChatUserModel())->create($_POST);
            $this->redirect('/usuarios');
        }

        $areas = (new AreaModel())->getAll(true);
        $roles = (new RolModel())->getAll();
        $title = 'Nuevo Usuario';
        $this->view('usuarios/form', compact('areas', 'roles', 'title'));
    }

    public function editar(string $id): void
    {
        AuthMiddleware::hotelAdmin();

        $usuario = (new ChatUserModel())->getById((int)$id);
        if ($usuario === null) {
            $this->redirect('/usuarios');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            (new ChatUserModel())->update((int)$id, $_POST);

            // Sincronizar permisos de apps del hotel (chk_usuario_apps)
            $appIds  = array_map('intval', $_POST['hotel_apps'] ?? []);
            $perfil  = ($usuario['rol_nombre'] ?? '') === 'Administrador' ? 'Administrador' : 'Operador';
            (new HotelAppModel())->sincronizarAcceso(
                $usuario['email'],
                $usuario['nombre'],
                $perfil,
                $appIds
            );

            $this->redirect('/usuarios');
        }

        $areas     = (new AreaModel())->getAll(true);
        $roles     = (new RolModel())->getAll();
        $appsHotel = (new HotelAppModel())->getAppsConAcceso($usuario['email']);
        $title     = 'Editar Usuario';
        $this->view('usuarios/form', compact('usuario', 'areas', 'roles', 'appsHotel', 'title'));
    }

    public function toggleEstado(string $id): void
    {
        AuthMiddleware::hotelAdmin();
        csrf_verify();

        $user = (new ChatUserModel())->getById((int)$id);
        if ($user === null) {
            $this->redirect('/usuarios');
        }

        (new ChatUserModel())->update((int)$id, ['estado' => $user['estado'] ? 0 : 1]);
        $this->redirect('/usuarios');
    }

    public function porArea(): void
    {
        AuthMiddleware::hotelAdmin();
        $areaId   = (int)($_GET['id'] ?? 0);
        $usuarios = (new ChatUserModel())->getByArea($areaId);
        $this->json(['ok' => true, 'usuarios' => $usuarios]);
    }
}
