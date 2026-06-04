<?php
/**
 * Servicio para manejar la lógica compleja de los exámenes.
 */
class UnivExamService
{
    private $questionModel;
    private $evaluationModel;
    private $enrollmentModel;

    public function __construct()
    {
        $this->questionModel = new UnivQuestionModel();
        $this->evaluationModel = new UnivEvaluationModel();
        $this->enrollmentModel = new UnivEnrollmentModel();
    }

    /**
     * Prepara las preguntas para un examen (aleatoriedad).
     */
    public function prepareExam($courseId, $totalRequired)
    {
        $allQuestions = $this->questionModel->getByCourse($courseId);
        
        // Mezclar preguntas y tomar solo las requeridas
        shuffle($allQuestions);
        $selected = array_slice($allQuestions, 0, $totalRequired);

        // Mezclar también las alternativas de cada pregunta
        foreach ($selected as &$q) {
            shuffle($q['options']);
        }

        return $selected;
    }

    /**
     * Califica un examen y registra los resultados.
     */
    public function gradeExam($enroll, $userAnswers, $startTime)
    {
        $courseId = $enroll['course_id'];
        $dbQuestions = $this->questionModel->getByCourse($courseId);
        
        $correctCount = 0;
        $totalQuestions = count($userAnswers);
        $details = [];

        foreach ($userAnswers as $qId => $optId) {
            $isCorrect = false;
            // Buscar la pregunta en la BD para validar
            foreach ($dbQuestions as $dbQ) {
                if ($dbQ['id'] == $qId) {
                    foreach ($dbQ['options'] as $opt) {
                        if ($opt['id'] == $optId && $opt['es_correcta'] == 1) {
                            $isCorrect = true;
                            $correctCount++;
                            break;
                        }
                    }
                    break;
                }
            }
            $details[] = [
                'question_id' => $qId,
                'option_id' => $optId,
                'es_correcta' => $isCorrect ? 1 : 0
            ];
        }

        $score = ($totalQuestions > 0) ? round(($correctCount / $totalQuestions) * 100) : 0;
        $aprobado = ($score >= $enroll['min_score_to_approve']) ? 1 : 0;
        
        $endTime = date('Y-m-d H:i:s');
        $duration = strtotime($endTime) - strtotime($startTime);

        // Registrar Intento
        $evalId = $this->evaluationModel->createAttempt([
            'enrollment_id' => $enroll['id'],
            'numero_intento' => $enroll['intentos_usados'] + 1,
            'score' => $score,
            'preguntas_correctas' => $correctCount,
            'preguntas_totales' => $totalQuestions,
            'aprobado' => $aprobado,
            'tiempo_total_segundos' => $duration,
            'fecha_inicio' => $startTime,
            'fecha_fin' => $endTime,
            'ip_origen' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);

        // Guardar detalle de respuestas
        foreach ($details as $d) {
            $this->evaluationModel->saveAnswer($evalId, $d['question_id'], $d['option_id'], $d['es_correcta']);
        }

        // Actualizar Enrollment
        $this->updateEnrollmentAfterExam($enroll, $aprobado, $score);

        // Notificar por Email
        $this->notifyResult($enroll, $aprobado, $score, $correctCount, $totalQuestions);

        return [
            'score' => $score,
            'aprobado' => $aprobado,
            'correctas' => $correctCount,
            'totales' => $totalQuestions
        ];
    }

    private function notifyResult($enroll, $aprobado, $score, $correctas, $totales)
    {
        $mail = new MailService();
        $userEmail = $_SESSION['user_email'] ?? ''; // Asumiendo que el email está en sesión
        
        if (empty($userEmail)) {
            // Si no está en sesión, lo buscamos en la tabla chk_usuarios
            $db = new Database();
            $conn = $db->connect();
            $stmt = $conn->prepare("SELECT email FROM chk_usuarios WHERE id = ?");
            $stmt->bind_param("i", $enroll['user_id']);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $userEmail = $res['email'] ?? '';
        }

        if (empty($userEmail)) return;

        $estadoStr = $aprobado ? "APROBADO" : "REPROBADO";
        $subject = "Resultado de Capacitación: " . $enroll['nombre'] . " ($estadoStr)";
        
        $html = "<h2>Resultado de Evaluación</h2>";
        $html .= "<p>Estimado/a empleado/a,</p>";
        $html .= "<p>Has finalizado la evaluación del curso: <strong>" . htmlspecialchars($enroll['nombre']) . "</strong></p>";
        $html .= "<ul>";
        $html .= "<li><strong>Estado:</strong> $estadoStr</li>";
        $html .= "<li><strong>Puntaje:</strong> $score%</li>";
        $html .= "<li><strong>Respuestas correctas:</strong> $correctas de $totales</li>";
        $html .= "</ul>";
        
        if ($aprobado) {
            $html .= "<p>¡Felicitaciones! Has sumado " . $enroll['creditos'] . " créditos a tu perfil.</p>";
        } else {
            $html .= "<p>Te recomendamos repasar el contenido y volver a intentarlo.</p>";
        }

        $html .= "<p><small>Este es un mensaje automático del Sistema de Universidad Atankalama.</small></p>";

        // Enviar al alumno
        $mail->send($userEmail, $subject, $html);

        // Enviar a la jefatura (Lógica simplificada por ahora: enviar a una cuenta de RRHH central)
        // En una fase posterior se puede buscar el jefe directo del área.
        $mail->send(MAIL_FROM_EMAIL, "[REPORTE JEFATURA] " . $subject . " - Alumno ID: " . $enroll['user_id'], $html);
    }

    private function updateEnrollmentAfterExam($enroll, $aprobado, $score)
    {
        $db = new Database();
        $conn = $db->connect();
        
        $status = $aprobado ? 'aprobado' : 'reprobado';
        $intentos = $enroll['intentos_usados'] + 1;
        
        // Si reprobó y ya usó sus intentos máximos (v1: 3 intentos)
        if (!$aprobado && $intentos >= 3) {
            $status = 'bloqueado';
        }

        $fechaAprobacion = $aprobado ? "CURRENT_TIMESTAMP" : "NULL";
        
        $sql = "UPDATE univ_enrollments SET 
                status = ?, 
                intentos_usados = ?, 
                fecha_aprobacion = $fechaAprobacion,
                creditos_ganados = ?
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $creditos = $aprobado ? (int)$enroll['creditos'] : 0; 
        
        $stmt->bind_param("siii", $status, $intentos, $creditos, $enroll['id']);
        $stmt->execute();
    }
}
