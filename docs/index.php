<?php
/**
 * Redirección opcional en caso de que mod_rewrite no esté activo.
 * El punto de entrada principal es public/index.php.
 */
header("Location: public/");
exit;
