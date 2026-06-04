---
name: controller-builder
description: Crea controllers PHP para el Sistema de Contratos Atankalama. Define la estructura MVC, middleware de permisos, CSRF, validación, y respuestas JSON/redirect.
---

# 🎮 Controller Builder — Sistema de Contratos Atankalama

## Contexto

- **Proyecto:** Sistema de Contratos – Hotel Atankalama
- **Clase base:** `Controller` (en `app/core/Controller.php`)
- **Métodos heredados:** `view($path, $data)`, `json($data, $status)`, `redirect($path)`
- **Router:** `app/core/Router.php` — convención automática URL → Controller → Método
- **Ubicación:** `app/controllers/{Entidad}Controller.php`

---

## 📐 Convenciones obligatorias

### 1. Estructura base de un Controller

```php
<!--
  = Proyecto: Sistema de Contratos Atankalama =
  = Autor: Rodrigo Jaque Escobar              =
  = Contacto: rjaquers@gmail.com              =

-->
<?php
/**
 * Controller de {Módulo}.
 *
 * Gestiona las acciones CRUD para {entidad}.
 * Requiere permisos: {permiso}_view, {permiso}_create, etc.
 *
 * @package App\Controllers
 */
class {Entidad}Controller extends Controller
{
    /**
     * Lista todos los registros.
     * Permiso requerido: {entidad}_view
     */
    public function index()
    {
        PermissionMiddleware::check('{entidad}_view');
        // ...
        $this->view('{modulo}/index', compact('data'));
    }
    // Fin de la función index()
}
```

### 2. Métodos estándar CRUD

| Método | Verbo HTTP | URL | Descripción |
|---|---|---|---|
| `index()` | GET | `/{modulo}` | Listar registros |
| `create()` | GET | `/{modulo}/create` | Mostrar formulario de creación |
| `store()` | POST | `/{modulo}/store` | Guardar nuevo registro |
| `show($id)` | GET | `/{modulo}/show/{id}` | Ver detalle |
| `edit($id)` | GET | `/{modulo}/edit/{id}` | Mostrar formulario de edición |
| `update($id)` | POST | `/{modulo}/update/{id}` | Guardar cambios |
| `delete($id)` | POST | `/{modulo}/delete/{id}` | Soft delete |

### 3. Patrón completo de un método store()

```php
/**
 * Guarda un nuevo registro.
 *
 * Qué hace:
 * - Verifica CSRF
 * - Valida campos requeridos
 * - Llama al modelo para insertar
 * - Registra en historial/auditoría
 * - Redirige al listado con mensaje
 *
 * @return void
 */
public function store()
{
    PermissionMiddleware::check('{entidad}_create');
    csrf_verify();

    // ===============================
    // RECOGER Y VALIDAR DATOS
    // ===============================
    $data = [
        'campo1' => trim($_POST['campo1'] ?? ''),
        'campo2' => trim($_POST['campo2'] ?? ''),
    ];

    // Validación básica
    if (empty($data['campo1'])) {
        $_SESSION['flash_error'] = 'El campo es requerido';
        $this->redirect('/{modulo}/create');
        return;
    }

    // ===============================
    // GUARDAR EN BASE DE DATOS
    // ===============================
    $model = new {Entidad}Model();
    $id = $model->create($data);

    if ($id) {
        // Registrar en historial
        (new ContractHistoryModel())->add($id, AuthService::userId(), 'creado', 'Registro creado');
        $_SESSION['flash_success'] = 'Registro creado exitosamente';
        $this->redirect('/{modulo}/show/' . $id);
    } else {
        $_SESSION['flash_error'] = 'Error al crear el registro';
        $this->redirect('/{modulo}/create');
    }
}
// Fin de la función store()
```

### 4. Seguridad obligatoria

En cada método:

```php
// SIEMPRE verificar permisos al inicio
PermissionMiddleware::check('permiso_requerido');

// SIEMPRE verificar CSRF en POST
csrf_verify();

// SIEMPRE validar que el ID existe
$record = $model->getById((int)$id);
if (!$record) {
    $_SESSION['flash_error'] = 'Registro no encontrado';
    $this->redirect('/{modulo}');
    return;
}
```

### 5. Mensajes flash (feedback al usuario)

```php
// Éxito
$_SESSION['flash_success'] = 'Operación exitosa';

// Error
$_SESSION['flash_error'] = 'Ocurrió un error';

// Warning
$_SESSION['flash_warning'] = 'Atención: revise los datos';
```

### 6. Roles y permisos del sistema

| Rol | Permisos |
|---|---|
| **admin** | Todos |
| **vendedor** | contracts_*, companies_*, services_view, templates_view, payments_view, attachments_upload, reports_* |
| **cobranzas** | contracts_view, companies_view, payments_*, attachments_upload, reports_* |
| **recepcion** | contracts_view, companies_view, attachments_upload |

---

## 📁 Routing (cómo funciona)

El Router resuelve automáticamente:
```
URL: /{controller}/{method}/{param1}/{param2}

Ejemplo: /contracts/show/15
→ new ContractController()->show(15)
```

- El nombre del controller en la URL es en minúsculas
- El Router añade "Controller" automáticamente y hace ucfirst()
- NO hay archivo de rutas separado

---

## ❌ Lo que NO hacer

- ❌ No poner lógica de negocio compleja en controllers — moverla a Services
- ❌ No olvidar `PermissionMiddleware::check()` al inicio de cada método
- ❌ No olvidar `csrf_verify()` en métodos POST
- ❌ No hacer echo/print — usar `$this->view()`, `$this->json()` o `$this->redirect()`
- ❌ No acceder a `$_POST` sin trim/cast
