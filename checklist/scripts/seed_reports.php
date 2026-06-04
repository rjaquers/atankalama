require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

// Mock data
$nombres = ['Juan', 'Maria', 'Pedro', 'Ana', 'Diego', 'Lucia', 'Carlos', 'Elena'];
$apellidos = ['Perez', 'Gonzalez', 'Rodriguez', 'Soto', 'Munoz', 'Rojas', 'Sepulveda', 'Morales'];
$usuarios = ['admin@checklist.local', 'supervisor@checklist.local'];

try {
$db = Database::getInstance();

// 1. Obtener checklists existentes
$stmt = $db->query("SELECT id, nombre, area FROM " . DB_PREFIX . "checklists WHERE estado = 'activo'");
$checklists = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($checklists)) {
die("No hay checklists activos para generar reportes.\n");
}

echo "Generando 100 reportes ficticios...\n";

for ($i = 0; $i < 100; $i++) { $checklist=$checklists[array_rand($checklists)];
    $evaluado_nombre=$nombres[array_rand($nombres)]; $evaluado_apellido=$apellidos[array_rand($apellidos)];
    $ejecutado_por=$usuarios[array_rand($usuarios)]; // Random date between Jan 1 and March 15, 2026 $month=rand(1, 3);
    $day=rand(1, 28); $hour=rand(8, 20); $min=rand(0, 59); $fecha=sprintf("2026-%02d-%02d %02d:%02d:00", $month, $day,
    $hour, $min); // Insert Evaluation $stmt=$db->prepare("INSERT INTO " . DB_PREFIX . "evaluaciones (checklist_id,
    evaluado_nombre, evaluado_apellido, ejecutado_por, fecha_evaluacion) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$checklist['id'], $evaluado_nombre, $evaluado_apellido, $ejecutado_por, $fecha]);
    $evaluacion_id = $db->lastInsertId();

    // Get questions for this checklist
    $stmt = $db->prepare("SELECT id, tipo_respuesta FROM " . DB_PREFIX . "checklist_preguntas WHERE checklist_id = ?");
    $stmt->execute([$checklist['id']]);
    $preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($preguntas as $pregunta) {
    $resp_bool = null;
    $resp_text = null;
    $resp_num = null;

    if ($pregunta['tipo_respuesta'] === 'boolean') {
    $resp_bool = (rand(0, 10) > 2) ? 1 : 0; // 80% Yes, 20% No
    } elseif ($pregunta['tipo_respuesta'] === 'numeric_scale') {
    $resp_num = rand(5, 10); // Score between 5 and 10
    } elseif ($pregunta['tipo_respuesta'] === 'text') {
    $resp_text = "Observación automática generada para el reporte #$i";
    }

    $stmt = $db->prepare("INSERT INTO " . DB_PREFIX . "evaluacion_respuestas (evaluacion_id, pregunta_id,
    respuesta_boolean, respuesta_texto, respuesta_numerica) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$evaluacion_id, $pregunta['id'], $resp_bool, $resp_text, $resp_num]);
    }

    if ($i % 10 == 0)
    echo "Generados $i reportes...\n";
    }

    echo "¡Completado! Se han generado 100 reportes exitosamente.\n";

    } catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    }