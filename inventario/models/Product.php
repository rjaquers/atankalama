<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/database.php';

class Product {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    public function getAll() {
        $query = "SELECT p.*, c.name as category_name, l.name as location_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  LEFT JOIN locations l ON p.location_id = l.id 
                  WHERE p.status = 'active' 
                  ORDER BY p.name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllWithInactive() {
        $query = "SELECT p.*, c.name as category_name, l.name as location_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  LEFT JOIN locations l ON p.location_id = l.id 
                  ORDER BY p.name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $query = "SELECT p.*, c.name as category_name, l.name as location_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  LEFT JOIN locations l ON p.location_id = l.id 
                  WHERE p.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($name, $codigoBarra,  $vencimiento, $description, $quantity, $unit, $category_id, $location_id, $min_stock) {
        $query = "INSERT INTO products (name, codigoBarra , vencimiento, description, quantity, unit, category_id, location_id, min_stock) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $name);
        $stmt->bindParam(2, $codigoBarra);
        $stmt->bindParam(3, $vencimiento);
        $stmt->bindParam(4, $description);
        $stmt->bindParam(5, $quantity);
        $stmt->bindParam(6, $unit);
        $stmt->bindParam(7, $category_id);
        $stmt->bindParam(8, $location_id);
        $stmt->bindParam(9, $min_stock);
        
        if ($stmt->execute()) {
            $product_id = $this->conn->lastInsertId();
            $this->logChange($product_id, $_SESSION['user_id'], 'CREATE', 'product', '', $name);
            return $product_id;
        }
        return false;
    }
    
    public function update($id, $name, $codigoBarra, $vencimiento, $description, $quantity, $unit, $category_id, $location_id, $min_stock, $status = 'active') {
        // Get old values for logging
        $old_product = $this->getById($id);
        
        $query = "UPDATE products SET name = ?, codigoBarra = ?, vencimiento = ?, description = ?, quantity = ?, unit = ?, 
                  category_id = ?, location_id = ?, min_stock = ?, status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $name);
        $stmt->bindParam(2, $codigoBarra);
        $stmt->bindParam(3, $vencimiento);
        $stmt->bindParam(4, $description);
        $stmt->bindParam(5, $quantity);
        $stmt->bindParam(6, $unit);
        $stmt->bindParam(7, $category_id);
        $stmt->bindParam(8, $location_id);
        $stmt->bindParam(9, $min_stock);
        $stmt->bindParam(10, $status);
        $stmt->bindParam(11, $id);
        
        if ($stmt->execute()) {
            // Log changes
            if ($old_product['quantity'] != $quantity) {
                $this->logChange($id, $_SESSION['user_id'], 'UPDATE', 'quantity', $old_product['quantity'], $quantity);
            }
            if ($old_product['name'] != $name) {
                $this->logChange($id, $_SESSION['user_id'], 'UPDATE', 'name', $old_product['name'], $name);
            }
            return true;
        }
        return false;
    }
    
    public function updateStock($id, $new_quantity) {
        $old_product = $this->getById($id);
        $query = "UPDATE products SET quantity = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $new_quantity);
        $stmt->bindParam(2, $id);
        
        if ($stmt->execute()) {
            $this->logChange($id, $_SESSION['user_id'], 'STOCK_UPDATE', 'quantity', $old_product['quantity'], $new_quantity);
            return true;
        }
        return false;
    }
    
    public function delete($id) {
        $query = "UPDATE products SET status = 'inactive' WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        
        if ($stmt->execute()) {
            $this->logChange($id, $_SESSION['user_id'], 'DELETE', 'status', 'active', 'inactive');
            return true;
        }
        return false;
    }
    
    public function getLowStockProducts() {
        $query = "SELECT p.*, c.name as category_name, l.name as location_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  LEFT JOIN locations l ON p.location_id = l.id 
                  WHERE p.status = 'active' AND p.quantity <= p.min_stock 
                  ORDER BY p.quantity ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    
    public function getStockSummary() {
        $query = "SELECT 
                    COUNT(*) as total_products,
                    SUM(quantity) as total_items,
                    COUNT(CASE WHEN quantity <= min_stock THEN 1 END) as low_stock_count,
                    COUNT(CASE WHEN quantity = 0 THEN 1 END) as out_of_stock_count
                  FROM products WHERE status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getRecentLogs($product_id, $limit = 10) {
        $query = "SELECT pl.*, u.full_name 
                  FROM product_logs pl 
                  JOIN users u ON pl.user_id = u.id 
                  WHERE pl.product_id = ? 
                  ORDER BY pl.timestamp DESC 
                  LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $product_id);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function logChange($product_id, $user_id, $action, $field, $old_value, $new_value) {
        $query = "INSERT INTO product_logs (product_id, user_id, action, field_changed, old_value, new_value) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $product_id);
        $stmt->bindParam(2, $user_id);
        $stmt->bindParam(3, $action);
        $stmt->bindParam(4, $field);
        $stmt->bindParam(5, $old_value);
        $stmt->bindParam(6, $new_value);
        $stmt->execute();
    }

    public function insertImage($product_id, $file_path, $thumb_path = null)
    {
        /**
         * Inserta una imagen asociada a un producto.
         * - file_path: ruta de imagen principal
         * - thumb_path: ruta miniatura (opcional)
         */
        try {
            $sql = 'INSERT INTO product_images (product_id, file_path, thumb_path)
                VALUES (:product_id, :file_path, :thumb_path)';

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':product_id', (int)$product_id, PDO::PARAM_INT);
            $stmt->bindValue(':file_path', $file_path, PDO::PARAM_STR);
            $stmt->bindValue(':thumb_path', $thumb_path, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error al insertar imagen: ' . $e->getMessage());
            return false;
        }
    }


    // Obtener imágenes de un producto
    public function getImagesByProduct($product_id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM product_images WHERE product_id = :product_id');
        $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

// Eliminar imagen específica
    public function deleteImage($image_id)
    {
        $stmt = $this->conn->prepare('DELETE FROM product_images WHERE id = :id');
        $stmt->bindValue(':id', $image_id, PDO::PARAM_INT);

        return $stmt->execute();
    }


    /**
     * Cuenta productos sin stock (quantity = 0)
     */
    public function countSinStock(): int
    {
        $sql = "
        SELECT COUNT(*) AS total
        FROM products
        WHERE status = 'active'
          AND quantity = 0
    ";

        $result = $this->conn->query($sql);
        $row = $result->fetch(PDO::FETCH_ASSOC);

        return (int)($row['total'] ?? 0);

    }

    /**
     * Obtiene productos sin stock
     */
    /**
     * Obtiene productos SIN stock (quantity = 0)
     *
     * @return array
     */
    public function getSinStockProducts(): array
    {
        $sql = "
        SELECT 
            p.id,
            p.name,
            p.quantity,
            p.min_stock,
            c.name AS category,
            l.name AS location
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        LEFT JOIN locations l ON l.id = p.location_id
        WHERE p.status = 'active'
          AND p.quantity = 0
        ORDER BY p.name ASC
    ";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca productos por nombre o descripción
     * Usado para autocompletado (dashboard)
     *
     * @param string $term
     * @return array
     */
    /**
     * Busca productos por nombre o descripción
     * (Autocompletado dashboard)
     *
     * @param string $term
     * @return array
     */
    /**
     * Busca productos por nombre o descripción
     * Usado para autocompletado del dashboard
     *
     * @param string $term
     * @return array
     */
    /**
     * Busca productos por nombre o descripción (autocompletado dashboard)
     *
     * NOTA PDO:
     * - Con ATTR_EMULATE_PREPARES = false, NO reutilizar el mismo placeholder (:term)
     * - Se usan :term1 y :term2 para evitar HY093
     *
     * @param string $term
     * @return array
     */
    public function searchProducts(string $term): array
    {
        $sql = "
        SELECT
            id,
            name,
            COALESCE(description, '') AS description
        FROM products
        WHERE name LIKE :term1
           OR description LIKE :term2
        ORDER BY name ASC
        LIMIT 10
    ";

        $stmt = $this->conn->prepare($sql);

        $like = '%' . $term . '%';

        $stmt->execute([
                           'term1' => $like,
                           'term2' => $like,
                       ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene métricas de salud del inventario
     * - Total productos
     * - Productos bajo mínimo
     * - Productos sin movimiento en X días
     *
     * @param int $days
     * @return array
     */
    public function getInventoryHealth(int $days = 30): array
    {
        // Total de productos activos
        $total = $this->conn->query("
            SELECT COUNT(*) 
            FROM products 
            WHERE status = 'active'
        ")->fetchColumn();

        // Productos bajo mínimo
        $lowStock = $this->conn->query("
            SELECT COUNT(*) 
            FROM products 
            WHERE status = 'active'
            AND quantity <= min_stock
        ")->fetchColumn();

        // Productos sin movimiento en X días
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) 
            FROM products p
            WHERE p.status = 'active'
            AND NOT EXISTS (
                SELECT 1 
                FROM consumption_events c
                WHERE c.product_id = p.id
                AND c.event_date >= DATE_SUB(NOW(), INTERVAL :days DAY)
            )
        ");
        $stmt->execute(['days' => $days]);
        $noMovement = $stmt->fetchColumn();

        return [
            'total'        => (int)$total,
            'low_stock'    => (int)$lowStock,
            'no_movement'  => (int)$noMovement,
            'ok'           => max(0, $total - $lowStock)
        ];
    }


    public function getDeadStock(int $days = 30): array
    {
        $stmt = $this->conn->prepare("
        SELECT 
            p.name,
            p.quantity,
            p.updated_at
        FROM products p
        WHERE p.status = 'active'
        AND NOT EXISTS (
            SELECT 1 
            FROM consumption_events c
            WHERE c.product_id = p.id
            AND c.event_date >= DATE_SUB(NOW(), INTERVAL :days DAY)
        )
        ORDER BY p.updated_at ASC
    ");

        $stmt->execute(['days' => $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



}// fin model


