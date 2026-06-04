---
name: view-builder
description: Crea vistas PHP/HTML para el Sistema de Contratos Atankalama. Define estructura de layouts, formularios, tablas DataTables, gráficos Chart.js, y componentes reutilizables.
---

# 🎨 View Builder — Sistema de Contratos Atankalama

## Contexto

- **Proyecto:** Sistema de Contratos – Hotel Atankalama
- **Stack:** PHP + HTML5 + Bootstrap 5.3 + DataTables + Chart.js
- **Iconos:** Font Awesome 6 + Bootstrap Icons
- **Layout:** `views/layouts/header.php` + `views/layouts/footer.php`
- **CSS custom:** `public/assets/css/style.css`
- **Ubicación:** `views/{modulo}/{archivo}.php`

---

## 📐 Convenciones obligatorias

### 1. Estructura base de una vista

```php
<?php require VIEW_PATH . "/layouts/header.php"; ?>

<!-- Mensajes flash -->
<?php if(!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if(!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <i class="fa-solid fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Page Header -->
<div class="page-header">
  <h3><i class="fa-solid fa-{icono}"></i> {Título}</h3>
  <div>
    <a href="<?= BASE_URL ?>/{modulo}/create" class="btn btn-atk">
      <i class="fa-solid fa-plus"></i> Nuevo
    </a>
  </div>
</div>

<!-- Contenido -->
<div class="card fade-in">
  <div class="card-body">
    <!-- contenido aquí -->
  </div>
</div>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
```

### 2. Tabla con DataTables

```html
<table id="tabla{Entidad}" class="table table-striped table-hover">
  <thead>
    <tr>
      <th>Código</th>
      <th>Nombre</th>
      <th>Estado</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($items as $item): ?>
    <tr>
      <td><?= htmlspecialchars($item['code']) ?></td>
      <td><?= htmlspecialchars($item['name']) ?></td>
      <td><span class="badge badge-<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span></td>
      <td>
        <a href="<?= BASE_URL ?>/{modulo}/show/<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver">
          <i class="fa-solid fa-eye"></i>
        </a>
        <a href="<?= BASE_URL ?>/{modulo}/edit/<?= $item['id'] ?>" class="btn btn-sm btn-outline-warning" title="Editar">
          <i class="fa-solid fa-pen"></i>
        </a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<script>
$(document).ready(function() {
    $('#tabla{Entidad}').DataTable();
});
</script>
```

### 3. Formularios

```html
<form method="post" action="<?= BASE_URL ?>/{modulo}/store" enctype="multipart/form-data">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Nombre <span class="text-danger">*</span></label>
      <input type="text" name="name" class="form-control" required
             value="<?= htmlspecialchars($item['name'] ?? '') ?>">
    </div>

    <div class="col-md-6">
      <label class="form-label">Tipo</label>
      <select name="type" class="form-select" required>
        <option value="">-- Seleccionar --</option>
        <option value="cliente" <?= ($item['type'] ?? '') === 'cliente' ? 'selected' : '' ?>>Cliente</option>
        <option value="proveedor" <?= ($item['type'] ?? '') === 'proveedor' ? 'selected' : '' ?>>Proveedor</option>
      </select>
    </div>
  </div>

  <div class="mt-4">
    <button type="submit" class="btn btn-atk"><i class="fa-solid fa-save"></i> Guardar</button>
    <a href="<?= BASE_URL ?>/{modulo}" class="btn btn-secondary">Cancelar</a>
  </div>
</form>
```

### 4. KPI Cards (Dashboard)

```html
<div class="col-12 col-md-6 col-xl-3">
  <div class="card kpi-card {variante}">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <div class="kpi-label">{Etiqueta}</div>
          <div class="kpi-value"><?= (int)$valor ?></div>
        </div>
        <i class="fa-solid fa-{icono} fa-2x opacity-50"></i>
      </div>
    </div>
  </div>
</div>
```

Variantes: (sin clase) = azul, `success` = verde, `warning` = amarillo, `danger` = rojo, `info` = cyan

### 5. Badges de estado

```php
// Contratos
<span class="badge badge-<?= $contract['status'] ?>"><?= ucfirst($contract['status']) ?></span>

// Pagos
<span class="badge badge-<?= $payment['status'] ?>"><?= ucfirst($payment['status']) ?></span>
```

### 6. Componentes parciales (partials)

Los archivos que empiezan con `_` son componentes reutilizables:

```php
// En views/contracts/show.php:
<?php require VIEW_PATH . "/contracts/_attachments.php"; ?>
<?php require VIEW_PATH . "/contracts/_payments_timeline.php"; ?>
```

---

## 🛡️ Seguridad en vistas

- **SIEMPRE** usar `htmlspecialchars()` al mostrar datos del usuario
- **SIEMPRE** incluir token CSRF en formularios
- **SIEMPRE** verificar permisos antes de mostrar botones de acción
- Usar `enctype="multipart/form-data"` solo si hay upload de archivos

```php
<?php if(AuthService::hasPermission('contracts_edit')): ?>
  <a href="..." class="btn btn-sm btn-warning">Editar</a>
<?php endif; ?>
```

---

## ❌ Lo que NO hacer

- ❌ No mostrar datos sin `htmlspecialchars()`
- ❌ No crear formularios sin CSRF token
- ❌ No usar estilos inline — usar clases CSS de `style.css`
- ❌ No incluir scripts de CDN — ya están en el footer
- ❌ No hardcodear URLs — siempre usar `BASE_URL`
- ❌ No mostrar botones de acciones sin verificar permisos
