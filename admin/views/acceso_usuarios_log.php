<?php include 'layout.php'; ?>

<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">
                <i class="bi bi-journal-text"></i> Log de ingresos
                <?php if ($filtroEmail): ?>
                    <span class="fs-6 text-muted fw-normal ms-2">
                        &mdash; <?= htmlspecialchars($filtroEmail) ?>
                    </span>
                <?php endif; ?>
            </h4>
        </div>
        <div class="d-flex gap-2">
            <?php if ($filtroEmail): ?>
                <a href="index.php?route=acceso/usuarios/log" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Ver todos
                </a>
            <?php endif; ?>
            <a href="index.php?route=acceso/log/archivo" class="btn btn-sm btn-outline-success">
                <i class="bi bi-file-earmark-text"></i> Log todas las apps
            </a>
            <a href="index.php?route=acceso/usuarios/list" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Volver a usuarios
            </a>
        </div>
    </div>

    <?php if (empty($registros)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <?= $filtroEmail
                ? 'Sin registros de ingreso para ' . htmlspecialchars($filtroEmail) . '.'
                : 'Aún no hay registros de ingreso. Los ingresos se registrarán a partir de ahora.' ?>
        </div>
    <?php else: ?>
    <table id="tablaLog" class="table table-sm table-striped table-hover align-middle">
        <thead>
            <tr>
                <th>Fecha y hora</th>
                <th>Usuario</th>
                <th>App</th>
                <th>IP</th>
                <th>Navegador</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($registros as $r): ?>
            <tr>
                <td class="text-nowrap">
                    <?= htmlspecialchars(date('d/m/Y H:i:s', strtotime($r['created_at']))) ?>
                </td>
                <td>
                    <?php
                        $nombre = trim(($r['nombre'] ?? '') . ' ' . ($r['apellido'] ?? ''));
                        echo $nombre
                            ? htmlspecialchars($nombre) . '<br><small class="text-muted">' . htmlspecialchars($r['email']) . '</small>'
                            : '<small class="text-muted">' . htmlspecialchars($r['email']) . '</small>';
                    ?>
                </td>
                <td>
                    <span class="badge bg-secondary"><?= htmlspecialchars($r['app_slug']) ?></span>
                </td>
                <td class="text-nowrap">
                    <?= htmlspecialchars($r['ip_address'] ?? '—') ?>
                </td>
                <td>
                    <span class="text-muted small" style="max-width:320px;display:inline-block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                          title="<?= htmlspecialchars($r['user_agent'] ?? '') ?>">
                        <?= htmlspecialchars(self_describir_ua($r['user_agent'] ?? '')) ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php
function self_describir_ua(string $ua): string
{
    if (!$ua) return '—';
    // Extraer navegador y SO de forma simple
    $navegador = 'Desconocido';
    if (str_contains($ua, 'Edg/'))          $navegador = 'Edge';
    elseif (str_contains($ua, 'Chrome'))    $navegador = 'Chrome';
    elseif (str_contains($ua, 'Firefox'))   $navegador = 'Firefox';
    elseif (str_contains($ua, 'Safari'))    $navegador = 'Safari';
    elseif (str_contains($ua, 'Opera'))     $navegador = 'Opera';

    $os = 'Desconocido';
    if (str_contains($ua, 'Windows'))       $os = 'Windows';
    elseif (str_contains($ua, 'Mac OS'))    $os = 'Mac';
    elseif (str_contains($ua, 'iPhone'))    $os = 'iPhone';
    elseif (str_contains($ua, 'Android'))   $os = 'Android';
    elseif (str_contains($ua, 'Linux'))     $os = 'Linux';

    return "{$navegador} / {$os}";
}
?>

<script>
$(document).ready(function () {
    $('#tablaLog').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
        order: [[0, 'desc']],
        pageLength: 50,
        columnDefs: [{ orderable: false, targets: 4 }]
    });
});
</script>

<?php include '../helpers/cierre.php'; ?>
