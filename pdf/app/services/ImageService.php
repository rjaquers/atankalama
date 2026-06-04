<?php
/**
 * ImageService — convierte cualquier imagen a WebP y la guarda en el servidor.
 * PHP 7.4–8.2 compatible. Requiere extensión GD.
 */
class ImageService
{
    /**
     * Recibe un $_FILES entry y lo guarda como WebP.
     *
     * @param  array  $file    Entrada de $_FILES (con error, tmp_name, size, name)
     * @param  string $subdir  Subdirectorio dentro de UPLOAD_PATH (ej: 'mensajes', 'tareas/15')
     * @return string|false    Ruta relativa pública (ej: 'uploads/chat/mensajes/img_xxx.webp') o false en error
     */
    public function saveAsWebp(array $file, string $subdir = '')
    {
        if (!function_exists('imagewebp')) {
            app_log('ImageService: extensión GD/WebP no disponible');
            return false;
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return false;
        }

        if (($file['size'] ?? 0) > (UPLOAD_MAX_MB * 1024 * 1024)) {
            app_log('ImageService: archivo demasiado grande (' . $file['size'] . ' bytes)');
            return false;
        }

        $ext     = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        if (!in_array($ext, $allowed, true)) {
            app_log("ImageService: extensión no permitida: $ext");
            return false;
        }

        $image = $this->createFromFile($file['tmp_name'], $ext);
        if (!$image) {
            app_log("ImageService: no se pudo crear imagen desde {$file['tmp_name']}");
            return false;
        }

        $dir = UPLOAD_PATH . ($subdir !== '' ? '/' . trim($subdir, '/') : '');
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            app_log("ImageService: no se pudo crear directorio $dir");
            imagedestroy($image);
            return false;
        }

        $filename = 'img_' . uniqid('', true) . '.webp';
        $fullPath = $dir . '/' . $filename;

        $saved = imagewebp($image, $fullPath, WEBP_QUALITY);
        imagedestroy($image);

        if (!$saved) {
            app_log("ImageService: error al guardar WebP en $fullPath");
            return false;
        }

        $relativeSub = $subdir !== '' ? '/' . trim($subdir, '/') : '';
        return 'uploads/chat' . $relativeSub . '/' . $filename;
    }

    /**
     * Guarda múltiples archivos de un input multiple.
     *
     * @param  array  $filesArray  Resultado de reorganizar $_FILES['campo'] para múltiples
     * @param  string $subdir
     * @return array  Rutas guardadas
     */
    public function saveMultiple(array $filesArray, string $subdir = ''): array
    {
        $rutas = [];
        $count = count($filesArray['name'] ?? []);
        for ($i = 0; $i < $count; $i++) {
            $file = [
                'name'     => $filesArray['name'][$i],
                'tmp_name' => $filesArray['tmp_name'][$i],
                'error'    => $filesArray['error'][$i],
                'size'     => $filesArray['size'][$i],
            ];
            $ruta = $this->saveAsWebp($file, $subdir);
            if ($ruta !== false) {
                $rutas[] = $ruta;
            }
        }
        return $rutas;
    }

    private function createFromFile(string $tmpPath, string $ext)
    {
        switch ($ext) {
            case 'jpg':
            case 'jpeg': return @imagecreatefromjpeg($tmpPath);
            case 'png':  $img = @imagecreatefrompng($tmpPath);
                         if ($img) { imagealphablending($img, true); imagesavealpha($img, true); }
                         return $img;
            case 'gif':  return @imagecreatefromgif($tmpPath);
            case 'bmp':  return @imagecreatefrombmp($tmpPath);
            case 'webp': return @imagecreatefromwebp($tmpPath);
            default:     return false;
        }
    }
}
