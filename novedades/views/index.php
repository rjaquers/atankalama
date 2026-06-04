<?php
// Redirigimos al enrutador principal en la carpeta public
$route = $_GET['route'] ?? 'novedades/form';
header("Location: ../public/index.php?route=" . urlencode($route));
exit;
