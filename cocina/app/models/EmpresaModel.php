<?php

require_once __DIR__ . '/../config/db.php';

class EmpresaModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = TicketsDatabase::getInstance();
    }

    public function listarEmpresasActivas()
    {
        $stmt = $this->conn->prepare(
            'SELECT id, business_name, trade_name, contact_name, contact_email
             FROM doc_companies
             WHERE active = 1
             ORDER BY business_name ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarContratosPorEmpresa($company_id)
    {
        $stmt = $this->conn->prepare(
            'SELECT id, code, contract_type, status, start_date, end_date, base_amount, pricing_mode
             FROM doc_contracts
             WHERE company_id = :company_id AND active = 1
             ORDER BY created_at DESC'
        );
        $stmt->bindParam(':company_id', $company_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearEmpresa(array $datos): int
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO doc_companies
               (rut, business_name, trade_name, contact_name, contact_email,
                contact_phone, address, city, type, notes, active)
             VALUES
               (:rut, :business_name, :trade_name, :contact_name, :contact_email,
                :contact_phone, :address, :city, :type, :notes, 1)'
        );
        $stmt->execute([
            ':rut'           => $datos['rut']           ?: null,
            ':business_name' => $datos['business_name'],
            ':trade_name'    => $datos['trade_name']    ?: null,
            ':contact_name'  => $datos['contact_name']  ?: null,
            ':contact_email' => $datos['contact_email'] ?: null,
            ':contact_phone' => $datos['contact_phone'] ?: null,
            ':address'       => $datos['address']       ?: null,
            ':city'          => $datos['city']          ?: null,
            ':type'          => in_array($datos['type'] ?? '', ['cliente','proveedor'])
                                ? $datos['type'] : 'cliente',
            ':notes'         => $datos['notes']         ?: null,
        ]);
        return (int) $this->conn->lastInsertId();
    }

    public function obtenerEmpresa($id)
    {
        $stmt = $this->conn->prepare(
            'SELECT id, business_name, trade_name, contact_name, contact_email, contact_phone
             FROM doc_companies
             WHERE id = :id AND active = 1'
        );
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listarProyectosPorEmpresa(int $companyId): array
    {
        $stmt = $this->conn->prepare(
            'SELECT id, name FROM doc_projects WHERE company_id = ? AND active = 1 ORDER BY name ASC'
        );
        $stmt->execute([$companyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarTodasEmpresas(): array
    {
        $stmt = $this->conn->prepare(
            'SELECT c.id, c.rut, c.business_name, c.trade_name, c.contact_name,
                    c.contact_email, c.contact_phone, c.type,
                    (SELECT COUNT(*) FROM doc_projects p WHERE p.company_id = c.id AND p.active = 1) AS proyectos
             FROM doc_companies c
             WHERE c.active = 1
             ORDER BY c.business_name ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEmpresaCompleta(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT id, rut, business_name, trade_name, contact_name, contact_email,
                    contact_phone, address, city, type, notes
             FROM doc_companies
             WHERE id = :id AND active = 1'
        );
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function crearProyecto(int $companyId, string $nombre): int
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO doc_projects (company_id, name, active) VALUES (:company_id, :name, 1)'
        );
        $stmt->execute([':company_id' => $companyId, ':name' => $nombre]);
        return (int) $this->conn->lastInsertId();
    }

    public function eliminarProyecto(int $id): void
    {
        $stmt = $this->conn->prepare('UPDATE doc_projects SET active = 0 WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
