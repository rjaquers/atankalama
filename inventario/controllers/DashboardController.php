<?php
require_once __DIR__ . '/../config/database.php';
require_once 'models/Product.php';
require_once 'models/ConsumptionEvent.php';
require_once 'models/Category.php';
require_once 'models/LocationModel.php';

class DashboardController {
    private $product_model;
    private ConsumptionEvent $consumptionModel;
    private $consumption_model;
    private $category_model;
    private $location_model;
    
    public function __construct() {
        $database = new Database();
        $db = $database->connect();

        //$this->productModel     = new Product($db);
        $this->consumptionModel = new ConsumptionEvent($db);
        $this->product_model = new Product();
        $this->consumption_model = new ConsumptionEvent();
        $this->category_model = new Category();
        $this->location_model = new LocationModel($db);
    }
    
    public function index() {
        requireLogin();




            $inventoryHealth = $this->product_model->getInventoryHealth(30);
            $topConsumed     = $this->consumptionModel->getTopConsumed(30);
            $deadStock       = $this->product_model->getDeadStock(30);


        // Get summary data
        // 🔹 Total de productos con stock bajo

        $stock_summary = $this->product_model->getStockSummary();
        // Variables para los cards
        $totalProductos = (int) ($stock_summary['total_products'] ?? 0);
        $itemsStock     = (int) ($stock_summary['total_items'] ?? 0);

        // Ya calculados también (opcional coherencia)
        $lowStockCount  = (int) ($stock_summary['low_stock_count'] ?? 0);
        $sinStock       = (int) ($stock_summary['out_of_stock_count'] ?? 0);


         $low_stock_products = $this->product_model->getLowStockProducts();
       // $lowStockCount = count($low_stock_products);
        $consumption_summary = $this->consumption_model->getConsumptionSummary();
        $recent_events = $this->consumption_model->getAll(10);
        $top_consumed = $this->consumption_model->getTopConsumedProducts(30, 5);
      //  $sinStock = $this->product_model->countSinStock();

        if(!isset($_GET['page'])) {$page = 'dashboard';}

        include 'views/dashboard/index.php';
    }


    /**
     * Muestra el detalle de productos sin movimiento
     * (Inventario muerto)
     */
    public function deadStock(): void
    {
        requireLogin();

        $days = 30; // parametrizable más adelante

        $deadStock = $this->product_model->getDeadStock($days);

        // Variables requeridas por el layout home.php
        $page_title = 'Productos Sin Stock';
      //  $view = __DIR__ . '/../views/products/sin_stock.php';
        $view = __DIR__ . '/../views/dashboard/dead_stock.php';
        // Renderizar usando el layout principal
        require __DIR__ . '/../views/layout/home.php';


    }


} // fin controller
