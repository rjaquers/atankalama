<?php
/**
 * Modelo para contactos de empresa (doc_contacts).
 * Soporta múltiples contactos por empresa. Solo soft-delete (active = 0).
 */
class ContactModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    /** Crea la tabla si no existe (migración automática). */
    private function ensureTable(): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        $this->conn->query("
            CREATE TABLE IF NOT EXISTS doc_contacts (
                id         INT          AUTO_INCREMENT PRIMARY KEY,
                company_id INT          NOT NULL,
                name       VARCHAR(150) NOT NULL,
                email      VARCHAR(150) NOT NULL,
                phone      VARCHAR(50)  DEFAULT NULL,
                role       VARCHAR(100) DEFAULT NULL,
                active     TINYINT(1)   NOT NULL DEFAULT 1,
                created_by INT          DEFAULT NULL,
                created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_company_id (company_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    /**
     * Devuelve todos los contactos activos de una empresa.
     *
     * @param  int   $companyId
     * @return array
     */
    public function getByCompany(int $companyId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM doc_contacts WHERE company_id = ? AND active = 1 ORDER BY name ASC"
        );
        $stmt->bind_param('i', $companyId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Crea un nuevo contacto.
     *
     * @param  array $data  Requiere: company_id, name, email. Opcionales: phone, role.
     * @param  int   $userId
     * @return int   ID insertado
     */
    public function create(array $data, int $userId): int
    {
        $phone = $data['phone'] ?? null;
        $role  = $data['role']  ?? null;

        $stmt = $this->conn->prepare("
            INSERT INTO doc_contacts (company_id, name, email, phone, role, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'issssi',
            $data['company_id'],
            $data['name'],
            $data['email'],
            $phone,
            $role,
            $userId
        );
        $stmt->execute();
        return (int) $this->conn->insert_id;
    }
}
