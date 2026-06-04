<?php
class ReferenciaModel extends Model
{
    public function crear(int $tarjeta_id, int $tablero_destino_id, int $lista_destino_id, string $mensaje): int
    {
        // Evitar duplicado al mismo tablero
        $chk = $this->conn->prepare(
            "SELECT id FROM trell_referencias WHERE tarjeta_id = ? AND tablero_destino_id = ? LIMIT 1"
        );
        $chk->bind_param('ii', $tarjeta_id, $tablero_destino_id);
        $chk->execute();
        if ($chk->get_result()->fetch_assoc()) return -1;

        $stmt = $this->conn->prepare(
            "INSERT INTO trell_referencias (tarjeta_id, tablero_destino_id, lista_destino_id, mensaje)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('iiis', $tarjeta_id, $tablero_destino_id, $lista_destino_id, $mensaje);
        $stmt->execute();
        return $this->conn->insert_id;
    }

    public function porTableroDestino(int $tablero_destino_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT r.id AS ref_id, r.tarjeta_id, r.lista_destino_id, r.mensaje,
                    t.titulo, t.numero, t.fecha_vencimiento,
                    tb.nombre AS tablero_origen_nombre, tb.fondo_color AS tablero_origen_color,
                    l.nombre  AS lista_origen_nombre
             FROM trell_referencias r
             JOIN trell_tarjetas  t  ON r.tarjeta_id        = t.id
             JOIN trell_tableros  tb ON t.tablero_id         = tb.id
             JOIN trell_listas    l  ON t.lista_id           = l.id
             WHERE r.tablero_destino_id = ? AND t.deleted_at IS NULL AND t.archivada = 0
             ORDER BY r.created_at"
        );
        $stmt->bind_param('i', $tablero_destino_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function porTarjeta(int $tarjeta_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT r.*, tb.nombre AS tablero_destino_nombre, l.nombre AS lista_destino_nombre
             FROM trell_referencias r
             JOIN trell_tableros tb ON r.tablero_destino_id = tb.id
             JOIN trell_listas   l  ON r.lista_destino_id  = l.id
             WHERE r.tarjeta_id = ?"
        );
        $stmt->bind_param('i', $tarjeta_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM trell_referencias WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function tarjetaDe(int $id): ?int
    {
        $stmt = $this->conn->prepare(
            "SELECT tarjeta_id FROM trell_referencias WHERE id = ? LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        return $r ? (int)$r['tarjeta_id'] : null;
    }
}
