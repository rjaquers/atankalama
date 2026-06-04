<?php
/**
 * ChatModel — conversaciones, mensajes y participantes
 * PHP 7.4–8.2 compatible
 */
class ChatModel extends Model
{
    /**
     * Lista de conversaciones activas (no archivadas) del usuario,
     * con último mensaje, foto, nombre del contacto y conteo de no leídos.
     * @return array
     */
    public function getConversacionesDeUsuario(int $userId): array
    {
        $stmt = $this->conn->prepare("
            SELECT
                c.id,
                c.tipo,
                c.nombre        AS nombre_grupo,
                c.foto          AS foto_grupo,
                c.area_id,
                c.updated_at,
                p.archivada,
                p.ultimo_leido_id,
                u2.id           AS otro_usuario_id,
                u2.nombre       AS otro_nombre,
                u2.foto_perfil  AS otro_foto,
                m.id            AS ultimo_msg_id,
                m.tipo          AS ultimo_msg_tipo,
                m.contenido     AS ultimo_mensaje_contenido,
                m.created_at    AS ultimo_mensaje_at,
                mu.nombre       AS ultimo_msg_autor,
                (
                    SELECT COUNT(*)
                    FROM chat_mensajes cm
                    WHERE cm.conversacion_id = c.id
                      AND cm.usuario_id != ?
                      AND cm.eliminado = 0
                      AND (p.ultimo_leido_id IS NULL OR cm.id > p.ultimo_leido_id)
                ) AS no_leidos
            FROM chat_participantes p
            INNER JOIN chat_conversaciones c ON c.id = p.conversacion_id
            LEFT JOIN chat_participantes p2 ON p2.conversacion_id = c.id
                                           AND p2.usuario_id != ?
                                           AND c.tipo = 'individual'
            LEFT JOIN chat_usuarios u2 ON u2.id = p2.usuario_id
            LEFT JOIN chat_mensajes m ON m.id = (
                SELECT MAX(mx.id) FROM chat_mensajes mx
                WHERE mx.conversacion_id = c.id AND mx.eliminado = 0
            )
            LEFT JOIN chat_usuarios mu ON mu.id = m.usuario_id
            WHERE p.usuario_id = ?
              AND p.archivada = 0
            ORDER BY c.updated_at DESC
        ");
        $stmt->bind_param('iii', $userId, $userId, $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Conversaciones archivadas del usuario.
     * @return array
     */
    public function getConversacionesArchivadas(int $userId): array
    {
        $stmt = $this->conn->prepare("
            SELECT
                c.id,
                c.tipo,
                c.nombre        AS nombre_grupo,
                c.foto          AS foto_grupo,
                c.area_id,
                c.updated_at,
                p.archivada,
                p.ultimo_leido_id,
                u2.id           AS otro_usuario_id,
                u2.nombre       AS otro_nombre,
                u2.foto_perfil  AS otro_foto,
                m.id            AS ultimo_msg_id,
                m.tipo          AS ultimo_msg_tipo,
                m.contenido     AS ultimo_mensaje_contenido,
                m.created_at    AS ultimo_mensaje_at,
                mu.nombre       AS ultimo_msg_autor,
                0               AS no_leidos
            FROM chat_participantes p
            INNER JOIN chat_conversaciones c ON c.id = p.conversacion_id
            LEFT JOIN chat_participantes p2 ON p2.conversacion_id = c.id
                                           AND p2.usuario_id != ?
                                           AND c.tipo = 'individual'
            LEFT JOIN chat_usuarios u2 ON u2.id = p2.usuario_id
            LEFT JOIN chat_mensajes m ON m.id = (
                SELECT MAX(mx.id) FROM chat_mensajes mx
                WHERE mx.conversacion_id = c.id AND mx.eliminado = 0
            )
            LEFT JOIN chat_usuarios mu ON mu.id = m.usuario_id
            WHERE p.usuario_id = ?
              AND p.archivada = 1
            ORDER BY c.updated_at DESC
        ");
        $stmt->bind_param('ii', $userId, $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Obtiene o crea una conversación individual entre dos usuarios.
     * @return int ID de la conversación
     */
    public function getOrCreateConversacionIndividual(int $userId1, int $userId2): int
    {
        $stmt = $this->conn->prepare("
            SELECT c.id
            FROM chat_conversaciones c
            INNER JOIN chat_participantes p1 ON p1.conversacion_id = c.id AND p1.usuario_id = ?
            INNER JOIN chat_participantes p2 ON p2.conversacion_id = c.id AND p2.usuario_id = ?
            WHERE c.tipo = 'individual'
            LIMIT 1
        ");
        $stmt->bind_param('ii', $userId1, $userId2);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            return (int)$row['id'];
        }

        $stmt = $this->conn->prepare("
            INSERT INTO chat_conversaciones (tipo, creado_por) VALUES ('individual', ?)
        ");
        $stmt->bind_param('i', $userId1);
        $stmt->execute();
        $convId = (int)$this->conn->insert_id;

        $stmt = $this->conn->prepare("
            INSERT INTO chat_participantes (conversacion_id, usuario_id) VALUES (?, ?), (?, ?)
        ");
        $stmt->bind_param('iiii', $convId, $userId1, $convId, $userId2);
        $stmt->execute();

        return $convId;
    }

    /**
     * Obtiene o crea el Chat General (tipo 'sistema') y agrega al usuario como participante.
     * @return int ID de la conversación
     */
    public function getOrCreateChatGeneral(int $userId): int
    {
        // Buscar Chat General existente
        $res = $this->conn->query("
            SELECT id FROM chat_conversaciones
            WHERE tipo = 'sistema' AND nombre = 'Chat General'
            LIMIT 1
        ");

        if ($res && $row = $res->fetch_assoc()) {
            $convId = (int)$row['id'];
        } else {
            // Crear el Chat General
            $stmt = $this->conn->prepare("
                INSERT INTO chat_conversaciones (tipo, nombre, creado_por)
                VALUES ('sistema', 'Chat General', ?)
            ");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $convId = (int)$this->conn->insert_id;
        }

        // Asegurar que el usuario es participante
        $stmt = $this->conn->prepare("
            INSERT IGNORE INTO chat_participantes (conversacion_id, usuario_id) VALUES (?, ?)
        ");
        $stmt->bind_param('ii', $convId, $userId);
        $stmt->execute();

        return $convId;
    }

    /**
     * Obtiene o crea el chat grupal de un área.
     * Si ya existe, sincroniza los miembros activos del área como participantes.
     * @return int ID de la conversación
     */
    public function getOrCreateGrupoArea(int $areaId, int $creadoPor): int
    {
        // Buscar si ya existe una conversación de tipo 'area' para este área
        $stmt = $this->conn->prepare("
            SELECT id FROM chat_conversaciones
            WHERE tipo = 'area' AND area_id = ?
            LIMIT 1
        ");
        $stmt->bind_param('i', $areaId);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $row = $res->fetch_assoc()) {
            $convId = (int)$row['id'];
        } else {
            // Obtener nombre del área
            $stmtArea = $this->conn->prepare("SELECT nombre FROM chat_areas WHERE id = ? LIMIT 1");
            $stmtArea->bind_param('i', $areaId);
            $stmtArea->execute();
            $resArea = $stmtArea->get_result();
            $nombreArea = ($resArea && $rowArea = $resArea->fetch_assoc()) ? $rowArea['nombre'] : 'Área';

            // Crear la conversación de área
            $stmtIns = $this->conn->prepare("
                INSERT INTO chat_conversaciones (tipo, nombre, area_id, creado_por)
                VALUES ('area', ?, ?, ?)
            ");
            $stmtIns->bind_param('sii', $nombreArea, $areaId, $creadoPor);
            $stmtIns->execute();
            $convId = (int)$this->conn->insert_id;
        }

        // Sincronizar: agregar todos los usuarios activos del área que no sean participantes aún
        $stmtUsr = $this->conn->prepare("
            SELECT id FROM chat_usuarios WHERE area_id = ? AND estado = 1
        ");
        $stmtUsr->bind_param('i', $areaId);
        $stmtUsr->execute();
        $resUsr = $stmtUsr->get_result();
        $miembros = $resUsr ? $resUsr->fetch_all(MYSQLI_ASSOC) : [];

        $ids = array_unique(array_merge(array_column($miembros, 'id'), [$creadoPor]));
        foreach ($ids as $uid) {
            $uid  = (int)$uid;
            $stmtP = $this->conn->prepare("
                INSERT IGNORE INTO chat_participantes (conversacion_id, usuario_id) VALUES (?, ?)
            ");
            $stmtP->bind_param('ii', $convId, $uid);
            $stmtP->execute();
        }

        return $convId;
    }

    /**
     * Crea una conversación de grupo y agrega participantes.
     * @param array $participanteIds
     * @return int ID de la conversación creada
     */
    public function crearGrupo(string $nombre, int $creadoPor, array $participanteIds): int
    {
        $stmt = $this->conn->prepare("
            INSERT INTO chat_conversaciones (tipo, nombre, creado_por) VALUES ('grupo', ?, ?)
        ");
        $stmt->bind_param('si', $nombre, $creadoPor);
        $stmt->execute();
        $convId = (int)$this->conn->insert_id;

        $todos = array_unique(array_merge($participanteIds, [$creadoPor]));
        foreach ($todos as $uid) {
            $uid  = (int)$uid;
            $stmt = $this->conn->prepare("
                INSERT IGNORE INTO chat_participantes (conversacion_id, usuario_id) VALUES (?, ?)
            ");
            $stmt->bind_param('ii', $convId, $uid);
            $stmt->execute();
        }

        return $convId;
    }

    /**
     * Retorna mensajes de una conversación (paginados desde un ID base).
     * Verifica que el usuario sea participante.
     * @return array
     */
    public function getMensajes(int $convId, int $usuarioId, int $desde = 0, int $limite = 50): array
    {
        if (!$this->esParticipante($convId, $usuarioId)) {
            return [];
        }

        $stmt = $this->conn->prepare("
            SELECT
                m.id,
                m.conversacion_id,
                m.usuario_id,
                m.tipo,
                m.contenido,
                m.archivo_ruta,
                m.archivo_nombre,
                m.eliminado,
                m.created_at,
                u.nombre        AS autor_nombre,
                u.foto_perfil   AS autor_foto
            FROM chat_mensajes m
            INNER JOIN chat_usuarios u ON u.id = m.usuario_id
            WHERE m.conversacion_id = ?
              AND m.eliminado = 0
              AND m.id > ?
            ORDER BY m.created_at ASC, m.id ASC
            LIMIT ?
        ");
        $stmt->bind_param('iii', $convId, $desde, $limite);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Solo mensajes nuevos con id > $desdeId (para polling).
     * Verifica participación.
     * @return array
     */
    public function getNuevosMensajes(int $convId, int $usuarioId, int $desdeId): array
    {
        if (!$this->esParticipante($convId, $usuarioId)) {
            return [];
        }

        $stmt = $this->conn->prepare("
            SELECT
                m.id,
                m.conversacion_id,
                m.usuario_id,
                m.tipo,
                m.contenido,
                m.archivo_ruta,
                m.archivo_nombre,
                m.created_at,
                u.nombre        AS autor_nombre,
                u.foto_perfil   AS autor_foto
            FROM chat_mensajes m
            INNER JOIN chat_usuarios u ON u.id = m.usuario_id
            WHERE m.conversacion_id = ?
              AND m.eliminado = 0
              AND m.id > ?
            ORDER BY m.created_at ASC, m.id ASC
        ");
        $stmt->bind_param('ii', $convId, $desdeId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Inserta un mensaje y actualiza updated_at de la conversación.
     * @return int ID del mensaje creado
     */
    public function enviarMensaje(int $convId, int $userId, string $tipo, string $contenido, string $archivoRuta = ''): int
    {
        $ruta = ($archivoRuta !== '') ? $archivoRuta : null;
        $stmt = $this->conn->prepare("
            INSERT INTO chat_mensajes (conversacion_id, usuario_id, tipo, contenido, archivo_ruta)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('iisss', $convId, $userId, $tipo, $contenido, $ruta);
        $stmt->execute();
        $msgId = (int)$this->conn->insert_id;

        $stmt2 = $this->conn->prepare("
            UPDATE chat_conversaciones SET updated_at = NOW() WHERE id = ?
        ");
        $stmt2->bind_param('i', $convId);
        $stmt2->execute();

        return $msgId;
    }

    /**
     * Marca la conversación como leída para el usuario (actualiza ultimo_leido_id).
     * @return void
     */
    public function marcarLeido(int $convId, int $userId): void
    {
        $stmt = $this->conn->prepare("
            SELECT MAX(id) AS ultimo FROM chat_mensajes
            WHERE conversacion_id = ? AND eliminado = 0
        ");
        $stmt->bind_param('i', $convId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        if (!$row || !$row['ultimo']) {
            return;
        }

        $ultimoId = (int)$row['ultimo'];
        $stmt2    = $this->conn->prepare("
            UPDATE chat_participantes
            SET ultimo_leido_id = ?
            WHERE conversacion_id = ? AND usuario_id = ?
        ");
        $stmt2->bind_param('iii', $ultimoId, $convId, $userId);
        $stmt2->execute();
    }

    /**
     * Archiva o desarchiva una conversación para el usuario.
     * @return void
     */
    public function archivarConversacion(int $convId, int $userId, bool $archivar): void
    {
        $val  = $archivar ? 1 : 0;
        $stmt = $this->conn->prepare("
            UPDATE chat_participantes
            SET archivada = ?
            WHERE conversacion_id = ? AND usuario_id = ?
        ");
        $stmt->bind_param('iii', $val, $convId, $userId);
        $stmt->execute();
    }

    /**
     * Obtiene los datos de una conversación verificando que el usuario sea participante.
     * Para individuales agrega datos del otro participante.
     * @return array|null
     */
    public function getConversacion(int $convId, int $userId): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT
                c.id,
                c.tipo,
                c.nombre        AS nombre_grupo,
                c.foto          AS foto_grupo,
                c.area_id,
                c.creado_por,
                c.created_at,
                c.updated_at,
                p.archivada,
                p.silenciada,
                p.ultimo_leido_id,
                u2.id           AS otro_usuario_id,
                u2.nombre       AS otro_nombre,
                u2.foto_perfil  AS otro_foto,
                u2.email        AS otro_email
            FROM chat_conversaciones c
            INNER JOIN chat_participantes p ON p.conversacion_id = c.id AND p.usuario_id = ?
            LEFT JOIN chat_participantes p2 ON p2.conversacion_id = c.id
                                           AND p2.usuario_id != ?
                                           AND c.tipo = 'individual'
            LEFT JOIN chat_usuarios u2 ON u2.id = p2.usuario_id
            WHERE c.id = ?
            LIMIT 1
        ");
        $stmt->bind_param('iii', $userId, $userId, $convId);
        $stmt->execute();
        $res = $stmt->get_result();
        return ($res && $row = $res->fetch_assoc()) ? $row : null;
    }

    /**
     * Busca usuarios activos por nombre o email, excluyendo al usuario actual.
     * @return array
     */
    public function buscarUsuarios(string $q, int $excludeUserId): array
    {
        $like = '%' . $q . '%';
        $stmt = $this->conn->prepare("
            SELECT id, nombre, email, foto_perfil, area_id
            FROM chat_usuarios
            WHERE (nombre LIKE ? OR email LIKE ?)
              AND id != ?
              AND estado = 1
            LIMIT 10
        ");
        $stmt->bind_param('ssi', $like, $like, $excludeUserId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Retorna el nombre display de la conversación según el tipo y el usuario.
     * @return string
     */
    public function getNombreConversacion(array $conv, int $userId): string
    {
        if ($conv['tipo'] === 'individual') {
            return $conv['otro_nombre'] ?? $conv['nombre_grupo'] ?? 'Conversación';
        }
        return $conv['nombre_grupo'] ?? $conv['nombre'] ?? 'Grupo';
    }

    /**
     * Retorna la lista de participantes de una conversación con datos de usuario.
     * @return array
     */
    public function getParticipantes(int $convId): array
    {
        $stmt = $this->conn->prepare("
            SELECT u.id, u.nombre, u.foto_perfil
            FROM chat_participantes p
            JOIN chat_usuarios u ON u.id = p.usuario_id
            WHERE p.conversacion_id = ?
        ");
        $stmt->bind_param('i', $convId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Verifica que un usuario sea participante de una conversación.
     * @return bool
     */
    public function esParticipante(int $convId, int $userId): bool
    {
        $stmt = $this->conn->prepare("
            SELECT id FROM chat_participantes
            WHERE conversacion_id = ? AND usuario_id = ?
            LIMIT 1
        ");
        $stmt->bind_param('ii', $convId, $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res && $res->num_rows > 0;
    }

    /**
     * Retorna el total de mensajes no leídos en todas las conversaciones del usuario.
     * @return int
     */
    public function totalNoLeidos(int $userId): int
    {
        $stmt = $this->conn->prepare("
            SELECT SUM(
                (SELECT COUNT(*) FROM chat_mensajes cm
                 WHERE cm.conversacion_id = p.conversacion_id
                   AND cm.usuario_id != ?
                   AND cm.eliminado = 0
                   AND (p.ultimo_leido_id IS NULL OR cm.id > p.ultimo_leido_id)
                )
            ) AS total
            FROM chat_participantes p
            WHERE p.usuario_id = ? AND p.archivada = 0
        ");
        $stmt->bind_param('ii', $userId, $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        return $row ? (int)$row['total'] : 0;
    }

    /**
     * Retorna los push tokens (Expo) de todos los participantes de una conversación,
     * excluyendo al remitente.
     *
     * @param int $convId       ID de la conversación
     * @param int $senderUserId Usuario que envió el mensaje (no recibe su propia notificación)
     * @return string[]
     */
    public function getPushTokensForConversacion(int $convId, int $senderUserId): array
    {
        $stmt = $this->conn->prepare("
            SELECT u.fcm_token
            FROM chat_participantes p
            JOIN chat_usuarios u ON u.id = p.usuario_id
            WHERE p.conversacion_id = ?
              AND p.usuario_id != ?
              AND p.silenciada = 0
              AND u.estado = 1
              AND u.fcm_token IS NOT NULL
              AND u.fcm_token != ''
        ");
        $stmt->bind_param('ii', $convId, $senderUserId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? array_column($res->fetch_all(MYSQLI_ASSOC), 'fcm_token') : [];
    }
}
