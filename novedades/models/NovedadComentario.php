<?php

class NovedadComentario
{
    private PDO $pdo;

    /**
     * Constructor del modelo NovedadComentario
     *
     * @param PDO $pdo Conexión a base de datos
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    // Fin de la función __construct()

    /**
     * Agrega un comentario de seguimiento a una novedad.
     *
     * Qué hace:
     * - Limpia datos trim()
     * - Valida campos obligatorios
     * - Trunca comentario a 500 caracteres (límite BD)
     * - Inserta en tabla nov_seguimiento_comentarios
     *
     * @param int $novedadId ID de la novedad vinculada
     * @param string $autor Nombre de quien comenta
     * @param string $comentario Texto del comentario
     * @return bool Éxito de la operación
     */
    public function agregar(int $novedadId, string $autor, string $comentario): bool
    {
        $autor = trim($autor);
        $comentario = trim($comentario);

        if ($novedadId <= 0 || $autor === '' || $comentario === '') {
            return false;
        }

        // Respetar largo BD (varchar 500)
        if (mb_strlen($comentario) > 500) {
            $comentario = mb_substr($comentario, 0, 500);
        }

        $sql = '
            INSERT INTO `nov_seguimiento_comentarios`
                (`novedad_id`, `autor`, `comentario`)
            VALUES
                (?, ?, ?)
        ';

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([$novedadId, $autor, $comentario]);
    }
    // Fin de la función agregar()

    /**
     * Lista comentarios de seguimiento de una novedad.
     *
     * @param int $novedadId ID de la novedad
     * @return array Lista de comentarios (autor, comentario, creado_at)
     */
    public function listarPorNovedad(int $novedadId): array
    {
        if ($novedadId <= 0) {
            return [];
        }

        $sql = '
            SELECT `autor`, `comentario`, `creado_at`
            FROM `nov_seguimiento_comentarios`
            WHERE `novedad_id` = ?
            ORDER BY `creado_at` ASC
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$novedadId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    // Fin de la función listarPorNovedad()
}
