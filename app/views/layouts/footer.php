<footer class="kanban-footer text-center text-white-50 small py-2">
  &copy; <?= date('Y') ?> Rodrigo Jaque Escobar &mdash; Todos los derechos reservados.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>

<?php if (isset($js_extra)): ?>
  <?= $js_extra ?>
<?php endif; ?>

</body>
</html>
