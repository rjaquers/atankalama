<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Gestión de Usuarios</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="bi bi-person-plus me-2"></i> Añadir Usuario
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small">
                    <tr>
                        <th class="ps-4">Email Corporativo</th>
                        <th>Perfil</th>
                        <th>Estado</th>
                        <th>Último Acceso</th>
                        <th>Creado en</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="fw-semibold">
                                    <?= htmlspecialchars($user['email']) ?>
                                </span>
                            </td>
                            <td>
                                <span
                                    class="badge <?= $user['perfil'] == 'Administrador' ? 'bg-primary' : 'bg-info' ?>-subtle <?= $user['perfil'] == 'Administrador' ? 'text-primary' : 'text-info' ?> px-2 py-1 rounded-pill small">
                                    <?= $user['perfil'] ?>
                                </span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-success-subtle text-success px-2 py-1 rounded-pill small">Activo</span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca' ?>
                                </small>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                </small>
                            </td>
                            <td class="text-end pe-4">
                                <?php if ($user['email'] !== \AccesoBootstrap::email()): ?>
                                    <button onclick="openEditModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['email']) ?>', '<?= htmlspecialchars($user['perfil']) ?>')"
                                        class="btn btn-sm btn-outline-secondary border-0 me-1">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['email']) ?>')"
                                        class="btn btn-sm btn-outline-danger border-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Editar Usuario -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Editar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="editUserId">
                    <div class="mb-3">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" id="editUserEmail" class="form-control" readonly disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Perfil de Usuario</label>
                        <select id="editUserPerfil" class="form-select">
                            <option value="Operador">Operador</option>
                            <option value="Administrador">Administrador</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary px-4" onclick="saveEditUser()">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Añadir Usuario -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Nuevo Acceso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" id="newUserEmail" class="form-control"
                            placeholder="nombre<?= ALLOWED_DOMAIN ?>" required>
                        <div class="form-text text-muted small">El correo debe pertenecer al dominio corporativo.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Perfil de Usuario</label>
                        <select id="newUserPerfil" class="form-select">
                            <option value="Operador">Operador</option>
                            <option value="Administrador">Administrador</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary px-4" onclick="saveUser()">Registrar Usuario</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openEditModal(id, email, perfil) {
        document.getElementById('editUserId').value = id;
        document.getElementById('editUserEmail').value = email;
        document.getElementById('editUserPerfil').value = perfil;
        new bootstrap.Modal(document.getElementById('editUserModal')).show();
    }

    async function saveEditUser() {
        const id = document.getElementById('editUserId').value;
        const perfil = document.getElementById('editUserPerfil').value;

        try {
            const res = await fetch('<?= BASE_URL ?>/usuarios/actualizar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&perfil=${encodeURIComponent(perfil)}`
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

    async function saveUser() {
        const email = document.getElementById('newUserEmail').value;
        const perfil = document.getElementById('newUserPerfil').value;
        if (!email) return;

        try {
            const res = await fetch('<?= BASE_URL ?>/usuarios/guardar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `email=${encodeURIComponent(email)}&perfil=${encodeURIComponent(perfil)}`
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

    async function deleteUser(id, email) {
        if (!confirm(`¿Está seguro de eliminar el acceso para ${email}?`)) return;

        try {
            const res = await fetch('<?= BASE_URL ?>/usuarios/eliminar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}`
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