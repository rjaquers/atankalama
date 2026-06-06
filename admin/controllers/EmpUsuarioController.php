<?php

class EmpUsuarioController
{
    private const ROLES_VALIDOS = ['admin', 'visor'];

    // ── Helpers privados ───────────────────────────────────────

    /** Valida que la empresa exista; si no, redirige con error. */
    private function resolverEmpresa(int $companyId): array
    {
        if (!$companyId) {
            $_SESSION['flash_error'] = 'Empresa no especificada.';
            header('Location: index.php?route=doc_companies/list');
            exit;
        }
        $model   = new EmpUsuario();
        $empresa = $model->empresa($companyId);
        if (!$empresa) {
            $_SESSION['flash_error'] = 'Empresa no encontrada.';
            header('Location: index.php?route=doc_companies/list');
            exit;
        }
        return $empresa;
    }

    private function urlLista(int $companyId): string
    {
        return "index.php?route=emp/usuarios/list&company={$companyId}";
    }

    // ── Acciones ───────────────────────────────────────────────

    public function list(): void
    {
        $companyId = (int) ($_GET['company'] ?? 0);
        $empresa   = $this->resolverEmpresa($companyId);

        $model    = new EmpUsuario();
        $usuarios = $model->listarPorEmpresa($companyId);

        include __DIR__ . '/../views/emp_usuarios_list.php';
    }

    public function create(): void
    {
        $companyId = (int) ($_GET['company'] ?? 0);
        $empresa   = $this->resolverEmpresa($companyId);

        include __DIR__ . '/../views/emp_usuarios_form.php';
    }

    public function store(): void
    {
        $companyId = (int) ($_POST['company_id'] ?? 0);
        $empresa   = $this->resolverEmpresa($companyId);

        $name     = trim($_POST['name']     ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $role     = $_POST['role'] ?? 'visor';
        $notes    = trim($_POST['notes']    ?? '') ?: null;

        $urlCreate = "index.php?route=emp/usuarios/create&company={$companyId}";

        if (empty($name) || empty($email) || empty($password)) {
            $_SESSION['flash_error'] = 'Todos los campos son obligatorios.';
            header("Location: {$urlCreate}");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Correo electrónico inválido.';
            header("Location: {$urlCreate}");
            exit;
        }

        if (!in_array($role, self::ROLES_VALIDOS, true)) {
            $role = 'visor';
        }

        $model = new EmpUsuario();

        if ($model->emailExiste($email)) {
            $_SESSION['flash_error'] = 'El correo ya está registrado en otra cuenta.';
            header("Location: {$urlCreate}");
            exit;
        }

        $model->crear([
            'company_id' => $companyId,
            'email'      => $email,
            'password'   => $password,
            'name'       => $name,
            'role'       => $role,
            'notes'      => $notes,
        ]);

        $_SESSION['flash_ok'] = "Usuario <strong>" . htmlspecialchars($name) . "</strong> creado correctamente.";
        header("Location: " . $this->urlLista($companyId));
        exit;
    }

    public function edit(): void
    {
        $id        = (int) ($_GET['id']      ?? 0);
        $companyId = (int) ($_GET['company'] ?? 0);
        $empresa   = $this->resolverEmpresa($companyId);

        $model   = new EmpUsuario();
        $usuario = $model->buscar($id);

        if (!$usuario || (int) $usuario['company_id'] !== $companyId) {
            $_SESSION['flash_error'] = 'Usuario no encontrado.';
            header("Location: " . $this->urlLista($companyId));
            exit;
        }

        include __DIR__ . '/../views/emp_usuarios_edit.php';
    }

    public function update(): void
    {
        $id        = (int) ($_POST['id']         ?? 0);
        $companyId = (int) ($_POST['company_id'] ?? 0);
        $empresa   = $this->resolverEmpresa($companyId);

        $name     = trim($_POST['name']     ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $role     = $_POST['role']   ?? 'visor';
        $status   = isset($_POST['status']) ? 1 : 0;

        $urlEdit = "index.php?route=emp/usuarios/edit&id={$id}&company={$companyId}";

        if (empty($name) || empty($email)) {
            $_SESSION['flash_error'] = 'Nombre y correo son obligatorios.';
            header("Location: {$urlEdit}");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Correo electrónico inválido.';
            header("Location: {$urlEdit}");
            exit;
        }

        if (!in_array($role, self::ROLES_VALIDOS, true)) {
            $role = 'visor';
        }

        $model = new EmpUsuario();

        // Verificar que el usuario pertenece a esta empresa
        $usuario = $model->buscar($id);
        if (!$usuario || (int) $usuario['company_id'] !== $companyId) {
            $_SESSION['flash_error'] = 'Usuario no encontrado.';
            header("Location: " . $this->urlLista($companyId));
            exit;
        }

        if ($model->emailExiste($email, $id)) {
            $_SESSION['flash_error'] = 'El correo ya está en uso por otra cuenta.';
            header("Location: {$urlEdit}");
            exit;
        }

        $model->actualizar($id, [
            'name'     => $name,
            'email'    => $email,
            'password' => $password,
            'role'     => $role,
            'status'   => $status,
        ]);

        $_SESSION['flash_ok'] = 'Usuario actualizado correctamente.';
        header("Location: " . $this->urlLista($companyId));
        exit;
    }

    public function delete(): void
    {
        $id        = (int) ($_POST['id']         ?? 0);
        $companyId = (int) ($_POST['company_id'] ?? 0);
        $empresa   = $this->resolverEmpresa($companyId);

        $model   = new EmpUsuario();
        $usuario = $model->buscar($id);

        if (!$usuario || (int) $usuario['company_id'] !== $companyId) {
            $_SESSION['flash_error'] = 'Usuario no encontrado.';
            header("Location: " . $this->urlLista($companyId));
            exit;
        }

        $model->softDelete($id);
        $_SESSION['flash_ok'] = 'Usuario eliminado correctamente.';
        header("Location: " . $this->urlLista($companyId));
        exit;
    }

    public function resetPassword(): void
    {
        $id        = (int) ($_POST['id']         ?? 0);
        $companyId = (int) ($_POST['company_id'] ?? 0);
        $empresa   = $this->resolverEmpresa($companyId);

        $newPassword = trim($_POST['new_password'] ?? '');

        if (empty($newPassword) || mb_strlen($newPassword) < 6) {
            $_SESSION['flash_error'] = 'La nueva contraseña debe tener al menos 6 caracteres.';
            header("Location: index.php?route=emp/usuarios/edit&id={$id}&company={$companyId}");
            exit;
        }

        $model   = new EmpUsuario();
        $usuario = $model->buscar($id);

        if (!$usuario || (int) $usuario['company_id'] !== $companyId) {
            $_SESSION['flash_error'] = 'Usuario no encontrado.';
            header("Location: " . $this->urlLista($companyId));
            exit;
        }

        $model->resetPassword($id, $newPassword);
        $_SESSION['flash_ok'] = 'Contraseña restablecida correctamente.';
        header("Location: index.php?route=emp/usuarios/edit&id={$id}&company={$companyId}");
        exit;
    }
}
