<?php
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Servicio de Generación de PDFs.
 *
 * Utiliza Dompdf para transformar plantillas HTML en documentos PDF oficiales.
 * Soporta reemplazo de variables dinámicas del contrato y la empresa.
 *
 * @package App\Services
 */
class PdfGeneratorService
{
    /**
     * Devuelve el logo del hotel como data URI base64 para embeber en PDFs.
     */
    private function getLogoBase64()
    {
        $logoPath = dirname(APP_ROOT) . '/public/uploads/logoHotelAtankalama.png';
        if (!file_exists($logoPath)) return null;
        return 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
    }

    /**
     * Convierte una imagen a base64 embebible en HTML para Dompdf.
     * Dompdf no soporta WebP, por lo que se convierte a JPEG en memoria con GD.
     *
     * @param  string $fullPath Ruta absoluta del archivo en disco
     * @param  string $mimeType MIME type almacenado en BD
     * @return string|null Data URI lista para usar en src, o null si falla
     */
    private function imageToBase64($fullPath, $mimeType)
    {
        if (!file_exists($fullPath)) return null;

        if ($mimeType === 'image/webp' || strtolower(pathinfo($fullPath, PATHINFO_EXTENSION)) === 'webp') {
            $img = @imagecreatefromwebp($fullPath);
            if (!$img) return null;
            ob_start();
            imagejpeg($img, null, 85);
            $data = ob_get_clean();
            imagedestroy($img);
            return 'data:image/jpeg;base64,' . base64_encode($data);
        }

        return 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($fullPath));
    }

    /**
     * Genera el HTML para los archivos adjuntos (fotos).
     * Embebe las imágenes como base64 para garantizar compatibilidad con Dompdf.
     */
    private function renderAttachmentsHtml($contractId)
    {
        $attachmentModel = new ContractAttachmentModel();
        $attachments = $attachmentModel->getByContractId($contractId);

        $imagesHtml = '';
        if (!empty($attachments)) {
            $imagesHtml = '<div style="page-break-before: always;"><h3>Anexos Fotográficos y Diseños</h3><table style="width: 100%; border-collapse: collapse;">';
            $count = 0;
            foreach ($attachments as $att) {
                if (strpos($att['mime_type'], 'image') !== false) {
                    $fullPath = PUBLIC_PATH . $att['file_path'];
                    $src = $this->imageToBase64($fullPath, $att['mime_type']);
                    if (!$src) continue;

                    if ($count % 2 == 0) $imagesHtml .= '<tr>';
                    $imagesHtml .= '<td style="width: 50%; padding: 10px; text-align: center; border: 1px solid #eee;">
                        <img src="' . $src . '" style="width: 250px; height: auto; max-height: 300px;"><br>
                        <small style="color: #666; font-size: 8pt;">' . htmlspecialchars($att['original_name']) . '</small>
                    </td>';
                    if ($count % 2 == 1) $imagesHtml .= '</tr>';
                    $count++;
                }
            }
            if ($count > 0 && $count % 2 != 0) $imagesHtml .= '<td style="width: 50%;"></td></tr>';
            $imagesHtml .= '</table></div>';
        }
        return $imagesHtml;
    }

    /**
     * Genera y guarda el PDF de la cotización en el servidor.
     *
     * @param int $id ID de la cotización
     * @return array
     */
    public function saveQuotationPdf($id)
    {
        $contractModel = new ContractModel();
        $q = $contractModel->getById($id);
        if (!$q) return ['status' => false, 'message' => 'Cotización no encontrada'];

        // 1. Obtener HTML (Podríamos refactorizar streamQuotationPdf para que devuelva el HTML, 
        // pero por ahora replicaremos la lógica de construcción para asegurar el stream vs save)
        // ... por brevedad y para no romper el stream actual, llamaremos a una función interna de renderizado
        $html = $this->renderQuotationHtml($id);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', $_SERVER['DOCUMENT_ROOT']);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // 2. Definir ruta de guardado (Estructura igual a contratos)
        $subDir = $q['company_id'] . '/' . date('Y-m');
        $fullDir = UPLOAD_BASE_PATH . '/' . $subDir;
        if (!is_dir($fullDir)) mkdir($fullDir, 0755, true);

        $filename = 'COT_' . $q['code'] . '_' . date('His') . '.pdf';
        $filePath = $fullDir . '/' . $filename;
        $relativeUrl = '/uploads/contracts/' . $subDir . '/' . $filename;

        if (file_put_contents($filePath, $dompdf->output())) {
            $contractModel->updatePdfPath($id, $relativeUrl);
            return ['status' => true, 'file_path' => $relativeUrl];
        }

        return ['status' => false, 'message' => 'No se pudo escribir el archivo en el disco'];
    }

    /**
     * Centraliza la lógica de construcción del HTML de la cotización.
     */
    private function renderQuotationHtml($id)
    {
        $contractModel = new ContractModel();
        $q = $contractModel->getById($id);

        $serviceModel = new ServiceModel();
        $services = $serviceModel->getByContractId($id);

        $hotelModel = new HotelModel();
        $hotels = $hotelModel->getByContractId($id);
        $hotelNames = array_map(function($h) { return $h['name']; }, $hotels);

        $attachmentModel = new ContractAttachmentModel();
        $attachments = $attachmentModel->getByContractId($id);

        // 1. Obtener texto de plantilla si existe
        $templateText = "";
        if (!empty($q['template_id'])) {
            $templateModel = new ContractTemplateModel();
            $template = $templateModel->getById($q['template_id']);
            if ($template) {
                $templateText = $template['body_html'];
                // Reemplazar variables básicas en la plantilla
                $vars = $this->prepareVariables($q);
                foreach ($vars as $key => $value) {
                    $search = ['{{' . $key . '}}', '{{ ' . $key . ' }}'];
                    $templateText = str_replace($search, $value, $templateText);
                }
            }
        }

        // 2. Tabla de servicios con totales por línea
        $billingLabels = [
            'per_person' => 'Por persona',
            'per_day'    => 'Por día',
            'per_event'  => 'Por evento',
        ];

        $servicesHtml = '
        <table style="width:100%; border-collapse:collapse; margin-top:20px; font-size:10pt;">
            <thead>
                <tr style="background-color:#1a3a5c; color:#fff;">
                    <th style="padding:8px; border:1px solid #ccc; text-align:center; width:8%;">Cant.</th>
                    <th style="padding:8px; border:1px solid #ccc; text-align:left;">Descripción</th>
                    <th style="padding:8px; border:1px solid #ccc; text-align:right; width:16%;">V. Unitario</th>
                    <th style="padding:8px; border:1px solid #ccc; text-align:center; width:10%;">Moneda</th>
                    <th style="padding:8px; border:1px solid #ccc; text-align:center; width:14%;">Tipo Cobro</th>
                    <th style="padding:8px; border:1px solid #ccc; text-align:right; width:16%;">Total</th>
                </tr>
            </thead>
            <tbody>';

        $i = 1;
        foreach ($services as $s) {
            $qty        = 1;
            $unitPrice  = (float)$s['unit_price'];
            $total      = $unitPrice * $qty;
            $currency   = $s['currency'] ?? 'CLP';
            $billing    = $billingLabels[$s['billing_type']] ?? $s['billing_type'];
            $note       = !empty($s['contract_notes'])
                            ? '<br><span style="color:#666; font-size:8pt;">' . htmlspecialchars($s['contract_notes']) . '</span>'
                            : '';
            $fmtPrice   = number_format($unitPrice, 2, ',', '.');
            $fmtTotal   = number_format($total, 2, ',', '.');
            $bg         = ($i % 2 === 0) ? 'background-color:#f9f9f9;' : '';

            $servicesHtml .= "
                <tr style='{$bg}'>
                    <td style='padding:8px; border:1px solid #ddd; text-align:center;'>{$qty}</td>
                    <td style='padding:8px; border:1px solid #ddd;'><strong>" . htmlspecialchars($s['name']) . "</strong>{$note}</td>
                    <td style='padding:8px; border:1px solid #ddd; text-align:right;'>{$fmtPrice}</td>
                    <td style='padding:8px; border:1px solid #ddd; text-align:center;'>{$currency}</td>
                    <td style='padding:8px; border:1px solid #ddd; text-align:center;'>{$billing}</td>
                    <td style='padding:8px; border:1px solid #ddd; text-align:right;'>{$fmtTotal}</td>
                </tr>";
            $i++;
        }

        $servicesHtml .= '</tbody></table>';

        // 3. Bloque de totales: sumar los unit_price de los servicios de la tabla
        $neto     = 0.0;
        $currency = 'CLP';
        foreach ($services as $s) {
            $neto += (float)$s['unit_price'];
            if (!empty($s['currency'])) $currency = $s['currency'];
        }
        // Fallback: si no hay servicios con precio, usar base_amount del contrato
        if ($neto == 0) $neto = (float)$q['base_amount'];
        $iva        = $neto * 0.19;
        $totalFinal = $neto + $iva;

        $totalsHtml = '
        <table style="width:100%; border-collapse:collapse; margin-top:4px; font-size:10pt;">
            <tr>
                <td style="width:70%;"></td>
                <td style="padding:6px 8px; border:1px solid #ddd; text-align:right; width:16%;"><strong>Neto</strong></td>
                <td style="padding:6px 8px; border:1px solid #ddd; text-align:right; width:14%;">' . number_format($neto, 2, ',', '.') . ' ' . $currency . '</td>
            </tr>
            <tr style="background-color:#f9f9f9;">
                <td></td>
                <td style="padding:6px 8px; border:1px solid #ddd; text-align:right;">IVA (19%)</td>
                <td style="padding:6px 8px; border:1px solid #ddd; text-align:right;">' . number_format($iva, 2, ',', '.') . ' ' . $currency . '</td>
            </tr>
            <tr style="background-color:#1a3a5c; color:#fff;">
                <td></td>
                <td style="padding:8px; border:1px solid #1a3a5c; text-align:right;"><strong>TOTAL</strong></td>
                <td style="padding:8px; border:1px solid #1a3a5c; text-align:right;"><strong>' . number_format($totalFinal, 2, ',', '.') . ' ' . $currency . '</strong></td>
            </tr>
        </table>';

        $imagesHtml = $this->renderAttachmentsHtml($id);

        $logoSrc  = $this->getLogoBase64();
        $logoHtml = $logoSrc
            ? '<img src="' . $logoSrc . '" style="max-height:80px; margin-bottom:10px;">'
            : '';

        return '
        <html>
        <head>
            <style>
                body { font-family: "Helvetica", sans-serif; color: #333; line-height: 1.6; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1a3a5c; padding-bottom: 10px; }
                .footer { position: fixed; bottom: -30px; left: 0; right: 0; text-align: center; font-size: 9pt; color: #999; }
                .title { color: #1a3a5c; font-size: 24pt; margin-bottom: 5px; }
                .subtitle { font-size: 14pt; color: #666; }
                .info-box { background-color: #f9f9f9; padding: 15px; border-left: 5px solid #1a3a5c; margin-bottom: 20px; }
                .company-name { font-size: 16pt; font-weight: bold; color: #1a3a5c; }
                h3 { border-bottom: 1px solid #eee; padding-bottom: 5px; color: #1a3a5c; }
                .legal-content { font-size: 10pt; color: #444; margin-top: 30px; border-top: 1px dashed #ccc; padding-top: 20px; }
            </style>
        </head>
        <body>
            <div class="header">
                '.$logoHtml.'
                <div class="title">PROPUESTA COMERCIAL</div>
                <div class="subtitle">Hotel Atankalama — Cotización #'.$q['code'].'</div>
            </div>

            <div class="info-box">
                <div class="company-name">'.$q['business_name'].'</div>
                <div>RUT: '.$q['company_rut'].'</div>
                <div>Atención: '.$q['contact_name'].'</div>
            </div>

            <div style="margin-bottom: 20px;">
                <strong>Hoteles Incluidos:</strong> '.implode(', ', $hotelNames).'<br>
                <strong>Fecha de Emisión:</strong> '.date('d/m/Y').'<br>
                <strong>Validez de la oferta:</strong> 15 días corridos.
            </div>

            <h3>Detalle de Servicios y Valores</h3>
            '.$servicesHtml.'
            '.$totalsHtml.'

            '.(!empty($q['notes']) ? '
            <div style="margin-top:24px; padding:12px; background:#f9f9f9; border-left:4px solid #1a3a5c;">
                <strong>Observaciones</strong><br>
                <span style="font-size:10pt;">'.nl2br(htmlspecialchars($q['notes'])).'</span>
            </div>' : '').'

            '.(!empty($templateText) ? '<div class="legal-content"><h3>Términos y Condiciones Generales</h3>'.$templateText.'</div>' : '').'

            '.$imagesHtml.'

            <div class="footer">
                Hotel Atankalama — Sistema de Gestión Comercial
            </div>
        </body>
        </html>';
    }

    /**
     * Genera y envía el PDF de la cotización directamente al navegador.
     *
     * @param int $id ID de la cotización
     */
    public function streamQuotationPdf($id)
    {
        $contractModel = new ContractModel();
        $q = $contractModel->getById($id);
        if (!$q) die("Cotización no encontrada");

        $html = $this->renderQuotationHtml($id);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', $_SERVER['DOCUMENT_ROOT']);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("Cotizacion_{$q['code']}.pdf", ["Attachment" => false]);
        exit;
    }

    /**
     * Genera un PDF a partir de una plantilla y datos de contrato.
     * 
     * @param int $contractId ID del contrato
     * @return array ['status' => bool, 'message' => string, 'file_path' => string|null]
     */
    public function generateContractPdf($contractId)
    {
        $contractModel = new ContractModel();
        $contract = $contractModel->getById($contractId);

        if (!$contract) {
            return ['status' => false, 'message' => 'Contrato no encontrado.'];
        }

        if (empty($contract['template_id'])) {
            return ['status' => false, 'message' => 'El contrato no tiene una plantilla asociada.'];
        }

        // 1. Obtener la plantilla
        $templateModel = new ContractTemplateModel();
        $template = $templateModel->getById($contract['template_id']);

        if (!$template) {
            return ['status' => false, 'message' => 'Plantilla no encontrada.'];
        }

        // 2. Preparar variables de reemplazo
        $variables = $this->prepareVariables($contract);
        
        $bodyHtml   = $template['body_html'];
        $headerText = $template['header_text'] ?? '';
        $footerText = $template['footer_text'] ?? '';

        // Reemplazar variables en todas las secciones
        foreach ($variables as $key => $value) {
            $search = ['{{' . $key . '}}', '{{ ' . $key . ' }}'];
            $bodyHtml   = str_replace($search, $value, $bodyHtml);
            $headerText = str_replace($search, $value, $headerText);
            $footerText = str_replace($search, $value, $footerText);
        }

        $imagesHtml = $this->renderAttachmentsHtml($contractId);

        $logoSrc = $this->getLogoBase64();
        $logoHeaderHtml = $logoSrc
            ? '<img src="' . $logoSrc . '" style="max-height:50px; vertical-align:middle;">'
            : '';

        // 3. Construir HTML completo con estructura para Dompdf (Header/Footer fijos)
        $htmlContent = '
        <html>
        <head>
            <style>
                @page { margin: 110px 50px 70px; }
                header { position: fixed; top: -80px; left: 0px; right: 0px; height: 70px; border-bottom: 1px solid #1a3a5c; font-size: 9pt; color: #777; }
                header table { width: 100%; border-collapse: collapse; }
                header td { vertical-align: middle; padding: 4px 0; }
                footer { position: fixed; bottom: -50px; left: 0px; right: 0px; height: 40px; text-align: center; border-top: 1px solid #eee; font-size: 8pt; color: #999; }
                .page-number:after { content: counter(page); }
                body { font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.5; color: #333; }
                table { width: 100%; border-collapse: collapse; }
            </style>
        </head>
        <body>
            <header>
                <table><tr>
                    <td style="width:120px;">' . $logoHeaderHtml . '</td>
                    <td style="text-align:right; font-size:9pt; color:#777;">' . $headerText . '</td>
                </tr></table>
            </header>
            <footer>' . $footerText . ' | Página <span class="page-number"></span></footer>
            <main>' . $bodyHtml . '
            ' . $imagesHtml . '
            </main>
        </body>
        </html>';

        // 4. Configurar Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // Para imágenes externas
        $options->set('chroot', $_SERVER['DOCUMENT_ROOT']);
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlContent);

        // (Opcional) Configurar tamaño de papel
        $dompdf->setPaper('A4', 'portrait');

        try {
            // Renderizar PDF (memoria)
            $dompdf->render();

            // 4. Definir ruta de guardado
            $subDir = $contract['company_id'] . '/' . date('Y-m');
            $fullDir = UPLOAD_BASE_PATH . '/' . $subDir;
            if (!is_dir($fullDir)) mkdir($fullDir, 0755, true);

            $filename = 'CTR_' . $contract['code'] . '_' . date('His') . '.pdf';
            $filePath = $fullDir . '/' . $filename;
            $relativeUrl = $subDir . '/' . $filename;

            // Guardar archivo
            file_put_contents($filePath, $dompdf->output());

            // 5. Actualizar registro del contrato
            $contractModel->updatePdfPath($contractId, $relativeUrl);

            return [
                'status'    => true,
                'message'   => 'PDF generado correctamente.',
                'file_path' => $relativeUrl
            ];
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'Error al generar el PDF: ' . $e->getMessage()];
        }
    }

    /**
     * Prepara el mapa de variables para reemplazo en la plantilla.
     *
     * Variables disponibles:
     * - Contrato:  contrato_codigo, contrato_tipo, monto_total, monto_letras,
     *              frecuencia_pago, huespedes_base
     * - Empresa:   empresa_nombre, empresa_rut, empresa_direccion, representante
     * - Hotel:     hotel_nombre, hotel_codigo, hotel_direccion, hotel_ciudad,
     *              hotel_telefono, hoteles_lista
     * - Fechas:    fecha_actual, fecha_inicio, fecha_termino
     *
     * @param array $contract Datos del contrato (JOIN con empresa)
     * @return array Mapa ['variable' => 'valor']
     */
    private function prepareVariables($contract)
    {
        // Obtener hoteles vinculados al contrato
        $hotelModel = new HotelModel();
        $hotels = $hotelModel->getByContractId($contract['id']);
        $primaryHotel = !empty($hotels) ? $hotels[0] : null;

        // Lista de nombres de todos los hoteles asociados
        $hotelNames = array_map(function($h) { return $h['name']; }, $hotels);

        // Obtener servicios incluidos
        $serviceModel = new ServiceModel();
        $services = $serviceModel->getByContractId($contract['id']);
        $serviceNames = array_map(function($s) { return $s['name']; }, $services);

        // Obtener escalas de precio (tiers) si corresponde
        $tierModel = new ContractTierModel();
        $tiers = $tierModel->getByContractId($contract['id']);
        $tiersHtml = $this->formatTiersTable($tiers, $contract['pricing_mode']);

        $vars = [
            // -- Contrato --
            'contrato_codigo'     => $contract['code'],
            'contrato_tipo'       => ucfirst($contract['contract_type']),
            'monto_total'         => '$' . number_format((float)$contract['base_amount'], 0, ',', '.'),
            'monto_letras'        => '', // TODO: convertir monto a palabras
            'frecuencia_pago'     => ucfirst($contract['payment_frequency']),
            'huespedes_base'      => $contract['base_guests'] ?? '0',
            'escala_precios'      => $tiersHtml,
            'servicios_incluidos' => implode(', ', $serviceNames),
            'notas_contrato'      => $contract['notes'] ?? '',

            // -- Empresa --
            'empresa_nombre'    => $contract['business_name'],
            'empresa_rut'       => $contract['company_rut'] ?? '',
            'empresa_direccion' => $contract['company_address'] ?? '',
            'representante'     => $contract['contact_name'] ?? '',

            // -- Hotel (primer hotel vinculado; si hay varios, usar hoteles_lista) --
            'hotel_nombre'            => $primaryHotel ? $primaryHotel['name'] : '',
            'hotel_codigo'            => $primaryHotel ? $primaryHotel['code'] : '',
            'hotel_rut'               => $primaryHotel ? ($primaryHotel['rut'] ?? '') : '',
            'hotel_direccion'         => $primaryHotel ? ($primaryHotel['address'] ?? '') : '',
            'hotel_ciudad'            => $primaryHotel ? ($primaryHotel['city'] ?? '') : '',
            'hotel_telefono'          => $primaryHotel ? ($primaryHotel['phone'] ?? '') : '',
            'hotel_email'             => $primaryHotel ? ($primaryHotel['email'] ?? '') : '',
            'hotel_representante'     => $primaryHotel ? ($primaryHotel['legal_representative'] ?? '') : '',
            'hotel_rut_representante' => $primaryHotel ? ($primaryHotel['representative_rut'] ?? '') : '',
            'hoteles_lista'           => implode(', ', $hotelNames),

            // -- Fechas --
            'fecha_actual'      => date('d/m/Y'),
            'fecha_inicio'      => date('d/m/Y', strtotime($contract['start_date'])),
            'fecha_termino'     => $contract['end_date'] ? date('d/m/Y', strtotime($contract['end_date'])) : 'Indefinido',
        ];

        // Aliases para mayor flexibilidad (natural mapping)
        $vars['nombre_empresa']    = $vars['empresa_nombre'];
        $vars['rut_empresa']       = $vars['empresa_rut'];
        $vars['direccion_empresa'] = $vars['empresa_direccion'];
        $vars['codigo_contrato']   = $vars['contrato_codigo'];
        $vars['tipo_contrato']     = $vars['contrato_tipo'];
        $vars['nombre_hotel']      = $vars['hotel_nombre'];
        $vars['codigo_hotel']      = $vars['hotel_codigo'];

        return $vars;
    }

    /**
     * Formatea las escalas de precio en una tabla HTML básica para el PDF.
     *
     * @param array  $tiers       Lista de escalas
     * @param string $pricingMode Modo de cobro
     * @return string HTML de la tabla o mensaje si no aplica
     */
    private function formatTiersTable($tiers, $pricingMode)
    {
        if ($pricingMode !== 'por_persona' || empty($tiers)) {
            return '<em>No aplica (cobro por grupo/monto fijo)</em>';
        }

        $html = '<table style="width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11pt;">';
        $html .= '<thead>';
        $html .= '<tr style="background-color: #f2f2f2;">';
        $html .= '<th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Rango Huéspedes</th>';
        $html .= '<th style="border: 1px solid #ccc; padding: 8px; text-align: right;">Precio x Persona</th>';
        $html .= '<th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Descripción</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        foreach ($tiers as $tier) {
            $range = $tier['min_guests'];
            if (!empty($tier['max_guests'])) {
                $range .= ' a ' . $tier['max_guests'];
            } else {
                $range .= '+';
            }

            $price = '$' . number_format($tier['price_per_person'], 0, ',', '.');
            $desc = $tier['description'] ?? '-';

            $html .= '<tr>';
            $html .= '<td style="border: 1px solid #ccc; padding: 8px;">' . $range . '</td>';
            $html .= '<td style="border: 1px solid #ccc; padding: 8px; text-align: right;">' . $price . '</td>';
            $html .= '<td style="border: 1px solid #ccc; padding: 8px;">' . $desc . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }
}
