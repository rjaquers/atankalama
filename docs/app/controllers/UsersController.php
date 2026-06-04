<?php
/**
 * Controller de Usuarios.
 *
 * Gestiona la administración de usuarios del sistema.
 * Solo accesible por administradores.
 * Requiere permisos: users_view, users_manage.
 *
 * @package App\Controllers
 */
class UsersController extends Controller
{
    /**
     * Lista todos los usuarios.
     * Permiso requerido: users_view
     */
    public function index()
    {
        PermissionMiddleware::check('users_view');

        $model = new UserModel();
        $users = $model->getAll(true); // incluir inactivos

        $permModel = new PermissionModel();
        $roles = $permModel->getRoles();

        $this->view('users/index', compact('users', 'roles'));
    }
    // Fin de la función index()

    /**
     * Muestra formulario de creación.
     * Permiso requerido: users_manage
     */
    public function create()
    {
        PermissionMiddleware::check('users_manage');

        $user = [];
        $isEdit = false;

        $permModel = new PermissionModel();
        $roles = $permModel->getRoles();

        $this->view('users/form', compact('user', 'isEdit', 'roles'));
    }
    // Fin de la función create()

    /**
     * Guarda un nuevo usuario.
     * Permiso requerido: users_manage
     */
    public function store()
    {
        PermissionMiddleware::check('users_manage');
        csrf_verify();

        // ===============================
        // RECOGER Y VALIDAR DATOS
        // ===============================
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleId   = (int)($_POST['role_id'] ?? 0);

        if (empty($name) || empty($email) || empty($password) || $roleId === 0) {
            $_SESSION['flash_error'] = 'Todos los campos son obligatorios';
            $this->redirect('/users/create');
            return;
        }

        if (strlen($password) < 6) {
            $_SESSION['flash_error'] = 'La contraseña debe tener al menos 6 caracteres';
            $this->redirect('/users/create');
            return;
        }

        // ===============================
        // GUARDAR EN BASE DE DATOS
        // ===============================
        $model = new UserModel();
        $id = $model->create($name, $email, $password, $roleId);

        if ($id) {
            (new AuditModel())->add(AuthService::userId(), 'usuarios', 'crear', "Usuario creado: {$name} ({$email})");
            $_SESSION['flash_success'] = 'Usuario creado exitosamente';
            $this->redirect('/users');
        } else {
            $_SESSION['flash_error'] = 'Error al crear el usuario. Verifique que el email no esté duplicado.';
            $this->redirect('/users/create');
        }
    }
    // Fin de la función store()

    /**
     * Muestra formulario de edición.
     * Permiso requerido: users_manage
     *
     * @param int $id ID del usuario
     */
    public function edit($id)
    {
        PermissionMiddleware::check('users_manage');

        $model = new UserModel();
        $user = $model->getById((int)$id);

        if (!$user) {
            $_SESSION['flash_error'] = 'Usuario no encontrado';
            $this->redirect('/users');
            return;
        }

        $isEdit = true;
        $permModel = new PermissionModel();
        $roles = $permModel->getRoles();

        $this->view('users/form', compact('user', 'isEdit', 'roles'));
    }
    // Fin de la función edit()

    /**
     * Actualiza un usuario existente.
     * Permiso requerido: users_manage
     *
     * @param int $id ID del usuario
     */
    public function update($id)
    {
        PermissionMiddleware::check('users_manage');
        csrf_verify();

        $model = new UserModel();
        $user = $model->getById((int)$id);

        if (!$user) {
            $_SESSION['flash_error'] = 'Usuario no encontrado';
            $this->redirect('/users');
            return;
        }

        $name   = trim($_POST['name'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $roleId = (int)($_POST['role_id'] ?? 0);

        if (empty($name) || empty($email) || $roleId === 0) {
            $_SESSION['flash_error'] = 'Nombre, email y rol son obligatorios';
            $this->redirect('/users/edit/' . $id);
            return;
        }

        $model->update((int)$id, $name, $email, $roleId);

        // Actualizar contraseña solo si se proporcionó
        $password = $_POST['password'] ?? '';
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $_SESSION['flash_error'] = 'La contraseña debe tener al menos 6 caracteres';
                $this->redirect('/users/edit/' . $id);
                return;
            }
            $model->updatePassword((int)$id, $password);
        }

        (new AuditModel())->add(AuthService::userId(), 'usuarios', 'editar', "Usuario editado: {$name} (ID: {$id})");
        $_SESSION['flash_success'] = 'Usuario actualizado exitosamente';
        $this->redirect('/users');
    }
    // Fin de la función update()

    /**
     * Activa o desactiva un usuario.
     * Permiso requerido: users_manage
     *
     * @param int $id ID del usuario
     */
    public function toggle($id)
    {
        PermissionMiddleware::check('users_manage');
        csrf_verify();

        $model = new UserModel();
        $user = $model->getById((int)$id);

        if (!$user) {
            $_SESSION['flash_error'] = 'Usuario no encontrado';
            $this->redirect('/users');
            return;
        }

        // No permitir desactivarse a sí mismo
        if ((int)$id === AuthService::userId()) {
            $_SESSION['flash_error'] = 'No puede desactivar su propia cuenta';
            $this->redirect('/users');
            return;
        }

        $newStatus = (int)$user['status'] === 1 ? 0 : 1;
        $model->setStatus((int)$id, $newStatus);

        $action = $newStatus === 1 ? 'activado' : 'desactivado';
        (new AuditModel())->add(AuthService::userId(), 'usuarios', $action, "Usuario {$action}: {$user['name']} (ID: {$id})");
        $_SESSION['flash_success'] = "Usuario {$action} exitosamente";
        $this->redirect('/users');
    }
    // Fin de la función toggle()
}
