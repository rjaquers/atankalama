<?php

// controllers/ExcelColacionController.php
declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\IOFactory;

require_once __DIR__.'/../models/ExcelColacion.php';
require_once __DIR__.'/../models/ColacionLote.php';
require_once __DIR__.'/../models/ColacionVoucher.php';

class ExcelColacionController
{
    public function importarForm(): void
    {
        // combos: empresas, tipos servicio
        global $db;
        $empresas = $db->query('SELECT `id`, `business_name` AS `nombre` FROM `doc_companies` WHERE `active`=1 ORDER BY `business_name`')->fetch_all(MYSQLI_ASSOC);
        $tipos = $db->query('SELECT `id`,`nombre` FROM `colacion_servicio_tipo` ORDER BY `nombre`')->fetch_all(MYSQLI_ASSOC);
        include __DIR__.'/../views/colaciones/excel_import_form.php';
    }

    public function importarPost(): void
    {
        global $db;

        $empresa_id = (int)($_POST['empresa_id'] ?? 0);
        $tipo_id = (int)($_POST['servicio_tipo_id'] ?? 0);
        $fecha = trim((string)($_POST['fecha_servicio'] ?? date('Y-m-d')));

        if (empty($_FILES['archivo']['tmp_name'])) {
            http_response_code(400);
            echo 'Falta archivo';

            return;
        }

        $tmpPath = $_FILES['archivo']['tmp_name'];
        $originalName = $_FILES['archivo']['name'];
        $sha256 = hash_file('sha256', $tmpPath);

        // Evitar duplicado exacto
        $stmt = $db->prepare('SELECT id FROM colacion_excel WHERE sha256=? LIMIT 1');
        $stmt->bind_param('s', $sha256);
        $stmt->execute();
        $dup = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($dup) {
            header('Location: '.url('/colaciones/excel/ver/'.$dup['id']).'?dup=1');

            return;
        }

        $db->begin_transaction();
        try {
            // 1) crear registro excel
            $stmt = $db->prepare('INSERT INTO colacion_excel(empresa_id,servicio_tipo_id,fecha_servicio,nombre_archivo,sha256) VALUES(?,?,?,?,?)');
            $stmt->bind_param('iisss', $empresa_id, $tipo_id, $fecha, $originalName, $sha256);
            $stmt->execute();
            $excel_id = (int)$stmt->insert_id;
            $stmt->close();

            // 2) parseo
            $spread = IOFactory::load($tmpPath);
            $sheet = $spread->getSheet(0);
            $rows = $sheet->toArray(null, true, true, true); // keys A,B,C...

            $total = 0;
            $valid = 0;
            $ins = $db->prepare(
                'INSERT INTO colacion_excel_row (excel_id,row_num,archivo_row_id,rut,nombre,habitacion,is_valid,validation_msg)
                                 VALUES (?,?,?,?,?,?,?,?)'
            );

            foreach ($rows as $rowNum => $cols) {
                // Asumamos encabezado en fila 1. Ajusta si no lo tienes.
                if ($rowNum === 1) {
                    continue;
                }

                $total++;
                $archivo_row_id = trim((string)($cols['A'] ?? '')); // "id del archivo" (puede repetirse entre excels)
                $rut = strtoupper(trim((string)($cols['B'] ?? '')));
                $nombre = trim((string)($cols['C'] ?? ''));
                $hab = trim((string)($cols['D'] ?? ''));

                $is_valid = 1;
                $msg = null;
                if ($rut === '' || $nombre === '') {
                    $is_valid = 0;
                    $msg = 'Falta rut o nombre';
                }

                $ins->bind_param('iissssis', $excel_id, $rowNum, $archivo_row_id, $rut, $nombre, $hab, $is_valid, $msg);
                $ins->execute();
                if ($is_valid) {
                    $valid++;
                }
            }
            $ins->close();

            // 3) actualizar contadores
            $up = $db->prepare('UPDATE colacion_excel SET total_rows=?, valid_rows=? WHERE id=?');
            $up->bind_param('iii', $total, $valid, $excel_id);
            $up->execute();
            $up->close();

            $db->commit();

            header('Location: '.url('/colaciones/excel/ver/'.$excel_id));
        } catch (Throwable $e) {
            $db->rollback();
            http_response_code(500);
            echo 'Error importando: '.$e->getMessage();
        }
    }

    public function ver(int $excel_id): void
    {
        global $db;
        $excel = $db->query(
            'SELECT e.*, emp.business_name AS empresa, t.nombre AS servicio
                             FROM colacion_excel e
                             JOIN doc_companies emp ON emp.id=e.empresa_id
                             JOIN colacion_servicio_tipo t ON t.id=e.servicio_tipo_id
                             WHERE e.id='.(int)$excel_id
        )->fetch_assoc();
        if (! $excel) {
            http_response_code(404);
            echo 'Excel no encontrado';

            return;
        }

        $rows = $db->query('SELECT * FROM colacion_excel_row WHERE excel_id='.(int)$excel_id.' ORDER BY row_num ASC')->fetch_all(MYSQLI_ASSOC);
        include __DIR__.'/../views/colaciones/excel_preview.php';
    }

    public function crearLoteDesdeExcel(int $excel_id): void
    {
        global $db;
        $db->begin_transaction();
        try {
            $excel = $db->query('SELECT * FROM colacion_excel WHERE id='.(int)$excel_id.' FOR UPDATE')->fetch_assoc();
            if (! $excel) {
                throw new RuntimeException('Excel no encontrado');
            }

            $valid = (int)$excel['valid_rows'];
            if ($valid <= 0) {
                throw new RuntimeException('No hay filas válidas');
            }

            $loteM = new ColacionLote($db);
            $vchM = new ColacionVoucher($db);

            // 1) Crear lote
            $lote_id = $loteM->crear([
                                         'empresa_id' => (int)$excel['empresa_id'],
                                         'fecha_servicio' => $excel['fecha_servicio'],
                                         'servicio_tipo_id' => (int)$excel['servicio_tipo_id'],
                                         'cantidad' => $valid,
                                         'observaciones' => 'Generado desde Excel '.$excel['nombre_archivo'],
                                         'creado_por' => ($_SESSION['user_id'] ?? null),
                                         'excel_id' => (int)$excel['id']
                                     ], /*adics*/ []);

            // 2) Generar vouchers
            $vchM->generarDesdeLote($lote_id, $excel['fecha_servicio'], $valid);

            // 3) Traer vouchers + filas válidas y amarrar 1:1 en orden
            $vouchers = $vchM->listarPorLote($lote_id); // ya ordenados por numero_en_lote
            $rows = $db->query('SELECT * FROM colacion_excel_row WHERE excel_id='.(int)$excel_id.' AND is_valid=1 ORDER BY row_num ASC')->fetch_all(MYSQLI_ASSOC);

            $upd = $db->prepare('UPDATE `colacion_voucher` SET `excel_row_id`=?, `guest_rut`=?, `guest_nombre`=?, `guest_hab`=? WHERE `id`=?');
            $n = min(count($vouchers), count($rows));
            for ($i = 0; $i < $n; $i++) {
                $v = $vouchers[$i];
                $r = $rows[$i];
                $rid = (int)$r['id'];
                $upd->bind_param('isssi', $rid, $r['rut'], $r['nombre'], $r['habitacion'], $v['id']);
                $upd->execute();
            }
            $upd->close();

            $db->commit();
            header('Location: '.url('/colaciones/lotes/imprimir/'.$lote_id).'?ok=1');
        } catch (Throwable $e) {
            $db->rollback();
            http_response_code(500);
            echo 'Error creando lote: '.$e->getMessage();
        }
    }
}
