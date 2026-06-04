<?php

class DocCompany
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function listar(): array
    {
        $stmt = $this->pdo->query("
            SELECT c.*, 
                   CONCAT(u.nombre, ' ', u.apellido) as creator_name
            FROM doc_companies c
            LEFT JOIN chk_usuarios u ON c.created_by = u.id
            ORDER BY c.business_name ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM doc_companies WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar(array $data): int
    {
        $sql = "INSERT INTO doc_companies (
                    rut, business_name, trade_name, contact_name, 
                    contact_email, contact_phone, address, city, 
                    type, notes, active, created_by
                ) VALUES (
                    ?, ?, ?, ?, 
                    ?, ?, ?, ?, 
                    ?, ?, ?, ?
                )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['rut'] ?: null,
            $data['business_name'],
            $data['trade_name'] ?: null,
            $data['contact_name'] ?: null,
            $data['contact_email'] ?: null,
            $data['contact_phone'] ?: null,
            $data['address'] ?: null,
            $data['city'] ?: null,
            $data['type'] ?? 'cliente',
            $data['notes'] ?: null,
            $data['active'] ?? 1,
            $data['created_by'] ?: null
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function actualizar(int $id, array $data): bool
    {
        $sql = "UPDATE doc_companies SET 
                    rut = ?, 
                    business_name = ?, 
                    trade_name = ?, 
                    contact_name = ?, 
                    contact_email = ?, 
                    contact_phone = ?, 
                    address = ?, 
                    city = ?, 
                    type = ?, 
                    notes = ?, 
                    active = ?
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['rut'] ?: null,
            $data['business_name'],
            $data['trade_name'] ?: null,
            $data['contact_name'] ?: null,
            $data['contact_email'] ?: null,
            $data['contact_phone'] ?: null,
            $data['address'] ?: null,
            $data['city'] ?: null,
            $data['type'] ?? 'cliente',
            $data['notes'] ?: null,
            $data['active'] ?? 1,
            $id
        ]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM doc_companies WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getUsuarioIdPorEmail(string $email): ?int
    {
        $stmt = $this->pdo->prepare("SELECT id FROM chk_usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $res = $stmt->fetch();
        return $res ? (int)$res['id'] : null;
    }
}
