<?php
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 * Este software es propiedad exclusiva de su autor.
 * Se concede un derecho de uso limitado al cliente. No se transfiere
 * la propiedad del código ni de la aplicación.
 *
 * @author  Rodrigo Jaque Escobar
 * @project Sistema de Precios - Hotel Atankalama
 */

// ── Leer precios desde la BD ─────────────────────────────────────────────────
$categorias = [];
$tipos      = [];
$grilla     = [];
$desde_bd   = false;

try {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/shared/acceso_db.php';
    $pdo = acceso_pdo();

    $categorias = $pdo->query(
        "SELECT id, nombre FROM pre_categorias WHERE activo = 1 ORDER BY orden, nombre"
    )->fetchAll(PDO::FETCH_ASSOC);

    $tipos = $pdo->query(
        "SELECT id, nombre FROM pre_tipos WHERE activo = 1 ORDER BY orden, nombre"
    )->fetchAll(PDO::FETCH_ASSOC);

    $precios_raw = $pdo->query(
        "SELECT tipo_id, categoria_id, precio FROM pre_precios"
    )->fetchAll(PDO::FETCH_ASSOC);

    foreach ($precios_raw as $p) {
        $grilla[$p['tipo_id']][$p['categoria_id']] = $p['precio'];
    }

    $desde_bd = !empty($categorias) && !empty($tipos);
} catch (Throwable $e) {
    // Fallback a datos estáticos si la BD no está disponible
    $desde_bd = false;
}
?>
<!doctype html>
<html lang='es' data-bs-theme='auto'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <meta name='description' content='Listado de precios de atankalama'>
    <meta name='author' content='Rodrigo Jaque Escobar'>
    <meta name='generator' content='Phpstorm'>
    <!-- Eliminado el refresco automático para mayor estabilidad -->
       <META http-equiv='refresh' content='10;  url=index01.php'>
    <title>Atankalama - Precios</title>

<!--     fonts google-->
    <link rel='preconnect' href='https://fonts.googleapis.com'>
    <link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
    <link href='https://fonts.googleapis.com/css2?family=Ballet:opsz@16..72&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Playwrite+FR+Trad:wght@100..400&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap'
          rel='stylesheet'>
</head>
<body>

<header>
    <center>
        <h1>Bienvenidos a Atankalama</h1>
    </center>
</header>

<center class='container-fluid' id='contenidoTabla'>

    <?php if ($desde_bd): ?>
    <!-- Tabla generada dinámicamente desde la BD -->
    <div class="tabla-precios" style="display:block;">
        <table border="1" width="90%">
            <thead>
            <tr style="background-color: yellow">
                <th>Hotel</th>
                <?php foreach ($categorias as $cat): ?>
                    <th><?= htmlspecialchars($cat['nombre']) ?></th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($tipos as $tipo): ?>
            <tr align='center'>
                <td style="font-weight: bold"><?= htmlspecialchars($tipo['nombre']) ?></td>
                <?php foreach ($categorias as $cat): ?>
                    <td><?= htmlspecialchars($grilla[$tipo['id']][$cat['id']] ?? '--') ?></td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php else: ?>
    <!-- Fallback estático (BD no disponible) -->
    <div class="tabla-precios" style="display:block;">
        <table border="1" width="90%">
            <thead>
            <tr style="background-color: yellow">
                <th>Hotel</th>
                <th>Boutique</th>
                <th>Executive</th>
                <th>Loft</th>
                <th>Inn</th>
                <th>Juku</th>
            </tr>
            </thead>
            <tbody>
            <tr align='center'>
                <td style="font-weight: bold">Single</td>
                <td>$77.350</td><td>$65.450</td><td>$59.500</td><td>$47.600</td><td>$38.600</td>
            </tr>
            <tr align='center'>
                <td style='font-weight: bold'>Doble o Matrimonial</td>
                <td>$89.250</td><td>$77.350</td><td>$71.400</td><td>$59.500</td><td>$47.600</td>
            </tr>
            <tr align='center'>
                <td style='font-weight: bold'>Triple</td>
                <td>$101.150</td><td>$89.250</td><td>$83.300</td><td>$71.400</td><td>--</td>
            </tr>
            <tr align='center'>
                <td style='font-weight: bold'>Cuádruple</td>
                <td>$113.050</td><td>$101.150</td><td>$95.200</td><td>$83.300</td><td>--</td>
            </tr>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</center>

<footer>
    <div class="text-center py-2" style="font-size:.95rem">
        Precios incluyen I.V.A.
    </div>
    <div class="text-center text-muted py-1" style="font-size:.65rem">
        &copy; <?= date('Y') ?> Rodrigo Jaque Escobar &mdash; Todos los derechos reservados.
    </div>
</footer>

<style>
    body {
        min-height: 100vh;
        margin: 0;
        font-family: 'Roboto', sans-serif;
        display: flex;
        flex-direction: column;
        position: relative;
    }
    /* Imagen de fondo fija, cubriendo toda la pantalla */
    body::before {
        content: '';
        position: fixed;
        inset: 0;
        background: url('https://www.atankalama.cl/wp-content/uploads/2024/09/GALERIA_2.jpeg')
                    center center / cover no-repeat;
        filter: blur(1px) brightness(0.90);
        transform: scale(1.03); /* evita bordes blancos por el blur */
        z-index: -1;
    }
    /* Velo blanco semitransparente sobre la imagen */
    body::after {
        content: '';
        position: fixed;
        inset: 0;
        background: rgba(255, 255, 255, 0.45);
        z-index: -1;
    }
    #contenidoTabla {
        font-size: 15px;
        margin-top: 30px;
        margin-bottom: 30px;
        width: 100vw;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .tabla-precios {
        box-shadow: 0 8px 32px 0 rgba(60, 40, 10, 0.25);
        border-radius: 24px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.85);
        margin-bottom: 32px;
        transition: box-shadow 0.3s;
        width: 92vw;
        max-width: 1600px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background: transparent;
    }

    th {
        font-size: 2.2rem;
        color: #b3541a;
        background: linear-gradient(90deg, #ffe5b4 0%, #e9c46a 100%);
        font-family: 'Roboto', sans-serif;
        padding: 32px 24px;
        border-bottom: 4px solid #e76f51;
        letter-spacing: 1px;
        text-shadow: 1px 1px 0 #fff6e0;
    }

    td {
        font-size: 2.2rem;
        color: #264653;
        padding: 32px 24px;
        font-family: 'Roboto', sans-serif;
        border-bottom: 1px solid #e9c46a;
        background: linear-gradient(90deg, #fff8ee 0%, #f7e9d0 100%);
        transition: background 0.3s;
    }
    tr:last-child td {
        border-bottom: none;
    }

    tr:hover td {
        background: linear-gradient(90deg, #ffe5b4 0%, #f4a261 100%);
    }

    /* Encabezado principal */
    header h1 {
        font-family: 'Ballet', cursive;
        font-size: 3.5rem;
        color: #b3541a;
        margin-top: 32px;
        margin-bottom: 0;
        letter-spacing: 2px;
        text-shadow: 2px 2px 8px #fff6e0, 0 2px 8px #e9c46a;
    }

    footer {
        background: rgba(255, 255, 255, 0.7);
        border-top: 2px solid #e9c46a;
        box-shadow: 0 -2px 16px 0 rgba(60, 40, 10, 0.08);
        margin-top: auto;
    }
    footer center {
        color: #b3541a;
        font-weight: 500;
        letter-spacing: 1px;
    }

    /* Se eliminan las clases de variantes de Roboto para simplificar y modernizar el CSS */
</style>

<script>
// Ciclo automático entre secciones de precios
const tablas = document.querySelectorAll('.tabla-precios');
let actual = 0;
setInterval(() => {
    tablas[actual].style.display = 'none';
    actual = (actual + 1) % tablas.length;
    tablas[actual].style.display = 'block';
}, 8000); // Cambia cada 8 segundos
</script>
 
</body>
</html>
