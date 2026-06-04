<?php
/**
 * Proyecto: Starter Kit RKM
 * Autor: Rodrigo Jaque Escobar
 * Contacto: rjaquers@gmail.com
 */
class Model
{
    protected $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }
}
