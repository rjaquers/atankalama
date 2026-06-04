<?php
namespace App\Models;

use App\Core\Model;

class User extends Model
{
    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM " . DB_PREFIX . "usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function all()
    {
        $stmt = $this->db->query("SELECT * FROM " . DB_PREFIX . "usuarios ORDER BY email ASC");
        return $stmt->fetchAll();
    }

    public function create($email, $perfil = 'Operador')
    {
        $stmt = $this->db->prepare("INSERT INTO " . DB_PREFIX . "usuarios (email, perfil) VALUES (?, ?)");
        return $stmt->execute([$email, $perfil]);
    }

    public function updatePerfil($id, $perfil)
    {
        $stmt = $this->db->prepare("UPDATE " . DB_PREFIX . "usuarios SET perfil = ? WHERE id = ?");
        return $stmt->execute([$perfil, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM " . DB_PREFIX . "usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateLastLogin($email)
    {
        $stmt = $this->db->prepare("UPDATE " . DB_PREFIX . "usuarios SET last_login = NOW() WHERE email = ?");
        return $stmt->execute([$email]);
    }

    /**
     * Retorna usuarios activos cuyo perfil esté en el arreglo dado.
     * Usado para poblar el selector de personas en el formulario de evaluación.
     */
    public function byPerfiles(array $perfiles)
    {
        if (empty($perfiles)) return [];
        $placeholders = implode(',', array_fill(0, count($perfiles), '?'));
        $stmt = $this->db->prepare(
            "SELECT id, nombre, apellido, email, perfil FROM " . DB_PREFIX .
            "usuarios WHERE perfil IN ($placeholders) AND estado = 'activo' ORDER BY nombre ASC, apellido ASC"
        );
        $stmt->execute($perfiles);
        return $stmt->fetchAll();
    }
}
