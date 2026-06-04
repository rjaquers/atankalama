<?php

require_once 'models/ConsumptionEvent.php';
require_once 'models/Product.php';

class ConsumptionController {
    private $consumption_model;
    private $product_model;
    
    public function __construct() {
        $this->consumption_model = new ConsumptionEvent();
        $this->product_model = new Product();
    }
    
    public function index() {
        requireLogin();
        
        $events = $this->consumption_model->getAll(100);
        include 'views/consumption/index.php';
    }
    
    public function create() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $product_id = intval($_POST['product_id']);
            $quantity_consumed = intval($_POST['quantity_consumed']);
            $consumption_location = sanitize($_POST['consumption_location']);
            $description = sanitize($_POST['description']);
            
            if ($this->consumption_model->create($product_id, $_SESSION['user_id'], $quantity_consumed, $consumption_location, $description)) {
                $_SESSION['success'] = 'Evento de consumo registrado exitosamente';
            } else {
                $_SESSION['error'] = 'Error al registrar el consumo. Verifique que haya suficiente stock.';
            }
            redirect('consumption');
        }
        
        $products = $this->product_model->getAll();
        include 'views/consumption/create.php';
    }

    /**
     * =========================================================
     * Método: batch
     * Descripción:
     * Permite registrar consumo en lote (múltiples productos)
     * - Valida stock
     * - Ejecuta todo en una sola transacción
     * - Registra logs individuales
     * - Nunca permite stock negativo
     * =========================================================
     */
    public function batch()
    {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $items = json_decode($_POST['items'], true);
            $location = sanitize($_POST['consumption_location']);
            $description = sanitize($_POST['description']);
            $user_id = $_SESSION['user_id'];

            if (empty($items) || empty($location)) {
                $_SESSION['error'] = 'Debe agregar productos y definir área destino.';
                redirect('consumption&action=batch');
            }

            $result = $this->consumption_model->createBatch(
                $items,
                $user_id,
                $location,
                $description
            );

            if ($result) {
                $_SESSION['success'] = 'Consumo en lote registrado correctamente.';
                redirect('consumption');
            } else {
                $_SESSION['error'] = 'Error al procesar el lote. Verifique stock.';
                redirect('consumption&action=batch');
            }
        }

        $products = $this->product_model->getAll();
        include 'views/consumption/batch_create.php';
    }
}
