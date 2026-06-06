<?php
/**
 * Model Base - Atankalama Empresas
 */
class Model
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }
}
