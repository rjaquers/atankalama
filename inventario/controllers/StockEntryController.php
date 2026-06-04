<?php

require_once 'models/StockEntry.php';
require_once 'models/Product.php';
require_once 'models/LocationModel.php';

class StockEntryController
{
    private $stock_model;
    private $product_model;
    private $location_model;

    public function __construct()
    {
        /**
         * Constructor:
         * - Inicializa modelo de ingreso
         * - Inicializa modelo de productos
         * - Inicializa modelo de ubicaciones
         */
        $this->stock_model     = new StockEntry();
        $this->product_model   = new Product();
        $this->location_model  = new LocationModel();
    }

    /**
     * Formulario de ingreso
     * - GET: muestra vista
     * - POST: procesa ingreso
     */
    public function create()
    {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $product_id     = intval($_POST['product_id']);
            $quantity_added = intval($_POST['quantity_added']);
            $location_id    = intval($_POST['location_id']);
            $description    = sanitize($_POST['description']);

            if ($this->stock_model->create(
                $product_id,
                $_SESSION['user_id'],
                $quantity_added,
                $location_id,
                $description
            )) {
                $_SESSION['success_message'] = '✔ Ingreso registrado correctamente.';
            } else {
                $_SESSION['error_message'] = 'Error al ingresar stock.';
            }

            // 🔥 IMPORTANTE: volver a esta misma página
            redirect('stock_entry');
        }

        // 🔥 ESTA LÍNEA FALTABA
        $locations = $this->location_model->getActive();

        $products = $this->product_model->getAll();

        include 'views/stock_entry/create.php';
    }

    public function batch(): void
    {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $items       = json_decode($_POST['items'] ?? '[]', true);
            $locationId  = intval($_POST['location_id'] ?? 0);
            $description = sanitize($_POST['description'] ?? '');

            if (empty($items) || $locationId <= 0) {
                $_SESSION['error_message'] = 'Debe agregar productos y seleccionar una ubicación.';
                redirect('stock_entry&action=batch');
                return;
            }

            if ($this->stock_model->createBatch($items, $_SESSION['user_id'], $locationId, $description)) {
                $_SESSION['success_message'] = '✔ Ingreso en lote registrado correctamente.';
                redirect('stock_entry');
            } else {
                $_SESSION['error_message'] = 'Error al procesar el lote.';
                redirect('stock_entry&action=batch');
            }
        }

        $products  = $this->product_model->getAll();
        $locations = $this->location_model->getActive();
        include 'views/stock_entry/batch_create.php';
    }
}
