<?php

class EmpUsuario
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->ensureColumns();
    }

    private function ensureColumns(): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        $db  = $this->pdo->query('SELECT DATABASE()')->fetchColumn();
        $col = $this->pdo->query(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'emp_users' AND COLUMN_NAME = 'notes'"
        )->fetchColumn();

        if (!$col) {
            $this->pdo->exec("ALTER TABLE emp_users ADD COLUMN notes TEXT NULL DEFAULT NULL");
        }
    }

    // ── Empresa ────────────────────────────────────────────────

    public function empresa(int $companyId): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM doc_companies WHERE id = ? LIMIT 1");
        $stmt->execute([$companyId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ── Usuarios ───────────────────────────────────────────────

    public function listarPorEmpresa(int $companyId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM emp_users
            WHERE company_id = ? AND deleted_at IS NULL
            ORDER BY name ASC
        ");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(int $id): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM emp_users WHERE id = ? AND deleted_at IS NULL LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO emp_users (company_id, email, password_hash, name, role, status, notes)
            VALUES (?, ?, ?, ?, ?, 1, ?)
        ");
        $stmt->execute([
            $data['company_id'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['name'],
            $data['role'],
            $data['notes'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function actualizar(int $id, array $data): bool
    {
        $fields = ['name = ?', 'email = ?', 'role = ?', 'status = ?'];
        $params = [$data['name'], $data['email'], $data['role'], $data['status']];

        if (!empty($data['password'])) {
            $fields[] = 'password_hash = ?';
            $params[]  = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $params[] = $id;

        $sql  = 'UPDATE emp_users SET ' . implode(', ', $fields) . ' WHERE id = ? AND deleted_at IS NULL';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function softDelete(int $id): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE emp_users SET deleted_at = NOW(), status = 0 WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    public function resetPassword(int $id, string $plain): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE emp_users SET password_hash = ? WHERE id = ? AND deleted_at IS NULL
        ");
        return $stmt->execute([password_hash($plain, PASSWORD_DEFAULT), $id]);
    }

    public function emailExiste(string $email, ?int $excluirId = null): bool
    {
        if ($excluirId) {
            $stmt = $this->pdo->prepare("
                SELECT id FROM emp_users WHERE email = ? AND id != ? AND deleted_at IS NULL LIMIT 1
            ");
            $stmt->execute([$email, $excluirId]);
        } else {
            $stmt = $this->pdo->prepare("
                SELECT id FROM emp_users WHERE email = ? AND deleted_at IS NULL LIMIT 1
            ");
            $stmt->execute([$email]);
        }
        return (bool) $stmt->fetch();
    }
}
