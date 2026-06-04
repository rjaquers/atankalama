<?php
/*
  ===================================================
  = Proyecto: Hotel Atankalama - Sistema de Cocina  =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com.                   =
  = Fecha: <?= date('Y') ?>                  =
  ===================================================
*/

/**
 * Resumen:
 * Modelo del módulo "Operación por Voz".
 * - Analiza texto dictado (cantidad, verbo, producto probable).
 * - Aplica cambios confirmados (update stock + logs + consumo si retiro).
 * Compatible PHP 7.4.
 */
class VoiceStockModel
{
    private $conn;

    // Umbrales de coincidencia (ajustables)
    private $MIN_SCORE_OK = 60;     // match directo aceptable

    private $MIN_SCORE_SUGGEST = 40; // sugerir pero advertir

    // Verbos válidos por acción (para validación coherencia)
    private $VERBOS_INGRESO = ['agrego', 'agregue', 'compré', 'compre'];

    private $VERBOS_RETIRO = ['retire', 'retiré', 'saque', 'saqué'];

    public function __construct($db)
    {
        /**
         * Constructor del modelo.
         * Parámetros: $db (PDO)
         * Devuelve: nada
         * Usa: $this->conn
         */
        $this->conn = $db;
    } // fin __construct()

    public function analyzeVoice($texto, $accionBoton)
    {
        /**
         * Analiza texto + acción definida por botón y propone operación sin ejecutar.
         * Parámetros:
         * - $texto: string dictado
         * - $accionBoton: 'ingreso'|'retiro'
         * Devuelve: array JSON-friendly con {status, ...}
         * Usa: normalización, extracción, búsqueda producto
         */
        $texto = trim((string)$texto);
        $accionBoton = trim((string)$accionBoton);

        if ($accionBoton !== 'ingreso' && $accionBoton !== 'retiro') {
            return $this->err('Acción inválida (botón).');
        }

        if ($texto === '') {
            return $this->err('Texto vacío.');
        }

        $textoNorm = $this->normalize($texto);

        $cantidad = $this->extractQuantity($textoNorm);
        if ($cantidad <= 0) {
            return $this->err('No se detectó una cantidad numérica válida (usa número, ej: "15").');
        }

        $verboDetectado = $this->detectVerb($textoNorm);
        $coherencia = $this->validateVerbCoherence($verboDetectado, $accionBoton);

        $productoQuery = $this->extractProductQuery($textoNorm, $cantidad, $verboDetectado);
        if (mb_strlen($productoQuery) < 2) {
            return $this->err('No se detectó el nombre del producto.');
        }

        $match = $this->findBestMatchProduct($productoQuery);

        if (!$match) {
            return [
                'status' => 'not_found',
                'mensaje' => 'No se encontraron productos similares.'
            ];
        }

        if ($match['type'] === 'suggestions') {
            return [
                'status' => 'suggestions',
                'accion' => $accionBoton,
                'cantidad' => $cantidad,
                'texto_original' => $texto,
                'verbo_detectado' => $verboDetectado,
                'sugerencias' => $match['data']
            ];
        }

        if ($match['type'] === 'best') {
            $best = $match['data'];

            return [
                'status' => 'ok',
                'accion' => $accionBoton,
                'cantidad' => $cantidad,
                'texto_original' => $texto,
                'verbo_detectado' => $verboDetectado,
                'producto' => $best
            ];
        }


        //if (! $best) {
        //    // No hay candidatos -> ofrecer crear producto nuevo
        //    return [
        //        'status' => 'not_found',
        //        'accion' => $accionBoton,
        //        'cantidad' => $cantidad,
        //        'texto_original' => $texto,
        //        'texto_normalizado' => $textoNorm,
        //        'verbo_detectado' => $verboDetectado,
        //        'producto_query' => $productoQuery,
        //        'mensaje' => 'No se encontró un producto parecido. ¿Deseas registrarlo como nuevo producto?',
        //    ];
        //}

        // Preparar propuesta
        $warn = null;
        if ($best['score'] < $this->MIN_SCORE_OK) {
            $warn = 'Coincidencia no perfecta. Revisa antes de confirmar.';
        }
        if ($coherencia['status'] === 'warn') {
            $warn = ($warn ? ($warn.' ') : '').$coherencia['mensaje'];
        }

        return [
            'status' => 'ok',
            'accion' => $accionBoton,
            'cantidad' => $cantidad,
            'texto_original' => $texto,
            'texto_normalizado' => $textoNorm,
            'verbo_detectado' => $verboDetectado,
            'producto_query' => $productoQuery,
            'producto' => [
                'id' => (int)$best['id'],
                'name' => $best['name'],
                'quantity' => (int)$best['quantity'],
                'score' => (int)$best['score'],
            ],
            'warning' => $warn,
        ];
    } // fin analyzeVoice()

    public function applyConfirmedOperation($productoId, $cantidad, $accion, $userId, $textoOriginal, $verboDetectado)
    {
        /**
         * Aplica operación confirmada (update stock + logs + consumo si retiro).
         * Parámetros:
         * - $productoId int
         * - $cantidad int
         * - $accion 'ingreso'|'retiro'
         * - $userId int (desde sesión)
         * - $textoOriginal string (auditoría)
         * - $verboDetectado string (auditoría / coherencia)
         * Devuelve: array con resultado final (antes/después) o error
         * Usa: transacción PDO
         */
        if ($productoId <= 0) {
            return $this->err('Producto inválido.');
        }
        if ($cantidad <= 0) {
            return $this->err('Cantidad inválida.');
        }
        if ($accion !== 'ingreso' && $accion !== 'retiro') {
            return $this->err('Acción inválida.');
        }
        if ($userId <= 0) {
            return $this->err('Usuario inválido o sesión no válida.');
        }

        try {
            $this->conn->beginTransaction();

            $product = $this->getProductByIdForUpdate($productoId);
            if (! $product) {
                $this->conn->rollBack();

                return $this->err('Producto no existe.');
            }

            $before = (int)$product['quantity'];
            $after = $before;

            if ($accion === 'ingreso') {
                $after = $before + $cantidad;
            } else { // retiro
                if ($cantidad > $before) {
                    $this->conn->rollBack();

                    return $this->err('Stock insuficiente para retiro.');
                }
                $after = $before - $cantidad;
            }

            $this->updateProductQuantity($productoId, $after);

            // Log principal de cantidad
            $this->insertProductLog(
                $productoId,
                $userId,
                ($accion === 'ingreso' ? 'VOICE_IN' : 'VOICE_OUT'),
                'quantity',
                (string)$before,
                (string)$after
            );

            // Log extra: texto de voz (sin tocar schema)
            $this->insertProductLog(
                $productoId,
                $userId,
                'VOICE_TEXT',
                'voice_text',
                '',
                trim((string)$textoOriginal)
            );

            // Si retiro: registrar consumption_event
            if ($accion === 'retiro') {
                $this->insertConsumptionEvent($productoId, $userId, $cantidad, trim((string)$textoOriginal));
            }

            $this->conn->commit();

            return [
                'status' => 'done',
                'accion' => $accion,
                'producto' => [
                    'id' => (int)$productoId,
                    'name' => $product['name'],
                ],
                'cantidad' => (int)$cantidad,
                'stock_antes' => (int)$before,
                'stock_despues' => (int)$after,
                'verbo_detectado' => (string)$verboDetectado,
            ];
        } catch (\Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            return $this->err('Error interno al aplicar operación.');
        }
    } // fin applyConfirmedOperation()

    private function normalize($text)
    {
        /**
         * Normaliza texto para matching:
         * - lower
         * - quita tildes básicas
         * - colapsa espacios
         * Parámetros: $text string
         * Devuelve: string normalizado
         */
        $t = mb_strtolower((string)$text);

        // Quitar tildes (simple y suficiente para este caso)
        $map = [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ü' => 'u',
            'ñ' => 'n',
            'Á' => 'a',
            'É' => 'e',
            'Í' => 'i',
            'Ó' => 'o',
            'Ú' => 'u',
            'Ü' => 'u',
            'Ñ' => 'n'
        ];
        $t = strtr($t, $map);

        // Reemplazar caracteres raros por espacio
        $t = preg_replace('/[^a-z0-9\s]/u', ' ', $t);

        // Colapsar espacios
        $t = preg_replace('/\s+/', ' ', $t);

        return trim($t);
    } // fin normalize()

    private function detectVerb($textoNorm)
    {
        /**
         * Detecta verbo presente en el texto (si existe).
         * Parámetros: $textoNorm string normalizado
         * Devuelve: string verbo detectado o ''
         */
        $words = explode(' ', $textoNorm);

        foreach ($words as $w) {
            if (in_array($w, $this->VERBOS_INGRESO, true) || in_array($w, $this->VERBOS_RETIRO, true)) {
                return $w;
            }
        }

        return '';
    } // fin detectVerb()

    private function validateVerbCoherence($verboDetectado, $accionBoton)
    {
        /**
         * Valida coherencia entre verbo detectado y acción del botón.
         * Parámetros:
         * - $verboDetectado string
         * - $accionBoton 'ingreso'|'retiro'
         * Devuelve: array {status: ok|warn, mensaje}
         */
        if ($verboDetectado === '') {
            return ['status' => 'ok', 'mensaje' => ''];
        }

        $isIngresoVerb = in_array($verboDetectado, $this->VERBOS_INGRESO, true);
        $isRetiroVerb = in_array($verboDetectado, $this->VERBOS_RETIRO, true);

        if ($accionBoton === 'ingreso' && $isRetiroVerb) {
            return ['status' => 'warn', 'mensaje' => 'La frase sugiere RETIRO, pero estás en modo INGRESO.'];
        }
        if ($accionBoton === 'retiro' && $isIngresoVerb) {
            return ['status' => 'warn', 'mensaje' => 'La frase sugiere INGRESO, pero estás en modo RETIRO.'];
        }

        return ['status' => 'ok', 'mensaje' => ''];
    } // fin validateVerbCoherence()

    private function extractQuantity($textoNorm)
    {
        /**
         * Extrae la primera cantidad numérica del texto.
         * Parámetros: $textoNorm string normalizado
         * Devuelve: int cantidad (>0) o 0 si no hay
         */
        if (preg_match('/\b(\d+)\b/', $textoNorm, $m)) {
            return (int)$m[1];
        }

        return 0;
    } // fin extractQuantity()

    private function extractProductQuery($textoNorm, $cantidad, $verboDetectado)
    {
        /**
         * Remueve verbo y cantidad para obtener el texto del producto.
         * Parámetros:
         * - $textoNorm string
         * - $cantidad int
         * - $verboDetectado string
         * Devuelve: string query producto
         */
        $t = ' '.$textoNorm.' ';

        if ($verboDetectado !== '') {
            $t = str_replace(' '.$verboDetectado.' ', ' ', $t);
        }

        $t = preg_replace('/\b'.preg_quote((string)$cantidad, '/').'\b/', ' ', $t);
        $t = preg_replace('/\s+/', ' ', $t);

        return trim($t);
    } // fin extractProductQuery()

    private function findBestMatchProduct($productoQuery)
    {
        /**
         * Matching inteligente con sugerencias múltiples.
         * Devuelve:
         * - best (match fuerte)
         * - suggestions (si no alcanza umbral fuerte)
         */

        $words = explode(' ', mb_strtolower($productoQuery));
        $words = array_filter($words, fn($w) => strlen($w) >= 3);

        if (empty($words)) {
            return null;
        }

        // 🔵 MEJORA PROFESIONAL: normalizar plural simple
        $words = array_map(function($w) {
            if (substr($w, -1) === 's' && strlen($w) > 3) {
                return rtrim($w, 's');
            }
            return $w;
        }, $words);

        // Construir WHERE dinámico
        $conditions = [];
        $params = [];

        foreach ($words as $i => $w) {
            $conditions[] = "LOWER(name) LIKE :w$i";
            $params[":w$i"] = "%$w%";
        }

        $sql = "
        SELECT id, name, quantity
        FROM products
        WHERE status='active'
        AND (".implode(' OR ', $conditions).')
    ';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (! $rows) {
            return null;
        }

        $matches = [];

        foreach ($rows as $r) {
            $name = mb_strtolower($r['name']);

            $qNorm = $this->normalize($productoQuery);
            $nameNorm = $this->normalize($r['name']);

            similar_text($qNorm, $nameNorm, $percent);

            //similar_text($productoQuery, $name, $percent);

            $matches[] = [
                'id' => (int)$r['id'],
                'name' => $r['name'],
                'quantity' => (int)$r['quantity'],
                'score' => (int)round($percent)
            ];
        }

        usort($matches, fn($a, $b) => $b['score'] <=> $a['score']);

        // Mejor candidato
        $best = $matches[0];

        if ($best['score'] >= 70) {
            return ['type' => 'best', 'data' => $best];
        }

        // Si no hay match fuerte, devolver sugerencias top 5
        return [
            'type' => 'suggestions',
            'data' => array_slice($matches, 0, 5)
        ];
    }

// fin findBestMatchProduct()

    private function getProductByIdForUpdate($id)
    {
        /**
         * Obtiene producto bloqueándolo (FOR UPDATE) para evitar carreras.
         * Parámetros: $id int
         * Devuelve: array producto o null
         */
        $stmt = $this->conn->prepare('SELECT `id`, `name`, `quantity` FROM `products` WHERE `id` = :id FOR UPDATE');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ?: null;
    } // fin getProductByIdForUpdate()

    private function updateProductQuantity($id, $newQty)
    {
        /**
         * Actualiza quantity del producto.
         * Parámetros:
         * - $id int
         * - $newQty int
         * Devuelve: nada
         */
        $stmt = $this->conn->prepare('UPDATE `products` SET `quantity` = :q WHERE `id` = :id');
        $stmt->execute([':q' => $newQty, ':id' => $id]);
    } // fin updateProductQuantity()

    private function insertProductLog($productId, $userId, $action, $fieldChanged, $oldValue, $newValue)
    {
        /**
         * Inserta log en product_logs sin alterar schema (reutiliza old/new_value).
         * Parámetros: campos del log
         * Devuelve: nada
         */
        $stmt = $this->conn->prepare(
            '
            INSERT INTO `product_logs` (`product_id`, `user_id`, `action`, `field_changed`, `old_value`, `new_value`)
            VALUES (:pid, :uid, :act, :field, :oldv, :newv)
        '
        );
        $stmt->execute([
                           ':pid' => $productId,
                           ':uid' => $userId,
                           ':act' => $action,
                           ':field' => $fieldChanged,
                           ':oldv' => $oldValue,
                           ':newv' => $newValue
                       ]);
    } // fin insertProductLog()

    private function insertConsumptionEvent($productId, $userId, $qty, $voiceText)
    {
        /**
         * Registra retiro como consumption_event.
         * Parámetros:
         * - $productId int
         * - $userId int
         * - $qty int
         * - $voiceText string (se guarda en description)
         * Devuelve: nada
         */
        $stmt = $this->conn->prepare(
            '
            INSERT INTO consumption_events (product_id, user_id, quantity_consumed, consumption_location, description)
            VALUES (:pid, :uid, :qty, :loc, :desc)
        '
        );
        $stmt->execute([
                           ':pid' => $productId,
                           ':uid' => $userId,
                           ':qty' => $qty,
                           ':loc' => 'Voz',
                           ':desc' => $voiceText
                       ]);
    } // fin insertConsumptionEvent()

    private function err($msg)
    {
        /**
         * Helper de error consistente.
         * Parámetros: $msg string
         * Devuelve: array con status=error y mensaje
         */
        return ['status' => 'error', 'mensaje' => $msg];
    } // fin err()

    public function getProductsForLookup($query = '')
    {
        /**
         * Obtiene productos activos para consulta.
         * Si $query tiene al menos 3 letras, filtra por LIKE.
         * Devuelve: array JSON-friendly
         */

        $query = trim($query);

        if (strlen($query) >= 3) {
            $stmt = $this->conn->prepare("
            SELECT id, name, quantity
            FROM products
            WHERE status = 'active'
            AND LOWER(name) LIKE LOWER(:q)
            ORDER BY name ASC
            LIMIT 50
        ");
            $stmt->execute([':q' => '%' . $query . '%']);
        } else {
            $stmt = $this->conn->prepare("
            SELECT id, name, quantity
            FROM products
            WHERE status = 'active'
            ORDER BY name ASC
            LIMIT 100
        ");
            $stmt->execute();
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'status' => 'ok',
            'productos' => $rows
        ];
    }

}
