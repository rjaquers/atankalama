<?php
class TarjetaModel extends Model
{
    public function crear(int $lista_id, int $tablero_id, string $titulo, int $creado_por): ?array
    {
        app_log("Iniciando creación de tarjeta: Lista=$lista_id, Tablero=$tablero_id, Titulo=$titulo, Usuario=$creado_por");
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare(
                "SELECT COALESCE(MAX(numero), 0) + 1 AS sig FROM trell_tarjetas WHERE tablero_id = ?"
            );
            $stmt->bind_param('i', $tablero_id);
            if (!$stmt->execute()) {
                $err = "Error al calcular el número de tarjeta: " . $this->conn->error;
                app_log($err);
                throw new Exception($err);
            }
            $numero = (int)$stmt->get_result()->fetch_assoc()['sig'];

            $stmt2 = $this->conn->prepare(
                "SELECT COALESCE(MAX(posicion), 0) + 1000 AS pos FROM trell_tarjetas WHERE lista_id = ? AND deleted_at IS NULL"
            );
            $stmt2->bind_param('i', $lista_id);
            if (!$stmt2->execute()) {
                $err = "Error al calcular la posición: " . $this->conn->error;
                app_log($err);
                throw new Exception($err);
            }
            $posicion = (float)$stmt2->get_result()->fetch_assoc()['pos'];

            $stmt3 = $this->conn->prepare(
                "INSERT INTO trell_tarjetas (lista_id, tablero_id, numero, titulo, posicion, creado_por, archivada, es_plantilla, descripcion)
                 VALUES (?, ?, ?, ?, ?, ?, 0, 0, '')"
            );
            $stmt3->bind_param('iiisdi', $lista_id, $tablero_id, $numero, $titulo, $posicion, $creado_por);
            if (!$stmt3->execute()) {
                $err = "Error en el INSERT de trell_tarjetas: " . $this->conn->error;
                app_log($err);
                throw new Exception($err);
            }
            $id = $this->conn->insert_id;
            app_log("Tarjeta insertada con éxito. ID=$id");

            $this->conn->commit();
            
            $tarjeta = $this->porId($id);
            if (!$tarjeta) {
                app_log("ERROR: La tarjeta se insertó (ID=$id) pero no se pudo recuperar con porId(). Posible problema con filtros (deleted_at).");
            }
            return $tarjeta;
        } catch (Exception $e) {
            app_log("EXCEPCIÓN al crear tarjeta: " . $e->getMessage());
            $this->conn->rollback();
            return null;
        }
    }

    public function porId(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT t.*, l.nombre AS lista_nombre, tb.nombre AS tablero_nombre, tb.fondo_color
             FROM trell_tarjetas t
             JOIN trell_listas l  ON t.lista_id   = l.id
             JOIN trell_tableros tb ON t.tablero_id = tb.id
             WHERE t.id = ? AND t.deleted_at IS NULL LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            app_log("Error en porId() para ID=$id: " . $this->conn->error);
            return null;
        }
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function actualizar(int $id, string $titulo, string $descripcion, ?string $fecha_vencimiento, int $completada = 0): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE trell_tarjetas
             SET titulo = ?, descripcion = ?, fecha_vencimiento = NULLIF(?, ''), completada = ?
             WHERE id = ? AND deleted_at IS NULL"
        );
        $stmt->bind_param('sssii', $titulo, $descripcion, $fecha_vencimiento, $completada, $id);
        return $stmt->execute();
    }

    public function archivar(int $id): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE trell_tarjetas SET archivada = 1, completada = 1 WHERE id = ? AND deleted_at IS NULL"
        );
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function tableroDeTarjeta(int $id): ?int
    {
        $stmt = $this->conn->prepare(
            "SELECT tablero_id FROM trell_tarjetas WHERE id = ? AND deleted_at IS NULL LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? (int)$row['tablero_id'] : null;
    }

    public function mover(int $id, int $lista_id, ?int $prev_id, ?int $next_id): bool
    {
        $posicion = $this->posicionEntre($prev_id, $next_id, $lista_id);
        $stmt = $this->conn->prepare(
            "UPDATE trell_tarjetas SET lista_id = ?, posicion = ? WHERE id = ? AND deleted_at IS NULL"
        );
        $stmt->bind_param('idi', $lista_id, $posicion, $id);
        return $stmt->execute();
    }

    private function posicionEntre(?int $prev_id, ?int $next_id, int $lista_id): float
    {
        [$prev_pos, $next_pos] = $this->fetchPosiciones($prev_id, $next_id);

        if ($prev_pos === null && $next_pos === null) return 1000.0;
        if ($prev_pos === null) return max(1.0, $next_pos - 1000.0);
        if ($next_pos === null) return $prev_pos + 1000.0;

        if (abs($next_pos - $prev_pos) < 0.01) {
            $this->renumerarLista($lista_id);
            [$prev_pos, $next_pos] = $this->fetchPosiciones($prev_id, $next_id);
        }

        return ($prev_pos + $next_pos) / 2.0;
    }

    private function fetchPosiciones(?int $prev_id, ?int $next_id): array
    {
        $prev_pos = $next_pos = null;
        if ($prev_id) {
            $s = $this->conn->prepare("SELECT posicion FROM trell_tarjetas WHERE id = ? LIMIT 1");
            $s->bind_param('i', $prev_id);
            $s->execute();
            $r = $s->get_result()->fetch_assoc();
            $prev_pos = $r ? (float)$r['posicion'] : null;
        }
        if ($next_id) {
            $s = $this->conn->prepare("SELECT posicion FROM trell_tarjetas WHERE id = ? LIMIT 1");
            $s->bind_param('i', $next_id);
            $s->execute();
            $r = $s->get_result()->fetch_assoc();
            $next_pos = $r ? (float)$r['posicion'] : null;
        }
        return [$prev_pos, $next_pos];
    }

    public function conFechaVencimiento(int $usuario_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT t.id, t.numero, t.titulo, t.fecha_vencimiento,
                    t.tablero_id, tb.nombre AS tablero_nombre, tb.fondo_color,
                    l.nombre AS lista_nombre
             FROM trell_tarjetas t
             JOIN trell_tableros tb ON t.tablero_id = tb.id
             JOIN trell_listas   l  ON t.lista_id   = l.id
             WHERE t.deleted_at IS NULL
               AND t.archivada   = 0
               AND t.es_plantilla = 0
               AND t.fecha_vencimiento IS NOT NULL
               AND EXISTS (
                   SELECT 1 FROM trell_usuario_tableros ut
                   WHERE ut.tablero_id = t.tablero_id AND ut.usuario_id = ?
               )
             ORDER BY t.fecha_vencimiento ASC"
        );
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function archivadasPorTablero(int $tablero_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT t.*, l.nombre AS lista_nombre
             FROM trell_tarjetas t
             JOIN trell_listas l ON t.lista_id = l.id
             WHERE t.tablero_id = ? AND t.archivada = 1 AND t.deleted_at IS NULL
             ORDER BY t.updated_at DESC"
        );
        $stmt->bind_param('i', $tablero_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function desarchivar(int $id): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE trell_tarjetas SET archivada = 0 WHERE id = ? AND deleted_at IS NULL"
        );
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function proximasAVencer(int $dias = 1): array
    {
        $fecha_objetivo = date('Y-m-d', strtotime("+$dias days"));
        $stmt = $this->conn->prepare(
            "SELECT t.id, t.titulo, t.fecha_vencimiento,
                    tb.nombre AS tablero_nombre, tb.fondo_color,
                    l.nombre  AS lista_nombre,
                    u.id      AS usuario_id,
                    u.nombre  AS usuario_nombre,
                    u.apellido AS usuario_apellido,
                    u.email   AS usuario_email
             FROM trell_tarjetas t
             JOIN trell_tableros tb ON t.tablero_id = tb.id
             JOIN trell_listas   l  ON t.lista_id   = l.id
             JOIN trell_tarjeta_miembros tm ON tm.tarjeta_id = t.id
             JOIN chk_usuarios u ON tm.usuario_id = u.id
             WHERE DATE(t.fecha_vencimiento) = ?
               AND t.deleted_at   IS NULL
               AND t.archivada    = 0
               AND t.es_plantilla = 0
               AND u.estado = 'activo'
             ORDER BY t.id, u.id"
        );
        $stmt->bind_param('s', $fecha_objetivo);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function asignadasAUsuario(int $usuario_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT t.id, t.numero, t.titulo, t.fecha_vencimiento,
                    t.tablero_id, tb.nombre AS tablero_nombre, tb.fondo_color,
                    l.nombre AS lista_nombre,
                    (SELECT COUNT(*) FROM trell_checklist_items ci
                       JOIN trell_checklist c ON ci.checklist_id = c.id
                      WHERE c.tarjeta_id = t.id)                         AS items_total,
                    (SELECT COUNT(*) FROM trell_checklist_items ci
                       JOIN trell_checklist c ON ci.checklist_id = c.id
                      WHERE c.tarjeta_id = t.id AND ci.completado = 1)  AS items_ok
             FROM trell_tarjetas t
             JOIN trell_tableros tb ON t.tablero_id = tb.id
             JOIN trell_listas   l  ON t.lista_id   = l.id
             JOIN trell_tarjeta_miembros tm ON tm.tarjeta_id = t.id
             WHERE tm.usuario_id  = ?
               AND t.deleted_at   IS NULL
               AND t.archivada    = 0
               AND t.es_plantilla = 0
             ORDER BY (t.fecha_vencimiento IS NULL) ASC,
                      t.fecha_vencimiento ASC,
                      tb.nombre ASC"
        );
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function renumerarLista(int $lista_id): void
    {
        $stmt = $this->conn->prepare(
            "SELECT id FROM trell_tarjetas WHERE lista_id = ? AND deleted_at IS NULL ORDER BY posicion ASC"
        );
        $stmt->bind_param('i', $lista_id);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $pos  = 1000.0;
        $upd  = $this->conn->prepare("UPDATE trell_tarjetas SET posicion = ? WHERE id = ?");
        foreach ($rows as $row) {
            $upd->bind_param('di', $pos, $row['id']);
            $upd->execute();
            $pos += 1000.0;
        }
    }
}
