<?php
// views/tickets/listar.php
declare(strict_types=1);

// 1) Conexión (MySQLi)
require_once __DIR__ . '/../../connections/conec6.php';


// 2) Controller + Model
require_once __DIR__ . '/../../controllers/TicketController.php';

// 3) Ejecutar controlador
$controller = new TicketController();
$data = $controller->index();

$rows   = $data['rows'];
$total  = $data['total'];
$page   = $data['page'];
$pages  = $data['pages'];
$params = $data['params'];



// Si NO viene por el ruteador (no existen variables), cargamos legacy:
if (!isset($rows, $total, $page, $pages, $params)) {
    // 1) Conexión (MySQLi)
    require_once __DIR__ . '/../../connections/conec6.php';
    // 2) Controller + Model
    require_once __DIR__ . '/../../controllers/TicketController.php';
    // 3) Ejecutar controlador
    $controller = new TicketController();
    $data = $controller->index();
    $rows   = $data['rows'];
    $total  = $data['total'];
    $page   = $data['page'];
    $pages  = $data['pages'];
    $params = $data['params'];
}

function h(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }


include __DIR__ . '/../../includes/header.php';
?>

<style>
    /* ── Unificación de Tokens (Emil Principles) ── */
    :root {
        --color-primary:      #0D9488;
        --color-primary-dark: #0F766E;
        --color-bg-subtle:    #F8FAFC;
        --color-text-main:    #1E293B;
        --color-text-muted:   #64748B;
        --color-border:       #E2E8F0;
        --radius-md:          10px;
        --radius-sm:          6px;
        --shadow-sm:          0 1px 3px rgba(0,0,0,0.1);
        --transition-fast:    100ms ease;
    }

    body {
        background-color: var(--color-bg-subtle);
        color: var(--color-text-main);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    h1 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        letter-spacing: -0.02em;
    }

    /* ── Barra de Filtros Moderna ── */
    .filters-container {
        background: #fff;
        padding: 1.25rem;
        border-radius: var(--radius-md);
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
    }

    .filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: flex-end;
    }

    .filters .field-group {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .filters label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--color-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .filters input, .filters select {
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--color-border);
        border-radius: var(--radius-sm);
        font-size: 0.875rem;
        transition: border-color var(--transition-fast);
        outline: none;
        min-width: 160px;
    }

    .filters input:focus, .filters select:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
    }

    .filters button[type="submit"] {
        background: var(--color-primary);
        color: #fff;
        border: none;
        padding: 0.5rem 1.25rem;
        border-radius: var(--radius-sm);
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: background var(--transition-fast);
        height: 38px;
    }

    .filters button[type="submit"]:hover {
        background: var(--color-primary-dark);
    }

    .filters .btn-clear {
        color: var(--color-text-muted);
        text-decoration: none;
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-sm);
        transition: background var(--transition-fast);
    }

    .filters .btn-clear:hover {
        background: #f1f5f9;
        color: var(--color-text-main);
    }

    /* ── Tabla Refinada ── */
    .table-card {
        background: #fff;
        border-radius: var(--radius-md);
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    #tableTickets {
        margin-top: 0 !important;
        border-bottom: none !important;
    }

    #tableTickets thead th {
        background: #f8fafc;
        border-bottom: 1px solid var(--color-border);
        color: var(--color-text-muted);
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1rem 1.25rem;
    }

    #tableTickets tbody td {
        padding: 1rem 1.25rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.875rem;
    }

    #tableTickets tbody tr:last-child td {
        border-bottom: none;
    }

    #tableTickets tbody tr:hover {
        background-color: #f8fafc !important;
    }

    /* Tipografía Tabular para números */
    .tabular {
        font-variant-numeric: tabular-nums;
        font-family: 'JetBrains Mono', 'Menlo', monospace;
        font-size: 0.8rem;
    }

    /* Código estilizado */
    .ticket-code {
        background: #f1f5f9;
        color: var(--color-text-main);
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.8rem;
    }

    /* ── Badges de Estado ── */
    .badge-status {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: capitalize;
    }

    .status-en_custodia { background: #dcfce7; color: #166534; }
    .status-retirado    { background: #f1f5f9; color: #475569; }
    .status-extraviado  { background: #fee2e2; color: #991b1b; }
    .status-cancelado   { background: #fef3c7; color: #92400e; }

    /* Botón Imprimir */
    .btn-print-action {
        background: #fff;
        border: 1px solid var(--color-border);
        color: var(--color-text-main);
        padding: 0.4rem 0.8rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        transition: all var(--transition-fast);
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .btn-print-action:hover {
        border-color: var(--color-primary);
        color: var(--color-primary);
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .empty {
        background: #fff;
        border: 2px dashed var(--color-border);
        border-radius: var(--radius-md);
        padding: 3rem;
        text-align: center;
        color: var(--color-text-muted);
    }
</style>

<h1>Listado de Tickets</h1>

<div class="filters-container">
    <form class="filters" method="get" action="">
        <div class="field-group">
            <label>Buscar</label>
            <input type="text" name="q" placeholder="Código o huésped" value="<?= h($params['q'] ?? '') ?>">
        </div>
        <div class="field-group">
            <label>Estado</label>
            <select name="status">
                <option value="">Todos</option>
                <?php foreach (['en_custodia','retirado','extraviado','cancelado'] as $opt): ?>
                    <option value="<?= h($opt) ?>" <?= ($params['status'] ?? '') === $opt ? 'selected' : '' ?>><?= str_replace('_', ' ', h($opt)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field-group">
            <label>Modo</label>
            <select name="mode">
                <option value="">Todos</option>
                <?php foreach (['custodia','perdido'] as $opt): ?>
                    <option value="<?= h($opt) ?>" <?= ($params['mode'] ?? '') === $opt ? 'selected' : '' ?>><?= h($opt) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field-group">
            <label>Desde</label>
            <input type="date" name="from" value="<?= h($params['from'] ?? '') ?>">
        </div>
        <div class="field-group">
            <label>Hasta</label>
            <input type="date" name="to"   value="<?= h($params['to']   ?? '') ?>">
        </div>
        <button type="submit">Filtrar Resultados</button>
        <a href="?" class="btn-clear">Limpiar</a>
    </form>
</div>

<?php if (empty($rows)): ?>
    <div class="empty">
        <p>No se encontraron tickets con los filtros actuales.</p>
    </div>
<?php else: ?>
    <div class="muted" style="margin-bottom:12px; font-size: 0.8rem; color: var(--color-text-muted);">
        Mostrando <strong><?= (int)$total ?></strong> tickets — Página <?= (int)$page ?> de <?= (int)$pages ?>
    </div>
    <div class="table-card">
        <table id="tableTickets" class="table table-hover">
            <thead>
            <tr>
                <th>Acciones</th>
                <th>Código</th>
                <th>Modo</th>
                <th>Huésped</th>
                <th>Artículo</th>
                <th>Ubicación</th>
                <th>Estado</th>
                <th>Creado</th>
                <th class="text-end">Impresiones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td>
                        <a class='btn-print-action' href="/custodia/tickets/custodia/imprimir/<?= (int)$r['id'] ?>">
                            <i class="fa-solid fa-print"></i> Imprimir
                        </a>
                    </td>
                    <td><span class="ticket-code"><?= h($r['public_code']) ?></span></td>
                    <td><span style="font-size: 0.75rem; font-weight: 500;"><?= h($r['mode']) ?></span></td>
                    <td style="font-weight: 600; color: var(--color-text-main);"><?= h($r['guest_name'] ?? '—') ?></td>
                    <td><?= h($r['item_type'] ?? '—') ?></td>
                    <td><span class="muted" style="font-size: 0.8rem;"><?= h($r['location_label'] ?? '—') ?></span></td>
                    <td>
                        <span class="badge-status status-<?= h($r['status']) ?>">
                            <?= str_replace('_', ' ', h($r['status'])) ?>
                        </span>
                    </td>
                    <td class="tabular"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
                    <td class="text-end tabular"><?= (int)$r['print_count'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
        <div class="pagination">
            <?php
            // construir enlaces manteniendo filtros
            $baseParams = $_GET;
            for ($i = 1; $i <= $pages; $i++):
                $baseParams['page'] = $i;
                $url = '?' . http_build_query($baseParams);
                ?>
                <?php if ($i === (int)$page): ?>
                <span class="active"><?= $i ?></span>
            <?php else: ?>
                <a href="<?= h($url) ?>"><?= $i ?></a>
            <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>


<!-- LIBRERÍAS DATATABLES -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        $('#tableTickets').DataTable({
            "paging": false, // Mantenemos la paginación de PHP por ahora
            "info": false,   // Ocultamos info para no duplicar con el total de PHP
            "searching": true, // Permitimos búsqueda rápida en la página actual
            "order": [[1, 'desc']], // Ordenar por ID descendente por defecto
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
            },
            "columnDefs": [
                { "orderable": false, "targets": 0 } // Deshabilitar orden en columna de Acciones
            ]
        });
    });
</script>

</body>
</html>
