<!--
  ===================================================
  = Proyecto: Hotel Atankalama - Sistema de Inventario =
  = Autor: Rodrigo Jaque Escobar                     =
  = Contacto: rjaquers@gmail.com                     =
  = Fecha: <?= date('Y') ?>                          =
  ===================================================
-->
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <!-- ==============================
         PWA - Configuración General
    =============================== -->
    <link rel='manifest' href='manifest.json'>
    <meta name='theme-color' content='#0d6efd'>
    <meta name='application-name' content='Inventario Atankalama'>

    <!-- ==============================
         FAVICONS / ICONOS
    =============================== -->
    <link rel='icon' type='image/png' sizes='16x16' href='assets/icons/apple-icon-57x57.png'>
    <link rel='icon' type='image/png' sizes='32x32' href='assets/icons/apple-icon-60x60.png'>
    <link rel='icon' type='image/png' sizes='48x48' href='assets/icons/apple-icon-72x72.png'>
    <link rel='shortcut icon' href='assets/icons/favicon.ico' type='image/x-icon'>

    <!-- ==============================
         iOS - Configuración específica
    =============================== -->
    <meta name='apple-mobile-web-app-capable' content='yes'>
    <meta name='apple-mobile-web-app-status-bar-style' content='black-translucent'>
    <meta name='apple-mobile-web-app-title' content='Inventario Atankalama'>
    <link rel='apple-touch-icon' href='assets/icons/android-icon-192x192.png'>

    <link rel='apple-touch-startup-image' href='assets/icons/icon-512x512.png'>

<!-- ==============================
      CSS Datatables
 =============================== -->
<link rel='stylesheet' href='https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css'>
<link rel='stylesheet' href='https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css'>


<!-- ==============================
         Registrar Service Worker
    =============================== -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/inventario/service-worker.js')
                    .then(() => console.log('✅ Service Worker registrado correctamente'))
                    .catch(err => console.error('❌ Error al registrar SW:', err));
            });
        }
    </script>

    <!-- ==============================
         Estilos y dependencias
    =============================== -->
    <title>InventarioAtankalama</title>
    <!-- ==============================
     Librerías de estilos y scripts
=============================== -->

    <link href='assets/css/main.css' rel='stylesheet'> <!-- tu hoja de estilos responsive -->

    <!-- Scripts para menús y Bootstrap -->
<!--    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js' defer></script>-->
<!--    <script src='assets/js/main.js' defer></script>-->

    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>



