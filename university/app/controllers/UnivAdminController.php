<?php
/**
 * Controlador administrativo para el Módulo de Universidad.
 * Maneja el CRUD de cursos, gestión de páginas y banco de preguntas.
 */
class UnivAdminController extends Controller
{
    private $courseModel;

    public function __construct()
    {
        AuthMiddleware::check();
        $this->checkAdmin();
        $this->courseModel = new UnivCourseModel();
    }

    /**
     * Valida que el usuario tenga acceso al panel de administración.
     * Usa AccesoService (sistema centralizado de permisos) como check primario,
     * con fallback a chk_usuarios.perfil para compatibilidad.
     */
    private function checkAdmin()
    {
        $email = $_SESSION['portal_email'] ?? null;

        // Check primario: sistema de permisos centralizado (acc_usuario_roles)
        $accesoServicePath = $_SERVER['DOCUMENT_ROOT'] . '/shared/AccesoService.php';
        if ($email && file_exists($accesoServicePath)) {
            require_once $accesoServicePath;
            if (AccesoService::puedeAcceder('university', $email, 'univAdmin/index')) {
                return;
            }
        }

        // Fallback: verificar por perfil global en chk_usuarios
        $perfil = $_SESSION['perfil'] ?? '';
        $admins = ['Administrador', 'RRHH', 'Gerencia'];
        if (in_array($perfil, $admins)) {
            return;
        }

        http_response_code(403);
        die("Acceso denegado: Se requiere perfil administrativo o rol de administrador en el sistema University.");
    }

    /**
     * Listado de cursos.
     */
    public function index()
    {
        $courses = $this->courseModel->getAll(false); // Todos, incluso inactivos
        $this->view("univ/admin/courses_list", compact('courses'));
    }

    /**
     * Formulario de creación.
     */
    public function create()
    {
        $course          = null;
        $perfilesDisp    = $this->listarPerfiles();
        $perfilesSelec   = [];
        $this->view("univ/admin/course_form", compact('course', 'perfilesDisp', 'perfilesSelec'));
    }

    /**
     * Procesar guardado (Crear o Actualizar).
     */
    public function store()
    {
        csrf_verify();

        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'nombre'               => trim($_POST['nombre'] ?? ''),
            'descripcion'          => trim($_POST['descripcion'] ?? ''),
            'tipo'                 => $_POST['tipo'] ?? 'opcional',
            'creditos'             => (int)($_POST['creditos'] ?? 0),
            'min_score_to_approve' => (int)($_POST['min_score_to_approve'] ?? 70),
            'total_preguntas_examen' => (int)($_POST['total_preguntas_examen'] ?? 10),
            'tiempo_limite_minutos'  => (int)($_POST['tiempo_limite_minutos'] ?? 15),
            'max_intentos'         => (int)($_POST['max_intentos'] ?? 3),
            'vigencia_meses'       => !empty($_POST['vigencia_meses']) ? (int)$_POST['vigencia_meses'] : null,
            'activo'               => isset($_POST['activo']) ? 1 : 0,
        ];

        if (empty($data['nombre'])) {
            die("El nombre es obligatorio");
        }

        $db = (new Database())->connect();

        if ($id > 0) {
            $this->courseModel->update($id, $data);
        } else {
            $id = $this->courseModel->create($data);
        }

        // Si es obligatorio por área, sincronizar perfiles y enrolar usuarios
        if ($data['tipo'] === 'obligatorio_area') {
            $perfilesPost = array_filter(
                array_map('trim', (array)($_POST['perfiles'] ?? [])),
                fn($p) => $p !== ''
            );
            $this->sincronizarPerfilesCurso($db, $id, $perfilesPost);
            if (!empty($perfilesPost)) {
                $this->enrolarPorPerfiles($db, $id, $perfilesPost);
            }
        } else {
            // Si cambió de tipo, limpiar asociaciones de perfiles previas
            $stmt = $db->prepare("DELETE FROM univ_cursos_por_perfil WHERE course_id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
        }

        $this->redirect("/univAdmin/index");
    }

    /**
     * Formulario de edición.
     */
    public function edit($id)
    {
        $course = $this->courseModel->getById($id);
        if (!$course) {
            die("Curso no encontrado");
        }
        $db           = (new Database())->connect();
        $perfilesDisp = $this->listarPerfiles();

        $stmt = $db->prepare("SELECT perfil FROM univ_cursos_por_perfil WHERE course_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $perfilesSelec = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'perfil');

        $this->view("univ/admin/course_form", compact('course', 'perfilesDisp', 'perfilesSelec'));
    }

    private function listarPerfiles(): array
    {
        $db   = (new Database())->connect();
        $res  = $db->query(
            "SELECT DISTINCT perfil FROM chk_usuarios WHERE estado = 'activo' AND perfil IS NOT NULL AND perfil <> '' ORDER BY perfil"
        );
        return array_column($res->fetch_all(MYSQLI_ASSOC), 'perfil');
    }

    private function sincronizarPerfilesCurso($db, int $courseId, array $perfiles): void
    {
        $stmt = $db->prepare("DELETE FROM univ_cursos_por_perfil WHERE course_id = ?");
        $stmt->bind_param('i', $courseId);
        $stmt->execute();

        if (empty($perfiles)) return;

        $stmt = $db->prepare(
            "INSERT IGNORE INTO univ_cursos_por_perfil (perfil, course_id, es_obligatorio) VALUES (?, ?, 1)"
        );
        foreach ($perfiles as $perfil) {
            $stmt->bind_param('si', $perfil, $courseId);
            $stmt->execute();
        }
    }

    private function enrolarPorPerfiles($db, int $courseId, array $perfiles): void
    {
        $stmt = $db->prepare("SELECT version FROM univ_courses WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $courseId);
        $stmt->execute();
        $version = (int)($stmt->get_result()->fetch_assoc()['version'] ?? 1);

        $placeholders = implode(',', array_fill(0, count($perfiles), '?'));
        $types        = str_repeat('s', count($perfiles)) . 'ii';
        $params       = array_merge($perfiles, [$courseId, $courseId]);

        $stmt = $db->prepare(
            "INSERT IGNORE INTO univ_enrollments (user_id, course_id, course_version, status)
             SELECT u.id, ?, ?, 'asignado'
             FROM chk_usuarios u
             WHERE u.perfil IN ($placeholders)
               AND u.estado = 'activo'
               AND NOT EXISTS (
                   SELECT 1 FROM univ_enrollments e
                   WHERE e.user_id = u.id AND e.course_id = ?
                   AND e.status IN ('asignado','en_progreso','aprobado')
               )"
        );
        // Reordenar: courseId, version, ...perfiles, courseId
        $allParams = array_merge([$courseId, $version], $perfiles, [$courseId]);
        $allTypes  = 'ii' . str_repeat('s', count($perfiles)) . 'i';
        $stmt->bind_param($allTypes, ...$allParams);
        $stmt->execute();
    }

    /**
     * Eliminar curso.
     */
    public function delete($id)
    {
        // En un sistema robusto, podríamos preferir desactivar en lugar de borrar si hay enrollments
        $this->courseModel->delete($id);
        $this->redirect("/univAdmin");
    }

    // ==========================================
    // GESTIÓN DE PÁGINAS (CONTENIDO)
    // ==========================================

    public function pages($courseId)
    {
        $course = $this->courseModel->getById($courseId);
        if (!$course) die("Curso no encontrado");

        $pageModel = new UnivPageModel();
        $pages = $pageModel->getByCourse($courseId);

        $this->view("univ/admin/pages_manager", compact('course', 'pages'));
    }

    public function savePage()
    {
        csrf_verify();

        $tipo     = $_POST['tipo']     ?? 'html';
        $courseId = (int)($_POST['course_id'] ?? 0);

        // Determinar contenido según tipo
        switch ($tipo) {
            case 'html':
                $contenido = $_POST['contenido_html'] ?? '';
                break;
            case 'pdf':
                $contenido = $this->procesarPdf() ?? trim($_POST['contenido_pdf_url'] ?? '');
                break;
            case 'video':
                $contenido = $_POST['contenido_video'] ?? '';
                break;
            default:
                $contenido = '';
        }

        $data = [
            'id'                     => (int)($_POST['id'] ?? 0),
            'course_id'              => $courseId,
            'orden'                  => (int)($_POST['orden'] ?? 1),
            'titulo'                 => trim($_POST['titulo'] ?? 'Página sin título'),
            'tipo'                   => $tipo,
            'contenido'              => $contenido,
            'tiempo_minimo_segundos' => (int)($_POST['tiempo_minimo_segundos'] ?? 0),
        ];

        (new UnivPageModel())->save($data);
        $this->redirect("/univAdmin/pages/" . $courseId);
    }

    private function procesarPdf()
    {
        if (empty($_FILES['archivo_pdf']['tmp_name'])) return null;
        $file = $_FILES['archivo_pdf'];
        if ($file['error'] !== UPLOAD_ERR_OK) return null;

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') return null;

        $dir = PUBLIC_PATH . '/uploads/univ_pdfs/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $nombre = 'pdf_' . uniqid() . '.pdf';
        if (!move_uploaded_file($file['tmp_name'], $dir . $nombre)) return null;

        return BASE_URL . '/uploads/univ_pdfs/' . $nombre;
    }

    public function uploadImagen()
    {
        header('Content-Type: application/json');

        if (empty($_FILES['file']['tmp_name'])) {
            echo json_encode(['error' => 'No se recibió archivo']);
            exit;
        }

        $file = $_FILES['file'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg','jpeg','png','gif','webp','bmp'];

        if (!in_array($ext, $permitidos)) {
            echo json_encode(['error' => 'Formato no permitido']);
            exit;
        }

        $dir = PUBLIC_PATH . '/uploads/univ_imagenes/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        // Crear imagen GD según formato origen
        switch ($ext) {
            case 'jpg': case 'jpeg': $src = @imagecreatefromjpeg($file['tmp_name']); break;
            case 'png':              $src = @imagecreatefrompng($file['tmp_name']);  break;
            case 'gif':              $src = @imagecreatefromgif($file['tmp_name']);  break;
            case 'webp':             $src = @imagecreatefromwebp($file['tmp_name']); break;
            case 'bmp':              $src = @imagecreatefrombmp($file['tmp_name']);  break;
            default:                 $src = false;
        }

        if (!$src) {
            echo json_encode(['error' => 'No se pudo procesar la imagen']);
            exit;
        }

        $nombre = 'img_' . uniqid() . '.webp';
        imagewebp($src, $dir . $nombre, 85);
        imagedestroy($src);

        echo json_encode(['url' => BASE_URL . '/uploads/univ_imagenes/' . $nombre]);
        exit;
    }

    public function deletePage($id, $courseId)
    {
        (new UnivPageModel())->delete($id);
        $this->redirect("/univAdmin/pages/" . $courseId);
    }

    // ==========================================
    // GESTIÓN DE PREGUNTAS (BANCO DE EXAMEN)
    // ==========================================

    public function questions($courseId)
    {
        $course = $this->courseModel->getById($courseId);
        if (!$course) die("Curso no encontrado");

        $questionModel = new UnivQuestionModel();
        $questions = $questionModel->getByCourse($courseId);

        $this->view("univ/admin/questions_manager", compact('course', 'questions'));
    }

    public function saveQuestion()
    {
        csrf_verify();
        $questionModel = new UnivQuestionModel();

        $courseId = (int)$_POST['course_id'];
        $optionsRaw = $_POST['options'] ?? [];
        $correctIndex = (int)($_POST['correct_index'] ?? 0);

        $options = [];
        foreach ($optionsRaw as $index => $text) {
            if (empty(trim($text))) continue;
            $options[] = [
                'texto_opcion' => trim($text),
                'es_correcta' => ($index == $correctIndex)
            ];
        }

        $data = [
            'id' => (int)($_POST['id'] ?? 0),
            'course_id' => $courseId,
            'texto_pregunta' => trim($_POST['texto_pregunta'] ?? ''),
            'options' => $options
        ];

        if (!empty($data['texto_pregunta']) && count($options) >= 2) {
            $questionModel->save($data);
        }

        $this->redirect("/univAdmin/questions/" . $courseId);
    }

    public function deleteQuestion($id, $courseId)
    {
        $questionModel = new UnivQuestionModel();
        $questionModel->delete($id);
        $this->redirect("/univAdmin/questions/" . $courseId);
    }

    // ==========================================
    // ASIGNACIÓN DE CURSOS A ALUMNOS
    // ==========================================

    public function asignaciones()
    {
        $db   = (new Database())->connect();

        $usuarios = $db->query(
            "SELECT id, nombre, apellido, perfil, email
             FROM chk_usuarios
             WHERE estado = 'activo'
             ORDER BY nombre, apellido"
        )->fetch_all(MYSQLI_ASSOC);

        $courses = $db->query(
            "SELECT id, nombre, tipo, activo FROM univ_courses ORDER BY nombre"
        )->fetch_all(MYSQLI_ASSOC);

        // Enrollments activos indexados por user_id y course_id
        $res = $db->query(
            "SELECT user_id, course_id, status FROM univ_enrollments
             WHERE status IN ('asignado','en_progreso','aprobado','reprobado')"
        );
        $enrollments = [];
        while ($row = $res->fetch_assoc()) {
            $enrollments[$row['user_id']][$row['course_id']] = $row['status'];
        }

        $this->view("univ/admin/asignaciones", compact('usuarios', 'courses', 'enrollments'));
    }

    public function asignar()
    {
        csrf_verify();
        $userId   = (int)($_POST['user_id']   ?? 0);
        $courseId = (int)($_POST['course_id'] ?? 0);
        if (!$userId || !$courseId) { http_response_code(400); exit; }

        $db = (new Database())->connect();

        // Obtener versión actual del curso
        $stmt = $db->prepare("SELECT version FROM univ_courses WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $courseId);
        $stmt->execute();
        $version = (int)($stmt->get_result()->fetch_assoc()['version'] ?? 1);

        // Insertar solo si no existe enrollment activo
        $stmt = $db->prepare(
            "INSERT IGNORE INTO univ_enrollments (user_id, course_id, course_version, status)
             SELECT ?, ?, ?, 'asignado'
             WHERE NOT EXISTS (
                 SELECT 1 FROM univ_enrollments
                 WHERE user_id = ? AND course_id = ?
                 AND status IN ('asignado','en_progreso','aprobado')
             )"
        );
        $stmt->bind_param('iiiii', $userId, $courseId, $version, $userId, $courseId);
        $stmt->execute();

        $this->redirectPostAsignacion();
    }

    public function desasignar()
    {
        csrf_verify();
        $userId   = (int)($_POST['user_id']   ?? 0);
        $courseId = (int)($_POST['course_id'] ?? 0);
        if (!$userId || !$courseId) { http_response_code(400); exit; }

        $db   = (new Database())->connect();
        $stmt = $db->prepare(
            "DELETE FROM univ_enrollments
             WHERE user_id = ? AND course_id = ? AND status = 'asignado'"
        );
        $stmt->bind_param('ii', $userId, $courseId);
        $stmt->execute();

        $this->redirectPostAsignacion();
    }

    private function redirectPostAsignacion()
    {
        $back = trim($_POST['redirect_back'] ?? '');
        if ($back !== '' && strncmp($back, '/univAdmin/', 11) === 0) {
            $this->redirect($back);
        }
        $perfil = trim($_POST['perfil_filtro'] ?? '');
        $qs = $perfil !== '' ? '?perfil=' . urlencode($perfil) : '';
        $this->redirect("/univAdmin/asignaciones" . $qs);
    }

    public function alumnos($courseId)
    {
        $courseId = (int)$courseId;
        if (!$courseId) { $this->redirect('/univAdmin/index'); }

        $db = (new Database())->connect();

        $stmt = $db->prepare(
            "SELECT id, nombre, tipo, creditos, activo FROM univ_courses WHERE id = ? LIMIT 1"
        );
        $stmt->bind_param('i', $courseId);
        $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc();
        if (!$course) { $this->redirect('/univAdmin/index'); }

        $usuarios = $db->query(
            "SELECT id, nombre, apellido, perfil, email FROM chk_usuarios
             WHERE estado = 'activo' ORDER BY nombre, apellido"
        )->fetch_all(MYSQLI_ASSOC);

        $stmt = $db->prepare(
            "SELECT user_id, status, (SELECT MAX(score) FROM univ_evaluations WHERE enrollment_id = e.id) as max_score 
             FROM univ_enrollments e
             WHERE course_id = ? AND status IN ('asignado','en_progreso','aprobado','reprobado')"
        );
        $stmt->bind_param('i', $courseId);
        $stmt->execute();
        $enrollments = [];
        $scores = [];
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
            $enrollments[$r['user_id']] = $r['status'];
            $scores[$r['user_id']] = $r['max_score'];
        }

        $this->view("univ/admin/curso_alumnos", compact('course', 'usuarios', 'enrollments', 'scores'));
    }

    // ==========================================
    // REPORTES Y ESTADÍSTICAS
    // ==========================================

    public function reports()
    {
        $db = new Database();
        $conn = $db->connect();

        // 1. Estadísticas generales
        $stats = $conn->query("SELECT 
            status, COUNT(*) as total 
            FROM univ_enrollments 
            GROUP BY status")->fetch_all(MYSQLI_ASSOC);

        // 2. Ranking de créditos (Top 10)
        $ranking = $conn->query("SELECT 
            u.nombre, u.apellido, u.perfil,
            SUM(e.creditos_ganados) as total_creditos
            FROM univ_enrollments e
            JOIN chk_usuarios u ON e.user_id = u.id
            WHERE e.status = 'aprobado'
            GROUP BY e.user_id
            ORDER BY total_creditos DESC
            LIMIT 10")->fetch_all(MYSQLI_ASSOC);

        // 3. Cursos más tomados
        $topCourses = $conn->query("SELECT 
            c.nombre, COUNT(e.id) as inscritos,
            SUM(CASE WHEN e.status='aprobado' THEN 1 ELSE 0 END) as aprobados
            FROM univ_courses c
            LEFT JOIN univ_enrollments e ON c.id = e.course_id
            GROUP BY c.id
            ORDER BY inscritos DESC")->fetch_all(MYSQLI_ASSOC);

        $this->view("univ/admin/reports", compact('stats', 'ranking', 'topCourses'));
    }

    public function evaluateHistory($enrollmentId)
    {
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT e.*, u.nombre, u.apellido, u.email, c.nombre as course_name 
                             FROM univ_enrollments e 
                             JOIN chk_usuarios u ON e.user_id = u.id 
                             JOIN univ_courses c ON e.course_id = c.id 
                             WHERE e.id = ?");
        $stmt->bind_param("i", $enrollmentId);
        $stmt->execute();
        $enroll = $stmt->get_result()->fetch_assoc();
        
        if (!$enroll) die("Matrícula no encontrada");

        $evalModel = new UnivEvaluationModel();
        $history = $evalModel->getHistoryByEnrollment($enrollmentId);

        $this->view("univ/admin/evaluation_history", compact('enroll', 'history'));
    }

    public function evaluateDetail($evaluationId)
    {
        $evalModel = new UnivEvaluationModel();
        $evaluation = $evalModel->getById($evaluationId);
        if (!$evaluation) die("Evaluación no encontrada");

        $details = $evalModel->getDetails($evaluationId);

        $this->view("univ/admin/evaluation_detail", compact('evaluation', 'details'));
    }
}
