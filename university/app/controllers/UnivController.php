<?php
/**
 * Controlador para la interfaz del alumno (Universidad).
 */
class UnivController extends Controller
{
    private $enrollmentModel;
    private $courseModel;
    private $pageModel;

    public function __construct()
    {
        AuthMiddleware::check();
        $this->enrollmentModel = new UnivEnrollmentModel();
        $this->courseModel = new UnivCourseModel();
        $this->pageModel = new UnivPageModel();
    }

    /**
     * Dashboard del alumno: Cursos asignados, progreso y créditos.
     */
    public function index()
    {
        $userId = $_SESSION['user_id'];
        $enrollments = $this->enrollmentModel->getByUser($userId);
        
        // Calcular créditos totales obtenidos
        $totalCredits = 0;
        foreach ($enrollments as $e) {
            if ($e['status'] === 'aprobado') {
                $totalCredits += $e['creditos_ganados'];
            }
        }

        $this->view("univ/dashboard", compact('enrollments', 'totalCredits'));
    }

    /**
     * Reproductor del curso (Focus Mode).
     */
    public function play($enrollId, $pageNumber = null)
    {
        $userId = $_SESSION['user_id'];
        $enroll = $this->enrollmentModel->getByIdAndUser($enrollId, $userId);
        
        if (!$enroll) {
            die("Capacitación no encontrada o no tienes acceso.");
        }

        $pages = $this->pageModel->getByCourse($enroll['course_id']);
        $totalPages = count($pages);

        // Si no se especifica página, cargar la última guardada o la 1
        if ($pageNumber === null) {
            $pageNumber = $enroll['pagina_actual'] > 0 ? $enroll['pagina_actual'] : 1;
        }

        // Buscar la página actual en el array
        $currentPage = null;
        foreach ($pages as $p) {
            if ($p['orden'] == $pageNumber) {
                $currentPage = $p;
                break;
            }
        }

        // Si la página no existe, pero es > 0, podría ser que el curso cambió
        if (!$currentPage && !empty($pages)) {
            $currentPage = $pages[0];
            $pageNumber = $currentPage['orden'];
        }

        // Actualizar progreso en BD (Evento: "Entró a la página")
        $this->enrollmentModel->updateProgress($enrollId, $pageNumber);

        $this->view("univ/course_player", compact('enroll', 'pages', 'currentPage', 'pageNumber', 'totalPages'));
    }

    /**
     * Vista de Examen.
     */
    public function exam($enrollId)
    {
        $userId = $_SESSION['user_id'];
        $enroll = $this->enrollmentModel->getByIdAndUser($enrollId, $userId);
        
        if (!$enroll) die("Acceso denegado.");

        $pages = $this->pageModel->getByCourse($enroll['course_id']);
        $totalPages = count($pages);

        if ($enroll['pagina_actual'] < $totalPages) {
            die("Debes completar todas las páginas para iniciar el examen.");
        }

        if ($enroll['status'] === 'bloqueado' || $enroll['intentos_usados'] >= 3) {
            die("Has agotado tus intentos para este curso.");
        }

        $examService = new UnivExamService();
        $questions = $examService->prepareExam($enroll['course_id'], $enroll['total_preguntas_examen']);

        // Guardar inicio del examen en sesión para cálculo de tiempo
        $_SESSION['exam_start_' . $enrollId] = date('Y-m-d H:i:s');

        $this->view("univ/exam", compact('enroll', 'questions'));
    }

    /**
     * Procesar envío de respuestas del examen.
     */
    public function submitExam()
    {
        csrf_verify();

        $enrollId = (int)$_POST['enroll_id'];
        $userId = $_SESSION['user_id'];
        $enroll = $this->enrollmentModel->getByIdAndUser($enrollId, $userId);

        if (!$enroll) die("Acceso inválido.");

        $startTime = $_SESSION['exam_start_' . $enrollId] ?? date('Y-m-d H:i:s');
        $userAnswers = $_POST['answers'] ?? []; // Array [question_id => option_id]

        $examService = new UnivExamService();
        $result = $examService->gradeExam($enroll, $userAnswers, $startTime);

        // Limpiar sesión
        unset($_SESSION['exam_start_' . $enrollId]);

        $this->view("univ/exam_result", compact('enroll', 'result'));
    }

    /**
     * Ver Certificado de Aprobación.
     */
    public function certificate($enrollId)
    {
        $userId = $_SESSION['user_id'];
        $enroll = $this->enrollmentModel->getByIdAndUser($enrollId, $userId);

        if (!$enroll || $enroll['status'] !== 'aprobado') {
            die("Certificado no disponible. Debes aprobar el curso primero.");
        }

        // Obtener nombre completo del alumno desde la tabla chk_usuarios
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT nombre, apellido FROM chk_usuarios WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        $alumno = ($user) ? $user['nombre'] . ' ' . $user['apellido'] : 'Alumno';

        $this->view("univ/certificate", compact('enroll', 'alumno'));
    }
}
