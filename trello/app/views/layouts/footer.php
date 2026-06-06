<footer class="kanban-footer text-center text-white-50 small py-2">
  &copy; <?= date('Y') ?> Rodrigo Jaque Escobar &mdash; v<?= APP_VERSION ?> &mdash; Todos los derechos reservados.
</footer>

<script src="<?= BASE_URL ?>/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/vendor/flatpickr/flatpickr.min.js"></script>
<script src="<?= BASE_URL ?>/assets/vendor/flatpickr/es.js"></script>
<script src="<?= BASE_URL ?>/assets/vendor/sortablejs/Sortable.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/tarjeta-modal.js?v=<?= APP_VERSION ?>"></script>
<script src="<?= BASE_URL ?>/assets/js/app.js?v=<?= APP_VERSION ?>"></script>

<?php if (isset($js_extra)): ?>
  <?= $js_extra ?>
<?php endif; ?>

</body>
</html>
