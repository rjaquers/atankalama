<?php

class Temperatura
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /** Guarda un nuevo registro de temperatura */
    public function guardar($nombre, $hotel, $temperatura, $fotos)
    {
        $sql = 'INSERT INTO temp_registros (nombre, hotel, temperatura, fotos) VALUES (?, ?, ?, ?)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nombre, $hotel, $temperatura, implode(',', $fotos)]);

        return $this->pdo->lastInsertId();
    }

    /** Lista todos los registros ordenados por fecha */
    public function listarPorDia($fecha = null)
    {
        $fecha = $fecha ?: date('Y-m-d');
        $sql = 'SELECT * FROM `temp_registros` WHERE DATE(`fecha_hora`) = ? ORDER BY `fecha_hora` DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$fecha]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $sql = 'SELECT * FROM temp_registros WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function eliminar($id)
    {
        $registro = $this->obtenerPorId($id);
        if (!$registro) return false;

        if (!empty($registro['fotos'])) {
            foreach (explode(',', $registro['fotos']) as $ruta) {
                $ruta = trim($ruta);
                if ($ruta) {
                    $absoluta = __DIR__ . '/../' . $ruta;
                    if (file_exists($absoluta)) unlink($absoluta);
                }
            }
        }

        $stmt = $this->pdo->prepare('DELETE FROM temp_registros WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }


}

