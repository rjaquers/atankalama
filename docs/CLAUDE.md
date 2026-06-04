# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Visión General

**Hotel Atankalama - Sistema de Contratos**: Aplicación web PHP MVC para gestión de contratos, reservas, pagos y espacios. Basado en el framework ligero "Starter Kit RKM v6" (PHP 8.2 compatible con 7.4).

## Comandos de desarrollo

```bash
# Instalar dependencias (Dompdf, PHPMailer)
composer install

# Generador de módulos CRUD
php tools/build_crud.php
```

El despliegue al servidor lo realiza el usuario manualmente. No hay test suite ni linter configurados en este proyecto.

## Configuración del entorno

- Credenciales en `.env` (fuente única): `DB_*`, `SMTP_*`, `BASE_URL`, `WEBP_QUALITY`
- `config/config.php` carga el `.env` vía `EnvLoader` y define constantes (`APP_ROOT`, `VIEW_PATH`, `BASE_URL`, `UPLOAD_BASE_PATH`, etc.)
- Para overrides locales: crear `config/config.local.php` (no versionar)
- Apache: `DocumentRoot` apunta a `/public`

## Arquitectura

### Routing

URL pattern: `?url=controller/method/param1/param2`

El `Router` (`app/core/Router.php`) convierte `?url=contracts/edit/5` → `ContractsController::edit(5)`. Soporta hasta 2 parámetros posicionales. No existe archivo de rutas — el dispatch es automático por convención de nombres. El controlador base (`app/core/Controller.php`) provee `view($path, $data)`, `json($data, $status)` y `redirect($path)`.

### Capas

| Directorio | Responsabilidad |
|---|---|
| `app/core/` | Router, Controller base, Model base, Autoload, EventDispatcher |
| `app/controllers/` | Controladores de dominio + `api/` para endpoints JSON |
| `app/models/` | Acceso a datos con MySQLi (sentencias preparadas); `Model` base inyecta `$this->conn` |
| `app/services/` | Lógica de negocio: ContractService, PaymentService, PdfGeneratorService, FileUploadService, ImageConverterService, OtpService, MailService, AlertService, SyncService, NotificationService, SpaceUploadService |
| `app/middleware/` | `AuthMiddleware::handle()` verifica `$_SESSION['user_id']`; `PermissionMiddleware::check('permiso')` verifica permisos del rol |
| `app/helpers/` | csrf.php, logger.php, network.php, EnvLoader.php |
| `app/reports/` | PdfReport.php, ExcelReport.php |
| `views/` | Plantillas PHP por dominio; layout en `views/layouts/header.php` + `footer.php` |
| `sql/` | schema.sql, seed.sql y migraciones incrementales |
| `.agents/skills/` | Skills reutilizables: controller-builder, model-builder, service-builder, view-builder, documentation-writer |

### SSO Bridge (autenticación)

`public/index.php` implementa un puente SSO entre el hub central del hotel y esta app. En cada request:

1. Lee `$_SESSION['con_admin_email']` y `con_admin_expires` (sesión del hub central).
2. Si la sesión del hub es válida, verifica el email en `chk_usuarios` (BD `cat6852_hotel_tickets`).
3. Hace UPSERT en `doc_users` con el nombre sincronizado y establece `role_id = 1` (admin).
4. Carga en sesión: `user_id`, `user_name`, `user_email`, `role_id`, `role`, `permissions`.
5. Si el hub cerró sesión (`con_admin_email` ausente), limpia la sesión de docs también.

`AuthMiddleware::handle()` solo verifica `isset($_SESSION['user_id'])` — la sincronización ocurre antes del dispatch.

### Roles y permisos

| Rol | Permisos clave |
|---|---|
| `admin` | Acceso total (bypassa toda verificación de permisos) |
| `vendedor` | `contracts_*`, `companies_*`, `payments_view`, `reports_*`, `attachments_upload` |
| `cobranzas` | `contracts_view`, `companies_view`, `payments_*`, `reports_*` |
| `recepcion` | `contracts_view`, `companies_view`, `attachments_upload` |

Permisos cargados en `$_SESSION['permissions']` (array de strings). Verificar en controllers con `PermissionMiddleware::check('permiso')`. En vistas, usar `AuthService::hasPermission('permiso')` o `AuthService::isAdmin()`.

### Base de datos

- BD principal: `cat6852_hotel_docs` (tablas prefijo `doc_`, MySQLi)
- BD compartida del hotel (auth): `cat6852_hotel_tickets` (tablas `chk_*`, MySQLi directo en `public/index.php`)
- ORM **no se usa**: solo MySQLi con sentencias preparadas vía `$this->conn->prepare()`
- **Soft delete**: siempre `UPDATE SET active = 0`, nunca `DELETE FROM`. Incluir `WHERE active = 1` en todas las lecturas.

### Relaciones clave del dominio

```
doc_contracts → doc_companies (company_id)
doc_contracts → doc_hotels (N:M via doc_contract_hotels)
doc_contracts → doc_services (N:M via doc_contract_services)
doc_contracts → doc_contract_tiers (1:N) — bandas de precio por pax
doc_contracts → doc_contract_payments (1:N)
doc_contracts → doc_contract_attachments (1:N)
doc_contracts → doc_users (created_by)
```

Código único de contrato: patrón `CTR-YYYY-NNN` generado por `ContractService`.

## Patrones de código obligatorios

### Controller

```php
public function store()
{
    PermissionMiddleware::check('entidad_create');  // primero
    csrf_verify();                                   // segundo en POST

    $data = ['campo' => trim($_POST['campo'] ?? '')];
    // ... validar, persistir, historial, flash, redirect
}
```

Métodos CRUD estándar: `index`, `create`, `store`, `show($id)`, `edit($id)`, `update($id)`, `delete($id)`.

### Flash messages

```php
$_SESSION['flash_success'] = 'Mensaje de éxito';
$_SESSION['flash_error']   = 'Mensaje de error';
$_SESSION['flash_warning'] = 'Mensaje de alerta';
```

Las vistas muestran y destruyen los flash con `unset()` inmediatamente después de renderizar.

### Vistas

- Stack frontend: Bootstrap 5.3, DataTables, Chart.js, Font Awesome 6, Bootstrap Icons.
- Layout: `<?php require VIEW_PATH . "/layouts/header.php"; ?>` ... `<?php require VIEW_PATH . "/layouts/footer.php"; ?>`
- Partials (archivos con prefijo `_`): incluidos con `require VIEW_PATH . "/modulo/_partial.php"`.
- URLs: siempre `BASE_URL . "/ruta"`, nunca hardcodeadas.
- Siempre `htmlspecialchars()` al mostrar datos del usuario; token CSRF `<?= csrf_token() ?>` en formularios.
- KPI cards usan clase CSS: `kpi-card` con variantes `success`, `warning`, `danger`, `info`.

### File uploads

`FileUploadService` valida, convierte imágenes JPG/PNG a WebP via `ImageConverterService`, y guarda en `uploads/contracts/{company_id}/{año-mes}/`. La calidad WebP se configura con `WEBP_QUALITY` en `.env`.

## Skills disponibles (`.agents/skills/`)

Al crear código nuevo, invocar el skill correspondiente para seguir las convenciones del proyecto:

- **controller-builder**: genera `app/controllers/{Entidad}Controller.php` con CRUD completo, CSRF, permisos y flash.
- **model-builder**: genera `app/models/{Entidad}Model.php` con CRUD, filtros dinámicos y PHPDoc obligatorio.
- **service-builder**: genera `app/services/{Nombre}Service.php` con encabezado de clase, try/catch en transacciones y PHPDoc.
- **view-builder**: genera vistas con layout, flash messages, DataTables y formularios con CSRF.
- **documentation-writer**: completa o agrega PHPDoc a archivos existentes.

## Reglas de oro

1. No romper lo que funciona — agregar, no reemplazar.
2. Centralizar lógica en Services; ni vistas ni JS deben tener lógica de negocio.
3. Simplicidad extrema — si no se explica en 30 segundos, hay sobreingeniería.
4. Validar en backend; nunca exponer emails o stack traces al usuario.
5. Todo en español: variables, comentarios, mensajes de usuario.

## Despliegue

El despliegue es manual — el usuario sube los archivos al servidor de producción directamente. No hay pipeline CI/CD.
