<?php
namespace App\Controllers;

class UserController extends \App\Core\Controller
{
    public function index()
    {
        $userModel = new \App\Models\User();
        $users = $userModel->all();

        $this->render('usuarios/index', [
            'users' => $users,
            'active' => 'usuarios'
        ]);
    }

    public function store()
    {
        $email = $_POST['email'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with($email, ALLOWED_DOMAIN)) {
            return $this->json(['error' => 'Correo corporativo no válido'], 400);
        }

        $userModel = new \App\Models\User();
        if ($userModel->findByEmail($email)) {
            return $this->json(['error' => 'Este usuario ya existe'], 400);
        }

        $perfil = $_POST['perfil'] ?? 'Operador';

        if ($userModel->create($email, $perfil)) {
            \App\Services\EmailService::sendWelcomeEmail($email);
            \App\Core\Logger::info('USER_MGMT', 'Usuario registrado manualmente', ['email' => $email], \AccesoBootstrap::email());
            return $this->json(['message' => 'Usuario registrado correctamente e invitación enviada']);
        }

        return $this->json(['error' => 'Error al crear usuario'], 500);
    }

    public function update()
    {
        $id = $_POST['id'] ?? null;
        $perfil = $_POST['perfil'] ?? null;

        if (!$id || !in_array($perfil, ['Operador', 'Administrador'])) {
            return $this->json(['error' => 'Datos no válidos'], 400);
        }

        $userModel = new \App\Models\User();

        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT email FROM " . DB_PREFIX . "usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $targetEmail = $stmt->fetchColumn();

        if ($targetEmail === \AccesoBootstrap::email()) {
            return $this->json(['error' => 'No puedes modificar tu propio perfil'], 400);
        }

        if ($userModel->updatePerfil($id, $perfil)) {
            \App\Core\Logger::info('USER_MGMT', 'Perfil de usuario actualizado', ['email' => $targetEmail, 'perfil' => $perfil], \AccesoBootstrap::email());
            return $this->json(['message' => 'Usuario actualizado correctamente']);
        }

        return $this->json(['error' => 'Error al actualizar usuario'], 500);
    }

    public function delete()
    {
        $id = $_POST['id'] ?? null;
        if (!$id)
            return $this->json(['error' => 'ID no proporcionado'], 400);

        $userModel = new \App\Models\User();

        // Evitar que el usuario se borre a sí mismo
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT email FROM " . DB_PREFIX . "usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $targetEmail = $stmt->fetchColumn();

        if ($targetEmail === \AccesoBootstrap::email()) {
            return $this->json(['error' => 'No puedes eliminar tu propio usuario'], 400);
        }

        if ($userModel->delete($id)) {
            \App\Core\Logger::warning('USER_MGMT', 'Usuario eliminado', ['email' => $targetEmail], \AccesoBootstrap::email());
            return $this->json(['message' => 'Usuario eliminado correctamente']);
        }

        return $this->json(['error' => 'Error al eliminar usuario'], 500);
    }
}
