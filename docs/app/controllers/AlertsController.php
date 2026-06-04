<?php
/**
 * Controller de Alertas de Vencimiento.
 *
 * Configura las reglas de notificación por correo para contratos
 * que están próximos a expirar (ej: 30, 15, 7 días antes).
 *
 * @package App\Controllers
 */
class AlertsController extends Controller
{
    /**
     * Muestra las alertas configuradas.
     */
    public function index()
    {
        PermissionMiddleware::check('alerts_config');
        $model = new AlertConfigModel();
        $alerts = $model->getAll();
        $this->view('alerts/index', compact('alerts'));
    }

    /**
     * Guarda / Actualiza una alerta.
     */
    public function store()
    {
        PermissionMiddleware::check('alerts_config');
        csrf_verify();

        $days = (int)($_POST['days_before'] ?? 30);
        $emails = trim($_POST['email_recipients'] ?? '');
        $status = isset($_POST['active']) ? 1 : 0;

        $model = new AlertConfigModel();
        if ($model->set($days, $emails, $status)) {
            $this->redirect("/alerts");
        }
        
        die("Error al guardar configuración");
    }

    /**
     * Elimina (desactiva) una regla de alerta específica.
     */
    public function delete($id)
    {
        PermissionMiddleware::check('alerts_config');
        $model = new AlertConfigModel();
        $model->delete($id);
        $this->redirect("/alerts");
    }
}
