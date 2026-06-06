<?php
/**
 * UserModel - Atankalama Empresas
 */
class UserModel extends Model
{
    public function getByCompany($company_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM emp_users WHERE company_id = ? AND deleted_at IS NULL ORDER BY name ASC");
        $stmt->execute([$company_id]);
        return $stmt->fetchAll();
    }

    public function getById($id, $company_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM emp_users WHERE id = ? AND company_id = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$id, $company_id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $sql = "INSERT INTO emp_users (company_id, email, password_hash, name, role, status) 
                VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['company_id'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['name'],
            $data['role'] ?? 'visor'
        ]);
    }

    public function update($id, $data)
    {
        $fields = ["name = ?", "email = ?", "role = ?", "status = ?"];
        $params = [$data['name'], $data['email'], $data['role'], $data['status'], $id];

        if (!empty($data['password'])) {
            $fields[] = "password_hash = ?";
            // Reordenar params: el id siempre al final
            $id_val = array_pop($params);
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            $params[] = $id_val;
        }

        $sql = "UPDATE emp_users SET " . implode(', ', $fields) . " WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function softDelete($id, $company_id)
    {
        $stmt = $this->db->prepare("UPDATE emp_users SET deleted_at = NOW(), status = 0 WHERE id = ? AND company_id = ?");
        return $stmt->execute([$id, $company_id]);
    }

    public function emailExists($email, $exclude_id = null)
    {
        $sql = "SELECT id FROM emp_users WHERE email = ? AND deleted_at IS NULL";
        $params = [$email];
        if ($exclude_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }
}
