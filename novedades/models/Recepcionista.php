<?php


class Recepcionista
{
    private PDO $pdo;


    /**
     * Constructor del modelo Recepcionista
     * - Obtiene conexión única desde Database::getConnection()
     * - Asigna PDO a $this->pdo
     *
     * @throws RuntimeException si no hay conexión
     */
    public function __construct()
    {
        $this->pdo = Database::getConnection();

        if (!$this->pdo) {
            throw new RuntimeException('No se pudo establecer conexión PDO.');
        }
    }



    /**
     * Lista todos los miembros del personal activos con datos de área.
     *
     * @return array
     */
    public function listar(): array
    {
        $stmt = $this->pdo->query("
            SELECT r.*, a.nombre AS area_nombre, a.color AS area_color, a.icono AS area_icono
            FROM nov_recepcionistas r
            LEFT JOIN chat_areas a ON a.id = r.area_id
            WHERE r.activo = 1
            ORDER BY a.nombre ASC, r.nombre ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista todo el personal (activos e inactivos) con datos de área.
     *
     * @return array
     */
    public function listarTodos(): array
    {
        $stmt = $this->pdo->query("
            SELECT r.*, a.nombre AS area_nombre, a.color AS area_color, a.icono AS area_icono
            FROM nov_recepcionistas r
            LEFT JOIN chat_areas a ON a.id = r.area_id
            ORDER BY r.activo DESC, a.nombre ASC, r.nombre ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve todas las áreas activas para poblar selectores.
     *
     * @return array
     */
    public function listarAreas(): array
    {
        $stmt = $this->pdo->query("
            SELECT id, nombre, color, icono
            FROM chat_areas
            WHERE estado = 'activo'
            ORDER BY nombre ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Guarda un nuevo miembro del personal.
     *
     * @param string   $nombre
     * @param string|null $fono
     * @param string|null $correo
     * @param int|null $areaId
     * @return bool
     */
    public function guardar(string $nombre, ?string $fono = null, ?string $correo = null, ?int $areaId = null): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO nov_recepcionistas (nombre, fono, correo, area_id, activo) VALUES (?, ?, ?, ?, 1)'
        );
        return $stmt->execute([$nombre, $fono, $correo, $areaId]);
    }

    /**
     * Desactiva (baja lógica) un miembro del personal.
     *
     * @param int $id
     * @return bool
     */
    public function desactivar(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE nov_recepcionistas SET activo = 0 WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Busca un miembro del personal por su ID, incluye datos de área.
     *
     * @param int $id
     * @return array|false
     */
    public function buscar(int $id)
    {
        $stmt = $this->pdo->prepare("
            SELECT r.*, a.nombre AS area_nombre, a.color AS area_color
            FROM nov_recepcionistas r
            LEFT JOIN chat_areas a ON a.id = r.area_id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza los datos de un miembro del personal.
     *
     * @param int      $id
     * @param string   $nombre
     * @param string|null $fono
     * @param int      $activo
     * @param string|null $correo
     * @param int|null $areaId
     * @return bool
     */
    public function actualizar(int $id, string $nombre, ?string $fono, int $activo, ?string $correo = null, ?int $areaId = null): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE nov_recepcionistas SET nombre = ?, fono = ?, correo = ?, area_id = ?, activo = ? WHERE id = ?'
        );
        return $stmt->execute([$nombre, $fono, $correo, $areaId, $activo, $id]);
    }
}
