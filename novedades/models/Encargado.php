<?php

class Encargado
{
    private $pdo;

    /**
     * Constructor del modelo Encargado
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
    // Fin de la función __construct()

    /**
     * Lista todos los encargados con el nombre de su empresa.
     *
     * @return array Lista de encargados
     */
    public function listar()
    {
        $stmt = $this->pdo->query(
            'SELECT ne.*, ne.empresa_id, e.nombre AS empresa
         FROM nov_encargados ne
         INNER JOIN nov_empresas e ON ne.empresa_id = e.id
         ORDER BY ne.activo DESC, ne.nombre ASC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Fin de la función listar()

    /**
     * Busca un encargado por su ID.
     *
     * @param int $id ID del encargado
     * @return array|false Datos del encargado o false si no existe
     */
    public function buscar($id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM `nov_encargados` WHERE `id`=?');
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Fin de la función buscar()

    /**
     * Guarda un nuevo encargado.
     *
     * @param int $empresa_id ID de la empresa vinculada
     * @param string $nombre Nombre del encargado
     * @param string $telefono Teléfono
     * @param string $correo Correo electrónico
     * @param string $desde Fecha inicio periodo (Y-m-d)
     * @param string $hasta Fecha fin periodo (Y-m-d)
     * @return bool Éxito de la operación
     */
    public function guardar($empresa_id, $nombre, $telefono, $correo, $desde, $hasta)
    {
        $stmt = $this->pdo->prepare(
            '
            INSERT INTO nov_encargados (empresa_id, nombre, telefono, correo, periodo_desde, periodo_hasta)
            VALUES (?, ?, ?, ?, ?, ?)'
        );

        return $stmt->execute([$empresa_id, $nombre, $telefono, $correo, $desde, $hasta]);
    }
    // Fin de la función guardar()

    /**
     * Actualiza los datos de un encargado existente.
     *
     * @param int $id ID del encargado
     * @param int $empresa_id Nuevo ID de empresa
     * @param string $nombre Nuevo nombre
     * @param string $telefono Nuevo teléfono
     * @param string $correo Nuevo correo
     * @param string $desde Nuevo periodo desde
     * @param string $hasta Nuevo periodo hasta
     * @param int $activo Estado (0|1)
     * @return bool Éxito de la operación
     */
    public function actualizar($id, $empresa_id, $nombre, $telefono, $correo, $desde, $hasta, $activo)
    {
        $stmt = $this->pdo->prepare(
            '
            UPDATE `nov_encargados`
            SET `empresa_id`=?, `nombre`=?, `telefono`=?, `correo`=?, `periodo_desde`=?, `periodo_hasta`=?, `activo`=?
            WHERE `id`=?'
        );

        return $stmt->execute([$empresa_id, $nombre, $telefono, $correo, $desde, $hasta, $activo, $id]);
    }
    // Fin de la función actualizar()

    /**
     * Elimina físicamente un encargado.
     *
     * @param int $id ID del encargado
     * @return bool Éxito de la operación
     */
    public function eliminar($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM nov_encargados WHERE id=?');
        return $stmt->execute([$id]);
    }
    // Fin de la función eliminar()

    /**
     * Lista todos los encargados de una empresa específica.
     *
     * @param int $empresaId ID de la empresa
     * @return array Lista de encargados vinculados
     */
    public function listarPorEmpresa($empresaId)
    {
        $stmt = $this->pdo->prepare(
            'SELECT ne.*, e.nombre AS empresa
         FROM nov_encargados ne
         INNER JOIN nov_empresas e ON ne.empresa_id = e.id
         WHERE ne.empresa_id = ?
         ORDER BY ne.activo DESC, ne.nombre ASC'
        );

        $stmt->execute([$empresaId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Fin de la función listarPorEmpresa()
}
