<!--
  = Proyecto: Sistema de Contratos Atankalama =
  = Autor: Rodrigo Jaque Escobar              =
  = Contacto: rjaquers@gmail.com              =

-->
<?php
$title = "Configuración de Alertas";
include VIEW_PATH . "/layouts/header.php";
?>

<div class="header-section mb-4 pt-3">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="h3 fw-bold mb-1"><i class="fa-solid fa-bell text-warning me-2"></i> Notificaciones de Vencimiento</h2>
            <p class="text-muted small">Configura cuántos días antes debe avisarte el sistema de un vencimiento.</p>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Formulario Nueva Alerta -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h5 class="fw-bold mb-4">Añadir Regla de Alerta</h5>
            <form action="<?= BASE_URL ?>/alerts/store" method="post">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Anticipación (Días)</label>
                    <div class="input-group input-group-lg">
                        <input type="number" name="days_before" class="form-control border-2" placeholder="Ej: 30" required>
                        <span class="input-group-text border-2">Días</span>
                    </div>
                    <div class="form-text mt-2">Cuántos días antes del 'end_date' enviar el email.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Destinatarios Extras (Opcional)</label>
                    <textarea name="email_recipients" class="form-control border-2 shadow-none" 
                              placeholder="admin@hotel.com, rjaquers@gmail.com"></textarea>
                    <div class="form-text mt-2 small">Separa emails con coma. El vendedor siempre recibe copia.</div>
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-switch" type="checkbox" name="active" checked>
                        <label class="form-check-label fw-semibold">Alerta Activa</label>
                    </div>
                </div>

                <div class="pt-3">
                    <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold shadow-sm">
                        <i class="fa-solid fa-plus-circle me-1"></i> Guardar Nueva Regla
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Alertas -->
    <div class="col-md-7">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Reglas Configuradas</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Aviso a los...</th>
                            <th>Mails Extras</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alerts as $a): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="h5 fw-bold text-primary mb-0"><?= $a['days_before'] ?></span> 
                                    <span class="text-muted small">días antes</span>
                                </td>
                                <td class="small text-muted">
                                    <?= $a['email_recipients'] ? htmlspecialchars($a['email_recipients']) : '<i>Sólo gestor</i>' ?>
                                </td>
                                <td>
                                    <?php if($a['active']): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2">Pausado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="<?= BASE_URL ?>/alerts/delete/<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger border-2 rounded-circle shadow-none" 
                                       onclick="return confirm('¿Seguro quieres desactivar esta regla?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if(empty($alerts)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted opacity-50">
                                    No hay reglas definidas. El sistema no enviará avisos automáticos.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="alert alert-info border-2 mt-4 rounded-4 shadow-sm bg-info-subtle text-dark">
            <h6 class="fw-bold"><i class="fa-solid fa-info-circle me-2"></i> Nota Técnica</h6>
            <p class="mb-0 small">Este sistema funciona junto con el <strong>cron/contract_alerts.php</strong>. Asegúrate de que el administrador de IT haya programado una tarea CRON diaria en el servidor.</p>
        </div>
    </div>
</div>

<?php include VIEW_PATH . "/layouts/footer.php"; ?>
