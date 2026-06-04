</main><!-- /main-content -->

<!-- ========================================
     MOBILE: Navegación inferior (WhatsApp style)
     ======================================== -->
<?php
$url = $_SERVER['REQUEST_URI'] ?? '';
function mobileNavActive(string $seg): string {
    global $url;
    return strpos($url, '/' . $seg) !== false ? 'active' : '';
}
?>
<nav class="bottom-nav d-flex d-lg-none">
  <a href="<?= BASE_URL ?>/chat" class="bottom-nav-item <?= mobileNavActive('chat') ?>">
    <i class="bi bi-chat-dots-fill"></i>
    <span>Chat</span>
  </a>
  <a href="<?= BASE_URL ?>/tareas" class="bottom-nav-item <?= mobileNavActive('tareas') ?>">
    <i class="bi bi-clipboard-check-fill"></i>
    <span>Tareas</span>
  </a>
  <a href="<?= BASE_URL ?>/mantencion" class="bottom-nav-item <?= mobileNavActive('mantencion') ?>">
    <i class="bi bi-wrench-adjustable-circle-fill"></i>
    <span>Manten.</span>
  </a>
  <a href="<?= BASE_URL ?>/temperaturas" class="bottom-nav-item <?= mobileNavActive('temperaturas') ?>">
    <i class="bi bi-thermometer-half"></i>
    <span>Temp.</span>
  </a>
</nav>

<!-- Toast container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999" id="toast-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>

<footer class="text-center text-muted small py-3">
    &copy; <?= date('Y') ?> Rodrigo Jaque Escobar &mdash; Todos los derechos reservados.<br>
    Se concede uso operacional de esta aplicación. El código fuente y la aplicación
    permanecen como propiedad exclusiva del autor.
</footer>
</body>
</html>
