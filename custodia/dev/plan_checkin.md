# Plan: Sistema de Check-in / Checkout con QR

**Fecha de planificación:** 2026-04-08  
**Estado:** Pendiente de implementación

---

## Contexto del negocio

- Empresas envían trabajadores al hotel en grupos (hasta 120 personas en bus)
- La empresa entrega el listado de personas con anticipación (se carga como lote)
- Las empresas necesitan registros de: ingresos, egresos y comidas consumidas
- Es un respaldo complementario al PMS existente
- **Un único QR fijo** estará impreso/pegado en recepción
- El trabajador escanea el QR con su celular → ingresa su RUT → el sistema hace el resto

---

## Flujo del kiosko (página pública `/checkin`)

El QR apunta siempre a la misma URL. La lógica según estado del RUT:

| Estado en BD | Acción automática |
|---|---|
| No tiene check-in | Registra **check-in** |
| Tiene check-in, sin checkout | Registra **checkout** |
| RUT no encontrado en ningún lote activo | **Autoregistro pendiente**: pide nombre + selecciona empresa (solo empresas con lotes activos hoy) → mensaje "será validado por recepción" |
| Ya tiene checkout | Muestra estado, indica ir a recepción |

---

## Panel del recepcionista (`/recepcion`)

### Sección 1: Pendientes de validación
- Lista de autoregistros con: nombre, RUT, empresa seleccionada, hora de llegada
- **Aprobar**: recepcionista elige el lote de esa empresa → persona queda agregada al lote + check-in confirmado
- **Rechazar**: elimina el pendiente (con nota opcional)
- Badge en el menú con el número de pendientes activos

### Sección 2: Gestión de checkout
- Lista de huéspedes con check-in activo y sin checkout (agrupados por lote/empresa)
- **Checkout individual**: botón por persona
- **Checkout masivo**: seleccionar todo el lote → checkout de todos de un click

---

## Tablas nuevas en BD

### `huesped_movimiento`
Registro oficial de cada movimiento (check-in o checkout).

```sql
CREATE TABLE huesped_movimiento (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    lote_id         INT NOT NULL,
    persona_id      INT NULL,           -- FK a colacion_voucher (puede ser null si es autoregistro aprobado)
    guest_rut       VARCHAR(20) NOT NULL,
    guest_nombre    VARCHAR(150) NULL,
    tipo            ENUM('checkin','checkout') NOT NULL,
    fecha_hora      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    origen          ENUM('kiosko','recepcion') NOT NULL DEFAULT 'kiosko',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_rut (guest_rut),
    INDEX idx_lote (lote_id),
    INDEX idx_tipo (tipo)
);
```

### `checkin_pendiente`
Autoregistros esperando validación del recepcionista.

```sql
CREATE TABLE checkin_pendiente (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    guest_rut       VARCHAR(20) NOT NULL,
    guest_nombre    VARCHAR(150) NOT NULL,
    empresa_id      INT NOT NULL,
    estado          ENUM('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
    lote_id         INT NULL,           -- se llena al aprobar
    nota_rechazo    VARCHAR(255) NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resuelto_at     DATETIME NULL,
    INDEX idx_estado (estado),
    INDEX idx_rut (guest_rut)
);
```

---

## Archivos a crear / modificar

### Nuevos archivos

| Archivo | Descripción |
|---|---|
| `controllers/CheckinController.php` | Lógica del kiosko y del panel de recepción |
| `models/HuespedMovimiento.php` | Modelo para `huesped_movimiento` |
| `models/CheckinPendiente.php` | Modelo para `checkin_pendiente` |
| `views/checkin/kiosko.php` | Vista del kiosko público (similar a buscar_servicio.php) |
| `views/checkin/recepcion.php` | Panel del recepcionista (pendientes + checkout masivo) |
| `sql/checkin_tables.sql` | Script SQL con las dos tablas nuevas |

### Archivos a modificar

| Archivo | Cambio |
|---|---|
| `index.php` | Agregar rutas nuevas y instanciar `CheckinController` |
| `views/layout/` o menú | Agregar badge de pendientes en el nav del recepcionista |

---

## Rutas nuevas en `index.php`

```php
// Kiosko público
$router->add('GET',  '/checkin',              [$checkinController, 'kiosko']);
$router->add('POST', '/checkin/registrar',    [$checkinController, 'registrar']);
$router->add('POST', '/checkin/autoregistro', [$checkinController, 'autoregistro']);

// Panel recepcionista
$router->add('GET',  '/recepcion',                      [$checkinController, 'recepcion']);
$router->add('POST', '/recepcion/pendiente/aprobar',    [$checkinController, 'aprobarPendiente']);
$router->add('POST', '/recepcion/pendiente/rechazar',   [$checkinController, 'rechazarPendiente']);
$router->add('POST', '/recepcion/checkout/individual',  [$checkinController, 'checkoutIndividual']);
$router->add('POST', '/recepcion/checkout/masivo',      [$checkinController, 'checkoutMasivo']);
```

---

## Orden de implementación

- [ ] **Paso 1** — Crear `sql/checkin_tables.sql` y ejecutar en BD
- [ ] **Paso 2** — Crear `models/HuespedMovimiento.php` y `models/CheckinPendiente.php`
- [ ] **Paso 3** — Crear `controllers/CheckinController.php` con lógica de kiosko
- [ ] **Paso 4** — Crear `views/checkin/kiosko.php` (basado en `buscar_servicio.php`)
- [ ] **Paso 5** — Agregar rutas del kiosko en `index.php` y probar
- [ ] **Paso 6** — Crear lógica del panel recepcionista en el controller
- [ ] **Paso 7** — Crear `views/checkin/recepcion.php`
- [ ] **Paso 8** — Agregar badge de pendientes en el menú
- [ ] **Paso 9** — Pruebas de flujo completo (caso normal + excepción + checkout masivo)
- [ ] **Paso 10** — Reporte para empresas (entradas, salidas, comidas) — fase siguiente

---

## Notas adicionales

- El kiosko debe reutilizar el mismo estilo visual de `/colaciones/buscar` (auto-reset por inactividad, validación de RUT, reloj)
- El select de empresas en el autoregistro solo muestra empresas con lotes cuya `fecha_fin_servicio >= hoy`
- El checkout masivo opera sobre todos los registros de un `lote_id` que tengan check-in sin checkout
- El recepcionista al aprobar un pendiente: 1) agrega la persona al lote en `colacion_voucher`, 2) crea el `huesped_movimiento` de check-in, 3) marca el `checkin_pendiente` como aprobado
