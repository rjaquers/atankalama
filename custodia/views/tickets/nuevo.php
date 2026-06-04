<?php
// views/tickets/nuevo.php
declare(strict_types=1);

// Si vienes sin router, carga conexión y header igual que listar.php
if (!function_exists('h')) {
    require_once __DIR__ . '/../../connections/conec6.php';
    require_once __DIR__ . '/../../controllers/TicketController.php';
    function h(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
include __DIR__ . '/../../includes/header.php';

// Helpers de URL si existen; si no, fija BASE_URL manual
$BASE_URL   = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '/custodia';
$guardarUrl = function_exists('url') ? url('/tickets/custodia/guardar') : ($BASE_URL . '/tickets/custodia/guardar');
$listarUrl  = function_exists('url') ? url('/tickets/custodia/listar')  : ($BASE_URL . '/tickets/custodia/listar');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nuevo Ticket</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:0; padding:20px; background:#f7f7f7; }
        .page { background:#fff; max-width:780px; margin:0 auto; border:1px solid #ddd; border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,.08); overflow:hidden; }
        header { display:flex; justify-content:space-between; align-items:center; padding:12px 16px; border-bottom:1px solid #eee; background:#fafafa; }
        .wrapper { padding:16px; }
        label { display:block; font-size:12px; color:#555; margin-top:10px; margin-bottom:4px; }
        input[type="text"], select, textarea { width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:14px; }
        textarea { resize:vertical; min-height:80px; }
        .row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .actions { margin-top:16px; display:flex; gap:10px; }
        .btn { padding:10px 14px; border-radius:8px; border:1px solid #ccc; background:#fff; cursor:pointer; text-decoration:none; display:inline-block; }
        .btn.primary { background:#0a66c2; color:#fff; border-color:#0a66c2; }
    </style>
</head>
<body>
<div class="page">
    <header>
        <strong>Nuevo Ticket de Custodia</strong>
        <div>
            <a class="btn" href="<?= h($listarUrl) ?>">Volver al listado</a>
        </div>
    </header>
    <div class="wrapper">
        <form method="post" action="<?= h($guardarUrl) ?>">
            <div class="row">
                <div>
                    <label for="mode">Modo</label>
                    <select id="mode" name="mode" required>
                        <option value="custodia" selected>Custodia temporal</option>
                        <option value="perdido">Objeto perdido</option>
                    </select>
                </div>
                <div>
                    <label for="guest_name">Nombre del huésped (opcional)</label>
                    <input type="text" id="guest_name" name="guest_name" maxlength="120" placeholder="Ej: Juan Pérez">
                </div>
            </div>

            <div class="row">
                <div>
                    <label for="item_type">Tipo de objeto (opcional)</label>
                    <input type="text" id="item_type" name="item_type" maxlength="120" placeholder="Ej: Maleta, Mochila …">
                </div>
                <div>
                    <label for="location_label">Posición / Ubicación (opcional)</label>
                    <input type="text" id="location_label" name="location_label" maxlength="120" placeholder="Ej: Estante B-03">
                </div>
            </div>

            <label for="notes">Observaciones (opcional)</label>
            <textarea id="notes" name="notes" placeholder="Daños visibles, sellos, color, marca, cantidad de bultos, etc."></textarea>

            <div class="actions">
                <button type="submit" class="btn primary">Guardar y imprimir</button>
                <a class="btn" href="<?= h($listarUrl) ?>">Cancelar</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
