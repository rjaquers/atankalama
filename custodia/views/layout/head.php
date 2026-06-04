<?php
if (!function_exists('asset')) {
    require_once __DIR__ . '/../../config/app.php';
}
?>
<!doctype html>
<html lang='es'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Tickets Custodia</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href="<?=asset('css/app.css')?>" rel='stylesheet'>
</head>
<body>
<nav class='navbar navbar-expand-lg bg-body-tertiary mb-3'>
    <div class='container-fluid'>
        <a class='navbar-brand' href='index.php?c=tickets&a=index'>Tickets</a>
        <div class='d-flex'>
            <a href='index.php?c=tickets&a=create' class='btn btn-primary'>Nuevo</a>
        </div>
    </div>
</nav>
<div class='container'>