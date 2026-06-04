<?php

class Empresa
{
    private $pdo;

    /**
     * Constructor del modelo Empresa
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
     * Lista todas las empresas.
     *
     * @return array Lista de empresas
     */
    public function listar()
    {
        $stmt = $this->pdo->query('SELECT * FROM `doc_companies` ORDER BY `active` DESC, `business_name` ASC');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Fin de la función listar()

    /**
     * Busca una empresa por su ID.
     *
     * @param int $id ID de la empresa
     * @return array|false Datos de la empresa o false si no existe
     */
    public function buscar($id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM doc_companies WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Fin de la función buscar()

    /**
     * Guarda una nueva empresa.
     */
    public function guardar($rut, $business_name, $trade_name, $contact_name, $contact_email, $contact_phone, $address, $city, $type, $notes)
    {
        $stmt = $this->pdo->prepare('INSERT INTO doc_companies (rut, business_name, trade_name, contact_name, contact_email, contact_phone, address, city, type, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

        return $stmt->execute([$rut, $business_name, $trade_name, $contact_name, $contact_email, $contact_phone, $address, $city, $type, $notes]);
    }
    // Fin de la función guardar()

    /**
     * Actualiza los datos de una empresa existente.
     */
    public function actualizar($id, $rut, $business_name, $trade_name, $contact_name, $contact_email, $contact_phone, $address, $city, $type, $notes, $active)
    {
        $stmt = $this->pdo->prepare('UPDATE `doc_companies` SET `rut`=?, `business_name`=?, `trade_name`=?, `contact_name`=?, `contact_email`=?, `contact_phone`=?, `address`=?, `city`=?, `type`=?, `notes`=?, `active`=? WHERE `id`=?');

        return $stmt->execute([$rut, $business_name, $trade_name, $contact_name, $contact_email, $contact_phone, $address, $city, $type, $notes, $active, $id]);
    }
    // Fin de la función actualizar()

    /**
     * Elimina físicamente una empresa de la base de datos.
     *
     * @param int $id ID de la empresa
     * @return bool Éxito de la operación
     */
    public function eliminar($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM doc_companies WHERE id=?');

        return $stmt->execute([$id]);
    }
    // Fin de la función eliminar()
}
