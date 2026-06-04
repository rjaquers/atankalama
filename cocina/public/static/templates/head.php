<meta charset='UTF-8'>
<title><?= isset($pageTitle) ? $pageTitle . ' - Atankalama Cocina' : 'Cocina - Hotel Atankalama' ?></title>
<?php 
// El refresco automático solo es necesario en el monitor de cocina (Dashboard)
// Lo desactivamos para formularios y otras vistas para no perder datos.
$currentPage = $_GET['page'] ?? '';
if ($currentPage === 'cocina/index'): ?>
    <meta http-equiv='refresh' content='60'>
<?php endif; ?>
<script>localStorage.removeItem('darkMode');</script>
<!-- Favicon dinámico (Letra C para Cocina) -->
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 rx=%2220%22 fill=%22%230d6efd%22/><text y=%22.9em%22 font-size=%2280%22 x=%2250%%22 text-anchor=%22middle%22 font-family=%22serif%22 fill=%22white%22 font-weight=%22bold%22>C</text></svg>">

<link href='<?= BASE_URL ?>public/static/css/bootstrap.min.css' rel='stylesheet'>
<meta name='msapplication-TileColor' content='#ffffff'>
<meta name='theme-color' content='#ffffff'>

<!-- jQuery (primero) -->
<script src='https://code.jquery.com/jquery-3.7.0.min.js'></script>

<script src='<?= BASE_URL ?>public/static/css/bootstrap.bundle.min.js'></script>

<!-- UI UX Pro Max Global Styles -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href='<?= BASE_URL ?>public/static/css/pro-max.css' rel='stylesheet'>

<!--//Boostrap icons-->
<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css'>


<!-- DataTables CSS + Buttons -->
<link rel='stylesheet' href='https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css'>
<link rel='stylesheet' href='https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css'>


<!--
  ===================================================
  = Proyecto: Hotel Atankalama - Sistema de Cocina  =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com.                   =
  = Fecha: <?= date('Y') ?>                  =
  ===================================================
-->