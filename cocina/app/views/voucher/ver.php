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
    :root {
        --voucher-color: <?= $colorHex ?>;
        --voucher-text-color: <?= $colorBadge === 'warning' ? '#000' : '#fff' ?>;
        --voucher-img-filter: brightness(0)<?= $colorBadge !== 'warning' ? ' invert(1)' : '' ?>;
    }
    </style>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/static/voucher/ver.css">
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

<script src="<?= BASE_URL ?>public/static/voucher/ver.js"></script>
</body>
</html>
