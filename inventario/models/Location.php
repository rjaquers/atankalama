<?php

require_once 'config/database.php';

class Location {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    public function getAll() {
        $query = "SELECT * FROM locations WHERE active = 1 ORDER BY zone, name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllWithInactive() {
        $query = "SELECT * FROM locations ORDER BY zone, name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $query = "SELECT * FROM locations WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($name, $description, $zone) {
        $query = "INSERT INTO locations (name, description, zone) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $name);
        $stmt->bindParam(2, $description);
        $stmt->bindParam(3, $zone);
        return $stmt->execute();
    }
    
    public function update($id, $name, $description, $zone, $active = 1) {
        $query = "UPDATE locations SET name = ?, description = ?, zone = ?, active = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $name);
        $stmt->bindParam(2, $description);
        $stmt->bindParam(3, $zone);
        $stmt->bindParam(4, $active);
        $stmt->bindParam(5, $id);
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "UPDATE locations SET active = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        return $stmt->execute();
    }
    
    public function getProductCount($id) {
        $query = "SELECT COUNT(*) as count FROM products WHERE location_id = ? AND status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}

?>