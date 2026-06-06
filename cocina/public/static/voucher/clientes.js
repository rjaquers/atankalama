function addObs(text) {
    const ta = document.getElementById('obsTextarea');
    const cur = ta.value.trim();
    ta.value = cur ? (cur.endsWith('.') || cur.endsWith(',') ? cur + ' ' + text : cur + '. ' + text) : text;
    ta.focus();
}

function formatRut(value) {
    let v = value.replace(/[^0-9kK]/g, '').toUpperCase();
    if (v.length > 1) v = v.slice(0, -1).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.') + '-' + v.slice(-1);
    return v;
}

const rutInput = document.getElementById('rut-input');
if (rutInput) {
    rutInput.addEventListener('input', function () {
        this.value = formatRut(this.value);
    });
}

document.getElementById('edit_rut').addEventListener('input', function () {
    this.value = formatRut(this.value);
});

const modalEditar = new bootstrap.Modal(document.getElementById('modalEditarCliente'));
document.querySelectorAll('.btn-editar-cliente').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('edit_id').value     = this.dataset.id;
        document.getElementById('edit_nombre').value = this.dataset.nombre;
        document.getElementById('edit_rut').value    = this.dataset.rut;
        const chk = document.getElementById('editPropagar');
        if (chk) chk.checked = false;
        modalEditar.show();
    });
});

const modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminarCliente'));
document.querySelectorAll('.btn-eliminar-cliente').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('eliminar_id').value                    = this.dataset.id;
        document.getElementById('eliminar_nombre_display').textContent  = this.dataset.nombre;
        const chk = document.getElementById('eliminarPropagar');
        if (chk) chk.checked = false;
        modalEliminar.show();
    });
});
