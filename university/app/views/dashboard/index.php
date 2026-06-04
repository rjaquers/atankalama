<?php require VIEW_PATH . "/layouts/header.php"; ?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h3 class="m-0"><i class="fa-solid fa-chart-line"></i> Dashboard</h3>
  <div class="text-muted">
    <i class="fa-regular fa-user"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-md-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted">Usuarios activos</div>
            <div class="display-6"><?= (int)$totalUsers ?></div>
          </div>
          <i class="fa-solid fa-users fa-2x"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-8">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="fw-bold"><i class="fa-solid fa-bell"></i> Notificaciones recientes</div>
          <button class="btn btn-sm btn-outline-secondary" id="btnRefreshNoti">
            <i class="fa-solid fa-rotate"></i> actualizar
          </button>
        </div>
        <?php if (empty($notifications)): ?>
          <div class="text-muted">Sin notificaciones.</div>
        <?php else: ?>
          <ul class="list-group" id="notiList">
            <?php foreach($notifications as $n): ?>
              <li class="list-group-item">
                <div class="fw-bold"><?= htmlspecialchars($n['title']) ?></div>
                <div class="text-muted small"><?= htmlspecialchars($n['message']) ?></div>
                <div class="small"><?= htmlspecialchars($n['created_at']) ?> · <?= htmlspecialchars($n['channel']) ?></div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="fw-bold mb-2"><i class="fa-solid fa-qrcode"></i> Demo QR Scanner</div>
    <p class="text-muted mb-2">Para probar, abre desde celular y presiona iniciar. Requiere cámara y HTTPS en producción.</p>

    <div class="row g-2">
      <div class="col-12 col-md-6">
        <input id="qrValue" class="form-control" placeholder="Resultado QR aquí" readonly>
        <button class="btn btn-dark mt-2" id="btnQrStart"><i class="fa-solid fa-camera"></i> Iniciar scanner</button>
        <button class="btn btn-outline-dark mt-2" id="btnQrStop"><i class="fa-solid fa-stop"></i> Detener</button>
      </div>
      <div class="col-12 col-md-6">
        <div id="qr-reader" class="border rounded p-2" style="min-height: 280px;"></div>
      </div>
    </div>

  </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/minified/html5-qrcode.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/qr-scanner.js"></script>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
