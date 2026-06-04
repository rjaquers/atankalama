<?php
require_once __DIR__ . '/../config/db.php';

class ProductoModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance();
    }

    public function obtenerTodos()
    {
        $stmt = $this->conn->prepare('SELECT * FROM coci_productos ORDER BY nombre ASC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerActivos()
    {
        $stmt = $this->conn->prepare('SELECT * FROM coci_productos WHERE activo = 1 ORDER BY nombre ASC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM coci_productos WHERE producto_id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($nombre, $precio, $activo)
    {
        $sql = 'INSERT INTO coci_productos (nombre, precio, activo) VALUES (:nombre, :precio, :activo)';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function actualizar($id, $nombre, $precio, $activo)
    {
        $sql = 'UPDATE coci_productos SET nombre = :nombre, precio = :precio, activo = :activo WHERE producto_id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function eliminar($id)
    {
        $sql = 'DELETE FROM coci_productos WHERE producto_id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
