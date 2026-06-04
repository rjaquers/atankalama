<?php
/**
 * ===================================================
 * Modelo: Log
 * Proyecto: Hotel Atankalama - Sistema de Inventario
 * Autor: Rodrigo Jaque Escobar
 * Contacto: rjaquers@gmail.com
 * Año: <?= date('Y') ?>
 * ===================================================
 *
 * Responsabilidad:
 * - Gestionar la lectura de registros de auditoría
 * - Tabla principal: product_logs
 * - Solo lectura (auditoría)
 */

class Log
{
    /**
     * Conexión PDO a la base de datos
     *
     * @var PDO
     */
    protected PDO $conn;

    /**
     * Constructor
     *
     * @param PDO $conn Conexión PDO inyectada
     */
    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Obtiene el historial completo de cambios de productos
     *
     * Relaciona:
     * - product_logs
     * - users (usuario que realizó la acción)
     * - products (producto afectado)
     *
     * @return array Lista de logs ordenados por fecha descendente
     */
    public function getProductLogs(): array
    {
        $sql = '
            SELECT
                `pl`.`id`,
                `pl`.`product_id`,
                `pl`.`user_id`,
                `pl`.`action`,
                `pl`.`field_changed`,
                `pl`.`old_value`,
                `pl`.`new_value`,
                `pl`.`timestamp`,
                `u`.`username`  AS `user_name`,
                `p`.`name`  AS `product_name`
            FROM `product_logs` `pl`
            LEFT JOIN `users` `u`   ON `u`.`id` = `pl`.`user_id`
            LEFT JOIN `products` `p` ON `p`.`id` = `pl`.`product_id`
            ORDER BY `pl`.`timestamp` DESC
        ';

        $stmt = $this->conn->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene logs filtrados por producto
     *
     * @param int $productId ID del producto
     * @return array
     */
    public function getLogsByProduct(int $productId): array
    {
        $sql = '
            SELECT
                pl.*,
                u.username AS user_name,
                p.name AS product_name
            FROM product_logs pl
            LEFT JOIN users u ON u.id = pl.user_id
            LEFT JOIN products p ON p.id = pl.product_id
            WHERE pl.product_id = :product_id
            ORDER BY pl.timestamp DESC
        ';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['product_id' => $productId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene logs filtrados por usuario
     *
     * @param int $userId ID del usuario
     * @return array
     */
    public function getLogsByUser(int $userId): array
    {
        $sql = '
            SELECT
                pl.*,
                u.name AS user_name,
                p.name AS product_name
            FROM product_logs pl
            LEFT JOIN users u ON u.id = pl.user_id
            LEFT JOIN products p ON p.id = pl.product_id
            WHERE pl.user_id = :user_id
            ORDER BY pl.timestamp DESC
        ';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Elimina todos los registros de logs
     * ⚠️ Acción irreversible (solo para administradores)
     *
     * @return bool
     */
    public function clearAll(): bool
    {
        $sql = 'TRUNCATE TABLE product_logs';
        return $this->conn->exec($sql) !== false;
    }

    /**
     * Obtiene logs de productos filtrados por mes y año
     *
     * @param int $year Año (ej: 2026)
     * @param int $month Mes (1–12)
     * @return array
     */
    public function getProductLogsByMonth(int $year, int $month): array
    {
        $sql = '
        SELECT
            pl.id,
            pl.product_id,
            pl.user_id,
            pl.action,
            pl.field_changed,
            pl.old_value,
            pl.new_value,
            pl.timestamp,
            u.username AS user_name,
            p.name AS product_name
        FROM product_logs pl
        LEFT JOIN users u ON u.id = pl.user_id
        LEFT JOIN products p ON p.id = pl.product_id
        WHERE YEAR(pl.timestamp) = :year
          AND MONTH(pl.timestamp) = :month
        ORDER BY pl.timestamp DESC
    ';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
                           'year' => $year,
                           'month' => $month
                       ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
