<?php

class ColacionAdicional
{
    /** @var mysqli */
    private $db;

    private $table = 'colacion_adicional';

    /**
     * Construye el modelo con conexión MySQLi.
     *
     * @param mysqli|null $db Conexión inyectada o fallback a global.
     * @throws RuntimeException Si no hay conexión válida.
     */
    public function __construct($db = null)
    {
        if ($db instanceof mysqli) {
            $this->db = $db;

            return;
        }

        global $db;
        $globalDb = $db;
        if ($globalDb instanceof mysqli) {
            $this->db = $globalDb;

            return;
        }

        throw new RuntimeException('No hay conexión MySQLi válida en ColacionAdicional.');
    }
    // Fin de la función __construct()

    /**
     * Lista todos los adicionales/servicios.
     *
     * @return array Lista asociativa.
     */
    public function listar(bool $soloActivos = true): array
    {
        $sql = "SELECT id, nombre, tipo, activo
            FROM {$this->table}";

        if ($soloActivos) {
            $sql .= ' WHERE activo = 1';
        }

        // Orden lógico del negocio
        $sql .= ' ORDER BY tipo ASC, nombre ASC';

        $res = $this->db->query($sql);
        if (! $res) {
            throw new RuntimeException('Error listando: '.$this->db->error);
        }

        return $res->fetch_all(MYSQLI_ASSOC) ?? [];
    }
// Fin de la función listar()

// Fin de la función listar()

// Fin de la función listar()

    // Fin de la función listar()

    /**
     * Obtiene un registro por ID.
     *
     * @param int $id ID del adicional.
     * @return array|null Registro o null si no existe.
     */
    public function obtener(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT id, nombre FROM {$this->table} WHERE id=? LIMIT 1");
        if (! $stmt) {
            throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();

        return $row;
    }
    // Fin de la función obtener()

    /**
     * Crea un nuevo adicional.
     *
     * @param string $nombre Nombre único.
     * @return int ID creado.
     */
    public function crear(string $nombre, int $tipo): int
    {
        $nombre = trim($nombre);
        $tipo   = (int)$tipo;

        if ($nombre === '' || mb_strlen($nombre) > 50) {
            throw new RuntimeException('Nombre inválido.');
        }

        if (!in_array($tipo, [1,2,3], true)) {
            throw new RuntimeException('Tipo inválido.');
        }


        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (nombre, tipo, activo)
         VALUES (?, ?, 1)"
        );
        if (!$stmt) {
            throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        }

        $stmt->bind_param('si', $nombre, $tipo);

        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Error creando: '.$err);
        }

        $id = (int)$this->db->insert_id;
        $stmt->close();

        return $id;
    }
// Fin de la función crear()


    //public function crear(string $nombre): int
    //{
    //    $nombre = trim($nombre);
    //    if ($nombre === '' || mb_strlen($nombre) > 50) {
    //        throw new RuntimeException('Nombre inválido (1..50).');
    //    }
    //
    //    $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (?)");
    //    if (! $stmt) {
    //        throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
    //    }
    //
    //    $stmt->bind_param('s', $nombre);
    //    if (! $stmt->execute()) {
    //        $err = $stmt->error;
    //        $stmt->close();
    //        throw new RuntimeException('Error creando: '.$err);
    //    }
    //
    //    $id = (int)$this->db->insert_id;
    //    $stmt->close();
    //
    //    return $id;
    //}
    // Fin de la función crear()

    /**
     * Actualiza un adicional.
     *
     * @param int $id ID.
     * @param string $nombre Nombre único.
     * @return void
     */
    public function actualizar(int $id, string $nombre, int $tipo): void
    {
        $id     = (int)$id;
        $tipo   = (int)$tipo;
        $nombre = trim($nombre);

        if ($id <= 0) throw new RuntimeException('ID inválido.');
        if ($nombre === '') throw new RuntimeException('Nombre inválido.');
        if (!in_array($tipo, [1,2], true)) throw new RuntimeException('Tipo inválido.');

        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
         SET nombre = ?, tipo = ?
         WHERE id = ?"
        );
        if (!$stmt) {
            throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        }

        $stmt->bind_param('sii', $nombre, $tipo, $id);

        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Error actualizando: '.$err);
        }

        $stmt->close();
    }
// Fin de la función actualizar()

    //public function actualizar(int $id, string $nombre): void
    //{
    //    $id = (int)$id;
    //    $nombre = trim($nombre);
    //
    //    if ($id <= 0) {
    //        throw new RuntimeException('ID inválido.');
    //    }
    //    if ($nombre === '' || mb_strlen($nombre) > 50) {
    //        throw new RuntimeException('Nombre inválido (1..50).');
    //    }
    //
    //    $stmt = $this->db->prepare("UPDATE {$this->table} SET nombre=? WHERE id=?");
    //    if (! $stmt) {
    //        throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
    //    }
    //
    //    $stmt->bind_param('si', $nombre, $id);
    //    if (! $stmt->execute()) {
    //        $err = $stmt->error;
    //        $stmt->close();
    //        throw new RuntimeException('Error actualizando: '.$err);
    //    }
    //    $stmt->close();
    //}
    // Fin de la función actualizar()

    /**
     * Verifica si un adicional está en uso por algún lote.
     *
     * @param int $id ID adicional.
     * @return bool true si existe relación en colacion_lote_adicional.
     */
    public function estaEnUso(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM `colacion_lote_adicional` WHERE `adicional_id`=? LIMIT 1');
        if (! $stmt) {
            throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_row();
        $stmt->close();

        return ! empty($row);
    }
    // Fin de la función estaEnUso()

    /**
     * Elimina un adicional (físico). Protege si está en uso.
     *
     * @param int $id ID adicional.
     * @return void
     */



    public function desactivar(int $id): void
    {
        $id = (int)$id;
        if ($id <= 0) {
            throw new RuntimeException('ID inválido.');
        }

        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
         SET activo = 0
         WHERE id = ?"
        );
        if (!$stmt) {
            throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        }

        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Error desactivando: '.$err);
        }

        $stmt->close();
    }
// Fin de la función desactivar()

    public function activar(int $id): void
    {
        $id = (int)$id;
        if ($id <= 0) {
            throw new RuntimeException('ID inválido.');
        }

        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
         SET activo = 1
         WHERE id = ?"
        );
        if (!$stmt) {
            throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        }

        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Error activando: '.$err);
        }

        $stmt->close();
    }
// Fin de la función activar()


}
