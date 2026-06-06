<html lang='es'>

<head>
    <meta charset='UTF-8'>
    <title>Sistema de Temperaturas · Hotel Atankalama</title>
    <meta name='viewport' content='width=device-width, initial-scale=1, viewport-fit=cover'>

    <!-- Evitar cache en formularios -->
    <meta http-equiv='Cache-Control' content='no-store, no-cache, must-revalidate, max-age=0'>
    <meta http-equiv='Pragma' content='no-cache'>
    <meta http-equiv='Expires' content='0'>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='css/pro-max.css?v=<?= time(); ?>' rel='stylesheet'>
    <!-- FontAwesome -->
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css' rel='stylesheet'>
    <!-- SweetAlert2 -->
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script src='https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js'></script>

    <!-- PWA -->
    <link rel='manifest' href='manifest.json'>
    <meta name='theme-color' content='#0d6efd'>
    <link rel='apple-touch-icon' href='favicon/icon-192.png'>
    <meta name='apple-mobile-web-app-capable' content='yes'>
    <meta name='apple-mobile-web-app-status-bar-style' content='default'>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
        }

        .container-main {
            margin-top: 20px;
            margin-bottom: 40px;
            max-width: 1100px;
        }

        /* Botones Globales */
        .btn {
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 44px; /* touch target mínimo */
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Inputs */
        .form-control,
        .form-select {
            border-radius: 12px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        img.thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }

        img.thumb:hover {
            transform: scale(1.08);
        }

        /* SweetAlert overrides */
        div:where(.swal2-container) div:where(.swal2-popup) {
            border-radius: 20px;
            font-family: 'Outfit', sans-serif;
            padding: 2em;
        }

        /* Card header responsive */
        .card-header .btn-sm {
            font-size: 0.78rem;
            padding: 6px 12px;
            min-height: 36px;
        }

        @media (max-width: 480px) {
            .card-header {
                flex-direction: column;
                gap: 8px;
                align-items: flex-start !important;
            }

            .card-header .btn-sm {
                align-self: flex-end;
            }
        }
    </style>
</head>

<body>
    <div class='container container-main'>
        <?php
        echo isset($vistaContent)
            ? $vistaContent
            : '<div class="alert alert-danger">Error: no se pudo cargar la vista.</div>';
        ?>
    </div>

    <script>
        // ── Compresión client-side con Canvas API ─────────────────────────────
        const FOTO_MAX_MB  = 3;
        const FOTO_MAX_PX  = 1200;
        const FOTO_CALIDAD = 0.75;

        let fotosComprimidas = [];
        let comprimiendo     = false;

        async function procesarFotos(input) {
            if (!input.files.length) return;
            comprimiendo = true;
            const countText = document.getElementById('fotoCount');

            for (const file of input.files) {
                const sizeMB = file.size / (1024 * 1024);

                if (sizeMB > FOTO_MAX_MB) {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Foto muy grande',
                        html: `<b>${file.name}</b> pesa ${sizeMB.toFixed(1)} MB.<br>El límite es ${FOTO_MAX_MB} MB por foto.`,
                        confirmButtonColor: '#0d6efd'
                    });
                    continue;
                }

                if (countText) {
                    countText.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1 text-warning"></i> Comprimiendo...';
                }

                const blob = await comprimirImagen(file);
                fotosComprimidas.push(blob);
                actualizarPreview();
            }

            comprimiendo = false;
        }

        function comprimirImagen(file) {
            return new Promise((resolve) => {
                const reader = new FileReader();
                reader.onload = (evt) => {
                    const img = new Image();
                    img.onload = () => {
                        let w = img.naturalWidth;
                        let h = img.naturalHeight;
                        if (w > FOTO_MAX_PX) {
                            h = Math.round(h * FOTO_MAX_PX / w);
                            w = FOTO_MAX_PX;
                        }
                        const canvas = document.createElement('canvas');
                        canvas.width  = w;
                        canvas.height = h;
                        canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                        canvas.toBlob(resolve, 'image/jpeg', FOTO_CALIDAD);
                    };
                    img.src = evt.target.result;
                };
                reader.readAsDataURL(file);
            });
        }

        function actualizarPreview() {
            const preview   = document.getElementById('preview');
            const countText = document.getElementById('fotoCount');
            if (!preview) return;

            preview.innerHTML = '';
            fotosComprimidas.forEach((blob, i) => {
                const url  = URL.createObjectURL(blob);
                const kb   = Math.round(blob.size / 1024);
                const wrap = document.createElement('div');
                wrap.className = 'position-relative d-inline-block';
                wrap.style.margin = '4px';

                const img = document.createElement('img');
                img.src = url;
                img.className = 'img-thumbnail';
                img.style.cssText = 'width:80px;height:80px;object-fit:cover;border-radius:10px;display:block;';

                const badge = document.createElement('span');
                badge.className = 'position-absolute badge bg-success';
                badge.style.cssText = 'bottom:2px;right:2px;font-size:0.6rem;';
                badge.textContent = kb + ' KB';

                const btnX = document.createElement('button');
                btnX.type = 'button';
                btnX.className = 'position-absolute btn btn-danger p-0 d-flex align-items-center justify-content-center';
                btnX.style.cssText = 'top:-6px;left:-6px;width:20px;height:20px;border-radius:50%;font-size:0.75rem;';
                btnX.textContent = '×';
                btnX.addEventListener('click', () => {
                    fotosComprimidas.splice(i, 1);
                    actualizarPreview();
                });

                wrap.appendChild(btnX);
                wrap.appendChild(img);
                wrap.appendChild(badge);
                preview.appendChild(wrap);
            });

            if (countText) {
                const n = fotosComprimidas.length;
                countText.innerHTML = n === 0 ? '' :
                    `<i class="fa-solid fa-check-circle text-success me-1"></i>${n} ${n === 1 ? 'foto lista' : 'fotos listas'}`;
            }
        }

        // ── Eventos y envío con XHR + barra de progreso ───────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            const inputCamara  = document.getElementById('fotoCamara');
            const inputGaleria = document.getElementById('fotoGaleria');
            const form = document.querySelector('form[action*="guardar"]');

            inputCamara?.addEventListener('change',  () => procesarFotos(inputCamara));
            inputGaleria?.addEventListener('change', () => procesarFotos(inputGaleria));

            document.getElementById('btnReset')?.addEventListener('click', () => {
                fotosComprimidas = [];
            });

            form?.addEventListener('submit', (e) => {
                e.preventDefault();

                if (comprimiendo) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Espera un momento',
                        text: 'Las fotos se están comprimiendo. Intenta en unos segundos.',
                        timer: 2200,
                        showConfirmButton: false
                    });
                    return;
                }

                // El listener de temperatura_form.php ya actualizó hotelFinal
                const hotelFinal = document.getElementById('hotelFinal');
                if (!hotelFinal || !hotelFinal.value) return;

                const fd = new FormData(form);
                fd.delete('fotos[]');
                fotosComprimidas.forEach((blob, i) => fd.append('fotos[]', blob, `foto_${i + 1}.jpg`));

                Swal.fire({
                    title: 'Guardando registro...',
                    html: `<p class="text-muted small mb-2" id="swalStatus">Preparando...</p>
                           <div class="progress" style="height:18px;border-radius:9px;">
                             <div id="swalBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                  role="progressbar" style="width:0%;transition:width 0.25s ease;"></div>
                           </div>`,
                    showConfirmButton: false,
                    allowOutsideClick: false
                });

                // Pre-calcular byte offset de inicio de cada foto en el multipart
                const fotoTamanos = fotosComprimidas.map(b => b.size);
                const fotoStarts  = [];
                let   fotoAcum    = 0;
                for (const t of fotoTamanos) { fotoStarts.push(fotoAcum); fotoAcum += t; }
                const nFotos = fotosComprimidas.length;

                const xhr = new XMLHttpRequest();
                xhr.open('POST', form.action);

                xhr.upload.addEventListener('progress', (ev) => {
                    if (!ev.lengthComputable) return;
                    const bar    = document.getElementById('swalBar');
                    const status = document.getElementById('swalStatus');

                    if (bar) bar.style.width = Math.round(ev.loaded / ev.total * 100) + '%';
                    if (!status) return;

                    if (nFotos === 0) {
                        status.textContent = `Subiendo... ${Math.round(ev.loaded / ev.total * 100)}%`;
                        return;
                    }

                    // Determinar qué foto se está enviando según bytes acumulados
                    let idx = nFotos - 1;
                    for (let i = 0; i < nFotos; i++) {
                        if (ev.loaded < fotoStarts[i] + fotoTamanos[i]) { idx = i; break; }
                    }
                    const pctFoto = Math.min(100, Math.round((ev.loaded - fotoStarts[idx]) / fotoTamanos[idx] * 100));
                    status.textContent = `Subiendo foto ${idx + 1} de ${nFotos}... ${pctFoto}%`;
                });

                xhr.addEventListener('load', () => {
                    if (xhr.status === 200) {
                        fotosComprimidas = [];
                        form.reset();
                        actualizarPreview();
                        Swal.fire({
                            icon: 'success',
                            title: '¡Registro guardado!',
                            text: 'La temperatura y las fotos fueron guardadas correctamente.',
                            confirmButtonColor: '#0d6efd',
                            timer: 2500,
                            timerProgressBar: true
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al guardar',
                            text: `Error del servidor (${xhr.status}). Intenta nuevamente.`,
                            confirmButtonColor: '#0d6efd'
                        });
                    }
                });

                xhr.addEventListener('error', () => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de red',
                        text: 'No se pudo conectar al servidor. Verifica tu conexión.',
                        confirmButtonColor: '#0d6efd'
                    });
                });

                xhr.send(fd);
            });
        });

        // Alerta de éxito para recarga directa con ?success=1 (fallback)
        if (new URLSearchParams(window.location.search).get('success') === '1') {
            Swal.fire({
                icon: 'success',
                title: '¡Registro guardado!',
                text: 'La temperatura y las fotos fueron guardadas correctamente.',
                confirmButtonColor: '#0d6efd',
                timer: 2500,
                timerProgressBar: true
            }).then(() => {
                const url = new URL(window.location);
                url.searchParams.delete('success');
                window.history.replaceState({}, document.title, url.toString());
            });
        }
    </script>

    <!-- Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('./sw.js').catch(() => {});
        }
    </script>

    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'></script>
</body>

</html>
