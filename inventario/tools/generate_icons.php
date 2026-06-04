<?php

$source = __DIR__.'/../assets/icons/icons.png';
$destDir = __DIR__.'/../assets/icons/';
$sizes = [16, 32, 48, 96, 192, 512];

if (! file_exists($source)) {
    die("Archivo base no encontrado.\n");
}

foreach ($sizes as $size) {
    $target = $destDir."icon-$size.png";
    $cmd = "convert $source -resize {$size}x{$size} $target";
    echo "Creando $target\n";
    exec($cmd);
}

// Favicon ICO (multi-resolución)
$cmd = "convert {$destDir}icon-16.png {$destDir}icon-32.png {$destDir}icon-48.png {$destDir}favicon.ico";
exec($cmd);
echo "✅ Iconos generados correctamente.\n";

