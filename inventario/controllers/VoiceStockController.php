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
 * Controlador del módulo "Operación por Voz".
 * - index(): muestra pantalla con botones (Ingreso/Retiro) y confirmación.
 * - process(): recibe texto + acción (definida por botón), valida y propone operación (sin ejecutar).
 * - confirm(): ejecuta operación confirmada, actualiza stock y registra logs/eventos.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/VoiceStockModel.php';

class VoiceStockController
{
    private $model;

    public function __construct()
    {
        /**
         * Crea conexión y modelo del módulo.
         * Parámetros: ninguno
         * Devuelve: nada
         * Usa: Database, VoiceStockModel
         */
        $database = new Database();
        $db = $database->connect();

        $this->model = new VoiceStockModel($db);
    } // fin __construct()

    public function index()
    {
        /**
         * Renderiza la vista principal del módulo.
         * Parámetros: ninguno
         * Devuelve: nada
         * Usa: requireLogin()
         */
        //requireLogin();
        if (!isLoggedIn()) {
            redirect('login');
        }

        $page_title = 'Operación por Voz';
        $view = __DIR__ . '/../views/voice_stock/index.php';

        require __DIR__ . '/../views/layout/home.php';
    } // fin index()

    public function process()
    {
        /**
         * Analiza el texto dictado y propone la operación (NO ejecuta cambios).
         * Parámetros: POST JSON { texto, accion } donde accion=ingreso|retiro (definida por botón)
         * Devuelve: JSON con propuesta o error
         * Usa: requireLogin(), VoiceStockModel::analyzeVoice()
         */
        requireLogin();
        $payload = $this->readJsonBody();

        $texto  = $payload['texto']  ?? '';
        $accion = $payload['accion'] ?? ''; // 'ingreso' o 'retiro'

        $result = $this->model->analyzeVoice($texto, $accion);

        $this->jsonResponse($result);
    } // fin process()

    public function confirm()
    {
        /**
         * Ejecuta la operación confirmada y registra logs.
         * Parámetros: POST JSON { producto_id, cantidad, accion, texto_original, verbo_detectado }
         * Devuelve: JSON con resultado final (stock antes/después) o error
         */

        if (!isLoggedIn()) {
            redirect('login');
        }

        $payload = $this->readJsonBody();

        $productoId     = (int)($payload['producto_id'] ?? 0);
        $cantidad       = (int)($payload['cantidad'] ?? 0);
        $accion         = (string)($payload['accion'] ?? '');
        $textoOriginal  = (string)($payload['texto_original'] ?? '');
        $verboDetectado = (string)($payload['verbo_detectado'] ?? '');

        $userId = (int)($_SESSION['user_id'] ?? 0);

        // 🔒 Validaciones básicas
        if ($productoId <= 0) {
            return $this->jsonResponse(['status'=>'error','mensaje'=>'Producto inválido.']);
        }

        if ($cantidad <= 0) {
            return $this->jsonResponse(['status'=>'error','mensaje'=>'Cantidad inválida.']);
        }

        if (!in_array($accion, ['ingreso','retiro'])) {
            return $this->jsonResponse(['status'=>'error','mensaje'=>'Acción inválida.']);
        }

        if ($userId <= 0) {
            return $this->jsonResponse(['status'=>'error','mensaje'=>'Usuario no válido.']);
        }

        // ✅ Ejecutar operación real
        $result = $this->model->applyConfirmedOperation(
            $productoId,
            $cantidad,
            $accion,
            $userId,
            $textoOriginal,
            $verboDetectado
        );

        return $this->jsonResponse($result);
    }
    //fin confirm

    private function readJsonBody()
    {
        /**
         * Lee el body JSON del request.
         * Parámetros: ninguno
         * Devuelve: array asociativo
         * Usa: php://input
         */
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    } // fin readJsonBody()

    private function jsonResponse($data)
    {
        /**
         * Envía respuesta JSON con cabeceras correctas.
         * Parámetros: $data (array)
         * Devuelve: nada (echo + exit)
         * Usa: header()
         */
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    } // fin jsonResponse()

    public function listProducts()
    {
        /**
         * Devuelve listado de productos activos para consulta.
         * Parámetros: GET q (opcional)
         * Devuelve: JSON array productos
         */

        if (!isLoggedIn()) {
            redirect('login');
        }

        $query = $_GET['q'] ?? '';

        $result = $this->model->getProductsForLookup($query);

        $this->jsonResponse($result);
    }

}
