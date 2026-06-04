<?php
/**
 * Servicio de Gestión de Imágenes para Espacios.
 *
 * Maneja la subida, conversión a WebP y almacenamiento de fotos
 * principales y de galería para los espacios arrendables.
 *
 * @package App\Services
 */
class SpaceUploadService
{
    /** @var string Carpeta base de uploads para espacios */
    private $basePath;

    /** @var array Tipos de imágenes permitidos */
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    /** @var int Tamaño máximo (2MB) */
    private $maxSize = 2097152;

    /**
     * Constructor. Inicializa la ruta base.
     */
    public function __construct()
    {
        $this->basePath = PUBLIC_PATH . '/uploads/spaces';
        if (!is_dir($this->basePath)) {
            mkdir($this->basePath, 0755, true);
        }
    }

    /**
     * Sube y procesa una imagen para un espacio.
     * 
     * @param array $file    Elemento de $_FILES
     * @param int   $spaceId ID del espacio asociado
     * @param bool  $isMain  Indica si es la imagen principal
     * @return string|null   Ruta relativa guardada o null en error
     */
    public function upload($file, $spaceId, $isMain = false)
    {
        // Validaciones básicas
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if ($file['size'] > $this->maxSize) {
            return null;
        }

        if (!in_array($file['type'], $this->allowedTypes)) {
            return null;
        }

        // Definir nombre único
        $prefix = $isMain ? 'main' : 'gallery';
        $uniqueBase = $spaceId . '_' . $prefix . '_' . date('His') . '_' . uniqid();

        // Convertir a WebP (si está soportado)
        if (ImageConverterService::isSupported()) {
            $converter = new ImageConverterService();
            $newFilename = $converter->convertToWebp($file['tmp_name'], $this->basePath, $uniqueBase);
            if ($newFilename) {
                return '/public/uploads/spaces/' . $newFilename;
            }
        }

        // Fallback: Mover archivo original si no hay conversión
        $info = pathinfo($file['name']);
        $newFilename = $uniqueBase . '.' . ($info['extension'] ?? 'jpg');
        if (move_uploaded_file($file['tmp_name'], $this->basePath . '/' . $newFilename)) {
            return '/public/uploads/spaces/' . $newFilename;
        }

        return null;
    }

    /**
     * Elimina un archivo físico del servidor.
     * 
     * @param string $relativePath Ruta relativa guardada en BD
     * @return bool
     */
    public function deleteFile($relativePath)
    {
        if (empty($relativePath)) return false;
        $fullPath = APP_ROOT . $relativePath;
        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }
}
