<?php
namespace App\Services;

class PhotoUploadService
{
    const MAX_SIZE  = 3 * 1024 * 1024; // 3 MB por archivo
    const MAX_FILES = 5;
    const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    const UPLOAD_DIR = __DIR__ . '/../../public/uploads/fotos/';
    const WEBP_QUALITY = 80;

    /**
     * Procesa y guarda los archivos subidos para una pregunta de tipo foto.
     *
     * @param array  $fileData   Entrada de $_FILES para el input del pregunta (puede ser multi-file)
     * @param int    $evalId     ID de la evaluación
     * @param int    $preguntaId ID de la pregunta
     * @return array             Rutas relativas de los archivos guardados (relativas a public/)
     * @throws \RuntimeException Si hay errores de validación o almacenamiento
     */
    public function upload(array $fileData, int $evalId, int $preguntaId): array
    {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('La extensión GD de PHP es requerida para procesar imágenes.');
        }

        // Normalizar la estructura de $_FILES para múltiples archivos
        // $_FILES['fotos_X']['name'] puede ser string (un archivo) o array (múltiples)
        $files = $this->normalizeFiles($fileData);

        if (empty($files)) {
            return [];
        }

        if (count($files) > self::MAX_FILES) {
            throw new \RuntimeException('Se permiten máximo ' . self::MAX_FILES . ' fotos por pregunta.');
        }

        $paths = [];
        foreach ($files as $file) {
            if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new \RuntimeException('Error al subir archivo: código ' . $file['error']);
            }
            if ($file['size'] > self::MAX_SIZE) {
                throw new \RuntimeException('El archivo "' . htmlspecialchars($file['name']) . '" supera el límite de 3MB.');
            }

            // Validar MIME real con finfo (no confiar en el tipo del cliente)
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $realMime = $finfo->file($file['tmp_name']);

            if (!in_array($realMime, self::ALLOWED_TYPES, true)) {
                throw new \RuntimeException('Tipo de archivo no permitido: "' . htmlspecialchars($file['name']) . '". Solo se aceptan JPG, PNG o WebP.');
            }

            $destRelative = 'uploads/fotos/' . $this->generateName($evalId, $preguntaId);
            $destAbsolute = __DIR__ . '/../../public/' . $destRelative;

            $this->convertToWebp($file['tmp_name'], $realMime, $destAbsolute);

            $paths[] = $destRelative;
        }

        return $paths;
    }

    /**
     * Convierte la imagen a WebP y la guarda en el destino.
     */
    private function convertToWebp(string $tmpPath, string $mime, string $destPath): void
    {
        switch ($mime) {
            case 'image/jpeg':
                $img = imagecreatefromjpeg($tmpPath);
                break;
            case 'image/png':
                $img = imagecreatefrompng($tmpPath);
                // Preservar canal alfa
                imagealphablending($img, true);
                imagesavealpha($img, true);
                break;
            case 'image/webp':
                $img = imagecreatefromwebp($tmpPath);
                break;
            default:
                throw new \RuntimeException('Tipo de imagen no soportado para conversión.');
        }

        if ($img === false) {
            throw new \RuntimeException('No se pudo procesar la imagen.');
        }

        if (!imagewebp($img, $destPath, self::WEBP_QUALITY)) {
            imagedestroy($img);
            throw new \RuntimeException('No se pudo guardar la imagen en formato WebP.');
        }

        imagedestroy($img);
    }

    /**
     * Genera un nombre de archivo único.
     */
    private function generateName(int $evalId, int $preguntaId): string
    {
        return sprintf(
            'eval_%d_q_%d_%d_%s.webp',
            $evalId,
            $preguntaId,
            time(),
            bin2hex(random_bytes(4))
        );
    }

    /**
     * Normaliza la estructura de $_FILES para un input multiple.
     * Convierte el formato array-of-arrays a array-of-files.
     */
    private function normalizeFiles(array $fileData): array
    {
        // Si 'name' es string, es un solo archivo — envolver en array
        if (is_string($fileData['name'])) {
            return [$fileData];
        }

        // Si 'name' es array, son múltiples archivos
        $files = [];
        foreach ($fileData['name'] as $i => $name) {
            $files[] = [
                'name'     => $name,
                'type'     => $fileData['type'][$i],
                'tmp_name' => $fileData['tmp_name'][$i],
                'error'    => $fileData['error'][$i],
                'size'     => $fileData['size'][$i],
            ];
        }
        return $files;
    }
}
