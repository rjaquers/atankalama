</div>

<?php include __DIR__.'/inc_proyect.php'; ?>

<style>
    /* Espacio para que el contenido no choque con el footer fijo */
    body {
        padding-bottom: 50px;
    }

    /* Footer fijo */
    .footer-fixed {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: #212529; /* bg-dark */
        color: #ccc;
        padding: 4px 10px; /* compacto */
        font-size: 11px;   /* texto más pequeño */
        border-top: 1px solid #444;
        z-index: 999;
    }
</style>

<footer class="footer-fixed">
    <div class="container-fluid d-flex justify-content-between">

        <!-- Datos del hotel -->
        <div>
            <strong>Hotel Atankalama</strong> — Calama
            · <a href="https://www.atankalama.com" target="_blank" class="text-warning text-decoration-none">www.atankalama.com</a>
        </div>

        <!-- Datos del programador -->
        <div class="text-end">
            &copy; <?= date('Y') ?> <strong>Rodrigo Jaque Escobar</strong> &mdash; Todos los derechos reservados.
            Se concede uso operacional. El código fuente permanece como propiedad exclusiva del autor.
        </div>

    </div>
</footer>


<script src='https://www.atankalama.com/custodia/includes/bootstrap.bundle.min.js'></script>


</body>
</html>
