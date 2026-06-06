<?php

class DocProject
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function empresa(int $companyId): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM doc_companies WHERE id = ? LIMIT 1");
        $stmt->execute([$companyId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listarPorEmpresa(int $companyId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM doc_projects
            WHERE company_id = ?
            ORDER BY active DESC, name ASC
        ");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM doc_projects WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear(int $companyId, string $name): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO doc_projects (company_id, name, active) VALUES (?, ?, 1)
        ");
        $stmt->execute([$companyId, $name]);
        return (int) $this->pdo->lastInsertId();
    }

    public function actualizar(int $id, string $name, int $active): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE doc_projects SET name = ?, active = ? WHERE id = ?
        ");
        return $stmt->execute([$name, $active, $id]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM doc_projects WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function nombreExiste(string $name, int $companyId, ?int $excluirId = null): bool
    {
        if ($excluirId) {
            $stmt = $this->pdo->prepare("
                SELECT id FROM doc_projects WHERE name = ? AND company_id = ? AND id != ? LIMIT 1
            ");
            $stmt->execute([$name, $companyId, $excluirId]);
        } else {
            $stmt = $this->pdo->prepare("
                SELECT id FROM doc_projects WHERE name = ? AND company_id = ? LIMIT 1
            ");
            $stmt->execute([$name, $companyId]);
        }
        return (bool) $stmt->fetch();
    }
}
