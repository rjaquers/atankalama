# Starter Kit RKM - Historial de Cambios

Todas las modificaciones relevantes del framework se documentan en este archivo.

Formato basado en Keep a Changelog.

## [v6.1.1] - 2026-05-12
### Changed
- Improved visual feedback for completed tasks: cards now appear in grayscale, with reduced opacity and struck-through titles.
- Labels on completed tasks now appear muted.

## [v6.1.0] - 2026-05-12
### Added
- Feature "Tarea terminada": New explicit state for completed tasks.
- Auto-mark task as completed when archived.
- Checkbox in card modal to manually mark tasks as finished.
- Statistics in Dashboard now use the explicit completion state.

## [v6.0.2] - 2026-05-12
### Fixed
- Fatal error in `tarjeta/_modal.php` caused by accessing private property `$this->usuario_id`.
- Added `usuario_id` to view data in `TarjetaController::modal`.

## [v6.0.1] - 2026-05-12
### Changed
- Research and structural analysis of the `tablero/ver` route.
- Detailed review of `TableroController` and `app/views/tablero/index.php`.

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
