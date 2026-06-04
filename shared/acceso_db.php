<?php
/**
 * Conexión PDO y constantes SMTP centralizadas (cat6852_hotel_tickets).
 * Usada internamente por AccesoService y AccesoBootstrap.
 * No exponer este archivo públicamente.
 *
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 */

// ── SMTP centralizado ────────────────────────────────────
define('ACCESO_SMTP_HOST', 'mail.atankalama.com');
define('ACCESO_SMTP_PORT', 465);
define('ACCESO_SMTP_USER', 'sistema@atankalama.com');
define('ACCESO_SMTP_PASS', 'nCgXA,[&0Mwu2LO%@yM');
define('ACCESO_SMTP_FROM', 'sistema@atankalama.com');
define('ACCESO_SMTP_NAME', 'Hotel Atankalama');

function acceso_pdo(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=localhost;dbname=cat6852_hotel_tickets;charset=utf8mb4',
            'cat6852_rje',
            'MM)[9&VYxl[W',
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
    return $pdo;
}
