<?php
/**
 * AccesoService — Verificador de permisos centralizado
 *
 * Consulta acc_secciones / acc_roles / acc_rol_secciones en cat6852_hotel_tickets
 * para determinar si un usuario autenticado puede acceder a una ruta.
 *
 * ── Integración en cualquier app ──────────────────────────────────────────
 *
 *   require_once $_SERVER['DOCUMENT_ROOT'] . '/shared/AccesoService.php';
 *
 *   // Opción A: guard (redirige o muestra 403 automáticamente)
 *   AccesoService::requerir('cocina', $email, 'cocina/cerrar', 'index.php?page=auth/login');
 *
 *   // Opción B: solo verificar (para menús dinámicos, etc.)
 *   if (AccesoService::puedeAcceder('cocina', $email, 'cocina/cerrar')) { ... }
 *
 *   // Opción C: obtener todas las secciones accesibles (para menú)
 *   $accesibles = AccesoService::seccionesAccesibles('cocina', $email);
 *
 * ── Lógica de evaluación ──────────────────────────────────────────────────
 *   1. Sección no registrada en acc_secciones  → $defaultSiNoExiste (false por defecto)
 *   2. Sección tipo 'publica'                  → true  (cualquier usuario autenticado)
 *   3. Sección tipo 'restringida'              → true solo si el usuario tiene un rol
 *                                                con esa sección habilitada en esa app
 *
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 */
class AccesoService
{
    // ── Conexión interna ──────────────────────────────────

    private static function pdo(): PDO
    {
        require_once __DIR__ . '/acceso_db.php';
        return acceso_pdo();
    }

    // ── API pública ───────────────────────────────────────

    /**
     * Verifica si el usuario puede acceder a una sección de una app.
     *
     * @param string $appSlug           Slug de la app  (ej: 'cocina')
     * @param string $email             Email del usuario autenticado
     * @param string $ruta              Ruta a verificar (ej: 'cocina/cerrar')
     * @param bool   $defaultSiNoExiste Qué retornar si la sección no está registrada
     */
    public static function puedeAcceder(
        string $appSlug,
        string $email,
        string $ruta,
        bool   $defaultSiNoExiste = false
    ): bool {
        $pdo = self::pdo();

        // 1. Buscar la sección
        $stmt = $pdo->prepare("
            SELECT s.id, s.tipo
            FROM acc_secciones s
            JOIN chk_apps a ON a.id = s.app_id
            WHERE a.slug   = ?
              AND s.slug   = ?
              AND s.estado = 'activo'
              AND a.estado = 'activo'
            LIMIT 1
        ");
        $stmt->execute([$appSlug, $ruta]);
        $seccion = $stmt->fetch(PDO::FETCH_ASSOC);

        // No registrada → política por defecto
        if (!$seccion) {
            return $defaultSiNoExiste;
        }

        // Pública → cualquier usuario autenticado pasa
        if ($seccion['tipo'] === 'publica') {
            return true;
        }

        // Restringida → verificar rol del usuario
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS tiene
            FROM acc_rol_secciones rs
            JOIN acc_usuario_roles ur ON ur.rol_id  = rs.rol_id
            JOIN chk_usuarios u       ON u.id       = ur.usuario_id
            WHERE u.email        = ?
              AND u.estado       = 'activo'
              AND rs.seccion_id  = ?
        ");
        $stmt->execute([$email, $seccion['id']]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Guard: verifica acceso y actúa si no tiene permiso.
     * Si $email es null → redirige al login.
     * Si tiene sesión pero sin permiso → muestra 403.
     *
     * @param string      $appSlug
     * @param string|null $email       null = sin sesión activa
     * @param string      $ruta
     * @param string      $urlLogin    URL de login de la app
     * @param bool        $defaultSiNoExiste
     */
    public static function requerir(
        string  $appSlug,
        ?string $email,
        string  $ruta,
        string  $urlLogin,
        bool    $defaultSiNoExiste = false
    ): void {
        if (!$email) {
            header('Location: ' . $urlLogin);
            exit;
        }

        if (!self::puedeAcceder($appSlug, $email, $ruta, $defaultSiNoExiste)) {
            http_response_code(403);
            self::mostrar403($email, $ruta, $appSlug);
            exit;
        }
    }

    /**
     * Retorna los slugs de secciones accesibles para un usuario en una app.
     * Útil para construir menús dinámicos (ocultar lo que no puede ver).
     *
     * @param string $appSlug
     * @param string $email
     * @return string[]
     */
    public static function seccionesAccesibles(string $appSlug, string $email): array
    {
        $stmt = self::pdo()->prepare("
            SELECT DISTINCT s.slug
            FROM acc_secciones s
            JOIN chk_apps a ON a.id = s.app_id
            WHERE a.slug   = ?
              AND s.estado = 'activo'
              AND a.estado = 'activo'
              AND (
                  s.tipo = 'publica'
                  OR s.id IN (
                      SELECT rs.seccion_id
                      FROM acc_rol_secciones rs
                      JOIN acc_usuario_roles ur ON ur.rol_id = rs.rol_id
                      JOIN chk_usuarios u       ON u.id     = ur.usuario_id
                      WHERE u.email = ? AND u.estado = 'activo'
                  )
              )
        ");
        $stmt->execute([$appSlug, $email]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // ── Pantalla 403 ──────────────────────────────────────

    private static function mostrar403(string $email, string $ruta, string $app): void
    {
        echo '<!DOCTYPE html><html lang="es"><head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <title>Acceso denegado · Atankalama</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        </head>
        <body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">
            <div class="text-center p-5">
                <i class="bi bi-shield-x text-danger" style="font-size:4rem;"></i>
                <div class="display-4 fw-bold text-danger mt-2">403</div>
                <h5 class="mt-2">Sin acceso a esta sección</h5>
                <p class="text-muted">
                    <strong>' . htmlspecialchars($email) . '</strong>
                    no tiene permisos para
                    <code>' . htmlspecialchars($ruta) . '</code>
                    en <strong>' . htmlspecialchars($app) . '</strong>.
                </p>
                <p class="text-muted small">Contacta al administrador del sistema si crees que es un error.</p>
                <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </body></html>';
    }
}
