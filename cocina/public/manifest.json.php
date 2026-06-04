<?php
header('Content-Type: application/json');
?>
{
"name": "Cocina Atankalama",
"short_name": "Cocina",
"start_url": "/cocina/public/index.php?page=cocina/index",
"display": "standalone",
"background_color": "#ffffff",
"theme_color": "#0d6efd",
"orientation": "portrait",
"icons": [
{
"src": "/cocina/public/icons/icon-192x192.png",
"sizes": "192x192",
"type": "image/png"
},
{
"src": "/cocina/public/icons/icon-512x512.png",
"sizes": "512x512",
"type": "image/png"
}
]
}