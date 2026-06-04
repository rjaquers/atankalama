# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Despliegue

Para subir archivos a producción, usa el workflow `@ftpcocina` indicando las rutas de los archivos:

```
@ftpcocina @[app/controllers/CocinaController.php] @[app/views/cocina/index.php]
```

El script en producción es `python3 /Volumes/Mac_Secundario/htdocs/cocina/ftp_sync.py <archivos...>`.

No hay build ni bundler — los cambios en CSS/JS se suben directamente vía FTP.

## Entornos

| Variable | Local | Producción |
|---|---|---|
| `BASE_URL` | `http://cocina/` | `https://www.atankalama.com/cocina/` |
| `DB_NAME` | `cocina` | `cat6852_atan` |
| `DB_USER` | `rodrigo` | `cat6852_cotiza` |

El entorno se detecta automáticamente en `app/config/config.php` por el hostname. Las credenciales SMTP vienen del archivo `.env` en la raíz.

## Arquitectura

**Router:** `public/index.php` despacha rutas mediante `?page=controlador/metodo/arg`. Ejemplo: `?page=recepcion/crear` → `RecepcionController::crear()`. No usa AccesoBootstrap (sin autenticación OTP integrada en esta app actualmente).

**MVC sin framework:**
- Controladores: `app/controllers/` — cargan el modelo y hacen `include` de la vista
- Modelos: `app/models/` — PDO directo, singleton via `app/config/db.php`
- Vistas: `app/views/{modulo}/` — HTML+PHP puros
- Layout compartido: `public/static/templates/head.php`, `menu.php`, `footer.php`

**AJAX:** `CocinaController::index()` detecta `XMLHttpRequest` y retorna JSON. El dashboard de cocina hace polling cada 30 s desde `public/static/js/app.js`.

## Dominio funcional

**Módulos del sistema:**

| Módulo | Controlador | Propósito |
|---|---|---|
| `cocina` | `CocinaController` | Dashboard en tiempo real de órdenes pendientes |
| `recepcion` | `RecepcionController` | Crear órdenes con productos del catálogo |
| `producto` | `ProductoController` | CRUD del catálogo de productos |
| `reporte` | `ReporteController` | Reportes de ventas por rango de fechas |
| `estadistica` | `EstadisticaController` | Métricas y analytics |

**Código de colores de órdenes** (calculado en `CocinaModel::cerrarOrden()`):
- Verde: entregado a tiempo (≥15 min de margen)
- Amarillo: leve retraso (5–14 min)
- Rojo: crítico (<5 min)
- Negro: fuera de tiempo

**Prefijo de tablas BD:** `cocina_` (ej: `cocina_ordenes`, `cocina_detalle_ordenes`, `cocina_productos`).

**Reporte diario:** `app/cron/reporte_diario.php` — ejecutar a las 23:59. Envía email a gabrielacarrasco@atankalama.com, jorgeperez@atankalama.com, rjaquers@gmail.com.

## Dependencias PHP

```json
"phpoffice/phpspreadsheet": "^1.18",
"phpmailer/phpmailer": "^6.10"
```

Instaladas en `vendor/` via Composer. No ejecutar `composer install` en producción — subir `vendor/` directo si se agrega dependencia nueva.
