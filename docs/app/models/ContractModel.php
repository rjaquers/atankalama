<?php
/**
 * Modelo de Contratos.
 *
 * Gestiona las operaciones CRUD sobre la tabla doc_contracts.
 * Es el modelo principal del sistema. Incluye JOINs con empresas,
 * hoteles y usuario creador. Soporta filtros múltiples, soft delete,
 * y generación de código único.
 *
 * @package App\Models
 */
class ContractModel extends Model
{
    /**
     * Obtiene todos los contratos activos con datos relacionados.
     *
     * @param  array $filters Filtros opcionales
     * @return array Lista de contratos
     */
    public function getAll($filters = [])
    {
        $where = ["c.active = 1"];
        $params = [];
        $types = "";

        // Filtro de Seguridad (IDOR)
        if (!empty($filters['created_by'])) {
            $where[] = "c.created_by = ?";
            $params[] = (int)$filters['created_by'];
            $types .= "i";
        }

        if (!empty($filters['status'])) {
            $where[] = "c.status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }

        // Soporte para múltiples estados (IN)
        if (!empty($filters['status_in']) && is_array($filters['status_in'])) {
            $placeholders = implode(',', array_fill(0, count($filters['status_in']), '?'));
            $where[] = "c.status IN ($placeholders)";
            foreach ($filters['status_in'] as $s) {
                $params[] = $s;
                $types .= "s";
            }
        }

        // Excluir estados específicos (NOT IN)
        if (!empty($filters['status_not_in']) && is_array($filters['status_not_in'])) {
            $placeholders = implode(',', array_fill(0, count($filters['status_not_in']), '?'));
            $where[] = "c.status NOT IN ($placeholders)";
            foreach ($filters['status_not_in'] as $s) {
                $params[] = $s;
                $types .= "s";
            }
        }

        if (!empty($filters['company_id'])) {
            $where[] = "c.company_id = ?";
            $params[] = (int)$filters['company_id'];
            $types .= "i";
        }

        if (!empty($filters['contract_type'])) {
            $where[] = "c.contract_type = ?";
            $params[] = $filters['contract_type'];
            $types .= "s";
        }

        if (!empty($filters['search'])) {
            $where[] = "(c.code LIKE ? OR co.business_name LIKE ?)";
            $search = "%" . $filters['search'] . "%";
            $params[] = $search;
            $params[] = $search;
            $types .= "ss";
        }

        if (!empty($filters['date_from'])) {
            $where[] = "c.start_date >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }
        if (!empty($filters['date_to'])) {
            $where[] = "c.start_date <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }

        $sql = "
            SELECT c.*, co.business_name, co.trade_name, co.rut AS company_rut,
                   u.name AS created_by_name
            FROM doc_contracts c
            JOIN doc_companies co ON co.id = c.company_id
            LEFT JOIN doc_users u ON u.id = c.created_by
            WHERE " . implode(" AND ", $where) . "
            ORDER BY c.created_at DESC
        ";

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    /**
     * Busca un contrato por su ID con datos completos.
     */
    public function getById($id)
    {
        $stmt = $this->conn->prepare("
            SELECT c.*, co.business_name, co.trade_name, co.rut AS company_rut,
                   co.contact_name, co.contact_email, co.contact_phone,
                   co.address AS company_address, co.city AS company_city,
                   t.name AS template_name,
                   u.name AS created_by_name
            FROM doc_contracts c
            JOIN doc_companies co ON co.id = c.company_id
            LEFT JOIN doc_contract_templates t ON t.id = c.template_id
            LEFT JOIN doc_users u ON u.id = c.created_by
            WHERE c.id = ? AND c.active = 1
            LIMIT 1
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    /**
     * Crea un nuevo contrato.
     */
    public function create($data, $userId)
    {
        $code = $this->generateCode();
        $stmt = $this->conn->prepare("
            INSERT INTO doc_contracts(code, company_id, template_id, contract_type,
                pricing_mode, duration_type, start_date, end_date, base_amount,
                base_guests, payment_frequency, status, notes, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $templateId = !empty($data['template_id']) ? (int)$data['template_id'] : null;
        $endDate = !empty($data['end_date']) ? $data['end_date'] : null;
        $baseGuests = !empty($data['base_guests']) ? (int)$data['base_guests'] : null;
        $baseAmount = (float)($data['base_amount'] ?? 0);
        $notes = $data['notes'] ?? null;

        $stmt->bind_param("siisssssdisssi",
            $code,
            $data['company_id'],
            $templateId,
            $data['contract_type'],
            $data['pricing_mode'],
            $data['duration_type'],
            $data['start_date'],
            $endDate,
            $baseAmount,
            $baseGuests,
            $data['payment_frequency'],
            $data['status'],
            $notes,
            $userId
        );
        $stmt->execute();
        return $stmt->affected_rows > 0 ? $stmt->insert_id : false;
    }

    /**
     * Actualiza un contrato existente.
     */
    public function update($id, $data)
    {
        $templateId = !empty($data['template_id']) ? (int)$data['template_id'] : null;
        $endDate = !empty($data['end_date']) ? $data['end_date'] : null;
        $baseGuests = !empty($data['base_guests']) ? (int)$data['base_guests'] : null;
        $baseAmount = (float)($data['base_amount'] ?? 0);
        $notes = $data['notes'] ?? null;

        $stmt = $this->conn->prepare("
            UPDATE doc_contracts
            SET company_id = ?, template_id = ?, contract_type = ?,
                pricing_mode = ?, duration_type = ?, start_date = ?, end_date = ?,
                base_amount = ?, base_guests = ?, payment_frequency = ?,
                status = ?, notes = ?
            WHERE id = ? AND active = 1
        ");
        $stmt->bind_param("iisssssdisssi",
            $data['company_id'],
            $templateId,
            $data['contract_type'],
            $data['pricing_mode'],
            $data['duration_type'],
            $data['start_date'],
            $endDate,
            $baseAmount,
            $baseGuests,
            $data['payment_frequency'],
            $data['status'],
            $notes,
            $id
        );
        $stmt->execute();
        return $stmt->affected_rows >= 0;
    }

    /**
     * Soft delete de un contrato.
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare("UPDATE doc_contracts SET active = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    /**
     * Cambia el estado de un contrato.
     */
    public function changeStatus($id, $status)
    {
        $stmt = $this->conn->prepare("UPDATE doc_contracts SET status = ? WHERE id = ? AND active = 1");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    /**
     * Actualiza la ruta del PDF generado.
     */
    public function updatePdfPath($id, $path)
    {
        $stmt = $this->conn->prepare("UPDATE doc_contracts SET generated_pdf_path = ? WHERE id = ? AND active = 1");
        $stmt->bind_param("si", $path, $id);
        $stmt->execute();
        return $stmt->affected_rows >= 0;
    }

    /**
     * Clona una cotización para crear una nueva versión.
     */
    public function createVersion($id)
    {
        $original = $this->getById($id);
        if (!$original) return false;

        $parentId = $original['parent_id'] ?: $original['id'];
        $nextVersion = (int)$original['version_number'] + 1;

        $stmt = $this->conn->prepare("
            INSERT INTO doc_contracts (
                parent_id, version_number, company_id, template_id, code, 
                contract_type, start_date, end_date, status, 
                pricing_mode, base_amount, base_guests, payment_frequency, 
                notes, created_by
            ) SELECT 
                ?, ?, company_id, template_id, code, 
                contract_type, start_date, end_date, 'quotation_draft', 
                pricing_mode, base_amount, base_guests, payment_frequency, 
                notes, created_by
            FROM doc_contracts WHERE id = ?
        ");
        $stmt->bind_param("iii", $parentId, $nextVersion, $id);
        
        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            
            $serviceModel = new ServiceModel();
            $services = $serviceModel->getByContractId($id);
            foreach ($services as $s) {
                $noteEscaped = $this->conn->real_escape_string($s['contract_notes'] ?? '');
                $this->conn->query("
                    INSERT INTO doc_contract_services (contract_id, service_id, unit_price, currency, billing_type, notes)
                    VALUES ($newId, {$s['id']}, {$s['unit_price']}, '{$s['currency']}', '{$s['billing_type']}', '$noteEscaped')
                ");
            }
            $this->conn->query("INSERT INTO doc_contract_hotels (contract_id, hotel_id) SELECT $newId, hotel_id FROM doc_contract_hotels WHERE contract_id = $id");
            return $newId;
        }
        return false;
    }

    /**
     * Sincroniza servicios con precios personalizados.
     */
    public function syncServices($contractId, $serviceIds, $details = [])
    {
        $stmt = $this->conn->prepare("DELETE FROM doc_contract_services WHERE contract_id = ?");
        $stmt->bind_param("i", $contractId);
        $stmt->execute();

        $stmt = $this->conn->prepare("
            INSERT INTO doc_contract_services(contract_id, service_id, unit_price, currency, billing_type, notes) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($serviceIds as $serviceId) {
            $sid = (int)$serviceId;
            $price = $details['price'][$sid] ?? 0;
            $currency = $details['currency'][$sid] ?? 'CLP';
            $billing = $details['billing'][$sid] ?? 'per_person';
            $note = $details['notes'][$sid] ?? null;
            
            $stmt->bind_param("iidsss", $contractId, $sid, $price, $currency, $billing, $note);
            $stmt->execute();
        }
    }

    public function countByStatus($status = null)
    {
        if ($status) {
            $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM doc_contracts WHERE active = 1 AND status = ?");
            $stmt->bind_param("s", $status);
            $stmt->execute();
            $res = $stmt->get_result();
        } else {
            $res = $this->conn->query("SELECT COUNT(*) AS total FROM doc_contracts WHERE active = 1");
        }
        $row = $res ? $res->fetch_assoc() : ['total' => 0];
        return (int)$row['total'];
    }

    public function getExpiringContracts($daysAhead = 30)
    {
        $stmt = $this->conn->prepare("
            SELECT c.*, co.business_name, co.contact_email,
                   u.name AS created_by_name, u.email AS created_by_email
            FROM doc_contracts c
            JOIN doc_companies co ON co.id = c.company_id
            LEFT JOIN doc_users u ON u.id = c.created_by
            WHERE c.active = 1
              AND c.status = 'vigente'
              AND c.end_date IS NOT NULL
              AND c.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY c.end_date ASC
        ");
        $stmt->bind_param("i", $daysAhead);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    private function generateCode()
    {
        $year = date('Y');
        $prefix = "CTR-{$year}-";
        $res = $this->conn->query("
            SELECT MAX(CAST(SUBSTRING(code, " . (strlen($prefix) + 1) . ") AS UNSIGNED)) AS max_num
            FROM doc_contracts
            WHERE code LIKE '{$prefix}%'
        ");
        $row = $res ? $res->fetch_assoc() : null;
        $next = ($row && $row['max_num']) ? (int)$row['max_num'] + 1 : 1;
        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    public function syncHotels($contractId, $hotelIds)
    {
        $stmt = $this->conn->prepare("DELETE FROM doc_contract_hotels WHERE contract_id = ?");
        $stmt->bind_param("i", $contractId);
        $stmt->execute();
        $stmt = $this->conn->prepare("INSERT INTO doc_contract_hotels(contract_id, hotel_id) VALUES (?, ?)");
        foreach ($hotelIds as $hotelId) {
            $hid = (int)$hotelId;
            $stmt->bind_param("ii", $contractId, $hid);
            $stmt->execute();
        }
    }
}
