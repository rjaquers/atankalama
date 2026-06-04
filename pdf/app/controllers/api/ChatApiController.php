<?php
/**
 * ChatApiController — API REST del módulo de chat
 * Autenticación mediante JWT (AuthMiddleware::api())
 * PHP 7.4–8.2 compatible
 */
class ChatApiController extends Controller
{
    /**
     * GET /api/chat — lista las conversaciones activas del usuario autenticado.
     * @return void
     */
    public function index(): void
    {
        $payload = AuthMiddleware::api();
        $userId  = (int)($payload['uid'] ?? 0);

        $model          = new ChatModel();
        $conversaciones = $model->getConversacionesDeUsuario($userId);

        foreach ($conversaciones as &$conv) {
            $conv['display_nombre'] = $model->getNombreConversacion($conv, $userId);
            $conv['ultimo_mensaje'] = $conv['ultimo_mensaje_contenido'] ?? null;
        }
        unset($conv);

        $this->json(['ok' => true, 'conversaciones' => $conversaciones]);
    }

    /**
     * GET /api/chat/conversaciones — alias de index().
     * @return void
     */
    public function conversaciones(): void
    {
        $this->index();
    }

    /**
     * GET /api/chat/poll?conv=ID&since=LAST_ID
     * Retorna los mensajes nuevos desde el ID indicado.
     * @return void
     */
    public function poll(): void
    {
        $payload = AuthMiddleware::api();
        $userId  = (int)($payload['uid'] ?? 0);

        $convId  = (int)($_GET['conv']  ?? 0);
        $desdeId = (int)($_GET['since'] ?? 0);

        if ($convId === 0) {
            $this->json(['ok' => false, 'msg' => 'Parámetro conv requerido.'], 400);
        }

        $model    = new ChatModel();
        $mensajes = $model->getNuevosMensajes($convId, $userId, $desdeId);

        // Si hay mensajes, marcar como leídos automáticamente
        if (!empty($mensajes)) {
            $model->marcarLeido($convId, $userId);
        }

        $this->json(['ok' => true, 'mensajes' => $mensajes]);
    }

    /**
     * POST /api/chat/enviar — envía un mensaje de texto.
     * Body JSON: { conv_id, contenido, tipo? }
     * @return void
     */
    public function enviar(): void
    {
        $payload = AuthMiddleware::api();
        $userId  = (int)($payload['uid'] ?? 0);

        // Aceptar JSON o form-data
        $input    = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $convId   = (int)($input['conv_id']   ?? 0);
        $contenido = trim($input['contenido'] ?? '');
        $tipo     = in_array($input['tipo'] ?? 'texto', ['texto', 'imagen', 'archivo', 'sistema'], true)
                    ? $input['tipo']
                    : 'texto';

        if ($convId === 0) {
            $this->json(['ok' => false, 'msg' => 'conv_id requerido.'], 400);
        }

        $model = new ChatModel();

        if (!$model->esParticipante($convId, $userId)) {
            $this->json(['ok' => false, 'msg' => 'No eres participante de esta conversación.'], 403);
        }

        if ($contenido === '') {
            $this->json(['ok' => false, 'msg' => 'El contenido no puede estar vacío.'], 422);
        }

        $msgId = $model->enviarMensaje($convId, $userId, $tipo, $contenido, '');

        // Enviar notificación push a los demás participantes (asíncrono best-effort)
        $senderNombre = $payload['nombre'] ?? 'Nuevo mensaje';
        (new ExpoPushService())->notifyConversation($convId, $userId, $senderNombre, $contenido);

        $this->json(['ok' => true, 'msg_id' => $msgId]);
    }

    /**
     * POST /api/chat/iniciar — crea o recupera conversación individual con otro usuario.
     * Body JSON: { "usuario_id": 5 }
     * @return void
     */
    public function iniciar(): void
    {
        $payload        = AuthMiddleware::api();
        $userId         = (int)($payload['uid'] ?? 0);
        $input          = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $destinatarioId = (int)($input['usuario_id'] ?? 0);

        if ($destinatarioId === 0 || $destinatarioId === $userId) {
            $this->json(['ok' => false, 'msg' => 'Usuario destinatario inválido.'], 400);
        }

        $model  = new ChatModel();
        $convId = $model->getOrCreateConversacionIndividual($userId, $destinatarioId);

        $this->json(['ok' => true, 'conv_id' => $convId]);
    }

    /**
     * GET /api/chat/buscar?q=texto — busca usuarios para iniciar conversación.
     * @return void
     */
    public function buscar(): void
    {
        $payload = AuthMiddleware::api();
        $userId  = (int)($payload['uid'] ?? 0);

        $q        = trim($_GET['q'] ?? '');
        $model    = new ChatModel();
        $usuarios = $model->buscarUsuarios($q, $userId);

        $this->json(['ok' => true, 'usuarios' => $usuarios]);
    }

    /**
     * GET /api/chat/unread — total de mensajes no leídos del usuario.
     * Usado por la app móvil para actualizar el badge del ícono.
     */
    public function unread(): void
    {
        $payload = AuthMiddleware::api();
        $total   = (new ChatModel())->totalNoLeidos((int)($payload['uid'] ?? 0));
        $this->json(['ok' => true, 'total' => $total]);
    }
}
