<?php
class EtiquetaModel extends Model
{
    public function porTablero(int $tablero_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM trell_etiquetas
             WHERE tablero_id = ? OR tablero_id IS NULL
             ORDER BY es_base DESC, nombre"
        );
        $stmt->bind_param('i', $tablero_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function porTarjeta(int $tarjeta_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT e.* FROM trell_etiquetas e
             JOIN trell_tarjeta_etiquetas te ON te.etiqueta_id = e.id
             WHERE te.tarjeta_id = ?"
        );
        $stmt->bind_param('i', $tarjeta_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function toggleTarjeta(int $tarjeta_id, int $etiqueta_id): string
    {
        $stmt = $this->conn->prepare(
            "SELECT id FROM trell_tarjeta_etiquetas WHERE tarjeta_id = ? AND etiqueta_id = ? LIMIT 1"
        );
        $stmt->bind_param('ii', $tarjeta_id, $etiqueta_id);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            $del = $this->conn->prepare(
                "DELETE FROM trell_tarjeta_etiquetas WHERE tarjeta_id = ? AND etiqueta_id = ?"
            );
            $del->bind_param('ii', $tarjeta_id, $etiqueta_id);
            $del->execute();
            return 'removed';
        }
        $ins = $this->conn->prepare(
            "INSERT INTO trell_tarjeta_etiquetas (tarjeta_id, etiqueta_id) VALUES (?, ?)"
        );
        $ins->bind_param('ii', $tarjeta_id, $etiqueta_id);
        $ins->execute();
        return 'added';
    }
}
