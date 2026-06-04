<!--
  ===================================================
  = Proyecto: Hotel Atankalama - Sistema de Cocina  =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com.                   =
  = Fecha: <?= date('Y') ?>                  =
  ===================================================
-->
<?php
/**
 * Resumen:
 * Pantalla nueva (módulo independiente) para operar inventario por voz.
 * - Dos botones grandes: Ingreso (verde) y Retiro (rojo).
 * - Captura voz → texto → propone operación → confirmación → ejecuta.
 * - Optimizado para celular (botones grandes, poco texto).
 */
?>
<br>
<br>
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-lg-8 mx-auto">

            <div class="card shadow-sm">
                <div class="card-header">
                    <strong>Operación por Voz</strong>
                    <div class="text-muted small">Ingreso (supermercado) / Retiro (bodega) • 1 producto por frase</div>
                </div>

                <div class='d-flex justify-content-end gap-2 mb-3 flex-wrap'>

                    <button class='btn btn-outline-primary'
                            data-bs-toggle='modal'
                            data-bs-target='#modalProductos'>
                        📋 Ver productos disponibles
                    </button>

                    <button class='btn btn-outline-info btn-sm'
                            data-bs-toggle='modal'
                            data-bs-target='#modalAyudaVoz'>
                        ❓ ¿Cómo usar la voz?
                    </button>

                </div>


                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button id="btnIngreso" class="btn btn-success btn-lg py-3">
                            🟢 INGRESO (Sumar stock)
                        </button>

                        <button id="btnRetiro" class="btn btn-danger btn-lg py-3">
                            🔴 RETIRO (Restar stock)
                        </button>
                    </div>

                    <hr>

                    <div class="mb-2">
                        <label class="form-label"><strong>Texto reconocido</strong></label>
                        <textarea id="txtVoz" class="form-control" rows="2" placeholder="Aquí aparecerá lo dictado..."></textarea>
                        <div id='indicadorAccion' class='mt-2'></div>

                        <div class="text-muted small mt-1">Ej: “Agrego 15 yogurt” / “Saqué 10 detergente”</div>
                    </div>

                    <div id="panelResultado" class="mt-3" style="display:none;">
                        <div class="alert alert-info mb-2" id="resultadoInfo"></div>

                        <div class="d-grid gap-2 d-sm-flex">
                            <button id="btnConfirmar" class="btn btn-primary btn-lg flex-fill">
                                ✅ Confirmar
                            </button>
                            <button id="btnCancelar" class="btn btn-outline-secondary btn-lg flex-fill">
                                ✖ Cancelar
                            </button>
                        </div>
                    </div>

                    <div id="panelCrear" class="mt-3" style="display:none;">
                        <div class="alert alert-warning mb-2" id="crearInfo"></div>
                        <div class="d-grid gap-2 d-sm-flex">
                            <a id="btnCrearProducto" class="btn btn-warning btn-lg flex-fill" href="#">
                                ➕ Crear producto nuevo
                            </a>
                            <button id="btnCancelarCrear" class="btn btn-outline-secondary btn-lg flex-fill">
                                ✖ Cancelar
                            </button>
                        </div>
                    </div>

                    <div id="panelMsg" class="mt-3" style="display:none;">
                        <div class="alert mb-0" id="msgBox"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class='modal fade' id='modalProductos' tabindex='-1'>
    <div class='modal-dialog modal-lg modal-dialog-scrollable'>
        <div class='modal-content'>

            <div class='modal-header'>
                <h5 class='modal-title'>Productos disponibles</h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
            </div>

            <div class='modal-body'>

                <input type='text' id='buscarProducto' class='form-control mb-3'
                       placeholder='Escribe al menos 3 letras...'>

                <div class='table-responsive'>
                    <table class='table table-sm table-hover'>
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Stock</th>
                        </tr>
                        </thead>
                        <tbody id='tablaProductos'>
                        <tr>
                            <td colspan='3'>Cargando...</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>

<div class='modal fade' id='modalAyudaVoz' tabindex='-1'>
    <div class='modal-dialog modal-lg modal-dialog-scrollable'>
        <div class='modal-content'>

            <div class='modal-header'>
                <h5 class='modal-title'>🧠 Cómo usar la operación por voz</h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
            </div>

            <div class='modal-body'>

                <div class='row small'>
                    <div class='col-12 col-md-6'>
                        <strong class='text-success'>🟢 Ingreso (sumar stock)</strong>
                        <ul>
                            <li>Agrego</li>
                            <li>Agregué</li>
                            <li>Compré</li>
                            <li>Compre</li>
                        </ul>
                    </div>

                    <div class='col-12 col-md-6'>
                        <strong class='text-danger'>🔴 Retiro (restar stock)</strong>
                        <ul>
                            <li>Retiré</li>
                            <li>Retire</li>
                            <li>Saqué</li>
                            <li>Saque</li>
                        </ul>
                    </div>
                </div>

                <hr>

                <strong>🎤 Ejemplos válidos (toca para probar):</strong>

                <div class='d-flex flex-wrap gap-2 mt-3'>
                    <button class='btn btn-outline-secondary btn-sm ejemplo-voz'>
                        Agrego 10 yogurt
                    </button>

                    <button class='btn btn-outline-secondary btn-sm ejemplo-voz'>
                        Compré 5 arroz
                    </button>

                    <button class='btn btn-outline-secondary btn-sm ejemplo-voz'>
                        Retiré 3 detergente
                    </button>

                    <button class='btn btn-outline-secondary btn-sm ejemplo-voz'>
                        Saqué 2 leche
                    </button>
                </div>

            </div>

        </div>
    </div>
</div>


<script src="public/js/voice-stock.js?v=1"></script>

<style>
    /* Animación suave */
    .detect-success {
        animation: pulseGreen 0.4s ease-in-out;
        border: 2px solid #28a745 !important;
        background-color: #e9f9ee;
    }

    .detect-danger {
        animation: pulseRed 0.4s ease-in-out;
        border: 2px solid #dc3545 !important;
        background-color: #fdeaea;
    }

    @keyframes pulseGreen {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }

    @keyframes pulseRed {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }

    .badge-accion {
        font-size: 0.85rem;
    }
</style>

