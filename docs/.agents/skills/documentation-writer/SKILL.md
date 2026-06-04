---
name: documentation-writer
description: Genera y actualiza documentación PHPDoc para el proyecto Hotel Atankalama – Sistema de Novedades. Sigue las convenciones de estilo ya establecidas en el código del proyecto.
---

# 📝 Documentation Writer — Sistema de Novedades

## Contexto del Proyecto

- **Proyecto:** Sistema de Novedades – Hotel Atankalama
- **Stack:** PHP 7.4+, PDO, MySQL, PHPMailer, MVC sin framework
- **Estructura:**
  ```
  novedades/
  ├── config/         → Database.php, config.php
  ├── controllers/    → *Controller.php
  ├── models/         → *.php (clases con PDO)
  ├── services/       → *Service.php (lógica de negocio)
  ├── helpers/        → funciones auxiliares
  └── views/          → *.php (templates HTML)
  ```

---

## 🎯 Objetivo de esta Skill

Documentar correctamente el código PHP del proyecto usando **PHPDoc** con el estilo ya establecido en archivos como `Novedad.php` y `NovedadController.php`.

---

## 📐 Convenciones del Proyecto

### 1. Encabezado de Clase (solo para `services/`)
```php
<?php
/**
 * ===================================================
 * Servicio: NombreServicio
 * Proyecto: Hotel Atankalama – Sistema de Novedades
 * PHP: 7.4 compatible
 * ===================================================
 *
 * Responsabilidad:
 * [Descripción clara de qué hace este servicio]
 */
```

### 2. Constructor (models/)
```php
/**
 * Constructor del modelo NombreModelo
 * - Obtiene conexión única desde Database::getConnection()
 * - Asigna PDO a $this->pdo
 *
 * @throws RuntimeException si no hay conexión
 */
```

### 3. Métodos Públicos (estilo completo)
```php
/**
 * [Qué hace el método — verbo en tercera persona]
 *
 * Qué hace:
 * - [paso 1]
 * - [paso 2]
 *
 * @param tipo $nombre Descripción
 * @param array $filtros Claves esperadas:
 *   - clave (tipo) descripción
 *
 * @return tipo Descripción de lo que retorna
 *
 * @throws TipoException Cuándo se lanza
 *
 * Variables usadas:
 * - $this->pdo
 * - OtraClase::metodo()
 */
```

### 4. Métodos Privados / Helpers
```php
/**
 * [Qué hace — descripción corta]
 *
 * @param tipo $nombre
 * @return tipo
 */
```

### 5. Cierre de Funciones
Siempre agregar al final de cada función:
```php
}
// Fin de la función nombreMetodo()
```

### 6. Bloques de Código Interno
Separar secciones con comentarios en mayúsculas:
```php
// ===============================
// NOMBRE DE LA SECCIÓN
// ===============================
```
O en funciones más cortas:
```php
// ----------------------------
// Nombre de la sección
// ----------------------------
```

---

## 🗂️ Reglas por Tipo de Archivo

### `models/*.php`
- Documentar **constructor** siempre con `@throws RuntimeException`
- Documentar cada método con `@param`, `@return`, y la sección "Qué hace:"
- En métodos que reciben `array $filtros`, listar las **claves esperadas** en el `@param`
- Anotar `Retorna:` cuando el método devuelve arrays complejos

### `controllers/*.php`
- Documentar métodos públicos que sean acciones (form, store, list, export, etc.)
- En el `@param` de `store()`, mencionar las claves de `$_POST` usadas
- Métodos privados como `exportExcel()`, `exportPDF()`, `enviarEmail()` deben tener su propio bloque

### `services/*.php`
- Incluir **encabezado de clase** completo
- Documentar método principal `calcular()` o análogo con ejemplo de retorno

### `helpers/*.php`
- Documentar cada función con bloque corto (`@param`, `@return`)

### `config/Database.php`
- El patrón Singleton debe estar documentado brevemente

---

## ✅ Proceso para Documentar un Archivo

1. **Leer el archivo completo** con `view_file`
2. **Identificar qué falta documentar:**
   - Métodos sin bloque PHPDoc
   - Métodos con bloque incompleto (sin `@return`, sin sección "Qué hace:")
   - Funciones sin comentario de cierre
3. **Generar el bloque PHPDoc** siguiendo las convenciones arriba
4. **No eliminar** bloques existentes, solo completarlos o mejorarlos
5. **Usar** `multi_replace_file_content` para hacer múltiples ediciones en un solo paso

---

## 🚫 Lo que NO hacer

- ❌ No traducir comentarios internos al inglés
- ❌ No agregar `@author` ni `@version` (no se usa en este proyecto)
- ❌ No usar anotaciones de frameworks externos (no hay Laravel ni Symfony)
- ❌ No documentar variables locales simples (solo parámetros y retornos)
- ❌ No modificar la lógica del código, solo añadir/mejorar documentación

---

## 📋 Referencia de Tipos PHP usados en el proyecto

| Tipo PHP       | Cuándo usarlo                              |
|----------------|--------------------------------------------|
| `int`          | IDs, flags 0\|1, niveles de importancia    |
| `string`       | textos, fechas como `Y-m-d`, áreas, hotel  |
| `array`        | resultados de fetchAll, datos de formulario|
| `bool`         | retornos de éxito/fracaso                  |
| `void`         | métodos que no retornan nada               |
| `?string`      | parámetros opcionales que pueden ser null  |
| `PDO`          | conexión a base de datos                   |

---

## 💡 Ejemplo Completo — Antes y Después

### Antes (sin documentar):
```php
public function listarArchivos($novedad_id)
{
    $stmt = $this->pdo->prepare('SELECT * FROM nov_archivos WHERE novedad_id = ?');
    $stmt->execute([(int) $novedad_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

### Después (documentado):
```php
/**
 * Lista todos los archivos adjuntos de una novedad.
 *
 * @param int $novedad_id ID de la novedad
 * @return array Lista de archivos con columnas: id, novedad_id, archivo, tipo
 */
public function listarArchivos($novedad_id)
{
    $stmt = $this->pdo->prepare('SELECT * FROM nov_archivos WHERE novedad_id = ?');
    $stmt->execute([(int) $novedad_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Fin de la función listarArchivos()
```
