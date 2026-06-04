<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
//require_once __DIR__ . '/../config/Database.php';


class NovedadController
{
    /**
     * Muestra el formulario para ingresar una nueva novedad.
     *
     * Qué hace:
     * - Obtiene lista de recepcionistas para el select
     * - Carga la vista novedades_form.php
     */
    public function form()
    {
        // 1. Una sola conexión
        $pdo = Database::getConnection();

        // 2. Cargar modelo
        require_once __DIR__ . '/../models/Recepcionista.php';

        // 3. Obtener datos
        $recepcionistaModel = new Recepcionista(); // Corregido: sin argumento $pdo
        $recepcionistas = $recepcionistaModel->listar();

        // 4. Enviar datos a la vista
        include __DIR__ . '/../views/novedades/form.php';
    }
    // Fin de la función form()


    /**
     * Procesa y guarda una nueva novedad.
     *
     * Qué hace:
     * - Valida datos obligatorios de $_POST (area, detalle, hotel, recepcionista_id, nivel_importancia)
     * - Calcula score e importancia sugerida usando ImportanciaService
     * - Guarda la novedad en el modelo
     * - Procesa archivos adjuntos (conversión a WebP si aplica)
     * - Envía notificación por correo
     * - Redirige al listado
     */
    public function store()
    {
        $model = new Novedad(); // Corregido: sin argumento $pdo


        $requiereSeguimiento = isset($_POST['requiere_seguimiento']) ? (int) $_POST['requiere_seguimiento'] : 0;

        if ($requiereSeguimiento !== 1) {
            $requiereSeguimiento = 0;
        }

        $seguimientoEstado = ($requiereSeguimiento === 1) ? 1 : 0;

        $tipoSeguimiento = isset($_POST['tipo_seguimiento']) ? trim($_POST['tipo_seguimiento']) : null;
        $flexkeepingId = isset($_POST['flexkeeping_id']) ? trim($_POST['flexkeeping_id']) : null;

        // ===============================
        // VALIDACIONES BÁSICAS
        // ===============================

        $area = isset($_POST['area']) ? trim($_POST['area']) : '';
        $detalle = isset($_POST['detalle']) ? trim($_POST['detalle']) : '';
        $hotel = isset($_POST['hotel']) ? trim($_POST['hotel']) : '';
        $recepcionistaId = (int) ($_POST['recepcionista_id'] ?? 0);
        $nivel_importancia = isset($_POST['nivel_importancia']) ? (int) $_POST['nivel_importancia'] : 0;


        if ($recepcionistaId <= 0 || $area === '' || $detalle === '' || $hotel === '') {
            die('Error: Datos obligatorios incompletos.');
        }

        if ($nivel_importancia < 1 || $nivel_importancia > 10) {
            header('Location: index.php?route=novedades/form&error=datos');
            exit;
        }

        // ===============================
        // CÁLCULO AUTOMÁTICO
        // ===============================

        $importanciaService = new ImportanciaService();
        $resultado = $importanciaService->calcular($detalle, $area);

        $nivel_sugerido = $resultado['nivel_sugerido'];
        $score_calculado = $resultado['score_calculado'];
        $detalle_calculo = $resultado['detalle_calculo'];

        // ===============================
        // PREPARAR DATA PARA MODELO
        // ===============================

        $data = [
            'recepcionista_id' => $recepcionistaId,
            'area' => $area,
            'detalle' => $detalle,
            'hotel' => $hotel,
            'tipo_novedad' => $_POST['tipo_novedad'] ?? 'Otro',
            'requiere_seguimiento' => $requiereSeguimiento,
            'seguimiento_estado' => $seguimientoEstado,
            'tipo_seguimiento' => $tipoSeguimiento,
            'flexkeeping_id' => $flexkeepingId,
            'nivel_importancia' => $nivel_importancia,
            'nivel_sugerido' => $nivel_sugerido,
            'score_calculado' => $score_calculado,
            'detalle_calculo' => $detalle_calculo
        ];

        $idNovedad = $model->guardar($data);

        // ===============================
        // PROCESAR ARCHIVOS
        // ===============================

        if (!empty($_FILES['archivos']['name'][0])) {

            $uploadDir = __DIR__ . '/../uploads/' . date('Y_m_d') . '/novedad_' . $idNovedad . '/';

            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['archivos']['tmp_name'] as $index => $tmpName) {

                if (!is_uploaded_file($tmpName)) {
                    continue;
                }

                $nombreOriginal = basename($_FILES['archivos']['name'][$index]);
                $tipo = mime_content_type($tmpName);

                $nombreLimpio = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nombreOriginal);
                $nombreLimpio = strtolower($nombreLimpio);
                $nombreLimpio = preg_replace('/[^a-z0-9\._-]/', '_', $nombreLimpio);

                $timestamp = date('Ymd_His');
                $ext = pathinfo($nombreLimpio, PATHINFO_EXTENSION);
                $base = pathinfo($nombreLimpio, PATHINFO_FILENAME);

                $nombreSeguro = $base . '_' . $timestamp . '.' . $ext;
                $destino = $uploadDir . $nombreSeguro;

                $uploadSuccess = move_uploaded_file($tmpName, $destino);

                if ($uploadSuccess) {
                    if (in_array(strtolower($ext), ['jpg', 'jpeg'])) {

                        $imagen = @imagecreatefromjpeg($destino);

                        if ($imagen) {

                            $nombreWebp = $base . '_' . $timestamp . '.webp';
                            $rutaWebp = $uploadDir . $nombreWebp;

                            imagewebp($imagen, $rutaWebp, 80);
                            imagedestroy($imagen);

                            unlink($destino);

                            $nombreSeguro = $nombreWebp;
                            $tipo = 'image/webp';
                        }
                    }

                    $model->guardarArchivo($idNovedad, $nombreSeguro, $tipo);
                } else {
                    error_log("No se pudo subir el archivo: $destino");
                }
            }
        }

        // ===============================
        // ENVIAR CORREO
        // ===============================

        $novedad = $model->obtenerPorId($idNovedad);

        $this->enviarEmail(
            $novedad['recepcionista_id'],
            $novedad['area'],
            $novedad['detalle'],
            $novedad['hotel'],
            $novedad['nivel_importancia'],
            $novedad['nivel_sugerido'],
            $novedad['requiere_seguimiento'] ?? 0,
            $novedad['seguimiento_estado'] ?? 0,
            $novedad['tipo_seguimiento'] ?? null,
            $novedad['flexkeeping_id'] ?? null,
            $novedad['tipo_novedad'] ?? 'Otro',
            $idNovedad
        );

        header('Location: index.php?route=novedades/list');
        exit;
    }
    // Fin de la función store()


    /**
     * Envía correo de notificación de nueva novedad.
     *
     * Qué hace:
     * - Construye y envía un correo HTML con datos de la novedad.
     * - Incluye si requiere seguimiento y (si aplica) el estado del seguimiento.
     * - Calcula promedio de importancia y añade links a fotos.
     *
     * Parámetros:
     * @param int    $recepcionista_id
     * @param string $area
     * @param string $detalle
     * @param string $hotel
     * @param int    $nivel_importancia
     * @param int    $nivel_sugerido
     * @param int    $requiere_seguimiento 0|1
     * @param int    $seguimiento_estado   0 (N/A) | 1 (Pendiente) | 2 (Cerrada)
     * @param string $tipo_seguimiento
     * @param string $flexkeeping_id
     * @param string $tipo_novedad         Departamento involucrado
     * @param int    $id_novedad
     *
     * @return void
     */
    private function enviarEmail(
        $recepcionista_id,
        $area,
        $detalle,
        $hotel,
        $nivel_importancia,
        $nivel_sugerido,
        $requiere_seguimiento,
        $seguimiento_estado = 0,
        $tipo_seguimiento = null,
        $flexkeeping_id = null,
        $tipo_novedad = 'Otro',
        $id_novedad = null
    ) {
        $pdo = Database::getConnection();
        $model = new Novedad();
        $recepcionista = (new Recepcionista())->buscar($recepcionista_id);

        $mail = new PHPMailer(true);

        try {

            // ===============================
            // NORMALIZAR SEGUIMIENTO
            // ===============================
            $requiere_seguimiento = (int) $requiere_seguimiento;
            if ($requiere_seguimiento !== 1) {
                $requiere_seguimiento = 0;
            }

            $seguimiento_estado = (int) $seguimiento_estado;
            if ($requiere_seguimiento === 0) {
                $seguimiento_estado = 0;
            } else {
                if (!in_array($seguimiento_estado, [1, 2], true)) {
                    $seguimiento_estado = 1; // pendiente por defecto
                }
            }

            $reqSegTxt = ($requiere_seguimiento === 1) ? 'Sí' : 'No';

            $estadoTxt = 'N/A';
            if ($requiere_seguimiento === 1) {
                $estadoTxt = ($seguimiento_estado === 2) ? 'Cerrada' : 'Pendiente';
            }

            // ===============================
            // PROMEDIO IMPORTANCIA
            // ===============================
            $promedio = ($nivel_importancia + $nivel_sugerido) / 2;

            // ===============================
            // ADJUNTOS / FOTOS
            // ===============================
            $archivosHtml = "";
            if ($id_novedad) {
                $archivos = $model->listarArchivos($id_novedad);
                if (!empty($archivos)) {
                    $archivosHtml .= "<div style='margin-top:20px; padding:15px; background:#f8f9fa; border-radius:8px;'>";
                    $archivosHtml .= "<h4 style='margin-top:0; color:#444;'>Archivos Adjuntos / Evidencia:</h4>";
                    $archivosHtml .= "<ul style='list-style:none; padding:0;'>";
                    
                    $fechaDir = date('Y_m_d'); // Asumiendo que es de hoy, si no, buscar en la novedad
                    if ($id_novedad) {
                        $novedadRaw = $model->obtenerPorId($id_novedad);
                        if ($novedadRaw) {
                            $fechaDir = date('Y_m_d', strtotime($novedadRaw['fecha_registro']));
                        }
                    }

                    foreach ($archivos as $a) {
                        $linkFoto = "https://www.atankalama.com/novedades/uploads/{$fechaDir}/novedad_{$id_novedad}/" . $a['archivo'];
                        $archivosHtml .= "<li style='margin-bottom:10px;'>";
                        $archivosHtml .= "📎 <a href='{$linkFoto}' style='color:#0288d1; text-decoration:none; font-weight:bold;'>Ver " . htmlspecialchars($a['archivo']) . "</a>";
                        $archivosHtml .= "</li>";
                    }
                    $archivosHtml .= "</ul></div>";
                }
            }

            // ===============================
            // CONFIGURACIÓN SMTP
            // ===============================
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->Port = SMTP_PORT;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->CharSet = 'UTF-8';

            // ===============================
            // REMITENTE
            // ===============================
            $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);

            // ===============================
            // DESTINATARIOS
            // ===============================
            $mail->addAddress('sistema@atankalama.com', 'Sistema Novedades');

            require_once __DIR__ . '/../models/AccesoUsuario.php';
            $destinatarios = (new AccesoUsuario())->listarDestinatariosNovedades();
            foreach ($destinatarios as $dest) {
                $mail->addBCC($dest['email'], $dest['nombre_completo']);
            }

            // ===============================
            // DEFINIR COLOR SEGÚN IMPORTANCIA
            // ===============================
            if ((int) $nivel_importancia >= 8) {
                $color = '#d32f2f'; // rojo
            } elseif ((int) $nivel_importancia >= 5) {
                $color = '#f57c00'; // naranja
            } else {
                $color = '#388e3c'; // verde
            }

            // ===============================
            // CONTENIDO
            // ===============================
            $fechaHoy = date('Y-m-d');
            $link = "https://www.atankalama.com/novedades/index.php?route=novedades/list&fecha=$fechaHoy";

            $mail->isHTML(true);

            // NUEVO ASUNTO: Novedades / [Departamento Involucrado] / [area involucrada] / [Hotel]
            $mail->Subject = "Novedades / " . ucfirst($tipo_novedad) . " / " . ucfirst($area) . " / " . $hotel;

            $detalleSeguro = nl2br(htmlspecialchars($detalle, ENT_QUOTES, 'UTF-8'));
            $nombreRecep = htmlspecialchars((string) ($recepcionista['nombre'] ?? 'Sin datos'), ENT_QUOTES, 'UTF-8');
            $areaSeguro = htmlspecialchars((string) $area, ENT_QUOTES, 'UTF-8');
            $hotelSeguro = htmlspecialchars((string) $hotel, ENT_QUOTES, 'UTF-8');

            $bloqueEstadoSeguimiento = '';
            if ($requiere_seguimiento === 1) {
                $bloqueEstadoSeguimiento = "<p style='margin:5px 0;'><b>Estado seguimiento:</b> <span style='color:#e65100; font-weight:bold;'>{$estadoTxt}</span></p>";
                if ($tipo_seguimiento) {
                    $bloqueEstadoSeguimiento .= "<p style='margin:5px 0;'><b>Tipo Tarea:</b> " . ucfirst(htmlspecialchars($tipo_seguimiento)) . "</p>";
                }
                if ($flexkeeping_id) {
                    $bloqueEstadoSeguimiento .= "<p style='margin:5px 0;'><b>ID Flexkeeping:</b> <span style='background:#fff3e0; padding:2px 5px; border:1px solid #ffe0b2; border-radius:4px;'> " . htmlspecialchars($flexkeeping_id) . "</span></p>";
                }
            }

            $mail->Body = "
            <div style='font-family:\"Segoe UI\", Tahoma, Geneva, Verdana, sans-serif; max-width:600px; margin:auto; border:1px solid #eee; border-radius:10px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.05);'>
                <div style='background:{$color}; color:white; padding:20px; text-align:center;'>
                    <h2 style='margin:0; font-size:24px;'>Nueva Novedad Registrada</h2>
                    <p style='margin:5px 0 0 0; opacity:0.9;'>{$hotelSeguro} - " . date('d/m/Y H:i') . "</p>
                </div>
                
                <div style='padding:25px; color:#333; line-height:1.6;'>
                    <div style='margin-bottom:20px; padding:15px; background:#fff8e1; border-left:5px solid #ffb300; border-radius:4px;'>
                        <p style='margin:0;'><b>Departamento:</b> " . ucfirst(htmlspecialchars($tipo_novedad)) . "</p>
                        <p style='margin:5px 0 0 0;'><b>Área:</b> {$areaSeguro}</p>
                    </div>

                    <p style='margin-bottom:10px;'><b>Detalle de lo ocurrido:</b></p>
                    <div style='background:#f9f9f9; padding:15px; border-radius:8px; border:1px solid #eee; font-style:italic;'>
                        {$detalleSeguro}
                    </div>

                    <div style='margin-top:25px; display:grid; grid-template-columns: 1fr 1fr; gap:10px;'>
                        <div style='background:#e3f2fd; padding:15px; border-radius:8px; text-align:center;'>
                            <p style='margin:0; font-size:12px; color:#1565c0; text-transform:uppercase;'>Declarada</p>
                            <p style='margin:5px 0 0 0; font-size:20px; font-weight:bold; color:#1565c0;'>{$nivel_importancia}/10</p>
                        </div>
                        <div style='background:#f3e5f5; padding:15px; border-radius:8px; text-align:center;'>
                            <p style='margin:0; font-size:12px; color:#7b1fa2; text-transform:uppercase;'>Sugerida</p>
                            <p style='margin:5px 0 0 0; font-size:20px; font-weight:bold; color:#7b1fa2;'>{$nivel_sugerido}/10</p>
                        </div>
                    </div>

                    <div style='margin-top:15px; background:linear-gradient(to right, #eceff1, #cfd8dc); padding:15px; border-radius:8px; text-align:center;'>
                        <p style='margin:0; font-size:13px; color:#455a64; text-transform:uppercase; font-weight:bold;'>Importancia Promedio</p>
                        <p style='margin:5px 0 0 0; font-size:24px; font-weight:bold; color:#263238;'>" . number_format($promedio, 1) . "/10</p>
                    </div>

                    <div style='margin-top:25px;'>
                        <p style='margin:5px 0;'><b>Recepcionista:</b> {$nombreRecep}</p>
                        <p style='margin:5px 0;'><b>Requiere seguimiento:</b> {$reqSegTxt}</p>
                        {$bloqueEstadoSeguimiento}
                    </div>

                    {$archivosHtml}

                    <div style='margin-top:35px; text-align:center;'>
                        <a href='$link'
                           style='display:inline-block; padding:12px 25px; background:{$color}; color:#fff; text-decoration:none; border-radius:30px; font-weight:bold; box-shadow:0 4px 6px rgba(0,0,0,0.1);'>
                            Ver todas las novedades de hoy
                        </a>
                    </div>
                </div>

                <div style='background:#f5f5f5; padding:20px; text-align:center; color:#888; font-size:12px; border-top:1px solid #eee;'>
                    <p style='margin:0;'>Mensaje automático – Sistema de Novedades Hotel Atankalama</p>
                    <p style='margin:5px 0;'>No responder este correo.</p>
                    <p style='margin:10px 0 0 0;'><small>Desarrollado por Rkm Ingeniería Spa. https://www.rkm.cl</small></p>
                </div>
            </div>
        ";

            // ===============================
            // ENVÍO
            // ===============================
            $mail->send();

        } catch (Exception $e) {
            error_log('Error envío correo: ' . $mail->ErrorInfo);
        }
    }
    // Fin de la función enviarEmail()

// Fin de la función enviarEmail()


    // Fin de la función enviarEmail()

    /**
     * Lista novedades aplicando filtros.
     *
     * Qué hace:
     * - Captura $_GET (fecha, hotel, keyword, solo_pendientes, solo_criticas)
     * - Valida fechas y rango (máx 30 días)
     * - Llama al modelo buscar() con los filtros
     * - Carga la vista novedades_list.php
     */
    public function list()
    {
        // ----------------------------
        // 1) Inputs normalizados
        // ----------------------------
        $fechaInicio = $_GET['fecha_inicio'] ?? ($_GET['fecha'] ?? date('Y-m-d'));
        $fechaFin = $_GET['fecha_fin'] ?? $fechaInicio;

        $hotel = trim($_GET['hotel'] ?? '');
        $keyword = trim($_GET['keyword'] ?? '');
        $soloPendientes = isset($_GET['solo_pendientes']) ? 1 : 0;
        $soloCriticas = isset($_GET['solo_criticas']) ? 1 : 0;
        $tipoNovedad = trim($_GET['tipo_novedad'] ?? '');
        $area = trim($_GET['area'] ?? '');

        // ----------------------------
        // 2) Validación de fechas (backend manda)
        // ----------------------------
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio)) {
            $fechaInicio = date('Y-m-d');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
            $fechaFin = $fechaInicio;
        }

        // Asegurar orden (inicio <= fin)
        if ($fechaInicio > $fechaFin) {
            $tmp = $fechaInicio;
            $fechaInicio = $fechaFin;
            $fechaFin = $tmp;
        }

        // Rango máximo 30 días (regla dura)
        $inicioDT = new DateTime($fechaInicio);
        $finDT = new DateTime($fechaFin);
        $dias = (int) $inicioDT->diff($finDT)->days;

        if ($dias > 30) {
            // Puedes redirigir con error en vez de die
            die('Error: El rango máximo permitido es de 30 días corridos.');
        }

        // ----------------------------
        // 3) Armar filtros para búsqueda unificada
        // ----------------------------
        $filtros = [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'hotel' => $hotel,
            'keyword' => $keyword,
            'solo_pendientes' => $soloPendientes,
            'solo_criticas' => $soloCriticas,
            'tipo_novedad' => $tipoNovedad,
            'area' => $area,
        ];

        // ----------------------------
        // 4) Ejecutar búsqueda
        // ----------------------------
        $model = new Novedad(); // Corregido: sin argumentp $pdo

        $novedades = $model->buscar($filtros);

        // ----------------------------
        // 5) Render
        // ----------------------------
        include __DIR__ . '/../views/novedades/list.php';
    }
    // Fin de la función list()
    // Fin de la función list()



    /**
     * Agrega archivos adjuntos a una novedad existente.
     */
    public function agregarAdjunto()
    {
        $idNovedad = $_POST['novedad_id'] ?? null;

        if (!$idNovedad || empty($_FILES['archivos']['name'][0])) {
            header('Location: index.php?route=novedades/list&status=error');
            exit;
        }


        $model = new Novedad(); // Corregido: sin argumento $pdo


        $novedad = $model->obtenerPorId($idNovedad);

        $uploadDir = __DIR__ . '/../uploads/' . date('Y_m_d', strtotime($novedad['fecha_registro'])) . '/novedad_' . $idNovedad . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ok = false;

        foreach ($_FILES['archivos']['tmp_name'] as $index => $tmpName) {
            if (!is_uploaded_file($tmpName)) {
                continue;
            }

            $nombreOriginal = basename($_FILES['archivos']['name'][$index]);
            $tipo = mime_content_type($tmpName);

            // 🔧 Normalizar nombre
            $nombreLimpio = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nombreOriginal);
            $nombreLimpio = strtolower($nombreLimpio);
            $nombreLimpio = preg_replace('/[^a-z0-9\._-]/', '_', $nombreLimpio);
            $timestamp = date('Ymd_His');
            $ext = pathinfo($nombreLimpio, PATHINFO_EXTENSION);
            $base = pathinfo($nombreLimpio, PATHINFO_FILENAME);
            $nombreSeguro = $base . '_' . $timestamp . '.' . $ext;

            $destino = $uploadDir . $nombreSeguro;
            move_uploaded_file($tmpName, $destino);

            // 🖼️ Convertir JPG/JPEG/PNG → WEBP
            $ext = strtolower($ext);
            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $imagen = null;

                if ($ext === 'jpg' || $ext === 'jpeg') {
                    $imagen = @imagecreatefromjpeg($destino);
                } elseif ($ext === 'png') {
                    $imagen = @imagecreatefrompng($destino);
                    if ($imagen) {
                        // Mantener transparencia si existe
                        imagepalettetotruecolor($imagen);
                        imagealphablending($imagen, true);
                        imagesavealpha($imagen, true);
                    }
                }

                if ($imagen) {
                    $nombreWebp = $base . '_' . $timestamp . '.webp';
                    $rutaWebp = $uploadDir . $nombreWebp;

                    // Convertir con calidad 80
                    imagewebp($imagen, $rutaWebp, 80);
                    imagedestroy($imagen);

                    // Eliminar original
                    unlink($destino);

                    // Actualizar datos
                    $nombreSeguro = $nombreWebp;
                    $tipo = 'image/web';
                }
            }

            $model->guardarArchivo($idNovedad, $nombreSeguro, $tipo);
            $ok = true;
        }

        header('Location: index.php?route=novedades/list&status=' . ($ok ? 'ok' : 'error'));
        exit;
    }
    // Fin de la función agregarAdjunto()

    /**
     * Exporta las novedades a Excel o PDF.
     *
     * @param string $_GET['format'] Formato (excel|pdf)
     */
    public function export()
    {
        // -------------------------------------------------
        // Exporta novedades según filtros actuales
        // -------------------------------------------------

        $format = $_GET['format'] ?? 'excel';

        $fechaInicio = $_GET['fecha_inicio'] ?? $_GET['fecha'] ?? date('Y-m-d');
        $fechaFin = $_GET['fecha_fin'] ?? $fechaInicio;
        $hotel = $_GET['hotel'] ?? '';
        $keyword = $_GET['keyword'] ?? '';

        $model = new Novedad(); // Corregido: sin argumento $pdo

        if (isset($_GET['fecha_inicio'], $_GET['fecha_fin'])) {
            $novedades = $model->buscar([
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'hotel' => $hotel,
                'keyword' => $keyword
            ]);
        } else {
            $novedades = $model->buscar([
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaInicio,
                'hotel' => $hotel,
                'keyword' => $keyword
            ]);
        }

        if ($format === 'excel') {
            $this->exportExcel($novedades);
        }

        if ($format === 'pdf') {
            $this->exportPDF($novedades);
        }

        exit;
    }
    // Fin de la función export()
    // Fin de la función export()


    private function exportExcel(array $novedades)
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="novedades_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://output', 'w');

        // BOM para Excel (acentos)
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($out, [
            'ID',
            'Fecha',
            'Hotel',
            'Área',
            'Departamento',
            'Recepcionista',
            'Detalle',
            'Importancia declarada',
            'Importancia sugerida',
            'Requiere seguimiento',
            'Estado seguimiento',
            'Tipo tarea',
            'ID Flexkeeping',
        ], ',', '"', '\\');

        $estadoMap = [0 => 'N/A', 1 => 'Pendiente', 2 => 'Realizada'];

        foreach ($novedades as $n) {
            $requiere  = (int)($n['requiere_seguimiento'] ?? 0);
            $estadoNum = (int)($n['seguimiento_estado']   ?? 0);
            fputcsv($out, [
                $n['id'],
                date('d-m-Y H:i', strtotime($n['fecha_registro'])),
                $n['hotel'],
                $n['area'],
                $n['tipo_novedad'] ?? '',
                $n['recepcionista_nombre'] ?? '',
                preg_replace('/\s+/', ' ', strip_tags($n['detalle'])),
                $n['nivel_importancia'] ?? '',
                $n['nivel_sugerido']    ?? '',
                $requiere ? 'Sí' : 'No',
                $requiere ? ($estadoMap[$estadoNum] ?? 'Pendiente') : 'N/A',
                $n['tipo_seguimiento']  ?? '',
                $n['flexkeeping_id']    ?? '',
            ], ',', '"', '\\');
        }

        fclose($out);
    }
    // Fin de la función exportExcel()


    private function exportPDF(array $novedades)
    {
        // Limpiar buffers por seguridad
        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: text/html; charset=UTF-8');

        // 👇 SUBE un nivel desde /controllers a /
        require __DIR__ . '/../views/novedades/pdf.php';

        exit;
    }
    // Fin de la función exportPDF()


    /**
     * Muestra el detalle de una novedad y su hilo de seguimiento.
     */
    public function seguimiento()
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            die('ID de novedad inválido');
        }

        // 1. Conexión única
        $pdo = Database::getConnection();

        // 2. Modelos
        require_once __DIR__ . '/../models/Novedad.php';
        require_once __DIR__ . '/../models/NovedadComentario.php';
        require_once __DIR__ . '/../models/Recepcionista.php';

        // 3. Novedad
        $novedadModel = new Novedad(); // Corregido: sin argumento $pdo
        $novedad = $novedadModel->obtenerPorId($id);

        if (!$novedad) {
            die('Novedad no encontrada');
        }

        // 4. Comentarios
        $comentarioModel = new NovedadComentario($pdo);
        $comentarios = $comentarioModel->listarPorNovedad($id);

        // 5. Recepcionistas
        $recepcionistaModel = new Recepcionista(); // Corregido: sin argumento $pdo
        $recepcionistas = $recepcionistaModel->listar();

        // 6. Archivos (EVIDENCIA)
        $archivos = $novedadModel->listarArchivos($id);

        // 7. Enviar a la vista
        include __DIR__ . '/../views/novedades/seguimiento.php';
    }
    // Fin de la función seguimiento()



    /**
     * Agrega un comentario al seguimiento de la novedad.
     *
     * Qué hace:
     * - Guarda comentario en nov_seguimiento_comentarios
     * - Procesa archivos adjuntos si existen
     * - Los archivos se guardan en la carpeta de la novedad original
     */
    public function agregarComentario()
    {
        $novedadId = (int) ($_POST['novedad_id'] ?? 0);
        $autor = trim($_POST['autor'] ?? '');
        $comentario = trim($_POST['comentario'] ?? '');

        if ($novedadId <= 0 || $autor === '' || $comentario === '') {
            header('Location: index.php?route=novedades/seguimiento&id=' . $novedadId . '&error=1');
            exit;
        }

        $pdo = Database::getConnection();

        require_once __DIR__ . '/../models/NovedadComentario.php';
        require_once __DIR__ . '/../models/Novedad.php';

        // 1. Guardar comentario
        $comentarioModel = new NovedadComentario($pdo);
        $comentarioModel->agregar($novedadId, $autor, $comentario);

        // 2. Guardar archivos (si existen)
        if (!empty($_FILES['archivos']['name'][0])) {

            $novedadModel = new Novedad(); // Corregido: sin argumento $pdo
            $novedad = $novedadModel->obtenerPorId($novedadId);

            if ($novedad) {
                $uploadDir = __DIR__ . '/../uploads/' .
                    date('Y_m_d', strtotime($novedad['fecha_registro'])) .
                    '/novedad_' . $novedadId . '/';

                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                foreach ($_FILES['archivos']['tmp_name'] as $i => $tmpName) {
                    if (!is_uploaded_file($tmpName)) {
                        continue;
                    }

                    $original = basename($_FILES['archivos']['name'][$i]);
                    $tipo = mime_content_type($tmpName);

                    // Normalizar nombre original
                    $limpio = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $original);
                    $limpio = strtolower(preg_replace('/[^a-z0-9\._-]/', '_', $limpio));

                    $ext = strtolower(pathinfo($limpio, PATHINFO_EXTENSION));

                    // Nombre temporal real
                    $tmpNombre = 'upload_' . date('Ymd_His') . '_' . $i . '.' . $ext;
                    $rutaTmp = $uploadDir . $tmpNombre;

                    // 1️⃣ Subir archivo original
                    move_uploaded_file($tmpName, $rutaTmp);

                    $nombreFinal = $tmpNombre; // fallback
                    $tipoFinal = $tipo;

                    // 2️⃣ Convertir a WEBP si es imagen
                    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {

                        $imagen = null;

                        if ($ext === 'jpg' || $ext === 'jpeg') {
                            $imagen = @imagecreatefromjpeg($rutaTmp);
                        } elseif ($ext === 'png') {
                            $imagen = @imagecreatefrompng($rutaTmp);
                            if ($imagen) {
                                imagepalettetotruecolor($imagen);
                                imagealphablending($imagen, true);
                                imagesavealpha($imagen, true);
                            }
                        }

                        if ($imagen) {
                            $nombreFinal = 'images_' . date('Ymd_His') . '.webp';
                            $rutaWebp = $uploadDir . $nombreFinal;

                            imagewebp($imagen, $rutaWebp, 80);
                            imagedestroy($imagen);

                            unlink($rutaTmp);

                            $tipoFinal = 'image/webp';
                        }
                    }

                    // 3️⃣ Guardar referencia final en DB
                    $novedadModel->guardarArchivo($novedadId, $nombreFinal, $tipoFinal);
                }


            }
        }

        header('Location: index.php?route=novedades/seguimiento&id=' . $novedadId);
        exit;
    }
    // Fin de la función agregarComentario()



    /**
     * Cierra el seguimiento de una novedad.
     *
     * Qué hace:
     * - Agrega comentario final con prefijo "✔ CIERRE DE TAREA"
     * - Actualiza estado en nov_novedades
     * - Redirige con flag cerrada=1
     */
    public function cerrarSeguimiento()
    {
        $novedadId = (int) ($_POST['novedad_id'] ?? 0);
        $autor = trim($_POST['autor'] ?? '');
        $comentario = trim($_POST['comentario'] ?? '');

        // Validaciones duras
        if ($novedadId <= 0 || $autor === '' || $comentario === '') {
            header('Location: index.php?route=novedades/seguimiento&id=' . $novedadId . '&error=cierre');
            exit;
        }

        $pdo = Database::getConnection();

        require_once __DIR__ . '/../models/Novedad.php';
        require_once __DIR__ . '/../models/NovedadComentario.php';

        $novedadModel = new Novedad(); // Corregido: sin argumento $pdo
        $comentarioModel = new NovedadComentario($pdo);

        $novedad = $novedadModel->obtenerPorId($novedadId);

        if (!$novedad || (int) $novedad['seguimiento_estado'] !== 1) {
            die('La tarea ya está cerrada o no existe');
        }

        // 1️⃣ Guardar comentario final
        $comentarioModel->agregar(
            $novedadId,
            $autor,
            '✔ CIERRE DE TAREA: ' . $comentario
        );

        // 2️⃣ Marcar como cerrada
        $novedadModel->cerrarSeguimiento($novedadId);

        header('Location: index.php?route=novedades/seguimiento&id=' . $novedadId . '&cerrada=1');
        exit;
    }
    // Fin de la función cerrarSeguimiento()

}