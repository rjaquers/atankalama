// tarjeta-modal.js — Modal de tarjeta compartido (kanban + planificador)
// Requiere: BASE (global), bootstrap, flatpickr

let _tarjetaModal = null;

const _hooks = {
    onLabelsChanged:    (tid) => {},
    onMiembrosChanged:  (tid) => {},
    onAdjuntosChanged:  (tid) => {},
    onChecklistChanged: (tid) => {},
    onCardSaved:        (tid, titulo, fecha) => { hideCardModal(); },
    onCardArchived:     (tid) => { hideCardModal(); },
};

function setModalHooks(hooks) {
    Object.assign(_hooks, hooks);
}

function initTarjetaModal() {
    const el = document.getElementById('tarjetaModal');
    if (!el) return;
    _tarjetaModal = new bootstrap.Modal(el);
    el.addEventListener('shown.bs.modal', () => {
        const dp = document.querySelector('.flatpickr-date');
        if (dp && window.flatpickr && !dp._flatpickr) {
            flatpickr(dp, { dateFormat: 'Y-m-d', allowInput: true,
                            locale: { firstDayOfWeek: 1 } });
        }
    });
}

function hideCardModal() {
    _tarjetaModal?.hide();
}

async function openCardModal(id) {
    if (!_tarjetaModal) return;
    document.getElementById('tarjetaModalContent').innerHTML =
        '<div class="modal-body text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div>';
    _tarjetaModal.show();
    // Cambiamos a formato ?id=X para que la ruta sea siempre 'tarjeta/modal'
    const html = await fetch(BASE + '/tarjeta/modal?id=' + id).then(r => r.text());
    document.getElementById('tarjetaModalContent').innerHTML = html;
    
    // Inicializar Flatpickr después de cargar el HTML del modal
    const dp = document.querySelector('.flatpickr-date');
    if (dp && window.flatpickr) {
        flatpickr(dp, { 
            dateFormat: 'Y-m-d', 
            allowInput: true,
            locale: 'es'
        });
    }

    bindModalActions(id);
}

function bindModalActions(tarjetaId) {
    const tid = parseInt(tarjetaId);

    // ── Guardar ──────────────────────────────────────────────────────────────
    const btnGuardar = document.querySelector('.btn-guardar');
    if (btnGuardar) {
        btnGuardar.addEventListener('click', async () => {
            const form   = document.getElementById('form-tarjeta');
            const titulo = form.querySelector('[name=titulo]').value.trim();
            const desc   = form.querySelector('[name=descripcion]').value.trim();
            const fecha  = form.querySelector('[name=fecha_vencimiento]').value;
            const completada = form.querySelector('[name=completada]')?.checked ? 1 : 0;
            if (!titulo) { form.querySelector('[name=titulo]').focus(); return; }

            btnGuardar.disabled = true;
            const res = await fetchJSON(BASE + '/tarjeta/guardar',
                { id: tid, titulo, descripcion: desc, fecha_vencimiento: fecha, completada });
            btnGuardar.disabled = false;

            if (res.ok) _hooks.onCardSaved(tid, titulo, fecha, completada);
            else alert(res.error || 'Error al guardar');
        });
    }

    // ── Archivar ──────────────────────────────────────────────────────────────
    const btnArchivar = document.querySelector('.btn-archivar');
    if (btnArchivar) {
        btnArchivar.addEventListener('click', async () => {
            if (!confirm('¿Archivar esta tarjeta? Quedará en la papelera.')) return;
            const res = await fetchJSON(BASE + '/tarjeta/archivar', { id: tid });
            if (res.ok) _hooks.onCardArchived(tid);
            else alert(res.error || 'Error al archivar');
        });
    }

    // ── Referencias ───────────────────────────────────────────────────────────
    document.querySelectorAll('.btn-del-ref').forEach(btn => {
        btn.addEventListener('click', async () => {
            const refId = parseInt(btn.dataset.id);
            const res   = await fetchJSON(BASE + '/referencia/eliminar', { id: refId });
            if (res.ok) document.getElementById('ref-' + refId)?.remove();
        });
    });

    const btnShowRef = document.getElementById('btn-show-ref');
    if (btnShowRef) {
        btnShowRef.addEventListener('click', () => {
            document.getElementById('ref-form').classList.remove('d-none');
            btnShowRef.classList.add('d-none');
        });
        document.getElementById('btn-cancel-ref')?.addEventListener('click', () => {
            document.getElementById('ref-form').classList.add('d-none');
            btnShowRef.classList.remove('d-none');
        });
        document.getElementById('ref-tablero')?.addEventListener('change', async (e) => {
            const tId  = parseInt(e.target.value);
            const selL = document.getElementById('ref-lista');
            selL.classList.add('d-none');
            selL.innerHTML = '<option value="">— Lista —</option>';
            if (!tId) return;
            // Cambiamos a formato ?tablero_id=X
            const res = await fetch(BASE + '/referencia/listas?tablero_id=' + tId).then(r => r.json());
            if (!res.ok) return;
            res.listas.forEach(l => {
                selL.innerHTML += `<option value="${l.id}">${escHtml(l.nombre)}</option>`;
            });
            selL.classList.remove('d-none');
        });
        document.getElementById('btn-do-ref')?.addEventListener('click', async () => {
            const tbId = parseInt(document.getElementById('ref-tablero').value);
            const lId  = parseInt(document.getElementById('ref-lista').value);
            if (!tbId || !lId) { alert('Selecciona tablero y lista'); return; }
            const btnDo = document.getElementById('btn-do-ref');
            btnDo.disabled = true;
            const res = await fetchJSON(BASE + '/referencia/crear', {
                tarjeta_id: tid, tablero_destino_id: tbId, lista_destino_id: lId, mensaje: '',
            });
            btnDo.disabled = false;
            if (!res.ok) { alert(res.error || 'Error al referenciar'); return; }
            document.getElementById('ref-form').classList.add('d-none');
            btnShowRef.classList.remove('d-none');
            document.getElementById('ref-tablero').value = '';
            const selL = document.getElementById('ref-lista');
            selL.innerHTML = '';
            selL.classList.add('d-none');
            document.getElementById('refs-lista').querySelector('p.text-muted')?.remove();
            const tbName = document.querySelector(`#ref-tablero option[value="${tbId}"]`)?.textContent || '';
            const lName  = document.querySelector(`#ref-lista option[value="${lId}"]`)?.textContent || '';
            const div    = document.createElement('div');
            div.className = 'd-flex align-items-center gap-2 mb-1';
            div.id = 'ref-' + res.id;
            div.innerHTML = `<span class="badge bg-light text-dark" style="font-size:.68rem">${escHtml(tbName)} › ${escHtml(lName)}</span>
                <button class="btn btn-link btn-sm text-muted p-0 btn-del-ref" data-id="${res.id}" title="Quitar"><i class="bi bi-x"></i></button>`;
            document.getElementById('refs-lista').appendChild(div);
            div.querySelector('.btn-del-ref').addEventListener('click', async () => {
                const r = await fetchJSON(BASE + '/referencia/eliminar', { id: res.id });
                if (r.ok) div.remove();
            });
        });
    }

    // ── Adjuntos ──────────────────────────────────────────────────────────────
    const adjuntoForm = document.getElementById('adjunto-form');
    if (adjuntoForm) {
        adjuntoForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd  = new FormData(adjuntoForm);
            const btn = adjuntoForm.querySelector('button[type=submit]');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            let res;
            try {
                res = await fetch(BASE + '/adjunto/subir', { method: 'POST', body: fd }).then(r => r.json());
            } catch { res = { ok: false, error: 'Error de red' }; }
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-upload"></i>';
            if (!res.ok) { alert(res.error || 'Error al subir archivo'); return; }
            adjuntoForm.reset();
            const lista = document.getElementById('adjuntos-lista');
            lista.querySelector('p.text-muted')?.remove();
            const url = BASE + '/' + res.ruta;
            const div = document.createElement('div');
            div.className = 'adjunto-item d-flex align-items-center gap-2 mb-1';
            div.id = 'adj-' + res.id;
            div.innerHTML = (res.tipo === 'imagen'
                ? `<a href="${url}" target="_blank"><img src="${url}" alt="" class="adj-thumb"></a>`
                : `<a href="${url}" target="_blank" class="text-danger"><i class="bi bi-file-earmark-pdf fs-4"></i></a>`)
                + `<div class="flex-grow-1 min-w-0"><div class="small text-truncate">${escHtml(res.nombre_original)}</div></div>
                   <button class="btn btn-link btn-sm text-muted p-0 btn-del-adj" data-id="${res.id}" title="Eliminar">
                     <i class="bi bi-x"></i>
                   </button>`;
            lista.appendChild(div);
            div.querySelector('.btn-del-adj')?.addEventListener('click', () => _eliminarAdjunto(res.id));
            _hooks.onAdjuntosChanged(tid);
        });
    }
    document.querySelectorAll('.btn-del-adj').forEach(btn => {
        btn.addEventListener('click', () => _eliminarAdjunto(parseInt(btn.dataset.id)));
    });

    // ── Etiquetas ──────────────────────────────────────────────────────────────
    document.querySelectorAll('.btn-etq').forEach(btn => {
        btn.addEventListener('click', async () => {
            const etqId = parseInt(btn.dataset.id);
            const res   = await fetchJSON(BASE + '/etiqueta/toggle', { tarjeta_id: tid, etiqueta_id: etqId });
            if (!res.ok) return;
            const on = res.action === 'added';
            btn.classList.toggle('etq-on', on);
            btn.style.opacity = on ? '1' : '.45';
            const icon = btn.querySelector('.bi-check-lg');
            if (on && !icon) {
                const i = document.createElement('i'); i.className = 'bi bi-check-lg ms-1'; btn.appendChild(i);
            } else if (!on && icon) { icon.remove(); }
            _hooks.onLabelsChanged(tid);
        });
    });

    // ── Miembros ──────────────────────────────────────────────────────────────
    document.querySelectorAll('.btn-avatar').forEach(btn => {
        btn.addEventListener('click', async () => {
            const uid = parseInt(btn.dataset.id);
            const res = await fetchJSON(BASE + '/miembro/toggle', { tarjeta_id: tid, usuario_id: uid });
            if (!res.ok) return;
            btn.classList.toggle('avatar-on', res.action === 'added');
            _hooks.onMiembrosChanged(tid);
        });
    });

    // ── Checklist ─────────────────────────────────────────────────────────────
    document.getElementById('btn-show-cl')?.addEventListener('click', () => {
        document.getElementById('new-cl-form').classList.remove('d-none');
        document.getElementById('btn-show-cl').classList.add('d-none');
        document.getElementById('new-cl-titulo').focus();
    });
    document.getElementById('btn-cancel-cl')?.addEventListener('click', () => {
        document.getElementById('new-cl-form').classList.add('d-none');
        document.getElementById('btn-show-cl').classList.remove('d-none');
        document.getElementById('new-cl-titulo').value = '';
    });

    const btnDoCl = document.getElementById('btn-do-cl');
    if (btnDoCl) {
        btnDoCl.addEventListener('click', async () => {
            const input  = document.getElementById('new-cl-titulo');
            const titulo = input.value.trim() || 'Lista de verificación';
            btnDoCl.disabled = true;
            let res;
            try {
                res = await fetchJSON(BASE + '/checklist/crear', { tarjeta_id: tid, titulo });
            } catch (e) {
                btnDoCl.disabled = false;
                alert('Error de red al crear checklist');
                return;
            }
            btnDoCl.disabled = false;
            if (!res.ok) { alert('Error al crear checklist'); return; }
            input.value = '';
            document.getElementById('new-cl-form').classList.add('d-none');
            document.getElementById('btn-show-cl').classList.remove('d-none');
            const wrap = document.getElementById('checklists-wrap');
            const div  = document.createElement('div');
            div.className = 'cl-block';
            div.id = 'cl-' + res.id;
            div.innerHTML = `
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="fw-semibold small"><i class="bi bi-check2-square me-1"></i>${escHtml(titulo)}</span>
                  <button class="btn btn-link btn-sm text-muted p-0 btn-del-cl" data-id="${res.id}">Eliminar</button>
                </div>
                <ul class="list-unstyled mb-1" id="cl-items-${res.id}" data-ok="0" data-total="0"></ul>
                <div class="cl-add-wrap">
                  <div class="cl-add-form d-none" id="cl-form-${res.id}">
                    <input type="text" class="form-control form-control-sm mb-1" placeholder="Texto del elemento...">
                    <div class="d-flex gap-1">
                      <button class="btn btn-primary btn-sm btn-add-item" data-cl="${res.id}">Agregar</button>
                      <button class="btn btn-outline-secondary btn-sm btn-cancel-item" data-cl="${res.id}"><i class="bi bi-x"></i></button>
                    </div>
                  </div>
                  <button class="btn btn-link btn-sm text-muted p-0 btn-show-item" data-cl="${res.id}">
                    <i class="bi bi-plus me-1"></i>Agregar elemento
                  </button>
                </div>`;
            wrap.appendChild(div);
            bindChecklistBlock(div, tid);
        });
    }
    document.querySelectorAll('.cl-block').forEach(b => bindChecklistBlock(b, tid));

    // ── Comentarios ──────────────────────────────────────────────────────────
    const btnSaveComment = document.getElementById('btn-save-comment');
    if (btnSaveComment) {
        btnSaveComment.addEventListener('click', async () => {
            const ta = document.getElementById('new-comment-text');
            const comentario = ta.value.trim();
            if (!comentario) { ta.focus(); return; }

            btnSaveComment.disabled = true;
            const res = await fetchJSON(BASE + '/comentario/crear', { tarjeta_id: tid, comentario });
            btnSaveComment.disabled = false;

            if (res.ok) {
                ta.value = '';
                const thread = document.getElementById('comments-thread');
                const div = document.createElement('div');
                div.className = 'd-flex gap-2 mb-3';
                div.id = 'comment-' + res.comentario.id;
                const ini = res.comentario.usuario.substring(0, 1).toUpperCase();
                div.innerHTML = `
                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;font-size:.75rem">${ini}</div>
                    <div class="flex-grow-1 bg-light p-2 rounded" style="position:relative">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold small">${escHtml(res.comentario.usuario)}</span>
                            <span class="text-muted" style="font-size:.65rem">${res.comentario.fecha}</span>
                        </div>
                        <div class="small text-dark" style="white-space:pre-wrap">${escHtml(res.comentario.texto)}</div>
                        <button class="btn btn-link btn-sm text-muted p-0 btn-del-comment" data-id="${res.comentario.id}" style="position:absolute;top:0;right:-25px" title="Eliminar"><i class="bi bi-x"></i></button>
                    </div>`;
                thread.insertBefore(div, thread.firstChild);
                div.querySelector('.btn-del-comment').addEventListener('click', () => _eliminarComentario(res.comentario.id));
                refreshKanbanComments(tid, 1);
            } else {
                alert(res.error || 'Error al guardar comentario');
            }
        });
    }

    document.querySelectorAll('.btn-del-comment').forEach(btn => {
        btn.addEventListener('click', () => _eliminarComentario(parseInt(btn.dataset.id)));
    });
}

async function _eliminarComentario(id) {
    if (!confirm('¿Eliminar este comentario?')) return;
    const res = await fetchJSON(BASE + '/comentario/eliminar', { id });
    if (res.ok) {
        document.getElementById('comment-' + id)?.remove();
    }
}

function refreshKanbanComments(tarjetaId, inc) {
    const card = document.querySelector(`.kanban-card[data-id="${tarjetaId}"]`);
    if (!card) return;
    const footer = card.querySelector('.kanban-card-footer');
    if (!footer) return;
    let ic = footer.querySelector('.ic-comm');
    if (!ic) {
        ic = document.createElement('span');
        ic.className = 'ic ic-comm';
        ic.innerHTML = `<i class="bi bi-chat-left-text"></i> <span class="cnt">0</span>`;
        footer.appendChild(ic);
    }
    const span = ic.querySelector('.cnt');
    span.textContent = Math.max(0, parseInt(span.textContent) + inc);
}

async function _eliminarAdjunto(adjId) {
    if (!confirm('¿Eliminar este adjunto?')) return;
    const res = await fetchJSON(BASE + '/adjunto/eliminar', { id: adjId });
    if (res.ok) document.getElementById('adj-' + adjId)?.remove();
}

function bindChecklistBlock(block, tid) {
    const clId = block.id.replace('cl-', '');

    block.querySelector('.btn-del-cl')?.addEventListener('click', async () => {
        if (!confirm('¿Eliminar este checklist?')) return;
        const res = await fetchJSON(BASE + '/checklist/eliminar', { id: parseInt(clId) });
        if (res.ok) { block.remove(); _hooks.onChecklistChanged(tid); }
    });

    block.querySelector('.btn-show-item')?.addEventListener('click', () => {
        block.querySelector('.cl-add-form').classList.remove('d-none');
        block.querySelector('.btn-show-item').classList.add('d-none');
        block.querySelector('.cl-add-form input').focus();
        // Inicializar flatpickr
        const dp = block.querySelector('.flatpickr-date');
        if (dp && window.flatpickr && !dp._flatpickr) {
            flatpickr(dp, { dateFormat: 'Y-m-d', allowInput: true, locale: 'es' });
        }
    });
    block.querySelector('.btn-cancel-item')?.addEventListener('click', () => {
        block.querySelector('.cl-add-form').classList.add('d-none');
        block.querySelector('.btn-show-item').classList.remove('d-none');
        block.querySelector('.cl-add-form input').value = '';
    });

    block.querySelector('.btn-add-item')?.addEventListener('click', async () => {
        const input = block.querySelector('.cl-add-form input');
        const texto = input.value.trim();
        const fecha = block.querySelector(`#cl-date-${clId}`)?.value || '';
        const pri   = block.querySelector(`#cl-pri-${clId}`)?.value || 'normal';
        const respId = block.querySelector(`#cl-resp-${clId}`)?.value || '';

        if (!texto) { input.focus(); return; }
        const btn = block.querySelector('.btn-add-item');
        btn.disabled = true;
        let res;
        try {
            res = await fetchJSON(BASE + '/checklist/addItem', { 
                checklist_id: parseInt(clId), 
                texto,
                fecha,
                prioridad: pri,
                responsable_id: respId
            });
        } catch (e) {
            btn.disabled = false;
            alert('Error de red al agregar elemento');
            return;
        }
        btn.disabled = false;
        if (!res.ok) { alert('Error al agregar elemento'); return; }
        
        input.value = '';
        if (block.querySelector(`#cl-date-${clId}`)) block.querySelector(`#cl-date-${clId}`).value = '';
        if (block.querySelector(`#cl-resp-${clId}`)) block.querySelector(`#cl-resp-${clId}`).value = '';

        const ul = document.getElementById('cl-items-' + clId);
        const li = document.createElement('li');
        li.className = 'd-flex align-items-start gap-2 mb-1';
        li.id = 'ci-' + res.id;

        const priColor = { baja: '#94a3b8', normal: '#3b82f6', alta: '#ef4444' }[res.prioridad] || '#3b82f6';
        const displayFecha = res.fecha ? res.fecha.substring(5).split('-').reverse().join('/') : '';
        
        // Obtener iniciales del responsable si fue seleccionado
        let respBadge = '';
        if (res.responsable_id) {
            const sel = block.querySelector(`#cl-resp-${clId} option[value="${res.responsable_id}"]`);
            const nombre = sel ? sel.textContent : '';
            const iniciales = nombre.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
            respBadge = `<span class="badge bg-light text-dark border" style="font-size:.6rem;padding:2px 4px" title="Responsable: ${escHtml(nombre)}">${escHtml(iniciales)}</span>`;
        }

        li.innerHTML = `
            <input type="checkbox" class="form-check-input mt-1 ck-toggle" data-id="${res.id}" data-cl="${clId}">
            <div class="flex-grow-1 min-w-0">
                <span class="small" id="ci-text-${res.id}">${escHtml(texto)}</span>
                <div class="d-flex align-items-center gap-2 mt-0">
                    ${res.fecha ? `<span class="text-muted" style="font-size:.65rem"><i class="bi bi-clock me-1"></i>${displayFecha}</span>` : ''}
                    <span class="badge p-0" style="width:12px;height:4px;background:${priColor};margin-top:2px" title="Prioridad: ${res.prioridad}"></span>
                    ${respBadge}
                </div>
            </div>
            <button class="btn btn-link btn-sm text-muted p-0 btn-del-item" data-id="${res.id}" data-cl="${clId}" title="Eliminar"><i class="bi bi-x"></i></button>`;
        
        ul.appendChild(li);
        ul.dataset.total = parseInt(ul.dataset.total) + 1;
        updateClProgress(clId);
        _hooks.onChecklistChanged(tid);
        bindItemEvents(li, clId, tid);
    });

    block.querySelectorAll('li').forEach(li => bindItemEvents(li, clId, tid));
}

function bindItemEvents(li, clId, tid) {
    const chk = li.querySelector('.ck-toggle');
    if (chk) {
        chk.addEventListener('change', async () => {
            const itemId = parseInt(chk.dataset.id);
            const res    = await fetchJSON(BASE + '/checklist/toggle', { item_id: itemId });
            if (!res.ok) { chk.checked = !chk.checked; return; }
            const span = document.getElementById('ci-text-' + itemId);
            if (span) {
                span.classList.toggle('text-decoration-line-through', chk.checked);
                span.classList.toggle('text-muted', chk.checked);
            }
            const ul = document.getElementById('cl-items-' + clId);
            ul.dataset.ok = [...ul.querySelectorAll('.ck-toggle:checked')].length;
            updateClProgress(clId);
            _hooks.onChecklistChanged(tid);
        });
    }
    const btnDel = li.querySelector('.btn-del-item');
    if (btnDel) {
        btnDel.addEventListener('click', async () => {
            const itemId = parseInt(btnDel.dataset.id);
            const res    = await fetchJSON(BASE + '/checklist/eliminarItem', { item_id: itemId });
            if (res.ok) {
                li.remove();
                const ul = document.getElementById('cl-items-' + clId);
                ul.dataset.total = parseInt(ul.dataset.total) - 1;
                ul.dataset.ok    = [...ul.querySelectorAll('.ck-toggle:checked')].length;
                updateClProgress(clId);
                _hooks.onChecklistChanged(tid);
            }
        });
    }
}

function updateClProgress(clId) {
    const ul = document.getElementById('cl-items-' + clId);
    if (!ul) return;
    const total = parseInt(ul.dataset.total) || 0;
    const ok    = parseInt(ul.dataset.ok)    || 0;
    const pct   = total ? Math.round(ok / total * 100) : 0;
    const bar   = document.getElementById('cl-bar-'  + clId);
    const pctEl = document.getElementById('cl-pct-'  + clId);
    if (bar)   { bar.style.width = pct + '%'; bar.style.background = pct >= 100 ? '#22c55e' : '#3b82f6'; }
    if (pctEl) pctEl.textContent = pct + '%';
}

async function fetchJSON(url, body) {
    try {
        // Probamos enviando como FormData en lugar de JSON crudo para evitar bloqueos de firewall (403)
        const fd = new FormData();
        for (const key in body) fd.append(key, body[key]);

        const response = await fetch(url, {
            method: 'POST',
            body: fd,
        });
        const text = await response.text();
        try {
            const data = JSON.parse(text);
            if (typeof data === 'object' && data !== null) {
                data.status = response.status;
            }
            return data;
        } catch (parseError) {
            console.error("Error parseando JSON. Respuesta recibida:", text);
            return { 
                ok: false, 
                error: 'Respuesta del servidor no es JSON válido', 
                details: text.substring(0, 200),
                status: response.status 
            };
        }
    } catch (error) {
        console.error("Error de red:", error);
        return { ok: false, error: 'Error de red al conectar con el servidor', details: error.message };
    }
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
