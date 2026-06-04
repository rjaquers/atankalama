<?php
// views/empresas/listar.php
declare(strict_types=1);
if (!function_exists('h')) {
    function h(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

include __DIR__ . '/../../includes/header.php';

$BASE_URL = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '/custodia';
$listUrl  = function_exists('url') ? url('/empresas/listar') : ($BASE_URL . '/empresas/listar');
$guardarUrl = function_exists('url') ? url('/empresas/guardar') : ($BASE_URL . '/empresas/guardar');

$q      = isset($_GET['q'])      ? trim((string)$_GET['q'])      : '';
$activo = isset($_GET['activo']) ? (string)$_GET['activo']        : '';
$ok     = isset($_GET['ok'])     ? (int)$_GET['ok']               : 0;

$sort  = $_GET['sort'] ?? 'nombre';
$order = $_GET['order'] ?? 'ASC';

function sortUrl(string $field, string $currentSort, string $currentOrder, string $listUrl): string {
    $newOrder = ($field === $currentSort && strtoupper($currentOrder) === 'ASC') ? 'DESC' : 'ASC';
    $params = $_GET;
    $params['sort'] = $field;
    $params['order'] = $newOrder;
    return h($listUrl . '?' . http_build_query($params));
}

function sortIcon(string $field, string $currentSort, string $currentOrder): string {
    if ($field !== $currentSort) return '<i class="fa fa-sort text-muted opacity-50 small"></i>';
    return strtoupper($currentOrder) === 'ASC' 
        ? '<i class="fa fa-sort-up text-primary small"></i>' 
        : '<i class="fa fa-sort-down text-primary small"></i>';
}
?>

<style>
    .table-custom { border-radius: 8px; overflow: hidden; box-shadow: 0 0 15px rgba(0,0,0,0.05); }
    .table-custom thead { background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; }
    .table-custom th { font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.025em; color: #4a5568; }
    .table-custom th a { text-decoration: none; color: inherit; display: flex; align-items: center; justify-content: space-between; gap: 5px; }
    .table-custom th a:hover { color: #2153A7; }
    .table-custom td { vertical-align: middle; padding: 12px 8px; font-size: 0.875rem; }
    .table-custom tbody tr:hover { background-color: #f1f5f9; transition: background-color 0.2s ease; }
    .badge-soft-success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .badge-soft-secondary { background-color: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
</style>

<div class="container-fluid mt-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold"><i class="fa fa-building me-2 text-primary"></i>Gestión de Empresas</h5>
        <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaEmpresa">
            <i class="fa fa-plus me-1"></i> Nueva empresa
        </button>
    </div>

    <?php if ($ok): ?>
        <div class="alert alert-success alert-dismissible fade show py-2 border-0 shadow-sm" role="alert">
            <i class="fa fa-check-circle me-2"></i>Empresa creada correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($flash['msg'])): ?>
        <div class="alert alert-<?= h($flash['type'] ?? 'info') ?> alert-dismissible fade show py-2 border-0 shadow-sm" role="alert">
            <i class="fa fa-info-circle me-2"></i><?= h($flash['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form class="row g-2 mb-4 p-3 bg-light rounded-3 shadow-sm mx-0" method="get" action="<?= h($listUrl) ?>">
        <div class="col-auto">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0"><i class="fa fa-search text-muted"></i></span>
                <input type="text" class="form-control form-control-sm border-start-0" name="q"
                       placeholder="Buscar empresa..." value="<?= h($q) ?>">
            </div>
        </div>
        <div class="col-auto">
            <select class="form-select form-select-sm" name="activo">
                <option value="">Todos los estados</option>
                <option value="1" <?= $activo === '1' ? 'selected' : '' ?>>Activas</option>
                <option value="0" <?= $activo === '0' ? 'selected' : '' ?>>Inactivas</option>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary btn-sm px-3">Filtrar</button>
            <a href="<?= h($listUrl) ?>" class="btn btn-outline-secondary btn-sm">Limpiar</a>
        </div>
    </form>

    <?php if (!isset($rows, $total, $pages, $page)): ?>
        <p class="text-muted">Cargando datos…</p>
    <?php elseif (!$rows): ?>
        <div class="text-center py-5 bg-light rounded-3">
            <i class="fa fa-folder-open fa-3x text-muted opacity-25 mb-3"></i>
            <p class="text-muted">No se encontraron empresas con los criterios seleccionados.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive table-custom">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th><a href="<?= sortUrl('id', $sort, $order, $listUrl) ?>">ID <?= sortIcon('id', $sort, $order) ?></a></th>
                        <th><a href="<?= sortUrl('nombre', $sort, $order, $listUrl) ?>">Razón Social <?= sortIcon('nombre', $sort, $order) ?></a></th>
                        <th><a href="<?= sortUrl('rut', $sort, $order, $listUrl) ?>">RUT <?= sortIcon('rut', $sort, $order) ?></a></th>
                        <th><a href="<?= sortUrl('fantasia', $sort, $order, $listUrl) ?>">Nombre Fantasía <?= sortIcon('fantasia', $sort, $order) ?></a></th>
                        <th><a href="<?= sortUrl('contacto', $sort, $order, $listUrl) ?>">Contacto <?= sortIcon('contacto', $sort, $order) ?></a></th>
                        <th><a href="<?= sortUrl('email', $sort, $order, $listUrl) ?>">Email <?= sortIcon('email', $sort, $order) ?></a></th>
                        <th class="text-center"><a href="<?= sortUrl('activo', $sort, $order, $listUrl) ?>" class="justify-content-center">Estado <?= sortIcon('activo', $sort, $order) ?></a></th>
                        <th class="text-center" style="width: 80px;">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td class="text-muted small">#<?= (int)$r['id'] ?></td>
                            <td class="fw-bold text-dark"><?= h($r['nombre']) ?></td>
                            <td class="text-nowrap"><?= h($r['rut']) ?></td>
                            <td><?= h($r['fantasia']) ?></td>
                            <td><i class="fa fa-user-circle me-1 text-muted small"></i><?= h($r['contacto']) ?></td>
                            <td><a href="mailto:<?= h($r['email']) ?>" class="text-decoration-none text-muted small"><i class="fa fa-envelope me-1 opacity-50"></i><?= h($r['email']) ?></a></td>
                            <td class="text-center">
                                <?php if ((int)$r['activo']): ?>
                                    <span class="badge badge-soft-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-soft-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= function_exists('url') ? url('/empresas/editar/' . $r['id']) : ($BASE_URL . '/empresas/editar/' . $r['id']) ?>"
                                   class="btn btn-light btn-sm border shadow-sm" title="Editar">
                                    <i class="fa fa-edit text-primary"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pages > 1): ?>
            <nav>
                <ul class="pagination pagination-sm">
                    <?php
                    $baseParams = $_GET;
                    for ($i = 1; $i <= $pages; $i++):
                        $baseParams['page'] = $i;
                        $url = $listUrl . '?' . http_build_query($baseParams);
                    ?>
                        <li class="page-item <?= $i === (int)$page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= h($url) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Modal: Nueva empresa -->
<div class="modal fade" id="modalNuevaEmpresa" tabindex="-1" aria-labelledby="modalNuevaEmpresaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= h($guardarUrl) ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevaEmpresaLabel">Nueva empresa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modalBusinessName" class="form-label">Razón social <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modalBusinessName" name="business_name"
                               maxlength="200" required placeholder="Ej: Constructora Besalco S.A." autofocus>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">RUT</label>
                            <input type="text" class="form-control" name="rut" maxlength="20" placeholder="76.123.456-7">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Nombre de fantasía</label>
                            <input type="text" class="form-control" name="trade_name" maxlength="200" placeholder="Ej: Besalco">
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="activoEmpresa" name="active" value="1" checked>
                        <label class="form-check-label" for="activoEmpresa">Empresa activa</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
