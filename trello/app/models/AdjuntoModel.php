<?php
class AdjuntoModel extends Model
{
    public function porTarjeta(int $tarjeta_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT a.*, u.nombre AS subido_por_nombre
             FROM trell_adjuntos a
             JOIN chk_usuarios u ON a.subido_por = u.id
             WHERE a.tarjeta_id = ?
             ORDER BY a.created_at DESC"
        );
        $stmt->bind_param('i', $tarjeta_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function crear(
        int $tarjeta_id, string $nombre_original,
        string $ruta, string $tipo, int $tamanio, int $subido_por
    ): int {
        $stmt = $this->conn->prepare(
            "INSERT INTO trell_adjuntos (tarjeta_id, nombre_original, ruta, tipo, tamanio, subido_por)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('isssii', $tarjeta_id, $nombre_original, $ruta, $tipo, $tamanio, $subido_por);
        $stmt->execute();
        return $this->conn->insert_id;
    }

    public function eliminar(int $id): ?string
    {
        $stmt = $this->conn->prepare("SELECT ruta FROM trell_adjuntos WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) return null;
        $del = $this->conn->prepare("DELETE FROM trell_adjuntos WHERE id = ?");
        $del->bind_param('i', $id);
        $del->execute();
        return $row['ruta'];
    }

    public function tarjetaDe(int $id): ?int
    {
        $stmt = $this->conn->prepare(
            "SELECT tarjeta_id FROM trell_adjuntos WHERE id = ? LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        return $r ? (int)$r['tarjeta_id'] : null;
    }
}
