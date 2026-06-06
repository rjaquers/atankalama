<?php
/**
 * UsuariosController - Atankalama Empresas
 */
class UsuariosController extends Controller
{
    public function index()
    {
        $user = AuthService::user();
        $model = new UserModel();
        $usuarios = $model->getByCompany($user['company_id']);

        $this->view('usuarios/index', [
            'title' => 'Gestión de Usuarios',
            'user' => $user,
            'usuarios' => $usuarios,
            'success' => $_GET['success'] ?? null,
            'error' => $_GET['error'] ?? null
        ]);
    }

    public function create()
    {
        $user = AuthService::user();
        $this->view('usuarios/create', [
            'title' => 'Nuevo Usuario',
            'user' => $user
        ]);
    }

    public function store()
    {
        $currentUser = AuthService::user();
        $model = new UserModel();

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'visor';

        if (empty($name) || empty($email) || empty($password)) {
            header("Location: " . BASE_URL . "usuarios/create?error=Todos los campos son obligatorios");
            exit;
        }

        if ($model->emailExists($email)) {
            header("Location: " . BASE_URL . "usuarios/create?error=El correo ya está registrado");
            exit;
        }

        $data = [
            'company_id' => $currentUser['company_id'],
            'email' => $email,
            'password' => $password,
            'name' => $name,
            'role' => $role
        ];

        if ($model->create($data)) {
            header("Location: " . BASE_URL . "usuarios?success=Usuario creado correctamente");
        } else {
            header("Location: " . BASE_URL . "usuarios/create?error=Error al crear el usuario");
        }
        exit;
    }

    public function edit($id)
    {
        $user = AuthService::user();
        $model = new UserModel();
        $usuario = $model->getById($id, $user['company_id']);

        if (!$usuario) {
            header("Location: " . BASE_URL . "usuarios?error=Usuario no encontrado");
            exit;
        }

        $this->view('usuarios/edit', [
            'title' => 'Editar Usuario',
            'user' => $user,
            'usuario' => $usuario
        ]);
    }

    public function update($id)
    {
        $user = AuthService::user();
        $model = new UserModel();
        
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'visor';
        $status = isset($_POST['status']) ? 1 : 0;

        if (empty($name) || empty($email)) {
            header("Location: " . BASE_URL . "usuarios/edit/$id?error=Nombre y Email son obligatorios");
            exit;
        }

        if ($model->emailExists($email, $id)) {
            header("Location: " . BASE_URL . "usuarios/edit/$id?error=El correo ya está en uso por otro usuario");
            exit;
        }

        $data = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'status' => $status
        ];

        if ($model->update($id, $data)) {
            header("Location: " . BASE_URL . "usuarios?success=Usuario actualizado correctamente");
        } else {
            header("Location: " . BASE_URL . "usuarios/edit/$id?error=Error al actualizar");
        }
        exit;
    }

    public function delete($id)
    {
        $user = AuthService::user();
        $model = new UserModel();

        // Evitar que un usuario se borre a sí mismo
        if ($id == $user['id']) {
            header("Location: " . BASE_URL . "usuarios?error=No puedes eliminar tu propio usuario");
            exit;
        }

        if ($model->softDelete($id, $user['company_id'])) {
            header("Location: " . BASE_URL . "usuarios?success=Usuario eliminado correctamente");
        } else {
            header("Location: " . BASE_URL . "usuarios?error=Error al eliminar el usuario");
        }
        exit;
    }
}
