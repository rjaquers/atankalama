# Starter Kit RKM - Historial de Cambios

Todas las modificaciones relevantes del framework se documentan en este archivo.

Formato basado en Keep a Changelog.

## [v6.0.0] - 2026-03-04
### Added
- EventDispatcher (motor de eventos).
- PWA instalable (manifest + service worker).
- Offline sync: cola local + sincronización al volver internet con aviso.
- QR scanner (html5-qrcode) integrado en demo del dashboard.
- Notificaciones multicanal: internal/email/telegram (telegram opcional).
- Endpoint API ejemplo: notificaciones recientes.

### Notes
- PHPMailer es opcional y no viene incluido. Para SMTP recomendado, instalar en /vendor/phpmailer/.

## [v5.0.0] - 2026-03-04
- Notificaciones multicanal (primera iteración).
- Offline forms (primera iteración).
- PWA (primera iteración).

## [v4.0.0] - 2026-03-04
- Generadores/Reportes/Soporte Docker (diseño).

## [v3.0.0] - 2026-03-04
- Panel admin / menús dinámicos (diseño).

## [v2.0.0] - 2026-03-04
- Autoload / Router REST / Roles-permisos (diseño).

## [v1.0.0] - 2026-03-04
- MVC base / login / middleware / Bootstrap + iconos.
