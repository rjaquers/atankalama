<?php
class TableroModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->asegurarEsquema('trell_tableros');
    }

    public function debugPermisos(int $tablero_id, int $usuario_id): string
    {
        $stmt = $this->conn->prepare(
            "SELECT puede_editar FROM trell_usuario_tableros
             WHERE tablero_id = ? AND usuario_id = ?"
        );
        $stmt->bind_param('ii', $tablero_id, $usuario_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if (!$res) return "Registro de permisos NO ENCONTRADO para Tablero=$tablero_id, Usuario=$usuario_id";
        return "Permiso puede_editar=" . $res['puede_editar'] . " para Tablero=$tablero_id, Usuario=$usuario_id";
    }

    public function debugEsquema(string $tabla = 'trell_tarjetas'): string
    {
        $this->asegurarEsquema($tabla);
        $res = $this->conn->query("DESCRIBE $tabla");
        if (!$res) return "Error: " . $this->conn->error;
        $fields = [];
        while($f = $res->fetch_assoc()) {
            $fields[] = $f['Field'] . " (" . $f['Type'] . ( $f['Null'] == 'NO' ? ' NOT NULL' : '' ) . ( $f['Default'] !== null ? ' DEFAULT '.$f['Default'] : '' ) . ")";
        }
        return "ESQUEMA $tabla: " . implode(" | ", $fields);
    }

    public function estadisticasGlobales(int $usuario_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT 
                COUNT(DISTINCT t.id) as total_tarjetas,
                SUM(CASE WHEN t.archivada = 1 THEN 1 ELSE 0 END) as archivadas,
                SUM(CASE WHEN t.fecha_vencimiento < CURDATE() AND t.archivada = 0 AND t.completada = 0 THEN 1 ELSE 0 END) as atrasadas,
                SUM(CASE WHEN t.completada = 1 THEN 1 ELSE 0 END) as completadas
             FROM trell_tarjetas t
             WHERE t.deleted_at IS NULL
               AND EXISTS (
                   SELECT 1 FROM trell_usuario_tableros ut
                   WHERE ut.tablero_id = t.tablero_id AND ut.usuario_id = ?
               )"
        );
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function estadisticasPorTablero(int $usuario_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT 
                tb.id, tb.nombre, tb.fondo_color,
                COUNT(t.id) as total,
                SUM(CASE WHEN t.completada = 1 THEN 1 ELSE 0 END) as completadas
             FROM trell_tableros tb
             LEFT JOIN trell_tarjetas t ON t.tablero_id = tb.id AND t.deleted_at IS NULL AND t.archivada = 0
             WHERE EXISTS (
                 SELECT 1 FROM trell_usuario_tableros ut
                 WHERE ut.tablero_id = tb.id AND ut.usuario_id = ?
             )
             GROUP BY tb.id"
        );
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function areasDisponibles(): array
    {
        $res = $this->conn->query("SELECT id, nombre FROM chk_areas ORDER BY nombre");
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function crearTablero(string $nombre, string $fondo_color, int $area_id): int
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO trell_tableros (nombre, fondo_color, area_id) VALUES (?, ?, ?)"
        );
        $stmt->bind_param('ssi', $nombre, $fondo_color, $area_id);
        $stmt->execute();
        $id = $this->conn->insert_id;

        // Crear las 4 listas básicas protegidas
        $listas = ['Solicitudes nuevas', 'Pendiente', 'En Proceso', 'Listo'];
        $tieneEsBasica = $this->columnaExiste('trell_listas', 'es_basica');
        if ($tieneEsBasica) {
            $upd = $this->conn->prepare(
                "INSERT INTO trell_listas (tablero_id, nombre, posicion, es_basica) VALUES (?, ?, ?, 1)"
            );
            foreach ($listas as $idx => $l) {
                $pos = ($idx + 1) * 1000;
                $upd->bind_param('isi', $id, $l, $pos);
                $upd->execute();
            }
        } else {
            $upd = $this->conn->prepare(
                "INSERT INTO trell_listas (tablero_id, nombre, posicion) VALUES (?, ?, ?)"
            );
            foreach ($listas as $idx => $l) {
                $pos = ($idx + 1) * 1000;
                $upd->bind_param('isi', $id, $l, $pos);
                $upd->execute();
            }
        }

        return $id;
    }

    public function crearLista(int $tablero_id, string $nombre): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT COALESCE(MAX(posicion), 0) + 1000 AS pos
             FROM trell_listas WHERE tablero_id = ? AND deleted_at IS NULL"
        );
        $stmt->bind_param('i', $tablero_id);
        $stmt->execute();
        $pos = (int)$stmt->get_result()->fetch_assoc()['pos'];

        $tieneEsBasica = $this->columnaExiste('trell_listas', 'es_basica');
        if ($tieneEsBasica) {
            $stmt2 = $this->conn->prepare(
                "INSERT INTO trell_listas (tablero_id, nombre, posicion, es_basica) VALUES (?, ?, ?, 0)"
            );
        } else {
            $stmt2 = $this->conn->prepare(
                "INSERT INTO trell_listas (tablero_id, nombre, posicion) VALUES (?, ?, ?)"
            );
        }
        $stmt2->bind_param('isi', $tablero_id, $nombre, $pos);
        if (!$stmt2->execute()) return null;

        return ['id' => $this->conn->insert_id, 'nombre' => $nombre, 'es_basica' => 0];
    }

    public function eliminarLista(int $lista_id): array
    {
        if ($this->esListaBasica($lista_id)) {
            return ['ok' => false, 'error' => 'Esta columna es básica y no puede eliminarse.'];
        }

        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS cnt FROM trell_tarjetas
             WHERE lista_id = ? AND deleted_at IS NULL AND archivada = 0"
        );
        $stmt->bind_param('i', $lista_id);
        $stmt->execute();
        $cnt = (int)$stmt->get_result()->fetch_assoc()['cnt'];
        if ($cnt > 0) {
            $plural = $cnt > 1;
            return [
                'ok'    => false,
                'error' => "La columna tiene $cnt tarjeta" . ($plural ? 's' : '') .
                           " activa" . ($plural ? 's' : '') .
                           ". Muévelas o archívalas antes de eliminar.",
            ];
        }

        $stmt2 = $this->conn->prepare("UPDATE trell_listas SET deleted_at = NOW() WHERE id = ?");
        $stmt2->bind_param('i', $lista_id);
        $stmt2->execute();
        return ['ok' => true];
    }

    public function esListaBasica(int $lista_id): bool
    {
        if (!$this->columnaExiste('trell_listas', 'es_basica')) return false;
        $stmt = $this->conn->prepare(
            "SELECT es_basica FROM trell_listas WHERE id = ? AND deleted_at IS NULL LIMIT 1"
        );
        $stmt->bind_param('i', $lista_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? (bool)$row['es_basica'] : false;
    }

    public function tablerodeLista(int $lista_id): ?int
    {
        $stmt = $this->conn->prepare(
            "SELECT tablero_id FROM trell_listas WHERE id = ? AND deleted_at IS NULL LIMIT 1"
        );
        $stmt->bind_param('i', $lista_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? (int)$row['tablero_id'] : null;
    }

    public function actualizarFondo(int $id, string $fondo_color, string $fondo_imagen): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE trell_tableros SET fondo_color = ?, fondo_imagen = NULLIF(?, '') WHERE id = ?"
        );
        $stmt->bind_param('ssi', $fondo_color, $fondo_imagen, $id);
        return $stmt->execute();
    }

    public function actualizarTablero(int $id, string $nombre, string $fondo_color, int $area_id): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE trell_tableros SET nombre = ?, fondo_color = ?, area_id = ? WHERE id = ?"
        );
        $stmt->bind_param('ssii', $nombre, $fondo_color, $area_id, $id);
        return $stmt->execute();
    }

    public function eliminarTablero(int $id): bool
    {
        // Intentamos borrado lógico si la columna existe, sino borrado físico
        $res = $this->conn->query("SHOW COLUMNS FROM trell_tableros LIKE 'deleted_at'");
        if ($res && $res->num_rows > 0) {
            $stmt = $this->conn->prepare("UPDATE trell_tableros SET deleted_at = NOW() WHERE id = ?");
        } else {
            $stmt = $this->conn->prepare("DELETE FROM trell_tableros WHERE id = ?");
        }
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function usuarioId(string $email): ?int
    {
        $email = trim(strtolower($email));
        $stmt = $this->conn->prepare(
            "SELECT id FROM chk_usuarios WHERE LOWER(email) = ? AND estado = 'activo' LIMIT 1"
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

    public function listasDelTablero(int $tablero_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, nombre FROM trell_listas
             WHERE tablero_id = ? AND deleted_at IS NULL ORDER BY posicion"
        );
        $stmt->bind_param('i', $tablero_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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

        // Obtener miembros de las tarjetas para el tablero
        $stmt4 = $this->conn->prepare(
            "SELECT tm.tarjeta_id, u.nombre, u.apellido, u.id AS usuario_id
             FROM trell_tarjeta_miembros tm
             JOIN chk_usuarios u ON tm.usuario_id = u.id
             JOIN trell_tarjetas t ON tm.tarjeta_id = t.id
             WHERE t.tablero_id = ? AND t.deleted_at IS NULL"
        );
        $stmt4->bind_param('i', $tablero_id);
        $stmt4->execute();
        $mbr_raw = $stmt4->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $avatar_colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4'];
        $mbr_map = [];
        foreach ($mbr_raw as $m) {
            $ini = mb_strtoupper(mb_substr($m['nombre'],0,1).mb_substr($m['apellido'],0,1));
            $color = $avatar_colors[$m['usuario_id'] % count($avatar_colors)];
            $mbr_map[$m['tarjeta_id']][] = ['iniciales' => $ini, 'color' => $color, 'nombre' => $m['nombre'].' '.$m['apellido']];
        }

        foreach ($listas as &$l) {
            if (isset($por_lista[$l['id']])) {
                foreach ($por_lista[$l['id']] as &$t) {
                    $t['miembros_detalle'] = $mbr_map[$t['id']] ?? [];
                }
                $l['tarjetas'] = $por_lista[$l['id']];
            } else {
                $l['tarjetas'] = [];
            }
        }
        return $listas;
    }

    public function todosConConteoMiembros(): array
    {
        $res = $this->conn->query(
            "SELECT t.id, t.nombre, t.fondo_color, t.area_id, a.nombre AS area_nombre,
                    COUNT(ut.usuario_id) AS total_miembros
             FROM trell_tableros t
             JOIN chk_areas a ON t.area_id = a.id
             LEFT JOIN trell_usuario_tableros ut ON ut.tablero_id = t.id
             GROUP BY t.id, t.nombre, t.fondo_color, t.area_id, a.nombre
             ORDER BY a.nombre, t.nombre"
        );
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function miembrosTablero(int $tablero_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT u.id, u.email, u.nombre, u.apellido, ut.puede_editar
             FROM trell_usuario_tableros ut
             JOIN chk_usuarios u ON u.id = ut.usuario_id
             WHERE ut.tablero_id = ?
             ORDER BY u.nombre, u.apellido"
        );
        $stmt->bind_param('i', $tablero_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function usuariosDisponibles(int $tablero_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT u.id, u.email, u.nombre, u.apellido
             FROM chk_usuarios u
             WHERE u.estado = 'activo'
               AND u.id NOT IN (
                   SELECT usuario_id FROM trell_usuario_tableros WHERE tablero_id = ?
               )
             ORDER BY u.nombre, u.apellido"
        );
        $stmt->bind_param('i', $tablero_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function asignarUsuario(int $tablero_id, int $usuario_id, bool $puede_editar): void
    {
        $pe = $puede_editar ? 1 : 0;
        $stmt = $this->conn->prepare(
            "INSERT INTO trell_usuario_tableros (tablero_id, usuario_id, puede_editar)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE puede_editar = VALUES(puede_editar)"
        );
        $stmt->bind_param('iii', $tablero_id, $usuario_id, $pe);
        $stmt->execute();
    }

    public function revocarUsuario(int $tablero_id, int $usuario_id): void
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM trell_usuario_tableros WHERE tablero_id = ? AND usuario_id = ?"
        );
        $stmt->bind_param('ii', $tablero_id, $usuario_id);
        $stmt->execute();
    }

    public function togglePuedeEditar(int $tablero_id, int $usuario_id): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT puede_editar FROM trell_usuario_tableros
             WHERE tablero_id = ? AND usuario_id = ? LIMIT 1"
        );
        $stmt->bind_param('ii', $tablero_id, $usuario_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) return false;

        $nuevo = $row['puede_editar'] ? 0 : 1;
        $stmt2 = $this->conn->prepare(
            "UPDATE trell_usuario_tableros SET puede_editar = ? WHERE tablero_id = ? AND usuario_id = ?"
        );
        $stmt2->bind_param('iii', $nuevo, $tablero_id, $usuario_id);
        $stmt2->execute();
        return (bool)$nuevo;
    }
}
