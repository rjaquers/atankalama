<?php

require_once 'config/database.php';
require_once 'models/Product.php';

class StockEntry
{
    private $conn;

    public function __construct()
    {
        /**
         * Constructor:
         * - Crea conexión PDO reutilizando Database
         * - Compatible con arquitectura actual MVC
         */
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Registra ingreso de stock
     *
     * FUNCIONALIDAD:
     * - Inserta evento en stock_entries
     * - Suma cantidad al producto
     * - Registra log en product_logs
     * - Usa transacción para consistencia
     *
     * @param int $product_id
     * @param int $user_id
     * @param int $quantity_added
     * @param string|null $entry_location
     * @param string|null $description
     * @return bool
     */
    public function create($product_id, $user_id, $quantity_added, $location_id, $description)
    {
        $product_model = new Product();
        $product = $product_model->getById($product_id);

        if (! $product || $quantity_added <= 0) {
            return false;
        }

        $this->conn->beginTransaction();

        try {
            // Insertar evento
            $query = 'INSERT INTO stock_entries
                  (product_id, user_id, quantity_added, location_id, description)
                  VALUES (?, ?, ?, ?, ?)';
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                               $product_id,
                               $user_id,
                               $quantity_added,
                               $location_id,
                               $description
                           ]);

            $new_quantity = $product['quantity'] + $quantity_added;

            $update = $this->conn->prepare('UPDATE products SET quantity = ? WHERE id = ?');
            $update->execute([$new_quantity, $product_id]);

            $log = $this->conn->prepare(
                "
            INSERT INTO product_logs
            (product_id, user_id, action, field_changed, old_value, new_value)
            VALUES (?, ?, 'STOCK_ENTRY', 'quantity', ?, ?)
        "
            );
            $log->execute([
                              $product_id,
                              $user_id,
                              $product['quantity'],
                              $new_quantity
                          ]);

            $this->conn->commit();

            return true;
        } catch (Exception $e) {
            $this->conn->rollback();

            return false;
        }
    }

    /**
     * Registra ingresos de stock en lote dentro de una sola transacción.
     *
     * @param array  $items      [{product_id, quantity}]
     * @param int    $userId
     * @param int    $locationId
     * @param string $description
     * @return bool
     */
    public function createBatch(array $items, int $userId, int $locationId, string $description): bool
    {
        if (empty($items) || $locationId <= 0) {
            return false;
        }

        $product_model = new Product();

        $this->conn->beginTransaction();

        try {
            foreach ($items as $item) {
                $productId    = (int)($item['product_id'] ?? 0);
                $quantityAdd  = (int)($item['quantity']    ?? 0);

                if ($productId <= 0 || $quantityAdd <= 0) {
                    continue;
                }

                $product = $product_model->getById($productId);
                if (!$product) {
                    $this->conn->rollBack();
                    return false;
                }

                $this->conn->prepare(
                    'INSERT INTO stock_entries (product_id, user_id, quantity_added, location_id, description)
                     VALUES (?, ?, ?, ?, ?)'
                )->execute([$productId, $userId, $quantityAdd, $locationId, $description]);

                $newQuantity = (int)$product['quantity'] + $quantityAdd;

                $this->conn->prepare('UPDATE products SET quantity = ? WHERE id = ?')
                    ->execute([$newQuantity, $productId]);

                $this->conn->prepare(
                    "INSERT INTO product_logs (product_id, user_id, action, field_changed, old_value, new_value)
                     VALUES (?, ?, 'STOCK_ENTRY', 'quantity', ?, ?)"
                )->execute([$productId, $userId, $product['quantity'], $newQuantity]);
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
