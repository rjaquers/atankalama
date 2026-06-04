<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher — Hotel Atankalama</title>
    <link href="<?= BASE_URL ?>public/static/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <?php
    $dias  = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    $meses = ['enero','febrero','marzo','abril','mayo','junio','julio',
              'agosto','septiembre','octubre','noviembre','diciembre'];
    $ts          = strtotime($voucher['fecha']);
    $fechaTexto  = $dias[date('w',$ts)] . ' ' . date('j',$ts) . ' de '
                 . $meses[(int)date('n',$ts)-1] . ' de ' . date('Y',$ts);
    $etiqueta    = VoucherModel::etiquetaServicio($voucher['tipo_servicio']);
    $colorBadge  = VoucherModel::colorServicio($voucher['tipo_servicio']);
    $colorMap    = ['primary'=>'#0d6efd','success'=>'#198754','warning'=>'#ffc107','info'=>'#0dcaf0','secondary'=>'#6c757d'];
    $colorHex    = $colorMap[$colorBadge] ?? '#0d6efd';
    $hora        = $voucher['hora_servicio'] ? substr($voucher['hora_servicio'],0,5).' hrs' : '—';
    $esNominal   = ($tipo === 'nominal');
    $nombre      = $esNominal ? ($voucher['nombre'] ?? 'Invitado') : 'Sin nombre asignado';
    $empresa     = $esNominal ? ($voucher['empresa'] ?? '') : ($voucher['nombre_empresa'] ?? '');
    $escaneFecha = $esNominal ? ($voucher['canjeado_en'] ?? null) : ($voucher['canjeado_en'] ?? null);
    
    // Datos de impresión
    $impreso     = (bool)($voucher['impreso'] ?? false);
    $impresoEn   = $voucher['impreso_en'] ?? null;
    ?>

    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Arial, sans-serif; }

        .voucher-container {
            max-width: 480px;
            margin: 40px auto;
            padding: 0 16px;
        }

        .voucher-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,.1);
        }

        .voucher-header {
            background: <?= $colorHex ?>;
            color: <?= $colorBadge === 'warning' ? '#000' : '#fff' ?>;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .voucher-header img { height: 28px; filter: brightness(0)<?= $colorBadge !== 'warning' ? 'invert(1)' : '' ?>; opacity:.9; }
        .voucher-header .tipo { font-size: 1rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; }

        .voucher-body {
            display: flex;
            gap: 16px;
            padding: 20px;
            align-items: flex-start;
        }

        .voucher-info { flex: 1; }

        .v-nombre {
            font-size: 1.25rem;
            font-weight: 700;
            color: #212529;
            line-height: 1.3;
            margin-bottom: 4px;
        }

        .v-empresa { font-size: .85rem; color: #6c757d; margin-bottom: 10px; }

        .v-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .9rem;
            color: #495057;
            margin-bottom: 4px;
        }

        .v-row i { color: <?= $colorHex ?>; width: 16px; }

        .voucher-qr { text-align: center; }
        .voucher-qr canvas { border-radius: 6px; }

        .v-codigo {
            font-family: monospace;
            font-size: .65rem;
            color: #adb5bd;
            margin-top: 4px;
            word-break: break-all;
        }

        .voucher-footer {
            border-top: 1px solid #f0f0f0;
            padding: 10px 20px;
            font-size: .75rem;
            color: #adb5bd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .badge-estado {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 600;
        }

        .badge-ok      { background: #d1fae5; color: #065f46; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-vencido { background: #fee2e2; color: #991b1b; }
        .badge-usado   { background: #fee2e2; color: #991b1b; border: 2px solid #991b1b; animation: pulse 2s infinite; }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .acciones { margin-top: 20px; display: flex; gap: 10px; }
        .acciones .btn { flex: 1; }

        @media print {
            body { background: #fff; }
            .acciones, .info-escane { display: none !important; }
            .voucher-container { margin: 0; padding: 0; max-width: 100%; }
            .voucher-card { box-shadow: none; border: 1px solid #ccc; border-radius: 8px; }
            @page { size: A6 landscape; margin: 8mm; }
        }
    </style>
</head>
<body>

<div class="voucher-container">

    <!-- Estado del voucher -->
    <div class="text-center mb-3">
        <?php
        $hoy = date('Y-m-d');
        $vencido = $voucher['fecha'] < $hoy;
        
        if ($vencido): ?>
            <span class="badge-estado badge-vencido">
                <i class="bi bi-exclamation-triangle-fill"></i> Voucher vencido
            </span>
        <?php elseif ($voucher['canjeado'] && $escaneFecha): ?>
            <div class="badge-estado badge-usado d-block py-3 mb-2">
                <i class="bi bi-x-octagon-fill fs-4 d-block mb-1"></i>
                <div class="fs-5">VOUCHER YA UTILIZADO</div>
                <small>Registrado el <?= date('d/m/Y H:i', strtotime($escaneFecha)) ?></small>
            </div>
        <?php elseif ($escaneFecha): ?>
             <span class="badge-estado badge-ok">
                <i class="bi bi-check-circle-fill"></i>
                Canjeado ahora mismo (<?= date('H:i') ?>)
            </span>
        <?php else: ?>
            <span class="badge-estado badge-pending">
                <i class="bi bi-hourglass-split"></i> Voucher válido — pendiente de uso
            </span>
        <?php endif; ?>
    </div>

    <div class="voucher-card">
        <div class="voucher-header">
            <img src="<?= BASE_URL ?>public/static/img/logoAtankalama.png" alt="Atankalama">
            <span class="tipo"><?= $etiqueta ?></span>
        </div>

        <div class="voucher-body">
            <div class="voucher-info">
                <div class="v-nombre"><?= htmlspecialchars($nombre) ?></div>
                <?php if ($empresa): ?>
                <div class="v-empresa"><i class="bi bi-building me-1"></i><?= htmlspecialchars($empresa) ?></div>
                <?php endif; ?>
                <?php if ($esNominal && !empty($voucher['rut'])): ?>
                <div class="v-empresa"><i class="bi bi-person-badge me-1"></i>RUT <?= htmlspecialchars($voucher['rut']) ?></div>
                <?php endif; ?>

                <div class="v-row">
                    <i class="bi bi-calendar3"></i>
                    <span><?= $fechaTexto ?></span>
                </div>
                <?php if ($hora !== '—'): ?>
                <div class="v-row">
                    <i class="bi bi-clock"></i>
                    <span><?= $hora ?></span>
                </div>
                <?php endif; ?>
                <div class="v-row">
                    <i class="bi bi-house-door"></i>
                    <span><?= htmlspecialchars($voucher['nombre_hotel']) ?></span>
                </div>
                <?php if (!empty($voucher['observaciones'])): ?>
                <div class="v-row mt-2">
                    <i class="bi bi-chat-text"></i>
                    <span class="small text-muted"><?= htmlspecialchars($voucher['observaciones']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="voucher-qr">
                <div id="qrcode" style="display:inline-block;"></div>
                <div class="v-codigo"><?= $voucher['codigo'] ?></div>
            </div>
        </div>

        <div class="voucher-footer">
            <span>Válido solo para la fecha y servicio indicados</span>
            <span>No transferible</span>
        </div>
    </div>

    <div class="info-escane text-center mt-3 p-3 bg-white rounded-4 border shadow-sm">
        <h6 class="fw-bold mb-2 small text-uppercase text-muted">Historial del Voucher</h6>
        <div class="small text-muted mb-1">
            <i class="bi bi-printer me-1"></i> 
            Impreso: <?= $impresoEn ? date('d/m/Y H:i', strtotime($impresoEn)) : 'No registrado' ?>
        </div>
        <?php if ($escaneFecha): ?>
        <div class="small text-danger fw-bold">
            <i class="bi bi-qr-code-scan me-1"></i> 
            Uso: <?= date('d/m/Y H:i', strtotime($escaneFecha)) ?>
        </div>
        <?php else: ?>
        <div class="small text-success">
            <i class="bi bi-check-lg me-1"></i> 
            Estado: Disponible para uso
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('qrcode');
    const url = window.location.href;
    new QRCode(el, {
        text: url,
        width: 120,
        height: 120,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });
});
</script>
</body>
</html>
