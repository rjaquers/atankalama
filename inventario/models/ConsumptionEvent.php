<?php

require_once 'config/database.php';
require_once 'models/Product.php';

class ConsumptionEvent {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    public function getAll($limit = 50) {
        $query = "SELECT ce.*, p.name as product_name, u.full_name as user_name, p.unit
                  FROM consumption_events ce 
                  JOIN products p ON ce.product_id = p.id 
                  JOIN users u ON ce.user_id = u.id 
                  ORDER BY ce.event_date DESC 
                  LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    
    public function create($product_id, $user_id, $quantity_consumed, $consumption_location, $description) {
        // First check if product exists and has enough stock
        $product_model = new Product();
        $product = $product_model->getById($product_id);
        
        if (!$product || $product['quantity'] < $quantity_consumed) {
            return false;
        }
        
        // Start transaction
        $this->conn->beginTransaction();
        
        try {
            // Insert consumption event
            $query = "INSERT INTO consumption_events (product_id, user_id, quantity_consumed, consumption_location, description) 
                      VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $product_id);
            $stmt->bindParam(2, $user_id);
            $stmt->bindParam(3, $quantity_consumed);
            $stmt->bindParam(4, $consumption_location);
            $stmt->bindParam(5, $description);
            $stmt->execute();
            
            // Update product stock
            $new_quantity = $product['quantity'] - $quantity_consumed;
            $update_query = "UPDATE products SET quantity = ? WHERE id = ?";
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(1, $new_quantity);
            $update_stmt->bindParam(2, $product_id);
            $update_stmt->execute();
            
            // Log the consumption
            $log_query = "INSERT INTO product_logs (product_id, user_id, action, field_changed, old_value, new_value) 
                          VALUES (?, ?, ?, ?, ?, ?)";
            $log_stmt = $this->conn->prepare($log_query);
            $log_stmt->bindParam(1, $product_id);
            $log_stmt->bindParam(2, $user_id);
            $action = 'CONSUMPTION';
            $field = 'quantity';
            $log_stmt->bindParam(3, $action);
            $log_stmt->bindParam(4, $field);
            $log_stmt->bindParam(5, $product['quantity']);
            $log_stmt->bindParam(6, $new_quantity);
            $log_stmt->execute();
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
    
    public function getConsumptionSummary($days = 30) {
        $query = "SELECT 
                    COUNT(*) as total_events,
                    SUM(quantity_consumed) as total_consumed,
                    COUNT(DISTINCT product_id) as products_consumed,
                    COUNT(DISTINCT consumption_location) as locations_involved
                  FROM consumption_events 
                  WHERE event_date >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getTopConsumedProducts($days = 30, $limit = 10) {
        $query = "SELECT p.name, p.unit, SUM(ce.quantity_consumed) as total_consumed
                  FROM consumption_events ce
                  JOIN products p ON ce.product_id = p.id
                  WHERE ce.event_date >= DATE_SUB(NOW(), INTERVAL ? DAY)
                  GROUP BY ce.product_id, p.name, p.unit
                  ORDER BY total_consumed DESC
                  LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $days, PDO::PARAM_INT);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopConsumed(int $days = 30, int $limit = 10): array
    {
        $stmt = $this->conn->prepare(
            '
            SELECT 
                p.name,
                SUM(c.quantity_consumed) AS total_consumed,
                p.quantity AS current_stock
            FROM consumption_events c
            INNER JOIN products p ON p.id = c.product_id
            WHERE c.event_date >= DATE_SUB(NOW(), INTERVAL :days DAY)
            GROUP BY p.id
            ORDER BY total_consumed DESC
            LIMIT :limit
        '
        );

        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * =========================================================
     * Método: createBatch
     * Descripción:
     * Procesa múltiples consumos en una sola transacción.
     * - Crea registro en consumption_batches
     * - Inserta eventos individuales
     * - Actualiza stock
     * - Registra logs
     * - Hace rollback completo si algo falla
     * =========================================================
     */
    public function createBatch(array $items, int $user_id, string $location, string $description): bool
    {
        try {
            $this->conn->beginTransaction();

            // Generar código único de lote
            $batch_code = 'BATCH-'.date('YmdHis').'-'.$user_id;

            // Insertar lote
            $stmtBatch = $this->conn->prepare(
                'INSERT INTO consumption_batches (batch_code, user_id, consumption_location, description)
             VALUES (?, ?, ?, ?)'
            );

            $stmtBatch->execute([$batch_code, $user_id, $location, $description]);
            $batch_id = $this->conn->lastInsertId();

            foreach ($items as $item) {
                $product_id = (int)$item['product_id'];
                $quantity = (int)$item['quantity'];

                if ($quantity <= 0) {
                    throw new Exception('Cantidad inválida.');
                }

                // Obtener producto actualizado
                $stmtProd = $this->conn->prepare('SELECT quantity FROM products WHERE id = ?');
                $stmtProd->execute([$product_id]);
                $product = $stmtProd->fetch(PDO::FETCH_ASSOC);

                if (! $product) {
                    throw new Exception('Producto no existe.');
                }

                if ($product['quantity'] < $quantity) {
                    throw new Exception('Stock insuficiente.');
                }

                $new_quantity = $product['quantity'] - $quantity;

                // Insertar evento
                $stmtEvent = $this->conn->prepare(
                    'INSERT INTO consumption_events
                 (product_id, user_id, quantity_consumed, consumption_location, description, batch_id)
                 VALUES (?, ?, ?, ?, ?, ?)'
                );

                $stmtEvent->execute([
                                        $product_id,
                                        $user_id,
                                        $quantity,
                                        $location,
                                        $description,
                                        $batch_id
                                    ]);

                // Actualizar stock
                $stmtUpdate = $this->conn->prepare(
                    'UPDATE products SET quantity = ? WHERE id = ?'
                );
                $stmtUpdate->execute([$new_quantity, $product_id]);

                // Registrar log
                $stmtLog = $this->conn->prepare(
                    "INSERT INTO product_logs
                 (product_id, user_id, action, field_changed, old_value, new_value)
                 VALUES (?, ?, 'CONSUMPTION_BATCH', 'quantity', ?, ?)"
                );

                $stmtLog->execute([
                                      $product_id,
                                      $user_id,
                                      $product['quantity'],
                                      $new_quantity
                                  ]);
            }

            $this->conn->commit();

            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();

            return false;
        }
    }
} //fin del model
