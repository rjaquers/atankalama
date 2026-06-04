# Starter Kit RKM v6

Base MVC en PHP 8.2 compatible con PHP 7.4, con:
- Router + Front Controller
- Login + middleware
- EventDispatcher
- PWA instalable
- Offline sync (cola local + sync)
- Notificaciones multicanal (internal/email/telegram opcional)
- QR scanner demo (html5-qrcode)

## Instalación rápida (local)
1) Crear DB e importar:
   - sql/schema.sql
   - sql/seed.sql

2) Configurar credenciales en config/config.php (DB_* y MAIL_*)

3) Apuntar tu DocumentRoot a /public o usar VirtualHost.

## Usuario demo
- admin@rkm.local
- admin123

## PHPMailer (opcional, recomendado)
Descarga PHPMailer y coloca `src/` dentro de:
- /vendor/phpmailer/src/

MailService detecta PHPMailer automáticamente si existe.
