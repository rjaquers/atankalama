<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Gestión de Áreas y Departamentos</h2>
    <button class="btn btn-primary" onclick="openModal()">
        <i class="bi bi-plus-lg me-2"></i> Nueva Área
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small">
                    <tr>
                        <th class="ps-4">Nombre</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Fecha Creación</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($areas)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">No hay áreas registradas</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($areas as $area): ?>
                        <tr>
                            <td class="ps-4 fw-semibold">
                                <?php $color = getAreaColor($area['nombre']); ?>
                                <span class="badge bg-<?= $color ?> me-2" style="width: 12px; height: 12px; padding: 0; border-radius: 50%; display: inline-block;"></span>
                                <?= htmlspecialchars($area['nombre']) ?>
                            </td>
                            <td><small class="text-muted">
                                    <?= htmlspecialchars($area['descripcion']) ?>
                                </small></td>
                            <td>
                                <span
                                    class="badge <?= $area['estado'] == 'activo' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> px-2 py-1 rounded-pill small">
                                    <?= ucfirst($area['estado']) ?>
                                </span>
                            </td>
                            <td><small>
                                    <?= date('d/m/Y', strtotime($area['created_at'])) ?>
                                </small></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-secondary border-0"
                                    onclick='openModal(<?= json_encode($area) ?>)'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger border-0"
                                    onclick="deleteArea(<?= $area['id'] ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="areaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Nueva Área</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="areaForm">
                    <input type="hidden" id="areaId">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Área</label>
                        <input type="text" id="areaNombre" class="form-control"
                            placeholder="Ej: Housekeeping, Cocina..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción (Opcional)</label>
                        <textarea id="areaDescripcion" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3" id="estadoContainer" style="display:none">
                        <label class="form-label">Estado</label>
                        <select id="areaEstado" class="form-select">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary px-4" onclick="saveArea()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
    let modal;
    document.addEventListener('DOMContentLoaded', () => {
        modal = new bootstrap.Modal(document.getElementById('areaModal'));
    });

    function openModal(data = null) {
        document.getElementById('areaForm').reset();
        if (data) {
            document.getElementById('modalTitle').innerText = 'Editar Área';
            document.getElementById('areaId').value = data.id;
            document.getElementById('areaNombre').value = data.nombre;
            document.getElementById('areaDescripcion').value = data.descripcion;
            document.getElementById('areaEstado').value = data.estado;
            document.getElementById('estadoContainer').style.display = 'block';
        } else {
            document.getElementById('modalTitle').innerText = 'Nueva Área';
            document.getElementById('areaId').value = '';
            document.getElementById('estadoContainer').style.display = 'none';
        }
        modal.show();
    }

    async function saveArea() {
        const id = document.getElementById('areaId').value;
        const nombre = document.getElementById('areaNombre').value;
        const descripcion = document.getElementById('areaDescripcion').value;
        const estado = document.getElementById('areaEstado').value;

        if (!nombre) return;

        try {
            const params = new URLSearchParams();
            params.append('id', id);
            params.append('nombre', nombre);
            params.append('descripcion', descripcion);
            params.append('estado', estado);

            const res = await fetch('<?= BASE_URL ?>/areas/guardar', {
                method: 'POST',
                body: params
            });
            const data = await res.json();
            if (res.ok) {
                location.reload();
            } else {
                alert(data.error);
            }
        } catch (err) {
            alert("Error de conexión");
        }
    }

    async function deleteArea(id) {
        if (!confirm("¿Está seguro de eliminar esta área? Esto podría afectar a los checklists asociados.")) return;

        try {
            const params = new URLSearchParams();
            params.append('id', id);
            const res = await fetch('<?= BASE_URL ?>/areas/eliminar', {
                method: 'POST',
                body: params
            });
            if (res.ok) {
                location.reload();
            } else {
                const data = await res.json();
                alert(data.error);
            }
        } catch (err) {
            alert("Error de conexión");
        }
    }
</script>