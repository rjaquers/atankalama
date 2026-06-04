<?php
class ComentarioModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->migrarTablas();
    }

    public function porTarjeta(int $tarjeta_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT c.*, u.nombre, u.apellido, u.email 
             FROM trell_comentarios c
             JOIN chk_usuarios u ON c.usuario_id = u.id
             WHERE c.tarjeta_id = ? AND c.deleted_at IS NULL
             ORDER BY c.created_at DESC"
        );
        $stmt->bind_param('i', $tarjeta_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function crear(int $tarjeta_id, int $usuario_id, string $comentario): int
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO trell_comentarios (tarjeta_id, usuario_id, comentario) VALUES (?, ?, ?)"
        );
        $stmt->bind_param('iis', $tarjeta_id, $usuario_id, $comentario);
        $stmt->execute();
        return $this->conn->insert_id;
    }

    public function eliminar(int $id, int $usuario_id): bool
    {
        // Solo el autor puede eliminar su comentario (o un admin, pero aquí validamos autoría básica)
        $stmt = $this->conn->prepare(
            "UPDATE trell_comentarios SET deleted_at = NOW() WHERE id = ? AND usuario_id = ?"
        );
        $stmt->bind_param('ii', $id, $usuario_id);
        return $stmt->execute();
    }

    public function migrarTablas(): void
    {
        try {
            $res = @$this->conn->query("SHOW TABLES LIKE 'trell_comentarios'");
            if ($res && $res->num_rows == 0) {
                @$this->conn->query("
                    CREATE TABLE trell_comentarios (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        tarjeta_id INT NOT NULL,
                        usuario_id INT NOT NULL,
                        comentario TEXT NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        deleted_at TIMESTAMP NULL,
                        INDEX (tarjeta_id),
                        INDEX (usuario_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ");
            } else {
                // Si la tabla existe, verificar si falta la columna 'comentario'
                $cols = $this->conn->query("SHOW COLUMNS FROM trell_comentarios LIKE 'comentario'");
                if ($cols && $cols->num_rows == 0) {
                    $this->conn->query("ALTER TABLE trell_comentarios ADD COLUMN comentario TEXT NOT NULL AFTER usuario_id");
                }
            }
        } catch (Exception $e) {
            // Ignoramos errores de migración aquí
        }
    }
}
