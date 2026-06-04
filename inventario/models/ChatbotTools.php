<?php
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 *
 * Herramientas del chatbot de bodega para Claude (tool_use).
 * - definitions(): esquemas que se envían a la API de Anthropic.
 * - execute(): ejecuta la herramienta solicitada y devuelve JSON.
 *
 * READ tools  → se ejecutan automáticamente, resultado se devuelve a Claude.
 * PREVIEW tools → validan y preparan (no escriben en BD); el usuario confirma.
 */

require_once 'config/database.php';
require_once 'models/Product.php';

class ChatbotTools
{
    private $conn;
    private Product $product;

    public function __construct()
    {
        $db           = new Database();
        $this->conn   = $db->connect();
        $this->product = new Product();
    }

    // ─── Definiciones para Claude ──────────────────────────────────────────────

    public static function definitions(): array
    {
        return [
            [
                'name'        => 'buscar_producto',
                'description' => 'Busca productos en la bodega por nombre o parte del nombre. Devuelve lista con ID, nombre y stock actual. Úsalo siempre antes de proponer un ingreso o consumo.',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'nombre' => [
                            'type'        => 'string',
                            'description' => 'Nombre o parte del nombre del producto',
                        ],
                    ],
                    'required' => ['nombre'],
                ],
            ],
            [
                'name'        => 'obtener_stock',
                'description' => 'Obtiene stock actual, stock mínimo, unidad y ubicación de un producto por su ID.',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'producto_id' => [
                            'type'        => 'integer',
                            'description' => 'ID del producto',
                        ],
                    ],
                    'required' => ['producto_id'],
                ],
            ],
            [
                'name'        => 'listar_bajo_stock',
                'description' => 'Devuelve todos los productos cuyo stock es igual o menor al mínimo configurado.',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => new \stdClass(),
                ],
            ],
            [
                'name'        => 'historial_producto',
                'description' => 'Muestra el historial de movimientos recientes de un producto.',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'producto_id' => [
                            'type'        => 'integer',
                            'description' => 'ID del producto',
                        ],
                        'limite' => [
                            'type'        => 'integer',
                            'description' => 'Número de movimientos a mostrar (1-20)',
                        ],
                    ],
                    'required' => ['producto_id'],
                ],
            ],
            [
                'name'        => 'proponer_ingreso',
                'description' => 'Propone registrar un ingreso de stock (compra, llegada de proveedor, devolución). NO ejecuta el cambio; devuelve un preview para que el usuario confirme.',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'producto_id' => [
                            'type'        => 'integer',
                            'description' => 'ID del producto',
                        ],
                        'cantidad' => [
                            'type'        => 'integer',
                            'description' => 'Cantidad a ingresar',
                        ],
                        'descripcion' => [
                            'type'        => 'string',
                            'description' => 'Descripción del ingreso (ej: "Llegada proveedor", "Devolución cocina")',
                        ],
                    ],
                    'required' => ['producto_id', 'cantidad'],
                ],
            ],
            [
                'name'        => 'proponer_consumo',
                'description' => 'Propone registrar un consumo o retiro de stock. NO ejecuta el cambio; devuelve un preview para que el usuario confirme.',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'producto_id' => [
                            'type'        => 'integer',
                            'description' => 'ID del producto',
                        ],
                        'cantidad' => [
                            'type'        => 'integer',
                            'description' => 'Cantidad a consumir o retirar',
                        ],
                        'descripcion' => [
                            'type'        => 'string',
                            'description' => 'Descripción del consumo (ej: "Habitación 5", "Limpieza piscina")',
                        ],
                    ],
                    'required' => ['producto_id', 'cantidad'],
                ],
            ],
            [
                'name'        => 'proponer_deshacer',
                'description' => 'Propone deshacer el último movimiento de stock de un producto (dentro de los últimos 30 minutos). NO ejecuta nada; devuelve un preview para confirmar.',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'producto_id' => [
                            'type'        => 'integer',
                            'description' => 'ID del producto cuyo último movimiento se quiere deshacer',
                        ],
                    ],
                    'required' => ['producto_id'],
                ],
            ],
        ];
    }

    // ─── Ejecutor ──────────────────────────────────────────────────────────────

    public function execute(string $name, array $input): string
    {
        try {
            switch ($name) {
                case 'buscar_producto':
                    return $this->buscarProducto($input['nombre'] ?? '');
                case 'obtener_stock':
                    return $this->obtenerStock((int)($input['producto_id'] ?? 0));
                case 'listar_bajo_stock':
                    return $this->listarBajoStock();
                case 'historial_producto':
                    return $this->historialProducto(
                        (int)($input['producto_id'] ?? 0),
                        (int)($input['limite'] ?? 10)
                    );
                case 'proponer_ingreso':
                    return $this->proponerIngreso(
                        (int)($input['producto_id'] ?? 0),
                        (int)($input['cantidad'] ?? 0),
                        $input['descripcion'] ?? ''
                    );
                case 'proponer_consumo':
                    return $this->proponerConsumo(
                        (int)($input['producto_id'] ?? 0),
                        (int)($input['cantidad'] ?? 0),
                        $input['descripcion'] ?? ''
                    );
                case 'proponer_deshacer':
                    return $this->proponerDeshacer((int)($input['producto_id'] ?? 0));
                default:
                    return json_encode(['error' => 'Herramienta no reconocida: ' . $name]);
            }
        } catch (Exception $e) {
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    // ─── READ tools ────────────────────────────────────────────────────────────

    private function buscarProducto(string $nombre): string
    {
        if (strlen(trim($nombre)) < 2) {
            return json_encode(['error' => 'El término de búsqueda es demasiado corto']);
        }
        $resultados = $this->product->searchProducts($nombre);
        if (empty($resultados)) {
            return json_encode(['resultados' => [], 'mensaje' => 'No se encontraron productos con ese nombre']);
        }
        // Enriquecer con stock actual
        $enriquecidos = [];
        foreach ($resultados as $r) {
            $p = $this->product->getById($r['id']);
            $enriquecidos[] = [
                'id'           => $r['id'],
                'nombre'       => $r['name'],
                'stock_actual' => $p ? (int)$p['quantity'] : 0,
                'unidad'       => $p ? $p['unit'] : '',
            ];
        }
        return json_encode(['resultados' => $enriquecidos]);
    }

    private function obtenerStock(int $productoId): string
    {
        if ($productoId <= 0) {
            return json_encode(['error' => 'ID de producto inválido']);
        }
        $p = $this->product->getById($productoId);
        if (!$p) {
            return json_encode(['error' => 'Producto no encontrado']);
        }
        return json_encode([
            'id'           => (int)$p['id'],
            'nombre'       => $p['name'],
            'stock_actual' => (int)$p['quantity'],
            'stock_minimo' => (int)$p['min_stock'],
            'unidad'       => $p['unit'],
            'ubicacion'    => $p['location_name'],
            'categoria'    => $p['category_name'],
            'bajo_minimo'  => (int)$p['quantity'] <= (int)$p['min_stock'],
        ]);
    }

    private function listarBajoStock(): string
    {
        $productos = $this->product->getLowStockProducts();
        if (empty($productos)) {
            return json_encode(['productos' => [], 'mensaje' => 'Todos los productos tienen stock sobre el mínimo ✓']);
        }
        $lista = array_map(fn ($p) => [
            'id'           => (int)$p['id'],
            'nombre'       => $p['name'],
            'stock_actual' => (int)$p['quantity'],
            'stock_minimo' => (int)$p['min_stock'],
            'unidad'       => $p['unit'],
        ], $productos);
        return json_encode(['productos' => $lista, 'total' => count($lista)]);
    }

    private function historialProducto(int $productoId, int $limite): string
    {
        $limite = min(max($limite, 1), 20);
        if ($productoId <= 0) {
            return json_encode(['error' => 'ID de producto inválido']);
        }
        $logs = $this->product->getRecentLogs($productoId, $limite);
        if (empty($logs)) {
            return json_encode(['historial' => [], 'mensaje' => 'Sin movimientos registrados']);
        }
        $historial = array_map(fn ($l) => [
            'fecha'   => $l['timestamp'],
            'accion'  => $l['action'],
            'campo'   => $l['field_changed'],
            'antes'   => $l['old_value'],
            'despues' => $l['new_value'],
            'usuario' => $l['full_name'],
        ], $logs);
        return json_encode(['historial' => $historial]);
    }

    // ─── PREVIEW tools (sin escritura en BD) ───────────────────────────────────

    private function proponerIngreso(int $productoId, int $cantidad, string $descripcion): string
    {
        if ($productoId <= 0) return json_encode(['error' => 'ID de producto inválido']);
        if ($cantidad <= 0)   return json_encode(['error' => 'La cantidad debe ser mayor a cero']);

        $p = $this->product->getById($productoId);
        if (!$p) return json_encode(['error' => 'Producto no encontrado']);

        $stockDespues = (int)$p['quantity'] + $cantidad;
        $advertencia  = null;
        if ($cantidad > 100 || ((int)$p['quantity'] > 0 && $cantidad > 3 * (int)$p['quantity'])) {
            $advertencia = "La cantidad ({$cantidad}) parece elevada respecto al stock actual ({$p['quantity']}).";
        }

        return json_encode([
            'operacion'      => 'ingreso',
            'producto_id'    => (int)$p['id'],
            'producto_nombre'=> $p['name'],
            'cantidad'       => $cantidad,
            'stock_antes'    => (int)$p['quantity'],
            'stock_despues'  => $stockDespues,
            'unidad'         => $p['unit'],
            'descripcion'    => $descripcion ?: 'Ingreso vía Chatbot',
            'location_id'    => (int)$p['location_id'],
            'advertencia'    => $advertencia,
        ]);
    }

    private function proponerConsumo(int $productoId, int $cantidad, string $descripcion): string
    {
        if ($productoId <= 0) return json_encode(['error' => 'ID de producto inválido']);
        if ($cantidad <= 0)   return json_encode(['error' => 'La cantidad debe ser mayor a cero']);

        $p = $this->product->getById($productoId);
        if (!$p) return json_encode(['error' => 'Producto no encontrado']);

        if ((int)$p['quantity'] < $cantidad) {
            return json_encode([
                'error'        => 'Stock insuficiente',
                'stock_actual' => (int)$p['quantity'],
                'solicitado'   => $cantidad,
                'faltante'     => $cantidad - (int)$p['quantity'],
            ]);
        }

        $stockDespues = (int)$p['quantity'] - $cantidad;
        $advertencia  = null;
        if ($cantidad > 100) {
            $advertencia = "La cantidad ({$cantidad}) parece elevada.";
        }
        if ($stockDespues <= (int)$p['min_stock']) {
            $aviso = "⚠️ El stock quedará en {$stockDespues} (mínimo: {$p['min_stock']}).";
            $advertencia = $advertencia ? $advertencia . ' ' . $aviso : $aviso;
        }

        return json_encode([
            'operacion'      => 'consumo',
            'producto_id'    => (int)$p['id'],
            'producto_nombre'=> $p['name'],
            'cantidad'       => $cantidad,
            'stock_antes'    => (int)$p['quantity'],
            'stock_despues'  => $stockDespues,
            'unidad'         => $p['unit'],
            'descripcion'    => $descripcion ?: 'Consumo vía Chatbot',
            'advertencia'    => $advertencia,
        ]);
    }

    private function proponerDeshacer(int $productoId): string
    {
        if ($productoId <= 0) return json_encode(['error' => 'ID de producto inválido']);

        $p = $this->product->getById($productoId);
        if (!$p) return json_encode(['error' => 'Producto no encontrado']);

        $stmt = $this->conn->prepare("
            SELECT id, action, old_value, new_value, timestamp
            FROM product_logs
            WHERE product_id = ?
              AND action IN ('STOCK_ENTRY','CONSUMPTION','VOICE_IN','VOICE_OUT','CHAT_IN','CHAT_OUT')
              AND timestamp >= NOW() - INTERVAL 30 MINUTE
            ORDER BY timestamp DESC
            LIMIT 1
        ");
        $stmt->execute([$productoId]);
        $log = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$log) {
            return json_encode([
                'error' => "No hay movimientos recientes (últimos 30 min) para \"{$p['name']}\".",
            ]);
        }

        $delta           = abs((int)$log['new_value'] - (int)$log['old_value']);
        $accionOriginal  = $log['action'];
        $esIngreso       = in_array($accionOriginal, ['STOCK_ENTRY', 'VOICE_IN', 'CHAT_IN']);
        $operacionInversa = $esIngreso ? 'consumo' : 'ingreso';
        $stockDespues     = $esIngreso
            ? (int)$p['quantity'] - $delta
            : (int)$p['quantity'] + $delta;

        return json_encode([
            'operacion'       => 'deshacer',
            'producto_id'     => (int)$p['id'],
            'producto_nombre' => $p['name'],
            'cantidad'        => $delta,
            'accion_original' => $accionOriginal,
            'log_id'          => (int)$log['id'],
            'stock_actual'    => (int)$p['quantity'],
            'stock_despues'   => $stockDespues,
            'unidad'          => $p['unit'],
            'fecha_original'  => $log['timestamp'],
            'location_id'     => (int)$p['location_id'],
            'operacion_inversa' => $operacionInversa,
        ]);
    }
}
