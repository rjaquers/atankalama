<?php require VIEW_PATH . "/layouts/header.php"; ?>

<!-- jQuery + Summernote (editor HTML rico) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-bs5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-bs5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/lang/summernote-es-ES.min.js"></script>

<div class="mb-3 d-flex align-items-center justify-content-between">
  <a href="<?= BASE_URL ?>/univAdmin" class="btn btn-sm btn-outline-secondary">
    <i class="fa-solid fa-arrow-left"></i> Volver a Cursos
  </a>
  <span class="text-muted small"><i class="fa-solid fa-circle-info me-1"></i>Mínimo 1 página — no hay límite máximo.</span>
</div>

<div class="card mb-4">
  <div class="card-body bg-light d-flex justify-content-between align-items-center">
    <div>
      <h4 class="m-0">Gestión de Contenido: <?= htmlspecialchars($course['nombre']) ?></h4>
      <p class="text-muted mb-0 small"><?= count($pages) ?> página(s) definidas</p>
    </div>
    <button class="btn btn-success btn-sm" onclick="agregarPagina()">
      <i class="fa-solid fa-plus"></i> Agregar página
    </button>
  </div>
</div>

<div class="row">
  <!-- Lista de páginas -->
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
        <span>Páginas del Curso</span>
        <span class="badge bg-secondary" id="badge-total"><?= count($pages) ?></span>
      </div>
      <div class="list-group list-group-flush" id="lista-paginas">
        <?php foreach ($pages as $p): ?>
        <button type="button"
          class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
          id="btn-pag-<?= $p['orden'] ?>"
          onclick='editPage(<?= $p['orden'] ?>, <?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)'>
          <span>
            <span class="fw-bold me-1"><?= $p['orden'] ?>.</span>
            <?= htmlspecialchars($p['titulo']) ?>
          </span>
          <span class="badge bg-<?= $p['tipo'] === 'html' ? 'primary' : ($p['tipo'] === 'pdf' ? 'danger' : 'warning text-dark') ?> rounded-pill">
            <?= $p['tipo'] ?>
          </span>
        </button>
        <?php endforeach; ?>
        <?php if (empty($pages)): ?>
        <div class="list-group-item text-muted text-center py-4" id="empty-hint">
          <i class="fa-solid fa-plus-circle fa-2x mb-2 text-success d-block"></i>
          Haz clic en "Agregar página" para comenzar
        </div>
        <?php endif; ?>
      </div>
      <div class="card-footer">
        <button class="btn btn-success btn-sm w-100" onclick="agregarPagina()">
          <i class="fa-solid fa-plus"></i> Agregar página
        </button>
      </div>
    </div>
  </div>

  <!-- Editor -->
  <div class="col-md-8">
    <div class="card shadow-sm" id="editorCard" style="display:none;">
      <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
        <span id="editorTitle">Editor de Página</span>
        <button type="button" class="btn btn-outline-danger btn-sm" id="btnDeletePage"
                style="display:none;" onclick="confirmDeletePage()">
          <i class="fa-solid fa-trash"></i> Eliminar página
        </button>
      </div>
      <div class="card-body">
        <form id="formPage" action="<?= BASE_URL ?>/univAdmin/savePage"
              method="POST" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
          <input type="hidden" name="id" id="field_id" value="0">
          <input type="hidden" name="orden" id="field_orden" value="">

          <div class="row g-3 mb-3">
            <div class="col-md-8">
              <label class="form-label fw-bold">Título de la Página</label>
              <input type="text" name="titulo" id="field_titulo" class="form-control" required
                     placeholder="Ej: Introducción y conceptos básicos">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold">Tipo</label>
              <select name="tipo" id="field_tipo" class="form-select" onchange="cambiarTipo()">
                <option value="html">HTML / Texto</option>
                <option value="pdf">PDF</option>
                <option value="video">Video YouTube</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold">Tiempo mínimo (seg)</label>
              <input type="number" name="tiempo_minimo_segundos" id="field_tiempo"
                     class="form-control" value="0" min="0">
              <div class="form-text">0 = sin espera obligatoria</div>
            </div>
          </div>

          <!-- PANEL HTML -->
          <div id="panel_html">
            <label class="form-label fw-bold">Contenido</label>
            <textarea name="contenido_html" id="field_html" class="form-control" rows="14"></textarea>
          </div>

          <!-- PANEL PDF -->
          <div id="panel_pdf" style="display:none;">
            <label class="form-label fw-bold">Subir archivo PDF</label>
            <input type="file" name="archivo_pdf" id="field_pdf_file"
                   class="form-control mb-2" accept=".pdf,application/pdf">
            <div class="form-text mb-3">
              Sube un nuevo PDF <strong>o</strong> ingresa una URL externa abajo.
            </div>
            <label class="form-label fw-bold">URL del PDF (actual o externa)</label>
            <input type="url" name="contenido_pdf_url" id="field_pdf_url" class="form-control"
                   placeholder="https://... (déjalo vacío si subiste un archivo)">
            <div id="pdf_preview" class="mt-3" style="display:none;">
              <p class="small text-muted mb-1">Vista previa:</p>
              <iframe id="iframe_pdf" src="" width="100%" height="420px"
                      class="border rounded shadow-sm"></iframe>
            </div>
          </div>

          <!-- PANEL VIDEO -->
          <div id="panel_video" style="display:none;">
            <label class="form-label fw-bold">URL de YouTube</label>
            <input type="text" id="field_youtube_url" class="form-control mb-2"
                   placeholder="https://www.youtube.com/watch?v=... o youtu.be/..."
                   oninput="previewYoutube()">
            <input type="hidden" name="contenido_video" id="field_video_embed">
            <div id="video_preview" class="mt-3 ratio ratio-16x9" style="display:none;">
              <iframe id="iframe_youtube" src="" allowfullscreen
                      class="border rounded shadow-sm"></iframe>
            </div>
            <div class="form-text mt-2">
              <i class="fa-brands fa-youtube text-danger"></i>
              El iframe embed se guarda automáticamente al guardar la página.
            </div>
          </div>

          <div class="mt-4 pt-3 border-top text-end">
            <button type="submit" class="btn btn-success px-4">
              <i class="fa-solid fa-floppy-disk"></i> Guardar página
            </button>
          </div>
        </form>
      </div>
    </div>

    <div id="emptyState" class="text-center py-5 border rounded bg-white shadow-sm">
      <i class="fa-solid fa-arrow-left fa-3x text-muted mb-3 d-block"></i>
      <h5 class="text-muted">Selecciona o agrega una página para editar su contenido</h5>
    </div>
  </div>
</div>

<script>
const COURSE_ID = <?= (int)$course['id'] ?>;
const BASE_URL  = '<?= BASE_URL ?>';
let   nextOrden = <?= empty($pages) ? 1 : (max(array_column($pages, 'orden')) + 1) ?>;

/* ── Summernote ──────────────────────────────────────────── */
$(document).ready(function () {
    $('#field_html').summernote({
        lang: 'es-ES',
        height: 340,
        toolbar: [
            ['style',  ['bold','italic','underline','clear']],
            ['font',   ['fontsize']],
            ['color',  ['color']],
            ['para',   ['ul','ol','paragraph']],
            ['table',  ['table']],
            ['insert', ['link','picture','hr']],
            ['view',   ['codeview','fullscreen']]
        ],
        callbacks: {
            onImageUpload: function (files) {
                subirImagen(files[0], this);
            }
        }
    });
});

function subirImagen(file, editor) {
    const fd = new FormData();
    fd.append('file', file);
    fd.append('csrf', document.querySelector('input[name=csrf]').value);
    fetch(BASE_URL + '/univAdmin/uploadImagen', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.url) $(editor).summernote('insertImage', data.url);
            else alert('Error al subir imagen: ' + (data.error || 'desconocido'));
        })
        .catch(() => alert('Error de red al subir la imagen.'));
}

/* ── Cambiar tipo ─────────────────────────────────────────── */
function cambiarTipo() {
    const tipo = document.getElementById('field_tipo').value;
    document.getElementById('panel_html').style.display  = tipo === 'html'  ? '' : 'none';
    document.getElementById('panel_pdf').style.display   = tipo === 'pdf'   ? '' : 'none';
    document.getElementById('panel_video').style.display = tipo === 'video' ? '' : 'none';
}

/* ── Editar página existente ─────────────────────────────── */
function editPage(orden, data) {
    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('editorCard').style.display = '';
    document.getElementById('editorTitle').innerText = 'Editando Página ' + orden;
    document.getElementById('field_orden').value = orden;
    document.getElementById('field_id').value     = data.id;
    document.getElementById('field_titulo').value = data.titulo;
    document.getElementById('field_tipo').value   = data.tipo;
    document.getElementById('field_tiempo').value = data.tiempo_minimo_segundos;

    // Limpiar campos
    $('#field_html').summernote('code', '');
    document.getElementById('field_pdf_url').value     = '';
    document.getElementById('field_pdf_file').value    = '';
    document.getElementById('field_youtube_url').value = '';
    document.getElementById('field_video_embed').value = '';
    document.getElementById('pdf_preview').style.display   = 'none';
    document.getElementById('video_preview').style.display = 'none';

    if (data.tipo === 'html') {
        $('#field_html').summernote('code', data.contenido || '');
    } else if (data.tipo === 'pdf') {
        document.getElementById('field_pdf_url').value = data.contenido || '';
        mostrarPdfPreview(data.contenido);
    } else if (data.tipo === 'video') {
        const url = iframeToYoutubeUrl(data.contenido || '');
        document.getElementById('field_youtube_url').value = url;
        document.getElementById('field_video_embed').value = data.contenido || '';
        mostrarVideoPreview(extraerEmbedUrl(data.contenido || ''));
    }

    document.getElementById('btnDeletePage').style.display = '';
    cambiarTipo();
    document.querySelectorAll('#lista-paginas .list-group-item-action').forEach(b => b.classList.remove('active'));
    const btn = document.getElementById('btn-pag-' + orden);
    if (btn) btn.classList.add('active');
}

/* ── Nueva página ────────────────────────────────────────── */
function agregarPagina() {
    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('editorCard').style.display = '';
    document.getElementById('editorTitle').innerText = 'Nueva Página ' + nextOrden;
    document.getElementById('field_id').value     = 0;
    document.getElementById('field_orden').value  = nextOrden;
    document.getElementById('field_titulo').value = '';
    document.getElementById('field_tipo').value   = 'html';
    document.getElementById('field_tiempo').value = 0;
    $('#field_html').summernote('code', '');
    document.getElementById('field_pdf_url').value     = '';
    document.getElementById('field_pdf_file').value    = '';
    document.getElementById('field_youtube_url').value = '';
    document.getElementById('field_video_embed').value = '';
    document.getElementById('btnDeletePage').style.display  = 'none';
    document.getElementById('pdf_preview').style.display   = 'none';
    document.getElementById('video_preview').style.display = 'none';
    cambiarTipo();
    document.querySelectorAll('#lista-paginas .list-group-item-action').forEach(b => b.classList.remove('active'));
}

/* ── PDF preview ─────────────────────────────────────────── */
function mostrarPdfPreview(url) {
    const div = document.getElementById('pdf_preview');
    if (url && url.trim()) {
        document.getElementById('iframe_pdf').src = url;
        div.style.display = '';
    } else {
        div.style.display = 'none';
    }
}
document.getElementById('field_pdf_url').addEventListener('input', function () {
    mostrarPdfPreview(this.value);
});

/* ── YouTube ─────────────────────────────────────────────── */
function youtubeEmbedUrl(input) {
    if (!input) return '';
    let m = input.match(/youtu\.be\/([a-zA-Z0-9_-]{11})/);
    if (m) return 'https://www.youtube.com/embed/' + m[1];
    m = input.match(/[?&]v=([a-zA-Z0-9_-]{11})/);
    if (m) return 'https://www.youtube.com/embed/' + m[1];
    if (input.includes('youtube.com/embed/')) return input;
    return '';
}
function extraerEmbedUrl(html) {
    const m = html.match(/src="([^"]*youtube\.com\/embed\/[^"]*)"/);
    return m ? m[1] : '';
}
function iframeToYoutubeUrl(html) {
    const m = html.match(/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/);
    return m ? 'https://www.youtube.com/watch?v=' + m[1] : html;
}
function mostrarVideoPreview(embedUrl) {
    const div = document.getElementById('video_preview');
    if (embedUrl) {
        document.getElementById('iframe_youtube').src = embedUrl;
        div.style.display = '';
    } else {
        div.style.display = 'none';
    }
}
function previewYoutube() {
    const url   = document.getElementById('field_youtube_url').value;
    const embed = youtubeEmbedUrl(url);
    document.getElementById('field_video_embed').value = embed
        ? '<iframe src="' + embed + '" allowfullscreen style="width:100%;aspect-ratio:16/9;border-radius:8px;border:none;"></iframe>'
        : '';
    mostrarVideoPreview(embed);
}

/* ── Eliminar ────────────────────────────────────────────── */
function confirmDeletePage() {
    const id = document.getElementById('field_id').value;
    if (id > 0 && confirm('¿Eliminar esta página? El contenido se perderá.')) {
        window.location.href = BASE_URL + '/univAdmin/deletePage/' + id + '/' + COURSE_ID;
    }
}

/* ── Validación antes de enviar ─────────────────────────── */
document.getElementById('formPage').addEventListener('submit', function (e) {
    const tipo = document.getElementById('field_tipo').value;
    if (tipo === 'pdf') {
        const file = document.getElementById('field_pdf_file').files.length;
        const url  = document.getElementById('field_pdf_url').value.trim();
        if (!file && !url) {
            e.preventDefault();
            alert('Debes subir un archivo PDF o ingresar una URL.');
        }
    } else if (tipo === 'video') {
        if (!document.getElementById('field_video_embed').value) {
            e.preventDefault();
            alert('Ingresa una URL válida de YouTube.');
        }
    }
});
</script>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
