---
name: model-builder
description: Crea modelos PHP para el Sistema de Contratos Atankalama. Define convenciones de CRUD, prepared statements, PHPDoc, y naming para la capa de datos (tabla doc_*).
---

# 🗄️ Model Builder — Sistema de Contratos Atankalama

## Contexto

- **Proyecto:** Sistema de Contratos – Hotel Atankalama
- **Stack:** PHP 7.4+, MySQLi, MVC sin framework
- **Clase base:** `Model` (en `app/core/Model.php`) inyecta `$this->conn` (mysqli)
- **Prefijo tablas:** `doc_` (todas las tablas del sistema)
- **Ubicación:** `app/models/NombreModel.php`

---

## 📐 Convenciones obligatorias

### 1. Naming
- Nombre de archivo: `{Entidad}Model.php` (PascalCase)
- Nombre de clase: `{Entidad}Model extends Model`
- Ejemplo: `CompanyModel`, `ContractModel`, `PaymentModel`

### 2. Estructura de clase

```php
<!--
  = Proyecto: Sistema de Contratos Atankalama =
  = Autor: Rodrigo Jaque Escobar              =
  = Contacto: rjaquers@gmail.com              =

-->
<?php
/**
 * Modelo de {Entidad}.
 *
 * Gestiona las operaciones CRUD sobre la tabla doc_{tabla}.
 * {Descripción adicional del propósito}.
 *
 * @package App\Models
 */
class {Entidad}Model extends Model
{
    // Métodos CRUD aquí
}
```

### 3. Métodos estándar CRUD

Cada modelo de entidad principal DEBE tener estos métodos base:

| Método | Descripción | Return |
|---|---|---|
| `getAll($filters)` | Listado con filtros opcionales | `array` |
| `getById($id)` | Buscar por ID | `array\|null` |
| `create($data)` | Insertar nuevo registro | `int\|false` (insert_id) |
| `update($id, $data)` | Actualizar registro | `bool` |
| `delete($id)` | Soft delete (active = 0) | `bool` |
| `count($filters)` | Contar registros filtrados | `int` |

### 4. PHPDoc obligatorio

Cada método DEBE tener:
```php
/**
 * {Qué hace — verbo en tercera persona}.
 *
 * Qué hace:
 * - {paso 1}
 * - {paso 2}
 *
 * @param  tipo $nombre Descripción
 * @return tipo Descripción de retorno
 */
```

Y al cierre:
```php
}
// Fin de la función nombreMetodo()
```

### 5. Seguridad SQL

- **SIEMPRE** usar prepared statements (`$this->conn->prepare()`)
- **NUNCA** concatenar variables en SQL
- **SIEMPRE** usar `bind_param()` con tipos explícitos: `"s"` string, `"i"` int, `"d"` decimal
- Para soft delete: `WHERE active = 1` en todas las consultas de lectura

### 6. Soft Delete

```php
public function delete($id)
{
    $stmt = $this->conn->prepare("UPDATE doc_{tabla} SET active = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->affected_rows > 0;
}
```

### 7. Filtros dinámicos

Para métodos `getAll()` con múltiples filtros opcionales:

```php
public function getAll($filters = [])
{
    $where = ["t.active = 1"];
    $params = [];
    $types = "";

    if (!empty($filters['status'])) {
        $where[] = "t.status = ?";
        $params[] = $filters['status'];
        $types .= "s";
    }

    if (!empty($filters['company_id'])) {
        $where[] = "t.company_id = ?";
        $params[] = (int)$filters['company_id'];
        $types .= "i";
    }

    $sql = "SELECT t.* FROM doc_{tabla} t WHERE " . implode(" AND ", $where) . " ORDER BY t.created_at DESC";
    $stmt = $this->conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}
```

---

## 🔗 Relaciones entre modelos

### JOINs frecuentes
- `doc_contracts` → `doc_companies` (company_id)
- `doc_contracts` → `doc_contract_hotels` → `doc_hotels` (N:M)
- `doc_contracts` → `doc_contract_services` → `doc_services` (N:M)
- `doc_contracts` → `doc_contract_tiers` (1:N)
- `doc_contracts` → `doc_contract_payments` (1:N)
- `doc_contracts` → `doc_contract_attachments` (1:N)
- `doc_contracts` → `doc_users` (created_by)

### Patrón para relaciones N:M
```php
public function getHotelsByContractId($contractId)
{
    $stmt = $this->conn->prepare("
        SELECT h.*
        FROM doc_contract_hotels ch
        JOIN doc_hotels h ON h.id = ch.hotel_id
        WHERE ch.contract_id = ?
    ");
    $stmt->bind_param("i", $contractId);
    $stmt->execute();
    // ...
}
```

---

## ❌ Lo que NO hacer

- ❌ No usar `query()` directo con variables — siempre `prepare()`
- ❌ No hacer `DELETE FROM` — siempre soft delete
- ❌ No olvidar `WHERE active = 1` en lecturas
- ❌ No retornar el result set directamente — siempre convertir a array
- ❌ No crear modelos sin PHPDoc completo
