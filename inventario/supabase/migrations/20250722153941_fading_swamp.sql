/*
# Hotel Inventory Management System Database Schema

1. Database Structure
   - `users` - Sistema de usuarios con roles
   - `categories` - Categorías de productos
   - `locations` - Ubicaciones del hotel
   - `products` - Inventario de productos
   - `consumption_events` - Registro de consumos
   - `product_logs` - Bitácora de cambios

2. Security
   - Passwords hasheados con password_hash()
   - Roles de usuario (admin/user)
   - Validaciones de entrada

3. Features
   - Stock tracking con alertas
   - Event logging completo
   - Soft delete para mantenimiento de datos
*/

CREATE DATABASE IF NOT EXISTS hotel_inventory;
USE hotel_inventory;

-- Tabla de usuarios
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de categorías
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de ubicaciones
CREATE TABLE locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    zone VARCHAR(50),
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de productos
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    quantity INT DEFAULT 0,
    unit VARCHAR(20) NOT NULL,
    category_id INT,
    location_id INT,
    min_stock INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL
);

-- Tabla de eventos de consumo
CREATE TABLE consumption_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    quantity_consumed INT NOT NULL,
    consumption_location VARCHAR(100),
    description TEXT,
    event_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla de logs/bitácora
CREATE TABLE product_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    field_changed VARCHAR(50),
    old_value TEXT,
    new_value TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Datos iniciales
INSERT INTO users (username, password, full_name, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin'),
('user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Usuario Test', 'user');

INSERT INTO categories (name, description) VALUES
('Lencería', 'Sábanas, toallas, almohadas, cobijas'),
('Aseo', 'Productos de limpieza, jabones, champú'),
('Electrónicos', 'TV, control remoto, secadoras'),
('Insumos Generales', 'Papel higiénico, servilletas, amenities');

INSERT INTO locations (name, description, zone) VALUES
('Bodega Lencería', 'Almacén principal de lencería', 'Bodega'),
('Piso 1 - Limpieza', 'Área de limpieza primer piso', 'Piso 1'),
('Piso 2 - Limpieza', 'Área de limpieza segundo piso', 'Piso 2'),
('Habitación 101', 'Suite ejecutiva', 'Piso 1'),
('Habitación 102', 'Habitación estándar', 'Piso 1'),
('Habitación 201', 'Suite presidencial', 'Piso 2'),
('Habitación 202', 'Habitación estándar', 'Piso 2');

INSERT INTO products (name, description, quantity, unit, category_id, location_id, min_stock) VALUES
('Juego de sábanas matrimonial', 'Sábanas blancas 100% algodón', 50, 'juego', 1, 1, 10),
('Toallas de baño', 'Toallas blancas 70x140cm', 75, 'unidad', 1, 1, 15),
('Shampoo individual', 'Shampoo en botella 30ml', 200, 'unidad', 2, 2, 50),
('Papel higiénico', 'Rollo doble hoja', 120, 'rollo', 4, 2, 30),
('Control remoto TV', 'Control universal', 15, 'unidad', 3, 1, 5);