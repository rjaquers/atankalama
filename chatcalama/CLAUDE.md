# CLAUDE.md

Este archivo proporciona orientación a Claude Code (claude.ai/code) al trabajar con el código en este repositorio.

## Qué hace esta aplicación

**chatCalama** es un sistema interno de chat en tiempo real y gestión de tareas para los empleados del Hotel Atankalama. Soporta chat individual y grupal, seguimiento de tareas/tickets, solicitudes de mantenimiento, control de acceso basado en roles y notificaciones multicanal (correo electrónico, Telegram, push de Firebase). Construido como una PWA con sincronización fuera de línea.

## Ejecución de la aplicación

**Con Docker (recomendado):**
```bash
cd docker
docker-compose up -d
# Aplicación disponible en http://localhost:8080
```

**Sin Docker:**
- Apunte el DocumentRoot de Apache a `/public`
- Habilite `mod_rewrite`
- PHP 8.2+ con la extensión `mysqli`
- Copie `.env` y configure las credenciales de la base de datos

**Configuración de la base de datos (primera vez):**
```sql
-- Importar en orden:
mysql < sql/schema.sql
mysql < sql/chat_tables.sql
mysql < sql/primer_admin.sql
```

Administrador por defecto: `admin@rkm.local` / `admin123`

## Variables `.env` Requeridas

```
DB_HOST, DB_NAME, DB_USER, DB_PASS   # Conexión MySQL
SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_ENCRYPTION  # Correo electrónico
JWT_SECRET, JWT_EXPIRES              # Tokens de API móvil
# Opcional:
FCM_SERVER_KEY                       # Notificaciones push de Firebase
TELEGRAM_BOT_TOKEN, TELEGRAM_CHAT_ID # Integración con Telegram
```

## Arquitectura

Framework MVC de PHP personalizado sin dependencias externas (excepto PHPMailer opcional en `/vendor`).

**Flujo de la solicitud:**
```
HTTP → public/index.php → Router::dispatch()
    → AuthMiddleware → Controller
        → Service (lógica de negocio) → Model (consultas MySQLi)
        → EventDispatcher → NotificationService (email/telegram/FCM/interno)
    → View (plantillas PHP) o respuesta JSON (API)
```

**Directorios clave:**
- `app/core/` — Framework: `Router`, `Controller`, `Model`, `EventDispatcher`, autocargador PSR-4
- `app/controllers/` — Controladores web; `app/controllers/api/` — Endpoints de la API REST (autenticación JWT)
- `app/models/` — Capa de acceso a datos (MySQLi, un modelo por entidad)
- `app/services/` — Lógica de negocio: `AuthService`, `MailService`, `JwtService`, `NotificationService`, `ImageService`, `OtpService`, `SyncService`
- `app/middleware/` — Protectores de autenticación y permisos
- `app/views/` — Plantillas PHP usando diseños (`app/views/layouts/`)
- `config/config.php` — Carga del entorno, constantes, zona horaria (`America/Santiago`)
- `config/database.php` — Singleton de conexión MySQLi
- `public/` — Controlador frontal, activos (Bootstrap 5 + Bootstrap Icons local), subidas, manifiesto PWA + service worker

**Tablas principales de la base de datos** (todas con prefijo `chat_`): `chat_usuarios`, `chat_areas`, `chat_conversaciones`, `chat_mensajes`, `chat_participantes`, `chat_tareas`, `chat_mantencion`, `chat_notificaciones`, `chat_roles`, `chat_permisos`, `chat_rol_permisos`, `offline_queue`

## Autenticación

- **Web**: Sesiones PHP con tokens CSRF en todas las solicitudes POST
- **Móvil/API**: Tokens JWT (implementación personalizada, expiración de 24h por defecto)
- **Roles**: Admin, Jefe de Área, Operador — 15 permisos granulares a través de tablas RBAC
- Las cookies son HTTPOnly + Secure + SameSite Strict en producción

## Frontend

Vanilla JS (sin paso de compilación, sin Node.js). Bootstrap 5 + Bootstrap Icons servidos localmente. Características de PWA: almacenamiento en caché del service worker en `public/service-worker.js`, cola de acciones fuera de línea en `public/assets/js/offline-sync.js`. Subida de archivos de máximo 10MB con conversión opcional a WebP.

## Sin herramientas de Compilación o Linting

No hay sistema de compilación, gestor de paquetes ni suite de pruebas. Los cambios en PHP/JS/CSS surten efecto inmediatamente (no se necesita paso de compilación en el desarrollo local con Docker).
