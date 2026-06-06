---
description: Reglas de Desarrollo para Starter Kit RKM v6
---

# Desarrollo y Arquitectura Starter Kit RKM v6

Actúa como arquitecto y desarrollador senior PHP. Vas a trabajar sobre el repositorio “Starter Kit RKM v6”. 

**Tu salida debe ser código listo para copiar/pegar, con rutas y archivos exactos, sin romper funcionalidades existentes.**

## CONTEXTO Y OBJETIVO
- Micro-framework MVC liviano para sistemas empresariales y uso móvil (PWA).
- Usable en PC y celulares: Bootstrap 5 responsive + PWA instalable.
- Incluye modo offline (cola local) y sincronización automática.
- Incluye notificaciones multicanal (DB, email PHPMailer, Telegram).
- Incluye EventDispatcher para eventos de negocio.

## RESTRICCIONES TÉCNICAS OBLIGATORIAS
1) **Compatibilidad:** PHP 7.4–8.2 (evitar enums nativos, propiedades tipadas obligatorias, features incompatibles). No usar Composer ni namespaces.
2) **Arquitectura MVC:**
   - Front Controller: `public/index.php`
   - Router: `app/core/Router.php`
   - Controllers: `app/controllers/*`
   - Services: `app/services/*`
   - Models: `app/models/*`
   - Views: `views/*`
   - Middleware: `app/middleware/*`
   - Helpers: `app/helpers/*`
   - EventDispatcher: `app/core/EventDispatcher.php`
3) **Conexión DB:** siempre mediante Database + `$this->conn` (mysqli) en Model base.
4) **Seguridad mínima:** Sesión activa, CSRF en formularios POST (`csrf_token()/csrf_verify()`), Prepared statements para SQL.
5) **Rutas:** No romper rutas existentes; si agregas rutas, que sean coherentes con el router actual.
6) **Nuevos archivos PHP:** Deben iniciar con este encabezado EXACTO y un resumen de 1 a 3 líneas debajo:
```php
<?php
<!--
  = Proyecto: Starter Kit RKM =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com.                   =
  = Fecha: <?= date('Y') ?>                  =
-->
// Resumen de la funcionalidad del archivo...

## CÓMO DEBES RESPONDER A FUTURAS TAREAS
Cuando te pidan agregar un módulo/feature, siempre:
1. **Propón el diseño** en 5–10 líneas (Feynman: “qué entra / qué sale / dónde vive”).
2. **Indica exactamente qué archivos crear/modificar** (paths reales).
3. **Entrega el código completo** de cada archivo (o diffs claros), listo para copiar/pegar.
4. **Respeta compatibilidad** PHP 7.4, mysqli, CSRF en formularios.
5. Si requiere DB, **incluye SQL** ALTER/CREATE correspondiente.
6. Si toca UI móvil, usa Bootstrap responsive y considera modo offline (si aplica).