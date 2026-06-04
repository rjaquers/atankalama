<!DOCTYPE html>
<html lang='es'>
<head>
    <?php
    $page_title = 'Crear Producto';
    include 'views/layout/header.php'; // SOLO metadatos y links (sin <body> ni <nav>)

    $valueName = $_GET['prefill_name'] ?? '';

    ?>
</head>
<body>

<main class="container py-3 px-2 bg-gradient text-dark" style='background: linear-gradient(135deg, #5c6bc0 0%, #3949ab 100%); min-height: 100vh;'>
    <!-- Navbar (fuera del <head>) -->
    <?php include 'views/layout/navbar.php'; ?>
    <br>
    <div class='d-flex justify-content-between align-items-center mb-4'>
        <h2><i class='fas fa-plus me-2'></i><?=$page_title;?></h2>
        <a href='index.php' class='btn btn-secondary'>
            <i class='fas fa-arrow-left me-2'></i>Volver
        </a>
    </div>
    <!-- Acciones rápidas -->
    <br>


    <!-- Principals -->
    <div class="row   mb-12">
        <!-- Alertas -->
        <div class="col-12 ">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-warning text-white">
                    <i class="fas fa-bell me-2"></i>Crear producto
                </div>
                <div>
                    <div class='container mt-4'>
                        <h2 class='mb-4'>Agregar Nuevo Producto</h2>


                    </div>
                    <form action='index.php?page=products&action=create' method='POST' enctype='multipart/form-data' class='p-3 border rounded bg-light shadow-sm'>
                        <div class='row mb-3'>
                            <div class='col-md-6'>
                                <label for='name' class='form-label'>Nombre del Producto</label>
                                <input type='text'
                                       name='name'
                                       class='form-control'
                                       value="<?=isset($product) ? htmlspecialchars($product['name']) : ''?>"
                                       required>
                            </div>
                            <div class='col-md-6'>
                                <label for='unit' class='form-label'>Unidad</label>
                                <input type='text'
                                       name='unit'
                                       class='form-control'
                                       value="<?=isset($product) ? htmlspecialchars($product['unit']) : ''?>"
                                       required>
                            </div>
                            <div class='col-md-6'>
                                <label for='codigoBarra' class='form-label'>Código de Barra</label>
                                <input type='text'
                                       name='codigoBarra'
                                       class='form-control'
                                       value="<?=isset($product) ? htmlspecialchars($product['codigoBarra']) : ''?>">
                            </div>

                            <div class='col-md-6'>
                                <label for=vencimiento class='form-label'>Vencimiento</label>
                                <input type='date'
                                       name='vencimiento'
                                       class='form-control'
                                       value="<?=isset($product) ? htmlspecialchars($product['vencimiento']) : ''?>">
                            </div>
                        </div>

                        <div class='mb-3'>
                            <label for='description' class='form-label'>Descripción</label>

                            <textarea name='description'
                                      class='form-control'><?=isset($product) ? htmlspecialchars($product['description']) : ''?></textarea>

    </textarea>
                                <button type='button' class='btn btn-outline-secondary' id='btnVoice' title='Dictar descripción'> 🎤</button>
                            </div>

                        </div>

                        <div class='row mb-3'>
                            <div class='col-md-3'>
                                <label for='quantity' class='form-label'>Cantidad</label>
                                <input type='number'
                                       name='quantity'
                                       class='form-control'
                                       value="<?=isset($product) ? (int)$product['quantity'] : 0?>"
                                       required>
                            </div>
                            <div class='col-md-3'>
                                <label for='min_stock' class='form-label'>Stock mínimo</label>
                                <input type='number'
                                       name='min_stock'
                                       class='form-control'
                                       value="<?=isset($product) ? (int)$product['min_stock'] : 0?>">
                            </div>
                            <div class='col-md-3'>
                                <label for='category_id' class='form-label'>Categoría</label>
                                <select name='category_id' class='form-control'>
                                    <option value=''>Seleccione...</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?=$cat['id']?>"
                                                <?=(isset($product) && $product['category_id'] == $cat['id']) ? 'selected' : ''?>>
                                            <?=htmlspecialchars($cat['name'])?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="location_id" class="form-label">Ubicación</label>
                                <select name='location_id' class='form-control'>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?=$loc['id']?>"
                                                <?=(isset($product) && $product['location_id'] == $loc['id']) ? 'selected' : ''?>>
                                            <?=htmlspecialchars($loc['name'])?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class='form-check form-switch mb-3'>
                            <input class='form-check-input' type='checkbox' id='status' name='status' value='active' checked>
                            <label class='form-check-label' for='status'>Activo</label>
                        </div>


                        <div class='mb-3'>
                            <label class='form-label'>Fotos del producto</label>

                            <!-- Input real (oculto) -->
                            <input type='file'
                                   name='fotos[]'
                                   id='fotos'
                                   class='d-none'
                                   multiple
                                   accept='image/*'>

                            <div class='d-grid gap-2 d-md-flex'>
                                <!-- Botón tomar foto -->
                                <button type='button'
                                        class='btn btn-outline-primary'
                                        onclick='openCamera()'>
                                    <i class='fas fa-camera me-2'></i>Tomar foto
                                </button>

                                <!-- Botón seleccionar archivo -->
                                <button type='button'
                                        class='btn btn-outline-secondary'
                                        onclick='openGallery()'>
                                    <i class='fas fa-images me-2'></i>Seleccionar desde dispositivo
                                </button>
                            </div>

                            <small class='text-muted d-block mt-2'>
                                Puedes tomar una foto con la cámara o seleccionar imágenes desde tu dispositivo.
                            </small>
                        </div>


                        <!-- Previsualización -->
                        <div id="preview" class="d-flex flex-wrap gap-2 mb-3"></div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Guardar Producto
                            </button>
                            <a href="index.php?page=products" class="btn btn-secondary ms-2">
                                <i class="fas fa-arrow-left me-2"></i>Volver al Listado
                            </a>
                        </div>


                    </form>


                </div>
            </div>
        </div>

    </div>

    <?php include 'views/layout/footer.php'; ?>
</main>


<!--Adicional de la página -->
<script>
    document.getElementById('fotos').addEventListener('change', function (event) {
        const preview = document.getElementById('preview');
        preview.innerHTML = ''; // limpia previas
        const files = event.target.files;

        Array.from(files).forEach(file => {
            if (!file.type.startsWith('image/')) return;

            const reader = new FileReader();
            reader.onload = e => {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'rounded border';
                img.style.width = '120px';
                img.style.height = '120px';
                img.style.objectFit = 'cover';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    });
</script>

<script>
    function openCamera() {
        const input = document.getElementById('fotos');
        input.setAttribute('capture', 'environment'); // cámara trasera
        input.click();
    }

    function openGallery() {
        const input = document.getElementById('fotos');
        input.removeAttribute('capture'); // galería / archivos
        input.click();
    }
</script>

<script>
    /**
     * ==========================================
     * Reconocimiento de voz para descripción
     * ==========================================
     * - Activa el micrófono
     * - Convierte voz a texto
     * - Inserta texto en textarea description
     * - Permite activar y desactivar grabación
     */

    document.addEventListener('DOMContentLoaded', function() {

        const btn = document.getElementById('btnVoice');
        const textarea = document.getElementById('description');

        // Compatibilidad navegador
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        if (!SpeechRecognition) {
            btn.disabled = true;
            btn.innerText = 'No soportado';
            return;
        }

        const recognition = new SpeechRecognition();
        recognition.lang = 'es-CL';      // Español Chile
        recognition.continuous = true;   // Sigue escuchando
        recognition.interimResults = true; // Resultados parciales

        let isListening = false;

        /**
         * Evento cuando se recibe texto del micrófono
         */
        recognition.onresult = function(event) {
            let transcript = '';

            for (let i = event.resultIndex; i < event.results.length; i++) {
                transcript += event.results[i][0].transcript;
            }

            textarea.value += ' ' + transcript;
        };

        /**
         * Evento error
         */
        recognition.onerror = function(event) {
            console.error('Error reconocimiento:', event.error);
            isListening = false;
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-outline-secondary');
        };

        /**
         * Botón activar/desactivar micrófono
         */
        btn.addEventListener('click', function() {

            if (!isListening) {
                recognition.start();
                isListening = true;
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-danger');
            } else {
                recognition.stop();
                isListening = false;
                btn.classList.remove('btn-danger');
                btn.classList.add('btn-outline-secondary');
            }
        });

    });
</script>

</body>
</html>