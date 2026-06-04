<?php

require_once 'models/Category.php';

class CategoryController {
    private $category_model;
    
    public function __construct() {
        $this->category_model = new Category();
    }
    
    public function index() {
        requireAdmin();
        
        //$categories = $this->category_model->getAllWithInactive();
        $categories = $this->category_model->getAll();
        include 'views/categories/index.php';
    }
    
    public function create() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = sanitize($_POST['name']);
            $description = sanitize($_POST['description']);
            
            if ($this->category_model->create($name, $description)) {
                $_SESSION['success'] = 'Categoría creada exitosamente';
            } else {
                $_SESSION['error'] = 'Error al crear la categoría';
            }
            redirect('categories');
        }
        
        include 'views/categories/create.php';
    }
    
    public function edit($id) {
        requireAdmin();
        
        $category = $this->category_model->getById($id);
        if (!$category) {
            $_SESSION['error'] = 'Categoría no encontrada';
            redirect('categories');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = sanitize($_POST['name']);
            $description = sanitize($_POST['description']);
            $active = intval($_POST['active']);
            
            if ($this->category_model->update($id, $name, $description, $active)) {
                $_SESSION['success'] = 'Categoría actualizada exitosamente';
            } else {
                $_SESSION['error'] = 'Error al actualizar la categoría';
            }
            redirect('categories');
        }
        
        include 'views/categories/edit.php';
    }
    
    public function delete($id) {
        requireAdmin();
        
        $product_count = $this->category_model->getProductCount($id);
        if ($product_count > 0) {
            $_SESSION['error'] = 'No se puede eliminar la categoría porque tiene productos asignados';
        } else {
            if ($this->category_model->delete($id)) {
                $_SESSION['success'] = 'Categoría eliminada exitosamente';
            } else {
                $_SESSION['error'] = 'Error al eliminar la categoría';
            }
        }
        redirect('categories');
    }
}

?>