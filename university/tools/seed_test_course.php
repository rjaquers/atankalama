<?php
/**
 * Script para sembrar el curso de prueba: Contaminación Cruzada.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/core/Autoload.php';

echo "Iniciando siembra de curso de prueba...\n";

$courseModel = new UnivCourseModel();
$pageModel = new UnivPageModel();
$questionModel = new UnivQuestionModel();

// 1. Crear el Curso
$courseData = [
    'nombre' => 'Prevención de Contaminación Cruzada en Cocina',
    'descripcion' => 'Este curso esencial para el personal de cocina enseña a identificar y prevenir los riesgos de contaminación entre alimentos crudos y cocidos, garantizando la seguridad alimentaria en el Hotel Atankalama.',
    'tipo' => 'obligatorio_area',
    'creditos' => 15,
    'min_score_to_approve' => 80,
    'total_preguntas_examen' => 10,
    'tiempo_limite_minutos' => 15,
    'max_intentos' => 3,
    'vigencia_meses' => 12,
    'activo' => 1
];

$courseId = $courseModel->create($courseData);

if (!$courseId) {
    die("Error al crear el curso.\n");
}

echo "Curso creado con ID: $courseId\n";

// 2. Crear las 10 Páginas
$pages = [
    [
        'titulo' => 'Introducción a la Contaminación Cruzada',
        'tipo' => 'html',
        'contenido' => '<p>La contaminación cruzada es la transferencia de microorganismos dañinos (bacterias, virus, parásitos) desde un alimento contaminado a otro que no lo está. Es una de las principales causas de enfermedades transmitidas por alimentos (ETA) en la industria hotelera.</p><p>En esta lección aprenderemos a identificar los puntos críticos en nuestra cocina.</p>',
        'tiempo' => 10
    ],
    [
        'titulo' => 'Tipos: Directa vs Indirecta',
        'tipo' => 'html',
        'contenido' => '<ul><li><strong>Directa:</strong> Ocurre cuando un alimento contaminado toca uno sano (ej: sangre de carne cruda goteando sobre lechuga).</li><li><strong>Indirecta:</strong> Ocurre a través de un intermediario como cuchillos, tablas, trapos o manos del cocinero.</li></ul>',
        'tiempo' => 15
    ],
    [
        'titulo' => 'El Código de Colores de Tablas',
        'tipo' => 'html',
        'contenido' => '<p>Para prevenir riesgos, usamos tablas de picar por colores:</p><ul><li><span style="color:red">Rojo:</span> Carnes rojas crudas.</li><li><span style="color:yellow">Amarillo:</span> Aves crudas (pollo, pavo).</li><li><span style="color:blue">Azul:</span> Pescados y mariscos.</li><li><span style="color:green">Verde:</span> Frutas y verduras.</li><li><span style="color:white">Blanco:</span> Quesos, pan y alimentos cocidos.</li></ul>',
        'tiempo' => 20
    ],
    [
        'titulo' => 'Orden Correcto en la Cámara de Frío',
        'tipo' => 'html',
        'contenido' => '<p>El almacenamiento vertical es clave. Los alimentos deben ordenarse según su temperatura de cocción:</p><ol><li>Superior: Alimentos listos para el consumo (cocidos).</li><li>Medio: Pescados y carnes enteras.</li><li>Inferior: Aves crudas (tienen mayor carga bacteriana).</li></ol>',
        'tiempo' => 20
    ],
    [
        'titulo' => 'Higiene Personal del Cocinero',
        'tipo' => 'html',
        'contenido' => '<p>El uniforme limpio no es solo estética. Las filipinas y delantales acumulan bacterias del exterior. Nunca uses el delantal para limpiarte las manos después de tocar carne cruda.</p>',
        'tiempo' => 10
    ],
    [
        'titulo' => 'Técnica de Lavado de Manos',
        'tipo' => 'html',
        'contenido' => '<p>El lavado debe durar al menos 20 segundos, frotando entre los dedos, uñas y hasta el antebrazo. Debe hacerse cada vez que cambies de tarea o toques un alimento crudo.</p>',
        'tiempo' => 15
    ],
    [
        'titulo' => 'Desinfección de Herramientas',
        'tipo' => 'html',
        'contenido' => '<p>Lavar con agua y jabón no elimina bacterias. Es necesario sanitizar los utensilios con soluciones cloradas o alcohol al 70% después de cada uso con proteínas crudas.</p>',
        'tiempo' => 15
    ],
    [
        'titulo' => 'Gestión de Alérgenos',
        'tipo' => 'html',
        'contenido' => '<p>La contaminación cruzada también aplica a alérgenos. Un cuchillo usado para cortar queso puede causar una reacción fatal en un cliente alérgico a la lactosa si se usa luego para sus verduras.</p>',
        'tiempo' => 20
    ],
    [
        'titulo' => 'Puntos Críticos en el Servicio',
        'tipo' => 'html',
        'contenido' => '<p>Durante el "boleo" o despacho, evita tocar los platos por el borde donde el cliente pondrá la boca. No uses las manos para acomodar guarniciones listas.</p>',
        'tiempo' => 10
    ],
    [
        'titulo' => 'Resumen y Preparación',
        'tipo' => 'html',
        'contenido' => '<p>Recuerda: Separa, Lava, Cocina y Enfría. Estos 4 pilares garantizan una cocina segura. A continuación, realizarás el examen de 10 preguntas.</p>',
        'tiempo' => 10
    ]
];

foreach ($pages as $index => $p) {
    $pageModel->save([
        'course_id' => $courseId,
        'orden' => $index + 1,
        'titulo' => $p['titulo'],
        'tipo' => $p['tipo'],
        'contenido' => $p['contenido'],
        'tiempo_minimo_segundos' => $p['tiempo']
    ]);
}

echo "10 páginas creadas.\n";

// 3. Crear 10 Preguntas
$questions = [
    [
        'texto' => '¿Qué es la contaminación cruzada indirecta?',
        'opts' => [
            ['t' => 'Cuando un alimento toca a otro.', 'c' => 0],
            ['t' => 'Cuando se usan manos o utensilios sucios entre alimentos.', 'c' => 1],
            ['t' => 'Cuando el alimento está vencido.', 'c' => 0]
        ]
    ],
    [
        'texto' => '¿De qué color es la tabla para aves crudas?',
        'opts' => [
            ['t' => 'Roja', 'c' => 0],
            ['t' => 'Azul', 'c' => 0],
            ['t' => 'Amarilla', 'c' => 1]
        ]
    ],
    [
        'texto' => '¿Dónde se deben ubicar las aves crudas en el refrigerador?',
        'opts' => [
            ['t' => 'En el estante superior.', 'c' => 0],
            ['t' => 'En el estante más bajo para evitar goteos.', 'c' => 1],
            ['t' => 'Junto a las frutas.', 'c' => 0]
        ]
    ],
    [
        'texto' => '¿Cuánto tiempo mínimo debe durar el lavado de manos?',
        'opts' => [
            ['t' => '5 segundos.', 'c' => 0],
            ['t' => '20 segundos.', 'c' => 1],
            ['t' => '1 minuto.', 'c' => 0]
        ]
    ],
    [
        'texto' => '¿La tabla verde se usa para:',
        'opts' => [
            ['t' => 'Verduras y frutas.', 'c' => 1],
            ['t' => 'Pescados.', 'c' => 0],
            ['t' => 'Carnes cocidas.', 'c' => 0]
        ]
    ],
    [
        'texto' => '¿Es seguro usar el delantal para secarse las manos?',
        'opts' => [
            ['t' => 'Sí, si está limpio.', 'c' => 0],
            ['t' => 'No, porque acumula bacterias y contamina las manos.', 'c' => 1]
        ]
    ],
    [
        'texto' => '¿Qué se debe hacer después de picar carne cruda?',
        'opts' => [
            ['t' => 'Solo limpiar el cuchillo con un trapo.', 'c' => 0],
            ['t' => 'Lavar y sanitizar tabla, cuchillo y manos.', 'c' => 1],
            ['t' => 'Seguir con las verduras inmediatamente.', 'c' => 0]
        ]
    ],
    [
        'texto' => 'En la tabla roja se pica:',
        'opts' => [
            ['t' => 'Carne roja cruda.', 'c' => 1],
            ['t' => 'Tomates.', 'c' => 0],
            ['t' => 'Pollo.', 'c' => 0]
        ]
    ],
    [
        'texto' => '¿Qué riesgo hay al usar el mismo cuchillo para queso y vegetales?',
        'opts' => [
            ['t' => 'Ninguno.', 'c' => 0],
            ['t' => 'Contaminación por alérgenos.', 'c' => 1],
            ['t' => 'Que el vegetal sepa a queso.', 'c' => 0]
        ]
    ],
    [
        'texto' => '¿Cuándo se considera que un curso está aprobado en este sistema?',
        'opts' => [
            ['t' => 'Con 50%.', 'c' => 0],
            ['t' => 'Con 80% o más según este curso.', 'c' => 1],
            ['t' => 'Con solo asistir.', 'c' => 0]
        ]
    ]
];

foreach ($questions as $q) {
    $options = [];
    foreach ($q['opts'] as $o) {
        $options[] = ['texto_opcion' => $o['t'], 'es_correcta' => $o['c']];
    }
    $questionModel->save([
        'course_id' => $courseId,
        'texto_pregunta' => $q['texto'],
        'options' => $options
    ]);
}

echo "10 preguntas creadas.\n";
echo "Siembra completada con éxito. Curso listo para probar.\n";
