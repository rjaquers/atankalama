<?php
class TableroModel extends Model
{
    public function usuarioId(string $email): ?int
    {
        $stmt = $this->conn->prepare(
            "SELECT id FROM chk_usuarios WHERE email = ? AND estado = 'activo' LIMIT 1"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? (int)$row['id'] : null;
    }

    public function todos(): array
    {
        $res = $this->conn->query(
            "SELECT t.*, a.nombre AS area_nombre
             FROM trell_tableros t
             JOIN chk_areas a ON t.area_id = a.id
             ORDER BY a.nombre"
        );
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function porId(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT t.*, a.nombre AS area_nombre
             FROM trell_tableros t
             JOIN chk_areas a ON t.area_id = a.id
             WHERE t.id = ? LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function puedeEditar(int $tablero_id, int $usuario_id): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT puede_editar FROM trell_usuario_tableros
             WHERE tablero_id = ? AND usuario_id = ? LIMIT 1"
        );
        $stmt->bind_param('ii', $tablero_id, $usuario_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? (bool)$row['puede_editar'] : false;
    }

    public function listasConTarjetas(int $tablero_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM trell_listas
             WHERE tablero_id = ? AND deleted_at IS NULL
             ORDER BY posicion"
        );
        $stmt->bind_param('i', $tablero_id);
        $stmt->execute();
        $listas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmt2 = $this->conn->prepare(
            "SELECT t.*,
                (SELECT COUNT(*) FROM trell_tarjeta_miembros     WHERE tarjeta_id = t.id)                                                          AS cnt_miembros,
                (SELECT COUNT(*) FROM trell_adjuntos              WHERE tarjeta_id = t.id)                                                          AS cnt_adjuntos,
                (SELECT COUNT(*) FROM trell_comentarios           WHERE tarjeta_id = t.id AND deleted_at IS NULL)                                   AS cnt_comentarios,
                (SELECT COUNT(*) FROM trell_checklist_items ci JOIN trell_checklist c ON ci.checklist_id = c.id WHERE c.tarjeta_id = t.id)          AS items_total,
                (SELECT COUNT(*) FROM trell_checklist_items ci JOIN trell_checklist c ON ci.checklist_id = c.id WHERE c.tarjeta_id = t.id AND ci.completado = 1) AS items_ok
             FROM trell_tarjetas t
             WHERE t.tablero_id = ? AND t.deleted_at IS NULL AND t.archivada = 0 AND t.es_plantilla = 0
             ORDER BY t.lista_id, t.posicion"
        );
        $stmt2->bind_param('i', $tablero_id);
        $stmt2->execute();
        $tarjetas = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmt3 = $this->conn->prepare(
            "SELECT te.tarjeta_id, e.nombre, e.color
             FROM trell_tarjeta_etiquetas te
             JOIN trell_etiquetas e  ON te.etiqueta_id = e.id
             JOIN trell_tarjetas  t  ON te.tarjeta_id  = t.id
             WHERE t.tablero_id = ? AND t.deleted_at IS NULL"
        );
        $stmt3->bind_param('i', $tablero_id);
        $stmt3->execute();
        $etiq_raw = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);

        $etiq_map = [];
        foreach ($etiq_raw as $e) {
            $etiq_map[$e['tarjeta_id']][] = $e;
        }

        $por_lista = [];
        foreach ($tarjetas as $t) {
            $t['etiquetas'] = $etiq_map[$t['id']] ?? [];
            $por_lista[$t['lista_id']][] = $t;
        }

        foreach ($listas as &$l) {
            $l['tarjetas'] = $por_lista[$l['id']] ?? [];
        }
        return $listas;
    }
}
