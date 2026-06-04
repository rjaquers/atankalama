<?php
/**
 * Servicio de Gestión de Archivos (Upload).
 *
 * Gestiona la subida de adjuntos (PDF, JPG, PNG, WebP) vinculados a contratos.
 * Implementa validación, organización por carpetas {company_id}/{año-mes}/ 
 * y conversión automática de imágenes a WebP.
 *
 * @package App\Services
 */
class FileUploadService
{
    /** @var string Carpeta base de uploads */
    private $basePath;

    /** @var array Tipos de archivos permitidos */
    private $allowedTypes = [
        'application/pdf', 
        'image/jpeg', 
        'image/png', 
        'image/webp'
    ];

    /** @var int Tamaño máximo (2MB) */
    private $maxSize = 2097152;

    /**
     * Constructor. Configura la ruta base.
     */
    public function __construct()
    {
        $this->basePath = UPLOAD_BASE_PATH;
    }

    /**
     * Sube y procesa un archivo adjunto para un contrato.
     * 
     * @param array  $file       Elemento de $_FILES
     * @param int    $contractId ID del contrato
     * @param int    $companyId  ID de la empresa (para la estructura de carpetas)
     * @param string $category   Categoría del adjunto (e.g., 'firma', 'anexo', 'identidad')
     * @param int    $userId     ID del usuario que sube el archivo
     * @return array             ['status' => bool, 'message' => string, 'data' => array]
     */
    public function uploadAttachment($file, $contractId, $companyId, $category, $userId, $paymentId = null)
    {
        // 1. Validaciones básicas
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['status' => false, 'message' => 'Parámetros de archivo inválidos.'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['status' => false, 'message' => 'Error al subir el archivo (Código: ' . $file['error'] . ').'];
        }

        if ($file['size'] > $this->maxSize) {
            return ['status' => false, 'message' => 'El archivo excede el tamaño máximo permitido (2MB).'];
        }

        if (!in_array($file['type'], $this->allowedTypes)) {
            return ['status' => false, 'message' => 'Tipo de archivo no permitido (solo PDF, JPG, PNG).'];
        }

        // 2. Definir ruta de destino: {company_id}/{año-mes}/
        $subDir = $companyId . '/' . date('Y-m');
        $fullPathDir = $this->basePath . '/' . $subDir;

        if (!is_dir($fullPathDir)) {
            mkdir($fullPathDir, 0755, true);
        }

        // 3. Procesar archivo: Generar nombre único y corto
        $info = pathinfo($file['name']);
        $baseName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $info['filename']);
        $shortName = substr($baseName, 0, 20); // Limitar a 20 caracteres
        $uniqueBase = date('His') . '_' . $shortName;

        $isImage = in_array($file['type'], ['image/jpeg', 'image/png']);
        
        if ($isImage) {
            if (!ImageConverterService::isSupported()) {
                return ['status' => false, 'message' => 'El servidor no soporta la conversión obligatoria a WebP (GD missing).'];
            }

            // Convertir a WebP (SI O SI)
            $converter = new ImageConverterService();
            $newFilename = $converter->convertToWebp($file['tmp_name'], $fullPathDir, $uniqueBase);
            
            if ($newFilename) {
                $finalPath = '/uploads/contracts/' . $subDir . '/' . $newFilename;
                $fileType = 'image/webp';
            } else {
                return ['status' => false, 'message' => 'Error crítico al convertir la imagen a formato WebP.'];
            }
        } else {
            // Guardar original (PDF u otros no-imágenes permitidos)
            $newFilename = $uniqueBase . '.' . $info['extension'];
            $destPath = $fullPathDir . '/' . $newFilename;
            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                return ['status' => false, 'message' => 'No se pudo guardar el archivo en el servidor.'];
            }
            $finalPath = '/uploads/contracts/' . $subDir . '/' . $newFilename;
            $fileType = $file['type'];
        }

        // 4. Guardar registro en base de datos
        $attachmentModel = new ContractAttachmentModel();
        $attachmentData = [
            'contract_id'   => $contractId,
            'filename'      => basename($finalPath),
            'original_name' => $file['name'],
            'mime_type'     => $fileType,
            'file_size'     => $file['size'],
            'file_path'     => $finalPath,
            'category'      => $category,
            'description'   => null,
            'payment_id'    => $paymentId
        ];

        $attachmentId = $attachmentModel->create($attachmentData, $userId);

        if ($attachmentId) {
            return [
                'status'  => true, 
                'message' => 'Archivo subido correctamente.', 
                'data'    => array_merge(['id' => $attachmentId], $attachmentData)
            ];
        } else {
            // TODO: Podríamos borrar el archivo físico si falla la BD
            return ['status' => false, 'message' => 'Error al registrar el archivo en la base de datos.'];
        }
    }
}
