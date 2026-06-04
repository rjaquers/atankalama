<?php

require_once 'models/User.php';

class UserController {
    private $user_model;
    
    public function __construct() {
        $this->user_model = new User();
    }
    
    public function index() {
        requireAdmin();
        
        $users = $this->user_model->getAll();
        include 'views/users/index.php';
    }
    
    public function create() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = sanitize($_POST['username']);
            $password = $_POST['password'];
            $full_name = sanitize($_POST['full_name']);
            $role = sanitize($_POST['role']);
            
            if ($this->user_model->create($username, $password, $full_name, $role)) {
                $_SESSION['success'] = 'Usuario creado exitosamente';
            } else {
                $_SESSION['error'] = 'Error al crear el usuario';
            }
            redirect('users');
        }
        
        include 'views/users/create.php';
    }
    
    public function edit($id) {
        requireAdmin();
        
        $user = $this->user_model->getById($id);
        if (!$user) {
            $_SESSION['error'] = 'Usuario no encontrado';
            redirect('users');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = sanitize($_POST['username']);
            $full_name = sanitize($_POST['full_name']);
            $role = sanitize($_POST['role']);
            $active = intval($_POST['active']);
            $password = $_POST['password'];
            
            if ($this->user_model->update($id, $username, $full_name, $role, $active, $password)) {
                $_SESSION['success'] = 'Usuario actualizado exitosamente';
            } else {
                $_SESSION['error'] = 'Error al actualizar el usuario';
            }
            redirect('users');
        }
        
        include 'views/users/edit.php';
    }
    
    public function delete($id) {
        requireAdmin();
        
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = 'No puedes eliminarte a ti mismo';
        } else {
            if ($this->user_model->delete($id)) {
                $_SESSION['success'] = 'Usuario eliminado exitosamente';
            } else {
                $_SESSION['error'] = 'Error al eliminar el usuario';
            }
        }
        redirect('users');
    }
}

?>