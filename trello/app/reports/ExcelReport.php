<?php
/**
 * ExcelReport — genera un .xls real usando SpreadsheetML 2003 (XML nativo).
 * No requiere librerías externas. Soportado por Excel 2003+, LibreOffice y Google Sheets.
 */
class ExcelReport
{
    // Colores corporativos para el encabezado
    private static string $COLOR_HEADER   = '#1e3a5f';
    private static string $COLOR_LISTA    = '#2563eb';
    private static string $COLOR_VENCIDA  = '#fee2e2';
    private static string $COLOR_HOY      = '#fef3c7';
    private static string $COLOR_SEMANA   = '#dbeafe';

    public static function exportTablero(array $tablero, array $listas, string $filename = ''): void
    {
        if (!$filename) {
            $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($tablero['nombre']));
            $filename = 'tablero-' . $slug . '-' . date('Y-m-d') . '.xls';
        }

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Calcular estadísticas globales
        $total_tarjetas  = 0;
        $total_vencidas  = 0;
        $total_items_ok  = 0;
        $total_items     = 0;
        $hoy             = date('Y-m-d');
        $semana          = date('Y-m-d', strtotime('+7 days'));

        foreach ($listas as $l) {
            $total_tarjetas += count($l['tarjetas']);
            foreach ($l['tarjetas'] as $t) {
                $total_items    += (int)$t['items_total'];
                $total_items_ok += (int)$t['items_ok'];
                $fv = $t['fecha_vencimiento'] ? substr($t['fecha_vencimiento'], 0, 10) : null;
                if ($fv && $fv < $hoy) $total_vencidas++;
            }
        }

        $color = ltrim($tablero['fondo_color'] ?? self::$COLOR_HEADER, '#');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
        ?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">

 <Styles>
  <Style ss:ID="s_title">
   <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
   <Font ss:Bold="1" ss:Color="#FFFFFF" ss:Size="14"/>
   <Interior ss:Color="#<?= $color ?>" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="s_subtitle">
   <Font ss:Bold="1" ss:Color="#475569" ss:Size="10"/>
   <Interior ss:Color="#f8fafc" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="s_col_header">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:Bold="1" ss:Color="#FFFFFF" ss:Size="10"/>
   <Interior ss:Color="#1e3a5f" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#3b82f6"/>
   </Borders>
  </Style>
  <Style ss:ID="s_lista_header">
   <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
   <Font ss:Bold="1" ss:Color="#FFFFFF" ss:Size="11"/>
   <Interior ss:Color="#<?= $color ?>" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="s_cell">
   <Alignment ss:Vertical="Top" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>
   </Borders>
  </Style>
  <Style ss:ID="s_cell_num">
   <Alignment ss:Horizontal="Center" ss:Vertical="Top"/>
   <Font ss:Color="#64748b" ss:Size="10"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#e2e8f0"/>
   </Borders>
  </Style>
  <Style ss:ID="s_vencida">
   <Alignment ss:Vertical="Top"/>
   <Interior ss:Color="<?= self::$COLOR_VENCIDA ?>" ss:Pattern="Solid"/>
   <Font ss:Color="#b91c1c"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#fca5a5"/>
   </Borders>
  </Style>
  <Style ss:ID="s_hoy">
   <Alignment ss:Vertical="Top"/>
   <Interior ss:Color="<?= self::$COLOR_HOY ?>" ss:Pattern="Solid"/>
   <Font ss:Color="#b45309"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#fcd34d"/>
   </Borders>
  </Style>
  <Style ss:ID="s_semana">
   <Alignment ss:Vertical="Top"/>
   <Interior ss:Color="<?= self::$COLOR_SEMANA ?>" ss:Pattern="Solid"/>
   <Font ss:Color="#1d4ed8"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#93c5fd"/>
   </Borders>
  </Style>
  <Style ss:ID="s_stat_label">
   <Font ss:Bold="1" ss:Color="#64748b" ss:Size="10"/>
   <Interior ss:Color="#f1f5f9" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="s_stat_val">
   <Alignment ss:Horizontal="Center"/>
   <Font ss:Bold="1" ss:Size="14"/>
   <Interior ss:Color="#f8fafc" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="s_bold">
   <Font ss:Bold="1"/>
  </Style>
  <Style ss:ID="s_muted">
   <Font ss:Color="#64748b" ss:Size="10"/>
  </Style>
 </Styles>

 <!-- ═══════════════════ HOJA 1: TARJETAS ═══════════════════ -->
 <Worksheet ss:Name="Tarjetas">
  <Table ss:DefaultRowHeight="16">

   <Column ss:Width="40"/>   <!-- # -->
   <Column ss:Width="100"/>  <!-- Lista -->
   <Column ss:Width="180"/>  <!-- Título -->
   <Column ss:Width="200"/>  <!-- Descripción -->
   <Column ss:Width="130"/>  <!-- Miembros -->
   <Column ss:Width="100"/>  <!-- Etiquetas -->
   <Column ss:Width="80"/>   <!-- Vencimiento -->
   <Column ss:Width="70"/>   <!-- Checklist -->
   <Column ss:Width="50"/>   <!-- Adjuntos -->
   <Column ss:Width="50"/>   <!-- Comentarios -->

   <!-- Fila 1: Título del tablero -->
   <Row ss:Height="28">
    <Cell ss:StyleID="s_title" ss:MergeAcross="9">
     <Data ss:Type="String"><?= self::x($tablero['nombre']) ?> — Tablero Kanban</Data>
    </Cell>
   </Row>

   <!-- Fila 2: Metadatos -->
   <Row ss:Height="18">
    <Cell ss:StyleID="s_subtitle" ss:MergeAcross="4">
     <Data ss:Type="String">Área: <?= self::x($tablero['area_nombre'] ?? '') ?> | Generado: <?= date('d/m/Y H:i') ?></Data>
    </Cell>
    <Cell ss:StyleID="s_subtitle" ss:MergeAcross="4">
     <Data ss:Type="String">Total tarjetas: <?= $total_tarjetas ?> | Vencidas: <?= $total_vencidas ?> | Checklist: <?= $total_items ? round($total_items_ok/$total_items*100).'%' : 'N/A' ?></Data>
    </Cell>
   </Row>

   <!-- Fila 3: en blanco -->
   <Row ss:Height="8"><Cell ss:MergeAcross="9"><Data ss:Type="String"></Data></Cell></Row>

   <!-- Fila 4: Cabeceras de columnas -->
   <Row ss:Height="22">
    <Cell ss:StyleID="s_col_header"><Data ss:Type="String">#</Data></Cell>
    <Cell ss:StyleID="s_col_header"><Data ss:Type="String">Lista</Data></Cell>
    <Cell ss:StyleID="s_col_header"><Data ss:Type="String">Título</Data></Cell>
    <Cell ss:StyleID="s_col_header"><Data ss:Type="String">Descripción</Data></Cell>
    <Cell ss:StyleID="s_col_header"><Data ss:Type="String">Miembros</Data></Cell>
    <Cell ss:StyleID="s_col_header"><Data ss:Type="String">Etiquetas</Data></Cell>
    <Cell ss:StyleID="s_col_header"><Data ss:Type="String">Vencimiento</Data></Cell>
    <Cell ss:StyleID="s_col_header"><Data ss:Type="String">Checklist</Data></Cell>
    <Cell ss:StyleID="s_col_header"><Data ss:Type="String">Adjuntos</Data></Cell>
    <Cell ss:StyleID="s_col_header"><Data ss:Type="String">Comentarios</Data></Cell>
   </Row>

   <?php foreach ($listas as $lista): ?>
   <!-- Sub-encabezado de lista -->
   <Row ss:Height="20">
    <Cell ss:StyleID="s_lista_header" ss:MergeAcross="9">
     <Data ss:Type="String">&#9658; <?= self::x($lista['nombre']) ?> (<?= count($lista['tarjetas']) ?> tarjeta<?= count($lista['tarjetas']) !== 1 ? 's' : '' ?>)</Data>
    </Cell>
   </Row>

   <?php if (empty($lista['tarjetas'])): ?>
   <Row>
    <Cell ss:StyleID="s_muted"><Data ss:Type="String"></Data></Cell>
    <Cell ss:StyleID="s_muted" ss:MergeAcross="8"><Data ss:Type="String">Sin tarjetas en esta lista</Data></Cell>
   </Row>
   <?php else: ?>
   <?php foreach ($lista['tarjetas'] as $t):
     $fv     = $t['fecha_vencimiento'] ? substr($t['fecha_vencimiento'], 0, 10) : null;
     $fv_txt = $fv ? date('d/m/Y', strtotime($fv)) : '';
     $urgencia = '';
     if ($fv) {
         if ($fv < $hoy)     $urgencia = 'vencida';
         elseif ($fv === $hoy) $urgencia = 'hoy';
         elseif ($fv <= $semana) $urgencia = 'semana';
     }
     $style_fv = match($urgencia) {
         'vencida' => 's_vencida',
         'hoy'     => 's_hoy',
         'semana'  => 's_semana',
         default   => 's_cell',
     };
     $miembros = implode(', ', array_column($t['miembros_detalle'] ?? [], 'nombre'));
     $etiquetas = implode(', ', array_map(fn($e) => $e['nombre'], $t['etiquetas'] ?? []));
     $cl_txt = $t['items_total'] > 0
         ? $t['items_ok'] . '/' . $t['items_total'] . ' (' . round($t['items_ok']/$t['items_total']*100) . '%)'
         : '';
     $desc = trim(preg_replace('/\s+/', ' ', $t['descripcion'] ?? ''));
     if (strlen($desc) > 250) $desc = substr($desc, 0, 247) . '...';
   ?>
   <Row ss:AutoFitHeight="1">
    <Cell ss:StyleID="s_cell_num"><Data ss:Type="Number"><?= (int)$t['numero'] ?></Data></Cell>
    <Cell ss:StyleID="s_cell"><Data ss:Type="String"><?= self::x($lista['nombre']) ?></Data></Cell>
    <Cell ss:StyleID="s_cell"><Data ss:Type="String"><?= self::x($t['titulo']) ?></Data></Cell>
    <Cell ss:StyleID="s_cell"><Data ss:Type="String"><?= self::x($desc) ?></Data></Cell>
    <Cell ss:StyleID="s_cell"><Data ss:Type="String"><?= self::x($miembros) ?></Data></Cell>
    <Cell ss:StyleID="s_cell"><Data ss:Type="String"><?= self::x($etiquetas) ?></Data></Cell>
    <Cell ss:StyleID="<?= $style_fv ?>"><Data ss:Type="String"><?= self::x($fv_txt) ?></Data></Cell>
    <Cell ss:StyleID="s_cell"><Data ss:Type="String"><?= self::x($cl_txt) ?></Data></Cell>
    <Cell ss:StyleID="s_cell_num"><Data ss:Type="Number"><?= (int)$t['cnt_adjuntos'] ?></Data></Cell>
    <Cell ss:StyleID="s_cell_num"><Data ss:Type="Number"><?= (int)$t['cnt_comentarios'] ?></Data></Cell>
   </Row>
   <?php endforeach; ?>
   <?php endif; ?>
   <?php endforeach; ?>

  </Table>
 </Worksheet>

 <!-- ═══════════════════ HOJA 2: RESUMEN ═══════════════════ -->
 <Worksheet ss:Name="Resumen">
  <Table>
   <Column ss:Width="160"/>
   <Column ss:Width="100"/>
   <Column ss:Width="100"/>
   <Column ss:Width="100"/>

   <Row ss:Height="28">
    <Cell ss:StyleID="s_title" ss:MergeAcross="3">
     <Data ss:Type="String">Resumen — <?= self::x($tablero['nombre']) ?></Data>
    </Cell>
   </Row>
   <Row ss:Height="8"><Cell ss:MergeAcross="3"><Data ss:Type="String"></Data></Cell></Row>

   <!-- Cabeceras resumen -->
   <Row ss:Height="20">
    <Cell ss:StyleID="s_col_header"><Data ss:Type="String">Lista</Data></Cell>
    <Cell ss:StyleID="s_col_header"><Data ss:Type="String">Tarjetas</Data></Cell>
    <Cell ss:StyleID="s_col_header"><Data ss:Type="String">Vencidas</Data></Cell>
    <Cell ss:StyleID="s_col_header"><Data ss:Type="String">% Checklist</Data></Cell>
   </Row>

   <?php
   $tot_t = 0; $tot_v = 0; $tot_ci = 0; $tot_co = 0;
   foreach ($listas as $l):
     $lv = 0; $li = 0; $lo = 0;
     foreach ($l['tarjetas'] as $t) {
         $fv2 = $t['fecha_vencimiento'] ? substr($t['fecha_vencimiento'], 0, 10) : null;
         if ($fv2 && $fv2 < $hoy) $lv++;
         $li += (int)$t['items_total'];
         $lo += (int)$t['items_ok'];
     }
     $pct_cl = $li ? round($lo/$li*100).'%' : 'N/A';
     $tot_t += count($l['tarjetas']); $tot_v += $lv; $tot_ci += $li; $tot_co += $lo;
   ?>
   <Row>
    <Cell ss:StyleID="s_cell"><Data ss:Type="String"><?= self::x($l['nombre']) ?></Data></Cell>
    <Cell ss:StyleID="s_cell"><Data ss:Type="Number"><?= count($l['tarjetas']) ?></Data></Cell>
    <Cell ss:StyleID="s_cell"><Data ss:Type="Number"><?= $lv ?></Data></Cell>
    <Cell ss:StyleID="s_cell"><Data ss:Type="String"><?= $pct_cl ?></Data></Cell>
   </Row>
   <?php endforeach; ?>

   <!-- Fila de totales -->
   <Row ss:Height="20">
    <Cell ss:StyleID="s_bold"><Data ss:Type="String">TOTAL</Data></Cell>
    <Cell ss:StyleID="s_bold"><Data ss:Type="Number"><?= $tot_t ?></Data></Cell>
    <Cell ss:StyleID="s_bold"><Data ss:Type="Number"><?= $tot_v ?></Data></Cell>
    <Cell ss:StyleID="s_bold"><Data ss:Type="String"><?= $tot_ci ? round($tot_co/$tot_ci*100).'%' : 'N/A' ?></Data></Cell>
   </Row>

  </Table>
 </Worksheet>

</Workbook>
<?php
        exit;
    }

    private static function x(string $str): string
    {
        return htmlspecialchars($str, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
