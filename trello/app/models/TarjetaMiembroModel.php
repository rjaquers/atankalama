<?php
class TarjetaMiembroModel extends Model
{
    public function porTarjeta(int $tarjeta_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT u.id, u.nombre, u.apellido, u.email
             FROM trell_tarjeta_miembros tm
             JOIN chk_usuarios u ON tm.usuario_id = u.id
             WHERE tm.tarjeta_id = ?"
        );
        $stmt->bind_param('i', $tarjeta_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function usuariosTablero(int $tablero_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT u.id, u.nombre, u.apellido, u.email
             FROM trell_usuario_tableros ut
             JOIN chk_usuarios u ON ut.usuario_id = u.id
             WHERE ut.tablero_id = ? AND u.estado = 'activo'
             ORDER BY u.nombre"
        );
        $stmt->bind_param('i', $tablero_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function datosUsuario(int $usuario_id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, nombre, apellido, email FROM chk_usuarios WHERE id = ? AND estado = 'activo' LIMIT 1"
        );
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function miembrosConEmail(int $tarjeta_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT u.id, u.nombre, u.apellido, u.email
             FROM trell_tarjeta_miembros tm
             JOIN chk_usuarios u ON tm.usuario_id = u.id
             WHERE tm.tarjeta_id = ? AND u.estado = 'activo'"
        );
        $stmt->bind_param('i', $tarjeta_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function toggle(int $tarjeta_id, int $usuario_id): string
    {
        $stmt = $this->conn->prepare(
            "SELECT id FROM trell_tarjeta_miembros WHERE tarjeta_id = ? AND usuario_id = ? LIMIT 1"
        );
        $stmt->bind_param('ii', $tarjeta_id, $usuario_id);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            $del = $this->conn->prepare(
                "DELETE FROM trell_tarjeta_miembros WHERE tarjeta_id = ? AND usuario_id = ?"
            );
            $del->bind_param('ii', $tarjeta_id, $usuario_id);
            $del->execute();
            return 'removed';
        }
        $ins = $this->conn->prepare(
            "INSERT INTO trell_tarjeta_miembros (tarjeta_id, usuario_id) VALUES (?, ?)"
        );
        $ins->bind_param('ii', $tarjeta_id, $usuario_id);
        $ins->execute();
        return 'added';
    }
}
