<?php include 'layout.php'; ?>

<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">
                <i class="bi bi-file-earmark-text"></i> Registro de accesos — todas las apps
            </h4>
            <p class="text-muted small mb-0 mt-1">
                Archivo centralizado · mostrando últimos <?= count($registros) ?> registros
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php?route=acceso/usuarios/log" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-database"></i> Ver log BD
            </a>
            <a href="index.php?route=acceso/usuarios/list" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Usuarios
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <form method="GET" action="index.php" class="row g-2 mb-3">
        <input type="hidden" name="route" value="acceso/log/archivo">

        <div class="col-md-3">
            <select name="app" class="form-select form-select-sm">
                <option value="">— Todas las apps —</option>
                <?php foreach ($apps as $slug => $nombre): ?>
                    <option value="<?= htmlspecialchars($slug) ?>"
                        <?= (($_GET['app'] ?? '') === $slug) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($nombre) ?> (<?= htmlspecialchars($slug) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <input type="text" name="email" class="form-control form-control-sm"
                   placeholder="Filtrar por email"
                   value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
        </div>

        <div class="col-md-2">
            <input type="date" name="fecha" class="form-control form-control-sm"
                   value="<?= htmlspecialchars($_GET['fecha'] ?? '') ?>">
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="bi bi-funnel"></i> Filtrar
            </button>
            <a href="index.php?route=acceso/log/archivo" class="btn btn-sm btn-outline-secondary ms-1">
                <i class="bi bi-x-circle"></i> Limpiar
            </a>
        </div>
    </form>

    <?php if (empty($registros)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Sin registros para los filtros seleccionados. Los ingresos se registrarán a partir del primer login exitoso.
        </div>
    <?php else: ?>

    <table id="tablaLogArchivo" class="table table-sm table-striped table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>Fecha y hora</th>
                <th>App</th>
                <th>Usuario</th>
                <th>IP</th>
                <th>Navegador / SO</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($registros as $r): ?>
            <tr>
                <td class="text-nowrap small">
                    <?= htmlspecialchars(date('d/m/Y H:i:s', strtotime($r['fecha']))) ?>
                </td>
                <td>
                    <span class="badge bg-secondary"><?= htmlspecialchars($r['app_slug']) ?></span>
                    <span class="text-muted small ms-1"><?= htmlspecialchars($r['app_nombre']) ?></span>
                </td>
                <td>
                    <span class="text-muted small"><?= htmlspecialchars($r['email']) ?></span>
                </td>
                <td class="text-nowrap small"><?= htmlspecialchars($r['ip']) ?></td>
                <td>
                    <span class="text-muted small"
                          title="<?= htmlspecialchars($r['user_agent']) ?>">
                        <?= htmlspecialchars(acceso_log_describir_ua($r['user_agent'])) ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php endif; ?>
</div>

<?php
function acceso_log_describir_ua(string $ua): string
{
    if (!$ua || $ua === '-') return '—';

    $nav = 'Desconocido';
    if (str_contains($ua, 'Edg/'))       $nav = 'Edge';
    elseif (str_contains($ua, 'Chrome')) $nav = 'Chrome';
    elseif (str_contains($ua, 'Firefox'))$nav = 'Firefox';
    elseif (str_contains($ua, 'Safari')) $nav = 'Safari';
    elseif (str_contains($ua, 'Opera'))  $nav = 'Opera';

    $os = 'Desconocido';
    if (str_contains($ua, 'Windows'))    $os = 'Windows';
    elseif (str_contains($ua, 'iPhone')) $os = 'iPhone';
    elseif (str_contains($ua, 'iPad'))   $os = 'iPad';
    elseif (str_contains($ua, 'Android'))$os = 'Android';
    elseif (str_contains($ua, 'Mac OS')) $os = 'Mac';
    elseif (str_contains($ua, 'Linux'))  $os = 'Linux';

    return "{$nav} / {$os}";
}
?>

<script>
$(document).ready(function () {
    $('#tablaLogArchivo').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
        order: [[0, 'desc']],
        pageLength: 50,
        columnDefs: [{ orderable: false, targets: 4 }]
    });
});
</script>

<?php include '../helpers/cierre.php'; ?>
