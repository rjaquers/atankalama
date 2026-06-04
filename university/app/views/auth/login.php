<?php require VIEW_PATH . "/layouts/header.php"; ?>

<div class="row justify-content-center">
  <div class="col-12 col-md-5 col-lg-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4 class="mb-3"><i class="fa-solid fa-lock"></i> Login</h4>

        <?php if(!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>/authenticate" autocomplete="off">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

          <label class="form-label">Email</label>
          <input class="form-control mb-2" name="email" type="email" required>

          <label class="form-label">Password</label>
          <input class="form-control mb-3" name="password" type="password" required>

          <button class="btn btn-primary w-100">
            <i class="fa-solid fa-right-to-bracket"></i> Ingresar
          </button>
        </form>

        <hr>
        <small class="text-muted">
          Usuario demo: <b>admin@rkm.local</b> / <b>admin123</b>
        </small>
      </div>
    </div>
  </div>
</div>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
