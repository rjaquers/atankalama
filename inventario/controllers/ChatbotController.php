<?php
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 *
 * Controlador del chatbot de bodega.
 * Flujo: index (vista) → process (loop tool_use con Claude) → confirm (ejecuta operación).
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/AnthropicHttpClient.php';
require_once __DIR__ . '/../models/ChatbotMessage.php';
require_once __DIR__ . '/../models/ChatbotTools.php';
require_once __DIR__ . '/../models/ConsumptionEvent.php';
require_once __DIR__ . '/../models/StockEntry.php';
require_once __DIR__ . '/../models/Product.php';

class ChatbotController
{
    private AnthropicHttpClient $claude;
    private ChatbotMessage $msgModel;
    private ChatbotTools $tools;
    private $conn;

    // Herramientas de PREVIEW: no escriben en BD; el usuario debe confirmar
    private const PREVIEW_TOOLS = ['proponer_ingreso', 'proponer_consumo', 'proponer_deshacer'];

    private const SYSTEM_PROMPT =
        "Eres el asistente de bodega del Hotel Atankalama. Ayudas al equipo a registrar ingresos y consumos de productos, y a consultar stock.\n\n" .
        "REGLAS CRÍTICAS:\n" .
        "1. Responde siempre en castellano chileno, conciso y amable.\n" .
        "2. ANTES de cualquier ingreso o consumo, usa buscar_producto para identificar el producto correctamente.\n" .
        "3. ANTES de ejecutar cualquier escritura (ingresar, consumir, deshacer), usa la herramienta proponer_* correspondiente. NUNCA confirmes tú la operación; espera que el usuario confirme explícitamente con el botón o diciendo 'sí'.\n" .
        "4. Si la cantidad parece exagerada (más de 100 unidades o más de 3 veces el stock actual), advierte al usuario antes de proponer.\n" .
        "5. Si el mensaje es ambiguo (ej: 'tengo 50 cajas'), PREGUNTA antes de actuar: '¿Quieres registrar un ingreso de 50 unidades o solo estás consultando?'\n" .
        "6. Si no encuentras el producto, sugiere alternativas con buscar_producto o indica que puede crearlo desde la app.\n" .
        "7. Para deshacer: solo aplica al último movimiento en los últimos 30 minutos del producto específico.\n" .
        "8. Cuando el usuario saluda o conversa sin pedir nada, responde brevemente sin usar herramientas.";

    public function __construct()
    {
        $db           = new Database();
        $this->conn   = $db->connect();
        $apiKey       = defined('ANTHROPIC_API_KEY') ? ANTHROPIC_API_KEY : (string)getenv('ANTHROPIC_API_KEY');
        $model        = defined('CHATBOT_MODEL')     ? CHATBOT_MODEL     : 'claude-sonnet-4-6';
        $this->claude   = new AnthropicHttpClient($apiKey);
        $this->msgModel = new ChatbotMessage();
        $this->tools    = new ChatbotTools();
    }

    // ─── Rutas públicas ─────────────────────────────────────────────────────────

    public function index(): void
    {
        requireLogin();

        $sessionId = $this->msgModel->getOrCreateSessionId($_SESSION['user_id']);
        $mensajes  = $this->msgModel->getDisplayMessages($sessionId);

        $page_title = 'Asistente de Bodega';
        $view       = __DIR__ . '/../views/chatbot/index.php';
        require_once __DIR__ . '/../views/layout/home.php';
    }

    public function process(): void
    {
        requireLogin();
        header('Content-Type: application/json; charset=utf-8');

        $data  = $this->readJsonBody();
        $texto = trim($data['texto'] ?? '');

        if ($texto === '') {
            echo json_encode(['status' => 'error', 'mensaje' => 'Mensaje vacío']);
            exit;
        }

        $userId    = (int)$_SESSION['user_id'];
        $sessionId = $this->msgModel->getOrCreateSessionId($userId);

        // 1. Guardar mensaje del usuario
        $this->msgModel->saveTurn($sessionId, $userId, 'user', $texto);

        // 2. Cargar historial para el contexto
        $messages = $this->msgModel->getRecentMessages($sessionId, 30);

        // 3. Loop de tool_use (máximo 5 iteraciones para evitar bucles)
        $pendiente     = null;
        $respuestaFinal = '';
        $model         = defined('CHATBOT_MODEL') ? CHATBOT_MODEL : 'claude-sonnet-4-6';

        try {
            for ($i = 0; $i < 5; $i++) {
                $response   = $this->claude->createMessage([
                    'model'      => $model,
                    'max_tokens' => 1024,
                    'system'     => self::SYSTEM_PROMPT,
                    'messages'   => $messages,
                    'tools'      => ChatbotTools::definitions(),
                ]);

                $stopReason = $response['stop_reason'] ?? 'end_turn';
                $content    = $response['content'] ?? [];

                if ($stopReason !== 'tool_use') {
                    // Respuesta textual final
                    $respuestaFinal = $this->extractText($content);
                    $this->msgModel->saveTurn($sessionId, $userId, 'assistant', $content);
                    break;
                }

                // Guardar turno del asistente con tool_use
                $this->msgModel->saveTurn($sessionId, $userId, 'assistant', $content);
                $messages[] = ['role' => 'assistant', 'content' => $content];

                // Ejecutar todas las herramientas del turno
                $toolResults = [];
                foreach ($content as $block) {
                    if (($block['type'] ?? '') !== 'tool_use') continue;

                    $toolName  = $block['name'];
                    $toolInput = $block['input'] ?? [];
                    $toolId    = $block['id'];

                    $result = $this->tools->execute($toolName, $toolInput);

                    // Capturar pendiente si es herramienta PREVIEW sin error
                    if (in_array($toolName, self::PREVIEW_TOOLS)) {
                        $decoded = json_decode($result, true);
                        if ($decoded && !isset($decoded['error'])) {
                            $pendiente = $decoded;
                        }
                    }

                    $toolResults[] = [
                        'type'        => 'tool_result',
                        'tool_use_id' => $toolId,
                        'content'     => $result,
                    ];
                }

                // Guardar resultados de herramientas como turno de usuario
                $this->msgModel->saveTurn($sessionId, $userId, 'user', $toolResults);
                $messages[] = ['role' => 'user', 'content' => $toolResults];
            }
        } catch (RuntimeException $e) {
            error_log('[Chatbot] Error API: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'mensaje' => 'No pude procesar tu mensaje. Por favor intenta de nuevo.']);
            exit;
        }

        echo json_encode([
            'status'    => 'ok',
            'respuesta' => $respuestaFinal,
            'pendiente' => $pendiente,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function confirm(): void
    {
        requireLogin();
        header('Content-Type: application/json; charset=utf-8');

        $data      = $this->readJsonBody();
        $operacion = $data['operacion'] ?? '';
        $userId    = (int)$_SESSION['user_id'];
        $sessionId = $this->msgModel->getOrCreateSessionId($userId);

        if (!in_array($operacion, ['ingreso', 'consumo', 'deshacer'])) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Operación no reconocida']);
            exit;
        }

        $productoId  = (int)($data['producto_id'] ?? 0);
        $cantidad    = (int)($data['cantidad'] ?? 0);
        $descripcion = trim($data['descripcion'] ?? 'Registrado vía Chatbot');

        if ($productoId <= 0 || $cantidad <= 0) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Datos de operación inválidos']);
            exit;
        }

        $resultado = false;
        $mensaje   = '';

        switch ($operacion) {
            case 'ingreso':
                $locationId = (int)($data['location_id'] ?? 1);
                $model      = new StockEntry();
                $resultado  = $model->create($productoId, $userId, $cantidad, $locationId, $descripcion);
                $mensaje    = $resultado ? '✓ Ingreso registrado correctamente.' : 'Error al registrar el ingreso.';
                break;

            case 'consumo':
                $model     = new ConsumptionEvent();
                $resultado = $model->create($productoId, $userId, $cantidad, 'Chatbot', $descripcion);
                $mensaje   = $resultado ? '✓ Consumo registrado correctamente.' : 'Error: sin stock suficiente o producto no encontrado.';
                break;

            case 'deshacer':
                $resultado = $this->ejecutarDeshacer(
                    $productoId,
                    $cantidad,
                    (int)($data['log_id'] ?? 0),
                    $data['operacion_inversa'] ?? '',
                    (int)($data['location_id'] ?? 1),
                    $userId
                );
                $mensaje = $resultado ? '✓ Movimiento deshecho correctamente.' : 'Error al deshacer (puede haber expirado la ventana de 30 min o stock insuficiente).';
                break;
        }

        // Registrar la confirmación en el historial del chat
        $confirmText = "Operación confirmada: " . ucfirst($operacion) . " de {$cantidad} unidades (producto #{$productoId})";
        $this->msgModel->saveTurn($sessionId, $userId, 'user', $confirmText);

        // Stock actualizado
        $product      = new Product();
        $p            = $product->getById($productoId);
        $stockActual  = $p ? (int)$p['quantity'] : null;

        echo json_encode([
            'status'       => $resultado ? 'done' : 'error',
            'mensaje'      => $mensaje,
            'stock_actual' => $stockActual,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function reset(): void
    {
        requireLogin();
        header('Content-Type: application/json; charset=utf-8');
        unset($_SESSION['chatbot_session_id']);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    // ─── Helpers privados ───────────────────────────────────────────────────────

    private function ejecutarDeshacer(int $productoId, int $cantidad, int $logId, string $operacionInversa, int $locationId, int $userId): bool
    {
        if (!in_array($operacionInversa, ['ingreso', 'consumo'])) return false;

        try {
            $this->conn->beginTransaction();

            // Verificar que el log sigue dentro de la ventana de 30 min
            $stmt = $this->conn->prepare(
                'SELECT id FROM product_logs
                 WHERE id = ? AND product_id = ? AND timestamp >= NOW() - INTERVAL 30 MINUTE'
            );
            $stmt->execute([$logId, $productoId]);
            if (!$stmt->fetch()) {
                $this->conn->rollBack();
                return false;
            }

            // Bloquear fila del producto
            $stmt = $this->conn->prepare('SELECT quantity FROM products WHERE id = ? FOR UPDATE');
            $stmt->execute([$productoId]);
            $prod = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$prod) {
                $this->conn->rollBack();
                return false;
            }

            $stockActual = (int)$prod['quantity'];

            if ($operacionInversa === 'consumo') {
                // Deshace un ingreso → consumir la diferencia
                if ($stockActual < $cantidad) {
                    $this->conn->rollBack();
                    return false;
                }
                $nuevaCantidad = $stockActual - $cantidad;
            } else {
                // Deshace un consumo → devolver la diferencia
                $nuevaCantidad = $stockActual + $cantidad;
            }

            $this->conn->prepare('UPDATE products SET quantity = ? WHERE id = ?')
                ->execute([$nuevaCantidad, $productoId]);

            $this->conn->prepare(
                "INSERT INTO product_logs (product_id, user_id, action, field_changed, old_value, new_value)
                 VALUES (?, ?, 'CHAT_UNDO', 'quantity', ?, ?)"
            )->execute([$productoId, $userId, $stockActual, $nuevaCantidad]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log('[Chatbot] Error deshacer: ' . $e->getMessage());
            return false;
        }
    }

    private function extractText(array $content): string
    {
        $text = '';
        foreach ($content as $block) {
            if (is_string($block)) {
                $text .= $block;
            } elseif (($block['type'] ?? '') === 'text') {
                $text .= $block['text'] ?? '';
            }
        }
        return $text;
    }

    private function readJsonBody(): array
    {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
