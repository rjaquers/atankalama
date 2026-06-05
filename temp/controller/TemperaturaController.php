<?php

require_once __DIR__.'/../___conec6.php';
require_once __DIR__.'/../models/Temperatura.php';
require_once __DIR__.'/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class TemperaturaController
{
    private $model;

    public function __construct()
    {
        global $pdo;
        $this->model = new Temperatura($pdo);
    }

    /** Formulario principal */
    public function form()
    {
        // 🔒 Evitar caché del navegador
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: 0');

        include __DIR__.'/../views/temperatura_form.php';
    }

    /** Listado de registros */
    public function listar()
    {
        $fecha = $_GET['fecha'] ?? null;
        $registros = $this->model->listarPorDia($fecha);
        include __DIR__ . '/../views/temperatura_list.php';
    }

    /** Guardar registro */
    public function guardar()
    {
        $nombre = trim($_POST['nombre']);
        $hotel = $_POST['hotel'] ?? 'Sin definir';
        $temperatura = (float)$_POST['temperatura'];
        $fotosGuardadas = [];

        $carpeta = __DIR__.'/../uploads/'.date('Y_m_d').'/';
        if (! is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        foreach ($_FILES['fotos']['tmp_name'] as $i => $tmpName) {
            if (! empty($tmpName)) {
                $ext = pathinfo($_FILES['fotos']['name'][$i], PATHINFO_EXTENSION);
                $nuevoNombre = uniqid('temp_').'.webp';
                $rutaDestino = $carpeta.$nuevoNombre;

                $this->convertirAWebp($tmpName, $rutaDestino, $ext);
                $fotosGuardadas[] = 'uploads/'.date('Y_m_d').'/'.$nuevoNombre;
            }
        }

        $id = $this->model->guardar($nombre, $hotel, $temperatura, $fotosGuardadas);

        $this->notificarNuevoRegistro($id, $nombre, $hotel, $temperatura);

        header('Location: index.php?success=1');
        exit;
    }


    /**
     * Convierte una imagen a formato WebP y ajusta su tamaño máximo.
     *
     * Esta función realiza varias tareas automáticas:
     * 1. Carga la imagen según su tipo original (JPG, PNG, WEBP).
     * 2. Si el ancho es mayor a 800 píxeles, la reduce proporcionalmente.
     * 3. Guarda la nueva imagen en formato WebP con calidad 80.
     *
     * Parámetros:
     * @param string $rutaOrigen  Ruta temporal del archivo subido.
     * @param string $rutaDestino Ruta donde se guardará el archivo convertido.
     * @param string $extension   Extensión original del archivo (jpg, png, webp).
     *
     * Retorna:
     * - true si el proceso fue exitoso.
     * - false si el archivo no pudo procesarse o no es compatible.
     */
    private function convertirAWebp($rutaOrigen, $rutaDestino, $extension)
    {
        // 1️⃣ Crear imagen fuente según el tipo
        switch (strtolower($extension)) {
            case 'jpeg':
            case 'jpg':
                $img = @imagecreatefromjpeg($rutaOrigen);
                break;
            case 'png':
                $img = @imagecreatefrompng($rutaOrigen);
                break;
            case 'webp':
                $img = @imagecreatefromwebp($rutaOrigen);
                break;
            default:
                return false; // formato no soportado
        }

        if (!$img) return false;

        // 2️⃣ Obtener dimensiones originales
        $anchoOriginal = imagesx($img);
        $altoOriginal  = imagesy($img);

        // 3️⃣ Redimensionar si excede 800 px de ancho
        $anchoMax = 800;
        if ($anchoOriginal > $anchoMax) {
            $factor = $anchoMax / $anchoOriginal;
            $nuevoAncho = $anchoMax;
            $nuevoAlto  = intval($altoOriginal * $factor);

            // Crear una nueva imagen redimensionada
            $imgRedimensionada = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

            // Copiar y escalar la imagen original
            imagecopyresampled($imgRedimensionada, $img, 0, 0, 0, 0,
                               $nuevoAncho, $nuevoAlto, $anchoOriginal, $altoOriginal);

            // Liberar la original y reemplazar
            imagedestroy($img);
            $img = $imgRedimensionada;
        }

        // 4️⃣ Guardar en formato WebP optimizado (calidad 80)
        imagewebp($img, $rutaDestino, 80);

        // 5️⃣ Liberar recursos de memoria
        imagedestroy($img);

        return true;
    }



    /** Elimina un registro y sus fotos del disco */
    public function eliminar()
    {
        header('Content-Type: application/json');
        $id = intval($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
            exit;
        }
        $ok = $this->model->eliminar($id);
        echo json_encode(['ok' => $ok]);
        exit;
    }

    /**
     * Genera un PDF con los datos y fotos de un registro individual.
     * Usa Dompdf para renderizar el contenido HTML.
     * Permite dos modos:
     *  - ?modo=ver → abre el PDF inline (por iframe o modal)
     *  - ?modo=descargar → fuerza la descarga del archivo
     */
    public function exportarPDF()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        if (!isset($_GET['id'])) {
            die('Falta el ID.');
        }

        $id = intval($_GET['id']);
        global $pdo;
        $modelo = new Temperatura($pdo);
        $registro = $modelo->obtenerPorId($id);

        if (!$registro) {
            die('Registro no encontrado.');
        }

        // Renderizar vista HTML
        ob_start();
        include __DIR__ . '/../views/temperatura_pdf.php';
        $html = ob_get_clean();

        // Cargar Dompdf
        require_once __DIR__ . '/../vendor/autoload.php';
        $dompdf = new Dompdf\Dompdf([
                                        'isHtml5ParserEnabled' => true,
                                        'isRemoteEnabled' => true
                                    ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // ✅ Detección del modo (ver o descargar)
        $modo = $_GET['modo'] ?? 'ver';
        $nombreArchivo = 'temperatura_' . $id . '.pdf';

        header('Content-Type: application/pdf');

        if ($modo === 'descargar') {
            header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        } else {
            header('Content-Disposition: inline; filename="' . $nombreArchivo . '"');
        }

        echo $dompdf->output();
        exit;
    }




    private function notificarNuevoRegistro($id, $nombre, $hotel, $temperatura)
    {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = 'tls';
            $mail->Port       = SMTP_PORT;

            $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
            $mail->addAddress('rjaquers@gmail.com', 'Rodrigo');

            $fechaHora = date('d/m/Y H:i:s');
            $linkLista = "https://www.atankalama.com/temp/index.php?route=listar&fecha=" . date('Y-m-d');

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = "Nuevo registro de temperatura – $hotel ($temperatura°C)";
            $mail->Body = "
<!DOCTYPE html>
<html lang='es'>
<head><meta charset='UTF-8'></head>
<body style='font-family:sans-serif;background:#f8f9fa;padding:20px;'>
<div style='max-width:480px;margin:auto;background:#fff;border-radius:12px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,.08);'>
  <h2 style='color:#0d6efd;margin-top:0;'>Nuevo registro de temperatura</h2>
  <table style='width:100%;border-collapse:collapse;font-size:15px;'>
    <tr><td style='padding:8px 0;color:#6c757d;'>Encargado</td><td style='padding:8px 0;font-weight:600;'>" . htmlspecialchars($nombre) . "</td></tr>
    <tr><td style='padding:8px 0;color:#6c757d;'>Lugar</td><td style='padding:8px 0;font-weight:600;'>" . htmlspecialchars($hotel) . "</td></tr>
    <tr><td style='padding:8px 0;color:#6c757d;'>Temperatura</td><td style='padding:8px 0;font-weight:600;color:#0d6efd;font-size:18px;'>{$temperatura}°C</td></tr>
    <tr><td style='padding:8px 0;color:#6c757d;'>Fecha / Hora</td><td style='padding:8px 0;'>{$fechaHora}</td></tr>
  </table>
  <a href='{$linkLista}' style='display:inline-block;margin-top:16px;padding:10px 20px;background:#0d6efd;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;'>Ver registros del día</a>
</div>
</body>
</html>";

            $mail->send();
        } catch (\Exception $e) {
            // El fallo de notificación no interrumpe el flujo principal
        }
    }

}

?>
