/**
 * Tablero Kanban - Lógica del Cliente
 */

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar modal de tarjeta
    if (typeof initTarjetaModal === 'function') {
        initTarjetaModal();
    }
    
    const modalArchivadasEl = document.getElementById('modalArchivadas');
    const modalArchivadas = modalArchivadasEl ? new bootstrap.Modal(modalArchivadasEl) : null;

    if (typeof setModalHooks === 'function') {
        setModalHooks({
            onLabelsChanged: (tid) => refreshKanbanLabels(tid),
            onMiembrosChanged: (tid) => refreshKanbanMiembros(tid),
            onAdjuntosChanged: (tid) => refreshKanbanAdjuntos(tid),
            onChecklistChanged: (tid) => refreshKanbanChecklist(tid),
            onCardSaved: (tid, titulo, fecha, completada) => {
                const card = document.querySelector(`.kanban-card[data-id="${tid}"]`);
                if (card) {
                    card.querySelector('.kanban-card-title').textContent = titulo;
                    card.classList.toggle('is-completed', !!completada);
                }
                if (typeof hideCardModal === 'function') hideCardModal();
            },
            onCardArchived: (tid) => {
                const card = document.querySelector(`.kanban-card[data-id="${tid}"]`);
                if (card) {
                    const lid = card.closest('.kanban-col-cards').dataset.lista;
                    card.remove();
                    actualizarContador(lid);
                    toggleEmptyHint(lid);
                }
                if (typeof hideCardModal === 'function') hideCardModal();
            },
        });
    }

    document.querySelectorAll('.kanban-card:not(.ref-card)').forEach(bindCardClick);

    document.querySelectorAll('.ref-card').forEach(card => {
        card.addEventListener('click', () => {
            if (typeof openCardModal === 'function') openCardModal(card.dataset.refId);
        });
    });

    if (window.PUEDE_EDITAR && typeof Sortable !== 'undefined') {
        document.querySelectorAll('.kanban-col-cards').forEach(col => {
            Sortable.create(col, {
                group: 'kanban', animation: 150,
                ghostClass: 'card-ghost', chosenClass: 'card-chosen',
                onEnd: handleDragEnd,
            });
        });
    }

    // Lógica de tarjetas archivadas
    document.getElementById('btn-ver-archivadas')?.addEventListener('click', async () => {
        const res = await fetch(BASE + '/tablero/archivadas?id=' + TABLERO_ID).then(r => r.json());
        const lista = document.getElementById('lista-archivadas');
        const msg = document.getElementById('msg-sin-archivadas');

        if (!lista || !msg) return;

        lista.innerHTML = '';
        if (res.ok && res.archivadas.length > 0) {
            msg.classList.add('d-none');
            res.archivadas.forEach(t => {
                const item = document.createElement('div');
                item.className = 'list-group-item d-flex align-items-center justify-content-between py-3';
                item.innerHTML = `
          <div>
            <div class="fw-bold mb-0">#${t.numero} - ${escHtml(t.titulo)}</div>
            <small class="text-muted">Lista original: ${escHtml(t.lista_nombre)}</small>
          </div>
          <button class="btn btn-sm btn-outline-primary btn-restaurar" data-id="${t.id}">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Restaurar
          </button>
        `;
                lista.appendChild(item);
            });

            // Evento restaurar
            lista.querySelectorAll('.btn-restaurar').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const tid = btn.dataset.id;
                    btn.disabled = true;
                    if (typeof fetchJSON === 'function') {
                        const r = await fetchJSON(BASE + '/tarjeta/desarchivar', { id: tid });
                        if (r.ok) {
                            location.reload();
                        } else {
                            alert('Error al restaurar: ' + (r.error || 'Desconocido'));
                            btn.disabled = false;
                        }
                    }
                });
            });
        } else {
            msg.classList.remove('d-none');
        }
        modalArchivadas?.show();
    });

    /* ── Agregar tarjeta ─────────────────────────────────── */
    document.querySelectorAll('.btn-show-add').forEach(btn => {
        btn.addEventListener('click', () => {
            const lid = btn.dataset.lista;
            document.getElementById('add-form-' + lid).classList.remove('d-none');
            btn.classList.add('d-none');
            document.querySelector('#add-form-' + lid + ' textarea').focus();
        });
    });

    document.querySelectorAll('.btn-cancel-add').forEach(btn => {
        btn.addEventListener('click', () => closeAddForm(btn.dataset.lista));
    });

    document.querySelectorAll('.btn-add-card').forEach(btn => {
        btn.addEventListener('click', async () => {
            const lid = btn.dataset.lista;
            const ta = document.querySelector('#add-form-' + lid + ' textarea');
            const titulo = ta.value.trim();
            if (!titulo) { ta.focus(); return; }
            btn.disabled = true;
            try {
                if (typeof fetchJSON === 'function') {
                    const res = await fetchJSON(BASE + '/tarjeta/crear', { lista_id: parseInt(lid), tablero_id: TABLERO_ID, titulo });
                    btn.disabled = false;
                    if (res.ok) {
                        const col = document.getElementById('cards-' + lid);
                        col.querySelector('.col-empty-hint')?.remove();
                        const div = buildCardEl(res.tarjeta);
                        col.appendChild(div);
                        bindCardClick(div);
                        actualizarContador(lid);
                        closeAddForm(lid);
                    } else {
                        alert('Error: ' + (res.error || 'No se pudo crear la tarjeta') +
                            ' (Status: ' + (res.status || 'unknown') + ')\n\nDetalle: ' + (res.details || 'Sin detalles'));
                    }
                }
            } catch (e) {
                btn.disabled = false;
                alert('Error de red o del servidor: ' + e.message);
                console.error(e);
            }
        });
    });

    /* ── Gestión de columnas (listas) ─────────────────────────── */
    document.querySelectorAll('.btn-del-lista').forEach(bindDelLista);

    // Mostrar/ocultar formulario de nueva columna
    document.getElementById('btn-show-col')?.addEventListener('click', () => {
        document.getElementById('add-col-form').classList.remove('d-none');
        document.getElementById('btn-show-col').classList.add('d-none');
        document.getElementById('add-col-input').focus();
    });
    
    document.getElementById('btn-cancel-col')?.addEventListener('click', cerrarFormCol);
    
    document.getElementById('add-col-input')?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') document.getElementById('btn-do-col')?.click();
        if (e.key === 'Escape') cerrarFormCol();
    });

    document.getElementById('btn-do-col')?.addEventListener('click', async () => {
        const input = document.getElementById('add-col-input');
        const nombre = input.value.trim();
        if (!nombre) { input.focus(); return; }
        const btn = document.getElementById('btn-do-col');
        btn.disabled = true;
        if (typeof fetchJSON === 'function') {
            const res = await fetchJSON(BASE + '/lista/crear', { tablero_id: TABLERO_ID, nombre });
            btn.disabled = false;
            if (!res.ok) { alert(res.error || 'Error al crear columna'); return; }

            const newCol = buildColEl(res.lista);
            document.getElementById('col-add-lista').insertAdjacentElement('beforebegin', newCol);

            if (window.PUEDE_EDITAR && typeof Sortable !== 'undefined') {
                Sortable.create(newCol.querySelector('.kanban-col-cards'), {
                    group: 'kanban', animation: 150,
                    ghostClass: 'card-ghost', chosenClass: 'card-chosen',
                    onEnd: handleDragEnd,
                });
            }
            cerrarFormCol();
        }
    });

    /* ── Panel de fondo ───────────────────────────────────── */
    const btnFondo = document.getElementById('btn-fondo');
    const panelFondo = document.getElementById('panel-fondo');
    if (btnFondo && panelFondo) {
        btnFondo.addEventListener('click', (e) => {
            e.stopPropagation();
            panelFondo.classList.toggle('d-none');
        });

        document.addEventListener('click', (e) => {
            if (!panelFondo.contains(e.target) && e.target !== btnFondo) {
                panelFondo.classList.add('d-none');
            }
        });

        panelFondo.querySelectorAll('.fondo-swatch, .fondo-foto').forEach(el => {
            el.addEventListener('click', () => aplicarFondo(el.dataset.color, el.dataset.img));
        });

        document.getElementById('btn-sin-foto')?.addEventListener('click', () => {
            const colorOriginal = document.querySelector('.tablero-dot')?.style.backgroundColor || '#1e3a5f';
            aplicarFondo(colorOriginal, '');
        });
    }

    /* ── Menú Contextual de Tarjetas ────────────────────────── */
    const ctxMenu = document.getElementById('card-context-menu');
    let currentCardId = null;
    let tableroMiembros = null;

    document.addEventListener('contextmenu', async (e) => {
        const card = e.target.closest('.kanban-card:not(.ref-card)');
        if (!card) {
            ctxMenu?.classList.add('d-none');
            return;
        }

        e.preventDefault();
        currentCardId = card.dataset.id;

        if (ctxMenu) {
            ctxMenu.classList.remove('d-none');
            
            // Posicionamiento inteligente
            const menuWidth = 180;
            const menuHeight = 180;
            let x = e.clientX;
            let y = e.clientY;

            if (x + menuWidth * 2 > window.innerWidth) x -= menuWidth;
            if (y + menuHeight > window.innerHeight) y -= (menuHeight / 2);

            ctxMenu.style.left = x + 'px';
            ctxMenu.style.top = y + 'px';

            // Actualizar texto del item "completar"
            const itemComp = document.getElementById('ctx-completar');
            if (itemComp) {
                const isComp = card.classList.contains('is-completed');
                itemComp.querySelector('span').textContent = isComp ? 'Marcar como pendiente' : 'Marcar como terminada';
                itemComp.querySelector('i').className = isComp ? 'bi bi-arrow-counterclockwise me-2' : 'bi bi-check-circle me-2';
            }

            // Cargar miembros si es la primera vez o actualizar lista
            await renderCtxMiembros(card);
        }
    });

    async function renderCtxMiembros(card) {
        const listWrap = document.getElementById('ctx-miembros-list');
        if (!listWrap) return;

        if (!tableroMiembros) {
            const res = await fetch(BASE + '/tablero/miembros?id=' + TABLERO_ID).then(r => r.json());
            if (res.ok) tableroMiembros = res.miembros;
        }

        if (!tableroMiembros) {
            listWrap.innerHTML = '<div class="p-2 text-center text-danger small">Error al cargar</div>';
            return;
        }

        // Obtener miembros actuales de la tarjeta (desde los avatares en el DOM)
        const assignedIds = [...card.querySelectorAll('.kanban-avatar')].map(av => {
            // Buscamos el ID en los datos si estuviera, o por título si no.
            // Para ser más precisos, si tableroMiembros ya está cargado, comparamos nombres.
            return av.getAttribute('title');
        });

        listWrap.innerHTML = tableroMiembros.map(m => {
            const fullName = `${m.nombre} ${m.apellido}`;
            const isAssigned = assignedIds.includes(fullName);
            return `
                <div class="ctx-mbr-item ${isAssigned ? 'is-assigned' : ''}" data-uid="${m.id}">
                    <i class="bi bi-person me-2"></i>
                    <span>${escHtml(m.nombre)}</span>
                    ${isAssigned ? '<i class="bi bi-check text-primary"></i>' : ''}
                </div>
            `;
        }).join('');

        // Eventos para asignar
        listWrap.querySelectorAll('.ctx-mbr-item').forEach(item => {
            item.addEventListener('click', async (e) => {
                e.stopPropagation();
                const uid = item.dataset.uid;
                if (typeof fetchJSON === 'function') {
                    const res = await fetchJSON(BASE + '/miembro/toggle', { tarjeta_id: currentCardId, usuario_id: uid });
                    if (res.ok) {
                        refreshKanbanMiembros(currentCardId);
                        // Cerrar menú o refrescar lista interna
                        ctxMenu.classList.add('d-none');
                    }
                }
            });
        });
    }

    document.addEventListener('click', (e) => {
        if (!ctxMenu?.contains(e.target)) {
            ctxMenu?.classList.add('d-none');
        }
    });

    document.getElementById('ctx-abrir')?.addEventListener('click', () => {
        if (currentCardId) openCardModal(currentCardId);
        ctxMenu?.classList.add('d-none');
    });

    document.getElementById('ctx-completar')?.addEventListener('click', async () => {
        if (!currentCardId) return;
        const card = document.querySelector(`.kanban-card[data-id="${currentCardId}"]`);
        const wasCompleted = card.classList.contains('is-completed');
        const newState = wasCompleted ? 0 : 1;

        if (typeof fetchJSON === 'function') {
            const res = await fetchJSON(BASE + '/tarjeta/completar', { id: currentCardId, completada: newState });
            if (res.ok) {
                card.classList.toggle('is-completed', !!newState);
            } else {
                alert('Error: ' + (res.error || 'No se pudo actualizar el estado'));
            }
        }
        ctxMenu?.classList.add('d-none');
    });

    document.getElementById('ctx-archivar')?.addEventListener('click', async () => {
        if (!currentCardId) return;
        if (!confirm('¿Seguro que deseas archivar esta tarjeta?')) return;

        if (typeof fetchJSON === 'function') {
            const res = await fetchJSON(BASE + '/tarjeta/archivar', { id: currentCardId });
            if (res.ok) {
                const card = document.querySelector(`.kanban-card[data-id="${currentCardId}"]`);
                if (card) {
                    const lid = card.closest('.kanban-col-cards').dataset.lista;
                    card.remove();
                    actualizarContador(lid);
                    toggleEmptyHint(lid);
                }
            } else {
                alert('Error: ' + (res.error || 'No se pudo archivar'));
            }
        }
        ctxMenu?.classList.add('d-none');
    });
});

function closeAddForm(lid) {
    const form = document.getElementById('add-form-' + lid);
    const btn = document.getElementById('btn-show-add-' + lid);
    if (form) form.classList.add('d-none');
    const ta = document.querySelector('#add-form-' + lid + ' textarea');
    if (ta) ta.value = '';
    if (btn) btn.classList.remove('d-none');
}

function bindCardClick(card) {
    card.addEventListener('click', () => {
        if (typeof openCardModal === 'function') openCardModal(card.dataset.id);
    });
}

async function handleDragEnd(evt) {
    const card = evt.item;
    const tarjetaId = parseInt(card.dataset.id);
    const lidDest = evt.to.dataset.lista;
    const lidOrig = evt.from.dataset.lista;
    const cards = [...evt.to.querySelectorAll('.kanban-card')];
    const idx = cards.indexOf(card);
    const prevId = idx > 0 ? parseInt(cards[idx - 1].dataset.id) : null;
    const nextId = idx < cards.length - 1 ? parseInt(cards[idx + 1].dataset.id) : null;

    if (typeof fetchJSON === 'function') {
        const res = await fetchJSON(BASE + '/tarjeta/mover', {
            id: tarjetaId, lista_id: parseInt(lidDest), prev_id: prevId, next_id: nextId,
        });
        if (!res.ok) {
            alert(res.error || 'Error al mover tarjeta');
            evt.from.insertBefore(card, evt.from.children[evt.oldIndex] || null);
        } else if (lidOrig !== lidDest) {
            actualizarContador(lidOrig);
            actualizarContador(lidDest);
            toggleEmptyHint(lidOrig);
            toggleEmptyHint(lidDest);
        }
    }
}

function refreshKanbanLabels(tarjetaId) {
    const card = document.querySelector(`.kanban-card[data-id="${tarjetaId}"]`);
    if (!card) return;
    const activeBtns = [...document.querySelectorAll('.btn-etq.etq-on')];
    let wrap = card.querySelector('.kanban-labels');
    if (activeBtns.length === 0) { if (wrap) wrap.remove(); return; }
    if (!wrap) {
        wrap = document.createElement('div');
        wrap.className = 'kanban-labels';
        card.insertBefore(wrap, card.firstChild);
    }
    wrap.innerHTML = activeBtns.map(b =>
        `<div class="kanban-label" style="background:${b.dataset.color}" title="${escHtml(b.dataset.nombre)}"></div>`
    ).join('');
}

function refreshKanbanMiembros(tarjetaId) {
    const card = document.querySelector(`.kanban-card[data-id="${tarjetaId}"]`);
    if (!card) return;

    const avatarColors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'];
    const activeBtns = [...document.querySelectorAll('.btn-avatar.avatar-on')];

    let row = card.querySelector('.kanban-card-avatars-row');
    if (activeBtns.length === 0) {
        if (row) row.remove();
        return;
    }
    if (!row) {
        row = document.createElement('div');
        row.className = 'kanban-card-avatars-row';
        const title = card.querySelector('.kanban-card-title');
        title.insertAdjacentElement('afterend', row);
    }
    row.innerHTML = activeBtns.map(btn => {
        const uid = parseInt(btn.dataset.id || 0);
        const name = btn.getAttribute('title') || '';
        const parts = name.trim().split(/\s+/);
        const ini = ((parts[0]?.[0] || '') + (parts[1]?.[0] || parts[0]?.[1] || '')).toUpperCase();
        const color = avatarColors[uid % avatarColors.length];
        return `<div class="kanban-avatar" style="background:${color}" title="${escHtml(name)}">${ini}</div>`;
    }).join('');
}

function refreshKanbanAdjuntos(tarjetaId) {
    const card = document.querySelector(`.kanban-card[data-id="${tarjetaId}"]`);
    if (!card) return;
    const cnt = document.querySelectorAll('.adjunto-item').length;
    const footer = card.querySelector('.kanban-card-footer');
    if (!footer) return;
    let ic = footer.querySelector('.ic-adj');
    if (cnt > 0) {
        if (!ic) { ic = document.createElement('span'); ic.className = 'ic ic-adj'; footer.appendChild(ic); }
        ic.innerHTML = `<i class="bi bi-paperclip"></i> ${cnt}`;
    } else if (ic) { ic.remove(); }
}

function refreshKanbanChecklist(tarjetaId) {
    const card = document.querySelector(`.kanban-card[data-id="${tarjetaId}"]`);
    if (!card) return;
    let total = 0, ok = 0;
    document.querySelectorAll('[id^="cl-items-"]').forEach(ul => {
        total += parseInt(ul.dataset.total) || 0;
        ok += parseInt(ul.dataset.ok) || 0;
    });
    const footer = card.querySelector('.kanban-card-footer');
    if (!footer) return;
    let ic = footer.querySelector('.ic-ck');
    if (total > 0) {
        if (!ic) { ic = document.createElement('span'); ic.className = 'ic ic-ck'; footer.insertBefore(ic, footer.firstChild); }
        const pct = Math.round(ok / total * 100);
        ic.innerHTML = `<i class="bi bi-check2-square"></i> ${ok}/${total}
      <span class="ck-bar-wrap"><span class="ck-bar" style="width:${pct}%"></span></span>`;
    } else if (ic) { ic.remove(); }
}

function toggleEmptyHint(lid) {
    const col = document.getElementById('cards-' + lid);
    if (!col) return;
    const hint = col.querySelector('.col-empty-hint');
    const hasCards = col.querySelectorAll('.kanban-card').length > 0;
    if (hasCards && hint) hint.remove();
    if (!hasCards && !hint) {
        const d = document.createElement('div');
        d.className = 'col-empty-hint';
        d.textContent = 'Sin tarjetas aún';
        col.appendChild(d);
    }
}

function actualizarContador(lid) {
    const cardsContainer = document.getElementById('cards-' + lid);
    if (!cardsContainer) return;
    const n = cardsContainer.querySelectorAll('.kanban-card').length;
    const countEl = document.getElementById('count-' + lid);
    if (countEl) countEl.textContent = n;
}

function buildCardEl(t) {
    const div = document.createElement('div');
    div.className = 'kanban-card';
    div.dataset.id = t.id;
    div.innerHTML = `<div class="kanban-card-num">#${t.numero}</div>
                   <div class="kanban-card-title">${escHtml(t.titulo)}</div>`;
    return div;
}

function bindDelLista(btn) {
    btn.addEventListener('click', async (e) => {
        e.stopPropagation();
        const listaId = parseInt(btn.dataset.id);
        const nombre = btn.dataset.nombre;
        if (!confirm(`¿Eliminar la columna "${nombre}"?\n\nSolo puede eliminarse si está vacía.`)) return;
        if (typeof fetchJSON === 'function') {
            const res = await fetchJSON(BASE + '/lista/eliminar', { lista_id: listaId });
            if (!res.ok) { alert(res.error || 'No se pudo eliminar la columna'); return; }
            btn.closest('.kanban-col').remove();
        }
    });
}

function cerrarFormCol() {
    const form = document.getElementById('add-col-form');
    const input = document.getElementById('add-col-input');
    const btn = document.getElementById('btn-show-col');
    if (form) form.classList.add('d-none');
    if (input) input.value = '';
    if (btn) btn.classList.remove('d-none');
}

function buildColEl(lista) {
    const col = document.createElement('div');
    col.className = 'kanban-col';
    col.id = 'col-' + lista.id;
    col.innerHTML = `
    <div class="kanban-col-header">
      <span>${escHtml(lista.nombre)}</span>
      <div class="d-flex align-items-center gap-1">
        <span class="badge-count" id="count-${lista.id}">0</span>
        <button class="btn-del-lista"
                data-id="${lista.id}"
                data-nombre="${escHtml(lista.nombre)}"
                title="Eliminar columna"
                style="background:none;border:none;color:#94a3b8;cursor:pointer;padding:0 3px;font-size:1.1rem;line-height:1;display:flex;align-items:center">
          &times;
        </button>
      </div>
    </div>
    <div class="kanban-col-cards" id="cards-${lista.id}" data-lista="${lista.id}">
      <div class="col-empty-hint">Sin tarjetas aún</div>
    </div>
    <div class="kanban-add-wrap">
      <div class="kanban-add-form d-none" id="add-form-${lista.id}">
        <textarea class="form-control form-control-sm" rows="2"
                  placeholder="Título de la tarjeta..."></textarea>
        <div class="d-flex gap-1 mt-1">
          <button class="btn btn-primary btn-sm flex-grow-1 btn-add-card"
                  data-lista="${lista.id}">Agregar</button>
          <button class="btn btn-outline-secondary btn-sm btn-cancel-add"
                  data-lista="${lista.id}"><i class="bi bi-x"></i></button>
        </div>
      </div>
      <button class="kanban-add-btn btn-show-add"
              id="btn-show-add-${lista.id}"
              data-lista="${lista.id}">
        <i class="bi bi-plus-lg me-1"></i> Agregar tarjeta
      </button>
    </div>`;

    // Bind botón eliminar la nueva columna
    bindDelLista(col.querySelector('.btn-del-lista'));

    // Bind "agregar tarjeta" en nueva columna
    const lid = lista.id;
    col.querySelector('.btn-show-add').addEventListener('click', () => {
        document.getElementById('add-form-' + lid).classList.remove('d-none');
        col.querySelector('.btn-show-add').classList.add('d-none');
        col.querySelector('#add-form-' + lid + ' textarea').focus();
    });
    col.querySelector('.btn-cancel-add').addEventListener('click', () => closeAddForm(lid));
    col.querySelector('.btn-add-card').addEventListener('click', async () => {
        const ta = col.querySelector('#add-form-' + lid + ' textarea');
        const titulo = ta.value.trim();
        if (!titulo) { ta.focus(); return; }
        const addBtn = col.querySelector('.btn-add-card');
        addBtn.disabled = true;
        if (typeof fetchJSON === 'function') {
            const res = await fetchJSON(BASE + '/tarjeta/crear', {
                lista_id: lid, tablero_id: TABLERO_ID, titulo,
            });
            addBtn.disabled = false;
            if (res.ok) {
                const cards = document.getElementById('cards-' + lid);
                cards.querySelector('.col-empty-hint')?.remove();
                const div = buildCardEl(res.tarjeta);
                cards.appendChild(div);
                bindCardClick(div);
                actualizarContador(lid);
                closeAddForm(lid);
            } else {
                alert('Error: ' + (res.error || 'No se pudo crear la tarjeta'));
            }
        }
    });

    return col;
}

function escHtml(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}

async function aplicarFondo(color, img) {
    console.log('Aplicando fondo:', { color, img, tablero: TABLERO_ID });
    try {
        const response = await fetch(BASE + '/tablero/fondo', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: TABLERO_ID, fondo_color: color, fondo_imagen: img })
        });

        const text = await response.text();
        console.log('Respuesta cruda del servidor:', text);

        let res;
        try {
            res = JSON.parse(text);
        } catch (e) {
            console.error('La respuesta no es JSON:', text);
            alert('Error: La respuesta del servidor no es válida. Revisa la consola (F12) para ver el detalle.');
            return;
        }

        if (!res.ok) {
            console.error('Error al guardar fondo:', res);
            alert('No se pudo guardar el fondo: ' + (res.error || 'Error desconocido'));
            return;
        }

        // Aplicar visualmente
        document.body.style.background = img
            ? `url(${img}) center/cover no-repeat fixed ${color}`
            : color;

        const navbar = document.querySelector('.kanban-navbar');
        if (navbar) navbar.style.background = color + 'dd';

        const dot = document.querySelector('.tablero-dot');
        if (dot) dot.style.background = color;

        document.getElementById('panel-fondo')?.classList.add('d-none');
    } catch (err) {
        console.error('Fallo crítico en aplicarFondo:', err);
        alert('Error de conexión al cambiar el fondo.');
    }
}
