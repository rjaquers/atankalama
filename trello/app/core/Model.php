<?php
class Model
{
    protected $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    protected function asegurarEsquema(string $tabla): void
    {
        if ($tabla === 'trell_tableros') {
            if (!$this->columnaExiste('trell_tableros', 'fondo_imagen')) {
                $this->conn->query("ALTER TABLE trell_tableros ADD COLUMN fondo_imagen VARCHAR(255) NULL AFTER fondo_color");
                app_log("Columna fondo_imagen añadida a trell_tableros");
            }
            if (!$this->columnaExiste('trell_tableros', 'area_id')) {
                $this->conn->query("ALTER TABLE trell_tableros ADD COLUMN area_id INT NULL AFTER nombre");
                app_log("Columna area_id añadida a trell_tableros");
            }
        }

        if ($tabla === 'trell_tarjetas') {
            if (!$this->columnaExiste('trell_tarjetas', 'completada')) {
                $this->conn->query("ALTER TABLE trell_tarjetas ADD COLUMN completada TINYINT(1) DEFAULT 0 AFTER archivada");
                app_log("Columna completada añadida a trell_tarjetas");
            }
        }
    }

    protected function columnaExiste(string $tabla, string $columna): bool
    {
        $tabla   = preg_replace('/[^a-zA-Z0-9_]/', '', $tabla);
        $columna = preg_replace('/[^a-zA-Z0-9_]/', '', $columna);
        $res     = $this->conn->query("SHOW COLUMNS FROM `$tabla` LIKE '$columna'");
        return $res && $res->num_rows > 0;
    }
}
