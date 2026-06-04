<?php
require_once __DIR__.'/../config/config.php';

class DashboardModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
        if (!$this->pdo) {
            die("Error Carga BD.");
        }
    }

    public function getTotalNovedades($start, $end) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM nov_novedades WHERE fecha_registro >= ? AND fecha_registro <= ?");
        $stmt->execute([$start, $end . ' 23:59:59']);
        return $stmt->fetchColumn();
    }

    public function getPendientesSeguimiento($start, $end) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM nov_novedades WHERE requiere_seguimiento = 1 AND seguimiento_estado = 0 AND fecha_registro >= ? AND fecha_registro <= ?");
        $stmt->execute([$start, $end . ' 23:59:59']);
        return $stmt->fetchColumn();
    }

    public function getNovedadesPorHotel($start, $end) {
        $stmt = $this->pdo->prepare("SELECT hotel, COUNT(*) as total FROM nov_novedades WHERE fecha_registro >= ? AND fecha_registro <= ? GROUP BY hotel");
        $stmt->execute([$start, $end . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNovedadesPorArea($start, $end) {
         $stmt = $this->pdo->prepare("SELECT area, COUNT(*) as total FROM nov_novedades WHERE fecha_registro >= ? AND fecha_registro <= ? GROUP BY area ORDER BY total DESC");
         $stmt->execute([$start, $end . ' 23:59:59']);
         return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNovedadesPorTipo($start, $end) {
         $stmt = $this->pdo->prepare("SELECT tipo_novedad, COUNT(*) as total FROM nov_novedades WHERE fecha_registro >= ? AND fecha_registro <= ? GROUP BY tipo_novedad ORDER BY total DESC");
         $stmt->execute([$start, $end . ' 23:59:59']);
         return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getImportanciaCritica($start, $end) {
         $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM nov_novedades WHERE nivel_importancia >= 8 AND fecha_registro >= ? AND fecha_registro <= ?");
         $stmt->execute([$start, $end . ' 23:59:59']);
         return $stmt->fetchColumn();
    }

    public function getRecientes($start, $end, $limite = 5) {
        $stmt = $this->pdo->prepare("SELECT id, hotel, area, tipo_novedad, fecha_registro, nivel_importancia FROM nov_novedades WHERE fecha_registro >= ? AND fecha_registro <= ? ORDER BY id DESC LIMIT ?");
        $stmt->bindValue(1, $start);
        $stmt->bindValue(2, $end . ' 23:59:59');
        $stmt->bindValue(3, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopRegistradores($start, $end, $limite = 5) {
        $stmt = $this->pdo->prepare("
            SELECT r.nombre, COUNT(n.id) as total
            FROM nov_novedades n
            JOIN nov_recepcionistas r ON r.id = n.recepcionista_id
            WHERE n.fecha_registro >= ? AND n.fecha_registro <= ?
            GROUP BY n.recepcionista_id, r.nombre
            ORDER BY total DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $start);
        $stmt->bindValue(2, $end . ' 23:59:59');
        $stmt->bindValue(3, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDistribucionImportancia($start, $end) {
        $stmt = $this->pdo->prepare("
            SELECT
                CASE
                    WHEN nivel_importancia BETWEEN 1 AND 3 THEN 'Baja (1-3)'
                    WHEN nivel_importancia BETWEEN 4 AND 7 THEN 'Media (4-7)'
                    WHEN nivel_importancia BETWEEN 8 AND 10 THEN 'Crítica (8-10)'
                    ELSE 'Sin clasificar'
                END as rango,
                COUNT(*) as total
            FROM nov_novedades
            WHERE fecha_registro >= ? AND fecha_registro <= ?
            GROUP BY rango
            ORDER BY MIN(nivel_importancia)
        ");
        $stmt->execute([$start, $end . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstadisticasDetalladasPorHotel($start, $end) {
        $stmt = $this->pdo->prepare("
            SELECT
                hotel,
                COUNT(*) as total,
                SUM(CASE WHEN nivel_importancia >= 8 THEN 1 ELSE 0 END) as criticas,
                SUM(CASE WHEN requiere_seguimiento = 1 AND seguimiento_estado != 2 THEN 1 ELSE 0 END) as pendientes,
                ROUND(AVG(nivel_importancia), 1) as promedio_importancia
            FROM nov_novedades
            WHERE fecha_registro >= ? AND fecha_registro <= ?
            GROUP BY hotel
            ORDER BY total DESC
        ");
        $stmt->execute([$start, $end . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopAreasPorHotel($start, $end, $hotel) {
        $stmt = $this->pdo->prepare("
            SELECT area, COUNT(*) as total
            FROM nov_novedades
            WHERE fecha_registro >= ? AND fecha_registro <= ? AND hotel = ?
            GROUP BY area
            ORDER BY total DESC
            LIMIT 3
        ");
        $stmt->execute([$start, $end . ' 23:59:59', $hotel]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRawTipoPorHotel($start, $end) {
        $stmt = $this->pdo->prepare("
            SELECT hotel, tipo_novedad, COUNT(*) as total
            FROM nov_novedades
            WHERE fecha_registro >= ? AND fecha_registro <= ?
            GROUP BY hotel, tipo_novedad
            ORDER BY hotel, total DESC
        ");
        $stmt->execute([$start, $end . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRawAreaPorHotel($start, $end) {
        $stmt = $this->pdo->prepare("
            SELECT hotel, area, COUNT(*) as total
            FROM nov_novedades
            WHERE fecha_registro >= ? AND fecha_registro <= ?
            GROUP BY hotel, area
            ORDER BY hotel, total DESC
        ");
        $stmt->execute([$start, $end . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRawCriticidadPorHotel($start, $end) {
        $stmt = $this->pdo->prepare("
            SELECT hotel,
                CASE
                    WHEN nivel_importancia BETWEEN 1 AND 3  THEN 'Baja (1-3)'
                    WHEN nivel_importancia BETWEEN 4 AND 7  THEN 'Media (4-7)'
                    WHEN nivel_importancia BETWEEN 8 AND 10 THEN 'Crítica (8-10)'
                    ELSE 'Sin clasificar'
                END as rango,
                COUNT(*) as total
            FROM nov_novedades
            WHERE fecha_registro >= ? AND fecha_registro <= ?
            GROUP BY hotel, rango
            ORDER BY hotel, MIN(nivel_importancia)
        ");
        $stmt->execute([$start, $end . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
