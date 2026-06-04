
# CLAUDE.md

Este archivo proporciona instrucciones a Claude Code (claude.ai/code) para trabajar con el código de este repositorio.

## Descripción general del proyecto

**Custodia** es una aplicación de gestión hotelera en PHP 7.4 (objetivo `7.4.33` según `composer.json`) que corre en la ruta `/custodia`. Gestiona:
- **Colaciones** — gestión de lotes de comida con vales por persona
- **Tickets de custodia** — tickets de custodia de equipaje con impresión térmica
- **Empresas** — gestión de empresas vinculadas a lotes de comida
- **Servicios adicionales** — servicios extra por lote de comida
- **Importación Excel** — carga masiva de personas en lotes de comida
- **Escaneo de QR** — validación de cédula/ID QR en el check-in de servicios

## Ejecución de la aplicación

Esta es una app PHP servida por un servidor web local (Apache/Nginx) en `http://localhost/custodia`. No hay paso de compilación. El despliegue es por copia de archivos; Composer gestiona las dependencias PHP.

```bash
# Instalar/actualizar dependencias PHP
composer install
composer update
```

Base de datos: MySQL, nombre por defecto `cat6852_hotel_tickets`. Las credenciales se leen de variables de entorno, con valores por defecto en `connections/conec6.php`.

## Arquitectura

### Punto de entrada y ruteo

Todo el tráfico HTTP llega a `index.php`, que:
1. Define `BASE_URL = '/custodia'`
2. Inicializa la conexión a BD vía `connections/conec6.php`
3. Instancia todos los controladores
4. Define rutas usando una clase `Router` simple en línea
5. Despacha con `$router->dispatch()`

Las rutas siguen el patrón `$router->add('METHOD', '/regex/pattern', callable)`. Las capturas regex se pasan como argumentos posicionales al handler.

También existe una clase `Router` más completa en `src/Core/Router.php` y un `Controller` base en `src/Core/Controller.php`, pero la mayoría de los controladores en `/controllers/` **no** extienden esa clase base — usan los globals (`global $db`, `global $mysqli`) directamente.

### Acceso a base de datos

`connections/conec6.php` crea un `$mysqli` global y lo alias como `$db` y `$conn`. También define un helper global `db()`. Los controladores llaman `global $db;` al inicio de los métodos o usan el método privado `$this->db()` que lee el mismo global. **Se usa MySQLi en todo el código — no PDO, no ORM.**

### Estructura MVC

```
controllers/   — Clases controlador (uno por dominio, sin herencia de base en la práctica)
models/        — Clases modelo (ColacionLote, ColacionVoucher, Ticket, Empresa, etc.)
views/         — Vistas PHP por include, organizadas por dominio
  layout/      — head.php, footer.php (incluidos manualmente en cada vista)
connections/   — Conexión BD (conec6.php) y config/polyfills (config.php)
src/Core/      — Clases base Controller y Router (no usadas en la práctica)
lib/           — phpqrcode.php (generación QR)
sql/           — schema.sql (esquema de referencia)
```

Las vistas se renderizan vía `include __DIR__.'/../views/...'` directamente en los métodos de los controladores. No hay motor de plantillas.

### Dependencias clave (vendor/)

- `mike42/escpos-php` — soporte para impresoras térmicas ESC/POS (usado para boletas 80mm)
- `phpoffice/phpspreadsheet` — importación/exportación Excel para carga masiva de lotes

### Compatibilidad PHP

El objetivo es PHP 7.4. `connections/config.php` incluye polyfills para `str_contains()` y `str_starts_with()` para que el código funcione también en PHP 8+. Evitar sintaxis exclusiva de PHP 8.

### Zona horaria

Todas las operaciones de fecha usan `America/Santiago` (definido en `connections/conec6.php`).

## Conceptos clave del dominio

- **Lote** (`colacion_lote`) — lote de comida para una empresa en un rango de fechas, contiene varias personas
- **Voucher** (`colacion_voucher`) — vale individual imprimible por persona y lote
- **Ticket** (`tickets`) — ticket de custodia de equipaje con código público secuencial diario
- **Adicional** (`colacion_adicional`) — servicios extra (ej. desayuno, snack) asociables a un lote
- **Persona** — huésped vinculado a un lote; puede cargarse por Excel o manualmente

## Agregar rutas

Todas las rutas se definen en `index.php`. Para agregar una nueva ruta, añade `$router->add(...)` antes de la llamada final a `$router->dispatch()`. Instancia el controlador arriba en el archivo junto a los otros si es necesario.
