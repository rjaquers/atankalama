<?php

class AccesoUsuarioController
{
    public function logArchivo(): void
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/shared/AccesoLog.php';

        $filtroApp   = trim($_GET['app']   ?? '');
        $filtroEmail = trim($_GET['email'] ?? '');
        $filtroFecha = trim($_GET['fecha'] ?? '');

        $registros = AccesoLog::leer(
            2000,
            $filtroApp   ?: null,
            $filtroEmail ?: null,
            $filtroFecha ?: null
        );
        $apps = AccesoLog::apps();

        include __DIR__ . '/../views/acceso_log_archivo.php';
    }


    public function list()
    {
        $model       = new AccesoUsuario();
        $usuarios    = $model->listar();
        $emailActual = AccesoBootstrap::email();
        include __DIR__ . '/../views/acceso_usuarios_list.php';
    }

    public function toggleNovedades(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            header('Location: index.php?route=acceso/usuarios/list');
            exit;
        }
        (new AccesoUsuario())->toggleRecibeNovedades($id);
        header('Location: index.php?route=acceso/usuarios/list');
        exit;
    }

    public function log()
    {
        $email    = trim($_GET['email'] ?? '');
        $model    = new AccesoUsuario();
        $registros = $model->listarLog($email ?: null);
        $filtroEmail = $email;
        include __DIR__ . '/../views/acceso_usuarios_log.php';
    }

    public function verComo()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header('Location: index.php?route=acceso/usuarios/list'); exit; }

        $model   = new AccesoUsuario();
        $usuario = $model->buscar($id);
        if (!$usuario) { header('Location: index.php?route=acceso/usuarios/list'); exit; }

        // Guardar el admin original si no estamos ya en modo "ver como"
        if (empty($_SESSION['admin_impersonator_email'])) {
            $_SESSION['admin_impersonator_email'] = $_SESSION['adm_admin_email'];
        }

        // Configurar sesiones para el portal y todas las apps
        $email  = $usuario['email'];
        $expira = time() + 4 * 3600;

        // Sesión Admin (esta app)
        $_SESSION['adm_admin_email']   = $email;
        $_SESSION['adm_admin_expires'] = $expira;

        // Sesión Portal
        $_SESSION['portal_email']   = $email;
        $_SESSION['portal_expires'] = $expira;

        // Sesión de cada app
        $apps = $model->listarAppsUsuario($id);
        foreach ($apps as $app) {
            if ($app['tiene_acceso'] && !empty($app['session_prefix'])) {
                $prefix = $app['session_prefix'];
                $_SESSION["{$prefix}_admin_email"]   = $email;
                $_SESSION["{$prefix}_admin_expires"] = $expira;
            }
        }

        $_SESSION['flash_ok'] = "Ahora estás viendo el sistema como: {$email}";
        header('Location: /login/index.php');
        exit;
    }

    public function salirVerComo()
    {
        // Limpiar absolutamente todas las variables de sesión
        $_SESSION = [];

        // Borrar la cookie de sesión si existe
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destruir la sesión en el servidor
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        // Redirigir al formulario de login principal
        header('Location: /login/index.php?route=auth/login');
        exit;
    }

    public function resetValidado()
    {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            header('Location: index.php?route=acceso/usuarios/list');
            exit;
        }
        (new AccesoUsuario())->resetValidado($id);
        $_SESSION['flash_ok'] = 'Verificación reseteada. El usuario deberá validar su correo nuevamente.';
        header("Location: index.php?route=acceso/usuarios/edit&id={$id}");
        exit;
    }

    public function cerrarSesion()
    {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            header('Location: index.php?route=acceso/usuarios/list');
            exit;
        }

        $model   = new AccesoUsuario();
        $usuario = $model->buscar($id);

        // No permitir cerrarse la sesión a uno mismo desde aquí
        $emailActual = AccesoBootstrap::email();
        if ($usuario && $usuario['email'] === $emailActual) {
            $_SESSION['flash_error'] = 'No puedes cerrar tu propia sesión desde aquí.';
            header('Location: index.php?route=acceso/usuarios/list');
            exit;
        }

        $model->forzarLogout($id);
        $_SESSION['flash_ok'] = 'Sesión cerrada. El usuario será desconectado en su próxima acción.';
        header('Location: index.php?route=acceso/usuarios/list');
        exit;
    }

    public function create()
    {
        $perfiles = (new AccesoUsuario())->listarPerfiles();
        include __DIR__ . '/../views/acceso_usuarios_form.php';
    }

    public function store()
    {
        $email     = trim($_POST['email']     ?? '');
        $nombre    = trim($_POST['nombre']    ?? '');
        $apellido  = trim($_POST['apellido']  ?? '');
        $perfil    = $_POST['perfil'] ?? 'Operador';
        $telefono  = trim($_POST['telefono']  ?? '');
        $rut       = trim($_POST['rut']       ?? '');

        $model = new AccesoUsuario();

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Correo electrónico inválido.';
            header('Location: index.php?route=acceso/usuarios/create');
            exit;
        }

        if ($model->emailExiste($email)) {
            $_SESSION['flash_error'] = 'El correo ya está registrado.';
            header('Location: index.php?route=acceso/usuarios/create');
            exit;
        }

        $model->guardar($email, $nombre, $apellido, $perfil, $telefono, $rut);
        $_SESSION['flash_ok'] = 'Usuario creado correctamente.';
        header('Location: index.php?route=acceso/usuarios/list');
        exit;
    }

    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header('Location: index.php?route=acceso/usuarios/list'); exit; }

        $model   = new AccesoUsuario();
        $usuario = $model->buscar($id);
        if (!$usuario) { header('Location: index.php?route=acceso/usuarios/list'); exit; }

        $perfiles = $model->listarPerfiles();
        include __DIR__ . '/../views/acceso_usuarios_edit.php';
    }

    public function update()
    {
        $id       = (int)($_POST['id'] ?? 0);
        $email    = trim($_POST['email']     ?? '');
        $nombre   = trim($_POST['nombre']    ?? '');
        $apellido = trim($_POST['apellido']  ?? '');
        $perfil   = $_POST['perfil']  ?? 'Operador';
        $estado   = $_POST['estado']  ?? 'activo';
        $telefono = trim($_POST['telefono']  ?? '');
        $rut      = trim($_POST['rut']       ?? '');

        $model = new AccesoUsuario();

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Correo electrónico inválido.';
            header("Location: index.php?route=acceso/usuarios/edit&id=$id");
            exit;
        }

        if ($model->emailExiste($email, $id)) {
            $_SESSION['flash_error'] = 'El correo ya está usado por otro usuario.';
            header("Location: index.php?route=acceso/usuarios/edit&id=$id");
            exit;
        }

        $model->actualizar($id, $email, $nombre, $apellido, $perfil, $estado, $telefono, $rut);
        $_SESSION['flash_ok'] = 'Usuario actualizado correctamente.';
        header('Location: index.php?route=acceso/usuarios/list');
        exit;
    }

    public function permisos()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header('Location: index.php?route=acceso/usuarios/list'); exit; }

        $model   = new AccesoUsuario();
        $usuario = $model->buscar($id);
        if (!$usuario) { header('Location: index.php?route=acceso/usuarios/list'); exit; }

        $rolModel = new AccesoRol();
        $apps     = $model->listarAppsUsuario($id);

        // Para cada app, cargar sus roles disponibles
        $rolesPorApp = [];
        foreach ($apps as $app) {
            $rolesPorApp[$app['id']] = $rolModel->listarPorApp((int)$app['id']);
        }

        include __DIR__ . '/../views/acceso_usuarios_permisos.php';
    }

    public function guardarPermisos()
    {
        $id      = (int)($_POST['id'] ?? 0);
        $appIds  = $_POST['apps']  ?? [];
        $roles   = $_POST['roles'] ?? [];

        if (!$id) { header('Location: index.php?route=acceso/usuarios/list'); exit; }

        (new AccesoUsuario())->sincronizarAppsYRoles($id, $appIds, $roles);
        $_SESSION['flash_ok'] = 'Permisos actualizados correctamente.';
        header("Location: index.php?route=acceso/usuarios/permisos&id=$id");
        exit;
    }
}
