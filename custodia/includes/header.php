<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='utf-8'>
    <title>Tickets – Listado</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <!-- FontAwesome -->
    <link rel='stylesheet'    href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css'>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css' rel='stylesheet'
          integrity='sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB' crossorigin='anonymous'>


    <style>
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, 'Helvetica Neue', Arial, sans-serif;
            margin: 20px;
            color: #2D3748
        }

        h1 {
            margin: 0 0 16px
        }

        .filters {
            display: grid;
            grid-template-columns:repeat(6, 1fr);
            gap: 8px;
            margin-bottom: 14px
        }

        .filters input, .filters select {
            padding: 8px;
            border: 1px solid #E2E8F0;
            border-radius: 8px
        }

        .filters button {
            padding: 10px;
            border: 0;
            background: #2153A7;
            color: #fff;
            border-radius: 8px;
            cursor: pointer
        }

        .filters a {
            display: inline-block;
            text-align: center;
            padding: 10px;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            text-decoration: none;
            color: #2D3748;
            background: #fff
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #E2E8F0;
            font-size: 14px;
            vertical-align: top
        }

        th {
            background: #F7FAFC;
            text-align: left
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 12px;
            border: 1px solid #E2E8F0
        }

        .muted {
            color: #4A5568
        }

        .pagination {
            margin-top: 14px;
            display: flex;
            gap: 6px;
            flex-wrap: wrap
        }

        .pagination a, .pagination span {
            padding: 8px 10px;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            text-decoration: none;
            color: #2D3748
        }

        .pagination .active {
            background: #2153A7;
            color: #fff;
            border-color: #2153A7
        }

        .empty {
            padding: 18px;
            border: 1px dashed #E2E8F0;
            border-radius: 12px;
            background: #F7FAFC
        }

        @media (max-width: 980px) {
            .filters {
                grid-template-columns:repeat(2, 1fr)
            }
        }
    </style>
</head>
<body>
<style>
    .navbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #2153A7;
        color: #fff;
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .navbar .brand {
        font-size: 20px;
        font-weight: bold;
        letter-spacing: 1px;
    }

    .navbar .menu {
        display: flex;
        gap: 16px;
    }

    .navbar .menu a {
        color: #fff;
        text-decoration: none;
        font-size: 14px;
        padding: 6px 10px;
        border-radius: 6px;
        transition: background 0.2s;
    }

    .navbar .menu a:hover {
        background: rgba(255, 255, 255, 0.15);
    }


</style>
<style>
    .btn-print {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 8px;
        background: #2153A7;
        color: #fff;
        text-decoration: none;
        font-size: 13px
    }

    .btn-print:hover {
        opacity: .9
    }
</style>

<!-- NAVBAR desplegable -->
<div class='navbar no-print' >
    <div class='brand'>
        <img src='https://www.atankalama.com/custodia/img/Logo-Atankalama.png' alt='Atankalama'/>
        <span>Sistemas Atankalama</span>
    </div>

    <nav class='menu'>
        <!-- <details class='dropdown'>
            <summary>Encuesta</summary>
            <div class='dropdown-menu'>
                <a href='https://goo.su/arsKb' target='_blank'>Resultados</a>
            </div>
        </details>
-->

        <details class='dropdown'>
            <summary>SISWifi</summary>
            <div class='dropdown-menu'>
                <a href='/custodia/wifi/imprimir' target="_blank">Imprimir</a>
            </div>
        </details>

        <details class='dropdown'>
            <summary>SISCustodia</summary>
            <div class='dropdown-menu'>
                <a href='/custodia/tickets/custodia/nuevo'>Crear</a>
                <a href='/custodia/tickets/custodia/listar'>Listar</a>
            </div>
        </details>




            <a href='https://www.atankalama.com/login/index.php?route=dashboard' class='plain'>
                Inicio
            </a>
            <?php if (!empty($_SESSION['cus_admin_email'])): ?>
            <a href='<?= url('/logout') ?>' class='plain' style='color:#ffcccc'>
                Salir
            </a>
            <?php endif; ?>


    </nav>

    <button class='hamburger' aria-label='Abrir menú' aria-expanded='false' aria-controls='menuMobile'>☰</button>
</div>

<style>
    .navbar {
        position: sticky;
        top: 0;
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        background: #0a66c2;
        color: #fff;
    }

    .brand {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700
    }

    .brand img {
        height: 28px;
        width: auto;
        display: block;
        border-radius: 6px;
        background: #fff
    }

    .menu {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .menu .plain {
        color: #ec0d0d;
        text-decoration: none;
        padding: 8px 10px;
        border-radius: 8px;
        border: 1px solid transparent;
    }

    .menu .plain:hover {
        background: rgba(255, 255, 255, .12)
    }

    /* Dropdowns con <details> */
    .dropdown {
        position: relative;
        list-style: none
    }

    .dropdown > summary {
        list-style: none;
        cursor: pointer;
        user-select: none;
        outline: none;
        padding: 8px 12px;
        border-radius: 8px;
        color: #fff;
        border: 1px solid transparent;
    }

    .dropdown > summary::-webkit-details-marker {
        display: none
    }

    .dropdown > summary::after {
        content: '▾';
        margin-left: 6px;
        font-size: 12px;
        opacity: .9;
    }

    .dropdown[open] > summary {
        background: #084f98
    }

    .dropdown-menu {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        min-width: 180px;
        background: #fff;
        color: #111;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .15);
        padding: 8px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .dropdown-menu a {
        display: block;
        padding: 8px 10px;
        border-radius: 8px;
        text-decoration: none;
        color: #111;
    }

    .dropdown-menu a:hover {
        background: #f2f6ff
    }

    /* Móvil: hamburguesa */
    .hamburger {
        display: none;
        margin-left: auto;
        background: transparent;
        border: 1px solid rgba(255, 255, 255, .6);
        color: #fff;
        padding: 6px 10px;
        border-radius: 8px;
        cursor: pointer;
    }

    @media (max-width: 820px) {
        .menu {
            display: none;
            width: 100%
        }

        .navbar {
            flex-wrap: wrap
        }

        .hamburger {
            display: block
        }

        .menu.open {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 8px
        }

        .dropdown {
            width: 100%
        }

        .dropdown-menu {
            position: static;
            box-shadow: none;
            border: 1px solid #eee
        }

        .dropdown > summary {
            background: rgba(255, 255, 255, .08)
        }
    }
</style>
<style>
    /* === Contraste fuerte para la barra y los menús === */
    .navbar{ background:#0a66c2 !important; color:#fff !important; }
    .navbar .brand, .navbar .brand span{ color:#fff !important; }

    .navbar .menu a.plain{ color:#fff !important; }
    .navbar .menu a.plain:hover{ background:rgba(255,255,255,.14) !important; }

    .navbar .dropdown > summary{
        color:#fff !important;
        background:rgba(0,0,0,.12);
        border:1px solid rgba(255,255,255,.35);
        border-radius:8px;
    }
    .navbar .dropdown[open] > summary{
        background:#084f98 !important;
        border-color:#084f98 !important;
    }

    /* Submenú: fondo claro y texto oscuro para legibilidad */
    .navbar .dropdown-menu{
        background:#ffffff !important;
        color:#111 !important;
        border:1px solid #e5e7eb !important;
        box-shadow:0 10px 30px rgba(0,0,0,.15);
    }
    .navbar .dropdown-menu a{
        color:#111 !important;
    }
    .navbar .dropdown-menu a:hover{
        background:#f2f6ff !important;
    }

    /* Móvil: mantiene contraste al desplegar */
    @media (max-width:820px){
        .navbar .menu.open{ background:#0a66c2 !important; }
        .navbar .dropdown > summary{ background:rgba(255,255,255,.12) !important; color:#fff !important; }
        .navbar .dropdown-menu{ background:#fff !important; color:#111 !important; border-color:#e5e7eb !important; }
    }
</style>

<script>
    // Cerrar otros dropdowns al abrir uno
    document.addEventListener('toggle', function (e) {
        if (e.target.matches('.dropdown') && e.target.open) {
            document.querySelectorAll('.dropdown[open]').forEach(function (d) {
                if (d !== e.target) d.open = false;
            });
        }
    }, true);

    // Cerrar al hacer clic fuera
    document.addEventListener('click', function (e) {
        const anyOpen = document.querySelector('.dropdown[open]');
        if (!anyOpen) return;
        const isInside = anyOpen.contains(e.target);
        if (!isInside) anyOpen.open = false;
    });

    // Hamburguesa (móvil)
    (function () {
        const btn = document.querySelector('.hamburger');
        const menu = document.querySelector('.menu');
        if (!btn || !menu) return;
        btn.addEventListener('click', function () {
            const isOpen = menu.classList.toggle('open');
            btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    })();
</script>
