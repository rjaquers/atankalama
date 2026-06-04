<?php
/**
 * ChatController — rutas web del módulo de chat
 * PHP 7.4–8.2 compatible
 */
class ChatController extends Controller
{
    /**
     * GET /chat — lista de conversaciones activas del usuario.
     * Auto-crea y sincroniza Chat General y Chat de Área antes de mostrar la lista.
     * @return void
     */
    public function index(): void
    {
        AuthMiddleware::check();

        $userId    = (int)$_SESSION['user_id'];
        $userAreaId = (int)($_SESSION['user_area_id'] ?? 0);
        $model     = new ChatModel();

        // Siempre garantizar participación en Chat General
        $model->getOrCreateChatGeneral($userId);

        // Garantizar participación en el chat de área (si el usuario tiene área asignada)
        if ($userAreaId > 0) {
            $model->getOrCreateGrupoArea($userAreaId, $userId);
        }

        $conversaciones = $model->getConversacionesDeUsuario($userId);

        foreach ($conversaciones as &$conv) {
            $conv['display_nombre'] = $model->getNombreConversacion($conv, $userId);
        }
        unset($conv);

        $title = 'Chat';
        $this->view('chat/index', compact('conversaciones', 'title'));
    }

    /**
     * GET /chat/conversacion/{id} — abre una conversación específica.
     * @return void
     */
    public function conversacion(string $id): void
    {
        AuthMiddleware::check();

        $convId = (int)$id;
        $userId = (int)$_SESSION['user_id'];
        $model  = new ChatModel();

        $conv = $model->getConversacion($convId, $userId);
        if (!$conv) {
            $this->redirect('/chat');
        }

        $mensajes = $model->getMensajes($convId, $userId);
        $model->marcarLeido($convId, $userId);

        $lastId = !empty($mensajes) ? (int)end($mensajes)['id'] : 0;
        $titulo = $model->getNombreConversacion($conv, $userId);
        $title  = $titulo;

        $this->view('chat/conversacion', compact('conv', 'mensajes', 'titulo', 'lastId', 'title', 'convId'));
    }

    /**
     * GET /chat/nueva — formulario para iniciar una conversación.
     * @return void
     */
    public function nueva(): void
    {
        AuthMiddleware::check();

        $title = 'Nueva conversación';
        $this->view('chat/nueva', compact('title'));
    }

    /**
     * POST /chat/iniciar — crea o abre una conversación individual con otro usuario.
     * @return void
     */
    public function iniciar(): void
    {
        AuthMiddleware::check();
        csrf_verify();

        $userId         = (int)$_SESSION['user_id'];
        $destinatarioId = (int)($_POST['usuario_id'] ?? 0);

        if ($destinatarioId === 0 || $destinatarioId === $userId) {
            $this->redirect('/chat/nueva');
        }

        // Verificar que el destinatario existe y está activo
        $userModel = new ChatUserModel();
        $destino   = $userModel->getById($destinatarioId);
        if (!$destino || !$destino['estado']) {
            $this->redirect('/chat/nueva');
        }

        $model  = new ChatModel();
        $convId = $model->getOrCreateConversacionIndividual($userId, $destinatarioId);

        $this->redirect('/chat/conversacion/' . $convId);
    }

    /**
     * POST /chat/enviar — envía un mensaje (texto o imagen).
     * Soporta AJAX (X-Requested-With: XMLHttpRequest) y envío normal.
     * @return void
     */
    public function enviar(): void
    {
        AuthMiddleware::check();
        csrf_verify();

        $userId   = (int)$_SESSION['user_id'];
        $convId   = (int)($_POST['conv_id'] ?? 0);
        $contenido = trim($_POST['contenido'] ?? '');

        if ($convId === 0) {
            if ($this->esAjax()) {
                $this->json(['ok' => false, 'msg' => 'Conversación inválida.'], 400);
            }
            $this->redirect('/chat');
        }

        $model = new ChatModel();

        // Verificar participación
        if (!$model->esParticipante($convId, $userId)) {
            if ($this->esAjax()) {
                $this->json(['ok' => false, 'msg' => 'No eres participante de esta conversación.'], 403);
            }
            $this->redirect('/chat');
        }

        $tipo = 'texto';
        $ruta = '';

        // Manejo de imagen adjunta
        if (!empty($_FILES['foto']['tmp_name'])) {
            $img  = new ImageService();
            $ruta = $img->saveAsWebp($_FILES['foto'], 'mensajes');
            $tipo = 'imagen';
        }

        // Validar que hay algo que enviar
        if ($contenido === '' && $ruta === '') {
            if ($this->esAjax()) {
                $this->json(['ok' => false, 'msg' => 'El mensaje no puede estar vacío.'], 422);
            }
            $this->redirect('/chat/conversacion/' . $convId);
        }

        $msgId = $model->enviarMensaje($convId, $userId, $tipo, $contenido, $ruta);

        // Notificación push a los demás participantes (Expo Push, best-effort)
        $senderNombre = $_SESSION['user_nombre'] ?? $_SESSION['user_email'] ?? 'Nuevo mensaje';
        $pushBody     = $tipo === 'imagen' ? '📷 Imagen' : $contenido;
        (new ExpoPushService())->notifyConversation($convId, $userId, $senderNombre, $pushBody);

        if ($this->esAjax()) {
            $this->json(['ok' => true, 'msg_id' => $msgId, 'archivo_ruta' => $ruta]);
        }

        $this->redirect('/chat/conversacion/' . $convId);
    }

    /**
     * POST /chat/archivar/{id} — toggle archivar/desarchivar conversación.
     * Los chats de área y el Chat General no se pueden archivar.
     * @return void
     */
    public function archivar(string $id): void
    {
        AuthMiddleware::check();
        csrf_verify();

        $convId = (int)$id;
        $userId = (int)$_SESSION['user_id'];
        $model  = new ChatModel();

        $conv = $model->getConversacion($convId, $userId);
        if (!$conv) {
            $this->redirect('/chat');
        }

        // Chats de área y Chat General no se pueden archivar
        if (in_array($conv['tipo'] ?? '', ['area', 'sistema'], true)) {
            $this->redirect('/chat/conversacion/' . $convId);
        }

        $estaArchivada = (bool)$conv['archivada'];
        $model->archivarConversacion($convId, $userId, !$estaArchivada);

        $this->redirect('/chat');
    }

    /**
     * GET /chat/archivadas — lista de conversaciones archivadas.
     * @return void
     */
    public function archivadas(): void
    {
        AuthMiddleware::check();

        $userId         = (int)$_SESSION['user_id'];
        $model          = new ChatModel();
        $conversaciones = $model->getConversacionesArchivadas($userId);

        foreach ($conversaciones as &$conv) {
            $conv['display_nombre'] = $model->getNombreConversacion($conv, $userId);
        }
        unset($conv);

        $title = 'Archivadas';
        $this->view('chat/index', compact('conversaciones', 'title'));
    }

    /**
     * GET /chat/grupoArea/{area_id} — abre (o crea) el chat grupal de un área.
     * Accesible para administradores y usuarios que pertenecen al área.
     * @return void
     */
    public function grupoArea(string $areaId): void
    {
        AuthMiddleware::check();

        $areaId = (int)$areaId;
        $userId = (int)$_SESSION['user_id'];
        $rolId  = (int)($_SESSION['user_rol_id'] ?? 0);

        // Verificar que el área existe
        $area = (new AreaModel())->getById($areaId);
        if (!$area || $area['estado'] !== 'activo') {
            $this->redirect('/chat');
        }

        $esAdmin = ($rolId === 1);
        $userModel = new ChatUserModel();
        $usuario   = $userModel->getById($userId);
        $perteneceAlArea = ($usuario && (int)$usuario['area_id'] === $areaId);

        if (!$esAdmin && !$perteneceAlArea) {
            $this->redirect('/chat');
        }

        $model  = new ChatModel();
        $convId = $model->getOrCreateGrupoArea($areaId, $userId);

        $this->redirect('/chat/conversacion/' . $convId);
    }

    /**
     * GET /chat/buscarUsuario?q=texto — búsqueda de usuarios para iniciar conversación.
     * @return void
     */
    public function buscarUsuario(): void
    {
        AuthMiddleware::check();

        $q      = trim($_GET['q'] ?? '');
        $userId = (int)$_SESSION['user_id'];
        $model  = new ChatModel();

        $usuarios = $model->buscarUsuarios($q, $userId);
        $this->json(['ok' => true, 'usuarios' => $usuarios]);
    }

    /**
     * Detecta si la petición es AJAX (XMLHttpRequest).
     * @return bool
     */
    private function esAjax(): bool
    {
        return (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest');
    }
}
