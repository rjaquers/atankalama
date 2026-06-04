<?php
require_once 'config/database.php';
require_once 'config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple routing
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';
$id = isset($_GET['id']) ? intval($_GET['id']) : null;



// Route handling
switch ($page) {
    case 'dashboard':
        require_once 'controllers/DashboardController.php';
        $controller = new DashboardController();

        switch ($action) {
            case 'dead_stock':

                $controller->deadStock();
                break;

            default:
                $controller->index();
                break;
        }
        break;




    case 'products':
        require_once 'controllers/ProductController.php';
        $controller = new ProductController();
        switch ($action) {
            case 'create':
                $controller->create();
                break;
            case 'edit':
                $controller->edit($id);
                break;
            case 'view':
                $controller->view($id);
                break;
            case 'update_stock':
                $controller->updateStock($id);
                break;
            case 'delete':
                $controller->delete($id);
                break;
            case 'addImage':               // 👈 NUEVO
                $controller->addImage($id);
                break;
            case 'deleteImage':            // 👈 NUEVO
                $controller->deleteImage($id);
                break;
            case 'low_stock':              // 👈 NUEVO
                $controller->lowStock();
                break;
            case 'sin_stock':
                $controller->sinStock();
                break;
            case 'search':
                $controller->search();
                break;
            case 'duplicate':   // 👈 AQUÍ VA
                $controller->duplicate($id);
                break;

            default:
                $controller->index();
        }
        break;



        
    case 'categories':
        require_once 'controllers/CategoryController.php';
        $controller = new CategoryController();
        switch ($action) {
            case 'create':
                $controller->create();
                break;
            case 'edit':
                $controller->edit($id);
                break;
            case 'delete':
                $controller->delete($id);
                break;
            default:
                $controller->index();
        }
        break;
        
    case 'locations':
        require_once 'controllers/LocationController.php';
        $controller = new LocationController();
        switch ($action) {
            case 'create':
                $controller->create();
                break;
            case 'edit':
                $controller->edit($id);
                break;
            case 'delete':
                $controller->delete($id);
                break;
            default:
                $controller->index();
        }
        break;
        
    case 'users':
        require_once 'controllers/UserController.php';
        $controller = new UserController();
        switch ($action) {
            case 'create':
                $controller->create();
                break;
            case 'edit':
                $controller->edit($id);
                break;
            case 'delete':
                $controller->delete($id);
                break;
            default:
                $controller->index();
        }
        break;

    case 'logs':
        require_once 'controllers/LogController.php';
        $controller = new LogController();
        switch ($action) {
            case 'clear':
                $controller->clear();
                break;
            default:
                $controller->index();
                break;
        }
        break;

    case 'voice_stock':
        require_once __DIR__ . '/controllers/VoiceStockController.php';
        $controller = new VoiceStockController();

        $action = $_GET['action'] ?? 'index';

        switch ($action) {
            case 'process':
                $controller->process();  // Analiza texto y propone operación (NO ejecuta)
                break;

            case 'confirm':
                $controller->confirm();  // Ejecuta operación confirmada
                break;

            case 'list_products':
                $controller->listProducts();
                break;


            default:
                $controller->index();    // Pantalla principal
                break;
        }
        break;

    case 'stock_entry':
        require_once 'controllers/StockEntryController.php';
        $controller = new StockEntryController();
        switch ($action) {
            case 'batch': $controller->batch();  break;
            default:      $controller->create(); break;
        }
        break;

    case 'consumption':
        require_once 'controllers/ConsumptionController.php';
        $controller = new ConsumptionController();
        switch ($action) {
            case 'create':
                $controller->create();
                break;

            case 'batch':   // NUEVO
                $controller->batch();
                break;

            default:
                $controller->index();
        }
        break;







    case 'chatbot':
        require_once 'controllers/ChatbotController.php';
        $controller = new ChatbotController();
        switch ($action) {
            case 'process': $controller->process(); break;
            case 'confirm': $controller->confirm(); break;
            case 'reset':   $controller->reset();   break;
            default:        $controller->index();
        }
        break;

    default:
        redirect('dashboard');
}