</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables.js para tablas interactivas -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<!-- Chart.js para gráficos del dashboard -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<script src="<?= BASE_URL ?>/assets/js/offline-sync.js"></script>

<script>
  // Registrar Service Worker (PWA)
  if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register("<?= BASE_URL ?>/service-worker.js").catch(()=>{});
  }

  // DataTables: configuración global en español
  if (typeof $.fn.dataTable !== 'undefined') {
    $.extend(true, $.fn.dataTable.defaults, {
      language: {
        url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-CL.json'
      },
      pageLength: 25,
      responsive: true
    });
  }
</script>

<footer class="text-center text-muted small py-3 mt-4 border-top">
    &copy; <?= date('Y') ?> Rodrigo Jaque Escobar &mdash; Todos los derechos reservados.<br>
    Se concede uso operacional de esta aplicación. El código fuente y la aplicación
    permanecen como propiedad exclusiva del autor.
</footer>

</body>
</html>
