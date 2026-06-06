<?php $vista = __FILE__; ?>

<div class='row justify-content-center'>
    <div class='col-12 col-md-10 col-lg-8'>
        <div class='pro-card mt-3'>
            <div class='card-header bg-primary text-white d-flex justify-content-between align-items-center'>
                <span class='fw-semibold'>
                    <i class='fa-solid fa-temperature-high me-1'></i> Registrar Temperatura
                </span>
                <a href='index.php?route=listar' class='btn btn-light btn-sm'>
                    <i class='fa-solid fa-list me-1'></i> Ver Registros
                </a>
            </div>

            <div class='card-body p-3 p-md-4'>
                <form action='index.php?route=guardar' method='POST' enctype='multipart/form-data'>

                    <!-- Nombre -->
                    <div class='form-floating mb-3'>
                        <input type='text' name='nombre' id='nombreInput' class='form-control'
                            placeholder="Su nombre" required autocomplete="name">
                        <label for='nombreInput'>
                            <i class='fa-solid fa-user me-1 text-muted'></i> Nombre del Encargado
                        </label>
                    </div>

                    <!-- Establecimiento + Área -->
                    <div class='row g-3 mb-3'>
                        <div class='col-6'>
                            <div class='form-floating'>
                                <select id='establecimiento' class='form-select' required>
                                    <option value='' disabled selected>Seleccione...</option>
                                    <option value='Atankalama'>Atankalama</option>
                                    <option value='Atan Inn'>Atan Inn</option>
                                    <option value='Externo'>Externo</option>
                                </select>
                                <label for='establecimiento'>Establecimiento</label>
                            </div>
                        </div>

                        <div class='col-6'>
                            <div class='form-floating'>
                                <select id='area' class='form-select' required>
                                    <option value='' disabled selected>Seleccione...</option>
                                    <option value='Cocina'>Cocina</option>
                                    <option value='Comedor'>Comedor</option>
                                    <option value='Frio'>Frio</option>
                                    <option value='Otros'>Otros</option>
                                </select>
                                <label for='area'>Área</label>
                            </div>
                        </div>
                    </div>

                    <!-- Temperatura -->
                    <div class='form-floating mb-4'>
                        <input type='number' step='0.1' name='temperatura' id='temperatura'
                            class='form-control fw-bold fs-5 text-primary'
                            placeholder="0.0" required inputmode='decimal'
                            pattern='[0-9]+([.,][0-9]+)?'
                            oninput="this.value = this.value.replace(/[^0-9.,]/g, '').replace(',', '.');">
                        <label for='temperatura'>
                            <i class='fa-solid fa-thermometer-half me-1 text-muted'></i> Temperatura (°C)
                        </label>
                    </div>

                    <!-- Campo oculto con la combinación final -->
                    <input type='hidden' name='hotel' id='hotelFinal'>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const form = document.querySelector('form[action*="guardar"]');
                            const est = document.getElementById('establecimiento');
                            const area = document.getElementById('area');
                            const hotelFinal = document.getElementById('hotelFinal');

                            function actualizarHotel() {
                                const e = est.value.trim();
                                const a = area.value.trim();
                                hotelFinal.value = (e && a) ? `${e} ${a}` : '';
                            }

                            est.addEventListener('change', actualizarHotel);
                            area.addEventListener('change', actualizarHotel);

                            form.addEventListener('submit', function (ev) {
                                actualizarHotel();
                                if (!hotelFinal.value) {
                                    ev.preventDefault();
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Faltan datos',
                                        text: 'Debe seleccionar establecimiento y área.',
                                        confirmButtonColor: '#0d6efd'
                                    });
                                }
                            });
                        });
                    </script>

                    <!-- Evidencia Fotográfica -->
                    <div class='mb-4 p-3 rounded-4 border border-2' style='border-color: #e2e8f0 !important; background: #f8fafc;'>
                        <p class='text-muted fw-semibold mb-3 text-center' style='font-size: 0.9rem;'>
                            <i class="fa-solid fa-camera-retro me-1"></i> Evidencia Fotográfica
                        </p>

                        <!-- Inputs ocultos -->
                        <input type='file' id='fotoCamara' name='fotos[]'
                            accept='image/*' capture='environment' multiple class='d-none'>
                        <input type='file' id='fotoGaleria' name='fotos[]'
                            accept='image/*' multiple class='d-none'>

                        <!-- Botones de foto -->
                        <div class='d-flex justify-content-center gap-3'>
                            <button type='button'
                                class='btn btn-primary d-flex flex-column align-items-center justify-content-center'
                                style='width: 120px; height: 80px; font-size: 0.82rem;'
                                onclick="document.getElementById('fotoCamara').click()">
                                <i class='fa-solid fa-camera fa-xl mb-1'></i>
                                Tomar Foto
                            </button>

                            <button type='button'
                                class='btn btn-outline-primary d-flex flex-column align-items-center justify-content-center'
                                style='width: 120px; height: 80px; font-size: 0.82rem;'
                                onclick="document.getElementById('fotoGaleria').click()">
                                <i class='fa-solid fa-images fa-xl mb-1'></i>
                                Galería
                            </button>
                        </div>

                        <!-- Preview y contador -->
                        <div id='preview' class='mt-3 d-flex flex-wrap justify-content-center gap-2'></div>
                        <div id='fotoCount' class='text-primary fw-medium text-center mt-2' style='font-size: 0.85rem;'></div>
                    </div>

                    <!-- Botones de acción -->
                    <div class='d-grid gap-2'>
                        <button type='submit' class='btn btn-success btn-lg fw-bold' style='font-size: 1rem;'>
                            <i class='fa-solid fa-check-circle me-1'></i> Guardar Registro
                        </button>

                        <button type='button' id='btnReset' class='btn btn-outline-secondary'>
                            <i class='fa-solid fa-eraser me-1'></i> Limpiar formulario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btnReset = document.getElementById('btnReset');
        const form = document.querySelector('form[action*="guardar"]');
        const preview = document.getElementById('preview');
        const countText = document.getElementById('fotoCount');

        btnReset?.addEventListener('click', () => {
            if (!form) return;
            form.reset();
            if (preview) preview.innerHTML = '';
            if (countText) countText.textContent = '';

            Swal.fire({
                icon: 'info',
                title: 'Formulario limpio',
                text: 'Todos los campos y fotos han sido eliminados.',
                timer: 1800,
                showConfirmButton: false
            });
        });
    });
</script>
