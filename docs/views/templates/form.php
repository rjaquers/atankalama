<!--
  = Proyecto: Sistema de Contratos Atankalama =
  = Autor: Rodrigo Jaque Escobar              =
  = Contacto: rjaquers@gmail.com              =

-->
<?php
$title = !empty($template) ? "Editar Plantilla" : "Nueva Plantilla";
include VIEW_PATH . "/layouts/header.php";
?>

<!-- TinyMCE 6 CDN (GPLv2 - sin API key requerida) -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>

<style>
  /* Panel de variables sticky */
  #variables-panel {
    position: sticky;
    top: 1rem;
  }
  /* Hover mejorado para variable tags */
  .var-tag {
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.75rem !important;
  }
  .var-tag:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  }
  /* Tabs estilizados */
  .editor-tabs .nav-link {
    border: none;
    color: #6c757d;
    font-weight: 600;
    font-size: 0.85rem;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem 0.5rem 0 0;
    transition: all 0.2s ease;
  }
  .editor-tabs .nav-link.active {
    color: #1a3a5c;
    background: #f0f4f8;
    border-bottom: 3px solid #1a3a5c;
  }
  .editor-tabs .nav-link:hover:not(.active) {
    color: #2c5f8a;
    background: #f8f9fa;
  }
  /* Preview panel */
  #preview-panel {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    padding: 2rem;
    min-height: 400px;
    max-height: 600px;
    overflow-y: auto;
    font-family: 'Times New Roman', Times, serif;
    font-size: 14px;
    line-height: 1.6;
  }
  #preview-panel h1, #preview-panel h2, #preview-panel h3 {
    color: #1a3a5c;
  }
  /* Variable highlight in preview */
  .var-highlight {
    background: #fff3cd;
    padding: 1px 4px;
    border-radius: 3px;
    font-family: monospace;
    font-size: 0.85em;
    color: #856404;
    border: 1px dashed #ffc107;
  }
  /* Header / Footer fields */
  .field-header-footer {
    background: #f8f9fa;
    border-radius: 0.75rem;
    padding: 1rem;
    margin-top: 1rem;
  }
  .field-header-footer label {
    font-size: 0.82rem;
  }
</style>

<div class="row pt-4 g-4">
    <!-- ========================= -->
    <!-- COLUMNA IZQUIERDA: EDITOR -->
    <!-- ========================= -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <!-- Título -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h2 class="h3 fw-bold mb-0">
                    <i class="fa-solid fa-palette text-primary me-2"></i><?= $title ?>
                </h2>
                <a href="<?= BASE_URL ?>/templates" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Volver
                </a>
            </div>

            <form id="templateForm" action="<?= BASE_URL ?>/templates/<?= !empty($template) ? 'update/' . $template['id'] : 'store' ?>" method="post">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

                <!-- Nombre + Tipo -->
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">
                            <i class="fa-solid fa-tag text-muted me-1"></i> Nombre de la Plantilla
                        </label>
                        <input type="text" name="name" class="form-control form-control-lg border-2 shadow-none"
                               value="<?= htmlspecialchars($template['name'] ?? '') ?>" required
                               placeholder="Ej: Contrato Hospedaje Estándar">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fa-solid fa-layer-group text-muted me-1"></i> Tipo de Contrato
                        </label>
                        <select name="contract_type" class="form-select form-select-lg border-2 shadow-none">
                            <option value="hospedaje" <?= ($template['contract_type'] ?? '') == 'hospedaje' ? 'selected' : '' ?>>🏨 Hospedaje</option>
                            <option value="arriendo" <?= ($template['contract_type'] ?? '') == 'arriendo' ? 'selected' : '' ?>>🏠 Arriendo</option>
                            <option value="proveedor" <?= ($template['contract_type'] ?? '') == 'proveedor' ? 'selected' : '' ?>>🚛 Proveedor</option>
                        </select>
                    </div>
                </div>

                <!-- Tabs: Editor Visual / Código / Vista Previa -->
                <ul class="nav editor-tabs mb-0" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-visual" type="button" role="tab"
                                onclick="switchTab('visual')">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Editor Visual
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-code" type="button" role="tab"
                                onclick="switchTab('code')">
                            <i class="fa-solid fa-code me-1"></i> Código HTML
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-preview" type="button" role="tab"
                                onclick="switchTab('preview')">
                            <i class="fa-solid fa-eye me-1"></i> Vista Previa
                        </button>
                    </li>
                </ul>

                <!-- Panel Visual (TinyMCE) -->
                <div id="panel-visual">
                    <textarea id="body_html_editor" name="body_html"><?= htmlspecialchars($template['body_html'] ?? '<h2 style="text-align: center;">CONTRATO DE HOSPEDAJE</h2>
<p style="text-align: center;"><strong>N° {{contrato_codigo}}</strong></p>
<hr>
<p>En la ciudad de {{hotel_ciudad}}, con fecha {{fecha_actual}}, entre:</p>
<p><strong>{{empresa_nombre}}</strong>, RUT {{empresa_rut}}, representada por {{representante}}, con domicilio en {{empresa_direccion}}, en adelante "EL CLIENTE";</p>
<p>y <strong>{{hotel_nombre}}</strong>, en adelante "EL HOTEL";</p>
<p>Se acuerda el siguiente contrato de {{contrato_tipo}}:</p>
<h3>CLÁUSULA PRIMERA: Objeto</h3>
<p>El presente contrato tiene por objeto...</p>
<h3>CLÁUSULA SEGUNDA: Vigencia</h3>
<p>El contrato tendrá vigencia desde el {{fecha_inicio}} hasta el {{fecha_termino}}.</p>
<h3>CLÁUSULA TERCERA: Precio y Forma de Pago</h3>
<p>El precio total es de {{monto_total}} ({{monto_letras}}), pagadero de forma {{frecuencia_pago}}.</p>') ?></textarea>
                </div>

                <!-- Panel Código HTML (oculto por defecto) -->
                <div id="panel-code" style="display:none;">
                    <textarea id="body_html_raw" class="form-control border-2 shadow-none"
                              style="height: 450px; font-family: 'Fira Code', 'Cascadia Code', 'Consolas', monospace; font-size: 0.85rem; line-height: 1.5; tab-size: 2; background: #1e1e2e; color: #cdd6f4; border-color: #45475a;"
                              spellcheck="false"></textarea>
                </div>

                <!-- Panel Vista Previa (oculto por defecto) -->
                <div id="panel-preview" style="display:none;">
                    <div id="preview-panel">
                        <p class="text-muted text-center"><i class="fa-solid fa-spinner fa-spin"></i> Cargando vista previa...</p>
                    </div>
                </div>

                <!-- Header / Footer Text -->
                <div class="field-header-footer">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted">
                                <i class="fa-solid fa-arrow-up me-1"></i> Texto Encabezado
                                <small class="fw-normal">(opcional)</small>
                            </label>
                            <input type="text" name="header_text" class="form-control border-2 shadow-none"
                                   value="<?= htmlspecialchars($template['header_text'] ?? '') ?>"
                                   placeholder="Ej: Hotel Atankalama - Contrato Oficial">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-muted">
                                <i class="fa-solid fa-arrow-down me-1"></i> Texto Pie de Página
                                <small class="fw-normal">(opcional)</small>
                            </label>
                            <input type="text" name="footer_text" class="form-control border-2 shadow-none"
                                   value="<?= htmlspecialchars($template['footer_text'] ?? '') ?>"
                                   placeholder="Ej: Página {page} de {pages}">
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm">
                        <i class="fa-solid fa-save me-1"></i> Guardar Plantilla
                    </button>
                    <a href="<?= BASE_URL ?>/templates" class="btn btn-outline-secondary btn-lg px-4 border-2 fw-semibold">
                        <i class="fa-solid fa-xmark me-1"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- =============================== -->
    <!-- COLUMNA DERECHA: VARIABLES       -->
    <!-- =============================== -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-light" id="variables-panel">
            <h5 class="fw-bold mb-1 ms-1">
                <i class="fa-solid fa-code text-primary me-2"></i> Variables Disponibles
            </h5>
            <p class="text-muted small mb-3 ms-1">Haz clic en una variable para insertarla en el editor:</p>

            <!-- Contrato -->
            <h6 class="fw-bold text-uppercase text-muted small mt-2 mb-2 ms-1" style="font-size:.7rem; letter-spacing:.08em;">
                <i class="fa-solid fa-file-contract me-1"></i> Contrato
            </h6>
            <ul class="list-group list-group-flush small bg-transparent mb-3">
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold var-tag" role="button" data-var="{{contrato_codigo}}" title="Insertar en editor">{{contrato_codigo}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Código único</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold var-tag" role="button" data-var="{{contrato_tipo}}" title="Insertar en editor">{{contrato_tipo}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Hospedaje / Arriendo…</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold var-tag" role="button" data-var="{{monto_total}}" title="Insertar en editor">{{monto_total}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Monto formateado $</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold var-tag" role="button" data-var="{{monto_letras}}" title="Insertar en editor">{{monto_letras}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Monto en palabras</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold var-tag" role="button" data-var="{{frecuencia_pago}}" title="Insertar en editor">{{frecuencia_pago}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Semanal / Mensual…</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold var-tag" role="button" data-var="{{huespedes_base}}" title="Insertar en editor">{{huespedes_base}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Cant. huéspedes base</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-danger bg-opacity-10 text-danger fw-semibold var-tag" role="button" data-var="{{escala_precios}}" title="Insertar en editor">{{escala_precios}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Tabla de precios (tiers)</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold var-tag" role="button" data-var="{{servicios_incluidos}}" title="Insertar en editor">{{servicios_incluidos}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Servicios del contrato</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold var-tag" role="button" data-var="{{notas_contrato}}" title="Insertar en editor">{{notas_contrato}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Notas del contrato</span>
                </li>
            </ul>

            <!-- Empresa -->
            <h6 class="fw-bold text-uppercase text-muted small mt-1 mb-2 ms-1" style="font-size:.7rem; letter-spacing:.08em;">
                <i class="fa-solid fa-building me-1"></i> Empresa
            </h6>
            <ul class="list-group list-group-flush small bg-transparent mb-3">
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-success bg-opacity-10 text-success fw-semibold var-tag" role="button" data-var="{{empresa_nombre}}" title="Insertar en editor">{{empresa_nombre}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Razón social</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-success bg-opacity-10 text-success fw-semibold var-tag" role="button" data-var="{{empresa_rut}}" title="Insertar en editor">{{empresa_rut}}</span>
                    <span class="text-muted" style="font-size:.72rem;">RUT de la empresa</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-success bg-opacity-10 text-success fw-semibold var-tag" role="button" data-var="{{empresa_direccion}}" title="Insertar en editor">{{empresa_direccion}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Dirección comercial</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-success bg-opacity-10 text-success fw-semibold var-tag" role="button" data-var="{{representante}}" title="Insertar en editor">{{representante}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Contacto principal</span>
                </li>
            </ul>

            <!-- Hotel -->
            <h6 class="fw-bold text-uppercase text-muted small mt-1 mb-2 ms-1" style="font-size:.7rem; letter-spacing:.08em;">
                <i class="fa-solid fa-hotel me-1"></i> Hotel
            </h6>
            <ul class="list-group list-group-flush small bg-transparent mb-3">
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-warning bg-opacity-10 text-warning fw-semibold var-tag" role="button" data-var="{{hotel_nombre}}" title="Insertar en editor">{{hotel_nombre}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Ej: Hotel Atankalama</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-warning bg-opacity-10 text-warning fw-semibold var-tag" role="button" data-var="{{hotel_codigo}}" title="Insertar en editor">{{hotel_codigo}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Ej: ATK, INN</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-warning bg-opacity-10 text-warning fw-semibold var-tag" role="button" data-var="{{hotel_rut}}" title="Insertar en editor">{{hotel_rut}}</span>
                    <span class="text-muted" style="font-size:.72rem;">RUT del hotel</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-warning bg-opacity-10 text-warning fw-semibold var-tag" role="button" data-var="{{hotel_direccion}}" title="Insertar en editor">{{hotel_direccion}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Dirección del hotel</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-warning bg-opacity-10 text-warning fw-semibold var-tag" role="button" data-var="{{hotel_ciudad}}" title="Insertar en editor">{{hotel_ciudad}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Ciudad del hotel</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-warning bg-opacity-10 text-warning fw-semibold var-tag" role="button" data-var="{{hotel_telefono}}" title="Insertar en editor">{{hotel_telefono}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Teléfono del hotel</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-warning bg-opacity-10 text-warning fw-semibold var-tag" role="button" data-var="{{hotel_email}}" title="Insertar en editor">{{hotel_email}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Email del hotel</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-warning bg-opacity-10 text-warning fw-semibold var-tag" role="button" data-var="{{hotel_representante}}" title="Insertar en editor">{{hotel_representante}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Representante Legal</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-warning bg-opacity-10 text-warning fw-semibold var-tag" role="button" data-var="{{hotel_rut_representante}}" title="Insertar en editor">{{hotel_rut_representante}}</span>
                    <span class="text-muted" style="font-size:.72rem;">RUT Representante Legal</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-warning bg-opacity-10 text-warning fw-semibold var-tag" role="button" data-var="{{hoteles_lista}}" title="Insertar en editor">{{hoteles_lista}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Todos los hoteles</span>
                </li>
            </ul>

            <!-- Fechas -->
            <h6 class="fw-bold text-uppercase text-muted small mt-1 mb-2 ms-1" style="font-size:.7rem; letter-spacing:.08em;">
                <i class="fa-solid fa-calendar-days me-1"></i> Fechas
            </h6>
            <ul class="list-group list-group-flush small bg-transparent mb-3">
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-info bg-opacity-10 text-info fw-semibold var-tag" role="button" data-var="{{fecha_actual}}" title="Insertar en editor">{{fecha_actual}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Fecha de generación</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-info bg-opacity-10 text-info fw-semibold var-tag" role="button" data-var="{{fecha_inicio}}" title="Insertar en editor">{{fecha_inicio}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Inicio del contrato</span>
                </li>
                <li class="list-group-item bg-transparent px-1 py-1 border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-info bg-opacity-10 text-info fw-semibold var-tag" role="button" data-var="{{fecha_termino}}" title="Insertar en editor">{{fecha_termino}}</span>
                    <span class="text-muted" style="font-size:.72rem;">Fin del contrato</span>
                </li>
            </ul>

            <!-- Feedback toast -->
            <div id="copy-toast" class="position-fixed bottom-0 end-0 p-3" style="z-index:1090; display:none;">
                <div class="toast align-items-center text-bg-success border-0 show" role="alert">
                    <div class="d-flex">
                        <div class="toast-body fw-semibold">
                            <i class="fa-solid fa-check me-1"></i> <span id="toast-text">Variable insertada</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // ==========================================
    // INICIALIZAR TINYMCE
    // ==========================================
    var editorInstance = null;
    var currentTab = 'visual';

    // Definición de las variables para el menú desplegable
    var templateVariables = [
        { title: '── Contrato ──', value: '' },
        { title: 'Código del Contrato', value: '{{contrato_codigo}}' },
        { title: 'Tipo de Contrato', value: '{{contrato_tipo}}' },
        { title: 'Monto Total', value: '{{monto_total}}' },
        { title: 'Monto en Letras', value: '{{monto_letras}}' },
        { title: 'Frecuencia de Pago', value: '{{frecuencia_pago}}' },
        { title: 'Huéspedes Base', value: '{{huespedes_base}}' },
        { title: 'Tabla Escala de Precios', value: '{{escala_precios}}' },
        { title: 'Servicios Incluidos', value: '{{servicios_incluidos}}' },
        { title: 'Notas del Contrato', value: '{{notas_contrato}}' },
        { title: '── Empresa ──', value: '' },
        { title: 'Nombre Empresa', value: '{{empresa_nombre}}' },
        { title: 'RUT Empresa', value: '{{empresa_rut}}' },
        { title: 'Dirección Empresa', value: '{{empresa_direccion}}' },
        { title: 'Representante', value: '{{representante}}' },
        { title: '── Hotel ──', value: '' },
        { title: 'Nombre Hotel', value: '{{hotel_nombre}}' },
        { title: 'Código Hotel', value: '{{hotel_codigo}}' },
        { title: 'RUT Hotel', value: '{{hotel_rut}}' },
        { title: 'Dirección Hotel', value: '{{hotel_direccion}}' },
        { title: 'Ciudad Hotel', value: '{{hotel_ciudad}}' },
        { title: 'Teléfono Hotel', value: '{{hotel_telefono}}' },
        { title: 'Email Hotel', value: '{{hotel_email}}' },
        { title: 'Representante Hotel', value: '{{hotel_representante}}' },
        { title: 'RUT Repres. Hotel', value: '{{hotel_rut_representante}}' },
        { title: 'Lista de Hoteles', value: '{{hoteles_lista}}' },
        { title: '── Fechas ──', value: '' },
        { title: 'Fecha Actual', value: '{{fecha_actual}}' },
        { title: 'Fecha Inicio', value: '{{fecha_inicio}}' },
        { title: 'Fecha Término', value: '{{fecha_termino}}' }
    ];

    tinymce.init({
        selector: '#body_html_editor',
        license_key: 'gpl',
        height: 480,
        language: 'es',
        promotion: false,
        branding: false,
        menubar: 'file edit view insert format table',
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'charmap', 'preview',
            'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'table', 'wordcount', 'pagebreak', 'hr'
        ],
        toolbar: [
            'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough',
            'forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
            'table | pagebreak hr | insertVariable | removeformat | code fullscreen'
        ].join(' | '),
        font_size_formats: '8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 36pt 48pt',
        block_formats: 'Párrafo=p; Título 1=h1; Título 2=h2; Título 3=h3; Título 4=h4; Preformateado=pre',
        content_style: `
            body {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 12pt;
                line-height: 1.6;
                color: #333;
                max-width: 800px;
                margin: 0 auto;
                padding: 1rem;
            }
            h1, h2, h3 { color: #1a3a5c; }
            table { border-collapse: collapse; width: 100%; }
            table td, table th { border: 1px solid #ccc; padding: 8px; }
            .mce-content-body [data-mce-selected="inline-boundary"] { background: none; }
        `,
        // Botón personalizado: Insertar Variable
        setup: function(editor) {
            editorInstance = editor;

            // Registrar botón de menú para insertar variables
            editor.ui.registry.addMenuButton('insertVariable', {
                text: '⚡ Variable',
                tooltip: 'Insertar variable de contrato',
                fetch: function(callback) {
                    var items = [];
                    templateVariables.forEach(function(v) {
                        if (!v.value) {
                            // Separador / título de sección
                            items.push({
                                type: 'menuitem',
                                text: v.title,
                                enabled: false,
                                onAction: function() {}
                            });
                        } else {
                            items.push({
                                type: 'menuitem',
                                text: v.title + '  →  ' + v.value,
                                onAction: function() {
                                    editor.insertContent(v.value);
                                    showToast(v.value + ' insertada');
                                }
                            });
                        }
                    });
                    callback(items);
                }
            });
        },
        // Formato de tabla por defecto
        table_default_styles: {
            'border-collapse': 'collapse',
            'width': '100%'
        },
        table_default_attributes: {
            border: '1'
        }
    });

    // ==========================================
    // CAMBIAR TABS
    // ==========================================
    window.switchTab = function(tab) {
        // Desactivar todos los tabs
        document.querySelectorAll('.editor-tabs .nav-link').forEach(function(t) {
            t.classList.remove('active');
        });
        document.getElementById('tab-' + tab).classList.add('active');

        // Ocultar todos los paneles
        document.getElementById('panel-visual').style.display = 'none';
        document.getElementById('panel-code').style.display = 'none';
        document.getElementById('panel-preview').style.display = 'none';

        if (tab === 'visual') {
            document.getElementById('panel-visual').style.display = 'block';
            // Si venimos del código, actualizar TinyMCE
            if (currentTab === 'code' && editorInstance) {
                editorInstance.setContent(document.getElementById('body_html_raw').value);
            }
        } else if (tab === 'code') {
            document.getElementById('panel-code').style.display = 'block';
            // Sincronizar código desde TinyMCE
            if (editorInstance) {
                var html = editorInstance.getContent();
                // Formatear el HTML para legibilidad
                document.getElementById('body_html_raw').value = formatHtml(html);
            }
        } else if (tab === 'preview') {
            document.getElementById('panel-preview').style.display = 'block';
            // Generar preview
            if (editorInstance) {
                var content = editorInstance.getContent();
                // Resaltar variables en la preview
                var previewHtml = content.replace(
                    /\{\{(\w+)\}\}/g,
                    '<span class="var-highlight">{{$1}}</span>'
                );
                document.getElementById('preview-panel').innerHTML = previewHtml || '<p class="text-muted text-center">Sin contenido</p>';
            }
        }

        currentTab = tab;
    };

    // ==========================================
    // CLICK EN VARIABLES → INSERTAR EN TINYMCE
    // ==========================================
    document.querySelectorAll('.var-tag').forEach(function(tag) {
        tag.addEventListener('click', function() {
            var varText = this.getAttribute('data-var');

            if (currentTab === 'code') {
                // Insertar en textarea de código
                var raw = document.getElementById('body_html_raw');
                var start = raw.selectionStart;
                var end = raw.selectionEnd;
                var text = raw.value;
                raw.value = text.substring(0, start) + varText + text.substring(end);
                raw.selectionStart = raw.selectionEnd = start + varText.length;
                raw.focus();
            } else {
                // Insertar en TinyMCE (cambiar a visual si está en preview)
                if (currentTab === 'preview') {
                    switchTab('visual');
                }
                if (editorInstance) {
                    editorInstance.focus();
                    editorInstance.insertContent(varText);
                }
            }

            showToast(varText + ' insertada');

            // Efecto visual
            this.classList.add('bg-opacity-50');
            var self = this;
            setTimeout(function() { self.classList.remove('bg-opacity-50'); }, 300);
        });
    });

    // ==========================================
    // SINCRONIZAR AL ENVIAR FORMULARIO
    // ==========================================
    document.getElementById('templateForm').addEventListener('submit', function(e) {
        // Si estamos en tab de código, sincronizar al editor TinyMCE
        if (currentTab === 'code' && editorInstance) {
            editorInstance.setContent(document.getElementById('body_html_raw').value);
        }
        // TinyMCE sync automáticamente, pero forzamos por seguridad
        if (editorInstance) {
            editorInstance.save();
        }
    });

    // ==========================================
    // HELPERS
    // ==========================================
    function showToast(message) {
        var toast = document.getElementById('copy-toast');
        document.getElementById('toast-text').textContent = message;
        toast.style.display = 'block';
        setTimeout(function() { toast.style.display = 'none'; }, 1800);
    }

    function formatHtml(html) {
        // Formateo básico de HTML para legibilidad
        var formatted = '';
        var indent = 0;
        var tags = html.replace(/>\s*</g, '>\n<').split('\n');
        tags.forEach(function(tag) {
            if (tag.match(/^<\/\w/)) indent--;
            formatted += '  '.repeat(Math.max(0, indent)) + tag.trim() + '\n';
            if (tag.match(/^<\w[^>]*[^\/]>/) && !tag.match(/^<(br|hr|img|input|meta|link)/i)) indent++;
        });
        return formatted.trim();
    }
});
</script>

<?php include VIEW_PATH . "/layouts/footer.php"; ?>
