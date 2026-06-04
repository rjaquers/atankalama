<?php
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../config/config.php";
require_once "../config/database.php";
require_once "../app/core/Autoload.php";
require_once "../app/helpers/csrf.php";
require_once "../app/helpers/logger.php";
require_once "../app/helpers/network.php";

if (session_status() === PHP_SESSION_NONE) session_start();

// ── Logout centralizado ──────────────────────────────────────────────────────
// Limpiar sesión local y redirigir al logout del hub central
if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) !== false) {
    $_logoutBasePath = rtrim((string) parse_url(BASE_URL, PHP_URL_PATH), '/');
    $_logoutReqPath  = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $_logoutClean    = trim(substr($_logoutReqPath, strlen($_logoutBasePath)), '/');
    if ($_logoutClean === 'logout') {
        unset($_SESSION['con_admin_email'], $_SESSION['con_admin_expires']);
        header('Location: https://www.atankalama.com/login/index.php?route=auth/logout');
        exit;
    }
}

// ── SSO Bridge: sincronizar sesión del hub central ──────────────────────────
// Fuente de verdad: chk_usuarios (misma BD cat6852_hotel_tickets).
// Si el hub tiene sesión activa, verificamos en chk_usuarios y hacemos UPSERT
// en doc_users para mantener nombre sincronizado. Role: admin (id=1) por default.
// Si el hub cerró sesión, limpiamos docs también.
(function () {
    $hubEmail   = $_SESSION['con_admin_email']   ?? null;
    $hubExpires = (int)($_SESSION['con_admin_expires'] ?? 0);

    if ($hubEmail && time() < $hubExpires) {
        // Solo resincronizar si el email de docs no coincide con el hub
        if (($_SESSION['user_email'] ?? null) !== $hubEmail) {
            $db   = new Database();
            $conn = $db->connect();

            // Validar en chk_usuarios (fuente de verdad)
            $stmt = $conn->prepare(
                "SELECT nombre, apellido FROM chk_usuarios
                 WHERE email = ? AND estado = 'activo' LIMIT 1"
            );
            $stmt->bind_param('s', $hubEmail);
            $stmt->execute();
            $chk = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$chk) {
                // Email no activo en el hub → limpiar sesión de docs
                unset(
                    $_SESSION['user_id'], $_SESSION['user_name'],
                    $_SESSION['user_email'], $_SESSION['role_id'],
                    $_SESSION['role'], $_SESSION['permissions']
                );
                return;
            }

            $nombre = trim($chk['nombre'] . ' ' . $chk['apellido']);
            $roleId = 1; // admin por default para usuarios autenticados via hub

            // UPSERT en doc_users
            $stmt = $conn->prepare(
                "SELECT id FROM doc_users WHERE email = ? LIMIT 1"
            );
            $stmt->bind_param('s', $hubEmail);
            $stmt->execute();
            $docRow = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($docRow) {
                $userId = (int)$docRow['id'];
                $stmt = $conn->prepare(
                    "UPDATE doc_users SET name = ?, role_id = ?, status = 1 WHERE id = ?"
                );
                $stmt->bind_param('sii', $nombre, $roleId, $userId);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO doc_users (name, email, role_id, status) VALUES (?, ?, ?, 1)"
                );
                $stmt->bind_param('ssi', $nombre, $hubEmail, $roleId);
                $stmt->execute();
                $userId = (int)$conn->insert_id;
                $stmt->close();
            }

            $_SESSION['user_id']     = $userId;
            $_SESSION['user_name']   = $nombre;
            $_SESSION['user_email']  = $hubEmail;
            $_SESSION['role_id']     = $roleId;
            $_SESSION['role']        = 'admin';
            $_SESSION['permissions'] = (new PermissionModel())->getPermissionsByRoleId($roleId);
        } else {
            // Hub activo y email ya coincide: garantizar que el rol sea admin
            // (puede haber quedado otro valor si el usuario se logueó directo vía OTP)
            if (($_SESSION['role'] ?? '') !== 'admin') {
                $_SESSION['role']    = 'admin';
                $_SESSION['role_id'] = 1;
                $_SESSION['permissions'] = (new PermissionModel())->getPermissionsByRoleId(1);
            }
        }
    } elseif (!$hubEmail && isset($_SESSION['user_id'])) {
        // Hub cerró sesión → cerrar docs también
        unset(
            $_SESSION['user_id'], $_SESSION['user_name'],
            $_SESSION['user_email'], $_SESSION['role_id'],
            $_SESSION['role'], $_SESSION['permissions']
        );
    }
})();

// Registrar listeners de eventos (ejemplo)
EventDispatcher::listen("offline_synced", function($data){
    // Ejemplo: dejar trazabilidad o disparar notificación
    // (Evita spamear correo: en producción filtra por módulo/acción)
    app_log("EVENT offline_synced: " . json_encode($data, JSON_UNESCAPED_UNICODE));
});

$router = new Router();
$router->dispatch();
