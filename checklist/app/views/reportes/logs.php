<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0 text-gray-800">Logs de Auditoría</h1>
        <div>
            <a href="<?= BASE_URL ?>/reportes/logs" class="btn btn-sm btn-light border me-2">
                <i class="bi bi-arrow-clockwise"></i> Actualizar
            </a>
            <a href="<?= BASE_URL ?>/reportes/logs?export=1" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-download me-1"></i> Exportar CSV
            </a>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/reportes/logs" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-bold">Fecha Inicio</label>
                <input type="date" name="startDate" class="form-control form-control-sm"
                    value="<?= htmlspecialchars($filters['startDate']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Fecha Fin</label>
                <input type="date" name="endDate" class="form-control form-control-sm"
                    value="<?= htmlspecialchars($filters['endDate']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Usuario</label>
                <input type="text" name="user" class="form-control form-control-sm" placeholder="Email..."
                    value="<?= htmlspecialchars($filters['user']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Nivel</label>
                <select name="nivel" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="INFO" <?= $filters['nivel'] == 'INFO' ? 'selected' : '' ?>>INFO</option>
                    <option value="ERROR" <?= $filters['nivel'] == 'ERROR' ? 'selected' : '' ?>>ERROR</option>
                    <option value="SECURITY" <?= $filters['nivel'] == 'SECURITY' ? 'selected' : '' ?>>SECURITY</option>
                    <option value="WARNING" <?= $filters['nivel'] == 'WARNING' ? 'selected' : '' ?>>WARNING</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Módulo</label>
                <select name="modulo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($modules as $mod): ?>
                        <option value="<?= htmlspecialchars($mod) ?>" <?= $filters['modulo'] == $mod ? 'selected' : '' ?>>
                            <?= htmlspecialchars($mod) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 text-end">
                <a href="<?= BASE_URL ?>/reportes/logs" class="btn btn-sm btn-light border">Limpiar</a>
                <button type="submit" class="btn btn-sm btn-primary px-3">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Nivel</th>
                        <th>Módulo</th>
                        <th>Mensaje</th>
                        <th>Usuario</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>
                                <?= $log['created_at'] ?>
                            </td>
                            <td>
                                <span
                                    class="badge bg-<?= $log['nivel'] == 'ERROR' ? 'danger' : ($log['nivel'] == 'SECURITY' ? 'warning text-dark' : 'info') ?>">
                                    <?= $log['nivel'] ?>
                                </span>
                            </td>
                            <td><code class="text-primary"><?= $log['modulo'] ?></code></td>
                            <td>
                                <?= $log['mensaje'] ?>
                            </td>
                            <td class="text-muted small">
                                <?= $log['user_email'] ?>
                            </td>
                            <td class="text-muted small">
                                <?= $log['ip_address'] ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>