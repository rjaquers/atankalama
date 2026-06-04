<?php



class ColacionVoucher
{
    /** @var mysqli */
    private $db;

    private $table = 'colacion_voucher';

    public function __construct($conn = null)
    {
        // 1) Si viene inyectada por parámetro
        if ($conn instanceof mysqli) {
            $this->db = $conn;

            return;
        }

        // 2) Si existe en el ámbito global como $db o $mysqli
        global $db, $mysqli;

        if (isset($db) && $db instanceof mysqli) {
            $this->db = $db;

            return;
        }

        if (isset($mysqli) && $mysqli instanceof mysqli) {
            $this->db = $mysqli;

            return;
        }

        throw new RuntimeException('No hay conexión MySQLi válida en ColacionVoucher.');
    }



    private function generarCodigoPublico(string $fecha, ?int $lote_id = null): string
    {
        // Fecha en YYYYMMDD (robusto ante valores inválidos)
        $ts = strtotime($fecha) ?: time();
        $ymd = date('Ymd', $ts);

        // 5 bytes = 10 hex mayúsculas = 2^40 (~1 billón) combinaciones.
        // Elimina la lógica de salt anterior que reducía la entropía efectiva
        // al recortar random_bytes a 4 chars y reemplazar 2 con sha1 determinista.
        $randHex = strtoupper(bin2hex(random_bytes(5)));

        return "ATK-CL-{$ymd}-{$randHex}";
    }

    public function generarDesdeLote($lote_id, $fecha_servicio, $cantidad, $guests): void
    {
        $sql = "INSERT INTO {$this->table}
            (lote_id, numero_en_lote, codigo_publico, guest_rut, guest_nombre, guest_habitacion)
            VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new \RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        }

        for ($i = 1; $i <= (int)$cantidad; $i++) {
            // si hay datos desde Excel para este número en lote, úsalos
            $rut  = $guests[$i]['rut']        ?? null;
            $nom  = $guests[$i]['nombre']     ?? null;
            $hab  = $guests[$i]['habitacion'] ?? null;

            $maxRetries = 5;
            $ok = false;

            for ($t = 0; $t < $maxRetries; $t++) {
                $code = $this->generarCodigoPublico($fecha_servicio, (int)$lote_id);
                // i i s s s s  -> (lote, nro, cod, rut, nombre, hab)
                $stmt->bind_param('iissss', $lote_id, $i, $code, $rut, $nom, $hab);

                if ($stmt->execute()) { $ok = true; break; }

                // 1062 = duplicate key en codigo_publico → reintenta
                if ((int)$this->db->errno !== 1062) {
                    $stmt->close();
                    throw new \RuntimeException('DB_INSERT_FAILED: '.$this->db->error);
                }
            }

            if (!$ok) {
                $stmt->close();
                throw new \RuntimeException('No se pudo generar un codigo_publico único tras reintentos.');
            }
        }

        $stmt->close();
    }


    public function listarPorLote($lote_id): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE lote_id=? ORDER BY numero_en_lote ASC");
        if (! $stmt) {
            throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        }
        $stmt->bind_param('i', $lote_id);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
        $stmt->close();

        return $rows;
    }


    public function listarPorLoteConHuesped(int $lote_id): array
    {
        $sql = "
        SELECT
            v.id,
            v.lote_id,
            v.numero_en_lote,
            v.codigo_publico,
            -- prioridad a lo grabado en el voucher; si está vacío, toma Excel
            CASE WHEN v.guest_nombre     IS NOT NULL AND v.guest_nombre     <> '' THEN v.guest_nombre     ELSE i.nombre     END AS nombre,
            CASE WHEN v.guest_rut        IS NOT NULL AND v.guest_rut        <> '' THEN v.guest_rut        ELSE i.rut        END AS rut,
            CASE WHEN v.guest_habitacion IS NOT NULL AND v.guest_habitacion <> '' THEN v.guest_habitacion ELSE i.habitacion END AS habitacion
        FROM colacion_voucher v
        JOIN colacion_lote    l ON l.id = v.lote_id
        LEFT JOIN excel_upload_item i
               ON i.upload_id = l.from_upload_id
              AND i.fila_nro  = v.numero_en_lote
        WHERE v.lote_id = ?
        ORDER BY v.numero_en_lote ASC
    ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new \RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        }
        $stmt->bind_param('i', $lote_id);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
        $stmt->close();

        return $rows;
    }




    public function marcarImpreso($lote_id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
            SET impreso_count = impreso_count + 1, ultimo_impreso_en = NOW()
            WHERE lote_id=?"
        );
        if (! $stmt) {
            throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        }
        $stmt->bind_param('i', $lote_id);
        $stmt->execute();
        $stmt->close();
    }

    public function registrarImpresionLote(int $lote_id, string $accion = 'impresion', int $copias = 1, ?int $usuario_id = null): void
    {
        // Subir contador para TODOS los vouchers del lote
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
            SET impreso_count = impreso_count + 1, ultimo_impreso_en = NOW()
            WHERE lote_id = ?"
        );
        if (! $stmt) {
            throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        }
        $stmt->bind_param('i', $lote_id);
        $stmt->execute();
        $stmt->close();

        // Log (un registro por lote)
        $log = $this->db->prepare(
            'INSERT INTO `colacion_impresion_log` (`lote_id`, `voucher_id`, `accion`, `copias`, `usuario_id`)
             VALUES (?, NULL, ?, ?, ?)'
        );
        if (!$log) {
            error_log('ColacionVoucher::registrarImpresionLote prepare falló: '.$this->db->error);
        } else {
            $log->bind_param('isii', $lote_id, $accion, $copias, $usuario_id);
            if (!$log->execute()) {
                error_log('ColacionVoucher::registrarImpresionLote execute falló: '.$log->error);
            }
            $log->close();
        }
    }

    public function registrarImpresionVoucher(int $voucher_id, string $accion = 'impresion', int $copias = 1, ?int $usuario_id = null): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
            SET impreso_count = impreso_count + 1, ultimo_impreso_en = NOW()
            WHERE id = ?"
        );
        if (! $stmt) {
            throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        }
        $stmt->bind_param('i', $voucher_id);
        $stmt->execute();
        $stmt->close();

        $log = $this->db->prepare(
            "INSERT INTO colacion_impresion_log (lote_id, voucher_id, accion, copias, usuario_id)
             SELECT lote_id, ?, ?, ?, ?
             FROM {$this->table}
             WHERE id = ?
             LIMIT 1"
        );
        if (!$log) {
            error_log('ColacionVoucher::registrarImpresionVoucher prepare falló: '.$this->db->error);
        } else {
            $log->bind_param('isiii', $voucher_id, $accion, $copias, $usuario_id, $voucher_id);
            if (!$log->execute()) {
                error_log('ColacionVoucher::registrarImpresionVoucher execute falló: '.$log->error);
            }
            $log->close();
        }
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id=?");
        if (! $stmt) {
            throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();

        return $row;
    }

    public function obtenerPorCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE codigo_publico=?");
        if (! $stmt) {
            throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        }
        $stmt->bind_param('s', $codigo);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();

        return $row;
    }

    public function incrementarScan(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET scan_count = scan_count + 1 WHERE id=?");
        if (! $stmt) {
            throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }

    public function marcarUsado(int $id, ?string $ip): bool
    {
        $now = (new DateTime('now', new DateTimeZone('America/Santiago')))->format('Y-m-d H:i:s');
        $sql = "UPDATE {$this->table}
                SET estado='usado', usado_en=?, usado_por_ip=INET6_ATON(?)
                WHERE id=? AND estado='pendiente'";
        $stmt = $this->db->prepare($sql);
        if (! $stmt) {
            throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        }
        $stmt->bind_param('ssi', $now, $ip, $id);
        $stmt->execute();
        $aff = $stmt->affected_rows;
        $stmt->close();

        return $aff > 0;
    }

    public function crearVoucherIndividual($lote_id, $rut, $nombre, $habitacion = null)
    {
        // Generar código público único
        $codigo_publico = strtoupper('ATK-'.bin2hex(random_bytes(4)));

        // Obtener correlativo dentro del lote
        $sql = 'SELECT COALESCE(MAX(numero_en_lote),0)+1 AS nextnum 
                FROM colacion_voucher 
                WHERE lote_id=?';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $lote_id);
        $stmt->execute();
        $next = $stmt->get_result()->fetch_assoc()['nextnum'];

        // Insertar voucher individual
        $sql = "INSERT INTO colacion_voucher
                (lote_id, numero_en_lote, codigo_publico, guest_rut, guest_nombre, guest_habitacion, estado)
                VALUES (?, ?, ?, ?, ?, ?, 'pendiente')";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            'iissss',
            $lote_id,
            $next,
            $codigo_publico,
            $rut,
            $nombre,
            $habitacion
        );
        $stmt->execute();

        return [
            'id' => $stmt->insert_id,
            'codigo_publico' => $codigo_publico,
            'numero_en_lote' => $next
        ];
    }



    public function obtenerPorCodigoPublico($codigo)
    {
        $sql = 'SELECT * FROM colacion_voucher WHERE codigo_publico = ? LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $codigo);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerVoucherDeLotePorRutYServicio($lote_id, $rut, $servicio_id)
    {
        $sql = 'SELECT v.*
            FROM colacion_voucher v
            INNER JOIN colacion_lote l ON l.id = v.lote_id
            WHERE v.lote_id = ?
              AND v.guest_rut = ?
              AND l.servicio_tipo_id = ?
            LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('isi', $lote_id, $rut, $servicio_id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }
}
