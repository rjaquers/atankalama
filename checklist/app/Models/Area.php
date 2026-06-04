<?php
namespace App\Models;

use App\Core\Model;

class Area extends Model
{
    /**
     * Obtiene todas las áreas registradas en el sistema.
     *
     * Qué hace:
     * - Ejecuta consulta SELECT sobre la tabla de áreas.
     * - Filtra por estado 'activo' si se solicita.
     * - Ordena alfabéticamente por nombre.
     *
     * @param bool $onlyActive Indica si se deben retornar solo las áreas activas.
     * @return array Listado de áreas encontradas.
     *
     * Variables usadas:
     * - $this->db
     */
    public function all($onlyActive = false)
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "areas " . ($onlyActive ? "WHERE estado = 'activo' " : "") . "ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll();
    }
    // Fin de la función all()

    /**
     * Busca un área específica por su ID.
     *
     * Qué hace:
     * - Prepara y ejecuta sentencia SELECT filtrando por ID.
     * - Retorna el registro como un array asociativo.
     *
     * @param int $id ID del área a buscar.
     * @return array|false Datos del área o false si no existe.
     */
    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM " . DB_PREFIX . "areas WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    // Fin de la función find()

    /**
     * Registra una nueva área en el sistema.
     *
     * Qué hace:
     * - Inserta nombre y descripción en la tabla de áreas.
     *
     * @param string $nombre Nombre del área.
     * @param string $descripcion Descripción opcional del área.
     * @return bool True si se creó correctamente, false en caso contrario.
     */
    public function create($nombre, $descripcion = '')
    {
        $stmt = $this->db->prepare("INSERT INTO " . DB_PREFIX . "areas (nombre, descripcion) VALUES (?, ?)");
        return $stmt->execute([$nombre, $descripcion]);
    }
    // Fin de la función create()

    /**
     * Actualiza los datos de un área existente.
     *
     * Qué hace:
     * - Actualiza nombre, descripción y estado del registro especificado.
     *
     * @param int $id ID del área.
     * @param string $nombre Nuevo nombre.
     * @param string $descripcion Nueva descripción.
     * @param string $estado Nuevo estado (activo/inactivo).
     * @return bool True si se actualizó, false en caso contrario.
     */
    public function update($id, $nombre, $descripcion, $estado)
    {
        $stmt = $this->db->prepare("UPDATE " . DB_PREFIX . "areas SET nombre = ?, descripcion = ?, estado = ? WHERE id = ?");
        return $stmt->execute([$nombre, $descripcion, $estado, $id]);
    }
    // Fin de la función update()

    /**
     * Elimina físicamente un área del sistema.
     *
     * Qué hace:
     * - Ejecuta sentencia DELETE sobre el registro especificado.
     *
     * @param int $id ID del área a eliminar.
     * @return bool True si se eliminó, false en caso contrario.
     */
    public function delete($id)
    {
        // Podríamos hacer soft delete cambiando el estado a 'inactivo'
        $stmt = $this->db->prepare("DELETE FROM " . DB_PREFIX . "areas WHERE id = ?");
        return $stmt->execute([$id]);
    }
    // Fin de la función delete()
}
