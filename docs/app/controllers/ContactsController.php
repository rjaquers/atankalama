<?php
/**
 * ContactsController — endpoint AJAX para gestión de contactos de empresa.
 * Ruta: ?url=contacts/store  (POST)
 */
class ContactsController extends Controller
{
    /**
     * Crea un contacto nuevo para una empresa y devuelve JSON.
     */
    public function store(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido.'], 405);
            return;
        }

        PermissionMiddleware::check('companies_edit');

        $companyId = (int) ($_POST['company_id'] ?? 0);
        $name      = trim($_POST['name']  ?? '');
        $email     = trim($_POST['email'] ?? '');
        $phone     = trim($_POST['phone'] ?? '') ?: null;
        $role      = trim($_POST['role']  ?? '') ?: null;

        if (!$companyId || $name === '' || $email === '') {
            echo json_encode(['success' => false, 'message' => 'Nombre y correo son obligatorios.']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Correo electrónico inválido.']);
            exit;
        }

        $model = new ContactModel();
        $newId = $model->create([
            'company_id' => $companyId,
            'name'       => $name,
            'email'      => $email,
            'phone'      => $phone,
            'role'       => $role,
        ], AuthService::userId());

        echo json_encode([
            'success' => true,
            'contact' => [
                'id'    => $newId,
                'name'  => htmlspecialchars($name),
                'email' => htmlspecialchars($email),
                'phone' => $phone ? htmlspecialchars($phone) : null,
                'role'  => $role  ? htmlspecialchars($role)  : null,
            ],
        ]);
        exit;
    }
}
