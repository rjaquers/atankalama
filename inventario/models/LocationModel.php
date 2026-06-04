<?php

class LocationModel
{

   // protected PDO $db;
   // private $conn;
   //
   // public function __construct($conn)
   // {
   //     $this->db = $conn;
   // }
   //
   //

    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll(): array
    {
        $stmt = $this->conn->prepare(
            'SELECT * FROM `locations` WHERE `active` = 1 ORDER BY `zone`, `name`'
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT * FROM `locations` WHERE `id` = ?'
        );
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $description, string $zone): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO `locations` (`name`, `description`, `zone`) VALUES (?, ?, ?)'
        );

        return $stmt->execute([$name, $description, $zone]);
    }

    public function update(
        int $id,
        string $name,
        string $description,
        string $zone,
        int $active
    ): bool {
        $stmt = $this->conn->prepare(
            'UPDATE `locations` SET `name`=?, `description`=?, `zone`=?, `active`=? WHERE `id`=?'
        );

        return $stmt->execute([$name, $description, $zone, $active, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE locations SET active = 0 WHERE id = ?'
        );

        return $stmt->execute([$id]);
    }

    public function getProductCount(int $id): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM products WHERE location_id = ? AND status='active'"
        );
        $stmt->execute([$id]);

        return (int)$stmt->fetchColumn();
    }

    public function getActive()
    {
        $stmt = $this->conn->prepare(
            '
            SELECT id, name 
            FROM locations
            WHERE active = 1
            ORDER BY name ASC
        '
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
