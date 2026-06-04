<?php

require_once 'config/database.php';

class User {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    public function login($username, $password) {
        $query = "SELECT id, username, password, full_name, role FROM users WHERE username = ? AND active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                return $row;
            }
        }
        return false;
    }
    
    public function getAll() {
        $query = "SELECT id, username, full_name, role, active, created_at FROM users ORDER BY full_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($username, $password, $full_name, $role = 'user') {
        $query = "INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bindParam(1, $username);
        $stmt->bindParam(2, $hashed_password);
        $stmt->bindParam(3, $full_name);
        $stmt->bindParam(4, $role);
        return $stmt->execute();
    }
    
    public function update($id, $username, $full_name, $role, $active = 1, $password = null) {
        if ($password) {
            $query = "UPDATE users SET username = ?, full_name = ?, role = ?, active = ?, password = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bindParam(1, $username);
            $stmt->bindParam(2, $full_name);
            $stmt->bindParam(3, $role);
            $stmt->bindParam(4, $active);
            $stmt->bindParam(5, $hashed_password);
            $stmt->bindParam(6, $id);
        } else {
            $query = "UPDATE users SET username = ?, full_name = ?, role = ?, active = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $username);
            $stmt->bindParam(2, $full_name);
            $stmt->bindParam(3, $role);
            $stmt->bindParam(4, $active);
            $stmt->bindParam(5, $id);
        }
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "UPDATE users SET active = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }

    /**
     * Busca un usuario por correo electrónico
     */
    public function getUserByEmail($email)
    {
        $stmt = $this->conn->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Guarda token temporal para recuperación
     */
    public function setResetToken($user_id, $token, $expiry)
    {
        $sql = 'ALTER TABLE users 
            ADD COLUMN IF NOT EXISTS reset_token VARCHAR(255) NULL,
            ADD COLUMN IF NOT EXISTS reset_expire DATETIME NULL';
        $this->conn->exec($sql);

        $stmt = $this->conn->prepare('UPDATE users SET reset_token=?, reset_expire=? WHERE id=?');

        return $stmt->execute([$token, $expiry, $user_id]);
    }

    /**
     * Obtiene usuario a partir del token de reseteo
     */
    public function getUserByToken($token)
    {
        $stmt = $this->conn->prepare('SELECT * FROM users WHERE reset_token = ? LIMIT 1');
        $stmt->execute([$token]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza contraseña y limpia token
     */
    public function updatePassword($user_id, $hash)
    {
        $stmt = $this->conn->prepare(
            'UPDATE users 
        SET password = ?, reset_token = NULL, reset_expire = NULL 
        WHERE id = ?'
        );

        return $stmt->execute([$hash, $user_id]);
    }
}

?>