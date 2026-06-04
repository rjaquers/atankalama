<?php
class ChecklistModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->migrarTablas();
    }

    public function porTarjeta(int $tarjeta_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, titulo, posicion FROM trell_checklist WHERE tarjeta_id = ? ORDER BY posicion"
        );
        $stmt->bind_param('i', $tarjeta_id);
        $stmt->execute();
        $checklists = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($checklists as &$cl) {
            $s = $this->conn->prepare(
                "SELECT i.*, u.nombre AS responsable_nombre, u.apellido AS responsable_apellido 
                 FROM trell_checklist_items i
                 LEFT JOIN chk_usuarios u ON i.responsable_id = u.id
                 WHERE i.checklist_id = ? ORDER BY posicion"
            );
            $s->bind_param('i', $cl['id']);
            $s->execute();
            $cl['items'] = $s->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        return $checklists;
    }

    public function crearChecklist(int $tarjeta_id, string $titulo): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COALESCE(MAX(posicion), 0) + 1000 AS pos FROM trell_checklist WHERE tarjeta_id = ?"
        );
        $stmt->bind_param('i', $tarjeta_id);
        $stmt->execute();
        $pos = (float)$stmt->get_result()->fetch_assoc()['pos'];

        $stmt2 = $this->conn->prepare(
            "INSERT INTO trell_checklist (tarjeta_id, titulo, posicion) VALUES (?, ?, ?)"
        );
        $stmt2->bind_param('isd', $tarjeta_id, $titulo, $pos);
        $stmt2->execute();
        return $this->conn->insert_id;
    }

    public function agregarItem(int $checklist_id, string $texto, ?string $fecha = null, string $prioridad = 'normal', ?int $responsable_id = null): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COALESCE(MAX(posicion), 0) + 1000 AS pos FROM trell_checklist_items WHERE checklist_id = ?"
        );
        $stmt->bind_param('i', $checklist_id);
        $stmt->execute();
        $pos = (float)$stmt->get_result()->fetch_assoc()['pos'];

        $stmt2 = $this->conn->prepare(
            "INSERT INTO trell_checklist_items (checklist_id, texto, posicion, fecha_vencimiento, prioridad, responsable_id) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt2->bind_param('isdssi', $checklist_id, $texto, $pos, $fecha, $prioridad, $responsable_id);
        $stmt2->execute();
        return $this->conn->insert_id;
    }

    public function actualizarItem(int $item_id, array $data): bool
    {
        $fields = [];
        $types  = "";
        $values = [];
        foreach ($data as $k => $v) {
            $fields[] = "$k = ?";
            $values[] = $v;
            if (is_null($v)) {
                $types .= 's'; // bind_param doesn't have a 'null' type, 's' works for null
            } else {
                $types .= is_int($v) ? 'i' : (is_double($v) ? 'd' : 's');
            }
        }
        if (empty($fields)) return false;

        $sql = "UPDATE trell_checklist_items SET " . implode(', ', $fields) . " WHERE id = ?";
        $types .= 'i';
        $values[] = $item_id;

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    public function migrarTablas(): void
    {
        // Verificar si las columnas ya existen para evitar errores
        $res = $this->conn->query("SHOW COLUMNS FROM trell_checklist_items LIKE 'fecha_vencimiento'");
        if ($res && $res->num_rows == 0) {
            $this->conn->query("ALTER TABLE trell_checklist_items ADD COLUMN fecha_vencimiento DATE NULL AFTER completado");
        }

        $res2 = $this->conn->query("SHOW COLUMNS FROM trell_checklist_items LIKE 'prioridad'");
        if ($res2 && $res2->num_rows == 0) {
            $this->conn->query("ALTER TABLE trell_checklist_items ADD COLUMN prioridad VARCHAR(20) DEFAULT 'normal' AFTER fecha_vencimiento");
        }

        $res3 = $this->conn->query("SHOW COLUMNS FROM trell_checklist_items LIKE 'responsable_id'");
        if ($res3 && $res3->num_rows == 0) {
            $this->conn->query("ALTER TABLE trell_checklist_items ADD COLUMN responsable_id INT NULL AFTER prioridad");
        }
    }

    public function toggleItem(int $item_id): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE trell_checklist_items SET completado = 1 - completado WHERE id = ?"
        );
        $stmt->bind_param('i', $item_id);
        return $stmt->execute();
    }

    public function eliminarItem(int $item_id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM trell_checklist_items WHERE id = ?");
        $stmt->bind_param('i', $item_id);
        return $stmt->execute();
    }

    public function eliminarChecklist(int $id): bool
    {
        $s = $this->conn->prepare("DELETE FROM trell_checklist_items WHERE checklist_id = ?");
        $s->bind_param('i', $id);
        $s->execute();
        $stmt = $this->conn->prepare("DELETE FROM trell_checklist WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function tarjetaDe(int $checklist_id): ?int
    {
        $stmt = $this->conn->prepare(
            "SELECT tarjeta_id FROM trell_checklist WHERE id = ? LIMIT 1"
        );
        $stmt->bind_param('i', $checklist_id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        return $r ? (int)$r['tarjeta_id'] : null;
    }

    public function tarjetaDeItem(int $item_id): ?int
    {
        $stmt = $this->conn->prepare(
            "SELECT c.tarjeta_id FROM trell_checklist_items i
             JOIN trell_checklist c ON i.checklist_id = c.id
             WHERE i.id = ? LIMIT 1"
        );
        $stmt->bind_param('i', $item_id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        return $r ? (int)$r['tarjeta_id'] : null;
    }
}
