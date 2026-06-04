<?php
/**
 * Servicio de Conversión de Imágenes.
 *
 * Convierte archivos JPG y PNG a formato WebP para optimizar el almacenamiento
 * y la carga de la aplicación. Utiliza la extensión GD de PHP.
 *
 * @package App\Services
 */
class ImageConverterService
{
    /** @var int Calidad de la conversión WebP (0-100) */
    private $quality;

    /**
     * Constructor. Inicializa la calidad desde .env o usa 80 por defecto.
     */
    public function __construct()
    {
        $this->quality = (int)($_ENV['WEBP_QUALITY'] ?? 80);
    }

    /**
     * Convierte una imagen a WebP.
     * 
     * @param string $sourcePath Ruta temporal del archivo original
     * @param string $destDir    Directorio de destino
     * @param string $filename   Nombre base del archivo (sin extensión)
     * @return string|bool       Nuevo nombre del archivo convertido o false en error
     */
    public static function isSupported()
    {
        return function_exists('imagewebp') && function_exists('imagecreatefromjpeg') && function_exists('imagecreatefrompng');
    }

    /**
     * Convierte una imagen a WebP.
     * 
     * @param string $sourcePath Ruta temporal del archivo original
     * @param string $destDir    Directorio de destino
     * @param string $filename   Nombre base del archivo (sin extensión)
     * @return string|bool       Nuevo nombre del archivo convertido o false en error
     */
    public function convertToWebp($sourcePath, $destDir, $filename)
    {
        if (!file_exists($sourcePath)) return false;

        $info = getimagesize($sourcePath);
        if (!$info) return false;

        $mime = $info['mime'];
        $image = null;

        // Crear recurso de imagen según el tipo original
        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                // Preservar transparencia para PNG si es necesario (opcional)
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }

        if (!$image) return false;

        // Generar nuevo nombre .webp
        $newFilename = $filename . '.webp';
        $destPath = $destDir . '/' . $newFilename;

        // Guardar como WebP
        if (function_exists('imagewebp')) {
            if (imagewebp($image, $destPath, $this->quality)) {
                imagedestroy($image);
                return $newFilename;
            }
        }

        imagedestroy($image);
        return false;
    }
}
