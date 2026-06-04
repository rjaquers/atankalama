# Módulo Universidad Atankalama

> Documento maestro del módulo de capacitación continua.
> Ubicación sugerida: raíz del módulo (`/univ/CLAUDE.md`) o raíz del proyecto si Claude Code trabaja sobre todo el sistema.

---

## 1. Contexto y alcance

Sistema de capacitación interna para el Hotel Atankalama. Los empleados toman cursos estructurados en 10 páginas, rinden un examen, obtienen créditos y, cuando corresponde, un certificado. RRHH y jefaturas reciben reportes automáticos de avance e incumplimiento.

**Escala esperada:** <100 usuarios concurrentes. Picos esperados los lunes (capacitaciones semanales).

**Integración:** Este módulo vive dentro del framework MVC propio existente. Usa `chk_usuarios` para login y perfiles. No reemplaza nada del sistema base; se suma con prefijo `univ_`.

---

## 2. Stack y entorno

- PHP 8.4+ (compatible 7.x)
- MySQL 5.7+ con `mysqli` y consultas preparadas
- Bootstrap + DataTables en frontend
- PHPMailer para notificaciones
- FPDF para certificados (sin Composer)
- Desarrollo: MAMP local (Mac)
- Producción: cPanel vía FTP, sin SSH ni CLI
- Cron jobs configurados desde cPanel

**Prohibido en este módulo:** Composer, dependencias que requieran CLI en producción, librerías pesadas tipo dompdf.

---

## 3. Actores y flujo principal

### 3.1. Actores
- **Alumno:** cualquier perfil de `chk_usuarios` (Garzón, Recepcionista, etc.).
- **Administrador:** perfiles Administrador, RRHH, Gerencia.
- **Jefatura:** perfiles "Jefatura de ..." (reciben reportes; no gestionan cursos).

### 3.2. Ciclo de capacitación
1. Alumno inicia sesión con clave única existente.
2. Ve catálogo de cursos asignados (automáticos por perfil + obligatorios legales).
3. Inicia o continúa un curso.
4. Avanza por las 10 páginas. Progreso se guarda en eventos discretos (NO heartbeat).
5. Al completar la página 10, se habilita "Iniciar Evaluación".
6. Rinde examen con tiempo límite. Máximo 3 intentos; cuenta el último.
7. Si aprueba: se calculan créditos, se genera certificado PDF, se notifica a alumno + jefatura.
8. Si vence el curso (ej: recertificación anual), el sistema genera automáticamente un nuevo ciclo.

---

## 4. Reglas de negocio clave

### 4.1. Tipos de curso
- **obligatorio_legal:** exigido por ley (ej: manipulación de alimentos). Todos los empleados relevantes lo toman.
- **obligatorio_area:** exigido según el perfil/área (ej: garzones toman "Servicio en mesa").
- **opcional:** suma créditos para bonos o incentivos.

### 4.2. Vencimiento y recertificación
- Cada curso define su propia `vigencia_meses` (NULL = no vence).
- Al aprobar, se calcula `fecha_vencimiento`.
- Un cron diario marca como `vencido` los enrollments expirados y genera nuevo ciclo automáticamente.

### 4.3. Intentos de examen
- Máximo 3 intentos por ciclo.
- **Cuenta el último**, no el mejor.
- Si falla el intento 3, enrollment pasa a `bloqueado`.
- RRHH puede habilitar manualmente un nuevo ciclo (no se autogenera).

### 4.4. Versionado de cursos
- Editar un curso incrementa `version`.
- Si el admin marca `requiere_retoma = 1` al publicar, los enrollments aprobados anteriores pasan a `vencido` y se generan nuevos con la versión nueva.
- Si `requiere_retoma = 0`, los aprobados conservan su estado (útil para corregir typos o ajustes menores).

### 4.5. Asignación automática
Al crear un usuario en `chk_usuarios`, un trigger en PHP (desde el controlador de creación) inserta enrollments según `univ_cursos_por_perfil`.

### 4.6. Banco de preguntas
- El banco debe tener mínimo 3x el número de preguntas del examen (si examen = 10, banco >= 30).
- El sistema no bloquea la creación de cursos con menos, pero muestra advertencia.
- Se randomiza orden de preguntas Y orden de alternativas.

### 4.7. Anti-trampa básico
- `timestamp_inicio` y `timestamp_fin` del examen guardados.
- Tiempo límite obligatorio (default 15 minutos).
- Si el tiempo de respuesta es sospechosamente bajo, el intento se marca pero igual cuenta (revisión manual posterior).

---

## 5. Decisiones de arquitectura

### 5.1. HTML estático para contenido
El contenido de las 10 páginas cambia muy poco. Al publicar/guardar un curso, se generan archivos HTML estáticos en `/cursos_cache/curso_{id}/pagina_{orden}.html`. El player los incluye directamente. Solo progreso y examen pasan por PHP/BD.

### 5.2. Progreso por eventos, no heartbeat
- `UPDATE` al entrar a página (guarda `pagina_actual` y timestamp).
- `UPDATE` al hacer clic en "Siguiente" (marca página como vista).
- Sin pings periódicos. Evita saturar cPanel con 80 garzones capacitándose el mismo lunes.

### 5.3. Historial completo de evaluaciones
`univ_evaluations` NUNCA se sobrescribe. Cada intento es una fila. Auditoría legal + análisis de cursos mal diseñados.

### 5.4. Respuestas normalizadas, no JSON
`univ_evaluation_answers` es tabla hija. Permite `GROUP BY question_id` para el reporte de "preguntas más falladas" sin parsear JSON en PHP.

### 5.5. Log de cron
`univ_cron_log` registra cada ejecución. Sin esto, los fallos de cron en cPanel son invisibles.

### 5.6. Verificación de certificados
URL pública tipo `/univ/verificar/{hash}` en lugar de QR. Mismo resultado, sin librería QR. QR se puede agregar después si alguien lo pide.

---

## 6. Esquema de base de datos

Tablas con prefijo `univ_`:

| Tabla | Propósito |
|-------|-----------|
| `univ_courses` | Catálogo maestro. Tipo, créditos, vigencia, versión, configuración de examen. |
| `univ_pages` | 10 páginas por curso. Tipos: html, pdf, video. |
| `univ_questions` | Banco de preguntas por curso. |
| `univ_options` | Alternativas de cada pregunta. |
| `univ_cursos_por_perfil` | Asignación automática perfil → cursos. |
| `univ_enrollments` | Matrícula del alumno. Una fila por (user, course, ciclo). Estado actual. |
| `univ_evaluations` | Historial completo de intentos. Nunca se sobrescribe. |
| `univ_evaluation_answers` | Detalle de respuestas por intento. |
| `univ_cron_log` | Registro de ejecución de cron jobs. |

**Archivo SQL de creación:** `univ_schema.sql` (en la misma carpeta que este documento).

### 6.1. Relaciones clave
- `univ_pages.course_id` → `univ_courses.id` (CASCADE)
- `univ_questions.course_id` → `univ_courses.id` (CASCADE)
- `univ_options.question_id` → `univ_questions.id` (CASCADE)
- `univ_enrollments.course_id` → `univ_courses.id` (CASCADE)
- `univ_enrollments.user_id` → FK lógica a `chk_usuarios.id` (sin constraint física para no acoplar)
- `univ_evaluations.enrollment_id` → `univ_enrollments.id` (CASCADE)
- `univ_evaluation_answers.evaluation_id` → `univ_evaluations.id` (CASCADE)

---

## 7. Estructura de archivos

```
/controllers/
  UnivController.php             ← rutas del alumno (catalogo, player, examen)
  UnivAdminController.php        ← panel admin/RRHH (CRUD cursos, reportes)

/models/
  UnivCourseModel.php
  UnivEnrollmentModel.php
  UnivEvaluationModel.php
  UnivQuestionModel.php

/views/univ/
  dashboard.php                  ← home del alumno
  course_player.php              ← "Focus Mode" con sidebar de 10 páginas
  exam.php                       ← examen pregunta a pregunta
  admin/
    course_builder.php           ← constructor con accordion
    questions_manager.php
    reports.php

/services/
  UnivExamService.php            ← calificación, registro de intento
  UnivCertificateService.php     ← generación PDF con FPDF

/cron/
  univ_check_vencimientos.php    ← diario 6am
  univ_reporte_semanal.php       ← lunes 8am

/cursos_cache/                   ← HTML estático generado (permisos escritura)
/uploads/univ_pdfs/              ← PDFs subidos como contenido de páginas
/uploads/univ_certificados/      ← certificados PDF generados
```

**Justificación de los servicios:** `UnivExamService` y `UnivCertificateService` concentran lógica que toca múltiples modelos (calificar un examen escribe en 3 tablas; generar certificado requiere leer usuario + curso + evaluación). Sin el servicio, esa lógica se duplicaría en controladores. Cumple la regla: "capa extra solo si reduce errores".

---

## 8. Convenciones de código

- PHP 7.4+ sintaxis. No usar `match`, `enum`, ni features exclusivas de 8.1+.
- Siempre `mysqli` con prepared statements. Nunca concatenar SQL.
- Validación robusta en backend. Frontend valida para UX, backend valida para seguridad.
- Cálculos y reglas de negocio al guardar, no al leer.
- Logs en archivos en `/logs/univ/` además de BD cuando aplique.
- Nombres de variables, funciones y comentarios en español.
- Código copiable y ejecutable. Sin pseudocódigo.

---

## 9. Plan de implementación por fases

| Fase | Alcance | Estimado |
|------|---------|----------|
| 1 | Ejecutar SQL + CRUD básico de cursos en admin | 1-2 días |
| 2 | Constructor de páginas (accordion) + banco de preguntas | 2-3 días |
| 3 | Dashboard alumno + course_player + navegación | 3-4 días |
| 4 | Motor de examen: aleatoriedad, timer, calificación, historial | 2-3 días |
| 5 | Cron vencimientos + cron reporte semanal + certificado PDF + email | 1-2 días |

**Total estimado:** 9-14 días de trabajo efectivo.

---

## 10. Fuera de alcance v1

No implementar en la primera versión (se pueden agregar después si hay demanda real):

- Código QR en certificados (reemplazado por URL de verificación pública).
- Heatmap visual de áreas rezagadas (reporte tabular con DataTables es suficiente).
- Multi-hotel real (la columna `hotel_id` queda en las tablas, pero en v1 siempre = 1).
- Gamificación (rankings, badges, niveles).
- App móvil nativa (el sistema debe ser responsive y funcionar en navegador móvil).
- Foros o comentarios entre alumnos.
- Videoconferencias en vivo.

---

## 11. Preguntas abiertas / por decidir

Cosas que no están cerradas y hay que resolver durante la implementación:

- [ ] ¿Los créditos acumulados tienen algún uso real (bonos, reconocimientos), o son solo métricos?
- [ ] ¿Los cursos obligatorios legales tienen plazo máximo para tomarlos desde la asignación?
- [ ] ¿El certificado PDF usa plantilla fija o tiene diseño personalizable por curso?
- [ ] ¿Los jefes deben poder reasignar manualmente cursos adicionales a su equipo?
- [ ] ¿RRHH necesita poder exportar reportes a Excel/CSV desde el panel?

---

## 12. Reglas para Claude al trabajar en este módulo

Cuando Claude (chat o Code) ayude con este proyecto:

1. **Respetar el framework MVC existente.** No proponer Laravel, Symfony ni similares.
2. **No proponer Composer ni dependencias que requieran CLI.** Todo debe desplegarse vía FTP.
3. **PHP 7.4+ compatible.** Si se usa sintaxis de 8.x, marcarlo explícitamente.
4. **mysqli preparado siempre.** Nunca PDO en este proyecto (consistencia con el resto del sistema).
5. **Validar contra este documento antes de proponer capas nuevas.** Si una abstracción no reduce errores o aclara lógica, no va.
6. **Preguntar antes de asumir** detalles del framework propio (estructura de rutas, cómo se cargan vistas, cómo funciona la sesión, etc.).
7. **Respuestas en español.**
8. **Código copiable y ejecutable.** Sin fragmentos incompletos.

---

*Última actualización: abril 2026*
