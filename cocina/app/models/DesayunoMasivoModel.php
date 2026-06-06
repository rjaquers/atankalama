<?php

require_once __DIR__ . '/../config/db.php';

class DesayunoMasivoModel
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance();
        $this->ensureTablas();
    }

    private function ensureTablas(): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS `coci_desayunos_masivos` (
              `id` INT NOT NULL AUTO_INCREMENT,
              `fecha` DATE NOT NULL,
              `nombre_hotel` VARCHAR(50) NOT NULL DEFAULT 'Atankalama',
              `company_id` INT NOT NULL,
              `project_id` INT DEFAULT NULL,
              `nombre_empresa` VARCHAR(255) NOT NULL,
              `nombre_proyecto` VARCHAR(255) DEFAULT NULL,
              `cantidad` INT NOT NULL DEFAULT 1,
              `observaciones` TEXT DEFAULT NULL,
              `registrado_por` VARCHAR(100) NOT NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_fecha_hotel_empresa` (`fecha`, `nombre_hotel`, `company_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS `coci_desayunos_masivos_log` (
              `id` INT NOT NULL AUTO_INCREMENT,
              `desayuno_id` INT NOT NULL,
              `fecha` DATE NOT NULL,
              `nombre_hotel` VARCHAR(50) NOT NULL,
              `company_id` INT NOT NULL,
              `nombre_empresa` VARCHAR(255) NOT NULL,
              `cantidad_anterior` INT DEFAULT NULL,
              `cantidad_nueva` INT DEFAULT NULL,
              `observaciones_anterior` TEXT DEFAULT NULL,
              `observaciones_nueva` TEXT DEFAULT NULL,
              `accion` ENUM('insert','update','delete') NOT NULL,
              `registrado_por` VARCHAR(100) NOT NULL,
              `fecha_cambio` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_fecha_hotel` (`fecha`, `nombre_hotel`),
              KEY `idx_desayuno_id` (`desayuno_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function obtenerPorFechaHotel(string $fecha, string $hotel): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM coci_desayunos_masivos
             WHERE fecha = ? AND nombre_hotel = ?
             ORDER BY nombre_empresa ASC"
        );
        $stmt->execute([$fecha, $hotel]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Devuelve ['Atankalama' => total, 'Atankalama Inn' => total] para una fecha. */
    public function totalesPorHotel(string $fecha): array
    {
        $stmt = $this->conn->prepare(
            "SELECT nombre_hotel, SUM(cantidad) AS total
             FROM coci_desayunos_masivos
             WHERE fecha = ?
             GROUP BY nombre_hotel"
        );
        $stmt->execute([$fecha]);
        $mapa = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $mapa[$r['nombre_hotel']] = (int)$r['total'];
        }
        return $mapa;
    }

    public function upsert(
        string $fecha,
        string $hotel,
        int $companyId,
        ?int $projectId,
        string $nombreEmpresa,
        ?string $nombreProyecto,
        int $cantidad,
        ?string $observaciones,
        string $usuario
    ): int {
        $stmt = $this->conn->prepare(
            "SELECT id, cantidad, observaciones FROM coci_desayunos_masivos
             WHERE fecha = ? AND nombre_hotel = ? AND company_id = ?
             LIMIT 1"
        );
        $stmt->execute([$fecha, $hotel, $companyId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $this->conn->prepare(
                "UPDATE coci_desayunos_masivos
                 SET project_id = ?, nombre_empresa = ?, nombre_proyecto = ?,
                     cantidad = ?, observaciones = ?, registrado_por = ?, updated_at = NOW()
                 WHERE id = ?"
            )->execute([$projectId, $nombreEmpresa, $nombreProyecto, $cantidad, $observaciones, $usuario, $existing['id']]);

            if ((int)$existing['cantidad'] !== $cantidad || $existing['observaciones'] !== $observaciones) {
                $this->log(
                    (int)$existing['id'], $fecha, $hotel, $companyId, $nombreEmpresa,
                    (int)$existing['cantidad'], $cantidad,
                    $existing['observaciones'], $observaciones,
                    'update', $usuario
                );
            }
            return (int)$existing['id'];
        }

        $this->conn->prepare(
            "INSERT INTO coci_desayunos_masivos
               (fecha, nombre_hotel, company_id, project_id, nombre_empresa, nombre_proyecto,
                cantidad, observaciones, registrado_por)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        )->execute([$fecha, $hotel, $companyId, $projectId, $nombreEmpresa, $nombreProyecto,
                    $cantidad, $observaciones, $usuario]);

        $id = (int)$this->conn->lastInsertId();
        $this->log($id, $fecha, $hotel, $companyId, $nombreEmpresa,
            null, $cantidad, null, $observaciones, 'insert', $usuario);
        return $id;
    }

    /** Elimina registros del día/hotel que NO estén en $keepIds, dejando log de auditoría. */
    public function eliminarPorCompanyIds(string $fecha, string $hotel, array $keepIds, string $usuario): void
    {
        if (empty($keepIds)) {
            $stmt = $this->conn->prepare(
                "SELECT * FROM coci_desayunos_masivos WHERE fecha = ? AND nombre_hotel = ?"
            );
            $stmt->execute([$fecha, $hotel]);
        } else {
            $ph = implode(',', array_fill(0, count($keepIds), '?'));
            $stmt = $this->conn->prepare(
                "SELECT * FROM coci_desayunos_masivos
                 WHERE fecha = ? AND nombre_hotel = ? AND company_id NOT IN ({$ph})"
            );
            $stmt->execute(array_merge([$fecha, $hotel], $keepIds));
        }

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $this->log(
                (int)$row['id'], $row['fecha'], $row['nombre_hotel'],
                (int)$row['company_id'], $row['nombre_empresa'],
                (int)$row['cantidad'], null,
                $row['observaciones'], null,
                'delete', $usuario
            );
            $this->conn->prepare("DELETE FROM coci_desayunos_masivos WHERE id = ?")
                ->execute([$row['id']]);
        }
    }

    private function log(
        int $desayunoId, string $fecha, string $hotel,
        int $companyId, string $nombreEmpresa,
        ?int $cantAnt, ?int $cantNueva,
        ?string $obsAnt, ?string $obsNueva,
        string $accion, string $usuario
    ): void {
        $this->conn->prepare(
            "INSERT INTO coci_desayunos_masivos_log
               (desayuno_id, fecha, nombre_hotel, company_id, nombre_empresa,
                cantidad_anterior, cantidad_nueva, observaciones_anterior, observaciones_nueva,
                accion, registrado_por)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        )->execute([
            $desayunoId, $fecha, $hotel, $companyId, $nombreEmpresa,
            $cantAnt, $cantNueva, $obsAnt, $obsNueva,
            $accion, $usuario,
        ]);
    }
}
