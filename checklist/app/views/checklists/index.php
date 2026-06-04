<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Checklists</h2>
    <a href="<?= BASE_URL ?>/checklists/nuevo" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nuevo Checklist o Encuesta
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted">
                    <tr>
                        <th class="ps-4">Nombre</th>
                        <th>Área</th>
                        <th>Creado por</th>
                        <th>Fecha</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($checklists)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-clipboard-x text-muted display-4 d-block mb-3"></i>
                                <span class="text-muted">No hay checklists creados todavía.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($checklists as $item): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?= htmlspecialchars($item['nombre']) ?></div>
                                    <?php if (($item['modo'] ?? 'cerrado') === 'abierto'): ?>
                                        <small class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                            <i class="bi bi-qr-code me-1"></i>Encuesta pública
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">
                                        <?= htmlspecialchars($item['area']) ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted"><?= htmlspecialchars($item['created_by']) ?></small>
                                </td>
                                <td>
                                    <small class="text-muted"><?= date('d/m/Y', strtotime($item['created_at'])) ?></small>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end align-items-center">
                                        <a href="<?= BASE_URL ?>/evaluaciones/ejecutar?id=<?= $item['id'] ?>" 
                                           class="btn btn-sm btn-primary me-3">
                                            <i class="bi bi-play-fill"></i> Ejecutar
                                        </a>
                                        <div class="btn-group">
                                            <a href="<?= BASE_URL ?>/checklists/editar/<?= $item['id'] ?>"
                                                class="btn btn-sm btn-outline-secondary border-0">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button
                                                onclick="deleteChecklist(<?= $item['id'] ?>, '<?= htmlspecialchars($item['nombre']) ?>')"
                                                class="btn btn-sm btn-outline-danger border-0">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    async function deleteChecklist(id, nombre) {
        if (!confirm(`¿Estás seguro de eliminar el checklist "${nombre}"?`)) return;

        try {
            const res = await fetch(`<?= BASE_URL ?>/api/checklists/eliminar/${id}`, {
                method: 'POST'
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
</script>