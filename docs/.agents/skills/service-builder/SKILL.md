---
name: service-builder
description: Crea servicios PHP para el Sistema de Contratos Atankalama. Define la capa de lógica de negocio, cálculos de tiers, pagos, generación PDF, upload de archivos con conversión WebP, y alertas.
---

# ⚙️ Service Builder — Sistema de Contratos Atankalama

## Contexto

- **Proyecto:** Sistema de Contratos – Hotel Atankalama
- **Stack:** PHP 7.4+, MySQLi
- **Ubicación:** `app/services/{Nombre}Service.php`
- **Propósito:** Lógica de negocio que NO pertenece ni al Model ni al Controller

---

## 📐 Convenciones obligatorias

### 1. Estructura base

```php
<!--
  = Proyecto: Sistema de Contratos Atankalama =
  = Autor: Rodrigo Jaque Escobar              =
  = Contacto: rjaquers@gmail.com              =

-->
<?php
/**
 * ===================================================
 * Servicio: {Nombre}Service
 * Proyecto: Hotel Atankalama – Sistema de Contratos
 * PHP: 7.4 compatible
 * ===================================================
 *
 * Responsabilidad:
 * {Descripción clara de qué hace este servicio}
 */
class {Nombre}Service
{
    // Métodos aquí
}
```

### 2. Cuándo usar un Service

| Usar Service | Ejemplo |
|---|---|
| Lógica de negocio compleja | Calcular precio según tiers |
| Operaciones multi-modelo | Crear contrato + hoteles + servicios |
| Integraciones externas | Enviar email, generar PDF |
| Procesos de archivo | Upload, conversión WebP |
| Cálculos financieros | Saldo pendiente, cuenta corriente |

### 3. PHPDoc

Mismas reglas que los modelos:
- Encabezado de clase con bloque `===`
- Cada método con `@param`, `@return`, sección "Qué hace:"
- Cierre con `// Fin de la función`

---

## 🔧 Services del sistema

### ContractService
Lógica de negocio de contratos:
- Crear contrato completo (contrato + hoteles + servicios + tiers)
- Calcular precio según tier aplicable
- Cambiar estado de contrato
- Generar código único (CTR-2026-001)

### PaymentService
Gestión de pagos:
- Registrar pago parcial
- Calcular saldo pendiente
- Obtener cuenta corriente de un contrato
- Verificar estado de pagos (al día / con deuda)

### FileUploadService
Upload de archivos:
- Validar tipo y tamaño
- Convertir JPG/PNG a WebP (via ImageConverterService)
- Crear estructura de carpetas: `{company_id}/{año-mes}/`
- Generar nombre único de archivo
- Guardar registro en doc_contract_attachments

### ImageConverterService
Conversión de imágenes:
- JPG → WebP (calidad configurable desde .env WEBP_QUALITY)
- PNG → WebP
- Eliminar archivo original tras conversión

### PdfGeneratorService
Generación de documentos:
- Cargar plantilla HTML desde doc_contract_templates
- Reemplazar variables ({{empresa_nombre}}, {{contrato_monto}}, etc.)
- Generar PDF con Dompdf
- Guardar en disco y actualizar ruta en doc_contracts

### AlertService
Alertas de vencimiento:
- Consultar contratos próximos a vencer
- Consultar pagos pendientes
- Enviar emails via MailService
- Registrar alertas en doc_notifications

### ContractReportService
Reportes y estadísticas:
- KPIs del dashboard (vigentes, por vencer, vencidos, huéspedes)
- Datos para gráficos (contratos por mes, pagos vs deuda)
- Exportación Excel/PDF

---

## 📋 Patrones de implementación

### Patrón: Operación multi-modelo (transacción)

```php
public function createFullContract($data, $hotelIds, $serviceIds, $tiers, $userId)
{
    // Obtener conexión para transacción
    $db = new Database();
    $conn = $db->connect();
    $conn->begin_transaction();

    try {
        // 1. Crear contrato
        $contractModel = new ContractModel();
        $contractId = $contractModel->create($data);

        // 2. Asociar hoteles
        foreach ($hotelIds as $hotelId) { /* INSERT doc_contract_hotels */ }

        // 3. Asociar servicios
        foreach ($serviceIds as $serviceId) { /* INSERT doc_contract_services */ }

        // 4. Crear tiers de precio
        foreach ($tiers as $tier) { /* INSERT doc_contract_tiers */ }

        // 5. Registrar en historial
        (new ContractHistoryModel())->add($contractId, $userId, 'creado', 'Contrato creado');

        $conn->commit();
        return $contractId;
    } catch (Exception $e) {
        $conn->rollback();
        app_log("Error al crear contrato: " . $e->getMessage());
        return false;
    }
}
```

### Patrón: Cálculo de tier

```php
public function calculateTierPrice($contractId, $guestCount)
{
    $tierModel = new ContractTierModel();
    $tiers = $tierModel->getByContractId($contractId);

    foreach ($tiers as $tier) {
        $max = $tier['max_guests'] ?? PHP_INT_MAX;
        if ($guestCount >= $tier['min_guests'] && $guestCount <= $max) {
            return [
                'tier_id'        => $tier['id'],
                'price_per_person' => $tier['price_per_person'],
                'discount'       => $tier['discount_percent'],
                'total'          => $tier['price_per_person'] * $guestCount,
            ];
        }
    }
    return null; // No matching tier
}
```

### Patrón: Upload con WebP

```php
public function upload($file, $contractId, $companyId, $category, $userId)
{
    // 1. Validar
    $allowed = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowed)) {
        return ['error' => 'Tipo de archivo no permitido'];
    }

    // 2. Crear ruta: uploads/contracts/{company_id}/{año-mes}/
    $subdir = $companyId . '/' . date('Y-m');
    $uploadDir = UPLOAD_BASE_PATH . '/' . $subdir;
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    // 3. Si es imagen, convertir a WebP
    $isImage = in_array($file['type'], ['image/jpeg', 'image/png']);
    if ($isImage) {
        $converter = new ImageConverterService();
        $filename = $converter->convertToWebp($file['tmp_name'], $uploadDir);
    } else {
        $filename = uniqid() . '_' . basename($file['name']);
        move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename);
    }

    // 4. Guardar en BD
    // ...
}
```

---

## ❌ Lo que NO hacer

- ❌ No poner SQL directo en Services — usar Models
- ❌ No hacer echo/die en Services — retornar datos al Controller
- ❌ No acceder a $_POST/_GET en Services — recibir datos como parámetros
- ❌ No crear Services sin PHPDoc completo
- ❌ No ignorar manejo de errores — siempre try/catch en operaciones críticas
