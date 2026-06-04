<?php require VIEW_PATH . '/layouts/header.php'; ?>

<style>
  .temp-iframe-wrapper {
    position: fixed;
    top: var(--mobile-top-h);
    left: 0;
    right: 0;
    bottom: var(--bottom-nav-h);
    z-index: 1;
  }
  @media (min-width: 992px) {
    .temp-iframe-wrapper {
      top: 0;
      left: var(--sidebar-w);
      bottom: 0;
    }
  }
  .temp-iframe-wrapper iframe {
    width: 100%;
    height: 100%;
    border: none;
  }
</style>

<div class="temp-iframe-wrapper">
  <iframe src="https://www.atankalama.com/temp/"
          title="Temperaturas"
          loading="lazy"
          allow="fullscreen">
  </iframe>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
