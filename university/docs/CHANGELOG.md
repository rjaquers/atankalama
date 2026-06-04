# Starter Kit RKM - Historial de Cambios

Todas las modificaciones relevantes del framework se documentan en este archivo.

Formato basado en Keep a Changelog.

## [v6.1.0] - 2026-05-28
### Added
- **Módulo Universidad: Certificados de Aprobación**
  - Nuevo diseño de diploma profesional con logo corporativo.
  - Generación dinámica con nombre del alumno y curso.
  - Cálculo automático de vigencia (6 meses desde aprobación).
  - Función de impresión optimizada para una sola página.
- **Módulo Universidad: Gestión de Exámenes**
  - Sistema de revisión detallada para administradores (ver en qué falló el alumno).
  - Historial de intentos por alumno con puntajes y duración.
  - Columna de "Mejor Puntaje" en lista de alumnos con ordenamiento.
- **Módulo Universidad: Experiencia del Alumno**
  - Barra de progreso dinámica basada en el total real de páginas.
  - Pantalla final de curso con opción para repetir o iniciar evaluación.
  - Temporizadores de permanencia mínima integrados en el examen.
- **Navegación:** Enlace directo a "Mis Cursos" en el menú superior para todos los usuarios.

### Fixed
- Error en `UnivQuestionModel` que impedía guardar correctamente la respuesta seleccionada.
- Error de tipos en `UnivExamService` que evitaba que el estado "aprobado" se guardara en la base de datos.
- Problemas de desbordamiento visual en el editor de preguntas con el resaltado de respuestas correctas.

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
