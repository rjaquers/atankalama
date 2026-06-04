<?php include __DIR__ . '/../../includes/header.php'; ?>
<h3>Lotes de Colaciones</h3>

<div class="row">
    <div class="col8"></div>
    <div class="col2">
        <form class='row g-2 mb-3'>
            <div class='col-md-4'>
                <select name='empresa_id' class='form-select'>
                    <option value=''>-- Empresa --</option>
                    <?php foreach ($empresas as $e): ?>
                        <option value="<?=(int)$e['id']?>" <?=(isset($_GET['empresa_id']) && $_GET['empresa_id'] == $e['id']) ? 'selected' : ''?>>
                            <?=htmlspecialchars($e['nombre'])?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="fecha" value="<?=htmlspecialchars($_GET['fecha'] ?? '')?>" class="form-control">
            </div>
            <div class="col-md-3">
                <button class="btn btn-secondary">Filtrar</button>
                <a class="btn btn-outline-secondary" href="/custodia/colaciones/lotes">Limpiar</a>
                <a class="btn btn-primary" href="/custodia/colaciones/lotes/crear">Nuevo Lote</a>
            </div>
        </form>
    </div>
</div>


<table class="table table-sm align-middle">
    <thead>
    <tr>
        <th>Acciones</th>
        <th>ID</th>
        <th>Empresa</th>
        <th>Fecha Inicio</th>
        <th>Fecha Fin</th>
        <th>Tipo de Comida</th>
        <th>Servicio</th>
        <th>OBS</th>
        <th>Cantidad</th>
        <th>Creado</th>


    </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr >
            <td nowrap="nowrap">
                <?php if (($r['activo'] != 0) and ($r['fecha_fin_servicio'] >= date('Y-m-d'))):   ?>

                    <!-- Imprimir Lote (ya existía) -->
                    <a class="btn btn-sm btn-secondary mb-1"
                       href="/custodia/colaciones/lotes/imprimir/<?= (int)$r['id'] ?>"
                       target="_blank" title="imprimir">  <i class="fa fa-print"></i>
                    </a>

                    <?php if($r['excel']===1): ?>
<!--                  Editar Lote --> 
<!--                    <a class='btn btn-sm btn-warning mb-1'-->
<!--                       href="/custodia/colaciones/lotes/form/--><?php //=(int)$r['id']?><!--"-->
<!--                       title='Editar lote'>-->
<!--                        <i class='fa fa-pencil-alt'></i>-->
<!--                    </a>-->


                    <!-- Listar Vouchers o Fichas (ya existía) -->
                    <a class='btn btn-sm btn-info text-white mb-1'
                       href="/custodia/colaciones/personas/<?=(int)$r['id']?>"
                       title='Listar Personas'  > <i class='fa fa-user'></i>
                    </a>
                     <?php endif; ?>

                <?php endif; ?>


            </td>
            <td><?=(int)$r['id']?></td>
            <td><?=htmlspecialchars($r['empresa'])?></td>
            <td><?=htmlspecialchars($r['fecha_servicio'])?></td>
            <td><?=htmlspecialchars($r['fecha_fin_servicio'])?></td>

            <td><?=htmlspecialchars($r['servicio'])?></td>
            <td  style="color: #990000; font-size: xx-small">
                <?=nl2br(htmlspecialchars($r['observaciones']?? '-'))?></td>
            <td>
                <small class="text-muted">
                    <?=htmlspecialchars($r['servicios_adicionales_nombre'] ?? '—') ?>

                </small>
            </td>

            <td><?=(int)$r['cantidad']?></td>
            <td><small><?=htmlspecialchars($r['creado_en'])?></small></td>


        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
