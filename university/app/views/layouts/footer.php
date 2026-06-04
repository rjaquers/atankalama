</div>

<footer class="text-center text-muted small py-3 border-top mt-4">
    &copy; <?= date('Y') ?> Rodrigo Jaque Escobar &mdash; Todos los derechos reservados.<br>
    <a href="<?= BASE_URL ?>/auth/privacy" class="text-decoration-none text-muted">Política de Privacidad</a> | 
    <a href="<?= BASE_URL ?>/auth/terms" class="text-decoration-none text-muted">Términos y Condiciones</a><br>
    Se concede uso operacional de esta aplicación. El código fuente y la aplicación
    permanecen como propiedad exclusiva del autor.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<script src="<?= BASE_URL ?>/assets/js/offline-sync.js"></script>

<script>
  // Registrar Service Worker (PWA)
  if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register("<?= BASE_URL ?>/service-worker.js").catch(()=>{});
  }
</script>

</body>
</html>
