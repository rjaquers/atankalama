<?php

require_once 'models/Product.php';
require_once 'models/Category.php';
require_once 'models/LocationModel.php';
require_once 'models/StockEntry.php';



class ProductController {
    protected $product_model;
    private $category_model;
    private $location_model;
    private $stock_model;
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();

        $this->product_model = new Product( $this->db);
        $this->category_model = new Category( $this->db);
        $this->location_model = new LocationModel( $this->db );

        $this->stock_model = new StockEntry();




    }
    
    public function index() {
        requireLogin();

        $products = $this->product_model->getAll();
        include 'views/products/index.php';
    }

    public function create()
    {
        /**
         * Método: create
         * ----------------
         * Responsabilidad:
         * - Crear un nuevo producto en el sistema.
         * - Registrar imágenes asociadas al producto (si existen).
         * - Informar al usuario si la operación fue exitosa o fallida.
         * - Redirigir al listado de productos mostrando feedback visual.
         *
         * Patrón aplicado:
         * - Post / Redirect / Get (PRG)
         */

        // 1️⃣ Seguridad: solo administradores pueden crear productos
        requireAdmin();

        // 2️⃣ Si el formulario fue enviado
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // 3️⃣ Sanitización y normalización de datos recibidos
            $name         = sanitize($_POST['name'] ?? '');
            $codigoBarra  = sanitize($_POST['codigoBarra'] ?? '');
            $vencimiento  = sanitize($_POST['vencimiento'] ?? '');
            $description  = sanitize($_POST['description'] ?? '');
            $quantity     = (int) ($_POST['quantity'] ?? 0);
            $unit         = sanitize($_POST['unit'] ?? '');
            $category_id  = (int) ($_POST['category_id'] ?? 0);
            $location_id  = (int) ($_POST['location_id'] ?? 0);
            $min_stock    = (int) ($_POST['min_stock'] ?? 0);
            $status       = $_POST['status'] ?? 'active';

            // 4️⃣ Crear el producto base en base de datos
            $product_id = $this->product_model->create(
                $name,
                $codigoBarra,
                $vencimiento,
                $description,
                $quantity,
                $unit,
                $category_id,
                $location_id,
                $min_stock,
                $status
            );

            $locations = $this->location_model->getActive();


            // 5️⃣ Verificar si el producto fue creado correctamente
            if ($product_id) {

                // 6️⃣ Manejo de imágenes asociadas al producto (múltiples archivos)
                if (!empty($_FILES['fotos']['name'][0])) {

                    /**
                     * Manejo avanzado de imágenes:
                     * - Nombre único
                     * - Optimización de peso
                     * - Conversión WebP
                     * - Creación de miniatura 50x50
                     */

                    $baseDir   = 'uploads/' . date('Y_m_d') . '/product_' . $product_id . '/';
                    $thumbDir  = $baseDir . 'thumbs/';

                    // Crear carpetas si no existen
                    if (!is_dir($baseDir)) {
                        mkdir($baseDir, 0775, true);
                    }
                    if (!is_dir($thumbDir)) {
                        mkdir($thumbDir, 0775, true);
                    }

                    foreach ($_FILES['fotos']['tmp_name'] as $i => $tmpName) {

                        if ($_FILES['fotos']['error'][$i] !== UPLOAD_ERR_OK) {
                            continue;
                        }

                        // 🔎 Validar imagen real
                        $info = getimagesize($tmpName);
                        if (!$info) {
                            continue;
                        }

                        $mime = $info['mime'];

                        // 🎨 Crear recurso GD
                        switch ($mime) {
                            case 'image/jpeg':
                                $srcImage = imagecreatefromjpeg($tmpName);
                                break;
                            case 'image/png':
                                $srcImage = imagecreatefrompng($tmpName);
                                break;
                            case 'image/webp':
                                $srcImage = imagecreatefromwebp($tmpName);
                                break;
                            default:
                                continue 2;
                        }

                        if (!$srcImage) {
                            continue;
                        }

                        // 📐 Dimensiones originales
                        $w = imagesx($srcImage);
                        $h = imagesy($srcImage);

                        /* =====================================================
                         * 1️⃣ IMAGEN PRINCIPAL OPTIMIZADA
                         * ===================================================== */

                        $maxWidth = 1600;
                        if ($w > $maxWidth) {
                            $ratio = $maxWidth / $w;
                            $newW  = $maxWidth;
                            $newH  = (int)($h * $ratio);

                            $mainImage = imagecreatetruecolor($newW, $newH);
                            imagecopyresampled($mainImage, $srcImage, 0, 0, 0, 0, $newW, $newH, $w, $h);
                        } else {
                            $mainImage = $srcImage;
                        }

                        // 🔐 Nombre único base
                        $baseName   = 'prod_' . $product_id . '_' . uniqid();
                        $mainPath   = $baseDir . $baseName . '.webp';

                        // Guardar imagen principal
                        $saved = function_exists('imagewebp')
                            ? imagewebp($mainImage, $mainPath, 80)
                            : imagejpeg($mainImage, str_replace('.webp', '.jpg', $mainPath), 85);

                        /* =====================================================
                         * 2️⃣ MINIATURA 50x50
                         * ===================================================== */

                        $thumbSize = 50;
                        $ratioT    = min($thumbSize / $w, $thumbSize / $h);
                        $thumbW    = (int)($w * $ratioT);
                        $thumbH    = (int)($h * $ratioT);

                        $thumbImage = imagecreatetruecolor($thumbW, $thumbH);
                        imagecopyresampled($thumbImage, $srcImage, 0, 0, 0, 0, $thumbW, $thumbH, $w, $h);

                        $thumbPath = $thumbDir . $baseName . '_50x50.webp';

                        if (function_exists('imagewebp')) {
                            imagewebp($thumbImage, $thumbPath, 75);
                        } else {
                            imagejpeg($thumbImage, str_replace('.webp', '.jpg', $thumbPath), 80);
                        }

                        /* =====================================================
                         * 3️⃣ LIMPIEZA + REGISTRO
                         * ===================================================== */

                        imagedestroy($srcImage);
                        if ($mainImage !== $srcImage) imagedestroy($mainImage);
                        imagedestroy($thumbImage);

                        // Guardar rutas en DB
                        if (file_exists($mainPath)) {
                            $this->product_model->insertImage($product_id, $mainPath, $thumbPath);
                        }
                    }
                }



                // 7️⃣ Feedback positivo al usuario (Flash Message)
                $_SESSION['flash_success'] = "Producto <strong>{$name}</strong> creado correctamente.";
                $_SESSION['flash_product_id'] = $product_id;

            } else {

                // 8️⃣ Feedback negativo si ocurre un error
                $_SESSION['flash_error'] = 'Error al crear el producto. Intente nuevamente.';
            }

            // 9️⃣ Redirección al listado de productos (PRG)
            redirect('products');
        }

        // 🔟 Si es GET: cargar datos para el formulario
        $categories = $this->category_model->getAll();
        $locations  = $this->location_model->getAll();

        // 1️⃣1️⃣ Renderizar vista de creación
        include 'views/products/create.php';
    }







    public function edit($id) {
        requireAdmin();
        
        $product = $this->product_model->getById($id);
        $images = $this->product_model->getImagesByProduct($id);

        if (!$product) {
            $_SESSION['error'] = 'Producto no encontrado';
            redirect('products');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = sanitize($_POST['name']);
            $codigoBarra = sanitize($_POST['codigoBarra'] ?? '');
            $vencimiento = sanitize($_POST['vencimiento'] ?? '');
            $description = sanitize($_POST['description']);
            $quantity = intval($_POST['quantity']);
            $unit = sanitize($_POST['unit']);
            $category_id = intval($_POST['category_id']);
            $location_id = intval($_POST['location_id']);
            $min_stock = intval($_POST['min_stock']);
            $status = sanitize($_POST['status']);


            if ($this->product_model->update($id, $name, $codigoBarra, $vencimiento, $description, $quantity, $unit, $category_id, $location_id, $min_stock, $status)) {
                $_SESSION['success'] = 'Producto actualizado exitosamente';
            } else {
                $_SESSION['error'] = 'Error al actualizar el producto';
            }
            redirect('products');
        }
        
        $categories = $this->category_model->getAll();
        $locations = $this->location_model->getAll();
        include 'views/products/edit.php';
    }
    
    public function view($id) {
        $images = $this->product_model->getImagesByProduct($id);
        requireLogin();
        
        $product = $this->product_model->getById($id);
        if (!$product) {
            $_SESSION['error'] = 'Producto no encontrado';
            redirect('products');
        }
        
        $logs = $this->product_model->getRecentLogs($id, 15);
        include 'views/products/view.php';
    }
    
    public function updateStock($id) {
        requireLogin();
        
        $product = $this->product_model->getById($id);
        if (!$product) {
            $_SESSION['error'] = 'Producto no encontrado';
            redirect('products');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_quantity = intval($_POST['quantity']);
            
            if ($this->product_model->updateStock($id, $new_quantity)) {
                $_SESSION['success'] = 'Stock actualizado exitosamente';
            } else {
                $_SESSION['error'] = 'Error al actualizar el stock';
            }
            redirect('products');
        }
        
        include 'views/products/update_stock.php';
    }
    
    public function delete($id) {
        requireAdmin();
        
        if ($this->product_model->delete($id)) {
            $_SESSION['success'] = 'Producto eliminado exitosamente';
        } else {
            $_SESSION['error'] = 'Error al eliminar el producto';
        }
        redirect('products');
    }

    // Subir nuevas imágenes desde la edición
    public function addImage($id)
    {
        requireAdmin();

        if (empty($_FILES['fotos']['tmp_name'])) {
            $_SESSION['error'] = 'No se recibió ninguna imagen.';
            redirect('products&action=edit&id=' . $id);
        }

        $subidas = 0;

        foreach ($_FILES['fotos']['tmp_name'] as $i => $tmpName) {

            if ($_FILES['fotos']['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $resultado = $this->procesarImagenProducto($id, $tmpName);

            if ($resultado) {
                $this->product_model->insertImage(
                    $id,
                    $resultado['main'],
                    $resultado['thumb']
                );
                $subidas++;
            }
        }

        if ($subidas > 0) {
            $_SESSION['success'] = "Se subieron {$subidas} imagen(es) optimizadas.";
        } else {
            $_SESSION['error'] = 'No se pudo subir ninguna imagen válida.';
        }

        redirect('products&action=edit&id=' . $id);
    }


// Eliminar una imagen específica
    public function deleteImage($id)
    {
        requireAdmin();

        $image_id = $_GET['id'];
        $product_id = $_GET['product_id'];

        $this->product_model->deleteImage($image_id);
        $_SESSION['success'] = 'Imagen eliminada correctamente.';
        redirect('products&action=edit&id=' . $product_id);
    }

    /**
     * Muestra el listado de productos con stock bajo
     * Criterio:
     *  - quantity <= min_stock
     *  - status = active
     *  - orden alfabético
     */
    public function lowStock()
    {
        $products = $this->product_model->getLowStockProducts();

        $page_title = 'Productos con Stock Bajo';
        $view = __DIR__ . '/../views/products/low_stock.php';

        require __DIR__ . '/../views/layout/home.php';
    }

    public function deadStock()
    {
        /**
         * Muestra el listado de productos SIN movimiento
         * en los últimos 30 días, usando el layout home.php
         */

        requireLogin();

        $days = 30;
        $deadStock = $this->product_model->getDeadStock($days);

        $page_title = 'Inventario sin Movimiento (30 días)';
        $view = __DIR__ . '/../views/dashboard/dead_stock.php';

        require __DIR__ . '/../views/layout/home.php';
    }


    /**
     * Endpoint AJAX
     * Busca productos para autocompletado
     */
    public function search()
    {
        // Endpoint AJAX → solo JSON
        header('Content-Type: application/json; charset=utf-8');

        try {
            $q = trim($_GET['q'] ?? '');

            if (strlen($q) < 2) {
                echo json_encode([]);
                exit;
            }

            $products = $this->product_model->searchProducts($q);

            echo json_encode($products);
            exit;

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                                 'error' => 'Error interno de búsqueda'
                             ]);
            exit;
        }
    }


    /**
     * Procesa una imagen de producto:
     * - Optimiza
     * - Convierte a WebP
     * - Genera miniatura 50x50
     *
     * @return array|null ['main' => path, 'thumb' => path]
     */
    private function procesarImagenProducto(int $product_id, string $tmpName): ?array
    {
        $info = getimagesize($tmpName);
        if (!$info) {
            return null;
        }

        $mime = $info['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $src = imagecreatefromjpeg($tmpName);
                break;
            case 'image/png':
                $src = imagecreatefrompng($tmpName);
                break;
            case 'image/webp':
                $src = imagecreatefromwebp($tmpName);
                break;
            default:
                return null;
        }

        if (!$src) {
            return null;
        }

        $w = imagesx($src);
        $h = imagesy($src);

        // Directorios
        $baseDir  = 'uploads/' . date('Y_m_d') . '/product_' . $product_id . '/';
        $thumbDir = $baseDir . 'thumbs/';

        if (!is_dir($baseDir)) mkdir($baseDir, 0775, true);
        if (!is_dir($thumbDir)) mkdir($thumbDir, 0775, true);

        $baseName = 'prod_' . $product_id . '_' . uniqid();

        /* ===== Imagen principal ===== */
        $maxW = 1600;
        if ($w > $maxW) {
            $ratio = $maxW / $w;
            $nw = $maxW;
            $nh = (int)($h * $ratio);
            $main = imagecreatetruecolor($nw, $nh);
            imagecopyresampled($main, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        } else {
            $main = $src;
        }

        $mainPath = $baseDir . $baseName . '.webp';
        imagewebp($main, $mainPath, 80);

        /* ===== Miniatura ===== */
        $size = 50;
        $r = min($size / $w, $size / $h);
        $tw = (int)($w * $r);
        $th = (int)($h * $r);

        $thumb = imagecreatetruecolor($tw, $th);
        imagecopyresampled($thumb, $src, 0, 0, 0, 0, $tw, $th, $w, $h);

        $thumbPath = $thumbDir . $baseName . '_50x50.webp';
        imagewebp($thumb, $thumbPath, 75);

        imagedestroy($src);
        if ($main !== $src) imagedestroy($main);
        imagedestroy($thumb);

        return [
            'main'  => $mainPath,
            'thumb' => $thumbPath
        ];
    }


    public function sinStock()
    {
        /**
         * Muestra el listado de productos SIN stock (quantity = 0)
         *
         * - Obtiene los productos cuyo stock actual es cero
         * - Define el título de la página
         * - Define la vista a renderizar
         * - Carga el layout principal home.php
         *
         * Uso:
         * index.php?page=products&action=sin_stock
         */

        requireLogin();

        // Obtener productos sin stock desde el modelo
        $products = $this->product_model->getSinStockProducts();

        // Variables requeridas por el layout home.php
        $page_title = 'Productos Sin Stock';
        $view = __DIR__ . '/../views/products/sin_stock.php';

        // Renderizar usando el layout principal
        require __DIR__ . '/../views/layout/home.php';
    }

    /**
     * =========================================================
     * Método: duplicate
     * Descripción:
     * Duplica un producto existente como base para crear uno nuevo.
     * - No copia ID
     * - No copia fechas
     * - Precarga datos en formulario create
     * =========================================================
     */
    public function duplicate($id)
    {
        requireLogin();

        $product = $this->product_model->getById($id);

        if (!$product) {
            $_SESSION['error'] = 'Producto no encontrado';
            redirect('products');
        }

        // Ajuste mínimo
        $product['quantity'] = 0;

        $categories = $this->category_model->getAll();
        $locations  = $this->location_model->getAll();

        include 'views/products/create.php';
    }





} //fin Product Controller