<?php
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 * Este software es propiedad exclusiva de su autor.
 *
 * @author  Rodrigo Jaque Escobar
 * @project Sistema de Precios - Hotel Atankalama
 */

class PrecioModel
{
    private PDO $pdo;

    public function __construct()
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/shared/acceso_db.php';
        $this->pdo = acceso_pdo();
    }

    // ── Categorías (columnas de la tabla) ────────────────────────────────────

    /** Todas las categorías activas ordenadas */
    public function getCategorias(): array
    {
        return $this->pdo->query(
            "SELECT * FROM pre_categorias WHERE activo = 1 ORDER BY orden, nombre"
        )->fetchAll();
    }

    /** Todas las categorías (incluyendo inactivas) para el admin */
    public function getAllCategorias(): array
    {
        return $this->pdo->query(
            "SELECT * FROM pre_categorias ORDER BY orden, nombre"
        )->fetchAll();
    }

    public function guardarCategoria(string $nombre, int $orden, ?int $id = null): int
    {
        if ($id) {
            $this->pdo->prepare(
                "UPDATE pre_categorias SET nombre = :n, orden = :o WHERE id = :id"
            )->execute([':n' => $nombre, ':o' => $orden, ':id' => $id]);
            return $id;
        }
        $this->pdo->prepare(
            "INSERT INTO pre_categorias (nombre, orden, activo) VALUES (:n, :o, 1)"
        )->execute([':n' => $nombre, ':o' => $orden]);
        return (int)$this->pdo->lastInsertId();
    }

    public function toggleCategoria(int $id, int $activo): void
    {
        $this->pdo->prepare(
            "UPDATE pre_categorias SET activo = :a WHERE id = :id"
        )->execute([':a' => $activo, ':id' => $id]);
    }

    // ── Tipos de habitación (filas de la tabla) ───────────────────────────────

    /** Todos los tipos activos ordenados */
    public function getTipos(): array
    {
        return $this->pdo->query(
            "SELECT * FROM pre_tipos WHERE activo = 1 ORDER BY orden, nombre"
        )->fetchAll();
    }

    /** Todos los tipos (incluyendo inactivos) para el admin */
    public function getAllTipos(): array
    {
        return $this->pdo->query(
            "SELECT * FROM pre_tipos ORDER BY orden, nombre"
        )->fetchAll();
    }

    public function guardarTipo(string $nombre, int $orden, ?int $id = null): int
    {
        if ($id) {
            $this->pdo->prepare(
                "UPDATE pre_tipos SET nombre = :n, orden = :o WHERE id = :id"
            )->execute([':n' => $nombre, ':o' => $orden, ':id' => $id]);
            return $id;
        }
        $this->pdo->prepare(
            "INSERT INTO pre_tipos (nombre, orden, activo) VALUES (:n, :o, 1)"
        )->execute([':n' => $nombre, ':o' => $orden]);
        return (int)$this->pdo->lastInsertId();
    }

    public function toggleTipo(int $id, int $activo): void
    {
        $this->pdo->prepare(
            "UPDATE pre_tipos SET activo = :a WHERE id = :id"
        )->execute([':a' => $activo, ':id' => $id]);
    }

    // ── Precios (celdas de la grilla) ────────────────────────────────────────

    /**
     * Retorna la grilla como array indexado [tipo_id][categoria_id] => precio
     */
    public function getGrilla(): array
    {
        $rows = $this->pdo->query(
            "SELECT tipo_id, categoria_id, precio FROM pre_precios"
        )->fetchAll();

        $grilla = [];
        foreach ($rows as $row) {
            $grilla[$row['tipo_id']][$row['categoria_id']] = $row['precio'];
        }
        return $grilla;
    }

    public function actualizarPrecio(int $tipoId, int $catId, string $precio): void
    {
        $this->pdo->prepare("
            INSERT INTO pre_precios (tipo_id, categoria_id, precio)
            VALUES (:t, :c, :p)
            ON DUPLICATE KEY UPDATE precio = :p2, updated_at = NOW()
        ")->execute([
            ':t'  => $tipoId,
            ':c'  => $catId,
            ':p'  => trim($precio),
            ':p2' => trim($precio),
        ]);
    }
}
