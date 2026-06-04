# Sistema de Inventario para Hotel

Sistema web completo de gestión de inventario desarrollado en PHP 7.4 con MariaDB, diseñado específicamente para hoteles.

## 🚀 Características Principales

- **CRUD completo** de productos, categorías, ubicaciones y usuarios
- **Sistema de autenticación** con roles (Admin/Usuario)
- **Gestión de inventario** con alertas de stock bajo
- **Registro de consumos** con descuento automático de stock
- **Bitácora detallada** de todos los movimientos
- **Dashboard interactivo** con métricas en tiempo real
- **Diseño responsive** con Bootstrap 5
- **Arquitectura MVC** simple y mantenible

## 📋 Requisitos del Sistema

- PHP 7.4 o superior
- MariaDB/MySQL 5.7+
- Servidor web (Apache/Nginx)
- Extensiones PHP: PDO, pdo_mysql

## 🛠 Instalación

### 1. Descargar los archivos
```bash
# Descargar y extraer los archivos del sistema
```

### 2. Configurar la base de datos
```sql
-- Ejecutar el archivo database/schema.sql en su base de datos MariaDB
mysql -u root -p < database/schema.sql
```

### 3. Configurar la conexión
Editar el archivo `config/database.php`:
```php
private $host = 'localhost';        // Su servidor de base de datos
private $db_name = 'hotel_inventory'; // Nombre de la base de datos
private $username = 'root';         // Su usuario de DB
private $password = '';             // Su contraseña de DB
```

### 4. Configurar el servidor web
Para desarrollo local, puede usar el servidor integrado de PHP:
```bash
php -S localhost:8000
```

Para producción, apuntar el DocumentRoot a la carpeta raíz del sistema donde está `index.php`.

### 5. Acceder al sistema
- URL desarrollo: `http://localhost:8000/`
- URL producción: `http://su-dominio.com/`
- Usuario admin: `admin` / `password`
- Usuario regular: `user` / `password`

## 📚 Estructura del Proyecto

```
hotel-inventory/
├── config/                 # Configuración
│   ├── config.php         # Configuración general
│   ├── database.php       # Conexión a DB
│   └── helpers.php        # Funciones auxiliares
├── controllers/           # Controladores MVC
├── models/               # Modelos de datos
├── views/                # Vistas HTML
│   ├── auth/            # Login/logout
│   ├── dashboard/       # Panel principal
│   ├── products/        # Gestión de productos
│   ├── consumption/     # Eventos de consumo
│   ├── categories/      # Categorías
│   ├── locations/       # Ubicaciones
│   ├── users/           # Usuarios
│   └── layout/          # Plantillas base
├── database/
│   └── schema.sql        # Script de base de datos
├── index.php            # Punto de entrada
└── README.md           # Este archivo
```

## 💡 Funcionalidades por Rol

### 👤 Usuario Regular
- Ver dashboard con métricas
- Listar y buscar productos
- Actualizar stock de productos
- Registrar eventos de consumo
- Ver historial de movimientos

### 🔒 Administrador
- **Todo lo anterior +**
- Crear/editar/eliminar productos
- Gestionar categorías y ubicaciones
- Administrar usuarios del sistema
- Acceso completo a configuración

## 🎯 Casos de Uso Principales

### Registro de Consumo
```
1. Usuario accede a "Registrar Consumo"
2. Selecciona producto del inventario
3. Ingresa cantidad consumida y ubicación
4. Sistema descuenta automáticamente del stock
5. Se registra el evento en la bitácora
```

### Alertas de Stock Bajo
```
1. Sistema monitorea niveles mínimos
2. Dashboard muestra alertas automáticamente
3. Productos aparecen resaltados en listados
4. Notificaciones visuales en tiempo real
```

### Gestión de Ubicaciones
```
Ejemplos de ubicaciones:
- Habitación 102 (Piso 1)
- Bodega Lencería (Bodega)
- Piso 2 - Limpieza (Piso 2)
- Suite Presidencial (Piso 3)
```

## 🔧 Personalización

### Agregar nuevas categorías por defecto
Editar `database/schema.sql` en la sección de datos iniciales:
```sql
INSERT INTO categories (name, description) VALUES
('Nueva Categoría', 'Descripción de la categoría');
```

### Modificar unidades de medida
En `views/products/create.php` y `edit.php`, actualizar las opciones del select:
```html
<option value="nueva_unidad">Nueva Unidad</option>
```

### Personalizar zonas del hotel
En `views/locations/create.php` y `edit.php`, modificar las opciones:
```html
<option value="Nueva Zona">Nueva Zona</option>
```

## 🛡 Seguridad

- Contraseñas hasheadas con `password_hash()`
- Validación y sanitización de entrada
- Protección contra inyección SQL con PDO
- Sesiones seguras con verificación de roles
- Logs de auditoría completos

## 📊 Base de Datos

### Tablas principales:
- `users` - Usuarios del sistema
- `categories` - Categorías de productos
- `locations` - Ubicaciones del hotel
- `products` - Inventario de productos
- `consumption_events` - Eventos de consumo
- `product_logs` - Bitácora de movimientos

## 🔍 Troubleshooting

### Error de conexión a la base de datos
1. Verificar credenciales en `config/database.php`
2. Confirmar que MariaDB está ejecutándose
3. Validar permisos del usuario de DB

### Páginas en blanco
1. Habilitar mostrar errores de PHP
2. Revisar logs del servidor web
3. Verificar permisos de archivos

### Problemas de sesión
1. Verificar configuración de sesiones PHP
2. Confirmar permisos de escritura en `/tmp`
3. Revisar configuración de cookies

## 🚀 Hosting en Servidor Compartido

### Pasos recomendados:
1. Subir archivos via FTP/cPanel
2. Importar base de datos via phpMyAdmin
3. Configurar conexión en `config/database.php`
4. Asegurar permisos 755 para directorios
5. Configurar dominio/subdominio

### Consideraciones:
- Compatible con hosting compartido tradicional
- No requiere Composer ni frameworks externos
- Funciona con PHP 7.4+ estándar
- Base de datos MariaDB/MySQL estándar

## 📞 Soporte

Para soporte técnico o consultas:
- Revisar la documentación incluida
- Verificar logs de errores del servidor
- Comprobar configuración de base de datos
- Validar permisos de archivos

---

**Desarrollado con ❤️ para hoteles que buscan eficiencia en su gestión de inventario**