<?php
class AdjuntoController extends Controller
{
    private int $usuario_id;
    private AdjuntoModel $modelo;
    private TarjetaModel $tarjetaModelo;
    private TableroModel $tableroModelo;

    public function __construct()
    {
        global $email;
        $this->modelo        = new AdjuntoModel();
        $this->tarjetaModelo = new TarjetaModel();
        $this->tableroModelo = new TableroModel();
        $uid = $this->tableroModelo->usuarioId($email ?? '');
        if (!$uid) $this->json(['ok' => false, 'error' => 'No autenticado'], 401);
        $this->usuario_id = $uid;
    }

    // POST /adjunto/subir  (multipart/form-data: tarjeta_id, archivo)
    public function subir(): void
    {
        $tarjeta_id = (int)($_POST['tarjeta_id'] ?? 0);
        if (!$tarjeta_id) $this->json(['ok' => false, 'error' => 'Sin tarjeta'], 400);

        $tablero_id = $this->tarjetaModelo->tableroDeTarjeta($tarjeta_id);
        if (!$tablero_id || !$this->tableroModelo->puedeEditar($tablero_id, $this->usuario_id)) {
            $this->json(['ok' => false, 'error' => 'Sin permiso'], 403);
        }

        if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['ok' => false, 'error' => 'Error en la carga del archivo'], 400);
        }

        $file = $_FILES['archivo'];
        $mime = mime_content_type($file['tmp_name']);
        $isImage = str_starts_with($mime, 'image/');
        $isPdf   = $mime === 'application/pdf';

        if (!$isImage && !$isPdf) {
            $this->json(['ok' => false, 'error' => 'Solo se permiten imágenes y PDF'], 400);
        }
        if ($file['size'] > 10 * 1024 * 1024) {
            $this->json(['ok' => false, 'error' => 'El archivo supera el límite de 10 MB'], 400);
        }

        $uuid   = bin2hex(random_bytes(16));
        $subdir = 'uploads/trello/' . date('Y/m');
        $absDir = APP_ROOT . '/public/' . $subdir;
        if (!is_dir($absDir)) mkdir($absDir, 0755, true);

        if ($isImage) {
            $filename = $uuid . '.webp';
            $dest     = $absDir . '/' . $filename;
            $ruta     = $subdir . '/' . $filename;
            if (!$this->toWebp($file['tmp_name'], $dest)) {
                move_uploaded_file($file['tmp_name'], $dest);
            }
            $tipo = 'imagen';
        } else {
            $filename = $uuid . '.pdf';
            $dest     = $absDir . '/' . $filename;
            $ruta     = $subdir . '/' . $filename;
            move_uploaded_file($file['tmp_name'], $dest);
            $tipo = 'pdf';
        }

        $id = $this->modelo->crear(
            $tarjeta_id, $file['name'], $ruta, $tipo, (int)$file['size'], $this->usuario_id
        );

        $this->json([
            'ok'             => true,
            'id'             => $id,
            'ruta'           => $ruta,
            'tipo'           => $tipo,
            'nombre_original'=> $file['name'],
            'tamanio'        => $file['size'],
        ]);
    }

    // POST /adjunto/eliminar  { id }
    public function eliminar(): void
    {
        $raw = file_get_contents('php://input');
        $d   = $raw ? (json_decode($raw, true) ?? []) : $_POST;
        $id  = (int)($d['id'] ?? 0);
        if (!$id) $this->json(['ok' => false], 400);

        $tarjeta_id = $this->modelo->tarjetaDe($id);
        if (!$tarjeta_id) $this->json(['ok' => false, 'error' => 'No encontrado'], 404);

        $tablero_id = $this->tarjetaModelo->tableroDeTarjeta($tarjeta_id);
        if (!$tablero_id || !$this->tableroModelo->puedeEditar($tablero_id, $this->usuario_id)) {
            $this->json(['ok' => false, 'error' => 'Sin permiso'], 403);
        }

        $ruta = $this->modelo->eliminar($id);
        if ($ruta) {
            $abs = APP_ROOT . '/public/' . $ruta;
            if (file_exists($abs)) unlink($abs);
        }
        $this->json(['ok' => true]);
    }

    private function toWebp(string $src, string $dest): bool
    {
        if (!function_exists('imagewebp')) return false;
        $info = @getimagesize($src);
        if (!$info) return false;

        $img = match($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($src),
            IMAGETYPE_PNG  => @imagecreatefrompng($src),
            IMAGETYPE_WEBP => @imagecreatefromwebp($src),
            IMAGETYPE_GIF  => @imagecreatefromgif($src),
            default        => false,
        };
        if (!$img) return false;

        $w = imagesx($img);
        if ($w > 1920) {
            $h   = imagesy($img);
            $new = imagescale($img, 1920, (int)($h * 1920 / $w));
            imagedestroy($img);
            $img = $new;
        }
        $ok = imagewebp($img, $dest, 82);
        imagedestroy($img);
        return $ok;
    }
}
