<?php
/**
 * Vista: Lista de conversaciones
 * Estilo WhatsApp — Bootstrap 5 + Bootstrap Icons
 */
require VIEW_PATH . '/layouts/header.php';

// Paleta de colores para avatares sin foto
$avatarColors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16'];

/**
 * Formatea una fecha/hora de mensaje para mostrar en la lista.
 */
function chatFormatTime(string $datetime): string {
    if (!$datetime) return '';
    $ts   = strtotime($datetime);
    $hoy  = strtotime('today');
    $ayer = strtotime('yesterday');
    if ($ts >= $hoy) {
        return date('H:i', $ts);
    } elseif ($ts >= $ayer) {
        return 'Ayer';
    } else {
        return date('d/m/Y', $ts);
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="fw-bold mb-0">
    <i class="bi bi-chat-dots-fill text-primary me-2"></i>
    <?= htmlspecialchars($title ?? 'Chat') ?>
  </h5>
  <a href="<?= BASE_URL ?>/chat/nueva"
     class="btn btn-success btn-sm d-flex align-items-center gap-1">
    <i class="bi bi-pencil-square"></i>
    <span class="d-none d-sm-inline">Nueva conversación</span>
  </a>
</div>

<?php if (empty($conversaciones)): ?>
  <div class="text-center py-5 text-muted">
    <i class="bi bi-chat-square-dots fs-1 d-block mb-3 opacity-25"></i>
    <p class="mb-2">No tienes conversaciones aún.</p>
    <a href="<?= BASE_URL ?>/chat/nueva" class="btn btn-primary btn-sm">
      Iniciar una conversación
    </a>
  </div>
<?php else: ?>
  <div class="card shadow-sm border-0">
    <div class="list-group list-group-flush" id="chat-list">
      <?php foreach ($conversaciones as $conv):
        $convId       = (int)$conv['id'];
        $nombre       = htmlspecialchars($conv['display_nombre'] ?? 'Conversación');
        $noLeidos     = (int)($conv['no_leidos'] ?? 0);
        $ultimoTipo   = $conv['ultimo_msg_tipo'] ?? 'texto';
        $ultimoTexto  = $conv['ultimo_mensaje_contenido'] ?? '';
        $ultimoAt     = $conv['ultimo_mensaje_at'] ?? '';
        $foto         = $conv['otro_foto'] ?? $conv['foto_grupo'] ?? '';
        $colorIdx     = $convId % count($avatarColors);
        $bgColor      = $avatarColors[$colorIdx];
        $inicial      = strtoupper(mb_substr($conv['display_nombre'] ?? 'C', 0, 1, 'UTF-8'));

        // Preview del último mensaje
        if ($ultimoTipo === 'imagen') {
            $preview = '<i class="bi bi-camera-fill me-1"></i>Foto';
        } elseif ($ultimoTipo === 'archivo') {
            $preview = '<i class="bi bi-paperclip me-1"></i>Archivo';
        } else {
            $preview = htmlspecialchars(mb_substr($ultimoTexto, 0, 60, 'UTF-8'));
            if (mb_strlen($ultimoTexto, 'UTF-8') > 60) $preview .= '…';
        }
      ?>
      <a href="<?= BASE_URL ?>/chat/conversacion/<?= $convId ?>"
         class="list-group-item list-group-item-action border-0 py-3 px-3 chat-list-item <?= $noLeidos > 0 ? 'fw-semibold' : '' ?>">
        <div class="d-flex align-items-center gap-3">

          <!-- Avatar -->
          <div class="chat-avatar flex-shrink-0"
               style="width:48px;height:48px;border-radius:<?= $conv['tipo'] === 'individual' ? '50%' : '14px' ?>;background:<?= $bgColor ?>;
                      display:flex;align-items:center;justify-content:center;
                      color:#fff;font-weight:700;font-size:18px;overflow:hidden;">
            <?php if ($foto): ?>
              <img src="<?= BASE_URL ?>/<?= htmlspecialchars($foto) ?>"
                   alt="<?= $nombre ?>"
                   style="width:100%;height:100%;object-fit:cover;">
            <?php elseif ($conv['tipo'] === 'sistema'): ?>
              <i class="bi bi-megaphone-fill" style="font-size:20px;font-weight:normal;"></i>
            <?php elseif ($conv['tipo'] === 'area'): ?>
              <i class="bi bi-people-fill" style="font-size:20px;font-weight:normal;"></i>
            <?php elseif ($conv['tipo'] === 'grupo'): ?>
              <i class="bi bi-chat-square-dots-fill" style="font-size:20px;font-weight:normal;"></i>
            <?php else: ?>
              <?= $inicial ?>
            <?php endif; ?>
          </div>

          <!-- Contenido -->
          <div class="flex-grow-1 overflow-hidden">
            <div class="d-flex justify-content-between align-items-center">
              <span class="chat-name text-truncate me-2">
                <?= $nombre ?>
                <?php if ($conv['tipo'] === 'sistema'): ?>
                  <span class="badge ms-1" style="font-size:9px;background:#fef3c7;color:#92400e;font-weight:600;vertical-align:middle;">GENERAL</span>
                <?php elseif ($conv['tipo'] === 'area'): ?>
                  <span class="badge ms-1" style="font-size:9px;background:#e0f2fe;color:#0369a1;font-weight:600;vertical-align:middle;">ÁREA</span>
                <?php elseif ($conv['tipo'] === 'grupo'): ?>
                  <span class="badge ms-1" style="font-size:9px;background:#f0fdf4;color:#166534;font-weight:600;vertical-align:middle;">GRUPO</span>
                <?php endif; ?>
              </span>
              <small class="chat-time text-muted text-nowrap" style="font-size:11px;">
                <?= $ultimoAt ? chatFormatTime($ultimoAt) : '' ?>
              </small>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-1">
              <small class="chat-preview text-muted text-truncate" style="font-size:12px;max-width:240px;">
                <?= $preview ?>
              </small>
              <?php if ($noLeidos > 0): ?>
                <span class="chat-badge badge rounded-pill ms-2"
                      style="background:#25d366;color:#fff;min-width:20px;font-size:11px;">
                  <?= $noLeidos > 99 ? '99+' : $noLeidos ?>
                </span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<!-- Enlace a archivadas -->
<div class="mt-3 text-center">
  <a href="<?= BASE_URL ?>/chat/archivadas"
     class="text-muted text-decoration-none d-inline-flex align-items-center gap-1"
     style="font-size:13px;">
    <i class="bi bi-archive"></i>
    <?php if (($title ?? '') === 'Archivadas'): ?>
      <a href="<?= BASE_URL ?>/chat" class="text-muted text-decoration-none ms-2" style="font-size:13px;">
        <i class="bi bi-chat-left-dots"></i> Ver conversaciones activas
      </a>
    <?php else: ?>
      Ver conversaciones archivadas
    <?php endif; ?>
  </a>
</div>

<style>
.chat-list-item {
  transition: background .12s;
  border-bottom: 1px solid #f1f5f9 !important;
}
.chat-list-item:hover {
  background: #f8fafc;
}
.chat-list-item:last-child {
  border-bottom: none !important;
}
</style>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
