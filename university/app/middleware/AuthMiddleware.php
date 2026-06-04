<!--
  = Proyecto: Starter Kit RKM =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com.                   =
  = Fecha: <?= date('Y') ?>                  =

-->
<?php
class AuthMiddleware
{
    public static function check()
    {
        // Validar sesión del portal central (atankalama.com/login)
        $email = $_SESSION['portal_email']   ?? null;
        $exp   = $_SESSION['portal_expires'] ?? 0;

        if (!$email || time() > (int)$exp) {
            ob_end_clean();
            header('Location: https://www.atankalama.com/login/index.php?route=auth/login');
            exit;
        }

        // Sincronizar user_id + perfil en sesión si no están cargados aún
        if (!empty($_SESSION['user_id']) && isset($_SESSION['perfil'])) return;

        $db   = (new Database())->connect();
        $stmt = $db->prepare(
            "SELECT id, perfil FROM chk_usuarios WHERE email = ? AND estado = 'activo' LIMIT 1"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($userId, $perfil);
        $stmt->fetch();
        $stmt->close();

        if (!$userId) {
            ob_end_clean();
            header('Location: https://www.atankalama.com/login/index.php?route=auth/login');
            exit;
        }

        $_SESSION['user_id'] = (int)$userId;
        $_SESSION['perfil']  = $perfil;
    }
}
